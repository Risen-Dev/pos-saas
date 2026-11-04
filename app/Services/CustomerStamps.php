<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyStampTransaction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerStamps
{
    public function balance(Customer $customer): int
    {
        return (int) LoyaltyStampTransaction::withoutGlobalScopes()
            ->where('tenant_id', $customer->tenant_id)
            ->where('customer_id', $customer->id)
            ->sum('stamps_delta');
    }

    public function redemptionDiscount(Customer $customer, int $payable, array $settings): int
    {
        if (! $customer->is_member || ! $this->planAllowsLoyalty($customer->tenant)
            || app(LoyaltyProgram::class)->type($settings) !== 'stamps') {
            throw ValidationException::withMessages(['redeem_stamp_reward' => 'Program stamp hanya dapat digunakan oleh member pada paket Growth atau Scale.']);
        }

        $goal = max(2, (int) ($settings['stamp_reward_goal'] ?? 8));
        if ($this->balance($customer) < $goal) {
            throw ValidationException::withMessages(['redeem_stamp_reward' => 'Stamp pelanggan belum cukup untuk ditukar.']);
        }

        $reward = max(1, (int) ($settings['stamp_reward_value'] ?? 10000));
        if ($reward >= $payable) {
            throw ValidationException::withMessages(['redeem_stamp_reward' => 'Sisakan sedikitnya Rp1 untuk dibayar setelah penukaran reward.']);
        }

        return $reward;
    }

    /** Reserve the reward at checkout; payment cancellation restores it idempotently. */
    public function redeemForOrder(Order $order): void
    {
        if (! $order->customer_id || $order->stamps_redeemed < 1) {
            return;
        }

        $operationKey = 'redeem-stamps:order:'.$order->id;
        if (LoyaltyStampTransaction::withoutGlobalScopes()->where('operation_key', $operationKey)->exists()) {
            return;
        }

        $customer = Customer::withoutGlobalScopes()
            ->where('tenant_id', $order->tenant_id)
            ->lockForUpdate()
            ->findOrFail($order->customer_id);
        if (! $customer->is_member || $this->balance($customer) < $order->stamps_redeemed) {
            throw ValidationException::withMessages(['redeem_stamp_reward' => 'Saldo stamp berubah. Periksa kembali sebelum melanjutkan transaksi.']);
        }

        LoyaltyStampTransaction::create([
            'tenant_id' => $order->tenant_id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'actor_user_id' => $order->user_id,
            'type' => 'redeemed',
            'stamps_delta' => -$order->stamps_redeemed,
            'amount' => $order->loyalty_discount,
            'description' => 'Reward stamp digunakan pada transaksi '.$order->invoice_number,
            'operation_key' => $operationKey,
        ]);
    }

    /** Caller holds the order lock and invokes this only on a paid transition. */
    public function award(Order $order): int
    {
        if (! $order->customer_id || $order->stamps_awarded > 0
            || $order->loyalty_program_type !== 'stamps'
            || ! $this->planAllowsLoyalty($order->tenant)) {
            return 0;
        }

        $customer = Customer::withoutGlobalScopes()
            ->where('tenant_id', $order->tenant_id)
            ->lockForUpdate()
            ->find($order->customer_id);
        if (! $customer || ! $customer->is_member) {
            return 0;
        }

        $qualifyingAmount = max(0, (int) round($order->subtotal - $order->discount - $order->loyalty_discount));
        $minimum = (int) ($order->stamp_minimum_purchase ?? 0);
        if ($qualifyingAmount < $minimum) {
            return 0;
        }

        $operationKey = 'earn-stamp:order:'.$order->id;
        if (LoyaltyStampTransaction::withoutGlobalScopes()->where('operation_key', $operationKey)->exists()) {
            return 0;
        }

        LoyaltyStampTransaction::create([
            'tenant_id' => $order->tenant_id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'actor_user_id' => $order->user_id,
            'type' => 'earned',
            'stamps_delta' => 1,
            'amount' => $qualifyingAmount,
            'description' => '1 stamp dari transaksi '.$order->invoice_number,
            'operation_key' => $operationKey,
        ]);
        $order->update(['stamps_awarded' => 1]);

        return 1;
    }

    public function releaseRedemption(Order $order, string $reason): void
    {
        if (! $order->customer_id || $order->stamps_redeemed < 1) {
            return;
        }

        $operationKey = 'release-stamps:order:'.$order->id;
        if (LoyaltyStampTransaction::withoutGlobalScopes()->where('operation_key', $operationKey)->exists()) {
            return;
        }

        LoyaltyStampTransaction::create([
            'tenant_id' => $order->tenant_id,
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'type' => 'redemption_reversed',
            'stamps_delta' => $order->stamps_redeemed,
            'amount' => $order->loyalty_discount,
            'description' => $reason,
            'operation_key' => $operationKey,
        ]);
    }

    /** Reverse earned stamps and restore a redeemed reward only after a full refund. */
    public function reverseForReturn(Order $order, int $refundAmount, int $priorRefund, int $returnId): void
    {
        if (! $order->customer_id || $order->grand_total < 1 || $priorRefund + $refundAmount < $order->grand_total) {
            return;
        }

        if ($order->stamps_awarded > 0) {
            $operationKey = 'return-earned-stamps:'.$returnId;
            if (! LoyaltyStampTransaction::withoutGlobalScopes()->where('operation_key', $operationKey)->exists()) {
                LoyaltyStampTransaction::create([
                    'tenant_id' => $order->tenant_id,
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'actor_user_id' => auth()->id(),
                    'type' => 'return_reversal',
                    'stamps_delta' => -$order->stamps_awarded,
                    'amount' => $refundAmount,
                    'description' => 'Stamp disesuaikan karena transaksi diretur penuh '.$order->invoice_number,
                    'operation_key' => $operationKey,
                ]);
            }
        }

        if ($order->stamps_redeemed > 0) {
            $operationKey = 'return-redeemed-stamps:'.$returnId;
            if (! LoyaltyStampTransaction::withoutGlobalScopes()->where('operation_key', $operationKey)->exists()) {
                LoyaltyStampTransaction::create([
                    'tenant_id' => $order->tenant_id,
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'actor_user_id' => auth()->id(),
                    'type' => 'redemption_restored',
                    'stamps_delta' => $order->stamps_redeemed,
                    'amount' => $refundAmount,
                    'description' => 'Stamp reward dikembalikan karena transaksi diretur penuh '.$order->invoice_number,
                    'operation_key' => $operationKey,
                ]);
            }
        }
    }

    private function planAllowsLoyalty($tenant): bool
    {
        return in_array($tenant?->currentPlan()?->slug, ['growth', 'scale'], true);
    }
}
