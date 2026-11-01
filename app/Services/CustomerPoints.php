<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerPoints
{
    public function settings(int $tenantId): array
    {
        return Setting::withoutGlobalScopes()->where('tenant_id', $tenantId)->pluck('value', 'key')->all();
    }

    // Caller holds the order lock and calls this only on the first paid transition.
    public function award(Order $order): int
    {
        if (! $order->customer_id || $order->points_awarded > 0 || ! $this->planAllowsLoyalty($order->tenant)) {
            return 0;
        }

        $customer = Customer::withoutGlobalScopes()
            ->where('tenant_id', $order->tenant_id)
            ->lockForUpdate()
            ->find($order->customer_id);
        if (! $customer || ! $customer->is_member) {
            return 0;
        }

        $settings = $this->settings($order->tenant_id);
        if (($settings['point_mode'] ?? 'disabled') === 'disabled') {
            return 0;
        }

        $rule = (float) ($settings['point_rule_value'] ?? 0);
        if ($rule <= 0) {
            return 0;
        }

        $points = (int) match ($settings['point_mode'] ?? 'disabled') {
            'per_investment' => floor($order->grand_total / $rule),
            'flat' => floor($rule),
            'percentage' => floor($order->grand_total * $rule / 100),
            default => 0,
        };

        if ($points < 1) {
            return 0;
        }

        $operationKey = 'earn:order:'.$order->id;
        if (LoyaltyPointTransaction::withoutGlobalScopes()->where('operation_key', $operationKey)->exists()) {
            return 0;
        }

        $customer->increment('points', $points);
        $order->update(['points_awarded' => $points]);
        LoyaltyPointTransaction::create([
            'tenant_id' => $order->tenant_id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'type' => 'earned',
            'points_delta' => $points,
            'amount' => $order->grand_total,
            'description' => 'Poin dari transaksi '.$order->invoice_number,
            'operation_key' => $operationKey,
        ]);

        return $points;
    }

    public function redemptionDiscount(Customer $customer, int $points, int $payable, array $settings): int
    {
        if ($points === 0) {
            return 0;
        }

        if (! $customer->is_member || ! $this->planAllowsLoyalty($customer->tenant)) {
            throw ValidationException::withMessages(['points_to_redeem' => 'Poin hanya dapat digunakan oleh member pada paket Growth atau Scale.']);
        }
        if (($settings['point_mode'] ?? 'disabled') === 'disabled' || (float) ($settings['point_rule_value'] ?? 0) <= 0) {
            throw ValidationException::withMessages(['points_to_redeem' => 'Program poin belum diaktifkan untuk toko ini.']);
        }

        $step = max(1, (int) ($settings['point_redemption_points'] ?? 100));
        $value = max(1, (int) ($settings['point_redemption_value'] ?? 10000));
        if ($points < $step || $points % $step !== 0) {
            throw ValidationException::withMessages(['points_to_redeem' => "Penukaran berlaku per {$step} poin."]);
        }
        if ($points > $customer->points) {
            throw ValidationException::withMessages(['points_to_redeem' => 'Saldo poin pelanggan tidak mencukupi.']);
        }

        $discount = (int) ($points / $step) * $value;
        if ($discount >= $payable) {
            throw ValidationException::withMessages(['points_to_redeem' => 'Sisakan sedikitnya Rp1 untuk dibayar setelah penukaran poin.']);
        }

        return $discount;
    }

    public function redeemForOrder(Order $order): void
    {
        if (! $order->customer_id || $order->points_redeemed < 1) {
            return;
        }

        $operationKey = 'redeem:order:'.$order->id;
        if (LoyaltyPointTransaction::withoutGlobalScopes()->where('operation_key', $operationKey)->exists()) {
            return;
        }

        $customer = Customer::withoutGlobalScopes()->where('tenant_id', $order->tenant_id)->lockForUpdate()->findOrFail($order->customer_id);
        if (! $customer->is_member || $customer->points < $order->points_redeemed) {
            throw ValidationException::withMessages(['points_to_redeem' => 'Saldo poin berubah. Periksa kembali sebelum melanjutkan transaksi.']);
        }

        $customer->decrement('points', $order->points_redeemed);
        LoyaltyPointTransaction::create([
            'tenant_id' => $order->tenant_id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'type' => 'redeemed',
            'points_delta' => -$order->points_redeemed,
            'amount' => $order->loyalty_discount,
            'description' => 'Poin digunakan pada transaksi '.$order->invoice_number,
            'operation_key' => $operationKey,
        ]);
    }

    public function releaseRedemption(Order $order, string $reason): void
    {
        if (! $order->customer_id || $order->points_redeemed < 1) {
            return;
        }

        $operationKey = 'release:order:'.$order->id;
        if (LoyaltyPointTransaction::withoutGlobalScopes()->where('operation_key', $operationKey)->exists()) {
            return;
        }

        $customer = Customer::withoutGlobalScopes()->where('tenant_id', $order->tenant_id)->lockForUpdate()->findOrFail($order->customer_id);
        $customer->increment('points', $order->points_redeemed);
        LoyaltyPointTransaction::create([
            'tenant_id' => $order->tenant_id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'type' => 'redemption_reversed',
            'points_delta' => $order->points_redeemed,
            'amount' => $order->loyalty_discount,
            'description' => $reason,
            'operation_key' => $operationKey,
        ]);
    }

    public function reverseForReturn(Order $order, int $refundAmount, int $priorRefund, int $returnId): void
    {
        if (! $order->customer_id || $order->grand_total < 1) {
            return;
        }

        $customer = Customer::withoutGlobalScopes()->where('tenant_id', $order->tenant_id)->lockForUpdate()->findOrFail($order->customer_id);
        $cumulativeRefund = min($order->grand_total, $priorRefund + $refundAmount);
        $earnedToReverse = (int) floor($order->points_awarded * $cumulativeRefund / $order->grand_total)
            - (int) floor($order->points_awarded * $priorRefund / $order->grand_total);

        if ($earnedToReverse > 0) {
            $actualReversal = min($customer->points, $earnedToReverse);
            if ($actualReversal > 0) {
                $customer->decrement('points', $actualReversal);
            }
            LoyaltyPointTransaction::create([
                'tenant_id' => $order->tenant_id,
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'type' => 'return_reversal',
                'points_delta' => -$actualReversal,
                'amount' => $refundAmount,
                'description' => 'Koreksi poin karena retur transaksi '.$order->invoice_number,
                'operation_key' => 'return-earned:'.$returnId,
            ]);
        }

        $redeemedToRestore = (int) floor($order->points_redeemed * $cumulativeRefund / $order->grand_total)
            - (int) floor($order->points_redeemed * $priorRefund / $order->grand_total);
        if ($redeemedToRestore > 0) {
            $customer->increment('points', $redeemedToRestore);
            LoyaltyPointTransaction::create([
                'tenant_id' => $order->tenant_id,
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'type' => 'redemption_restored',
                'points_delta' => $redeemedToRestore,
                'amount' => $refundAmount,
                'description' => 'Poin penukaran dikembalikan karena retur '.$order->invoice_number,
                'operation_key' => 'return-redeemed:'.$returnId,
            ]);
        }
    }

    private function planAllowsLoyalty($tenant): bool
    {
        return in_array($tenant?->currentPlan()?->slug, ['growth', 'scale'], true);
    }
}
