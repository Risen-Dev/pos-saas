<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Tenant;
use App\Services\CashLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role === 'kasir') {
            return redirect()->route('pos.index');
        }

        $query = Shift::where('tenant_id', auth()->user()->tenant_id)->with('user');

        // Filter Nama Staf / Kasir
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            });
        }

        // Filter Status (open / closed)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Berdasarkan Tanggal Mulai Shift
        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->date);
        }

        // Ambil data dengan Pagination (10 data per halaman)
        $shifts = $query->orderBy('id', 'desc')->paginate(10);

        return view('shifts.index', compact('shifts'));
    }

    private function activeShift(bool $lock = false): ?Shift
    {
        $query = Shift::where('tenant_id', Auth::user()->tenant_id)
            ->where('user_id', Auth::id())->where('status', 'open')->orderBy('id');

        return ($lock ? $query->lockForUpdate() : $query)->first();
    }

    private function cashSales(Shift $shift): float
    {
        return app(CashLedger::class)->net($shift);
    }

    public function current()
    {
        return response()->json(['success' => true, 'shift' => $this->activeShift()]);
    }

    public function open(Request $request)
    {
        $data = $request->validate(['cash_start' => 'required|numeric|min:0|max:9999999999']);

        return DB::transaction(function () use ($data) {
            // Lock a stable row even when no open shift exists yet.
            Tenant::lockForUpdate()->findOrFail(Auth::user()->tenant_id);
            if ($this->activeShift(true)) {
                return response()->json(['success' => false, 'message' => 'Anda masih memiliki shift aktif.'], 422);
            }
            $shift = Shift::create(['tenant_id' => Auth::user()->tenant_id, 'user_id' => Auth::id(), 'start_time' => now(), 'cash_start' => $data['cash_start'], 'cash_expected' => $data['cash_start'], 'status' => 'open']);
            session(['active_shift_id' => $shift->id]);

            return response()->json(['success' => true, 'message' => 'Shift berhasil dibuka!']);
        });
    }

    public function summary()
    {
        $shift = $this->activeShift();
        if (! $shift) {
            return response()->json(['success' => false, 'message' => 'Tidak ada shift aktif.'], 404);
        }
        $sales = $this->cashSales($shift);

        return response()->json(['success' => true, 'cash_start' => (float) $shift->cash_start, 'cash_sales' => $sales, 'cash_expected' => $shift->cash_start + $sales]);
    }

    public function close(Request $request)
    {
        $data = $request->validate(['cash_actual' => 'required|numeric|min:0|max:9999999999', 'notes' => 'nullable|string|max:2000']);

        return DB::transaction(function () use ($data) {
            Tenant::lockForUpdate()->findOrFail(Auth::user()->tenant_id);
            $shift = $this->activeShift(true);
            if (! $shift) {
                return response()->json(['success' => false, 'message' => 'Tidak ada shift aktif.'], 404);
            }
            $expected = $shift->cash_start + $this->cashSales($shift);
            $shift->update(['end_time' => now(), 'cash_expected' => $expected, 'cash_actual' => $data['cash_actual'], 'cash_difference' => $data['cash_actual'] - $expected, 'status' => 'closed', 'notes' => $data['notes'] ?? null]);
            session()->forget('active_shift_id');

            return response()->json(['success' => true, 'message' => 'Shift berhasil ditutup!']);
        });
    }
}
