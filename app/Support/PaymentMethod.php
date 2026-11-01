<?php

namespace App\Support;

final class PaymentMethod
{
    public const LABELS = [
        'cash' => 'Tunai',
        'midtrans' => 'QRIS otomatis',
        'transfer' => 'Transfer bank (manual)',
        'qris_manual' => 'QRIS toko (manual)',
    ];

    public static function label(?string $method): string
    {
        return self::LABELS[$method] ?? 'Metode lain';
    }
}
