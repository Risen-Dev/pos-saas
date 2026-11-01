<x-app-layout>
    @section('title', 'Laporan Operasional Toko')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop">

        <!-- Header Halaman & Form Filter Tanggal -->
        <div
            class="flex flex-col justify-between gap-4 pb-6 mb-8 border-b lg:flex-row lg:items-center border-border-200">
            <div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                    Laporan Operasional
                </h1>
                <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                    Lihat penjualan, penerimaan tunai, pembayaran manual, dan bon. Buka rincian jika memerlukan perhitungan modal dan biaya.
                    secara langsung.
                </p>
            </div>

            <form method="GET" action="{{ route('reports.index') }}"
                class="flex flex-wrap items-center gap-2.5 p-2 bg-surface-0 border border-border-200 rounded-md shadow-sm">
                <div class="grid w-full min-w-0 grid-cols-1 gap-2 sm:w-auto sm:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] sm:items-center">
                    <input aria-label="Tanggal mulai" type="date" name="start_date" value="{{ $startDate }}"
                        class="w-full min-w-0 h-10 px-3 text-xs border rounded-sm outline-none font-body text-ink-900 bg-surface-100 border-border-200 focus:border-primary-600">
                    <span class="text-xs font-semibold uppercase font-body text-ink-400">s/d</span>
                    <input aria-label="Tanggal sampai" type="date" name="end_date" value="{{ $endDate }}"
                        class="w-full min-w-0 h-10 px-3 text-xs border rounded-sm outline-none font-body text-ink-900 bg-surface-100 border-border-200 focus:border-primary-600">
                </div>

                <button type="submit"
                    class="h-10 px-4 inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 text-white font-body font-semibold text-xs rounded-sm transition-colors">
                    <x-icon class="text-xs fa-solid fa-filter" />
                    <span>Filter</span>
                </button>

                <a href="{{ route('reports.exports-list') }}"
                    class="h-10 px-3 inline-flex items-center gap-1.5 bg-surface-100 hover:bg-border-200 text-ink-900 font-body font-semibold text-xs rounded-sm transition-colors">
                    <x-icon class="text-xs fa-solid fa-folder-open" />
                    <span>Laci Unduhan</span>
                </a>

                {{-- DILINDUNGI GATE EXPORT EXCEL (Khusus Paket Growth & Scale) --}}
                @can('feature-crm')
                    <a href="{{ route('reports.export-excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                        class="h-10 px-4 inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-body font-semibold text-xs rounded-sm shadow-sm transition-colors">
                        <x-icon class="text-xs fa-solid fa-file-excel" />
                        <span>Export Excel</span>
                    </a>
                @else
                    <a href="{{ route('billing.index') }}" title="Fitur Export Excel membutuhkan Paket Growth / Scale"
                        class="h-10 px-3 inline-flex items-center gap-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 font-body font-bold text-xs rounded-sm transition-colors">
                        <x-icon class="fa-solid fa-lock text-amber-600 text-[10px]" />
                        <span>Export Excel</span>
                        <span
                            class="px-1.5 py-0.5 text-[9px] font-extrabold uppercase bg-amber-200 text-amber-900 rounded">GROWTH</span>
                    </a>
                @endcan
            </form>
        </div>

        @php
            $calculatedNetProfit = $netProfit;
            $marginPercent = $netProfit !== null && $revenueBeforeTax > 0 ? number_format(($netProfit / $revenueBeforeTax) * 100, 1) : null;
        @endphp

        <x-business-overview :overview="$overview" />
        @include('reports.partials.cash-summary')
        <section class="grid gap-4 my-5 sm:grid-cols-2" aria-label="Ringkasan penjualan">
            <div class="p-5 bg-white border rounded-lg border-border-200"><h2 class="text-sm font-semibold">Penjualan lunas</h2><p class="mt-2 text-2xl font-bold">Rp {{ number_format($salesSummary->total_net, 0, ',', '.') }}</p><p class="mt-2 text-xs text-ink-700">Termasuk pajak, sebelum retur. Mengikuti tanggal transaksi.</p></div>
            <div class="p-5 bg-white border rounded-lg border-border-200"><h2 class="text-sm font-semibold">Transaksi lunas</h2><p class="mt-2 text-2xl font-bold">{{ number_format($salesSummary->total_transactions, 0, ',', '.') }}</p><p class="mt-2 text-xs text-ink-700">Bon yang belum lunas belum termasuk penjualan ini.</p></div>
        </section>
        <details class="my-5 p-4 border rounded-lg border-border-200">
            <summary class="font-semibold cursor-pointer">Rincian modal, estimasi laba & biaya</summary>
        <!-- Stat Summary Cards Grid (Ringkasan Keuangan & Laba Bersih) -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 lg:grid-cols-3 md:gap-6">

            <!-- Card 1: Pendapatan Bersih (Omzet Net) -->
            <div class="flex flex-col justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Pendapatan
                        Omzet</span>
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-md bg-primary-50 text-primary-600 shrink-0">
                        <x-icon class="text-xs fa-solid fa-wallet" />
                    </div>
                </div>
                <div>
                    <p class="font-mono text-xl font-semibold md:text-2xl text-primary-600">
                        Rp {{ number_format($storeNetSales, 0, ',', '.') }}
                    </p>
                    <p class="font-body text-[11px] text-ink-400 mt-1">Penjualan lunas termasuk pajak, setelah estimasi komisi; sebelum retur</p>
                </div>
            </div>

            <!-- Card 2: Total HPP / COGS (Modal Barang Terjual) -->
            <div class="flex flex-col justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Total HPP (Modal
                        Produk)</span>
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-md bg-accent-100 text-accent-700 shrink-0">
                        <x-icon class="text-xs fa-solid fa-boxes-packing" />
                    </div>
                </div>
                <div>
                    <p class="font-mono text-xl font-semibold md:text-2xl text-accent-700">
                        {{ $totalHpp === null ? 'Belum tersedia' : 'Rp '.number_format($totalHpp, 0, ',', '.') }}
                    </p>
                    <p class="font-body text-[11px] text-ink-400 mt-1">{{ $missingCosts ? $missingCosts.' item belum memiliki snapshot modal' : 'Modal saat transaksi dicatat' }}</p>
                </div>
            </div>

            <!-- Card 3: Laba Bersih (Net Profit) -->
            <div class="flex flex-col justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Estimasi sebelum retur/biaya (
                        Profit)</span>
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-md bg-emerald-100 text-emerald-700 shrink-0">
                        <x-icon class="text-xs fa-solid fa-chart-line" />
                    </div>
                </div>
                <div>
                    <p
                        class="font-mono text-xl font-bold md:text-2xl {{ $calculatedNetProfit >= 0 ? 'text-primary-600' : 'text-semantic-danger' }}">
                        {{ $calculatedNetProfit === null ? 'Belum tersedia' : 'Rp '.number_format($calculatedNetProfit, 0, ',', '.') }}
                    </p>
                    <p class="font-body text-[11px] text-ink-400 mt-1">Margin Keuntungan: <span
                            class="font-semibold text-ink-900">{{ $marginPercent === null ? '—' : $marginPercent.'%' }}</span></p>
                </div>
            </div>

            <!-- Card 4: Total Transaksi -->
            <div class="flex flex-col justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Total
                        Transaksi</span>
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-md bg-primary-100 text-primary-700 shrink-0">
                        <x-icon class="text-xs fa-solid fa-receipt" />
                    </div>
                </div>
                <div>
                    <p class="font-mono text-xl font-semibold md:text-2xl text-ink-900">
                        {{ $salesSummary->total_transactions ?? ($totalOrders ?? 0) }} Nota
                    </p>
                    <p class="font-body text-[11px] text-ink-400 mt-1">Transaksi berstatus lunas</p>
                </div>
            </div>

            <!-- Card 5: Komisi Platform -->
            <div class="flex flex-col justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Komisi
                        Platform</span>
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-md bg-accent-100 text-accent-700 shrink-0">
                        <x-icon class="text-xs fa-solid fa-percent" />
                    </div>
                </div>
                <div>
                    <p class="font-mono text-xl font-semibold md:text-2xl text-accent-700">
                        Rp {{ number_format($totalPlatformFee, 0, ',', '.') }}
                    </p>
                    <p class="font-body text-[11px] text-ink-400 mt-1">Estimasi tarif QRIS saat ini; dibulatkan per nota</p>
                </div>
            </div>

            <!-- Card 6: Total Diskon Promo -->
            <div class="flex flex-col justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Subsidi
                        Diskon</span>
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-md bg-red-50 text-semantic-danger shrink-0">
                        <x-icon class="text-xs fa-solid fa-tags" />
                    </div>
                </div>
                <div>
                    <p class="font-mono text-xl font-semibold md:text-2xl text-semantic-danger">
                        -Rp {{ number_format($salesSummary->total_discount ?? ($totalDiscount ?? 0), 0, ',', '.') }}
                    </p>
                    <p class="font-body text-[11px] text-ink-400 mt-1">Total potongan promo produk</p>
                </div>
            </div>
        </div>

        </details>
        <!-- 2 Column Breakdown: Aliran Dana & Top 5 Selling -->
        <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-3">

            <!-- Left Panel: Payment Method Breakdown -->
            <div class="p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200 lg:col-span-1">
                <h3 class="pb-3 mb-4 text-base font-semibold border-b font-heading text-ink-900 border-border-200">
                    Metode pada nota lunas
                </h3>

                <div class="space-y-3">
                    @forelse($paymentMethods as $pm)
                        @if ($pm->payment_method == 'cash')
                            <div class="p-3 border rounded-md bg-surface-100 border-border-200">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-primary-600"></span>
                                        <span class="text-xs font-semibold uppercase font-body text-ink-900">Tunai /
                                            Cash</span>
                                    </div>
                                    <span class="font-mono text-xs font-semibold text-ink-900">
                                        Rp {{ number_format($pm->total_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between font-mono text-[11px] text-ink-400 pl-4">
                                    <span>Fee Admin:</span>
                                    <span>Rp 0</span>
                                </div>
                            </div>
                        @else
                            @php
                                $feeThisMethod = $pm->platform_fee;
                                $netThisMethod = $pm->total_amount - $feeThisMethod;
                            @endphp
                            <div class="p-3 border rounded-md bg-accent-100/40 border-accent-500/20">
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-accent-500"></span>
                                        <span class="text-xs font-semibold uppercase font-body text-accent-700">{{ \App\Support\PaymentMethod::label($pm->payment_method) }}</span>
                                    </div>
                                    <span class="font-mono text-xs font-semibold text-ink-900">
                                        Rp {{ number_format($pm->total_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="space-y-0.5 font-mono text-[11px] pl-4 pt-1 border-t border-accent-500/10">
                                    <div class="flex justify-between text-ink-400">
                                        <span>Estimasi komisi:</span>
                                        <span class="text-semantic-danger">-Rp
                                            {{ number_format($feeThisMethod, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between font-semibold text-ink-900">
                                        <span>Estimasi setelah komisi:</span>
                                        <span class="text-primary-600">Rp
                                            {{ number_format($netThisMethod, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="py-8 text-xs italic text-center font-body text-ink-400">Belum ada aliran dana masuk
                        </p>
                    @endforelse
                </div>
            </div>

            <!-- Right Panel: Top Selling Products Table -->
            <div class="p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200 lg:col-span-2">
                <h3 class="pb-3 mb-4 text-base font-semibold border-b font-heading text-ink-900 border-border-200">
                    5 Produk Terlaris (Top Selling)
                </h3>

                <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr
                                class="h-10 text-xs font-semibold tracking-wider uppercase border-b bg-surface-100 border-border-200 font-heading text-ink-700">
                                <th class="px-4 py-2">Nama Produk</th>
                                <th class="px-4 py-2 text-center">Qty Terjual</th>
                                <th class="px-4 py-2 text-right">Subtotal Omzet</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y font-body md:text-sm text-ink-900 divide-border-200">
                            @forelse($topProducts as $idx => $tp)
                                <tr class="h-12 transition-colors hover:bg-surface-100/60">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <span
                                                class="w-5 h-5 rounded-full bg-primary-100 text-primary-700 font-heading font-bold text-[10px] flex items-center justify-center shrink-0">
                                                {{ $idx + 1 }}
                                            </span>
                                            <span class="font-semibold text-ink-900 truncate max-w-[200px]">
                                                {{ $tp->product_name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-mono font-semibold text-center text-ink-700">
                                        {{ $tp->total_qty }}x
                                    </td>
                                    <td class="px-4 py-3 font-mono font-semibold text-right text-primary-600">
                                        Rp {{ number_format($tp->total_sales, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-xs italic text-center font-body text-ink-400">
                                        Belum ada data penjualan produk pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Audit Shift Table Box -->
        <div class="p-5 mb-8 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <h3 class="mb-1 text-base font-semibold font-heading text-ink-900">
                Audit Shift & Laci Kasir
            </h3>
            <p class="mb-4 text-xs font-body text-ink-700">
                Daftar rekonsiliasi kas kasir untuk mencocokkan saldo fisik laci dengan hitungan sistem.
            </p>

            <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr
                            class="h-10 text-xs font-semibold tracking-wider uppercase border-b bg-surface-100 border-border-200 font-heading text-ink-700">
                            <th class="px-4 py-2">Nama Kasir</th>
                            <th class="px-4 py-2">Waktu Buka / Tutup</th>
                            <th class="px-4 py-2 text-right">Modal Awal</th>
                            <th class="px-4 py-2 text-right">Fisik Laci</th>
                            <th class="px-4 py-2 text-center">Status</th>
                            <th class="px-4 py-2">Catatan Audit</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y font-body text-ink-900 divide-border-200">
                        @forelse($shifts as $s)
                            <tr class="h-12 transition-colors hover:bg-surface-100/60">
                                <td class="px-4 py-3 font-semibold text-ink-900">
                                    {{ $s->user->name ?? 'User Dihapus' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-[11px] text-ink-700">
                                    <span class="block text-primary-600">Buka: {{ $s->start_time }}</span>
                                    <span class="block text-ink-400">Tutup:
                                        {{ $s->end_time ?? 'Sedang Aktif' }}</span>
                                </td>
                                <td class="px-4 py-3 font-mono font-medium text-right text-ink-900">
                                    Rp {{ number_format($s->cash_start, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 font-mono font-semibold text-right text-ink-900">
                                    {{ $s->cash_actual ? 'Rp ' . number_format($s->cash_actual, 0, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($s->status === 'open')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[11px] font-semibold text-primary-700 bg-primary-100 rounded-full">
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
                                <td class="px-4 py-3 text-ink-700 font-body text-xs truncate max-w-[180px]"
                                    title="{{ $s->notes }}">
                                    {{ $s->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-xs italic text-center font-body text-ink-400">
                                    Tidak ada aktivitas shift kasir pada tanggal terpilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Visual Analytics Chart Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200 lg:col-span-2">
                <div class="mb-4">
                    <h3 class="text-base font-semibold font-heading text-ink-900">Tren Grafik Penjualan</h3>
                    <p class="font-body text-xs text-ink-700 mt-0.5">Pergerakan total omzet pada periode tanggal
                        terpilih.</p>
                </div>
                <div class="w-full h-64">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <div class="p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200 lg:col-span-1">
                <div class="mb-4">
                    <h3 class="text-base font-semibold font-heading text-ink-900">Proporsi Kanal Pembayaran</h3>
                    <p class="font-body text-xs text-ink-700 mt-0.5">Perbandingan metode pembayaran pada transaksi lunas.</p>
                </div>
                <div class="relative flex items-center justify-center w-full h-64">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>

    </div>



    <script>
        window.addEventListener("growpos:charts-ready", function() {
            // 1. Chart Omzet Penjualan (GrowPOS Colors: Emerald Green #16805F)
            const ctxTrend = document.getElementById('trendChart').getContext('2d');
            const trendLabels = {!! json_encode($chartLabels) !!};
            const trendData = {!! json_encode($chartValues) !!};

            new Chart(ctxTrend, {
                type: 'bar',
                data: {
                    labels: trendLabels.length > 0 ? trendLabels : ['Tidak Ada Data'],
                    datasets: [{
                        label: 'Omzet (Rp)',
                        data: trendData.length > 0 ? trendData : [0],
                        backgroundColor: 'rgba(22, 128, 95, 0.15)',
                        borderColor: '#16805F',
                        borderWidth: 2,
                        borderRadius: 6,
                        barPercentage: 0.55
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#E2E8F0'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                },
                                font: {
                                    family: 'IBM Plex Mono',
                                    size: 10
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Plus Jakarta Sans',
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // 2. Chart Proporsi Pembayaran
            const ctxPayment = document.getElementById('paymentChart').getContext('2d');
            let pmLabels = [];
            let pmValues = [];
            @foreach ($paymentMethods as $pm)
                pmLabels.push("{{ \App\Support\PaymentMethod::label($pm->payment_method) }}");
                pmValues.push({{ $pm->total_amount }});
            @endforeach

            new Chart(ctxPayment, {
                type: 'doughnut',
                data: {
                    labels: pmLabels.length > 0 ? pmLabels : ['Belum Ada Transaksi'],
                    datasets: [{
                        data: pmValues.length > 0 ? pmValues : [1],
                        backgroundColor: pmValues.length > 0 ? ['#16805F', '#F0932B'] : ['#E2E8F0'],
                        borderWidth: 3,
                        borderColor: '#FFFFFF'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    family: 'Plus Jakarta Sans',
                                    size: 11,
                                    weight: '600'
                                },
                                boxWidth: 10
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        });
    </script>
</x-app-layout>
