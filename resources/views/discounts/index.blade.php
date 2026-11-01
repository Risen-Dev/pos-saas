<x-app-layout>
    @section('title', 'Manajemen Diskon & Promo')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop" x-data="{ showDeleteModal: false, deleteUrl: '', discountName: '' }">

        <!-- Header Halaman & Action Button -->
        <div
            class="flex flex-col justify-between gap-4 pb-6 mb-8 border-b sm:flex-row sm:items-center border-border-200">
            <div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                    Manajemen Diskon & Promo
                </h1>
                <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                    Kelola skema promo event, diskon jam tertentu (Happy Hour), dan potongan harga khusus menu.
                </p>
            </div>

            <!-- Button Primary: Height 44px, Emerald Green -->
            <a href="{{ route('discounts.create') }}"
                class="inline-flex items-center justify-center gap-2 px-5 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body md:text-sm shrink-0">
                <x-icon class="text-xs fa-solid fa-plus" />
                <span>Buat Diskon Baru</span>
            </a>
        </div>

        <!-- 3 Metric Cards Ringkasan Promo -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-3 md:gap-6">
            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Total Event
                        Promo</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-ink-900">
                        {{ $discounts->count() }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-primary-50 text-primary-600 shrink-0">
                    <x-icon class="fa-solid fa-tags" />
                </div>
            </div>

            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Sedang
                        Berjalan</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-semantic-success">
                        {{ $discounts->filter(fn($d) => $d->isValidNow())->count() }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-primary-100 text-primary-700 shrink-0">
                    <x-icon class="fa-solid fa-bolt" />
                </div>
            </div>

            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Tipe Promo
                        Terbanyak</span>
                    <p class="mt-2 text-lg font-semibold font-body md:text-xl text-accent-500">
                        Persentase (%)
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-accent-100 text-accent-700 shrink-0">
                    <x-icon class="fa-solid fa-percent" />
                </div>
            </div>
        </div>

        <!-- Table Container (Spesifikasi GrowPOS: Row Height 48px, bg surface-100 header) -->
        <div class="overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr
                            class="h-12 text-xs font-semibold tracking-wider uppercase border-b bg-surface-100 border-border-200 font-heading text-ink-700">
                            <th class="px-5 py-3">Nama Promo Event</th>
                            <th class="px-5 py-3">Nilai Potongan</th>
                            <th class="px-5 py-3">Periode & Jam Aktif</th>
                            <th class="px-5 py-3">Menu Terikat</th>
                            <th class="px-5 py-3 text-center">Status Operasional</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y font-body md:text-sm text-ink-900 divide-border-200">
                        @forelse($discounts as $discount)
                            <tr class="h-12 transition-colors hover:bg-surface-100/60">

                                <!-- Promo Name & ID -->
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center w-8 h-8 rounded-md bg-accent-100 text-accent-700 shrink-0">
                                            <x-icon class="text-xs fa-solid fa-ticket" />
                                        </div>
                                        <div class="min-w-0">
                                            <span
                                                class="block font-semibold leading-tight truncate text-ink-900">{{ $discount->name }}</span>
                                            <span class="font-mono text-[11px] font-normal text-ink-400 mt-0.5 block">
                                                ID: #DSC-{{ $discount->id }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Discount Value Badge -->
                                <td class="px-5 py-3">
                                    @if ($discount->type === 'percentage')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold bg-accent-100 text-accent-700 border border-accent-500/20">
                                            {{ number_format($discount->value, 0) }}% OFF
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold bg-primary-100 text-primary-700 border border-primary-500/20">
                                            -Rp {{ number_format($discount->value, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Schedule Period & Time -->
                                <td class="px-5 py-3">
                                    <div class="flex flex-col gap-0.5 font-body text-xs text-ink-700">
                                        <div class="flex items-center gap-1.5">
                                            <x-icon class="fa-regular fa-calendar text-ink-400 text-[11px] w-3.5" />
                                            <span>
                                                {{ $discount->start_date ? \Carbon\Carbon::parse($discount->start_date)->format('d M Y') : 'Selamanya' }}
                                                -
                                                {{ $discount->end_date ? \Carbon\Carbon::parse($discount->end_date)->format('d M Y') : 'Tanpa Batas' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[11px] text-ink-400">
                                            <x-icon class="fa-regular fa-clock text-ink-400 text-[10px] w-3.5" />
                                            <span>
                                                {{ $discount->start_time ? \Carbon\Carbon::parse($discount->start_time)->format('H:i') : '24 Jam' }}
                                                {{ $discount->end_time ? 's/d ' . \Carbon\Carbon::parse($discount->end_time)->format('H:i') : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tied Products List -->
                                <td class="px-5 py-3">
                                    <div class="flex flex-wrap gap-1 max-w-[220px]">
                                        @foreach ($discount->products->take(2) as $product)
                                            <span
                                                class="px-2 py-0.5 text-[10px] font-medium bg-surface-100 text-ink-700 border border-border-200 rounded-md truncate max-w-[100px]">
                                                {{ $product->product_name }}
                                            </span>
                                        @endforeach
                                        @if ($discount->products->count() > 2)
                                            <span
                                                class="px-2 py-0.5 text-[10px] font-semibold bg-primary-50 text-primary-600 rounded-md">
                                                +{{ $discount->products->count() - 2 }} menu
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Active Status Badge -->
                                <td class="px-5 py-3 text-center">
                                    @if ($discount->isValidNow())
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-[11px] font-semibold text-primary-700 bg-primary-100 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary-600 animate-pulse"></span>
                                            Berjalan
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-[11px] font-semibold text-ink-400 bg-surface-100 border border-border-200 rounded-full">
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>

                                <!-- Action Column -->
                                <td class="px-5 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('discounts.edit', $discount->id) }}"
                                            class="p-2 transition-colors rounded-md text-ink-700 hover:text-primary-600 bg-surface-100 hover:bg-primary-50"
                                            title="Edit Promo">
                                            <x-icon class="text-xs fa-solid fa-pen-to-square" />
                                        </a>

                                        <button type="button"
                                            @click="showDeleteModal = true; deleteUrl = '{{ route('discounts.destroy', $discount->id) }}'; discountName = '{{ addslashes($discount->name) }}'"
                                            class="p-2 transition-colors rounded-md text-ink-700 hover:text-semantic-danger bg-surface-100 hover:bg-red-50"
                                            title="Hapus Promo">
                                            <x-icon class="text-xs fa-solid fa-trash-can" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <!-- Friendly Empty State -->
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 mb-2 rounded-full bg-accent-100 text-accent-700">
                                            <x-icon class="text-xl fa-solid fa-tags" />
                                        </div>
                                        <p class="text-sm font-semibold font-heading text-ink-900">Belum ada promo event
                                            terdaftar</p>
                                        <p class="font-body text-xs text-ink-700 mt-0.5 max-w-xs">
                                            Buat promo diskon persentase atau nominal untuk menarik minat pelanggan di
                                            kasir!
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Confirm Delete (Standardized GrowPOS Modal) -->
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
                    <h3 class="text-lg font-semibold font-heading text-ink-900">Hapus Promo Diskon?</h3>
                    <p class="mt-2 text-xs leading-relaxed font-body text-ink-700">
                        Apakah Anda yakin ingin menghapus promo <span class="font-semibold text-ink-900"
                            x-text="discountName"></span>? Promo ini tidak akan berlaku lagi di kasir.
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
