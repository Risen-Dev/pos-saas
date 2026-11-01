<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SalesDataset
{
    public function orders(int $tenantId): Builder
    {
        if ($tenantId < 1) {
            throw new \InvalidArgumentException('Tenant wajib ditentukan untuk laporan.');
        }

        return Order::withoutGlobalScopes()->where('tenant_id', $tenantId)
            ->where('payment_status', 'paid')->where('order_status', 'completed');
    }

    public function between(int $tenantId, mixed $start, mixed $end): Builder
    {
        return $this->orders($tenantId)->whereBetween(DB::raw('COALESCE(sold_at, created_at)'), [$start, $end]);
    }
}
