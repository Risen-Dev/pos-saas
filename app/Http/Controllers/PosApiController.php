<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosApiController extends Controller
{
    public function getRecommendation(Request $request)
    {
        $productId = $request->product_id;
        $tenantId = auth()->user()->tenant_id;

        if (! $productId) {
            return response()->json([]);
        }

        // Cari ID order/nota yang di dalamnya terdapat productId ini
        $relatedOrderIds = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.payment_status', 'paid')
            ->where('orders.order_status', 'completed')
            ->where('order_items.product_id', $productId)
            ->pluck('order_items.order_id');

        // Cari produk LAIN yang berada di dalam daftar nota di atas
        $recommendations = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('products.tenant_id', $tenantId)
            ->where('products.is_active', true)
            ->whereIn('order_items.order_id', $relatedOrderIds)
            ->where('order_items.product_id', '!=', $productId) // Kecualikan produk itu sendiri
            ->select('products.id', 'products.product_name', 'products.sell_price', DB::raw('COUNT(*) as kerapatan_dibeli'))
            ->groupBy('products.id', 'products.product_name', 'products.sell_price')
            ->orderBy('kerapatan_dibeli', 'desc')
            ->take(2) // Ambil 2 produk teratas saja sebagai saran pendamping
            ->get();

        return response()->json($recommendations);
    }
}
