<x-app-layout>
@section('title', 'Tambah katalog')
@php($tenant = auth()->user()->tenant)
<div class="max-w-3xl px-4 py-6 mx-auto sm:px-6">
    <a href="{{ route('products.index') }}" class="text-sm font-semibold text-primary-600">← Kembali ke {{ strtolower($tenant->catalogLabel()) }}</a>
    <h1 class="mt-4 text-2xl font-bold">Tambah {{ strtolower($tenant->catalogLabel()) }}</h1>
    <p class="mt-2 text-sm text-ink-700">Isi nama dan harga jual. Pengaturan lainnya bisa dilengkapi nanti.</p>
    <form method="post" action="{{ route('products.store') }}" enctype="multipart/form-data" class="mt-6 space-y-5" x-data="{ type: @js(old('type', $tenant->businessType() === 'service' ? 'service' : 'product')), tracked: @js((bool) old('manage_stock', $tenant->businessType() !== 'food' && $tenant->businessType() !== 'service')), newCategory: @js((bool) old('category_name')), saving: false }" @submit="saving = true">
        @csrf
        <section class="p-5 space-y-5 bg-white border rounded-lg border-border-200">
            <label for="product-name" class="block text-sm font-semibold">Nama {{ strtolower($tenant->catalogLabel()) }}<input id="product-name" name="product_name" value="{{ old('product_name') }}" required maxlength="255" class="block w-full mt-2 rounded-md border-border-200" autofocus></label>
            <label for="sell-price" class="block text-sm font-semibold">Harga jual (Rp)<input id="sell-price" name="sell_price" type="text" inputmode="numeric" data-rupiah-input value="{{ old('sell_price') }}" required class="block w-full mt-2 rounded-md border-border-200"></label>
            <div>
                <label for="category" class="block text-sm font-semibold">Kategori</label>
                <select id="category" name="category_id" :disabled="newCategory" class="w-full mt-2 rounded-md border-border-200">
                    <option value="">Umum</option>
                    @foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>@endforeach
                </select>
                <label class="flex items-center gap-2 mt-3 text-sm"><input type="checkbox" x-model="newCategory"> Buat kategori baru</label>
                <label x-show="newCategory" x-cloak class="block mt-3 text-sm">Nama kategori baru<input name="category_name" value="{{ old('category_name') }}" :disabled="!newCategory" :required="newCategory" maxlength="100" class="block w-full mt-2 rounded-md border-border-200"></label>
            </div>
            @if($tenant->businessType() === 'mixed' || ($tenant->hasBusinessModule('goods') && $tenant->hasBusinessModule('services')))
                <label class="block text-sm font-semibold">Jenis item<select name="type" x-model="type" class="block w-full mt-2 rounded-md border-border-200"><option value="product">Barang</option><option value="service">Layanan jasa</option></select></label>
            @else
                <input type="hidden" name="type" x-model="type">
            @endif
            <div x-show="type === 'product'">
                <label class="flex items-center gap-3 text-sm font-semibold"><input type="checkbox" name="manage_stock" value="1" x-model="tracked" :disabled="type !== 'product'"> Gunakan stok</label>
                <label x-show="tracked" class="block mt-4 text-sm">Stok awal<input name="stock" type="number" min="0" max="1000000" step="0.001" value="{{ old('stock', 0) }}" :disabled="type !== 'product' || !tracked" class="block w-full mt-2 rounded-md border-border-200"></label>
            </div>
        </section>
        <details class="p-5 bg-white border rounded-lg border-border-200" @if($errors->any()) open @endif>
            <summary class="text-sm font-semibold cursor-pointer">Pengaturan lanjutan · modal, kode, foto & satuan</summary>
            <div class="mt-5 space-y-5">
                <label class="block text-sm">Harga modal per satuan dasar (Rp)<input name="cost_price" type="text" data-rupiah-input value="{{ old('cost_price', 0) }}" class="block w-full mt-2 rounded-md border-border-200"></label>
                <label class="block text-sm">Kode SKU (otomatis jika kosong)<input name="sku" value="{{ old('sku') }}" maxlength="255" class="block w-full mt-2 rounded-md border-border-200"></label>
                <label class="block text-sm">Barcode (opsional)<input name="barcode" value="{{ old('barcode') }}" maxlength="100" class="block w-full mt-2 rounded-md border-border-200"></label>
                <label x-show="type === 'product' && tracked" class="block text-sm">Peringatan stok minimum<input name="min_stock" type="number" min="0" step="0.001" value="{{ old('min_stock', 0) }}" class="block w-full mt-2 rounded-md border-border-200"></label>
                <label class="block text-sm">Foto (opsional)<input name="image" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full mt-2"><span class="text-xs text-ink-700">JPG, PNG, atau WebP, maksimal 2 MB.</span></label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Aktifkan di kasir</label>
                @if($tenant->hasBusinessModule('food'))<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="requires_preparation" value="1" @checked(old('requires_preparation', true))> Kirim ke antrean dapur</label>@endif
                @include('products.partials.retail')
            </div>
        </details>
        <div class="flex flex-wrap gap-3"><button :disabled="saving" class="min-h-11 px-5 py-3 font-semibold text-white rounded-md bg-primary-600 disabled:opacity-50">Simpan katalog</button><a href="{{ route('products.index') }}" class="min-h-11 px-5 py-3 text-sm">Batal</a></div>
    </form>
</div>
</x-app-layout>
