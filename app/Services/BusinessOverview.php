<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Carbon\CarbonInterface;

final class BusinessOverview
{
    public function forTenant(int $tenantId, CarbonInterface $start, CarbonInterface $end): array
    {
        $manual = Order::withoutGlobalScopes()->where('tenant_id', $tenantId)
            ->whereIn('payment_method', ['transfer', 'qris_manual'])->where('payment_status', 'paid')
            ->whereBetween('payment_verified_at', [$start, $end])->sum('grand_total');

        return [
            'manual_receipts' => (float) $manual,
            'debt' => (float) Customer::withoutGlobalScopes()->where('tenant_id', $tenantId)->sum('total_debt'),
        ];
    }
}
