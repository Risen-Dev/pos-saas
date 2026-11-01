<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingPaymentService
{
    public function apply(int $invoiceId, array $payment): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $payment) {
            $tenantId = Invoice::whereKey($invoiceId)->value('tenant_id');
            $tenant = Tenant::whereKey($tenantId)->lockForUpdate()->firstOrFail();
            $invoice = Invoice::whereKey($invoiceId)->lockForUpdate()->firstOrFail();
            if (($payment['order_id'] ?? null) !== $invoice->invoice_number
                || ! is_numeric($payment['gross_amount'] ?? null)
                || round((float) $payment['gross_amount'], 2) !== round((float) $invoice->amount, 2)) {
                throw ValidationException::withMessages(['payment' => 'Identitas atau nominal invoice tidak sesuai.']);
            }
            $status = $payment['transaction_status'] ?? '';
            $settled = $status === 'settlement' || ($status === 'capture' && ($payment['fraud_status'] ?? '') === 'accept');
            if ($invoice->activation_applied_at || ($invoice->status === 'paid' && $invoice->subscription_id)) {
                return $invoice;
            }
            if (! $settled) {
                if ($invoice->status === 'pending' && in_array($status, ['expire', 'cancel', 'deny'], true)) {
                    $invoice->update(['status' => $status === 'expire' ? 'expired' : 'cancelled']);
                }

                return $invoice;
            }
            $plan = Plan::findOrFail($invoice->plan_id ?? $invoice->subscription?->plan_id);
            $duration = $invoice->duration_days ?? $plan->duration_days ?? 30;
            $active = Subscription::withoutGlobalScopes()->where('tenant_id', $tenantId)
                ->where('status', 'active')->where('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())->latest('id')->lockForUpdate()->first();
            if ($active && $active->plan_id === $plan->id) {
                $active->update(['end_date' => Carbon::parse($active->end_date)->addDays($duration)]);
                $subscription = $active;
            } else {
                Subscription::withoutGlobalScopes()->where('tenant_id', $tenantId)->where('status', 'active')->update(['status' => 'cancelled']);
                $subscription = Subscription::withoutGlobalScopes()->create([
                    'tenant_id' => $tenantId, 'plan_id' => $plan->id, 'status' => 'active',
                    'start_date' => today(), 'end_date' => now()->addDays($duration),
                ]);
            }
            $tenant->update(['status' => 'active']);
            $invoice->update([
                'subscription_id' => $subscription->id, 'status' => 'paid',
                'paid_at' => $invoice->paid_at ?? now(), 'activation_applied_at' => now(),
                'payment_method' => $payment['payment_type'] ?? null,
            ]);

            return $invoice;
        }, 3);
    }
}
