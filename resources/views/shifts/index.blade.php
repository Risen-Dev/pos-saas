<x-app-layout>
    @section('title', 'Manajemen Shift Kasir')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop" x-data="{ showExportDropdown: false }">

        <!-- Header Halaman & Action Buttons -->
        <div
            class="flex flex-col justify-between gap-4 pb-6 mb-8 border-b sm:flex-row sm:items-center border-border-200">
            <div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                    Manajemen Shift Kasir
                </h1>
                <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                    Pantau perputaran modal kasir, performa jam operasional staf, dan rekonsiliasi uang laci fisik.
                </p>
            </div>

            <div class="flex flex-wrap items-center w-full sm:w-auto gap-2.5">
                <!-- Export Data Button (Button Outline) -->
                <button @click="showExportDropdown = !showExportDropdown"
                    class="inline-flex items-center justify-center gap-2 px-4 text-xs font-semibold transition-colors border rounded-md shadow-sm h-11 bg-surface-0 border-border-200 hover:bg-surface-100 text-ink-900 font-body md:text-sm">
                    <x-icon class="text-xs fa-solid fa-file-export text-ink-400" />
                    <span>Ekspor Laporan</span>
                </button>

                @php
                    $activeShift = $shifts->where('status', 'open')->first();
                @endphp

                <!-- Active Shift Action Switcher -->
                @if ($activeShift)
                    <a href="{{ route('pos.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-semantic-danger hover:bg-red-700 font-body md:text-sm">
                        <x-icon class="text-xs fa-solid fa-power-off" />
                        <span>Tutup Shift Aktif (POS)</span>
                    </a>
                @else
                    <a href="{{ route('pos.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body md:text-sm">
                        <x-icon class="text-xs fa-solid fa-cash-register" />
                        <span>Buka Shift Baru</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- 4 Metric Cards Summary (GrowPOS Palette & IBM Plex Mono) -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 lg:grid-cols-4 md:gap-6">

            <!-- Card 1: Status Shift Berjalan -->
            <div
                class="relative flex flex-col justify-between p-5 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Shift
                            Berjalan</span>
                        @if ($activeShift)
                            <span
                                class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-primary-100 text-primary-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary-600 animate-pulse"></span>
                                Aktif
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-surface-100 text-ink-400">
                                Kosong
                            </span>
                        @endif
                    </div>

                    @if ($activeShift)
                        <p class="text-base font-semibold truncate font-heading text-ink-900">
                            {{ $activeShift->user->name ?? 'Kasir Store' }}
                        </p>
                        <p class="mt-1 text-xs font-body text-ink-700">
                            Mulai: <span
                                class="font-mono font-medium">{{ \Carbon\Carbon::parse($activeShift->start_time)->format('H:i') }}
                                WIB</span>
                        </p>
                    @else
                        <p class="text-sm italic font-medium font-body text-ink-400">Tidak ada shift aktif</p>
                        <p class="mt-1 text-xs font-body text-ink-400">—</p>
                    @endif
                </div>
            </div>

            <!-- Card 2: Total Kas Awal -->
            <div class="flex flex-col justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Total Modal Kas
                        Awal</span>
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-md bg-primary-50 text-primary-600 shrink-0">
                        <x-icon class="text-xs fa-solid fa-wallet" />
                    </div>
                </div>
                <div>
                    <p class="font-mono text-xl font-semibold md:text-2xl text-ink-900">
                        Rp {{ number_format($shifts->sum('cash_start'), 0, ',', '.') }}
                    </p>
                    <p class="font-body text-[11px] text-ink-400 mt-1">Akumulasi modal awal tunai</p>
                </div>
            </div>

            <!-- Card 3: Penjualan Tunai Laci -->
            @php
                $totalCashSales = $shifts->sum(function ($s) {
                    return $s->cash_expected - $s->cash_start;
                });
            @endphp
            <div class="flex flex-col justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Penjualan
                        Tunai</span>
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-md bg-primary-100 text-primary-700 shrink-0">
                        <x-icon class="text-xs fa-solid fa-money-bill-trend-up" />
                    </div>
                </div>
                <div>
                    <p class="font-mono text-xl font-semibold md:text-2xl text-primary-600">
                        Rp {{ number_format($totalCashSales, 0, ',', '.') }}
                    </p>
                    <p class="font-body text-[11px] text-ink-400 mt-1">Omzet masuk ke laci uang</p>
                </div>
            </div>

            <!-- Card 4: Total Selisih Laci Kasir -->
            @php
                $totalDifference = $shifts->where('status', 'closed')->sum('cash_difference');
            @endphp
            <div class="flex flex-col justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Selisih Laci
                        Fisik</span>
                    <div
                        class="w-8 h-8 rounded-md {{ $totalDifference < 0 ? 'bg-red-50 text-semantic-danger' : 'bg-accent-100 text-accent-700' }} flex items-center justify-center shrink-0">
                        <x-icon class="text-xs fa-solid fa-scale-balanced" />
                    </div>
                </div>
                <div>
                    <p
                        class="font-mono text-xl md:text-2xl font-semibold {{ $totalDifference < 0 ? 'text-semantic-danger' : ($totalDifference > 0 ? 'text-accent-700' : 'text-ink-900') }}">
                        {{ $totalDifference < 0 ? '-Rp ' . number_format(abs($totalDifference), 0, ',', '.') : '+Rp ' . number_format($totalDifference, 0, ',', '.') }}
                    </p>
                    <p class="font-body text-[11px] text-ink-400 mt-1">Kecocokan fisik vs sistem</p>
                </div>
            </div>
        </div>

        <!-- Main Table Box (Spesifikasi: Row Height 48px, bg surface-100 Header) -->
        <div class="mb-6 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">

            <!-- Filter Bar Form (Search & Date Filters) -->
            <form method="GET" action="{{ url()->current() }}"
                class="flex flex-col items-stretch justify-between gap-3 p-4 border-b md:flex-row md:items-center border-border-200 bg-surface-100/40">

                <!-- Search Cashier Name Field -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-ink-400">
                        <x-icon class="text-xs fa-solid fa-magnifying-glass" />
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama staf kasir..."
                        class="w-full pr-4 text-xs transition-all border rounded-sm outline-none h-11 pl-9 font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Status Filter Dropdown -->
                    <select name="status" onchange="this.form.submit()"
                        class="px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        <option value="">Semua Status Shift</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Aktif (Berjalan)
                        </option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Selesai (Ditutup)
                        </option>
                    </select>

                    <!-- Date Filter Picker -->
                    <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()"
                        class="px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">

                    @if (request()->filled('search') || request()->filled('status') || request()->filled('date'))
                        <a href="{{ url()->current() }}"
                            class="inline-flex items-center px-3 text-xs font-semibold transition-colors rounded-sm h-11 font-body text-semantic-danger hover:bg-red-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- Table Data View -->
            <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr
                            class="h-12 text-xs font-semibold tracking-wider uppercase border-b bg-surface-100 border-border-200 font-heading text-ink-700">
                            <th class="px-5 py-3">Nama Kasir</th>
                            <th class="px-5 py-3">Waktu Mulai</th>
                            <th class="px-5 py-3">Waktu Selesai</th>
                            <th class="px-5 py-3 text-right">Modal Kas Awal</th>
                            <th class="px-5 py-3 text-right">Kas Akhir (Fisik / Sistem)</th>
                            <th class="px-5 py-3 text-center">Status Sesi</th>
                            <th class="px-5 py-3 text-left">Catatan Selisih</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y font-body md:text-sm text-ink-900 divide-border-200">
                        @forelse($shifts as $shift)
                            <tr class="h-12 transition-colors hover:bg-surface-100/60">

                                <!-- Operator Staff Avatar & Name -->
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center w-8 h-8 text-xs font-bold rounded-full bg-primary-100 text-primary-700 font-heading shrink-0">
                                            {{ substr($shift->user->name ?? 'KS', 0, 2) }}
                                        </div>
                                        <span class="font-semibold text-ink-900 truncate max-w-[140px]">
                                            {{ $shift->user->name ?? 'User Dihapus' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Start Time -->
                                <td class="px-5 py-3 font-mono text-xs text-ink-700">
                                    {{ \Carbon\Carbon::parse($shift->start_time)->format('d M Y, H:i') }}
                                </td>

                                <!-- End Time -->
                                <td class="px-5 py-3 font-mono text-xs text-ink-700">
                                    {{ $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('d M Y, H:i') : '—' }}
                                </td>

                                <!-- Cash Start -->
                                <td class="px-5 py-3 font-mono font-medium text-right text-ink-900">
                                    Rp {{ number_format($shift->cash_start, 0, ',', '.') }}
                                </td>

                                <!-- Cash Actual vs Expected & Variance Indicator -->
                                <td class="px-5 py-3 text-right">
                                    @if ($shift->status === 'open')
                                        <span class="text-xs italic font-body text-ink-400">Sedang Berjalan...</span>
                                    @else
                                        <div class="font-mono font-semibold text-ink-900">
                                            Rp {{ number_format($shift->cash_actual, 0, ',', '.') }}
                                        </div>
                                        <span class="block font-mono text-[11px] text-ink-400 font-normal">
                                            Sistem: Rp {{ number_format($shift->cash_expected, 0, ',', '.') }}
                                        </span>

                                        @if ($shift->cash_difference < 0)
                                            <span
                                                class="block font-mono text-[11px] font-semibold text-semantic-danger">
                                                (Selisih -Rp
                                                {{ number_format(abs($shift->cash_difference), 0, ',', '.') }})
                                            </span>
                                        @elseif($shift->cash_difference > 0)
                                            <span class="block font-mono text-[11px] font-semibold text-accent-700">
                                                (Surplus +Rp {{ number_format($shift->cash_difference, 0, ',', '.') }})
                                            </span>
                                        @else
                                            <span class="block font-body text-[11px] font-semibold text-primary-600">
                                                (Cocok 100%)
                                            </span>
                                        @endif
                                    @endif
                                </td>

                                <!-- Active Status Badge (Pill Shape: radius-full) -->
                                <td class="px-5 py-3 text-center">
                                    @if ($shift->status === 'open')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-[11px] font-semibold text-primary-700 bg-primary-100 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary-600 animate-pulse"></span>
                                            Berjalan
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-[11px] font-semibold text-ink-400 bg-surface-100 border border-border-200 rounded-full">
                                            Ditutup
                                        </span>
                                    @endif
                                </td>

                                <!-- Notes Column -->
                                <td class="px-5 py-3 text-left max-w-[180px] truncate font-body text-xs text-ink-700"
                                    title="{{ $shift->notes }}">
                                    {{ $shift->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <!-- Friendly Empty State -->
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 mb-2 rounded-full bg-primary-50 text-primary-600">
                                            <x-icon class="text-xl fa-solid fa-user-clock" />
                                        </div>
                                        <p class="text-sm font-semibold font-heading text-ink-900">Belum ada riwayat
                                            shift</p>
                                        <p class="font-body text-xs text-ink-700 mt-0.5 max-w-xs">
                                            Sesi operasional kasir baru akan dicatat setiap kali staf membuka shift di
                                            Kasir.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination -->
            @if ($shifts->hasPages())
                <div class="p-4 border-t bg-surface-0 border-border-200">
                    {{ $shifts->appends(request()->query())->links() }}
                </div>
            @else
                <div class="p-4 text-xs font-medium border-t font-body text-ink-400 border-border-200">
                    Menampilkan {{ $shifts->count() }} entri riwayat shift operasional toko Anda.
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
