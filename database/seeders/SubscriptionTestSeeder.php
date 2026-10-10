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
use Illuminate\Support\Str;

class SubscriptionTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Dapatkan Plan Berdasarkan Slug / Nama
        $starterPlan = Plan::where('slug', 'starter')->first();
        $growthPlan = Plan::where('slug', 'growth')->first();
        $scalePlan = Plan::where('slug', 'scale')->first();

        // Fallback jika database plan belum di-seed
        if (! $starterPlan || ! $growthPlan || ! $scalePlan) {
            $this->command->error('Data Plans belum ada! Silakan jalankan seeder Plan terlebih dahulu.');

            return;
        }

        // ==========================================
        // AKUN 1: STARTER PLAN (GRATIS & TERBATAS)
        // ==========================================
        $userStarter = User::create([
            'tenant_id' => null,
            'name' => 'Pemilik Starter',
            'email' => 'starter@growpos.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $tenantStarter = Tenant::create([
            'user_id' => $userStarter->id,
            'name' => 'Toko Kelontong Starter',
            'slug' => Str::slug('Toko Kelontong Starter').'-'.Str::random(4),
            'email' => 'starter.store@growpos.test',
            'business_type' => 'Retail & Toko Kelontong',
            'phone' => '081111111111',
            'address' => 'Jl. Starter No. 1, Jakarta',
        ]);

        $userStarter->update(['tenant_id' => $tenantStarter->id]);

        Subscription::create([
            'tenant_id' => $tenantStarter->id,
            'plan_id' => $starterPlan->id,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
        ]);

        $this->seedTenantData($tenantStarter->id, 'Kategori Starter', 'Produk Starter', 5);

        // ==========================================
        // AKUN 2: GROWTH PLAN (FITUR QRIS & CRM)
        // ==========================================
        $userGrowth = User::create([
            'tenant_id' => null,
            'name' => 'Pemilik Growth',
            'email' => 'growth@growpos.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $tenantGrowth = Tenant::create([
            'user_id' => $userGrowth->id,
            'name' => 'Kafe Kopi Growth',
            'slug' => Str::slug('Kafe Kopi Growth').'-'.Str::random(4),
            'email' => 'growth.store@growpos.test',
            'business_type' => 'F&B Coffee Shop',
            'phone' => '082222222222',
            'address' => 'Jl. Growth No. 88, Bandung',
        ]);

        $userGrowth->update(['tenant_id' => $tenantGrowth->id]);

        Subscription::create([
            'tenant_id' => $tenantGrowth->id,
            'plan_id' => $growthPlan->id,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
        ]);

        $this->seedTenantData($tenantGrowth->id, 'Kategori Growth', 'Espresso Base', 10);

        Customer::create([
            'tenant_id' => $tenantGrowth->id,
            'name' => 'Budi Member Growth',
            'phone' => '081234567890',
            'is_member' => 1,
            'points' => 150,
        ]);

        // ==========================================
        // AKUN 3: SCALE PLAN (UNLIMITED & AI ANALYTICS)
        // ==========================================
        $userScale = User::create([
            'tenant_id' => null,
            'name' => 'Pemilik Scale',
            'email' => 'scale@growpos.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $tenantScale = Tenant::create([
            'user_id' => $userScale->id,
            'name' => 'Resto Steak Scale Utama',
            'slug' => Str::slug('Resto Steak Scale Utama').'-'.Str::random(4),
            'email' => 'scale.store@growpos.test',
            'business_type' => 'Restoran & Franchise Multi-Outlet',
            'phone' => '083333333333',
            'address' => 'Jl. Scale Raya No. 99, Surabaya',
        ]);

        $userScale->update(['tenant_id' => $tenantScale->id]);

        Subscription::create([
            'tenant_id' => $tenantScale->id,
            'plan_id' => $scalePlan->id,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
        ]);

        $this->seedTenantData($tenantScale->id, 'Kategori Scale', 'Sirloin Premium', 15);

        $this->command->info('Seeder Uji Coba Paket Langganan Berhasil Dijalankan!');
    }

    /**
     * Helper privat untuk membuat produk awal tiap tenant
     */
    private function seedTenantData(int $tenantId, string $categoryName, string $productNamePrefix, int $count): void
    {
        $category = Category::create([
            'tenant_id' => $tenantId,
            'name' => $categoryName,
            'slug' => Str::slug($categoryName).'-'.$tenantId,
        ]);

        for ($i = 1; $i <= $count; $i++) {
            Product::create([
                'tenant_id' => $tenantId,
                'category_id' => $category->id,
                'product_name' => "{$productNamePrefix} #{$i}",
                'sku' => 'SKU-'.$tenantId.'-'.str_pad($i, 3, '0', STR_PAD_LEFT),
                'cost_price' => 10000 * $i,
                'sell_price' => 15000 * $i,
                'stock' => 50,
            ]);
        }
    }
}
