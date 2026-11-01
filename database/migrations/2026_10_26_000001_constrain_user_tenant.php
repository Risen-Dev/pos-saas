<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('users')->leftJoin('tenants', 'users.tenant_id', '=', 'tenants.id')
            ->whereNotNull('users.tenant_id')->whereNull('tenants.id')->exists()) {
            throw new RuntimeException('Terdapat akun dengan tenant yang tidak tersedia. Rekonsiliasi tenant_id sebelum menjalankan migrasi; data tidak diubah.');
        }
        Schema::table('users', fn (Blueprint $table) => $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete());
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropForeign(['tenant_id']));
    }
};
