<?php

// app/Http/Controllers/InvoiceController.php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        return Invoice::with(['tenant', 'subscription'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'nullable|string|in:unpaid,paid,overdue',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        $invoice = Invoice::create($validated);

        return response()->json($invoice, 201);
    }

    public function show(Invoice $invoice)
    {
        abort_if(
            $invoice->tenant_id !== auth()->user()->tenant_id,
            403,
            'Anda tidak memiliki akses ke invoice ini.'
        );

        return $invoice->load(['tenant', 'subscription']);
    }

    public function update(Request $request, Invoice $invoice)
    {
        abort_if(
            $invoice->tenant_id !== auth()->user()->tenant_id,
            403,
            'Anda tidak memiliki akses ke invoice ini.'
        );

        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'due_date' => 'sometimes|date',
            'status' => 'sometimes|string|in:unpaid,paid,overdue',
        ]);

        $invoice->update($validated);

        return $invoice;
    }

    public function destroy(Invoice $invoice)
    {
        abort_if(
            $invoice->tenant_id !== auth()->user()->tenant_id,
            403,
            'Anda tidak memiliki akses ke invoice ini.'
        );

        $invoice->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
