<?php

namespace App\Services;

use App\Models\Order;

/** Compatibility vocabulary; Order.service_status remains the canonical progress. */
class LegacyServiceProgress
{
    public const STATES = ['received' => 'queued', 'washing' => 'working', 'drying' => 'working', 'finished' => 'ready', 'picked_up' => 'completed'];

    public function apply(Order $order, string $legacyStatus): void
    {
        $target = self::STATES[$legacyStatus];
        $current = $order->service_status ?? 'queued';
        $next = ['queued' => 'working', 'working' => 'ready', 'ready' => 'completed'];
        abort_if($order->order_status === 'cancelled', 422, 'Pesanan dibatalkan.');
        abort_unless($target === $current || ($next[$current] ?? null) === $target, 409, 'Perbarui progres jasa secara berurutan.');
        $order->update(['service_status' => $target]);
    }
}
