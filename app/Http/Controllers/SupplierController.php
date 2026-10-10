<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::where('tenant_id', auth()->user()->tenant_id)
            ->latest()
            ->paginate(10);

        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        Supplier::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'Supplier berhasil ditambahkan');
    }

    public function destroy(Supplier $supplier)
    {
        // Pastikan supplier milik tenant yang sedang login
        if ($supplier->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $supplier->delete();

        return redirect()->back()->with('success', 'Supplier berhasil dihapus');
    }
}
