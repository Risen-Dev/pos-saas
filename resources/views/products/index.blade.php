<x-app-layout>
    @section('title', 'Inventaris Produk')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop" x-data="{ showDeleteModal: false, deleteUrl: '', productName: '' }">

        @php
            $productCount = $catalogTotal;
            $currentPlan = auth()->user()->tenant?->currentPlan();
            $maxProducts = $currentPlan?->max_products ?? 100;
            $isProductFull = $productCount >= $maxProducts;
        @endphp

        <!-- Banner Flash Notification -->
        @if (session('success'))
            <div
                class="flex items-center gap-3 p-4 mb-6 text-sm font-medium border-l-4 rounded-md shadow-sm bg-primary-50 border-primary-600 text-ink-900">
                <x-icon class="text-base fa-solid fa-circle-check text-primary-600" />
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div
                class="flex items-center gap-3 p-4 mb-6 text-sm font-medium border-l-4 rounded-md shadow-sm bg-red-50 border-semantic-danger text-ink-900">
                <x-icon class="text-base fa-solid fa-circle-exclamation text-semantic-danger" />
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Banner Kuota Produk Real-Time -->
        <div
            class="flex flex-col justify-between gap-3 p-4 mb-6 border rounded-lg shadow-sm sm:flex-row sm:items-center bg-surface-0 border-border-200">
            <div class="flex items-center gap-3">
                <div
                    class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-50 text-primary-600 shrink-0">
                    <x-icon class="fa-solid fa-boxes-stacked" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-ink-900">Kuota Katalog Produk (Paket
                        {{ $currentPlan?->name ?? 'Starter' }})</h4>
                    <p class="text-[11px] text-ink-700 mt-0.5">
                        Terpakai <strong
                            class="{{ $isProductFull ? 'text-semantic-danger' : 'text-primary-600' }}">{{ $productCount }}</strong>
                        dari maksimal <strong class="text-ink-900">{{ $maxProducts }}</strong> produk terdaftar.
                    </p>
                </div>
            </div>

            @if ($isProductFull)
                <a href="{{ route('billing.index') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-md transition shrink-0">
                    <x-icon class="fa-solid fa-arrow-up-right-from-square text-[10px]" />
                    <span>Upgrade Kuota Produk</span>
                </a>
            @endif
        </div>

        <!-- Header Halaman & Action Button -->
        <div
            class="flex flex-col justify-between gap-4 pb-6 mb-8 border-b sm:flex-row sm:items-center border-border-200">
            <div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                    Daftar Produk & Stok
                </h1>
                <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                    Kelola item jualan, harga, varian produk, dan ketersediaan stok fisik di kasir.
                </p>
            </div>

            @if ($isProductFull)
                <button disabled title="Kuota produk paket {{ $currentPlan?->name }} sudah penuh"
                    class="inline-flex items-center justify-center gap-2 px-5 text-xs font-semibold border rounded-md cursor-not-allowed text-ink-400 bg-surface-100 border-border-200 h-11 opacity-60 font-body md:text-sm shrink-0">
                    <x-icon class="text-xs fa-solid fa-lock" />
                    <span>Kuota Produk Penuh</span>
                </button>
            @else
                <a href="{{ route('products.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body md:text-sm shrink-0">
                    <x-icon class="text-xs fa-solid fa-plus" />
                    <span>Tambah Produk Baru</span>
                </a>
            @endif
        </div>

        <!-- Metric Stat Cards Produk & Stok -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-3 md:gap-6">
            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Total Item
                        Produk</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-ink-900">
                        {{ $catalogTotal }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-primary-50 text-primary-600 shrink-0">
                    <x-icon class="fa-solid fa-box-archive" />
                </div>
            </div>

            <!-- Metric Stok Aman -->
            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Stok Aman /
                        Unlimited</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-primary-600">
                        {{ $safeCount }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-primary-100 text-primary-700 shrink-0">
                    <x-icon class="fa-solid fa-boxes-stacked" />
                </div>
            </div>

            <!-- Metric Stok Menipis / Habis (Hanya untuk produk yang dilacak stoknya) -->
            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Stok Menipis /
                        Habis</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-semantic-danger">
                        {{ $lowCount }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-red-50 text-semantic-danger shrink-0">
                    <x-icon class="fa-solid fa-triangle-exclamation" />
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap items-end gap-3 p-4 mb-5 border rounded-lg bg-white border-border-200">
            <label class="flex-1 min-w-0 text-sm font-semibold">Cari produk<input type="search" name="search" value="{{ request('search') }}" placeholder="Nama, SKU, atau barcode" class="block w-full mt-1 rounded-md border-border-200"></label>
            <label class="text-sm font-semibold">Tipe<select name="type" class="block mt-1 rounded-md border-border-200"><option value="">Semua tipe</option><option value="product" @selected(request('type')==='product')>Barang</option><option value="service" @selected(request('type')==='service')>Jasa</option></select></label>
            <label class="text-sm font-semibold">Status<select name="status" class="block mt-1 rounded-md border-border-200"><option value="">Semua status</option><option value="active" @selected(request('status')==='active')>Aktif</option><option value="inactive" @selected(request('status')==='inactive')>Nonaktif</option><option value="low" @selected(request('status')==='low')>Stok menipis</option></select></label>
            <button class="px-4 h-11 font-semibold text-white rounded-md bg-primary-600">Cari</button><a href="{{ route('products.index') }}" class="inline-flex items-center h-11 px-3 underline">Reset</a>
        </form>
        <p class="mb-3 text-sm text-ink-700">Menampilkan {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} hasil. Ringkasan di atas mencakup seluruh katalog.</p>
        <!-- Table Container -->
        <div class="mb-6 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr
                            class="h-12 text-xs font-semibold tracking-wider uppercase border-b bg-surface-100 border-border-200 font-heading text-ink-700">
                            <th class="px-5 py-3">Detail Produk & SKU</th>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3 text-right">Harga Jual</th>
                            <th class="px-5 py-3 text-center">Status Stok</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y font-body md:text-sm text-ink-900 divide-border-200">
                        @forelse ($products as $product)
                            <tr class="h-12 transition-colors hover:bg-surface-100/60">

                                <!-- Product Detail -->
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}"
                                                alt="{{ $product->product_name }}"
                                                class="object-cover border rounded-md w-9 h-9 border-border-200 shrink-0">
                                        @else
                                            <div
                                                class="flex items-center justify-center text-xs font-bold rounded-md w-9 h-9 bg-primary-100 text-primary-700 font-heading shrink-0">
                                                {{ strtoupper(substr($product->product_name, 0, 2)) }}
                                            </div>
                                        @endif

                                        <div class="min-w-0">
                                            <span class="block font-semibold leading-tight truncate text-ink-900">
                                                {{ $product->product_name }}
                                            </span>
                                            <span
                                                class="font-mono text-[11px] font-normal text-ink-400 mt-0.5 block truncate">
                                                SKU: {{ $product->sku }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-surface-100 border border-border-200 text-ink-700">
                                        {{ $product->category->name ?? 'Uncategorized' }}
                                    </span>
                                </td>

                                <!-- Sell Price -->
                                <td class="px-5 py-3 font-mono font-semibold text-right text-ink-900">
                                    Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                                </td>

                                <!-- Status Stok Badge PERBAIKAN -->
                                <!-- Status Stok Badge PERBAIKAN -->
                                <td class="px-5 py-3 text-center">
                                    @if (($product->type ?? 'product') === 'service')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-accent-700 bg-accent-100 rounded-full">
                                            Layanan / Jasa
                                        </span>
                                    @elseif (empty($product->manage_stock))
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full">
                                            <x-icon class="fa-solid fa-infinity text-[10px] mr-1" /> Unlimited
                                        </span>
                                    @elseif ($product->stock <= $product->min_stock)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold text-semantic-danger bg-red-50 rounded-full">
                                            <x-icon class="fa-solid fa-circle-exclamation text-[10px]" />
                                            {{ \App\Support\NumberFormat::quantity($product->stock) }} {{ $product->base_unit ?: 'pcs' }} (Menipis)
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full">
                                            {{ \App\Support\NumberFormat::quantity($product->stock) }} {{ $product->base_unit ?: 'pcs' }}
                                        </span>
                                    @endif
                                </td>
                                <!-- Action -->
                                <td class="px-5 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if ($product->barcode)
                                            <a href="{{ route('products.label', $product) }}" target="_blank"
                                                class="p-2 transition-colors rounded-md text-ink-700 hover:text-primary-600 bg-surface-100 hover:bg-primary-50"
                                                title="Cetak Label Barcode">
                                                <x-icon class="text-xs fa-solid fa-barcode" />
                                            </a>
                                        @endif

                                        <a href="{{ route('products.edit', $product->id) }}"
                                            class="p-2 transition-colors rounded-md text-ink-700 hover:text-primary-600 bg-surface-100 hover:bg-primary-50"
                                            title="Edit Produk">
                                            <x-icon class="text-xs fa-solid fa-pen-to-square" />
                                        </a>

                                        <button type="button"
                                            @click="showDeleteModal = true; deleteUrl = '{{ route('products.destroy', $product->id) }}'; productName = '{{ addslashes($product->product_name) }}'"
                                            class="p-2 transition-colors rounded-md text-ink-700 hover:text-semantic-danger bg-surface-100 hover:bg-red-50"
                                            title="Hapus Produk">
                                            <x-icon class="text-xs fa-solid fa-trash-can" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 mb-2 rounded-full bg-primary-50 text-primary-600">
                                            <x-icon class="text-xl fa-solid fa-boxes-packing" />
                                        </div>
                                        <p class="text-sm font-semibold font-heading text-ink-900">Belum ada produk
                                            yang cocok dengan pencarian ini</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $products->links() }}

        <!-- Modal Confirm Hapus Produk -->
        <div data-dialog x-show="showDeleteModal" x-cloak
            class="[&[x-cloak]]:!hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-900/40 backdrop-blur-[2px]">
            <div class="w-full p-6 border rounded-lg shadow-lg max-w-modal-sm bg-surface-0 border-border-200"
                @click.away="showDeleteModal = false">
                <div
                    class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-50 text-semantic-danger">
                    <x-icon class="text-xl fa-solid fa-trash-can" />
                </div>
                <div class="mb-6 text-center">
                    <h3 class="text-lg font-semibold font-heading text-ink-900">Hapus Produk Ini?</h3>
                    <p class="mt-2 text-xs leading-relaxed font-body text-ink-700">
                        Apakah Anda yakin ingin menghapus produk <span class="font-semibold text-ink-900"
                            x-text="productName"></span>?
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" @click="showDeleteModal = false"
                        class="flex-1 text-xs font-semibold rounded-md h-11 bg-surface-100 text-ink-900 font-body">Batal</button>
                    <form :action="deleteUrl" method="POST" class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-full text-xs font-semibold text-white rounded-md h-11 bg-semantic-danger font-body">Ya,
                            Hapus</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
