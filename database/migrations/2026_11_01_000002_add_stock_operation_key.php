<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->uuid('operation_key')->nullable();
            $table->string('request_hash', 64)->nullable();
            $table->unique(['tenant_id', 'operation_key']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'operation_key']);
            $table->dropColumn(['operation_key', 'request_hash']);
        });
    }
};
