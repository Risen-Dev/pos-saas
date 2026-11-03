<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\BillingPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class SubscriptionController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;
        $plans = Plan::where('is_active', true)->where('is_public', true)->get();

        // 🛠️ PERBAIKAN: Hanya ambil subscription yang berstatus ACTIVE dan belum expired
        $currentSubscription = $tenant ? $tenant->subscriptions()
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->latest('id')
            ->first() : null;

        // Ambil invoice pending jika ada (untuk info banner checkout tertunda)
        $pendingInvoice = $tenant ? Invoice::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->where('due_date', '>=', now())
            ->latest('id')
            ->first() : null;

        return view('billing.index', compact('plans', 'currentSubscription', 'pendingInvoice'));
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')->where('is_active', true)->where('is_public', true)],
            'billing_period' => ['nullable', Rule::in(['monthly', 'yearly'])],
        ]);
        $user = $request->user();
        abort_unless($user->tenant_id, 422, 'Siapkan bisnis sebelum memilih paket.');
        $invoice = DB::transaction(function () use ($user, $data) {
            $tenant = Tenant::whereKey($user->tenant_id)->lockForUpdate()->firstOrFail();
            $plan = Plan::where('is_active', true)->where('is_public', true)->findOrFail($data['plan_id']);
            $billingPeriod = (float) $plan->price === 0.0 ? 'free' : ($data['billing_period'] ?? 'monthly');
            $duration = $billingPeriod === 'yearly' ? 365 : ($plan->duration_days ?? 30);
            $amount = $billingPeriod === 'yearly' ? (int) round($plan->price * 10) : (int) round($plan->price);
            if ((float) $plan->price === 0.0) {
                $active = $tenant->subscriptions()->where('status', 'active')->where('start_date', '<=', today())
                    ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
                    ->latest('id')->first();
                if ($active) {
                    return null;
                }
                Subscription::create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id,
                    'status' => 'active', 'start_date' => today(), 'end_date' => null]);
                $tenant->update(['status' => 'active']);

                return null;
            }

            return Invoice::where('tenant_id', $tenant->id)->where('plan_id', $plan->id)
                ->where('duration_days', $duration)
                ->where('status', 'pending')->where('due_date', '>=', now())->latest('id')->first()
                ?? Invoice::create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id,
                    'invoice_number' => 'INV-SAAS-'.Str::uuid(), 'amount' => $amount,
                    'duration_days' => $duration, 'status' => 'pending', 'due_date' => now()->addDay()]);
        }, 3);
        if (! $invoice) {
            return redirect()->route('dashboard')->with('success', 'Paket aktif siap digunakan.');
        }
        if (! $invoice->snap_token) {
            $this->createSnapTransaction($invoice, $user);
        }

        return redirect()->route('billing.invoice', $invoice->id);
    }

    public function showInvoice(Invoice $invoice)
    {
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($invoice->status === 'pending' && empty($invoice->snap_token)) {
            $this->createSnapTransaction($invoice, auth()->user());
            $invoice->refresh();
        }

        return view('billing.invoice', compact('invoice'));
    }

    public function cancelInvoice(Invoice $invoice)
    {
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        Invoice::whereKey($invoice->id)->where('status', 'pending')->update(['status' => 'cancelled']);

        return redirect()->route('billing.index')->with('success', 'Tagihan berhasil dibatalkan.');
    }

    public function checkStatus(Invoice $invoice)
    {
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($invoice->status === 'paid' && $invoice->subscription_id) {
            return response()->json(['success' => true, 'status' => 'paid']);
        }

        $this->configureMidtrans();

        try {
            $midtransStatus = Transaction::status($invoice->invoice_number);
            app(BillingPaymentService::class)->apply($invoice->id, (array) $midtransStatus);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Status pembayaran belum dapat dikonfirmasi. Silakan coba lagi.'], 503);
        }

        return response()->json(['success' => true, 'status' => $invoice->fresh()->status]);
    }

    private function createSnapTransaction(Invoice $invoice, $user): void
    {
        $this->configureMidtrans();

        $params = [
            'transaction_details' => [
                'order_id' => $invoice->invoice_number,
                'gross_amount' => (int) round($invoice->amount),
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'enabled_payments' => ['qris', 'bank_transfer', 'gopay', 'shopeepay', 'other_qris'],
            'expiry' => [
                'unit' => 'hours',
                'duration' => 24,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $invoice->update(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap request failed.', ['invoice' => $invoice->invoice_number, 'exception' => $e::class]);
        }
    }

    private function configureMidtrans(): void
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }
}
