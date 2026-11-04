<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Services\CashLedger;
use App\Services\CustomerPoints;
use App\Services\CustomerStamps;
use App\Services\LoyaltyProgram;
use App\Services\MidtransGateway;
use App\Services\OrderPaymentService;
use App\Services\OrderPricing;
use App\Services\RecipeStock;
use App\Services\RetailQuantity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()?->tenant_id;
        $userId = auth()->id();

        $categories = Category::where('tenant_id', $tenantId)->get();
        $settings = Setting::where('tenant_id', $tenantId)->pluck('value', 'key')->toArray();
        $pricing = app(OrderPricing::class);
        $products = Product::where('tenant_id', $tenantId)->where('is_active', true)
            ->with(['discounts', 'variants', 'addons', 'units'])->get()->map(function ($product) use ($pricing) {
                $unit = $pricing->unitPrice($product);
                $product->sell_price = $unit['price'];
                $product->final_price = $unit['final'];
                $product->discount_applied = $unit['discount'];
                $product->discount_name = $unit['discount_name'];
                $product->price_tiers = collect($product->price_tiers ?? [])->map(fn ($tier) => array_merge($tier, ['discount' => $pricing->unitPrice($product, (float) $tier['price'])['discount']]))->all();
                foreach ($product->units as $saleUnit) {
                    $saleUnit->discount = $pricing->unitPrice($product, (float) $saleUnit->price)['discount'];
                }
                foreach ($product->variants as $variant) {
                    $variantUnit = $pricing->unitPrice($product, (float) $variant->price);
                    $variant->discount = $variantUnit['discount'];
                }

                return $product;
            });

        $customers = Customer::where('tenant_id', $tenantId)
            ->select(['id', 'name', 'is_member', 'points', 'loyalty_member_code'])
            ->withSum('loyaltyStampTransactions as stamp_balance', 'stamps_delta')
            ->orderBy('name')->limit(50)->get();

        $activeShift = Shift::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if ($activeShift instanceof Shift) {
            /** @var Shift $activeShift */
            session(['active_shift_id' => $activeShift->id]);
            $hasShift = true;
        } else {
            session()->forget('active_shift_id');
            $hasShift = false;
        }

        $pendingPayments = Order::where('tenant_id', $tenantId)->where('user_id', $userId)
            ->where('payment_method', 'midtrans')->where('payment_status', 'unpaid')
            ->where('order_status', '!=', 'cancelled')->latest()->limit(50)->get()
            ->map(fn (Order $order) => ['order_id' => $order->id, 'invoice_number' => $order->invoice_number,
                'payment_method' => 'midtrans', 'grand_total' => (float) $order->grand_total, 'qr_url' => $order->qr_url]);

        // Riwayat cepat POS: kasir hanya melihat transaksi yang ia proses sendiri.
        // Admin tetap dapat melihat riwayat tenant lengkap melalui halaman back-office.
        $recentOrders = Order::with(['customer', 'items'])
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->latest('sold_at')
            ->latest('id')
            ->limit(20)
            ->get();

        return view('pos.index', compact('customers', 'categories', 'products', 'settings', 'hasShift', 'activeShift', 'pendingPayments', 'recentOrders'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $data = $request->validate([
            'items' => 'required|array|min:1|max:200',
            'items.*.id' => 'required|integer',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.unit_id' => 'nullable|integer',
            'items.*.addon_ids' => 'nullable|array|max:20',
            'items.*.addon_ids.*' => 'integer',
            'items.*.note' => 'nullable|string|max:300',
            'checkout_key' => 'required_if:payment_method,midtrans,transfer,qris_manual|nullable|uuid',
            'shift_id' => 'nullable|integer',
            'sold_at' => 'nullable|date|before_or_equal:now',
            'items.*.quantity' => 'required|numeric|decimal:0,3|min:0.001|max:100000',
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where('tenant_id', $tenantId)],
            'points_to_redeem' => 'sometimes|integer|min:0|max:100000000',
            'redeem_stamp_reward' => 'sometimes|boolean',
            'payment_method' => 'required|in:cash,midtrans,transfer,qris_manual',
            'manual_confirmed' => Rule::when(in_array($request->input('payment_method'), ['transfer', 'qris_manual'], true), ['required', 'accepted'], ['exclude']),
            'payment_reference' => 'nullable|string|max:100',
            'payment_status' => 'required|in:paid,unpaid',
            'paid_amount' => 'required|numeric|min:0|max:999999999999',
            'grand_total' => 'nullable|numeric|min:0|max:999999999999',
            'order_type' => 'required|in:dine_in,takeaway,delivery',
            'table_number' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
        ]);
        abort_if($data['payment_method'] === 'midtrans' && Gate::denies('feature-qris'), 403, 'Paket Anda tidak mendukung QRIS.');
        abort_if(! empty($data['customer_id']) && Gate::denies('feature-crm'), 403, 'Pelanggan dan bon memerlukan paket aktif.');

        $manual = in_array($data['payment_method'], ['transfer', 'qris_manual'], true);
        if ($manual && $data['payment_status'] !== 'paid') {
            throw ValidationException::withMessages(['payment_status' => 'Pembayaran manual harus sudah diterima dan diperiksa. Gunakan bon untuk tagihan belum lunas.']);
        }

        $hash = hash('sha256', json_encode($data));

        $order = DB::transaction(function () use ($data, $tenantId, $hash) {
            // Serializes the monthly quota and checkout/shift close for this tenant/user.
            $tenant = Tenant::lockForUpdate()->findOrFail($tenantId);
            if (! empty($data['checkout_key'])) {
                $previous = Order::where('tenant_id', $tenantId)->where('checkout_key', $data['checkout_key'])->first();
                if ($previous) {
                    abort_unless($previous->user_id === auth()->id() && $previous->request_hash === $hash, 409, 'Referensi transaksi sudah digunakan dengan isi berbeda.');

                    return $previous;
                }
            }
            if ($tenant->isTransactionLimitReached()) {
                throw ValidationException::withMessages(['items' => 'Batas transaksi paket telah tercapai.']);
            }
            $shift = Shift::where('tenant_id', $tenantId)->where('user_id', auth()->id())->where('status', 'open')->lockForUpdate()->first();
            if (! $shift || (isset($data['shift_id']) && (int) $data['shift_id'] !== $shift->id)) {
                throw ValidationException::withMessages(['shift' => 'Mulai jualan sebelum melakukan transaksi.']);
            }

            if (! empty($data['sold_at']) && Carbon::parse($data['sold_at'])->lt(Carbon::parse($shift->start_time, config('app.timezone')))) {
                throw ValidationException::withMessages(['sold_at' => 'Waktu transaksi berada di luar shift aktif.']);
            }
            $products = Product::where('tenant_id', $tenantId)->where('is_active', true)
                ->whereIn('id', array_column($data['items'], 'id'))->orderBy('id')->lockForUpdate()->with(['discounts', 'materials'])->get()->keyBy('id');
            $subtotal = $discount = 0;
            $lines = [];
            $materialTotals = [];
            $stockTotals = [];
            foreach ($data['items'] as $item) {
                $product = $products->get($item['id']);
                if (! $product) {
                    throw ValidationException::withMessages(['items' => 'Produk tidak tersedia di toko ini.']);
                }
                $variant = ! empty($item['variant_id']) ? $product->variants()->lockForUpdate()->find($item['variant_id']) : null;
                if (! empty($item['variant_id']) && ! $variant) {
                    throw ValidationException::withMessages(['items' => 'Varian tidak tersedia untuk menu ini.']);
                }
                RetailQuantity::requireWhole($item['quantity'], $product->allow_fraction);
                $saleUnit = ! empty($item['unit_id']) ? $product->units()->find($item['unit_id']) : null;
                if (! empty($item['unit_id']) && (! $saleUnit || $variant)) {
                    throw ValidationException::withMessages(['items' => 'Kemasan tidak tersedia atau tidak dapat digabungkan dengan varian.']);
                }
                $factor = $saleUnit?->factor ?? 1;
                $baseQuantity = RetailQuantity::base($item['quantity'], $factor);
                RetailQuantity::requireWhole($baseQuantity, $product->allow_fraction);
                $addonIds = $item['addon_ids'] ?? [];
                $addons = $product->addons()->whereIn('id', $addonIds)->get();
                if ($addons->count() !== count($addonIds)) {
                    throw ValidationException::withMessages(['items' => 'Tambahan menu tidak valid.']);
                }
                $stockModel = $variant ?? $product;
                $stockKey = ($variant ? 'variant-' : 'product-').$stockModel->id;
                $tracked = $product->type === 'product' && $product->manage_stock;
                $stockTotals[$stockKey] = ($stockTotals[$stockKey] ?? 0) + ($tracked ? $baseQuantity : 0);
                if ($tracked && RetailQuantity::ticks($stockModel->stock) < RetailQuantity::ticks($stockTotals[$stockKey])) {
                    throw ValidationException::withMessages(['items' => "Stok {$product->product_name} tidak mencukupi."]);
                }
                $pricing = app(OrderPricing::class);
                $unit = $pricing->unitPrice($product, $saleUnit ? (float) $saleUnit->price : ($variant ? (float) $variant->price : $pricing->retailPrice($product, (float) $item['quantity'])));
                $price = $unit['price'] + $addons->sum('price');
                $lineSubtotal = (int) round($price * $item['quantity']);
                $lineDiscount = (int) round($unit['discount'] * $item['quantity']);
                $subtotal += $lineSubtotal;
                $discount += $lineDiscount;
                $reserved = [];
                foreach ($product->type === 'service' ? [] : $product->materials as $material) {
                    $reserved[$material->id] = $material->pivot->quantity * $baseQuantity;
                    $materialTotals[$material->id] = ($materialTotals[$material->id] ?? 0) + $reserved[$material->id];
                }
                $costKnown = ! $variant && $product->cost_price > 0 && $addons->every(fn ($addon) => $addon->cost !== null);
                $lines[] = ['allow_fraction' => $product->allow_fraction, 'requires_preparation' => $product->type === 'product' && (bool) ($product->requires_preparation ?? (auth()->user()->tenant->businessType() === 'food')), 'unit_name' => $saleUnit?->name ?? $product->base_unit, 'unit_factor' => $factor, 'discount_amount' => $lineDiscount, 'product_id' => $product->id, 'variant_id' => $variant?->id,
                    'product_name' => $product->product_name.($variant ? ' · '.$variant->name : ''),
                    'quantity' => $item['quantity'], 'price' => $price, 'subtotal' => $lineSubtotal,
                    'reserved_stock' => $tracked ? $baseQuantity : 0, 'reserved_materials' => $reserved,
                    'addons' => $addons->map->only(['id', 'name', 'price'])->values()->all(), 'note' => $item['note'] ?? null,
                    'unit_cost' => $costKnown ? $product->cost_price * $factor + $addons->sum('cost') : null];
            }
            $settings = Setting::where('tenant_id', $tenantId)->pluck('value', 'key');
            $loyaltyProgram = app(LoyaltyProgram::class)->type($settings->all());
            $taxRate = min(100, max(0, (float) ($settings['tax_percentage'] ?? 0)));
            $tax = ($settings['tax_active'] ?? '0') === '1' ? (int) round(($subtotal - $discount) * $taxRate / 100) : 0;
            $beforeLoyaltyTotal = $subtotal - $discount + $tax;
            $pointsToRedeem = (int) ($data['points_to_redeem'] ?? 0);
            $redeemStampReward = (bool) ($data['redeem_stamp_reward'] ?? false);
            $stampsToRedeem = 0;
            $loyaltyDiscount = 0;
            if ($loyaltyProgram === 'stamps' && $pointsToRedeem > 0) {
                throw ValidationException::withMessages(['points_to_redeem' => 'Program toko menggunakan stamp, bukan poin.']);
            }
            if ($loyaltyProgram !== 'stamps' && $redeemStampReward) {
                throw ValidationException::withMessages(['redeem_stamp_reward' => 'Program stamp tidak aktif untuk toko ini.']);
            }
            if ($redeemStampReward) {
                if (empty($data['customer_id'])) {
                    throw ValidationException::withMessages(['customer_id' => 'Pilih member untuk menggunakan reward stamp.']);
                }
                if ($data['payment_method'] === 'cash' && $data['payment_status'] === 'unpaid') {
                    throw ValidationException::withMessages(['redeem_stamp_reward' => 'Reward stamp hanya dapat digunakan pada transaksi yang dibayar.']);
                }
                $member = Customer::where('tenant_id', $tenantId)->lockForUpdate()->findOrFail($data['customer_id']);
                $loyaltyDiscount = app(CustomerStamps::class)->redemptionDiscount($member, $beforeLoyaltyTotal, $settings->all());
                $stampsToRedeem = max(2, (int) ($settings['stamp_reward_goal'] ?? 8));
            } elseif ($pointsToRedeem > 0) {
                if (empty($data['customer_id'])) {
                    throw ValidationException::withMessages(['points_to_redeem' => 'Pilih member untuk menggunakan poin.']);
                }
                $member = Customer::where('tenant_id', $tenantId)->lockForUpdate()->findOrFail($data['customer_id']);
                $loyaltyDiscount = app(CustomerPoints::class)->redemptionDiscount($member, $pointsToRedeem, $beforeLoyaltyTotal, $settings->all());
            }
            $total = $beforeLoyaltyTotal - $loyaltyDiscount;
            if ($total > 999999999999 || (isset($data['grand_total']) && (float) $data['grand_total'] !== (float) $total)) {
                throw ValidationException::withMessages(['grand_total' => 'Total berubah. Muat ulang kasir dan periksa harga terbaru.']);
            }
            $digital = $data['payment_method'] === 'midtrans';
            $manual = in_array($data['payment_method'], ['transfer', 'qris_manual'], true);
            $paid = ! $digital && $data['payment_status'] === 'paid';
            if ($manual && (float) $data['paid_amount'] !== (float) $total) {
                throw ValidationException::withMessages(['paid_amount' => 'Nominal pembayaran manual harus sama dengan total tagihan.']);
            }
            if ($paid && $data['paid_amount'] < $total) {
                throw ValidationException::withMessages(['paid_amount' => 'Uang tunai kurang.']);
            }
            if ($digital && $total < 1) {
                throw ValidationException::withMessages(['grand_total' => 'Nominal QRIS harus lebih dari nol.']);
            }
            if (! $digital && ! $paid && empty($data['customer_id'])) {
                throw ValidationException::withMessages(['customer_id' => 'Pilih pelanggan untuk transaksi bon.']);
            }

            $order = Order::create([
                'payment_reference' => $manual ? ($data['payment_reference'] ?? null) : null,
                'payment_verified_at' => $manual ? now() : null,
                'payment_verified_by' => $manual ? auth()->id() : null,
                'payment_attention' => $digital,
                'checkout_key' => $data['checkout_key'] ?? null, 'request_hash' => $hash,
                'service_status' => $products->contains(fn ($product) => $product->type === 'service') ? 'queued' : null,
                'kitchen_status' => auth()->user()->tenant->hasBusinessModule('food') && collect($lines)->contains(fn ($line) => $line['requires_preparation']) ? 'queued' : null, 'sold_at' => isset($data['sold_at']) ? Carbon::parse($data['sold_at'])->setTimezone(config('app.timezone')) : now(),
                'tenant_id' => $tenantId, 'user_id' => auth()->id(), 'shift_id' => $shift->id,
                'customer_id' => $data['customer_id'] ?? null,
                'loyalty_program_type' => $loyaltyProgram,
                'stamp_minimum_purchase' => $loyaltyProgram === 'stamps' ? (int) ($settings['stamp_minimum_purchase'] ?? 0) : null,
                'invoice_number' => 'INV-'.($data['checkout_key'] ?? Str::uuid()),
                'order_type' => $data['order_type'], 'table_number' => $data['table_number'] ?? null,
                'subtotal' => $subtotal, 'discount' => $discount, 'tax' => $tax, 'grand_total' => $total,
                'points_redeemed' => $pointsToRedeem, 'loyalty_discount' => $loyaltyDiscount,
                'stamps_redeemed' => $stampsToRedeem,
                'payment_method' => $data['payment_method'], 'payment_status' => $paid ? 'paid' : 'unpaid',
                'paid_amount' => $paid ? $data['paid_amount'] : 0,
                'change_amount' => $paid ? $data['paid_amount'] - $total : 0,
                'order_status' => $paid ? 'completed' : 'pending', 'note' => $data['note'] ?? null,
            ]);
            app(CustomerPoints::class)->redeemForOrder($order);
            app(CustomerStamps::class)->redeemForOrder($order);
            app(CashLedger::class)->checkout($order);
            foreach ($lines as $line) {
                $orderItem = $order->items()->create($line);
                if ($line['reserved_stock']) {
                    $stockModel = $line['variant_id'] ? $products[$line['product_id']]->variants()->findOrFail($line['variant_id']) : $products[$line['product_id']];
                    $beforeStock = $stockModel->stock;
                    $stockModel->decrement('stock', $line['reserved_stock']);
                    StockMovement::create(['tenant_id' => $tenantId, 'product_id' => $line['product_id'], 'user_id' => auth()->id(), 'type' => 'sales', 'quantity' => $line['reserved_stock'], 'before_stock' => $beforeStock, 'after_stock' => $stockModel->stock, 'note' => 'Penjualan '.$order->invoice_number, 'reference_type' => 'order_item', 'reference_id' => $orderItem->id]);
                }
            }
            app(RecipeStock::class)->deduct($tenantId, $materialTotals, $order);
            if ($paid) {
                app(CustomerPoints::class)->award($order);
                app(CustomerStamps::class)->award($order);
            } elseif (! $digital) {
                Customer::where('tenant_id', $tenantId)->whereKey($order->customer_id)->increment('total_debt', $total);
            }

            return $order;
        });

        // Commit the invoice before talking to the provider. A timeout must not erase
        // the reference needed to reconcile a charge that may already have succeeded.
        if ($order->payment_method !== 'midtrans' || ! $order->wasRecentlyCreated) {
            return $this->receiptResponse($order);
        }
        try {
            $qrUrl = app(MidtransGateway::class)->charge($order->invoice_number, (int) $order->grand_total);
            if (! $qrUrl) {
                throw new \RuntimeException('Gateway tidak mengembalikan kode QR.');
            }
            $order->update(['qr_url' => $qrUrl, 'payment_attention' => false]);
        } catch (\Throwable $exception) {
            report($exception);
            app(OrderPaymentService::class)->apply($order->id, [
                'order_id' => $order->invoice_number, 'gross_amount' => $order->grand_total, 'transaction_status' => 'expire',
            ]);
            $order->refresh();
            if ($order->payment_status === 'paid') {
                return $this->receiptResponse($order);
            }

            Order::whereKey($order->id)->where('payment_status', 'unpaid')->update(['payment_attention' => true]);

            return response()->json(['success' => false, 'order_id' => $order->id, 'order_status' => $order->order_status, 'message' => 'QRIS belum dapat dibuat. Stok telah dilepas; periksa status nota ini sebelum membuat pembayaran baru.'], 503);
        }

        return $this->receiptResponse($order->refresh());
    }

    private function receiptResponse(Order $order)
    {
        $loyalty = [];
        $customer = $order->customer;
        if ($customer?->is_member && Gate::allows('feature-loyalty')) {
            $customer->ensureLoyaltyCard();
            $cardUrl = route('loyalty.card', $customer->loyalty_card_token);
            $phone = preg_replace('/\D+/', '', (string) $customer->phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62'.substr($phone, 1);
            }
            $message = 'Kartu member '.$order->tenant->name.' untuk '.$customer->name.': '.$cardUrl;
            $programType = $order->loyalty_program_type ?? app(LoyaltyProgram::class)->type(app(CustomerPoints::class)->settings($order->tenant_id));
            $loyalty = [
                'loyalty_card_url' => $cardUrl,
                'whatsapp_share_url' => 'https://wa.me/'.($phone ?: '').'?text='.rawurlencode($message),
                'loyalty_program_type' => $programType,
                'points_awarded' => (int) $order->points_awarded,
                'points_balance' => (int) $customer->fresh()->points,
                'points_redeemed' => (int) $order->points_redeemed,
                'stamps_awarded' => (int) $order->stamps_awarded,
                'stamp_balance' => app(CustomerStamps::class)->balance($customer->fresh()),
                'stamps_redeemed' => (int) $order->stamps_redeemed,
                'loyalty_discount' => (int) $order->loyalty_discount,
            ];
        }

        return response()->json(array_merge(['success' => true, 'status' => $order->payment_status, 'order_id' => $order->id, 'invoice_number' => $order->invoice_number, 'payment_method' => $order->payment_method, 'payment_status' => $order->payment_status, 'order_status' => $order->order_status, 'grand_total' => (float) $order->grand_total, 'qr_url' => $order->qr_url], $loyalty));
    }

    public function checkStatus(int $id)
    {
        $order = Order::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        if ($order->payment_status !== 'paid' && $order->payment_method === 'midtrans') {
            try {
                $status = app(MidtransGateway::class)->status($order->invoice_number);
                app(OrderPaymentService::class)->apply($order->id, $status);
                $order->refresh();
            } catch (\Throwable $e) {
                report($e);

                return response()->json(['success' => false, 'message' => 'Status pembayaran belum dapat diperiksa. Silakan coba lagi.'], 503);
            }
        }

        if ($order->payment_status === 'paid') {
            return $this->receiptResponse($order);
        }

        return response()->json(['success' => true, 'status' => $order->order_status === 'cancelled' ? 'cancelled' : 'unpaid']);
    }
}
