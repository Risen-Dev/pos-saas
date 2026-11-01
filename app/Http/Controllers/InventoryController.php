<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockAdjustment;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->validate(['search' => 'nullable|string|max:100'])['search'] ?? '';
        $products = Product::with('variants')->where('type', 'product')->where('manage_stock', true)
            ->when($search, fn ($query) => $query->where(fn ($query) => $query->where('product_name', 'like', '%'.$search.'%')->orWhere('sku', 'like', '%'.$search.'%')->orWhere('barcode', 'like', '%'.$search.'%')))
            ->latest()->paginate(10)->withQueryString();

        return view('inventory.index', compact('products'));
    }

    public function quick(Request $request, StockAdjustment $stock)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'operation_key' => 'required|uuid',
            'quantity' => 'required|numeric|decimal:0,3|gt:0|max:1000000',
            'purchase_price' => 'nullable|numeric|min:0|max:100000000',
            'note' => 'nullable|string|max:255',
        ]);
        $data['type'] = 'stock_in';
        $data['note'] = $data['note'] ?? 'Tambah stok cepat';
        $stock->product($request->user(), $data);

        return back()->with('success', 'Stok masuk tersimpan. Modal rata-rata diperbarui jika harga modal diisi. Pembayaran pembelian belum dicatat sebagai pengeluaran kas.');
    }

    public function adjust(Request $request, StockAdjustment $stock)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'variant_id' => 'nullable|integer',
            'type' => 'required|in:stock_in,stock_out,adjustment',
            'quantity' => 'required|numeric|decimal:0,3|min:0|max:1000000000',
            'note' => 'required|string|max:255',
        ]);
        $stock->product($request->user(), $data);

        return back()->with('success', 'Stok kasir dan inventori berhasil diperbarui.');
    }

    public function history()
    {
        $movements = StockMovement::with(['product', 'material', 'user'])->latest()->paginate(20);

        return view('inventory.history', compact('movements'));
    }
}
