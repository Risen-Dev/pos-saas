<x-app-layout>
    @section('title', 'Daftar Pemasok')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop" x-data="{ showDeleteModal: false, deleteUrl: '', supplierName: '' }">

        <!-- Header Halaman & Action Button -->
        <div
            class="flex flex-col justify-between gap-4 pb-6 mb-8 border-b sm:flex-row sm:items-center border-border-200">
            <div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                    Daftar Pemasok & Pemasok
                </h1>
                <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                    Kelola hubungan dengan vendor bahan baku, termin pembayaran (TOP), dan info rekening bank supplier.
                </p>
            </div>

            <!-- Button Primary: Height 44px, Radius-md (10px), Emerald Green -->
            <button onclick="document.getElementById('addPemasokModal').classList.remove('hidden')"
                class="inline-flex items-center justify-center gap-2 px-5 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body md:text-sm shrink-0">
                <x-icon class="text-xs fa-solid fa-truck-field" />
                <span>Tambah Pemasok</span>
            </button>
        </div>

        <!-- Metric Stat Cards Ringkasan Vendor -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-3 md:gap-6">
            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Total Pemasok
                        Terdaftar</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-ink-900">
                        {{ $suppliers->total() }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-primary-50 text-primary-600 shrink-0">
                    <x-icon class="fa-solid fa-truck-ramp-box" />
                </div>
            </div>

            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Pembayaran Cash
                        / COD</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-primary-600">
                        {{ $suppliers->filter(fn($s) => $s->term_of_payment == 0)->count() }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-primary-100 text-primary-700 shrink-0">
                    <x-icon class="fa-solid fa-money-bill-wave" />
                </div>
            </div>

            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Termin Tempo
                        (TOP)</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-accent-500">
                        {{ $suppliers->filter(fn($s) => $s->term_of_payment > 0)->count() }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-accent-100 text-accent-700 shrink-0">
                    <x-icon class="fa-solid fa-business-time" />
                </div>
            </div>
        </div>

        <!-- Table Container (Spesifikasi GrowPOS: Row Height 48px, bg surface-100 header) -->
        <div class="mb-6 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr
                            class="h-12 text-xs font-semibold tracking-wider uppercase border-b bg-surface-100 border-border-200 font-heading text-ink-700">
                            <th class="px-5 py-3">Nama Pemasok & ID</th>
                            <th class="px-5 py-3 text-center">Termin Pembayaran (TOP)</th>
                            <th class="px-5 py-3">Kontak & Info Rekening Bank</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y font-body md:text-sm text-ink-900 divide-border-200">
                        @forelse($suppliers as $item)
                            <tr class="h-12 transition-colors hover:bg-surface-100/60">

                                <!-- Pemasok Avatar, Name & ID Code -->
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center w-8 h-8 text-xs font-bold rounded-md bg-primary-100 text-primary-700 font-heading shrink-0">
                                            {{ substr($item->name, 0, 2) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span
                                                class="block font-semibold leading-tight truncate text-ink-900">{{ $item->name }}</span>
                                            <span
                                                class="font-mono text-[11px] font-normal text-ink-400 mt-0.5 block truncate">
                                                ID: SUP-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Payment Term (TOP) Badge -->
                                <td class="px-5 py-3 text-center">
                                    @if ($item->term_of_payment > 0)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold text-accent-700 bg-accent-100 rounded-full">
                                            <x-icon class="fa-regular fa-clock text-[10px]" />
                                            {{ $item->term_of_payment }} Hari
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-ink-400 bg-surface-100 border border-border-200 rounded-full">
                                            Tunai / Cash
                                        </span>
                                    @endif
                                </td>

                                <!-- Phone & Bank Info -->
                                <td class="px-5 py-3">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-mono text-xs text-ink-900 flex items-center gap-1.5">
                                            <x-icon class="fa-brands fa-whatsapp text-semantic-success text-[11px]" />
                                            {{ $item->phone ?? 'Tidak ada WhatsApp' }}
                                        </span>
                                        @if ($item->bank_name)
                                            <span class="font-mono text-[11px] font-normal text-ink-400 block truncate">
                                                <x-icon class="fa-solid fa-building-columns text-[10px] mr-0.5" />
                                                {{ strtoupper($item->bank_name) }}: {{ $item->bank_account_number }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-5 py-3 text-center">
                                    <button type="button"
                                        @click="showDeleteModal = true; deleteUrl = '{{ route('suppliers.destroy', $item->id) }}'; supplierName = '{{ addslashes($item->name) }}'"
                                        class="p-2 transition-colors rounded-md text-ink-700 hover:text-semantic-danger bg-surface-100 hover:bg-red-50"
                                        title="Hapus Pemasok">
                                        <x-icon class="text-xs fa-solid fa-trash-can" />
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <!-- Empty State -->
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 mb-2 rounded-full bg-primary-50 text-primary-600">
                                            <x-icon class="text-xl fa-solid fa-truck-arrow-right" />
                                        </div>
                                        <p class="text-sm font-semibold font-heading text-ink-900">Belum ada supplier
                                            terdaftar</p>
                                        <p class="font-body text-xs text-ink-700 mt-0.5 max-w-xs">
                                            Daftarkan mitra vendor atau distributor untuk mempermudah pencatatan restock
                                            bahan baku.
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
        <div>
            {{ $suppliers->links() }}
        </div>

        <!-- Modal Tambah Pemasok (Max-Width 480px / max-w-modal-sm, Backdrop Blur 2px) -->
        <div data-dialog id="addPemasokModal"
            class="fixed inset-0 z-50 flex items-center justify-center hidden p-4 bg-ink-900/40 backdrop-blur-[2px]">
            <div class="w-full p-6 border rounded-lg shadow-lg bg-surface-0 max-w-modal-sm border-border-200">

                <div class="flex items-center justify-between pb-3 mb-4 border-b border-border-200">
                    <h3 class="text-lg font-semibold font-heading text-ink-900">Tambah Pemasok Baru</h3>
                    <button type="button" onclick="document.getElementById('addPemasokModal').classList.add('hidden')"
                        class="p-1 text-ink-400 hover:text-ink-900">
                        <x-icon class="text-base fa-solid fa-xmark" />
                    </button>
                </div>

                <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Nama Perusahaan / Vendor -->
                    <div>
                        <label for="field-name" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                            Nama Perusahaan / Pemasok <span class="text-semantic-danger">*</span>
                        </label>
                        <input id="field-name" type="text" name="name" required
                            placeholder="Contoh: PT Kopi Perkasa, Toko Sumber Rejeki"
                            class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                    </div>

                    <!-- No WhatsApp & TOP -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="field-phone" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">No.
                                WhatsApp</label>
                            <input id="field-phone" type="text" name="phone" placeholder="0812xxxx"
                                class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600">
                        </div>
                        <div>
                            <label for="field-term_of_payment" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Termin (TOP
                                Hari)</label>
                            <input id="field-term_of_payment" type="number" name="term_of_payment" value="0"
                                class="w-full px-3 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        </div>
                    </div>

                    <!-- Info Rekening Bank -->
                    <div class="p-3 border rounded-md bg-surface-100 border-border-200">
                        <p class="font-heading font-semibold text-[11px] text-ink-700 uppercase tracking-wider mb-2">
                            Informasi Pembayaran Bank</p>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label for="field-bank_name" class="block font-body text-[11px] font-medium text-ink-700 mb-1">Nama
                                    Bank</label>
                                <input id="field-bank_name" type="text" name="bank_name" placeholder="BCA / Mandiri"
                                    class="w-full h-10 px-3 text-xs border rounded-sm outline-none font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                            </div>
                            <div>
                                <label for="field-bank_account_number" class="block font-body text-[11px] font-medium text-ink-700 mb-1">No.
                                    Rekening</label>
                                <input id="field-bank_account_number" type="text" name="bank_account_number" placeholder="123xxxx"
                                    class="w-full h-10 px-3 font-mono text-xs border rounded-sm outline-none text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                            </div>
                        </div>
                    </div>

                    <!-- Alamat Lengkap -->
                    <div>
                        <label for="field-address" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Alamat Lengkap Kantor
                            / Gudang</label>
                        <textarea id="field-address" name="address" rows="2" placeholder="Contoh: Jl. Industri Raya No. 45, Jakarta"
                            class="w-full p-3 text-xs transition-all border rounded-sm outline-none font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3 pt-3">
                        <button type="button"
                            onclick="document.getElementById('addPemasokModal').classList.add('hidden')"
                            class="flex-1 text-xs font-semibold transition-colors rounded-md h-11 bg-surface-100 hover:bg-border-200 text-ink-900 font-body">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body">
                            Simpan Pemasok
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Confirm Hapus Pemasok -->
        <div data-dialog x-show="showDeleteModal" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="[&[x-cloak]]:!hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-900/40 backdrop-blur-[2px]">

            <div class="w-full p-6 border rounded-lg shadow-lg max-w-modal-sm bg-surface-0 border-border-200"
                @click.away="showDeleteModal = false">

                <div
                    class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-50 text-semantic-danger">
                    <x-icon class="text-xl fa-solid fa-truck-delete" />
                </div>

                <div class="mb-6 text-center">
                    <h3 class="text-lg font-semibold font-heading text-ink-900">Hapus Pemasok Ini?</h3>
                    <p class="mt-2 text-xs leading-relaxed font-body text-ink-700">
                        Apakah Anda yakin ingin menghapus data supplier <span class="font-semibold text-ink-900"
                            x-text="supplierName"></span>? Riwayat mutasi bahan baku yang terikat tidak akan terhapus.
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
