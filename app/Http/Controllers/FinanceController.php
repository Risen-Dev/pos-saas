<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Opsional jika ingin ditarik ke laporan detail kelak

class FinanceController extends Controller
{
    /**
     * Menampilkan Halaman Utama Keuangan Tenant
     */
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        // 1. Ambil data dompet/wallet tenant saat ini
        $wallet = DB::table('tenant_wallets')
            ->where('tenant_id', $tenantId)
            ->first();

        // 2. Jika tenant baru pertama kali buka menu ini dan dompet belum terdaftar di DB, buatkan otomatis
        if (! $wallet) {
            DB::table('tenant_wallets')->insertOrIgnore([
                'tenant_id' => $tenantId,
                'balance' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $wallet = DB::table('tenant_wallets')->where('tenant_id', $tenantId)->first();
        }

        // 3. Ambil riwayat pengajuan penarikan dana dari yang terbaru
        $withdrawals = DB::table('withdrawal_requests')
            ->where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->get();

        return view('finance.index', compact('wallet', 'withdrawals'));
    }

    /**
     * Memproses Pengajuan Penarikan Dana (Withdrawal Request) via AJAX
     */
    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'idempotency_key' => 'required|uuid',
            'amount' => 'required|integer|min:10000|max:999999999999',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $amountToWithdraw = (int) $request->amount;

        try {
            return DB::transaction(function () use ($tenantId, $amountToWithdraw, $data) {

                $wallet = DB::table('tenant_wallets')
                    ->where('tenant_id', $tenantId)
                    ->lockForUpdate()
                    ->first();

                if (! $wallet) {
                    return response()->json(['success' => false, 'message' => 'Dompet toko tidak ditemukan.'], 404);
                }

                $existing = DB::table('withdrawal_requests')->where('tenant_id', $tenantId)->where('idempotency_key', $data['idempotency_key'])->first();
                if ($existing) {
                    abort_unless((int) $existing->amount === $amountToWithdraw, 409, 'Referensi pengajuan sudah digunakan untuk nominal berbeda.');

                    return response()->json(['success' => true, 'reference_number' => $existing->reference_number, 'message' => 'Pengajuan ini sudah tercatat.']);
                }

                if (empty($wallet->account_number)) {
                    return response()->json(['success' => false, 'message' => 'Informasi rekening bank tujuan belum dilengkapi.'], 422);
                }

                if ($wallet->balance < $amountToWithdraw) {
                    return response()->json(['success' => false, 'message' => 'Saldo Anda tidak mencukupi untuk melakukan penarikan ini.'], 422);
                }

                $referenceNumber = 'WDR-'.Str::ulid();

                DB::table('tenant_wallets')
                    ->where('tenant_id', $tenantId)
                    ->decrement('balance', $amountToWithdraw);

                // insert() melempar QueryException kalau reference_number bentrok
                // (sangat jarang, tapi karena kolomnya unique(), harus ditangani -
                // supaya kalau gagal, seluruh transaction di-rollback termasuk
                // decrement saldo di atas, bukan saldo kepotong tanpa record).
                DB::table('withdrawal_requests')->insert([
                    'tenant_id' => $tenantId,
                    'reference_number' => $referenceNumber,
                    'idempotency_key' => $data['idempotency_key'],
                    'bank_name' => $wallet->bank_name,
                    'account_number' => $wallet->account_number,
                    'account_name' => $wallet->account_name,
                    'amount' => $amountToWithdraw,
                    'platform_fee' => 0,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pengajuan penarikan berhasil didaftarkan.',
                    'reference_number' => $referenceNumber,
                ]);
            });
        } catch (QueryException $e) {
            // Transaction otomatis rollback oleh Laravel saat exception dilempar
            // di dalam DB::transaction(), jadi saldo TIDAK jadi terpotong.
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kendala sistem saat memproses pengajuan. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Menyimpan atau Memperbarui Pengaturan Rekening Bank Tenant
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:50',
            'account_number' => 'required|string|max:30',
            'account_name' => 'required|string|max:100',
        ]);

        $tenantId = Auth::user()->tenant_id;

        try {
            DB::table('tenant_wallets')->updateOrInsert(
                ['tenant_id' => $tenantId],
                [
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                    'account_name' => $request->account_name,
                    'updated_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan rekening bank berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan rekening belum dapat disimpan. Silakan coba lagi.',
            ], 500);
        }
    }
}
