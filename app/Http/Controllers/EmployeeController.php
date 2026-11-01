<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    // Tampilkan Daftar Pegawai
    public function index()
    {
        // Hanya mengambil pegawai yang satu toko/tenant dengan admin yang login
        $employees = User::where('tenant_id', Auth::user()->tenant_id)
            ->orderBy('role', 'asc')
            ->get();

        return view('employees.index', compact('employees'));
    }

    // Simpan Pegawai Baru ke Database
    public function store(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $plan = $tenant?->currentPlan();

        if (! $plan) {
            return back()->with('error', 'Masa langganan Anda telah habis.');
        }

        // Hitung total staf/karyawan yang sudah didaftarkan
        $currentEmployeeCount = User::where('tenant_id', $tenant->id)->count();

        if ($currentEmployeeCount >= $plan->max_users) {
            return back()->with('error', "Gagal menambah staf! Paket {$plan->name} dibatasi maksimal {$plan->max_users} pengguna/kasir. Upgrade paket Anda di menu Billing untuk menambah tim.");
        }
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'in:admin,manager,kasir'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $employee = DB::transaction(function () use ($request, $tenant) {
            Tenant::whereKey($tenant->id)->lockForUpdate()->firstOrFail();
            $plan = $tenant->fresh()->currentPlan();
            if (! $plan || User::where('tenant_id', $tenant->id)->count() >= $plan->max_users) {
                throw ValidationException::withMessages(['email' => 'Kuota pegawai paket telah tercapai.']);
            }

            return User::create([
                'tenant_id' => Auth::user()->tenant_id, // Otomatis mengikat ke tenant admin
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'password' => Hash::make($request->password),
            ]);

        });
        event(new Registered($employee));

        return redirect()->route('employees.index')->with('success', 'Pegawai baru berhasil didaftarkan. Email verifikasi telah dikirim.');
    }

    public function status(Request $request, int $id)
    {
        $data = $request->validate(['status' => 'required|in:active,inactive']);
        DB::transaction(function () use ($request, $id, $data) {
            $tenant = Tenant::whereKey($request->user()->tenant_id)->lockForUpdate()->firstOrFail();
            $employee = User::where('tenant_id', $tenant->id)->whereKey($id)->lockForUpdate()->firstOrFail();
            abort_if($employee->id === $request->user()->id || $employee->id === $tenant->user_id, 422, 'Akun pemilik atau akun sendiri tidak dapat dinonaktifkan di sini.');
            $employee->update($data);
            ActivityLog::create(['tenant_id' => $tenant->id, 'user_id' => $request->user()->id,
                'action' => 'employee.status', 'description' => 'Pegawai '.$employee->id.' menjadi '.$data['status']]);
        }, 3);

        return back()->with('success', 'Status akses pegawai diperbarui.');
    }

    // Hapus Pegawai
    public function destroy($id)
    {
        $employee = User::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);

        // Mencegah admin menghapus dirinya sendiri
        if ($employee->id === Auth::id()) {
            return redirect()->route('employees.index')->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        if (Order::where('user_id', $employee->id)->exists() || Shift::where('user_id', $employee->id)->exists() || ActivityLog::where('user_id', $employee->id)->exists()) {
            return back()->withErrors(['employee' => 'Pegawai memiliki riwayat operasional dan tidak dapat dihapus.']);
        }
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Akun pegawai berhasil dihapus.');
    }
}
