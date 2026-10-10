<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Paket Starter (Gratis / UMKM Baru)
        Plan::updateOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'Cocok untuk pedagang kaki lima & UMKM baru.',
                'price' => 0,
                'duration_days' => 30,
                'max_users' => 1,
                'max_products' => 100,
                'max_outlets' => 1,
                'features' => [
                    '100 Transaksi / Bulan' => true,
                    'Manajemen Stok Dasar' => true,
                    'Laporan Harian' => true,
                    'Analitik AI' => false,
                ],
                'is_active' => true,
                'is_public' => true,
            ]
        );

        // 2. Paket Growth (Paling Populer)
        Plan::updateOrCreate(
            ['slug' => 'growth'],
            [
                'name' => 'Growth',
                'description' => 'Untuk toko yang mulai berkembang pesat.',
                'price' => 149000,
                'duration_days' => 30,
                'max_users' => 5,
                'max_products' => 1000,
                'max_outlets' => 2,
                'features' => [
                    'Transaksi Tanpa Batas' => true,
                    'Manajemen Stok Lanjut' => true,
                    'CRM & Loyalitas' => true,
                    'Panduan Operasional' => true,
                ],
                'is_active' => true,
                'is_public' => true,
            ]
        );

        // 3. Paket Scale (Multi-Cabang / Enterprise)
        Plan::updateOrCreate(
            ['slug' => 'scale'],
            [
                'name' => 'Scale',
                'description' => 'Solusi perusahaan untuk bisnis multi-cabang.',
                'price' => 499000,
                'duration_days' => 30,
                'max_users' => 20,
                'max_products' => 5000,
                'max_outlets' => 10,
                'features' => [
                    'Hingga 10 Outlet' => true,
                    'Analitik AI Eksklusif' => true,
                    'Ekspor Laporan' => true,
                    'Manajemen Kas dan Shift' => true,
                ],
                'is_active' => true,
                'is_public' => true,
            ]
        );
    }
}
