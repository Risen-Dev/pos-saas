<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Utama Diskon
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id'); // Multi-tenant support
            $table->string('name'); // Contoh: "Morning Discount", "Promo Imlek"
            $table->enum('type', ['percentage', 'fixed']); // Persen atau Rupiah
            $table->decimal('value', 15, 2); // Nilai diskon (misal: 10 untuk 10%, atau 15000 untuk Rp15.000)

            // Validasi Periode Waktu
            $table->date('start_date')->nullable(); // Tanggal mulai (jika event musiman)
            $table->date('end_date')->nullable();   // Tanggal selesai
            $table->time('start_time')->nullable(); // Jam mulai (misal: 08:00:00)
            $table->time('end_time')->nullable();   // Jam selesai (misal: 11:00:00)
            $table->string('days')->nullable();     // Hari aktif (JSON array atau comma-separated, misal: ["Monday", "Tuesday"])

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabel Pivot Hubungan Diskon ke Produk (Menu Tertentu)
        Schema::create('discount_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_product');
        Schema::dropIfExists('discounts');
    }
};
