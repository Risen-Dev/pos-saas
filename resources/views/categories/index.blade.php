<x-app-layout>
    @section('title', 'Kategori Produk')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop" x-data="{ showDeleteModal: false, deleteUrl: '', categoryName: '' }">

        <!-- Header Halaman -->
        <div class="pb-6 mb-8 border-b border-border-200">
            <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                Kategori Produk
            </h1>
            <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                Kelola pengelompokan menu & item barang untuk mempercepat navigasi pencarian di Kasir.
            </p>
        </div>

        <!-- 2 Column Grid Layout -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Column 1: Form Tambah Kategori Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky p-6 border rounded-lg shadow-sm bg-surface-0 border-border-200 top-6">
                    <div class="flex items-center gap-2.5 pb-4 mb-4 border-b border-border-200">
                        <div
                            class="flex items-center justify-center w-8 h-8 text-xs font-bold rounded-md bg-primary-50 text-primary-600 font-heading">
                            <x-icon class="text-xs fa-solid fa-folder-plus" />
                        </div>
                        <h3 class="text-base font-semibold font-heading text-ink-900">Tambah Kategori</h3>
                    </div>

                    <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="field-name" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                                Nama Kategori <span class="text-semantic-danger">*</span>
                            </label>
                            <input id="field-name" type="text" name="name" placeholder="Contoh: Minuman Dingin, Espresso" required
                                class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                        </div>

                        <button type="submit"
                            class="inline-flex items-center justify-center w-full gap-2 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body">
                            <x-icon class="text-xs fa-solid fa-check" />
                            <span>Simpan Kategori</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Column 2: Data Table Kategori -->
            <div class="lg:col-span-2">
                <div class="mb-4 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
                    <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr
                                    class="h-12 text-xs font-semibold tracking-wider uppercase border-b bg-surface-100 border-border-200 font-heading text-ink-700">
                                    <th class="px-5 py-3">Nama Kategori & Slug</th>
                                    <th class="px-5 py-3 text-center">Jumlah Produk</th>
                                    <th class="px-5 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs divide-y font-body md:text-sm text-ink-900 divide-border-200">
                                @forelse($categories as $category)
                                    <tr class="h-12 transition-colors hover:bg-surface-100/60">

                                        <!-- Name & Slug -->
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex items-center justify-center w-8 h-8 rounded-md bg-primary-100 text-primary-700 shrink-0">
                                                    <x-icon class="text-xs fa-solid fa-layer-group" />
                                                </div>
                                                <div class="min-w-0">
                                                    <span
                                                        class="block font-semibold leading-tight truncate text-ink-900">
                                                        {{ $category->name }}
                                                    </span>
                                                    <span
                                                        class="font-mono text-[11px] font-normal text-ink-400 mt-0.5 block truncate">
                                                        /{{ $category->slug }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Items Counter Badge (Pill Shape: radius-full) -->
                                        <td class="px-5 py-3 text-center">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full">
                                                {{ $category->products_count }} Item
                                            </span>
                                        </td>

                                        <!-- Action Column -->
                                        <td class="px-5 py-3 text-center">
                                            <button type="button"
                                                @click="showDeleteModal = true; deleteUrl = '{{ route('categories.destroy', $category) }}'; categoryName = '{{ addslashes($category->name) }}'"
                                                class="p-2 transition-colors rounded-md text-ink-700 hover:text-semantic-danger bg-surface-100 hover:bg-red-50"
                                                title="Hapus Kategori">
                                                <x-icon class="text-xs fa-solid fa-trash-can" />
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <!-- Empty State -->
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div
                                                    class="flex items-center justify-center w-12 h-12 mb-2 rounded-full bg-primary-50 text-primary-600">
                                                    <x-icon class="text-xl fa-solid fa-folder-open" />
                                                </div>
                                                <p class="text-sm font-semibold font-heading text-ink-900">Belum ada
                                                    kategori produk</p>
                                                <p class="font-body text-xs text-ink-700 mt-0.5 max-w-xs">
                                                    Gunakan form di sebelah kiri untuk mendaftarkan grup kategori
                                                    pertama Anda.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Bar -->
                @if ($categories->hasPages())
                    <div class="px-1">
                        {{ $categories->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal Confirm Hapus Kategori -->
        <div data-dialog x-show="showDeleteModal" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="[&[x-cloak]]:!hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-900/40 backdrop-blur-[2px]">

            <div class="w-full p-6 border rounded-lg shadow-lg max-w-modal-sm bg-surface-0 border-border-200"
                @click.away="showDeleteModal = false">

                <div
                    class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-50 text-semantic-danger">
                    <x-icon class="text-xl fa-solid fa-folder-minus" />
                </div>

                <div class="mb-6 text-center">
                    <h3 class="text-lg font-semibold font-heading text-ink-900">Hapus Kategori Ini?</h3>
                    <p class="mt-2 text-xs leading-relaxed font-body text-ink-700">
                        Apakah Anda yakin ingin menghapus kategori <span class="font-semibold text-ink-900"
                            x-text="categoryName"></span>? Produk yang terhubung dengan kategori ini tidak akan
                        terhapus.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" @click="showDeleteModal = false"
                        class="flex-1 text-xs font-semibold transition-colors rounded-md h-11 bg-surface-100 hover:bg-border-200 text-ink-900 font-body">
                        Batal
                    </button>

                    <form :action="deleteUrl" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full text-xs font-semibold text-white transition-colors rounded-md h-11 bg-semantic-danger hover:bg-red-700 font-body">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
