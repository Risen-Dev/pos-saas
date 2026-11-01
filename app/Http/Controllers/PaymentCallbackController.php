<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Services\BillingPaymentService;
use App\Services\OrderPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PaymentCallbackController extends Controller
{
    public function handleNotification(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|string|max:255',
                'transaction_status' => 'required|string|max:40',
                'status_code' => 'required|string|max:3',
                'gross_amount' => 'required|numeric|min:0',
                'signature_key' => 'required|string',
                'payment_type' => 'nullable|string|max:50',
                'fraud_status' => 'nullable|string|max:30',
            ]);
            // 1. Ambil data JSON mentah dari Midtrans
            $orderId = $request->input('order_id');
            $transactionStatus = $request->input('transaction_status');
            $statusCode = $request->input('status_code');
            $grossAmount = $request->input('gross_amount');
            $inputSignature = $request->input('signature_key');

            Log::info("Webhook Masuk - Order ID: {$orderId} | Status: {$transactionStatus} | Code: {$statusCode}");

            // 3. KEAMANAN KRITIS: Verifikasi Signature Key SHA512 dari Midtrans
            $serverKey = config('services.midtrans.server_key');
            $signatureString = $orderId.$statusCode.$grossAmount.$serverKey;
            $calculatedSignature = hash('sha512', $signatureString);

            if (! $serverKey || ! is_string($inputSignature) || ! hash_equals($calculatedSignature, $inputSignature)) {
                Log::warning('Midtrans Webhook: Signature Key TIDAK VALID! Potensi manipulasi data.', [
                    'order_id' => $orderId,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid signature key',
                ], 403);
            }

            // Services acquire locks in the same tenant-first order as checkout.
            $order = Order::withoutGlobalScopes()->where('invoice_number', $orderId)->first();
            if ($order) {
                app(OrderPaymentService::class)->apply($order->id, $request->all());

                return response()->json(['status' => 'success', 'message' => 'Callback POS diproses']);
            }
            $invoice = Invoice::where('invoice_number', $orderId)->first();
            if ($invoice) {
                app(BillingPaymentService::class)->apply($invoice->id, $request->all());

                return response()->json(['status' => 'success', 'message' => 'Callback billing diproses']);
            }

            return response()->json(['status' => 'retry', 'message' => 'Invoice belum tersedia. Kirim ulang notifikasi.'], 503);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Data pembayaran tidak sesuai.'], 422);
        } catch (\Throwable $e) {
            Log::error('Crash internal pada Webhook Midtrans: '.$e->getMessage());

            return response()->json([
                'status' => 'error_caught',
                'message' => 'Pemrosesan pembayaran gagal. Silakan kirim ulang notifikasi.',
            ], 503); // Failure must remain retryable; do not acknowledge an uncommitted payment.
        }
    }
}
