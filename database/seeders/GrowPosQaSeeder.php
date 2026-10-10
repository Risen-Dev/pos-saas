<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GrowPosQaSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('GrowPosQaSeeder hanya boleh dijalankan pada environment local/testing.');
        }

        $password = env('GROWPOS_QA_PASSWORD', 'GrowPOS-QA-2026!');

        $this->call(PlanSeeder::class);
        $growth = Plan::where('slug', 'growth')->firstOrFail();

        $tenantA = $this->seedTenant(
            ownerEmail: 'qa.owner@growpos.test',
            ownerName: 'Owner QA GrowPOS',
            cashierEmail: 'qa.cashier@growpos.test',
            cashierName: 'Kasir QA GrowPOS',
            tenantName: 'Toko Maju Jaya QA',
            tenantSlug: 'toko-maju-jaya-qa',
            phone: '081200000001',
            password: $password,
            plan: $growth,
        );

        $this->seedCatalog($tenantA);

        // Tenant kedua sengaja dibuat dengan katalog berbeda untuk QA tenant isolation.
        $this->seedTenant(
            ownerEmail: 'qa.owner.b@growpos.test',
            ownerName: 'Owner Tenant B QA',
            cashierEmail: 'qa.cashier.b@growpos.test',
            cashierName: 'Kasir Tenant B QA',
            tenantName: 'Toko Sejahtera QA',
            tenantSlug: 'toko-sejahtera-qa',
            phone: '081200000002',
            password: $password,
            plan: $growth,
            seedIsolationProduct: true,
        );

        $this->command?->newLine();
        $this->command?->info('GrowPOS QA dataset siap.');
        $this->command?->line('Tenant A owner : qa.owner@growpos.test');
        $this->command?->line('Tenant A kasir : qa.cashier@growpos.test');
        $this->command?->line('Tenant B owner : qa.owner.b@growpos.test');
        $this->command?->line('Tenant B kasir : qa.cashier.b@growpos.test');
        $this->command?->line('Password        : '.(env('GROWPOS_QA_PASSWORD') ? '[GROWPOS_QA_PASSWORD]' : 'GrowPOS-QA-2026!'));
    }

    private function seedTenant(
        string $ownerEmail,
        string $ownerName,
        string $cashierEmail,
        string $cashierName,
        string $tenantName,
        string $tenantSlug,
        string $phone,
        string $password,
        Plan $plan,
        bool $seedIsolationProduct = false,
    ): Tenant {
        $owner = User::updateOrCreate(
            ['email' => $ownerEmail],
            [
                'name' => $ownerName,
                'password' => Hash::make($password),
                'status' => 'active',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $tenant = Tenant::updateOrCreate(
            ['slug' => $tenantSlug],
            [
                'user_id' => $owner->id,
                'name' => $tenantName,
                'business_type' => 'mixed',
                'business_modules' => ['goods', 'services'],
                'email' => $ownerEmail,
                'phone' => $phone,
                'address' => 'Alamat khusus QA GrowPOS',
                'status' => 'active',
            ]
        );

        $owner->update(['tenant_id' => $tenant->id]);

        User::updateOrCreate(
            ['email' => $cashierEmail],
            [
                'tenant_id' => $tenant->id,
                'name' => $cashierName,
                'password' => Hash::make($password),
                'status' => 'active',
                'role' => 'kasir',
                'email_verified_at' => now(),
            ]
        );

        Subscription::updateOrCreate(
            ['tenant_id' => $tenant->id, 'plan_id' => $plan->id],
            [
                'start_date' => now()->startOfDay(),
                'end_date' => now()->addYear()->startOfDay(),
                'status' => 'active',
            ]
        );

        if ($seedIsolationProduct) {
            $category = Category::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'QA Tenant B'],
                ['slug' => 'qa-tenant-b-'.$tenant->id]
            );

            Product::updateOrCreate(
                ['tenant_id' => $tenant->id, 'sku' => 'QA-B-ONLY-001'],
                [
                    'category_id' => $category->id,
                    'barcode' => '8990000009001',
                    'product_name' => 'Produk Khusus Tenant B',
                    'type' => 'product',
                    'cost_price' => 5000,
                    'sell_price' => 10000,
                    'stock' => 50,
                    'manage_stock' => true,
                    'min_stock' => 5,
                    'base_unit' => 'pcs',
                    'allow_fraction' => false,
                    'price_tiers' => null,
                    'desc' => 'Produk ini tidak boleh terlihat dari Tenant A.',
                    'is_active' => true,
                ]
            );
        }

        return $tenant;
    }

    private function seedCatalog(Tenant $tenant): void
    {
        $goods = Category::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Barang QA'],
            ['slug' => 'barang-qa-'.$tenant->id]
        );
        $grocery = Category::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Sembako QA'],
            ['slug' => 'sembako-qa-'.$tenant->id]
        );
        $services = Category::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Jasa QA'],
            ['slug' => 'jasa-qa-'.$tenant->id]
        );

        $water = Product::updateOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'QA-AIR-001'],
            [
                'category_id' => $goods->id,
                'barcode' => '8990000001005',
                'product_name' => 'Air Mineral QA',
                'type' => 'product',
                'cost_price' => 3500,
                'sell_price' => 5000,
                'stock' => 100,
                'manage_stock' => true,
                'min_stock' => 12,
                'base_unit' => 'pcs',
                'allow_fraction' => false,
                'price_tiers' => null,
                'desc' => 'QA multi-unit: pcs, Pak 6 pcs, Dus 24 pcs.',
                'is_active' => true,
            ]
        );
        $water->units()->updateOrCreate(['name' => 'Pak'], ['factor' => 6, 'price' => 28000]);
        $water->units()->updateOrCreate(['name' => 'Dus'], ['factor' => 24, 'price' => 105000]);

        Product::updateOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'QA-BERAS-001'],
            [
                'category_id' => $grocery->id,
                'barcode' => '8990000002002',
                'product_name' => 'Beras Premium QA',
                'type' => 'product',
                'cost_price' => 12000,
                'sell_price' => 15000,
                'stock' => 20,
                'manage_stock' => true,
                'min_stock' => 5,
                'base_unit' => 'kg',
                'allow_fraction' => true,
                'price_tiers' => null,
                'desc' => 'QA fractional quantity. Contoh transaksi: 1.5 kg.',
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'QA-KOPI-001'],
            [
                'category_id' => $goods->id,
                'barcode' => '8990000003009',
                'product_name' => 'Kopi Sachet QA',
                'type' => 'product',
                'cost_price' => 1800,
                'sell_price' => 2500,
                'stock' => 200,
                'manage_stock' => true,
                'min_stock' => 20,
                'base_unit' => 'pcs',
                'allow_fraction' => false,
                'price_tiers' => [
                    ['min_quantity' => 10, 'price' => 2300],
                    ['min_quantity' => 50, 'price' => 2100],
                ],
                'desc' => 'QA tier harga: 1-9=2500, 10-49=2300, >=50=2100.',
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'QA-JASA-001'],
            [
                'category_id' => $services->id,
                'barcode' => null,
                'product_name' => 'Jasa Cuci Sepatu QA',
                'type' => 'service',
                'cost_price' => 10000,
                'sell_price' => 35000,
                'stock' => 0,
                'manage_stock' => false,
                'min_stock' => 0,
                'base_unit' => 'layanan',
                'allow_fraction' => false,
                'price_tiers' => null,
                'desc' => 'QA item jasa; tidak boleh mengurangi stok.',
                'is_active' => true,
            ]
        );

        Customer::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Customer Testing QA'],
            [
                'phone' => '081299999999',
                'points' => 100,
                'is_member' => true,
                'total_debt' => 0,
            ]
        );
    }
}
