<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Support\BusinessProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = auth()->user()->tenants()
            ->withCount('users')
            ->orderBy('id', 'desc')
            ->get();

        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        if (auth()->user()->tenants()->exists()) {
            return redirect()->route('tenants.add');
        }

        return view('tenants.create');
    }

    public function createAdditional()
    {
        if (! auth()->user()->tenants()->exists()) {
            return redirect()->route('tenants.create');
        }

        return view('tenants.add');
    }

    public function store(Request $request)
    {
        // -------------------------------------------------------------------------
        // VALIDASI LIMIT MAKSIMAL TENANT / CABANG BERDASARKAN PAKET
        // -------------------------------------------------------------------------
        $currentUser = auth()->user();

        // Ambil paket langganan dari tenant aktif saat ini (jika sudah ada)
        $currentPlan = $currentUser->tenant?->currentPlan();

        // Hitung total bisnis/tenant yang dimiliki user saat ini
        $ownedTenantsCount = $currentUser->tenants()->count();

        // Jika sudah punya minimal 1 tenant dan mencoba buat baru, cek limit paketnya
        if ($ownedTenantsCount > 0 && $currentPlan) {
            if ($ownedTenantsCount >= $currentPlan->max_outlets) {
                return redirect()->route('billing.index')->with(
                    'warning',
                    "Gagal menambah bisnis baru! Paket {$currentPlan->name} Anda dibatasi maksimal {$currentPlan->max_outlets} cabang/bisnis. Silakan upgrade paket Anda di menu Billing."
                );
            }
        }

        // -------------------------------------------------------------------------
        // PROSES SIMPAN TENANT BARU (Kode Bawaan Anda)
        // -------------------------------------------------------------------------
        $request->validate([
            'name' => 'required|string|max:255',
            'business_type' => ['required', Rule::in(array_keys(BusinessProfile::TYPES))],
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'img_logo' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('img_logo')) {
            $file = $request->file('img_logo');
            $fileName = time().'_'.Str::slug($request->name).'.'.$file->getClientOriginalExtension();
            $logoPath = $file->storeAs('logos', $fileName, 'public');
        }

        DB::transaction(function () use ($currentUser, $request, $logoPath) {
            $owner = User::whereKey($currentUser->id)->lockForUpdate()->firstOrFail();
            $owned = $owner->tenants()->count();
            $plan = $owner->tenant?->currentPlan();
            if ($owned > 0 && (! $plan || $owned >= $plan->max_outlets)) {
                throw ValidationException::withMessages(['name' => 'Kuota outlet paket tercapai atau paket belum aktif.']);
            }
            $tenant = $currentUser->tenants()->create([
                'name' => $request->name,
                'slug' => Str::slug($request->name).'-'.rand(100, 999),
                'business_type' => $request->business_type,
                'business_modules' => BusinessProfile::defaults(BusinessProfile::normalize($request->business_type)),
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'img_logo' => $logoPath,
                'status' => 'active',
            ]);

            $currentUser->update([
                'tenant_id' => $tenant->id,
            ]);

        }, 3);

        return redirect()->route('getting-started')->with('success', 'Bisnis berhasil ditambahkan. Siapkan katalog dan mulai berjualan.');
    }

    public function edit(Tenant $tenant)
    {
        if ($tenant->user_id !== auth()->id()) {
            abort(403);
        }

        return view('tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        if ($tenant->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'business_type' => ['required', Rule::in(array_keys(BusinessProfile::TYPES))],
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'img_logo' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'business_type' => $request->business_type,
            'business_modules' => $tenant->businessType() === BusinessProfile::normalize($request->business_type)
                ? $tenant->businessModules()
                : BusinessProfile::defaults(BusinessProfile::normalize($request->business_type)),
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        if ($request->hasFile('img_logo')) {
            // Hapus logo lama jika ada
            if ($tenant->img_logo && Storage::disk('public')->exists($tenant->img_logo)) {
                Storage::disk('public')->delete($tenant->img_logo);
            }

            $file = $request->file('img_logo');
            $fileName = time().'_'.Str::slug($request->name).'.'.$file->getClientOriginalExtension();
            $data['img_logo'] = $file->storeAs('logos', $fileName, 'public');
        }

        $tenant->update($data);

        return redirect()->route('tenants.index')->with('success', 'Data bisnis berhasil diperbarui!');
    }

    public function destroy(Tenant $tenant)
    {
        if ($tenant->user_id !== auth()->id()) {
            abort(403);
        }

        // Hapus file logo dari storage sebelum menghapus data
        if ($tenant->img_logo && Storage::disk('public')->exists($tenant->img_logo)) {
            Storage::disk('public')->delete($tenant->img_logo);
        }

        $tenant->delete();

        // Jika tenant yang dihapus adalah tenant aktif, set jadi null atau ganti ke tenant lain
        if (auth()->user()->tenant_id == $tenant->id) {
            $nextTenant = auth()->user()->tenants()->first();
            auth()->user()->update([
                'tenant_id' => $nextTenant ? $nextTenant->id : null,
            ]);
        }

        return redirect()->route('tenants.index')->with('success', 'Bisnis berhasil dihapus!');
    }

    // METHOD BARU UNTUK SWITCH / PINDAH TOKO AKTIF
    // public function switchTenant(Tenant $tenant)
    // {
    //     if ($tenant->user_id !== auth()->id()) {
    //         abort(403);
    //     }

    //     auth()->user()->update([
    //         'tenant_id' => $tenant->id
    //     ]);

    //     return redirect()->back()->with('success', "Berhasil pindah ke toko: {$tenant->name}");
    // }
}
