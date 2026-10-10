<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Manual inventory changes use the same stock columns as checkout. */
class StockAdjustment
{
    public function product(User $actor, array $data): StockMovement
    {
        return $this->adjust($actor, Product::class, 'product_id', $data);
    }

    public function material(User $actor, array $data): StockMovement
    {
        return $this->adjust($actor, Material::class, 'material_id', $data);
    }

    private function adjust(User $actor, string $model, string $key, array $data): StockMovement
    {
        abort_unless(in_array($actor->role, ['admin', 'manager'], true), 403);

        return DB::transaction(function () use ($actor, $model, $key, $data) {
            // Match checkout's lock order: tenant, then inventory row.
            Tenant::whereKey($actor->tenant_id)->lockForUpdate()->firstOrFail();
            $item = $model::where('tenant_id', $actor->tenant_id)->whereKey($data[$key])->lockForUpdate()->firstOrFail();
            if ($item instanceof Product && ($item->type !== 'product' || ! $item->manage_stock)) {
                throw ValidationException::withMessages([$key => 'Aktifkan pelacakan stok barang terlebih dahulu.']);
            }
            $productId = $item instanceof Product ? $item->id : null;
            if ($item instanceof Product) {
                RetailQuantity::requireWhole($data['quantity'], $item->allow_fraction);
            }
            if ($item instanceof Product && ! empty($data['variant_id'])) {
                $item = $item->variants()->whereKey($data['variant_id'])->lockForUpdate()->firstOrFail();
            }
            $quantity = (float) $data['quantity'];
            if ($data['type'] !== 'adjustment' && $quantity == 0) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah masuk/keluar harus lebih dari nol.']);
            }
            $before = (float) $item->stock;
            $after = match ($data['type']) {
                'stock_in' => $before + $quantity,
                'stock_out' => $before - $quantity,
                'adjustment' => $quantity,
            };
            if ($after < 0 || $after > 1000000000) {
                throw ValidationException::withMessages(['quantity' => 'Stok akhir harus antara 0 dan 1.000.000.000.']);
            }
            $item->update(['stock' => $after]);
            $movement = StockMovement::create([
                'tenant_id' => $actor->tenant_id,
                $key => $productId ?? $item->id,
                'reference_type' => ! empty($data['variant_id']) ? 'product_variant' : null,
                'reference_id' => $data['variant_id'] ?? null,
                'user_id' => $actor->id,
                'type' => $data['type'],
                'quantity' => abs($after - $before),
                'before_stock' => $before,
                'after_stock' => $after,
                'note' => $data['note'],
                'supplier_id' => $data['supplier_id'] ?? null,
                'purchase_price' => $data['purchase_price'] ?? 0,
            ]);
            Cache::forget("tenant_{$actor->tenant_id}_products_pos");

            return $movement;
        }, 3);
    }
}
