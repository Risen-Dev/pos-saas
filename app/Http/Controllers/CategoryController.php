<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        // Filter hanya kategori milik bisnis (tenant) user yang sedang login
        $categories = Category::where('tenant_id', auth()->user()->tenant_id)
            ->withCount('products')
            ->latest()
            ->paginate(10);

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Category::create([
            // Tambahkan baris ini untuk mengambil ID bisnis user yang sedang login
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name).'-'.rand(100, 999),
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan');
    }

    public function destroy(Category $category)
    {
        // VALIDASI: Pastikan kategori ini milik user yang sedang login
        if ($category->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki produk.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Kategori dihapus');
    }
}
