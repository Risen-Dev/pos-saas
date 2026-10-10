<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stocktake;
use App\Models\Tenant;
use App\Services\RetailQuantity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StocktakeController extends Controller
{
    public function index(Request $request)
    {
        $stocktakes = Stocktake::where('tenant_id', $request->user()->tenant_id)->latest()->paginate(20);

        return view('stocktakes.index', compact('stocktakes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['note' => 'nullable|string|max:1000']);
        $tenant = $request->user()->tenant_id;
        $stocktake = DB::transaction(function () use ($request, $data, $tenant) {
            $stocktake = Stocktake::create(['tenant_id' => $tenant, 'created_by' => $request->user()->id, 'number' => 'SO-'.now()->format('Ymd').'-'.Str::ulid(), 'note' => $data['note'] ?? null]);
            Product::where('tenant_id', $tenant)->where('type', 'product')
                ->where('manage_stock', true)->where('is_active', true)
                ->select(['id', 'stock'])->chunkById(500, function ($products) use ($stocktake) {
                    $timestamp = now();
                    $stocktake->items()->insert($products->map(fn ($product) => [
                        'stocktake_id' => $stocktake->id,
                        'product_id' => $product->id,
                        'system_quantity' => $product->stock,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ])->all());
                });

            return $stocktake;
        });

        return redirect()->route('stocktakes.show', $stocktake);
    }

    public function show(Request $request, Stocktake $stocktake)
    {
        abort_unless($stocktake->tenant_id === $request->user()->tenant_id, 404);
        $stocktake->load('items.product');

        return view('stocktakes.show', compact('stocktake'));
    }

    public function complete(Request $request, Stocktake $stocktake)
    {
        abort_unless($stocktake->tenant_id === $request->user()->tenant_id, 404);
        $data = $request->validate(['counts' => 'required|array', 'counts.*' => 'required|numeric|min:0|max:1000000000|decimal:0,3']);
        DB::transaction(function () use ($request, $stocktake, $data) {
            Tenant::whereKey($request->user()->tenant_id)->lockForUpdate()->firstOrFail();
            $stocktake = Stocktake::lockForUpdate()->findOrFail($stocktake->id);
            abort_if($stocktake->status !== 'open', 422, 'Stock opname sudah selesai.');
            $stocktake->load('items');
            foreach ($stocktake->items as $line) {
                $count = (float) ($data['counts'][$line->id] ?? -1);
                abort_if($count < 0, 422, 'Semua produk harus dihitung.');
                $product = Product::where('tenant_id', $request->user()->tenant_id)->whereKey($line->product_id)->lockForUpdate()->firstOrFail();
                RetailQuantity::requireWhole($count, $product->allow_fraction);
                $before = (float) $product->stock;
                $product->update(['stock' => $count]);
                $line->update(['counted_quantity' => $count]);
                if (abs($count - $before) > 0.0001) {
                    $product->stockMovements()->create(['tenant_id' => $request->user()->tenant_id, 'user_id' => $request->user()->id, 'type' => 'adjustment', 'quantity' => abs($count - $before), 'before_stock' => $before, 'after_stock' => $count, 'note' => 'Stock opname '.$stocktake->number, 'reference_type' => 'stocktake', 'reference_id' => $stocktake->id]);
                }
            }
            $stocktake->update(['status' => 'completed', 'completed_at' => now()]);
            Cache::forget('tenant_'.$request->user()->tenant_id.'_products_pos');
        });

        return redirect()->route('stocktakes.show', $stocktake)->with('success', 'Stock opname selesai dan stok telah direkonsiliasi.');
    }
}
