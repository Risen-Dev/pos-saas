<x-app-layout>
    @section('title', 'Edit Produk')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-modal-lg">

        <!-- Header & Breadcrumb -->
        <div class="mb-6">
            <a href="{{ route('products.index') }}"
                class="inline-flex items-center gap-2 mb-2 text-xs font-semibold font-body text-primary-600 hover:text-primary-700">
                <x-icon class="text-xs fa-solid fa-arrow-left" />
                <span>Kembali ke Inventaris Produk</span>
            </a>
            <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                Edit Produk
            </h1>
            <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                Memperbarui informasi rincian produk: <span
                    class="font-semibold text-ink-900">{{ $product->product_name }}</span>
            </p>
        </div>

        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">
            @csrf
            <details class="p-4 border rounded-lg border-border-200" @if($errors->any()) open @endif><summary class="font-semibold cursor-pointer">Satuan, kemasan & harga grosir</summary>@include('products.partials.retail')</details>
            @method('PUT')

            <!-- Section 1: Informasi Dasar -->
            <div class="p-6 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center gap-2.5 pb-4 mb-6 border-b border-border-200">
                    <div
                        class="flex items-center justify-center text-xs font-bold rounded-md w-7 h-7 bg-primary-50 text-primary-600 font-heading">
                        01
                    </div>
                    <h3 class="text-base font-semibold font-heading text-ink-900">Informasi Utama Produk</h3>
                </div>

                <!-- Preview Image Zone -->
                <div class="mb-6">
                    <label class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Foto Produk</label>
                    <label id="preview-container"
                        class="relative flex flex-col items-center justify-center w-full overflow-hidden transition-colors border-2 border-dashed rounded-md cursor-pointer h-44 border-border-200 bg-surface-100/50 hover:bg-surface-100">

                        <div id="upload-placeholder"
                            class="flex flex-col items-center justify-center text-center p-4 {{ $product->image ? 'hidden' : '' }}">
                            <x-icon class="mb-2 text-2xl fa-solid fa-cloud-arrow-up text-ink-400" />
                            <p class="text-xs font-semibold font-body text-ink-900">Klik untuk ganti foto produk</p>
                        </div>

                        <img id="image-preview" src="{{ $product->image ? asset('storage/' . $product->image) : '' }}"
                            class="absolute inset-0 object-cover w-full h-full {{ $product->image ? '' : 'hidden' }}" />

                        <input type="file" name="image" id="image-input" class="hidden" accept="image/*"
                            onchange="previewImage(this)" />
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="product_name" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                            Nama Produk <span class="text-semantic-danger">*</span>
                        </label>
                        <input type="text" name="product_name" id="product_name"
                            value="{{ old('product_name', $product->product_name) }}" required
                            class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                    </div>

                    <div>
                        <label for="category_id" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Kategori</label>
                        <select name="category_id" id="category_id" required
                            class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="type_select" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Tipe Produk</label>
                        <select name="type" id="type_select"
                            class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                            <option value="product" {{ $product->type == 'product' ? 'selected' : '' }}>Barang Fisik
                                (Stok Dihitung)</option>
                            <option value="service" {{ $product->type == 'service' ? 'selected' : '' }}>Jasa / Layanan
                                (Non-Stok)</option>
                        </select>
                    </div>

                    <label class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11 md:col-span-2">Barcode barang (opsional, terpisah dari SKU)
                        <input name="barcode" maxlength="100" value="{{ old('barcode', $product->barcode) }}" placeholder="Pindai barcode kemasan">
                    </label>
                    <div class="md:col-span-2">
                        <label for="sku_input" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Kode SKU</label>
                        <div class="relative">
                            <input type="text" name="sku" id="sku_input" value="{{ old('sku', $product->sku) }}"
                                required
                                class="w-full pl-3 pr-24 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                            <button type="button" onclick="generateSKU()"
                                class="absolute right-1.5 top-1.5 h-8 px-3 text-[11px] font-body font-semibold text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-sm transition-colors">
                                Re-Buat otomatis
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Pricing & Stock Control -->
            <div class="p-6 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center gap-2.5 pb-4 mb-6 border-b border-border-200">
                    <div
                        class="flex items-center justify-center text-xs font-bold rounded-md w-7 h-7 bg-primary-50 text-primary-600 font-heading">
                        02
                    </div>
                    <h3 class="text-base font-semibold font-heading text-ink-900">
                        Harga & <span id="section_title">Inventaris Stok</span>
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="display_cost_price" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Harga Modal
                            (HPP)</label>
                        <input type="text" id="display_cost_price"
                            value="Rp {{ number_format($product->cost_price, 0, ',', '.') }}"
                            class="w-full px-3 font-mono text-xs font-semibold text-right transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        <input type="hidden" name="cost_price" id="cost_price" value="{{ $product->cost_price }}">
                    </div>

                    <div>
                        <label for="display_sell_price" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Harga Jual</label>
                        <input type="text" id="display_sell_price"
                            value="Rp {{ number_format($product->sell_price, 0, ',', '.') }}" required
                            class="w-full px-3 font-mono text-xs font-bold text-right transition-all border rounded-sm outline-none h-11 text-primary-600 bg-primary-50/40 border-primary-200 focus:border-primary-600">
                        <input type="hidden" name="sell_price" id="sell_price" value="{{ $product->sell_price }}">
                    </div>

                    <!-- Manage Stock Toggle Box -->
                    @if(auth()->user()->tenant->hasBusinessModule('food'))
                        <label class="block my-4"><input type="checkbox" name="requires_preparation" value="1" @checked(old('requires_preparation', $product->requires_preparation ?? (auth()->user()->tenant->businessType() === 'food')))> Kirim barang ini ke dapur / bar setelah pembayaran</label>
                    @endif
                    <div id="manage_stock_container"
                        class="p-4 border rounded-md md:col-span-2 bg-surface-100 border-border-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-semibold font-heading text-ink-900">Lacak Ketersediaan Stok</h4>
                                <p class="font-body text-[11px] text-ink-400 mt-0.5">Nonaktifkan jika barang dibuat
                                    by-order.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input aria-label="Lacak ketersediaan stok" type="checkbox" name="manage_stock" id="manage_stock" value="1"
                                    {{ $product->manage_stock ? 'checked' : '' }} class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-border-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-border-200 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600">
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Inventory Stock Fields -->
                    <div id="inventory_fields" class="grid grid-cols-1 gap-4 md:col-span-2 md:grid-cols-2">
                        <div>
                            <label for="stock_input" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Jumlah Stok Saat
                                Ini</label>
                            <input type="number" step="0.001" id="stock_input" readonly aria-describedby="stock-help" value="{{ $product->
                            <p id="stock-help" class="mt-2 text-xs text-ink-700">Stok tidak berubah saat menyimpan katalog. <a class="font-semibold underline" href="{{ route('inventory.index') }}">Ubah stok melalui inventori</a>.</p>stock }}"
                                class="w-full px-3 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        </div>
                        <div>
                            <label for="min_stock_input" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Batas Minimum
                                Warning</label>
                            <input type="number" name="min_stock" step="0.001" id="min_stock_input"
                                value="{{ $product->min_stock }}"
                                class="w-full px-3 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div
                class="flex items-center justify-between p-4 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ $product->is_active ? 'checked' : '' }}
                        class="w-4 h-4 rounded text-primary-600 border-border-200 focus:ring-primary-600">
                    <span class="text-xs font-semibold font-body text-ink-900">Produk Status Aktif</span>
                </label>

                <div class="flex items-center gap-2">
                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center justify-center px-5 text-xs font-semibold transition-colors rounded-md h-11 bg-surface-100 hover:bg-border-200 text-ink-900 font-body">
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center px-6 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body md:text-sm">
                        Update Produk
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            const placeholder = document.getElementById('upload-placeholder');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function generateSKU() {
            const name = document.getElementById('product_name').value;
            const cat = document.getElementById('category_id').value;
            if (!name) return alert('Ketik nama produk terlebih dahulu');
            const res =
                `${name.substring(0,3).toUpperCase()}-${(cat || '0').padStart(2,'0')}-${Math.floor(1000+Math.random()*9000)}`;
            document.getElementById('sku_input').value = res;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type_select');
            const manageStock = document.getElementById('manage_stock');
            const inventoryFields = document.getElementById('inventory_fields');
            const manageStockContainer = document.getElementById('manage_stock_container');
            const sectionTitle = document.getElementById('section_title');

            function updateUI() {
                if (typeSelect.value === 'service') {
                    sectionTitle.innerText = 'Layanan Jasa';
                    manageStockContainer.classList.add('hidden');
                    inventoryFields.classList.add('hidden');
                } else {
                    sectionTitle.innerText = 'Inventaris Stok';
                    manageStockContainer.classList.remove('hidden');
                    if (manageStock.checked) {
                        inventoryFields.classList.remove('hidden');
                    } else {
                        inventoryFields.classList.add('hidden');
                    }
                }
            }

            typeSelect.addEventListener('change', updateUI);
            manageStock.addEventListener('change', updateUI);

            const formatRupiah = (angka) => {
                let number_string = angka.replace(/[^0-9]/g, '');
                let sisa = number_string.length % 3;
                let rupiah = number_string.substr(0, sisa);
                let ribuan = number_string.substr(sisa).match(/\d{3}/gi);
                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return rupiah ? 'Rp ' + rupiah : '';
            };

            ['cost', 'sell'].forEach(key => {
                const display = document.getElementById(`display_${key}_price`);
                const real = document.getElementById(`${key}_price`);
                display.addEventListener('input', function() {
                    real.value = this.value.replace(/[^0-9]/g, '');
                    this.value = formatRupiah(this.value);
                });
            });

            updateUI();
        });
    </script>
</x-app-layout>
