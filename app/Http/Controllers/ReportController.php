<?php

namespace App\Http\Controllers;

use App\Jobs\ExportReportJob;
use App\Models\Order;
use App\Models\ReportExport;
use App\Models\Shift;
use App\Services\BusinessOverview;
use App\Services\CashPeriodSummary;
use App\Services\GeminiService;
// Tambahkan import service AI Anda
use App\Services\SalesDataset;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    protected $gemini;

    // Tambahkan constructor untuk inject GeminiService
    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function index(Request $request)
    {
        $request->validate(['start_date' => 'nullable|date_format:Y-m-d', 'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date']);
        $tenantId = auth()->user()->tenant_id;
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->endOfMonth()->toDateString();

        // 1. Query Dasar Order Lunas
        $ordersQuery = app(SalesDataset::class)->between($tenantId, $startDate.' 00:00:00', $endDate.' 23:59:59');

        // 2. Sales Summary (Total Transaksi, Net Sales, Diskon, Pajak)
        $salesSummary = (object) [
            'total_gross' => (float) $ordersQuery->sum('subtotal'),
            'total_discount' => (float) (clone $ordersQuery)->selectRaw('SUM(discount + loyalty_discount) as total')->value('total'),
            'total_tax' => (float) $ordersQuery->sum('tax'),
            'total_net' => (float) $ordersQuery->sum('grand_total'),
            'total_transactions' => $ordersQuery->count(),
        ];

        // 3. Breakdown Metode Pembayaran (Cash vs QRIS) -> Solusi Error $paymentMethods
        $paymentMethods = Order::select('payment_method', DB::raw('SUM(grand_total) as total_amount'))
            ->selectRaw("SUM(CASE WHEN payment_method = 'midtrans' THEN ROUND(grand_total * ?, 0) ELSE 0 END) as platform_fee", [config('platform.commission_rate', 0.015)])
            ->where('tenant_id', $tenantId)
            ->where('payment_status', 'paid')->where('order_status', 'completed')
            ->whereBetween(DB::raw('COALESCE(sold_at, created_at)'), [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->groupBy('payment_method')
            ->get();

        // Unknown historic costs stay unknown; current catalog prices never rewrite history.
        // Join the filtered orders directly. A whereIn(subquery) here can fail on
        // older MySQL/MariaDB versions when the query is wrapped by aggregate calls.
        $costItems = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.payment_status', 'paid')
            ->where('orders.order_status', 'completed')
            ->whereBetween(DB::raw('COALESCE(orders.sold_at, orders.created_at)'), [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        $missingCosts = (clone $costItems)->whereNull('order_items.unit_cost')->count();
        $totalHpp = $missingCosts ? null : (float) (clone $costItems)->selectRaw('SUM(order_items.quantity * order_items.unit_cost) as cost')->value('cost');
        $totalPlatformFee = (float) $paymentMethods->sum('platform_fee');
        $storeNetSales = $salesSummary->total_net - $totalPlatformFee;
        $revenueBeforeTax = $salesSummary->total_gross - $salesSummary->total_discount;
        $netProfit = $totalHpp === null ? null : $revenueBeforeTax - $totalPlatformFee - $totalHpp;

        // 5. Top 5 Produk Terlaris
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.subtotal) as total_sales'))
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.payment_status', 'paid')->where('orders.order_status', 'completed')
            ->whereBetween(DB::raw('COALESCE(orders.sold_at, orders.created_at)'), [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 6. Audit Shift Kasir
        $shifts = Shift::where('tenant_id', $tenantId)
            ->whereBetween('start_time', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->with('user')
            ->latest()
            ->get();

        // 7. Data Grafik Tren Penjualan
        $dailySales = Order::select(DB::raw('DATE(COALESCE(sold_at, created_at)) as date'), DB::raw('SUM(grand_total) as total'))
            ->where('tenant_id', $tenantId)
            ->where('payment_status', 'paid')->where('order_status', 'completed')
            ->whereBetween(DB::raw('COALESCE(sold_at, created_at)'), [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->groupBy(DB::raw('DATE(COALESCE(sold_at, created_at))'))
            ->orderBy('date', 'asc')
            ->get();

        $chartLabels = $dailySales->pluck('date')->map(fn ($d) => date('d M', strtotime($d)))->toArray();
        $chartValues = $dailySales->pluck('total')->toArray();

        // 8. Tabel Transaksi Lunas dengan Pagination
        $orders = $ordersQuery->with(['customer', 'user'])->latest()->paginate(15);

        $cashSummary = app(CashPeriodSummary::class)->forTenant($tenantId, Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay());

        $overview = app(BusinessOverview::class)->forTenant($tenantId, Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay());

        return view('reports.index', compact('overview',
            'cashSummary', 'missingCosts', 'totalPlatformFee', 'storeNetSales', 'revenueBeforeTax',
            'startDate',
            'endDate',
            'salesSummary',
            'paymentMethods',
            'totalHpp',
            'netProfit',
            'topProducts',
            'shifts',
            'chartLabels',
            'chartValues',
            'orders'
        ));
    }

    public function exportExcel(Request $request)
    {
        $request->validate(['start_date' => 'nullable|date_format:Y-m-d', 'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date']);
        $startDate = $request->get('start_date', now()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // Buat data rekam berkas berstatus pending di database
        $reportExport = ReportExport::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'report_type' => 'Laporan Ringkasan Finansial',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'pending',
        ]);

        // Kirim ID ke antrean Job
        ExportReportJob::dispatch($startDate, $endDate, $reportExport->id);

        // Alihkan langsung ke halaman daftar unduhan laporan
        return redirect()->route('reports.exports-list')->with('success', 'Permintaan laporan berhasil dibuat dan sedang diproses di latar belakang.');
    }

    // 2. Tambahkan fungsi baru untuk melihat riwayat daftar unduhan
    public function exportList()
    {
        $exports = ReportExport::where('tenant_id', auth()->user()->tenant_id)->where('user_id', auth()->id())
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('reports.exports-list', compact('exports'));
    }

    // 3. Tambahkan fungsi unduh file fisik dari folder storage aman
    public function downloadFile($id)
    {
        $export = ReportExport::where('tenant_id', auth()->user()->tenant_id)->where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        if ($export->status !== 'completed' || ! Storage::disk('local')->exists($export->file_path)) {
            return redirect()->back()->with('error', 'File laporan belum selesai diproses atau tidak ditemukan.');
        }

        return Storage::disk('local')->download($export->file_path);
    }

    public function getExportsStatusJson()
    {
        // Ambil data id, status, dan link download untuk laporan milik user aktif
        $exports = ReportExport::where('tenant_id', auth()->user()->tenant_id)->where('user_id', auth()->id())
            ->orderBy('id', 'desc')
            ->take(10) // Cek 10 data teratas saja demi efisiensi performa server
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'status' => $item->status,
                    'download_url' => route('reports.download-file', $item->id),
                ];
            });

        return response()->json($exports);
    }
}
