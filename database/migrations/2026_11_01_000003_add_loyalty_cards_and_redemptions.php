<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('customers', 'loyalty_card_token') || ! Schema::hasColumn('customers', 'loyalty_member_code')) {
            Schema::table('customers', function (Blueprint $table) {
                if (! Schema::hasColumn('customers', 'loyalty_card_token')) {
                    $table->string('loyalty_card_token', 80)->nullable()->unique();
                }
                if (! Schema::hasColumn('customers', 'loyalty_member_code')) {
                    $table->string('loyalty_member_code', 24)->nullable()->unique();
                }
            });
        }

        if (! Schema::hasColumn('orders', 'points_redeemed') || ! Schema::hasColumn('orders', 'loyalty_discount')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'points_redeemed')) {
                    $table->unsignedInteger('points_redeemed')->default(0);
                }
                if (! Schema::hasColumn('orders', 'loyalty_discount')) {
                    $table->unsignedBigInteger('loyalty_discount')->default(0);
                }
            });
        }

        if (! Schema::hasTable('loyalty_point_transactions')) {
            Schema::create('loyalty_point_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
                $table->foreignId('customer_id')->constrained()->restrictOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->restrictOnDelete();
                $table->string('type', 32);
                $table->integer('points_delta');
                $table->unsignedBigInteger('amount')->default(0);
                $table->string('description', 255)->nullable();
                $table->string('operation_key', 191)->unique();
                $table->timestamps();

                $table->index(['tenant_id', 'customer_id', 'created_at'], 'lpt_tenant_customer_created_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_point_transactions');
        foreach (['orders' => ['points_redeemed', 'loyalty_discount'], 'customers' => ['loyalty_card_token', 'loyalty_member_code']] as $tableName => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn($column));
                }
            }
        }
    }
};
