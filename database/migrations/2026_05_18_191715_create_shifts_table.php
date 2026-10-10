<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Kasir yang bertugas
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->decimal('cash_start', 12, 2); // Modal awal di laci
            $table->decimal('cash_expected', 12, 2)->default(0); // Hitungan sistem (modal + penjualan cash)
            $table->decimal('cash_actual', 12, 2)->nullable(); // Uang fisik yang dihitung manual saat tutup
            $table->decimal('cash_difference', 12, 2)->nullable(); // Selisih (plus/minus)
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Tambahkan kolom shift_id ke tabel orders Anda (Optional tapi sangat direkomendasikan)
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('customer_id')->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn('shift_id');
        });
        Schema::dropIfExists('shifts');
    }
};
