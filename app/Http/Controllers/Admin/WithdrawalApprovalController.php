<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantWallet;
use App\Models\WithdrawlRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// PENTING: route untuk controller ini WAJIB dibungkus middleware role admin,
// contoh di routes/web.php:
//
// Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->group(function () {
//     Route::get('/withdrawals', [WithdrawalApprovalController::class, 'index']);
//     Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalApprovalController::class, 'approve']);
//     Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalApprovalController::class, 'reject']);
// });

class WithdrawalApprovalController extends Controller
{
    public function index()
    {
        // Admin perlu lihat SEMUA tenant, jadi query lepas dari Global Scope.
        $requests = WithdrawlRequest::withoutTenantScope()
            ->where('status', 'pending')
            ->with('tenant')
            ->latest()
            ->paginate(20);

        return view('admin.withdrawals.index', compact('requests'));
    }

    public function approve(Request $request, $withdrawalId)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string',
        ]);

        DB::transaction(function () use ($withdrawalId, $validated) {
            $withdrawal = WithdrawlRequest::withoutTenantScope()
                ->lockForUpdate()
                ->findOrFail($withdrawalId);

            abort_if($withdrawal->status !== 'pending', 422, 'Pengajuan ini sudah diproses sebelumnya.');

            // Saldo SUDAH dipotong saat pengajuan dibuat (lihat WithdrawalController::store),
            // jadi di sini tidak perlu potong saldo lagi - cukup update status.
            $withdrawal->update([
                'status' => 'approved',
                'admin_note' => $validated['admin_note'] ?? null,
                'processed_at' => now(),
            ]);
        });

        return back()->with('status', 'Penarikan disetujui.');
    }

    public function reject(Request $request, $withdrawalId)
    {
        $validated = $request->validate([
            'admin_note' => 'required|string', // wajib isi alasan kalau reject
        ]);

        DB::transaction(function () use ($withdrawalId, $validated) {
            $withdrawal = WithdrawlRequest::withoutTenantScope()
                ->lockForUpdate()
                ->findOrFail($withdrawalId);

            abort_if($withdrawal->status !== 'pending', 422, 'Pengajuan ini sudah diproses sebelumnya.');

            // Karena saldo sudah dipotong di awal, saat REJECT saldo harus
            // dikembalikan ke wallet tenant.
            $wallet = TenantWallet::withoutTenantScope()
                ->where('tenant_id', $withdrawal->tenant_id)
                ->lockForUpdate()
                ->firstOrFail();

            $wallet->increment('balance', $withdrawal->amount);

            $withdrawal->update([
                'status' => 'rejected',
                'admin_note' => $validated['admin_note'],
                'processed_at' => now(),
            ]);
        });

        return back()->with('status', 'Penarikan ditolak, saldo dikembalikan ke tenant.');
    }
}
