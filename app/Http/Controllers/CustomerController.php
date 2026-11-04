<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Services\CustomerPoints;
use App\Services\CustomerStamps;
use App\Services\LoyaltyProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function lookup(Request $request)
    {
        Gate::authorize('feature-crm');
        $data = $request->validate(['q' => 'nullable|string|max:100', 'selected' => 'nullable|integer|min:1']);
        $customers = Customer::where('tenant_id', auth()->user()->tenant_id)
            ->select(['id', 'name', 'is_member', 'points', 'loyalty_member_code'])
            ->withSum('loyaltyStampTransactions as stamp_balance', 'stamps_delta')
            ->when($data['q'] ?? '', fn ($query, $term) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$term.'%')->orWhere('phone', 'like', '%'.$term.'%')->orWhere('loyalty_member_code', 'like', '%'.$term.'%')))
            ->orderBy('name')->limit(50)->get();
        if (! empty($data['selected']) && ! $customers->contains('id', (int) $data['selected'])) {
            $selected = Customer::where('tenant_id', auth()->user()->tenant_id)
                ->select(['id', 'name', 'is_member', 'points', 'loyalty_member_code'])
                ->withSum('loyaltyStampTransactions as stamp_balance', 'stamps_delta')
                ->find($data['selected']);
            if ($selected) {
                $customers->prepend($selected);
            }
        }

        return response()->json(['customers' => $customers->values()]);
    }

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

        $totalDebt = Customer::where('tenant_id', $tenantId)->sum('total_debt');

        return view('customers.index', compact('customers', 'totalDebt'));
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
            'phone' => ['nullable', 'string', 'max:20', Rule::requiredIf(fn () => Gate::allows('feature-loyalty') && $request->boolean('is_member'))],
        ]);

        try {
            // 2. Simpan Data Pelanggan
            $customer = Customer::create([
                'tenant_id' => auth()->user()->tenant_id,
                'name' => $request->name,
                'phone' => $request->phone,
                'is_member' => Gate::allows('feature-loyalty') && $request->boolean('is_member'),
                'points' => 0,
                'total_debt' => 0,
            ]);
            if ($customer->is_member) {
                $customer->ensureLoyaltyCard();
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'message' => 'Pelanggan berhasil ditambahkan!',
                ]);
            }

            if ($customer->is_member) {
                return redirect()->route('customers.show', $customer)->with('success', 'Pelanggan terdaftar sebagai member. Bagikan tautan kartu dari halaman ini.');
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
        $tenantId = (int) $customer->tenant_id;

        if (Gate::allows('feature-loyalty') && $customer->is_member) {
            $customer->ensureLoyaltyCard();
        }

        $orders = $customer->orders()->when(auth()->user()->role === 'kasir', fn ($query) => $query->where('user_id', auth()->id()))
            ->latest('id')->paginate(15);

        $loyaltyProgramType = app(LoyaltyProgram::class)->type(app(CustomerPoints::class)->settings($tenantId));
        $loyaltyBalance = $loyaltyProgramType === 'stamps'
            ? app(CustomerStamps::class)->balance($customer)
            : (int) $customer->points;
        $loyaltyActivity = $loyaltyProgramType === 'stamps'
            ? $customer->loyaltyStampTransactions()->latest('id')->limit(10)->get()
            : $customer->loyaltyPointTransactions()->latest('id')->limit(10)->get();
        $loyaltyCardUrl = $customer->is_member && $customer->loyalty_card_token
            ? route('loyalty.card', $customer->loyalty_card_token)
            : null;
        $shareText = $loyaltyCardUrl
            ? 'Kartu member '.$customer->tenant->name.' untuk '.$customer->name.': '.$loyaltyCardUrl
            : '';
        $whatsappNumber = preg_replace('/\D+/', '', (string) $customer->phone);
        if (str_starts_with($whatsappNumber, '0')) {
            $whatsappNumber = '62'.substr($whatsappNumber, 1);
        }
        $whatsappShareUrl = $loyaltyCardUrl
            ? 'https://wa.me/'.($whatsappNumber ?: '').'?text='.rawurlencode($shareText)
            : null;

        return view('customers.show', compact('customer', 'orders', 'loyaltyActivity', 'loyaltyCardUrl', 'whatsappShareUrl', 'loyaltyProgramType', 'loyaltyBalance'));
    }

    public function joinLoyalty(Customer $customer)
    {
        Gate::authorize('feature-loyalty');
        abort_unless($customer->tenant_id === auth()->user()->tenant_id, 404);
        if (! $customer->phone) {
            return back()->withErrors(['phone' => 'Tambahkan nomor telepon sebelum membuat tautan kartu loyalitas.']);
        }

        $customer->ensureLoyaltyCard();

        return redirect()->route('customers.show', $customer)->with('success', 'Kartu loyalitas pelanggan berhasil dibuat. Bagikan tautannya melalui WhatsApp.');
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
            'phone' => ['nullable', 'string', 'max:20', Rule::requiredIf(fn () => Gate::allows('feature-loyalty') && $request->boolean('is_member'))],
            'is_member' => 'sometimes|boolean',
        ]);

        $customer = null;
        if (! empty($validated['phone'])) {
            $customer = Customer::where('tenant_id', $request->user()->tenant_id)->where('phone', $validated['phone'])->first();
        }
        if ($customer) {
            $customer->update(['name' => $validated['name']]);
        } else {
            $customer = Customer::create([
                'tenant_id' => $request->user()->tenant_id,
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'is_member' => false,
            ]);
        }
        if (Gate::allows('feature-loyalty') && $request->boolean('is_member')) {
            $customer->ensureLoyaltyCard();
        }

        $share = [];
        if ($customer->is_member) {
            $customer->ensureLoyaltyCard();
            $cardUrl = route('loyalty.card', $customer->loyalty_card_token);
            $phone = preg_replace('/\D+/', '', (string) $customer->phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62'.substr($phone, 1);
            }
            $message = 'Kartu member '.$customer->tenant->name.' untuk '.$customer->name.': '.$cardUrl;
            $share = [
                'loyalty_card_url' => $cardUrl,
                'whatsapp_share_url' => 'https://wa.me/'.($phone ?: '').'?text='.rawurlencode($message),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => array_merge(
                $customer->only(['id', 'name', 'is_member', 'points', 'loyalty_member_code']),
                ['stamp_balance' => app(\App\Services\CustomerStamps::class)->balance($customer)],
                $share,
            ),
        ]);
    }
}
