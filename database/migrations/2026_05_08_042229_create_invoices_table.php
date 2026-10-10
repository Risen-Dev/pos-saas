<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable();
            $table->foreignId('subscription_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('invoice_number')->unique();
            $table->decimal('amount', 15, 2);

            $table->enum('status', [
                'pending',
                'paid',
                'expired',
                'cancelled',
            ])->default('pending');

            $table->string('snap_token')->nullable();
            $table->string('payment_method')->nullable();

            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
