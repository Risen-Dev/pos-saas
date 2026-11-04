<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'loyalty_program_type')) {
                $table->string('loyalty_program_type', 16)->nullable()->after('loyalty_discount');
            }
            if (! Schema::hasColumn('orders', 'stamp_minimum_purchase')) {
                $table->unsignedBigInteger('stamp_minimum_purchase')->nullable()->after('loyalty_program_type');
            }
            if (! Schema::hasColumn('orders', 'stamps_awarded')) {
                $table->unsignedSmallInteger('stamps_awarded')->default(0)->after('stamp_minimum_purchase');
            }
            if (! Schema::hasColumn('orders', 'stamps_redeemed')) {
                $table->unsignedSmallInteger('stamps_redeemed')->default(0)->after('stamps_awarded');
            }
        });

        if (! Schema::hasTable('loyalty_stamp_transactions')) {
            Schema::create('loyalty_stamp_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
                $table->foreignId('customer_id')->constrained()->restrictOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->restrictOnDelete();
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type', 32);
                $table->integer('stamps_delta');
                $table->unsignedBigInteger('amount')->default(0);
                $table->string('description', 255)->nullable();
                $table->string('operation_key', 191)->unique();
                $table->timestamps();

                $table->index(['tenant_id', 'customer_id', 'created_at'], 'lst_tenant_customer_created_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_stamp_transactions');
        Schema::table('orders', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['loyalty_program_type', 'stamp_minimum_purchase', 'stamps_awarded', 'stamps_redeemed'],
                fn ($column) => Schema::hasColumn('orders', $column),
            ));
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
