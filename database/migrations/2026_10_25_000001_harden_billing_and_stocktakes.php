<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dateTime('due_date')->nullable()->change();
            $table->timestamp('activation_applied_at')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
        });
        Schema::table('stocktake_items', function (Blueprint $table) {
            // Legacy open counts must be recounted if any prior movement exists.
            $table->unsignedBigInteger('movement_id')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', fn (Blueprint $table) => $table->dropColumn(['activation_applied_at', 'duration_days']));
        Schema::table('stocktake_items', fn (Blueprint $table) => $table->dropColumn('movement_id'));
    }
};
