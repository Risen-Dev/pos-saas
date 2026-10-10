<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    /**
     * Menampilkan daftar pelanggan milik tenant yang sedang login.
     */
    public function index()
    {
        // Proteksi Fitur CRM (Khusus Growth & Scale)
        if (Gate::denies('feature-crm')) {
            return redirect()->route('billing.index')
                ->with('warning', 'Fitur Manajemen Pelanggan / CRM hanya tersedia pada Paket Growth & Scale.');
        }

        $tenantId = auth()->user()->tenant_id;

        $customers = Customer::where('tenant_id', $tenantId)
            ->latest()
            ->paginate(10);

        return view('customers.index', compact('customers'));
    }

    /**
     * Menyimpan data pelanggan baru.
     */
    public function store(Request $request)
    {
        // Proteksi Fitur CRM
        if (Gate::denies('feature-crm')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fitur Pelanggan/CRM hanya tersedia pada Paket Growth & Scale.',
                ], 403);
            }

            return redirect()->route('billing.index')->with('warning', 'Fitur CRM hanya tersedia pada Paket Growth & Scale.');
        }

        // 1. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            // 2. Simpan Data Pelanggan
            $customer = Customer::create([
                'tenant_id' => auth()->user()->tenant_id,
                'name' => $request->name,
                'phone' => $request->phone,
                'is_member' => filter_var($request->is_member, FILTER_VALIDATE_BOOLEAN) || $request->is_member == '1',
                'points' => 0,
                'total_debt' => 0,
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'message' => 'Pelanggan berhasil ditambahkan!',
                ]);
            }

            if ($request->redirect_to === 'pos') {
                return redirect()->route('pos.index')->with('success', 'Pelanggan '.$customer->name.' berhasil ditambahkan.');
            }

            return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil didaftarkan.');
        } catch (\Exception $e) {
            report($e);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan pelanggan. Silakan coba lagi.',
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menyimpan pelanggan. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menampilkan detail pelanggan (untuk riwayat belanja & piutang).
     */
    public function show(Customer $customer)
    {
        if (Gate::denies('feature-crm')) {
            abort(403, 'Fitur CRM tidak tersedia pada paket Anda.');
        }

        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        return view('customers.show', compact('customer'));
    }

    /**
     * Menghapus data pelanggan.
     */
    public function destroy(Customer $customer)
    {
        if (Gate::denies('feature-crm')) {
            abort(403);
        }

        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($customer->total_debt > 0) {
            return redirect()->back()->with('error', 'Pelanggan tidak bisa dihapus karena masih memiliki tanggungan hutang.');
        }

        abort_unless(auth()->user()->role === 'admin', 403);
        if (Order::where('customer_id', $customer->id)->exists()) {
            return back()->withErrors(['customer' => 'Pelanggan memiliki riwayat transaksi dan tidak dapat dihapus.']);
        }
        $customer->delete();

        return redirect()->back()->with('success', 'Data pelanggan berhasil dihapus.');
    }

    public function storeApi(Request $request)
    {
        if (Gate::denies('feature-crm')) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur Pelanggan/CRM hanya tersedia pada Paket Growth & Scale.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $customer = Customer::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $customer,
        ]);
    }
}
