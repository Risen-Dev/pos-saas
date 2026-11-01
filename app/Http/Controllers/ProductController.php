<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Services\Code128Barcode;
use App\Services\RetailCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// <--- PASTIKAN IMPORT INI ADA DI ATAS

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['search' => 'nullable|string|max:100', 'type' => 'nullable|in:product,service', 'status' => 'nullable|in:active,inactive,low']);
        $base = Product::where('tenant_id', $request->user()->tenant_id);
        $catalogTotal = (clone $base)->count();
        $lowCount = (clone $base)->where('type', 'product')->where('manage_stock', true)->whereColumn('stock', '<=', 'min_stock')->count();
        $safeCount = $catalogTotal - $lowCount;
        $products = $base->with('category')
            ->when($data['search'] ?? null, function ($query, $search) {
                $query->where(fn ($query) => $query->where('product_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")->orWhere('barcode', 'like', "%{$search}%"));
            })
            ->when($data['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($data['status'] ?? null, function ($query, $status) {
                if ($status === 'low') {
                    $query->where('type', 'product')->where('manage_stock', true)->whereColumn('stock', '<=', 'min_stock');
                } else {
                    $query->where('is_active', $status === 'active');
                }
            })->latest('id')->paginate(10)->withQueryString();

        return view('products.index', compact('products', 'catalogTotal', 'lowCount', 'safeCount'));
    }

    public function create()
    {
        $categories = Category::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name', 'asc')
            ->get();

        return view('products.create', compact('categories'));
    }

    public function store(ProductRequest $request)
    {
        $tenant = auth()->user()->tenant;
        $plan = $tenant?->currentPlan();

        if (! $plan) {
            return back()->with('error', 'Masa langganan Anda telah habis.');
        }

        // Cek Batas Maksimal Produk
        $currentProductCount = Product::where('tenant_id', $tenant->id)->count();

        if ($currentProductCount >= $plan->max_products) {
            return back()->with('error', "Gagal menambah produk! Paket {$plan->name} dibatasi maksimal {$plan->max_products} produk. Silakan upgrade paket Anda di menu Billing.");
        }
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }
        DB::transaction(function () use ($request, $tenant, $plan, $imagePath) {
            Tenant::whereKey($tenant->id)->lockForUpdate()->firstOrFail();
            abort_if(Product::where('tenant_id', $tenant->id)->count() >= $plan->max_products, 422, 'Batas produk paket tercapai.');
            $catalog = $request->catalogData();
            if ($request->filled('category_name') || empty($catalog['category_id'])) {
                $category = Category::firstOrCreate(['tenant_id' => $tenant->id, 'name' => $request->input('category_name') ?: 'Umum'], ['slug' => 'kategori-'.Str::uuid()]);
                $catalog['category_id'] = $category->id;
            }
            $product = Product::create(array_merge($catalog, ['tenant_id' => $tenant->id, 'image' => $imagePath]));
            app(RetailCatalog::class)->saveUnits($product, $request->validated('units', []));
        });

        $tenantId = auth()->user()->tenant_id;
        Cache::forget("tenant_{$tenantId}_products_pos");

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan');
    }

    public function label(Product $product)
    {
        abort_unless($product->tenant_id === auth()->user()->tenant_id, 403);

        if (blank($product->barcode)) {
            return back()->withErrors(['barcode' => 'Isi barcode produk terlebih dahulu sebelum mencetak label.']);
        }

        try {
            $barcodeSvg = app(Code128Barcode::class)->svg($product->barcode);
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['barcode' => $exception->getMessage()]);
        }

        return view('products.label', compact('product', 'barcodeSvg'));
    }

    public function edit(Product $product)
    {
        // Security check
        if ($product->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $categories = Category::where('tenant_id', auth()->user()->tenant_id)->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        if ($product->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $data = $request->catalogData();
        if (empty($data['category_id'])) {
            $data['category_id'] = $product->category_id;
        }
        // Stock is changed only through inventory operations, never a stale catalog form.
        unset($data['stock']);

        if ($request->hasFile('image')) {
            // Hapus gambar lama dari storage jika ada produk mengunggah gambar baru
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($product, $data, $request) {
            Tenant::whereKey($product->tenant_id)->lockForUpdate()->firstOrFail();
            $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $locked->update($data);
            if ($request->has('units_present')) {
                app(RetailCatalog::class)->saveUnits($locked, $request->validated('units', []));
            }

        }, 3);
        Cache::forget("tenant_{$product->tenant_id}_products_pos");

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui');
    }

    /**
     * Tambahan: Fungsi Delete
     */
    public function destroy(Product $product)
    {
        // Security check
        if ($product->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($product->orderItems()->exists() || $product->stockMovements()->exists()) {
            return back()->withErrors(['product' => 'Produk memiliki riwayat transaksi/stok. Nonaktifkan produk agar riwayat tetap utuh.']);
        }

        // 1. Hapus gambar dari storage agar tidak memenuhi server
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // 2. Hapus data dari database
        $product->delete();
        Cache::forget("tenant_{$product->tenant_id}_products_pos");

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus');
    }
}
