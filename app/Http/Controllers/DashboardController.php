<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shift;
use App\Services\BusinessOverview;
use App\Services\CashPeriodSummary;
use App\Services\SalesDataset;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role === 'kasir') {
            return redirect()->route('pos.index');
        }
        $data = $request->validate(['start_date' => 'nullable|date_format:Y-m-d', 'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date']);
        $start = Carbon::parse($data['start_date'] ?? today()->toDateString())->startOfDay();
        $end = Carbon::parse($data['end_date'] ?? $start->toDateString())->endOfDay();
        abort_if($end->lt($start) || $start->diffInDays($end) > 366, 422, 'Pilih periode maksimal satu tahun.');
        $base = app(SalesDataset::class)->orders($request->user()->tenant_id);
        $period = (clone $base)->whereBetween(DB::raw('COALESCE(sold_at, created_at)'), [$start, $end]);
        $revenue = (float) (clone $period)->sum('grand_total');
        $orderCount = (clone $period)->count();
        $days = (int) $start->diffInDays($end->copy()->startOfDay()) + 1;
        $previousRevenue = (float) (clone $base)->whereBetween(DB::raw('COALESCE(sold_at, created_at)'), [$start->copy()->subDays($days), $start->copy()->subMicrosecond()])->sum('grand_total');
        $change = $previousRevenue > 0 ? round(($revenue - $previousRevenue) / $previousRevenue * 100, 1) : null;
        $recentOrders = (clone $period)->with(['user', 'customer'])->latest()->limit(6)->get();
        $costItems = DB::table('order_items')->whereIn('order_id', (clone $period)->select('id'));
        $missingCosts = (clone $costItems)->whereNull('unit_cost')->count();
        $grossProfit = $orderCount && ! $missingCosts ? (float) (clone $period)->selectRaw('SUM(subtotal - discount - loyalty_discount) as net')->value('net') - (float) (clone $costItems)->selectRaw('SUM(unit_cost * quantity) as cost')->value('cost') : null;
        $lowStock = Product::where('is_active', true)->where('manage_stock', true)->where('type', 'product')->whereColumn('stock', '<=', 'min_stock')->orderBy('stock')->limit(6)->get();
        $lowMaterials = Material::whereColumn('stock', '<=', 'min_stock')->orderBy('stock')->limit(6)->get();
        $shifts = Shift::where('status', 'closed')->whereBetween('end_time', [$start, $end]);
        $cashDifference = (float) (clone $shifts)->sum('cash_difference');
        $shiftIssueCount = (clone $shifts)->where('cash_difference', '!=', 0)->count();
        $shiftIssues = (clone $shifts)->where('cash_difference', '!=', 0)->with('user')->latest('end_time')->limit(3)->get();
        $paymentMethods = (clone $period)->select('payment_method')->selectRaw('SUM(grand_total) as amount')->groupBy('payment_method')->get();
        $topProducts = (clone $costItems)->select('product_name')->selectRaw('SUM(quantity) as quantity')->groupBy('product_name')->orderByDesc('quantity')->limit(5)->get();
        $hasMenu = Product::exists();
        $hasSale = Order::where('payment_status', 'paid')->exists();

        $cashSummary = app(CashPeriodSummary::class)->forTenant($request->user()->tenant_id, $start, $end);

        $overview = app(BusinessOverview::class)->forTenant($request->user()->tenant_id, $start, $end);

        return view('dashboard.index', compact('overview', 'cashSummary', 'start', 'end', 'revenue', 'orderCount', 'change', 'previousRevenue', 'recentOrders', 'grossProfit', 'missingCosts', 'lowStock', 'lowMaterials', 'cashDifference', 'shiftIssueCount', 'shiftIssues', 'paymentMethods', 'topProducts', 'hasMenu', 'hasSale'));
    }
}
