<x-app-layout>
    <section class="p-4 m-4 space-y-4 bg-white border rounded-lg border-border-200">
    <h1 class="text-2xl font-bold">Stok</h1>
    <p class="text-sm text-ink-700">Cari barang lalu tambah stok langsung. Pembelian bertahap dan hitung stok tersedia di menu atas.</p>
    <form method="get" action="{{ route('inventory.index') }}" class="flex gap-3">
        <label class="flex-1 text-sm">Cari barang<input type="search" name="search" value="{{ request('search') }}" placeholder="Nama, SKU, atau barcode" class="block w-full mt-2 rounded-md border-border-200"></label>
        <button class="self-end min-h-11 px-4 py-2 border rounded-md">Cari</button>
    </form>
    <details @if($errors->any()) open @endif>
        <summary class="min-h-11 py-3 font-semibold cursor-pointer text-primary-700">+ Tambah stok cepat</summary>
        <form method="post" action="{{ route('inventory.quick') }}" class="grid gap-4 mt-3 sm:grid-cols-2" x-data="{ saving: false }" @submit="saving = true">
            @csrf
            <input type="hidden" name="operation_key" value="{{ old('operation_key', (string) \Illuminate\Support\Str::uuid()) }}">
            <label class="text-sm">Barang dari hasil pencarian<select name="product_id" required class="block w-full mt-2 rounded-md border-border-200"><option value="">Pilih barang</option>@foreach($products as $item)@if($item->variants->isEmpty())<option value="{{ $item->id }}" @selected(old('product_id') == $item->id)>{{ $item->product_name }} · {{ $item->base_unit }}</option>@endif
@endforeach</select></label>
            <label class="text-sm">Jumlah masuk (satuan dasar)<input name="quantity" type="number" min="0.001" max="1000000" step="0.001" value="{{ old('quantity') }}" required class="block w-full mt-2 rounded-md border-border-200"></label>
            <label class="text-sm">Modal per satuan dasar (Rp, opsional)<input name="purchase_price" type="text" data-rupiah-input value="{{ old('purchase_price') }}" class="block w-full mt-2 rounded-md border-border-200"></label>
            <label class="text-sm">Catatan (opsional)<input name="note" maxlength="255" value="{{ old('note') }}" class="block w-full mt-2 rounded-md border-border-200"></label>
            <p class="text-xs text-ink-700 sm:col-span-2">Untuk barang bervarian gunakan penyesuaian per varian di tabel. Tambah stok tidak memotong uang laci; catat pembayaran tunai pembelian secara terpisah di Kas & Pengeluaran.</p>
            <button :disabled="saving" class="min-h-11 px-4 py-3 text-white rounded-md bg-primary-600 disabled:opacity-50">Simpan stok masuk</button>
        </form>
    </details>
</section>
<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-lg font-bold text-ink-900">Daftar stok barang</h2>
                <p class="font-medium text-gray-500">Pantau ketersediaan barang di gudang Anda.</p>
            </div>
            <a href="{{ route('inventory.history') }}"
                class="px-6 py-3 font-bold text-gray-700 transition bg-gray-100 rounded-2xl hover:bg-gray-200">
                Riwayat Mutasi
            </a>
        </div>

        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b border-gray-100 bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-gray-400 uppercase">Produk</th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-center text-gray-400 uppercase">
                            Stok Saat Ini</th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-center text-gray-400 uppercase">
                            Status</th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-right text-gray-400 uppercase">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($products as $product)
                        <tr class="transition hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900">{{ $product->product_name }}</p>
                                <p class="text-xs text-gray-400">SKU: {{ $product->sku }}</p>
                                @foreach($product->variants as $variant)
                                    <div class="mt-2 text-sm">{{ $variant->name }}: {{ \App\Support\NumberFormat::quantity($variant->stock) }}
                                        <button type="button" class="text-blue-700 underline" data-product-id="{{ $product->id }}" data-product-name="{{ $product->product_name }} / {{ $variant->name }}" data-variant-id="{{ $variant->id }}" onclick="openModal(this.dataset.productId, this.dataset.productName, this.dataset.variantId)">Ubah stok varian</button>
                                    </div>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="text-lg font-black {{ $product->stock <= $product->min_stock ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ \App\Support\NumberFormat::quantity($product->stock) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($product->stock <= $product->min_stock)
                                    <span
                                        class="px-3 py-1 bg-red-100 text-red-600 rounded-lg text-[10px] font-black uppercase">Stok
                                        Menipis</span>
                                @else
                                    <span
                                        class="px-3 py-1 bg-green-100 text-green-600 rounded-lg text-[10px] font-black uppercase">Aman</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button data-product-id="{{ $product->id }}" data-product-name="{{ $product->product_name }}" onclick="openModal(this.dataset.productId, this.dataset.productName)"
                                    class="text-sm font-bold text-blue-600 hover:underline">
                                    Sesuaikan stok
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    </div>

    <div data-dialog id="stockModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-[2.5rem] p-8 w-full max-w-md shadow-2xl">
            <h3 id="modalTitle" class="mb-6 text-xl font-black text-gray-900">Sesuaikan stok</h3>
            <form action="{{ route('inventory.adjust') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" id="modalProductId">
                <input type="hidden" name="variant_id" id="modalVariantId">

                <div class="space-y-4">
                    <div>
                        <label for="field-type" class="block mb-2 text-xs font-black text-gray-400 uppercase">Tipe Mutasi</label>
                        <select id="field-type" name="type"
                            class="w-full px-4 py-3 font-bold border-gray-100 rounded-2xl bg-gray-50 focus:border-blue-500">
                            <option value="stock_in">Stok Masuk (+)</option>
                            <option value="stock_out">Stok Keluar (-)</option>
                            <option value="adjustment">Hitung fisik (jumlah stok akhir)</option>
                        </select>
                    </div>
                    <div>
                        <label for="field-quantity" class="block mb-2 text-xs font-black text-gray-400 uppercase">Jumlah</label>
                        <input id="field-quantity" type="number" name="quantity" step="0.001" required min="0"
                            class="w-full px-4 py-3 font-bold border-gray-100 rounded-2xl bg-gray-50 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="field-note" class="block mb-2 text-xs font-black text-gray-400 uppercase">Catatan</label>
                        <textarea id="field-note" name="note" required maxlength="255" class="w-full px-4 py-3 text-sm border-gray-100 rounded-2xl bg-gray-50 focus:border-blue-500"
                            placeholder="Contoh: Restock bulanan atau barang rusak"></textarea>
                    </div>
                </div>

                <div class="flex gap-3 mt-8">
                    <button type="button" onclick="closeModal()"
                        class="flex-1 py-4 font-bold text-gray-500 transition bg-gray-100 rounded-2xl hover:bg-gray-200">Batal</button>
                    <button type="submit"
                        class="flex-1 py-4 font-black text-white transition bg-blue-600 shadow-lg rounded-2xl shadow-blue-100 hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id, name, variantId = '') {
            document.getElementById('modalVariantId').value = variantId;
            document.getElementById('modalProductId').value = id;
            document.getElementById('modalTitle').innerText = 'Sesuaikan stok: ' + name;
            document.getElementById('stockModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('stockModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
