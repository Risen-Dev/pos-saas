<?php

namespace Database\Seeders;

use App\Models\CashEntry;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ServiceOrder;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\Stocktake;
use App\Models\StocktakeItem;
use App\Models\Subscription;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\TenantWallet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Idempotent, local-only demo dataset for manually exploring the main GrowPOS flows.
 *
 * Run after migrations with: php artisan db:seed --class=GrowPosDemoSeeder
 */
class GrowPosDemoSeeder extends Seeder
{
    private string $password;

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('GrowPosDemoSeeder hanya boleh dijalankan pada environment local/testing.');
        }

        $this->password = (string) env('GROWPOS_DEMO_PASSWORD', 'GrowPOS-Demo-2026!');
        $this->call(PlanSeeder::class);

        DB::transaction(function (): void {
            $growth = Plan::where('slug', 'growth')->firstOrFail();

            foreach ($this->businesses() as $business) {
                $this->seedBusiness($business, $growth);
            }
        });

        $this->command?->newLine();
        $this->command?->info('Data demo GrowPOS siap digunakan.');
        $this->command?->line('Password semua akun: ' . (env('GROWPOS_DEMO_PASSWORD') ? '[GROWPOS_DEMO_PASSWORD]' : $this->password));
        foreach ($this->businesses() as $business) {
            $this->command?->line($business['label'] . ': ' . $business['owner_email'] . ' (owner), ' . $business['cashier_email'] . ' (kasir)');
        }
    }

    private function businesses(): array
    {
        return [
            [
                'key' => 'kedai-senja',
                'label' => 'Kedai Senja (F&B)',
                'type' => 'food',
                'modules' => ['goods', 'food'],
                'name' => 'Kedai Senja Demo',
                'owner_name' => 'Nadia Putri',
                'cashier_name' => 'Raka Kasir',
                'owner_email' => 'owner.kedai@growpos.test',
                'cashier_email' => 'kasir.kedai@growpos.test',
                'phone' => '081200001001',
                'address' => 'Jl. Merdeka No. 10, Bandung',
                'categories' => ['Kopi', 'Non-kopi', 'Makanan'],
                'products' => [
                    ['sku' => 'DEMO-KEDAI-001', 'name' => 'Kopi Susu Gula Aren', 'category' => 'Kopi', 'cost' => 9000, 'price' => 22000, 'stock' => 48, 'unit' => 'gelas', 'type' => 'product', 'prep' => true],
                    ['sku' => 'DEMO-KEDAI-002', 'name' => 'Americano', 'category' => 'Kopi', 'cost' => 6000, 'price' => 18000, 'stock' => 32, 'unit' => 'gelas', 'type' => 'product', 'prep' => true],
                    ['sku' => 'DEMO-KEDAI-003', 'name' => 'Matcha Latte', 'category' => 'Non-kopi', 'cost' => 11000, 'price' => 26000, 'stock' => 25, 'unit' => 'gelas', 'type' => 'product', 'prep' => true],
                    ['sku' => 'DEMO-KEDAI-004', 'name' => 'Roti Bakar Cokelat', 'category' => 'Makanan', 'cost' => 7000, 'price' => 18000, 'stock' => 19, 'unit' => 'porsi', 'type' => 'product', 'prep' => true],
                ],
                'supplier' => ['name' => 'Kopi Nusantara Demo', 'phone' => '081200001101'],
                'customers' => [['name' => 'Rani Amelia', 'phone' => '081300001001'], ['name' => 'Dimas Pratama', 'phone' => '081300001002'], ['name' => 'Sinta Maharani', 'phone' => '081300001003']],
            ],
            [
                'key' => 'toko-maju',
                'label' => 'Toko Maju (Retail)',
                'type' => 'retail',
                'modules' => ['goods'],
                'name' => 'Toko Maju Demo',
                'owner_name' => 'Budi Santoso',
                'cashier_name' => 'Ayu Kasir',
                'owner_email' => 'owner.toko@growpos.test',
                'cashier_email' => 'kasir.toko@growpos.test',
                'phone' => '081200002001',
                'address' => 'Jl. Melati No. 22, Yogyakarta',
                'categories' => ['Minuman', 'Sembako', 'Kebutuhan Harian'],
                'products' => [
                    ['sku' => 'DEMO-TOKO-001', 'name' => 'Air Mineral 600 ml', 'category' => 'Minuman', 'cost' => 3000, 'price' => 5000, 'stock' => 120, 'unit' => 'pcs', 'type' => 'product'],
                    ['sku' => 'DEMO-TOKO-002', 'name' => 'Beras Premium', 'category' => 'Sembako', 'cost' => 12000, 'price' => 15000, 'stock' => 45, 'unit' => 'kg', 'type' => 'product', 'fraction' => true],
                    ['sku' => 'DEMO-TOKO-003', 'name' => 'Minyak Goreng 1 L', 'category' => 'Sembako', 'cost' => 15500, 'price' => 18000, 'stock' => 36, 'unit' => 'botol', 'type' => 'product'],
                    ['sku' => 'DEMO-TOKO-004', 'name' => 'Sabun Mandi', 'category' => 'Kebutuhan Harian', 'cost' => 3500, 'price' => 5000, 'stock' => 7, 'unit' => 'pcs', 'type' => 'product'],
                ],
                'supplier' => ['name' => 'Grosir Sumber Rejeki Demo', 'phone' => '081200002101'],
                'customers' => [['name' => 'Pak Hendra', 'phone' => '081300002001'], ['name' => 'Maya Lestari', 'phone' => '081300002002'], ['name' => 'Toko Berkah', 'phone' => '081300002003']],
            ],
            [
                'key' => 'laundry-ceria',
                'label' => 'Laundry Ceria (Jasa)',
                'type' => 'service',
                'modules' => ['services'],
                'name' => 'Laundry Ceria Demo',
                'owner_name' => 'Sari Wulandari',
                'cashier_name' => 'Tono Kasir',
                'owner_email' => 'owner.laundry@growpos.test',
                'cashier_email' => 'kasir.laundry@growpos.test',
                'phone' => '081200003001',
                'address' => 'Jl. Kenanga No. 8, Surabaya',
                'categories' => ['Cuci Reguler', 'Layanan Khusus', 'Produk Tambahan'],
                'products' => [
                    ['sku' => 'DEMO-LAUNDRY-001', 'name' => 'Cuci Kering Setrika', 'category' => 'Cuci Reguler', 'cost' => 4000, 'price' => 9000, 'stock' => 0, 'unit' => 'kg', 'type' => 'service'],
                    ['sku' => 'DEMO-LAUNDRY-002', 'name' => 'Cuci Satuan Bed Cover', 'category' => 'Layanan Khusus', 'cost' => 12000, 'price' => 35000, 'stock' => 0, 'unit' => 'pcs', 'type' => 'service'],
                    ['sku' => 'DEMO-LAUNDRY-003', 'name' => 'Setrika Saja', 'category' => 'Cuci Reguler', 'cost' => 2500, 'price' => 6000, 'stock' => 0, 'unit' => 'kg', 'type' => 'service'],
                    ['sku' => 'DEMO-LAUNDRY-004', 'name' => 'Pewangi Premium', 'category' => 'Produk Tambahan', 'cost' => 1000, 'price' => 3000, 'stock' => 30, 'unit' => 'paket', 'type' => 'product'],
                ],
                'supplier' => ['name' => 'Kimia Laundry Sejahtera Demo', 'phone' => '081200003101'],
                'customers' => [['name' => 'Dewi Anggraini', 'phone' => '081300003001'], ['name' => 'Andi Saputra', 'phone' => '081300003002'], ['name' => 'Kost Harmoni', 'phone' => '081300003003']],
            ],
        ];
    }

    private function seedBusiness(array $business, Plan $growth): void
    {
        $owner = User::updateOrCreate(['email' => $business['owner_email']], [
            'name' => $business['owner_name'],
            'password' => Hash::make($this->password),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::updateOrCreate(['slug' => $business['key'] . '-demo'], [
            'user_id' => $owner->id,
            'name' => $business['name'],
            'business_type' => $business['type'],
            'business_modules' => $business['modules'],
            'email' => $business['owner_email'],
            'phone' => $business['phone'],
            'address' => $business['address'],
            'status' => 'active',
        ]);
        $owner->update(['tenant_id' => $tenant->id]);

        $cashier = User::updateOrCreate(['email' => $business['cashier_email']], [
            'tenant_id' => $tenant->id,
            'name' => $business['cashier_name'],
            'password' => Hash::make($this->password),
            'role' => 'kasir',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        Subscription::updateOrCreate(['tenant_id' => $tenant->id, 'plan_id' => $growth->id], [
            'start_date' => today()->subDays(10),
            'end_date' => today()->addYear(),
            'status' => 'active',
        ]);
        TenantWallet::updateOrCreate(['tenant_id' => $tenant->id], [
            'bank_name' => 'Bank Demo',
            'account_number' => '000123456789',
            'account_name' => $business['name'],
            'balance' => 325000,
        ]);

        foreach (
            [
                'point_mode' => 'per_investment',
                'point_rule_value' => '10000',
                'point_member_only' => '1',
                'point_redemption_points' => '100',
                'point_redemption_value' => '10000',
                'tax_active' => '0',
                'tax_percentage' => '10',
                'store_receipt_footer' => 'Terima kasih sudah berbelanja!',
            ] as $key => $value
        ) {
            Setting::updateOrCreate(['tenant_id' => $tenant->id, 'key' => $key], ['value' => $value]);
        }

        $categories = [];
        foreach ($business['categories'] as $index => $name) {
            $categories[$name] = Category::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $name],
                ['slug' => Str::slug($business['key'] . '-' . $name) . '-' . ($index + 1)]
            );
        }

        $products = [];
        foreach ($business['products'] as $index => $data) {
            $products[] = Product::updateOrCreate(['tenant_id' => $tenant->id, 'sku' => $data['sku']], [
                'category_id' => $categories[$data['category']]->id,
                'barcode' => '899' . str_pad((string) ($tenant->id * 100 + $index + 1), 10, '0', STR_PAD_LEFT),
                'product_name' => $data['name'],
                'type' => $data['type'],
                'cost_price' => $data['cost'],
                'sell_price' => $data['price'],
                'stock' => $data['stock'],
                'manage_stock' => $data['type'] === 'product',
                'min_stock' => $data['stock'] > 0 ? min(8, $data['stock']) : 0,
                'base_unit' => $data['unit'],
                'allow_fraction' => $data['fraction'] ?? ($data['type'] === 'service' && $data['unit'] === 'kg'),
                'price_tiers' => $data['sku'] === 'DEMO-TOKO-001' ? [['min_quantity' => 6, 'price' => 4500], ['min_quantity' => 24, 'price' => 4000]] : null,
                'requires_preparation' => $data['prep'] ?? false,
                'desc' => 'Data contoh untuk pengujian GrowPOS.',
                'is_active' => true,
            ]);
        }

        if ($business['type'] === 'food') {
            $products[0]->addons()->updateOrCreate(['name' => 'Extra shot'], ['price' => 5000, 'cost' => 1500]);
            $products[0]->addons()->updateOrCreate(['name' => 'Susu oat'], ['price' => 6000, 'cost' => 2500]);
        }
        if ($business['type'] === 'retail') {
            $products[0]->units()->updateOrCreate(['name' => 'Dus isi 24'], ['factor' => 24, 'price' => 108000]);
            $products[1]->units()->updateOrCreate(['name' => 'Karung 5 kg'], ['factor' => 5, 'price' => 72000]);
        }

        $customers = [];
        foreach ($business['customers'] as $index => $customerData) {
            $customer = Customer::updateOrCreate(['tenant_id' => $tenant->id, 'name' => $customerData['name']], [
                'phone' => $customerData['phone'],
                'points' => $index === 0 ? 185 : 35,
                'is_member' => $index < 2,
                'total_debt' => $index === 1 ? 45000 : 0,
            ]);
            if ($customer->is_member) {
                $customer->ensureLoyaltyCard();
            }
            $customers[] = $customer;
        }

        $supplier = Supplier::updateOrCreate(['tenant_id' => $tenant->id, 'name' => $business['supplier']['name']], [
            'phone' => $business['supplier']['phone'],
            'email' => 'supplier+' . Str::slug($business['key']) . '@growpos.test',
            'address' => 'Alamat pemasok demo',
        ]);

        $openShift = Shift::updateOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $owner->id, 'notes' => 'GrowPOS demo: shift owner terbuka'],
            ['start_time' => now()->subHours(4), 'cash_start' => 300000, 'cash_expected' => 300000, 'status' => 'open']
        );
        $closedShift = Shift::updateOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $cashier->id, 'notes' => 'GrowPOS demo: shift kasir ditutup'],
            [
                'start_time' => now()->subDays(2)->setTime(8, 0),
                'end_time' => now()->subDays(2)->setTime(17, 0),
                'cash_start' => 200000,
                'cash_expected' => 875000,
                'cash_actual' => 870000,
                'cash_difference' => -5000,
                'status' => 'closed'
            ]
        );

        $paidOrders = [];
        foreach (
            [
                ['invoice' => strtoupper(substr($business['key'], 0, 4)) . '-DEMO-001', 'days' => 0, 'method' => 'cash', 'shift' => $openShift, 'customer' => $customers[0], 'product' => $products[0], 'quantity' => 2, 'paid' => true],
                ['invoice' => strtoupper(substr($business['key'], 0, 4)) . '-DEMO-002', 'days' => 2, 'method' => $business['type'] === 'food' ? 'qris' : 'transfer', 'shift' => $closedShift, 'customer' => $customers[1], 'product' => $products[1], 'quantity' => $business['type'] === 'retail' ? 1.5 : 1, 'paid' => true],
                ['invoice' => strtoupper(substr($business['key'], 0, 4)) . '-DEMO-003', 'days' => 1, 'method' => 'credit', 'shift' => $openShift, 'customer' => $customers[1], 'product' => $products[2], 'quantity' => 1, 'paid' => false],
            ] as $index => $sale
        ) {
            $quantity = (float) $sale['quantity'];
            $subtotal = (int) round($sale['product']->sell_price * $quantity);
            $discount = $index === 0 ? min(2000, $subtotal) : 0;
            $grandTotal = $subtotal - $discount;
            $soldAt = now()->subDays($sale['days'])->setTime(10 + $index, 15);
            $order = Order::updateOrCreate(['tenant_id' => $tenant->id, 'invoice_number' => $sale['invoice']], [
                'user_id' => $index === 1 ? $cashier->id : $owner->id,
                'customer_id' => $sale['customer']->id,
                'shift_id' => $sale['shift']->id,
                'order_type' => $business['type'] === 'food' ? 'takeaway' : 'regular',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $sale['paid'] ? $grandTotal : 0,
                'change_amount' => 0,
                'payment_status' => $sale['paid'] ? 'paid' : 'unpaid',
                'payment_method' => $sale['method'],
                'order_status' => 'completed',
                'cash_tracked' => $sale['method'] === 'cash',
                'sold_at' => $soldAt,
                'note' => 'Transaksi demo untuk pengujian.',
                'service_due_at' => $business['type'] === 'service' ? $soldAt->copy()->addDay() : null,
            ]);

            OrderItem::firstOrCreate(
                ['order_id' => $order->id, 'product_id' => $sale['product']->id],
                [
                    'product_name' => $sale['product']->product_name,
                    'quantity' => $quantity,
                    'price' => $sale['product']->sell_price,
                    'subtotal' => $subtotal,
                    'unit_cost' => $sale['product']->cost_price,
                    'unit_name' => $sale['product']->base_unit,
                    'unit_factor' => 1,
                    'allow_fraction' => $sale['product']->allow_fraction,
                    'requires_preparation' => $sale['product']->requires_preparation
                ]
            );

            if ($sale['paid']) {
                $paidOrders[] = $order;
                Payment::updateOrCreate(
                    ['order_id' => $order->id, 'reference_number' => 'DEMO-PAY-' . $order->invoice_number],
                    [
                        'tenant_id' => $tenant->id,
                        'user_id' => $order->user_id,
                        'payment_method' => $sale['method'] === 'qris' ? 'qris' : ($sale['method'] === 'transfer' ? 'transfer' : 'cash'),
                        'amount' => $grandTotal,
                        'paid_at' => $soldAt
                    ]
                );
            }

            if ($business['type'] === 'service') {
                ServiceOrder::updateOrCreate(['tenant_id' => $tenant->id, 'order_id' => $order->id], [
                    'service_status' => $index === 2 ? 'washing' : 'received',
                    'estimated_finish' => $soldAt->copy()->addDay(),
                    'notes' => 'Pesanan demo laundry.',
                ]);
            }
        }

        foreach ($paidOrders as $index => $order) {
            if ($order->payment_method === 'cash') {
                $key = sprintf('30000000-0000-4000-8000-%012d', $tenant->id * 10 + $index + 1);
                CashEntry::updateOrCreate(['tenant_id' => $tenant->id, 'operation_key' => $key], [
                    'shift_id' => $order->shift_id,
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'kind' => 'sale',
                    'amount' => $order->grand_total,
                    'reason' => 'Penjualan tunai demo ' . $order->invoice_number,
                ]);
            }
            if ($order->customer_id && $order->customer->is_member) {
                LoyaltyPointTransaction::updateOrCreate(
                    ['operation_key' => 'demo-earned-' . $order->id],
                    [
                        'tenant_id' => $tenant->id,
                        'customer_id' => $order->customer_id,
                        'order_id' => $order->id,
                        'type' => 'earned',
                        'points_delta' => 10,
                        'amount' => $order->grand_total,
                        'description' => 'Poin dari transaksi demo ' . $order->invoice_number
                    ]
                );
            }
        }

        CashEntry::updateOrCreate(
            ['tenant_id' => $tenant->id, 'operation_key' => sprintf('30000000-0000-4000-8000-%012d', $tenant->id * 10 + 9)],
            [
                'shift_id' => $openShift->id,
                'user_id' => $owner->id,
                'order_id' => null,
                'kind' => 'expense',
                'amount' => -25000,
                'reason' => 'Belanja operasional demo'
            ]
        );

        $discount = Discount::updateOrCreate(['tenant_id' => $tenant->id, 'name' => 'Promo Demo Hemat 10%'], [
            'type' => 'percentage',
            'value' => 10,
            'start_date' => today()->subDay(),
            'end_date' => today()->addDays(7),
            'is_active' => true,
        ]);
        $discount->products()->sync([$products[0]->id, $products[1]->id]);

        $purchase = PurchaseOrder::updateOrCreate(['tenant_id' => $tenant->id, 'number' => strtoupper(substr($business['key'], 0, 4)) . '-PO-DEMO-001'], [
            'supplier_id' => $supplier->id,
            'created_by' => $owner->id,
            'status' => 'ordered',
            'ordered_at' => today()->subDay(),
            'expected_at' => today()->addDays(2),
            'note' => 'Purchase order demo.',
        ]);
        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $purchase->id, 'product_id' => $products[0]->id],
            ['quantity' => 24, 'received_quantity' => 8, 'unit_cost' => $products[0]->cost_price]
        );

        $stocktake = Stocktake::updateOrCreate(['tenant_id' => $tenant->id, 'number' => strtoupper(substr($business['key'], 0, 4)) . '-ST-DEMO-001'], [
            'created_by' => $owner->id,
            'status' => 'open',
            'note' => 'Sesi opname demo. Hitung stok fisik dan simpan untuk mencoba alur rekonsiliasi.',
        ]);
        foreach ($products as $product) {
            if ($product->manage_stock) {
                StocktakeItem::firstOrCreate(
                    ['stocktake_id' => $stocktake->id, 'product_id' => $product->id],
                    ['system_quantity' => $product->stock, 'counted_quantity' => null]
                );
            }
        }

        $this->command?->line('Seeded: ' . $business['label'] . ' — ' . $tenant->slug);
    }
}
