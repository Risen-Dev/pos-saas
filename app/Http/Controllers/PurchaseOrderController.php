<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Services\RetailQuantity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = PurchaseOrder::with('supplier')->where('tenant_id', $request->user()->tenant_id)->latest()->paginate(20);

        return view('purchases.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $tenant = $request->user()->tenant_id;
        $suppliers = Supplier::where('tenant_id', $tenant)->where('is_active', true)->orderBy('name')->get();
        $products = Product::where('tenant_id', $tenant)->where('type', 'product')->where('manage_stock', true)->where('is_active', true)->orderBy('product_name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['supplier_id' => 'nullable|integer', 'expected_at' => 'nullable|date', 'note' => 'nullable|string|max:1000', 'items' => 'required|array|min:1', 'items.*' => 'required|array:product_id,quantity,unit_cost', 'items.*.product_id' => 'required|integer', 'items.*.quantity' => 'required|numeric|gt:0|max:1000000000|decimal:0,3', 'items.*.unit_cost' => 'required|numeric|min:0|max:999999999999']);
        $tenant = $request->user()->tenant_id;

        return DB::transaction(function () use ($request, $data, $tenant) {
            $supplier = ! empty($data['supplier_id']) ? Supplier::where('tenant_id', $tenant)->findOrFail($data['supplier_id']) : null;
            $po = PurchaseOrder::create(['tenant_id' => $tenant, 'supplier_id' => $supplier?->id, 'created_by' => $request->user()->id, 'number' => 'PO-'.now()->format('Ymd').'-'.Str::ulid(), 'status' => 'ordered', 'ordered_at' => today(), 'expected_at' => $data['expected_at'] ?? null, 'note' => $data['note'] ?? null]);
            foreach ($data['items'] as $line) {
                $product = Product::where('tenant_id', $tenant)->where('manage_stock', true)->where('type', 'product')->where('is_active', true)->findOrFail($line['product_id']);
                RetailQuantity::requireWhole($line['quantity'], $product->allow_fraction);
                $po->items()->create($line);
            }

            return redirect()->route('purchases.show', $po)->with('success', 'Purchase order dibuat.');
        });
    }

    public function show(Request $request, PurchaseOrder $purchase)
    {
        abort_unless($purchase->tenant_id === $request->user()->tenant_id, 404);
        $purchase->load(['supplier', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function receive(Request $request, PurchaseOrder $purchase)
    {
        abort_unless($purchase->tenant_id === $request->user()->tenant_id, 404);
        $data = $request->validate(['items' => 'required|array', 'items.*' => 'nullable|numeric|min:0|max:1000000000|decimal:0,3']);
        DB::transaction(function () use ($request, $purchase, $data) {
            Tenant::whereKey($request->user()->tenant_id)->lockForUpdate()->firstOrFail();
            $purchase = PurchaseOrder::lockForUpdate()->findOrFail($purchase->id);
            abort_unless(in_array($purchase->status, ['ordered', 'partial'], true), 422, 'Purchase order tidak dapat diterima.');
            $purchase->load('items');
            $received = false;
            foreach ($purchase->items as $item) {
                $qty = (float) ($data['items'][$item->id] ?? 0);
                if ($qty <= 0) {
                    continue;
                }
                $remaining = (float) $item->quantity - (float) $item->received_quantity;
                if ($qty > $remaining + 0.0001) {
                    throw ValidationException::withMessages(['items.'.$item->id => 'Jumlah diterima melebihi sisa PO.']);
                }
                $product = Product::where('tenant_id', $request->user()->tenant_id)->whereKey($item->product_id)->lockForUpdate()->firstOrFail();
                RetailQuantity::requireWhole($qty, $product->allow_fraction);
                $before = (float) $product->stock;
                $after = $before + $qty;
                $oldQty = max(0, $before);
                $oldCost = (float) $product->cost_price;
                $newCost = ($oldQty + $qty) > 0 ? (($oldQty * $oldCost) + ($qty * (float) $item->unit_cost)) / ($oldQty + $qty) : (float) $item->unit_cost;
                $product->update(['stock' => $after, 'cost_price' => round($newCost, 2)]);
                $item->increment('received_quantity', $qty);
                $received = true;
                $product->stockMovements()->create(['tenant_id' => $request->user()->tenant_id, 'user_id' => $request->user()->id, 'supplier_id' => $purchase->supplier_id, 'purchase_price' => $item->unit_cost, 'type' => 'stock_in', 'quantity' => $qty, 'before_stock' => $before, 'after_stock' => $after, 'note' => 'Penerimaan '.$purchase->number, 'reference_type' => 'purchase_order', 'reference_id' => $purchase->id]);
            }
            if (! $received) {
                throw ValidationException::withMessages(['items' => 'Masukkan setidaknya satu jumlah penerimaan.']);
            }
            $purchase->refresh()->load('items');
            $purchase->update(['status' => $purchase->items->every(fn ($i) => (float) $i->received_quantity >= (float) $i->quantity - 0.0001) ? 'received' : 'partial']);
            Cache::forget('tenant_'.$request->user()->tenant_id.'_products_pos');
        });

        return back()->with('success', 'Penerimaan stok berhasil dicatat.');
    }
}
