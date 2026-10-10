<?php

namespace App\Services;

use App\Models\Product;

class OrderPricing
{
    /** Tiers apply per cart line, in base selling units, before promotions. */
    public function retailPrice(Product $product, int|float $quantity): int
    {
        $tier = collect($product->price_tiers ?? [])
            ->filter(function ($tier) use ($quantity) {
                if (
                    ! is_array($tier) ||
                    ! isset($tier['min_quantity'], $tier['price']) ||
                    ! is_numeric($tier['min_quantity']) ||
                    ! is_numeric($tier['price'])
                ) {
                    return false;
                }

                $minQuantity = (float) $tier['min_quantity'];
                $tierPrice = (float) $tier['price'];

                if ($minQuantity <= 0 || $tierPrice < 0) {
                    return false;
                }

                return RetailQuantity::ticks($quantity) >=
                    RetailQuantity::ticks($minQuantity);
            })
            ->sortByDesc(fn ($tier) => (float) $tier['min_quantity'])
            ->first();

        return (int) round($tier['price'] ?? $product->sell_price);
    }

    // POS uses whole rupiah. Round once per unit and once for tax.
    public function unitPrice(Product $product, ?float $override = null): array
    {
        $price = (int) round($override ?? $product->sell_price);
        $discount = $product->discounts->sortBy('id')->first(fn ($discount) => $discount->isValidNow());
        $amount = $discount ? ($discount->type === 'percentage' ? $price * $discount->value / 100 : $discount->value) : 0;
        $amount = min($price, max(0, (int) round($amount)));

        return ['price' => $price, 'discount' => $amount, 'final' => $price - $amount, 'discount_name' => $discount?->name];
    }
}
