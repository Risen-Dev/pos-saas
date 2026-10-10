<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiscountRequest;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::with('products')->latest()->get();

        return view('discounts.index', compact('discounts'));
    }

    public function create()
    {
        $products = Product::where('tenant_id', auth()->user()->tenant_id)->get();

        return view('discounts.create', compact('products'));
    }

    public function store(DiscountRequest $request)
    {
        DB::transaction(function () use ($request) {
            $discount = Discount::create([
                ...$request->attributesForDiscount(),
                'tenant_id' => $request->user()->tenant_id,
            ]);
            $discount->products()->sync($request->validated('product_ids'));
        });

        return redirect()->route('discounts.index')->with('success', 'Skema diskon berhasil dibuat.');
    }

    public function edit(Discount $discount)
    {
        $discount->load('products');
        $products = Product::where('tenant_id', auth()->user()->tenant_id)->get();
        $selectedProductIds = $discount->products->pluck('id')->toArray();

        return view('discounts.edit', compact('discount', 'products', 'selectedProductIds'));
    }

    public function update(DiscountRequest $request, Discount $discount)
    {
        DB::transaction(function () use ($request, $discount) {
            $discount = Discount::lockForUpdate()->findOrFail($discount->id);
            $discount->update($request->attributesForDiscount());
            $discount->products()->sync($request->validated('product_ids'));
        });

        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil diperbarui.');
    }

    public function destroy(Discount $discount)
    {
        DB::transaction(function () use ($discount) {
            $discount = Discount::lockForUpdate()->findOrFail($discount->id);
            $discount->products()->detach();
            $discount->delete();
        });

        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil dihapus.');
    }
}
