<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
            ->where('end_date', '>=', now())
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
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = auth()->user();
        $tenant = $user->tenant;
        $plan = Plan::findOrFail($request->plan_id);

        // Jika memilih paket gratis (Starter)
        if ($plan->price == 0) {
            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays($plan->duration_days ?? 30),
            ]);

            $tenant->update(['status' => 'active']);

            return redirect()->route('dashboard')
                ->with('success', 'Paket Starter berhasil diaktifkan!');
        }

        // Cek jika sudah ada Invoice PENDING untuk paket ini
        $existingInvoice = Invoice::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->where('due_date', '>=', now())
            ->latest('id')
            ->first();

        if ($existingInvoice && $existingInvoice->subscription?->plan_id == $plan->id) {
            return redirect()->route('billing.invoice', $existingInvoice->id);
        }

        // 🛠️ PERBAIKAN UTAMA:
        // Invoice TIDAK LAGI membutuhkan subscription_id di awal!
        // Record Subscription HANYA diciptakan saat pembayaran telah LUNAS di Midtrans.
        $invoiceNumber = 'INV-SAAS-'.date('Ymd').'-'.strtoupper(Str::random(5));

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => null, // Set null saat pending
            'plan_id' => $plan->id, // Simpan plan_id target pada invoice
            'invoice_number' => $invoiceNumber,
            'amount' => $plan->price,
            'status' => 'pending',
            'due_date' => now()->addDays(1),
        ]);

        $this->createSnapTransaction($invoice, $user);

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

        if ($invoice->status === 'pending') {
            $invoice->update(['status' => 'cancelled']);
        }

        return redirect()->route('billing.index')->with('success', 'Tagihan berhasil dibatalkan.');
    }

    public function checkStatus(Invoice $invoice)
    {
        if ($invoice->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($invoice->status === 'paid') {
            return response()->json(['success' => true, 'status' => 'paid']);
        }

        $this->configureMidtrans();

        try {
            $midtransStatus = Transaction::status($invoice->invoice_number);
            $this->applyMidtransStatus($invoice, $midtransStatus);
        } catch (\Exception $e) {
            Log::error("Billing Check Status Error [{$invoice->invoice_number}]: ".$e->getMessage());
        }

        return response()->json(['success' => true, 'status' => $invoice->fresh()->status]);
    }

    public function notification(Request $request)
    {
        $payload = $request->all();

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if (! $orderId || ! $statusCode || ! $grossAmount || ! $signatureKey) {
            Log::warning('Midtrans notification: payload tidak lengkap', $payload);

            return response()->json(['message' => 'invalid payload'], 400);
        }

        $serverKey = config('services.midtrans.server_key');
        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if (! hash_equals($expectedSignature, $signatureKey)) {
            Log::warning("Midtrans notification: signature tidak valid untuk order_id {$orderId}");

            return response()->json(['message' => 'invalid signature'], 403);
        }

        $invoice = Invoice::where('invoice_number', $orderId)->first();

        if (! $invoice) {
            Log::warning("Midtrans notification: invoice tidak ditemukan untuk order_id {$orderId}");

            return response()->json(['message' => 'invoice not found'], 404);
        }

        $this->configureMidtrans();

        try {
            $midtransStatus = Transaction::status($orderId);
            $this->applyMidtransStatus($invoice, $midtransStatus);
        } catch (\Exception $e) {
            Log::error("Midtrans notification: gagal fetch status [{$orderId}]: ".$e->getMessage());

            return response()->json(['message' => 'error checking status'], 500);
        }

        return response()->json(['message' => 'ok']);
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
            Log::error("Midtrans Snap Error [{$invoice->invoice_number}]: ".$e->getMessage());
        }
    }

    private function applyMidtransStatus(Invoice $invoice, $midtransStatus): void
    {
        $status = $midtransStatus->transaction_status ?? '';
        $paymentType = $midtransStatus->payment_type ?? null;

        if (in_array($status, ['settlement', 'capture']) && $invoice->status !== 'paid') {

            // Dapatkan Plan target dari Invoice (atau dari relasi subscription jika ada)
            $planId = $invoice->plan_id ?? $invoice->subscription?->plan_id;
            $plan = Plan::find($planId);

            if ($plan) {
                $tenant = $invoice->tenant;

                // Cek apakah tenant punya paket aktif saat ini
                $activeSub = $tenant->subscriptions()
                    ->where('status', 'active')
                    ->where('end_date', '>=', now())
                    ->latest('id')
                    ->first();

                if ($activeSub && $activeSub->plan_id == $plan->id) {
                    // JIKA PERPANJANG PAKET SAMA: Tambahkan durasi dari end_date lama
                    $startDate = $activeSub->start_date;
                    $endDate = Carbon::parse($activeSub->end_date)->addDays($plan->duration_days ?? 30);

                    $activeSub->update([
                        'end_date' => $endDate,
                    ]);

                    $subscription = $activeSub;
                } else {
                    // JIKA UPGRADE PAKET BARU: Aktifkan paket baru mulai hari ini
                    if ($activeSub) {
                        $activeSub->update(['status' => 'cancelled']); // Nonaktifkan paket lama
                    }

                    $subscription = Subscription::create([
                        'tenant_id' => $tenant->id,
                        'plan_id' => $plan->id,
                        'status' => 'active',
                        'start_date' => now(),
                        'end_date' => now()->addDays($plan->duration_days ?? 30),
                    ]);
                }

                $tenant->update(['status' => 'active']);

                $invoice->update([
                    'subscription_id' => $subscription->id,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => $paymentType,
                ]);
            }
        } elseif (in_array($status, ['expire', 'cancel', 'deny']) && $invoice->status === 'pending') {
            $invoice->update(['status' => $status === 'expire' ? 'expired' : 'failed']);
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
