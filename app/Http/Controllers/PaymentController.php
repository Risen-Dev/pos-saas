<?php

// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        // Dengan trait BelongsToTenant terpasang di model Payment,
        // query ini OTOMATIS sudah ter-filter tenant_id.
        // Filter manual di bawah ini sifatnya double-safety, boleh dipertahankan.
        return Payment::with(['order', 'user'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,qris,transfer,debit,credit', // sesuaikan enum Anda
            'status' => 'nullable|string|in:pending,paid,failed',
            'reference_no' => 'nullable|string',
        ]);

        // Pastikan order yang dipakai memang milik tenant yang sama.
        $order = Order::where('id', $validated['order_id'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['user_id'] = auth()->id();

        $payment = Payment::create($validated);

        return response()->json($payment, 201);
    }

    public function show(Payment $payment)
    {
        abort_if(
            $payment->tenant_id !== auth()->user()->tenant_id,
            403,
            'Anda tidak memiliki akses ke data pembayaran ini.'
        );

        return $payment->load(['order', 'user']);
    }

    public function update(Request $request, Payment $payment)
    {
        abort_if(
            $payment->tenant_id !== auth()->user()->tenant_id,
            403,
            'Anda tidak memiliki akses ke data pembayaran ini.'
        );

        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'payment_method' => 'sometimes|string|in:cash,qris,transfer,debit,credit',
            'status' => 'sometimes|string|in:pending,paid,failed',
            'reference_no' => 'nullable|string',
        ]);

        $payment->update($validated);

        return $payment;
    }

    public function destroy(Payment $payment)
    {
        abort_if(
            $payment->tenant_id !== auth()->user()->tenant_id,
            403,
            'Anda tidak memiliki akses ke data pembayaran ini.'
        );

        $payment->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
