<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An existing production DB already ran this historical migration.
        // Keep the table and let the upgrade migration add missing columns.
        if (Schema::hasTable('plans')) {
            return;
        }

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Untuk URL/Identifier paket (misal: 'basic', 'pro')
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->integer('duration_days')->default(30); // Durasi dalam hari (misal: 30, 365)
            $table->integer('max_users')->default(1); // Batas jumlah karyawan/kasir
            $table->integer('max_products')->default(100); // Batas jumlah produk/SKU
            $table->integer('max_outlets')->default(1); // Batas jumlah outlet/toko
            $table->json('features')->nullable(); // List fitur dalam JSON
            $table->boolean('is_active')->default(true); // Status aktif paket
            $table->boolean('is_public')->default(true); // Tampil di halaman pricing publik?
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive: old production data must not be dropped.
    }
};
