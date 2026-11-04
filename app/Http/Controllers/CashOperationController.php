<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CashEntry;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shift;
use App\Models\Tenant;
use App\Services\CashLedger;
use App\Services\CustomerPoints;
use App\Services\OrderReturnService;
use App\Services\OrderStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashOperationController extends Controller
{
    public function index(Request $request)
    {
        $entries = CashEntry::with(['shift.user', 'user', 'order'])
            ->latest()
            ->paginate(30);

        $todayQuery = CashEntry::whereDate('created_at', today());
        $todayIn = (clone $todayQuery)->where('amount', '>', 0)->sum('amount');
        $todayOut = abs((float) (clone $todayQuery)->where('amount', '<', 0)->sum('amount'));

        $currentShift = Shift::with('user')
            ->where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->latest('start_time')
            ->first();

        $currentCash = $currentShift
            ? (float) $currentShift->cash_start + app(CashLedger::class)->net($currentShift)
            : null;

        return view('cash.index', compact('entries', 'todayIn', 'todayOut', 'currentShift', 'currentCash'));
    }

    public function payment(Request $request, Order $order)
    {
        $data = $request->validate(['operation_key' => 'required|uuid', 'amount' => 'required|integer|min:1|max:999999999999', 'reason' => 'required|string|max:500']);
        DB::transaction(function () use ($request, $order, $data) {
            Tenant::whereKey($request->user()->tenant_id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($order->id)->where('tenant_id', $request->user()->tenant_id)->lockForUpdate()->firstOrFail();
            if ($this->replayed($request, $data, 'payment', $order->id)) {
                return;
            }
            if ($order->payment_method !== 'cash' || $order->order_status === 'cancelled' || $order->payment_status === 'paid') {
                throw ValidationException::withMessages(['amount' => 'Pembayaran hanya untuk bon tunai yang masih aktif.']);
            }
            $remaining = max(0, (int) round($order->grand_total - $order->paid_amount + $order->change_amount));
            if ($data['amount'] > $remaining) {
                throw ValidationException::withMessages(['amount' => 'Pembayaran melebihi sisa tagihan.']);
            }
            if (! $order->cash_tracked && $order->paid_amount > 0) {
                throw ValidationException::withMessages(['amount' => 'Pembayaran lama perlu direkonsiliasi sebelum menerima cicilan baru.']);
            }
            $shift = $this->shift($request);
            $this->entry($request, $data, 'payment', $shift, $order->id);
            Payment::create(['tenant_id' => $order->tenant_id, 'order_id' => $order->id, 'user_id' => $request->user()->id, 'payment_method' => 'cash', 'amount' => $data['amount'], 'reference_number' => $data['operation_key'], 'paid_at' => now()]);
            $paid = (int) $data['amount'] === $remaining;
            $order->update(['cash_tracked' => true, 'paid_amount' => $order->paid_amount + $data['amount'], 'payment_status' => $paid ? 'paid' : 'unpaid', 'order_status' => $paid ? 'completed' : 'pending']);
            if ($order->customer_id) {
                $customer = Customer::whereKey($order->customer_id)->lockForUpdate()->firstOrFail();
                $customer->update(['total_debt' => max(0, $customer->total_debt - $data['amount'])]);
            }
            if ($paid) {
                app(CustomerPoints::class)->award($order);
                app(\App\Services\CustomerStamps::class)->award($order);
            }
        }, 3);

        return back()->with('success', 'Pembayaran tunai tercatat pada shift penerima.');
    }

    public function expense(Request $request)
    {
        $data = $request->validate(['operation_key' => 'required|uuid', 'amount' => 'required|integer|min:1|max:999999999999', 'reason' => 'required|string|max:500']);
        DB::transaction(function () use ($request, $data) {
            Tenant::whereKey($request->user()->tenant_id)->lockForUpdate()->firstOrFail();
            if ($this->replayed($request, $data, 'expense', null)) {
                return;
            }
            $shift = $this->shift($request);
            if ($data['amount'] > $shift->cash_start + app(CashLedger::class)->net($shift)) {
                throw ValidationException::withMessages(['amount' => 'Pengeluaran melebihi kas yang tersedia.']);
            }
            $this->entry($request, $data, 'expense', $shift, null);
        }, 3);

        return back()->with('success', 'Pengeluaran tercatat dan kas shift berkurang.');
    }

    public function cancel(Request $request, Order $order)
    {
        $data = $request->validate(['reason' => 'required|string|max:500']);
        DB::transaction(function () use ($request, $order, $data) {
            Tenant::whereKey($request->user()->tenant_id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($order->id)->where('tenant_id', $request->user()->tenant_id)->lockForUpdate()->firstOrFail();
            if ($order->order_status === 'cancelled') {
                return;
            }
            if ($order->payment_method !== 'cash' || $order->payment_status === 'paid' || $order->paid_amount > 0) {
                throw ValidationException::withMessages(['reason' => 'Pembatalan ini hanya untuk bon tanpa pembayaran. Transaksi yang sudah dibayar memerlukan retur/refund.']);
            }
            app(OrderStock::class)->change($order, true);
            if ($order->customer_id) {
                $customer = Customer::whereKey($order->customer_id)->lockForUpdate()->firstOrFail();
                $customer->update(['total_debt' => max(0, $customer->total_debt - $order->grand_total)]);
            }
            $order->update(['order_status' => 'cancelled', 'cancellation_reason' => $data['reason'], 'cancelled_by' => $request->user()->id, 'cancelled_at' => now()]);
            $this->audit($request, 'order.cancel', 'Order '.$order->id.': '.$data['reason']);
        }, 3);

        return back()->with('success', 'Bon dibatalkan; stok dan piutang dikembalikan.');
    }

    public function refund(Request $request, Order $order, OrderReturnService $returns)
    {
        $data = $request->validate([
            'operation_key' => 'required|uuid', 'item_id' => 'required|integer',
            'quantity' => 'required|numeric|decimal:0,3|min:0.001|max:100000', 'restock' => 'sometimes|boolean',
            'reason' => 'required|string|max:255',
        ]);
        $returns->process($request->user(), $order->id, $data);

        return back()->with('success', 'Retur dan pengembalian uang tunai tercatat.');
    }

    private function shift(Request $request): Shift
    {
        $shift = Shift::where('tenant_id', $request->user()->tenant_id)->where('user_id', $request->user()->id)->where('status', 'open')->lockForUpdate()->first();
        if (! $shift) {
            throw ValidationException::withMessages(['amount' => 'Buka shift terlebih dahulu.']);
        }

        return $shift;
    }

    private function replayed(Request $request, array $data, string $kind, ?int $orderId): bool
    {
        $entry = CashEntry::where('tenant_id', $request->user()->tenant_id)->where('operation_key', $data['operation_key'])->first();
        if (! $entry) {
            return false;
        }
        if ($entry->kind !== $kind || $entry->order_id !== $orderId || abs((float) $entry->amount) !== (float) $data['amount'] || $entry->reason !== $data['reason'] || $entry->user_id !== $request->user()->id) {
            throw ValidationException::withMessages(['operation_key' => 'Identitas operasi sudah digunakan untuk data berbeda.']);
        }

        return true;
    }

    private function entry(Request $request, array $data, string $kind, Shift $shift, ?int $orderId): void
    {
        CashEntry::create(['tenant_id' => $request->user()->tenant_id, 'shift_id' => $shift->id, 'user_id' => $request->user()->id, 'order_id' => $orderId, 'operation_key' => $data['operation_key'], 'kind' => $kind, 'amount' => $kind === 'expense' ? -$data['amount'] : $data['amount'], 'reason' => $data['reason']]);
        $this->audit($request, 'cash.'.$kind, 'Rp '.$data['amount'].'; '.$data['reason']);
    }

    private function audit(Request $request, string $action, string $description): void
    {
        ActivityLog::create(['tenant_id' => $request->user()->tenant_id, 'user_id' => $request->user()->id, 'action' => $action, 'description' => $description, 'ip_address' => $request->ip()]);
    }
}
