<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\CashEntry;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderReturnService
{
    /** Return one line partially or fully; each submission has a stable operation key. */
    public function process(User $actor, int $orderId, array $data): void
    {
        abort_unless($actor->role === 'admin', 403);
        DB::transaction(function () use ($actor, $orderId, $data) {
            Tenant::whereKey($actor->tenant_id)->lockForUpdate()->firstOrFail();
            $order = Order::where('tenant_id', $actor->tenant_id)->whereKey($orderId)->lockForUpdate()->firstOrFail();
            $existing = CashEntry::where('operation_key', $data['operation_key'])->first();
            if ($existing) {
                $returned = DB::table('order_returns')->where('cash_entry_id', $existing->id)->first();
                if ($existing->kind !== 'refund' || $existing->order_id !== $orderId || $existing->user_id !== $actor->id || $existing->reason !== $data['reason'] || ! $returned || $returned->order_item_id !== (int) $data['item_id'] || RetailQuantity::ticks($returned->quantity) !== RetailQuantity::ticks($data['quantity']) || (bool) $returned->restock !== (bool) ($data['restock'] ?? false)) {
                    throw ValidationException::withMessages(['operation_key' => 'Identitas retur sudah digunakan untuk data berbeda.']);
                }

                return;
            }
            if ($order->payment_method !== 'cash' || $order->payment_status !== 'paid' || $order->order_status !== 'completed') {
                throw ValidationException::withMessages(['item_id' => 'Retur tunai hanya untuk transaksi tunai yang sudah lunas.']);
            }
            $items = $order->items()->orderBy('id')->get();
            $item = $items->firstWhere('id', (int) $data['item_id']);
            if (! $item) {
                throw ValidationException::withMessages(['item_id' => 'Barang tidak termasuk dalam transaksi.']);
            }
            $previousQty = (float) DB::table('order_returns')->where('order_item_id', $item->id)->sum('quantity');
            $quantity = (float) $data['quantity'];
            // New invoices retain their selling rule even after catalog settings change.
            // For legacy invoices, use fractional evidence or the current catalog rule.
            $allowFraction = $item->allow_fraction ?? (RetailQuantity::ticks($item->quantity) % 1000 !== 0 || (bool) $item->product?->allow_fraction);
            RetailQuantity::requireWhole($quantity, $allowFraction);
            if (RetailQuantity::ticks($previousQty) + RetailQuantity::ticks($quantity) > RetailQuantity::ticks($item->quantity)) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah melebihi barang yang belum diretur.']);
            }
            // Cumulative allocation ensures all line entitlements sum to the invoice total,
            // including whole-rupiah tax and discounts, regardless of return order.
            $hasDiscountSnapshots = $items->every(fn ($row) => $row->discount_amount !== null);
            $lineValue = fn ($row) => (float) $row->subtotal - ($hasDiscountSnapshots ? (float) $row->discount_amount : 0);
            $lineTotal = (float) $items->sum($lineValue);
            if ($lineTotal <= 0) {
                throw ValidationException::withMessages(['item_id' => 'Transaksi tanpa nilai perlu diperiksa manual.']);
            }
            $before = (float) $items->takeWhile(fn ($row) => $row->id !== $item->id)->sum($lineValue);
            $entitlement = (int) round(($before + $lineValue($item)) / $lineTotal * $order->grand_total) - (int) round($before / $lineTotal * $order->grand_total);
            $amount = (int) round($entitlement * ($previousQty + $quantity) / $item->quantity) - (int) round($entitlement * $previousQty / $item->quantity);
            $shift = Shift::where('tenant_id', $actor->tenant_id)->where('user_id', $actor->id)->where('status', 'open')->lockForUpdate()->first();
            if (! $shift || $shift->cash_start + app(CashLedger::class)->net($shift) < $amount) {
                throw ValidationException::withMessages(['quantity' => 'Buka shift dengan kas cukup untuk mengembalikan uang.']);
            }
            $entry = CashEntry::create(['tenant_id' => $actor->tenant_id, 'shift_id' => $shift->id, 'user_id' => $actor->id, 'order_id' => $order->id, 'operation_key' => $data['operation_key'], 'kind' => 'refund', 'amount' => -$amount, 'reason' => $data['reason']]);
            $restock = (bool) ($data['restock'] ?? false);
            if ($restock) {
                if (! $item->reserved_stock) {
                    throw ValidationException::withMessages(['restock' => 'Item ini tidak memiliki stok barang yang dicadangkan.']);
                }
                $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
                $stock = $item->variant_id ? $product->variants()->whereKey($item->variant_id)->lockForUpdate()->firstOrFail() : $product;
                $restockQuantity = RetailQuantity::base($quantity, $item->unit_factor);
                $stockBefore = $stock->stock;
                $stock->increment('stock', $restockQuantity);
                StockMovement::create(['tenant_id' => $actor->tenant_id, 'product_id' => $product->id, 'user_id' => $actor->id, 'type' => 'return', 'quantity' => $restockQuantity, 'before_stock' => $stockBefore, 'after_stock' => $stockBefore + $restockQuantity, 'note' => $data['reason'], 'reference_type' => 'order_item', 'reference_id' => $item->id]);
            }
            $priorRefund = (float) DB::table('order_returns')->where('order_id', $order->id)->sum('amount');
            DB::table('order_returns')->insert(['tenant_id' => $actor->tenant_id, 'order_id' => $order->id, 'order_item_id' => $item->id, 'cash_entry_id' => $entry->id, 'quantity' => $quantity, 'amount' => $amount, 'restock' => $restock, 'created_at' => now(), 'updated_at' => now()]);
            app(CustomerPoints::class)->reverseForReturn($order, $amount, (int) $priorRefund, $entry->id);
            app(CustomerStamps::class)->reverseForReturn($order, $amount, (int) $priorRefund, $entry->id);
            ActivityLog::create(['tenant_id' => $actor->tenant_id, 'user_id' => $actor->id, 'action' => 'order.return', 'description' => 'Order '.$order->id.' item '.$item->id.'; '.$quantity.' unit; Rp '.$amount.'; '.$data['reason']]);
        }, 3);
    }
}
