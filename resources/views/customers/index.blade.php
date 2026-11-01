<x-app-layout>
    @section('title', 'Pelanggan & Bon')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop" x-data="{ showDeleteModal: false, deleteUrl: '', customerName: '' }">

        <!-- Header Halaman & Tombol Tambah Pelanggan -->
        <div
            class="flex flex-col justify-between gap-4 pb-6 mb-8 border-b sm:flex-row sm:items-center border-border-200">
            <div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                    Manajemen Pelanggan
                </h1>
                <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                    Kelola basis data pelanggan, program poin loyalty, dan catatan piutang (bon) usaha Anda.
                </p>
            </div>

            <!-- Button Primary: Height 44px, Radius-md (10px), Emerald Green -->
            <button onclick="document.getElementById('addCustomerModal').classList.remove('hidden')"
                class="inline-flex items-center justify-center gap-2 px-5 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body md:text-sm shrink-0">
                <x-icon class="text-xs fa-solid fa-user-plus" />
                <span>Registrasi Pelanggan</span>
            </button>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 md:gap-6">
            <!-- Total Pelanggan Card -->
            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Total Pelanggan
                        Terdaftar</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-ink-900">
                        {{ number_format($customers->total(), 0, ',', '.') }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-primary-50 text-primary-600 shrink-0">
                    <x-icon class="fa-solid fa-users" />
                </div>
            </div>

            <!-- Total Piutang (Bon) Card -->
            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Total Piutang
                        Pelanggan (Bon)</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-semantic-danger">
                        Rp {{ number_format($totalDebt, 0, ',', '.') }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-red-50 text-semantic-danger shrink-0">
                    <x-icon class="fa-solid fa-hand-holding-dollar" />
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
                            <th class="px-5 py-3">Pelanggan & WhatsApp</th>
                            @can('feature-loyalty')
                            <th class="px-5 py-3 text-center">Keanggotaan</th>
                            <th class="px-5 py-3 text-center">Poin loyalitas</th>
                            @endcan
                            <th class="px-5 py-3 text-right">Total Hutang / Bon</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y font-body md:text-sm text-ink-900 divide-border-200">
                        @forelse($customers as $c)
                            <tr class="h-12 transition-colors hover:bg-surface-100/60">

                                <!-- Customer Avatar, Name & Phone -->
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center w-8 h-8 text-xs font-bold rounded-full bg-primary-100 text-primary-700 font-heading shrink-0">
                                            {{ substr($c->name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span
                                                class="block font-semibold leading-tight truncate text-ink-900"><a class="font-semibold underline" href="{{ route('customers.show', $c) }}">{{ $c->name }}</a></span>
                                            <span
                                                class="font-mono text-[11px] font-normal text-ink-400 mt-0.5 block truncate">
                                                <x-icon
                                                    class="fa-brands fa-whatsapp text-semantic-success text-[10px] mr-0.5" />
                                                {{ $c->phone ?? 'Tidak ada kontak' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                @can('feature-loyalty')
                                <!-- Status Tier Loyalty Badge (Pill Shape: radius-full) -->
                                <td class="px-5 py-3 text-center">
                                    @if ($c->is_member)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full">
                                            <x-icon class="fa-solid fa-crown text-[10px] mr-1 text-accent-500" />
                                            Member
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-ink-400 bg-surface-100 border border-border-200 rounded-full">
                                            Reguler
                                        </span>
                                    @endif
                                </td>

                                <!-- Poin Loyalty -->
                                <td class="px-5 py-3 text-center">
                                    <span
                                        class="font-mono font-semibold text-ink-900">{{ number_format($c->points, 0, ',', '.') }}</span>
                                    <span class="text-[11px] text-ink-400 ml-0.5">pts</span>
                                </td>
                                @endcan

                                <!-- Total Debt / Bon -->
                                <td class="px-5 py-3 text-right">
                                    @if ($c->total_debt > 0)
                                        <span class="font-mono font-semibold text-semantic-danger">
                                            Rp {{ number_format($c->total_debt, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="font-mono font-medium text-ink-400">
                                            Rp 0
                                        </span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-5 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button"
                                            class="p-2 transition-colors rounded-md text-ink-700 hover:text-primary-600 bg-surface-100 hover:bg-primary-50"
                                            title="Lihat Detail Pelanggan">
                                            <x-icon class="text-xs fa-solid fa-eye" />
                                        </button>

                                        <button type="button"
                                            @click="showDeleteModal = true; deleteUrl = '{{ route('customers.destroy', $c->id) }}'; customerName = '{{ $c->name }}'"
                                            class="p-2 transition-colors rounded-md text-ink-700 hover:text-semantic-danger bg-surface-100 hover:bg-red-50"
                                            title="Hapus Data Pelanggan">
                                            <x-icon class="text-xs fa-solid fa-trash-can" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <!-- Empty State (Warm & Friendly Copy) -->
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 mb-2 rounded-full bg-primary-50 text-primary-600">
                                            <x-icon class="text-xl fa-solid fa-users-slash" />
                                        </div>
                                        <p class="text-sm font-semibold font-heading text-ink-900">Belum ada data
                                            pelanggan</p>
                                        <p class="font-body text-xs text-ink-700 mt-0.5 max-w-xs">
                                            Yuk, daftarkan pelanggan pertama Anda untuk mulai mengumpulkan poin dan
                                            mencatat transaksi!
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
            {{ $customers->links() }}
        </div>

        <!-- Modal Registrasi Pelanggan Baru (Max-Width 480px / max-w-modal-sm, Backdrop Blur 2px) -->
        <div data-dialog id="addCustomerModal"
            class="fixed inset-0 z-50 flex items-center justify-center hidden p-4 bg-ink-900/40 backdrop-blur-[2px]">
            <div class="w-full p-6 border rounded-lg shadow-lg bg-surface-0 max-w-modal-sm border-border-200">

                <div class="flex items-center justify-between pb-3 mb-4 border-b border-border-200">
                    <h3 class="text-lg font-semibold font-heading text-ink-900">Registrasi Pelanggan Baru</h3>
                    <button type="button" onclick="document.getElementById('addCustomerModal').classList.add('hidden')"
                        class="p-1 text-ink-400 hover:text-ink-900">
                        <x-icon class="text-base fa-solid fa-xmark" />
                    </button>
                </div>

                <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <!-- Nama Lengkap Field (Height 44px, Radius-sm = 6px) -->
                    <div>
                        <label for="field-name" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                            Nama Lengkap <span class="text-semantic-danger">*</span>
                        </label>
                        <input id="field-name" type="text" name="name" required placeholder="Contoh: Budi Santoso"
                            class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                    </div>

                    <!-- Nomor WhatsApp Field -->
                    <div>
                        <label for="field-phone" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                            No. WhatsApp (Untuk Struk Digital)
                        </label>
                        <input id="field-phone" type="text" name="phone" placeholder="0812xxxx"
                            class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                    </div>

                    <!-- Option Active Member Toggle -->
                    @can('feature-loyalty')
                    <div class="flex items-center gap-3 p-3 border rounded-md bg-primary-50 border-primary-100">
                        <input type="checkbox" name="is_member" id="is_member_check" value="1"
                            class="w-4 h-4 rounded text-primary-600 border-border-200 focus:ring-primary-600">
                        <label for="is_member_check"
                            class="text-xs font-semibold cursor-pointer font-body text-primary-700">
                            Daftarkan sebagai anggota loyalitas
                        </label>
                    </div>
                    @endcan

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3 pt-3">
                        <button type="button"
                            onclick="document.getElementById('addCustomerModal').classList.add('hidden')"
                            class="flex-1 text-xs font-semibold transition-colors rounded-md h-11 bg-surface-100 hover:bg-border-200 text-ink-900 font-body">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 font-body">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Confirm Delete Pelanggan -->
        <div data-dialog x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="[&[x-cloak]]:!hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-900/40 backdrop-blur-[2px]" x-cloak>

            <div class="w-full p-6 border rounded-lg shadow-lg max-w-modal-sm bg-surface-0 border-border-200"
                @click.away="showDeleteModal = false">

                <div
                    class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-50 text-semantic-danger">
                    <x-icon class="text-xl fa-solid fa-triangle-exclamation" />
                </div>

                <div class="mb-6 text-center">
                    <h3 class="text-lg font-semibold font-heading text-ink-900">Hapus Data Pelanggan?</h3>
                    <p class="mt-2 text-xs leading-relaxed font-body text-ink-700">
                        Apakah Anda yakin ingin menghapus pelanggan <span class="font-semibold text-ink-900"
                            x-text="customerName"></span>? Riwayat poin dan data pelanggan ini akan terhapus.
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
