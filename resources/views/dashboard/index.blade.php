<x-app-layout>
    @section('title', 'Ringkasan Usaha')

    <div class="min-h-screen px-4 py-8 mx-auto space-y-8 max-w-7xl sm:px-6 lg:px-8 bg-surface-100">

        <!-- Header & Quick CTA -->
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold tracking-wider uppercase text-ink-400">
                    <span>{{ auth()->user()->tenant->name }}</span>
                    <span>/</span>
                    <span class="text-primary-600">Ringkasan Pemilik</span>
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl text-ink-900">
                    Bagaimana usahamu hari ini?
                </h1>
                <p class="flex items-center gap-2 mt-1 text-xs sm:text-sm text-ink-700">
                    <x-icon class="fa-regular fa-clock text-ink-400" />
                    <span>{{ $start->translatedFormat('d M Y') }} — {{ $end->translatedFormat('d M Y') }}</span>
                    <span class="text-ink-400">•</span>
                    <span class="text-ink-700">Transaksi lunas, diperbarui saat dimuat.</span>
                </p>
            </div>
            <a href="{{ route('pos.index') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm shadow-sm transition-all duration-150 group">
                <x-icon class="text-base transition-transform fa-solid fa-cash-register group-hover:scale-110" />
                <span>Buka Kasir</span>
                <x-icon class="text-xs opacity-75 fa-solid fa-arrow-up-right-from-square" />
            </a>
        </div>

        <!-- Onboarding Banner -->
        @if (!$hasMenu || !$hasSale)
            <a href="{{ route('getting-started') }}"
                class="relative flex flex-col items-start justify-between p-6 transition-all duration-200 border shadow-sm group sm:flex-row sm:items-center rounded-xl bg-primary-50 border-primary-100 hover:border-primary-500/30">
                <div class="space-y-1">
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-primary-100 text-primary-700">
                        <x-icon class="fa-solid fa-flag text-[9px]" /> Langkah Pertama
                    </span>
                    <h2 class="text-base font-bold transition-colors text-ink-900 group-hover:text-primary-600">
                        {{ !$hasMenu ? 'Siapkan menu, lalu mulai berjualan.' : 'Menu sudah siap. Yuk, buat transaksi pertama.' }}
                    </h2>
                    <p class="text-xs text-ink-700">Panduan singkat, contoh menu, dan fitur impor Excel.</p>
                </div>
                <span
                    class="inline-flex items-center gap-2 mt-4 text-xs font-semibold transition-transform sm:mt-0 text-primary-600 group-hover:translate-x-1">
                    Lanjutkan setup <x-icon class="fa-solid fa-arrow-right" />
                </span>
            </a>
        @endif

        <!-- Date Filter Card -->
        <div class="p-4 border shadow-sm bg-surface-0 border-border-200 rounded-xl">
            <form method="get" class="flex flex-wrap items-end gap-3 sm:gap-4">
                <div class="flex-1 min-w-[140px]">
                    <label for="field-start_date" class="block mb-1 text-xs font-semibold text-ink-700">Mulai</label>
                    <div class="relative">
                        <input id="field-start_date" type="date" name="start_date" value="{{ $start->toDateString() }}" required
                            class="w-full py-2 pl-3 pr-2 text-xs border rounded-lg outline-none sm:text-sm text-ink-900 bg-surface-0 border-border-200 focus:ring-2 focus:ring-primary-600 focus:border-primary-600">
                    </div>
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label for="field-end_date" class="block mb-1 text-xs font-semibold text-ink-700">Sampai</label>
                    <div class="relative">
                        <input id="field-end_date" type="date" name="end_date" value="{{ $end->toDateString() }}" required
                            class="w-full py-2 pl-3 pr-2 text-xs border rounded-lg outline-none sm:text-sm text-ink-900 bg-surface-0 border-border-200 focus:ring-2 focus:ring-primary-600 focus:border-primary-600">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white transition-colors rounded-lg bg-primary-600 hover:bg-primary-700 sm:text-sm">
                        Tampilkan
                    </button>
                    <a href="{{ route('dashboard') }}"
                        class="px-4 py-2 text-xs font-semibold transition-colors rounded-lg bg-surface-100 hover:bg-border-200 text-ink-700 sm:text-sm">
                        Hari ini
                    </a>
                </div>
            </form>
        </div>

        <x-business-overview :overview="$overview" />
        @include('reports.partials.cash-summary')

        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Metric 1: Revenue -->
            <div class="flex flex-col justify-between p-5 border shadow-sm bg-surface-0 rounded-xl border-border-200">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold tracking-wider uppercase text-ink-400">Penjualan Lunas</span>
                        <span class="p-2 rounded-lg bg-primary-50 text-primary-600">
                            <x-icon class="fa-solid fa-wallet" />
                        </span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-ink-900">Rp {{ number_format($revenue, 0, ',', '.') }}</p>
                </div>
                <div class="pt-3 mt-3 text-xs border-t border-border-200">
                    @if ($change === null)
                        <span class="text-ink-400">Belum ada pembanding</span>
                    @else
                        <span class="font-bold {{ $change >= 0 ? 'text-semantic-success' : 'text-semantic-danger' }}">
                            <i class="fa-solid {{ $change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} text-[10px]"></i>
                            {{ abs($change) }}%
                        </span>
                        <span class="text-ink-700"> vs periode lalu</span>
                    @endif
                </div>
            </div>

            <!-- Metric 2: Orders -->
            <div class="flex flex-col justify-between p-5 border shadow-sm bg-surface-0 rounded-xl border-border-200">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold tracking-wider uppercase text-ink-400">Pesanan Selesai</span>
                        <span class="p-2 rounded-lg bg-primary-50 text-primary-600">
                            <x-icon class="fa-solid fa-receipt" />
                        </span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-ink-900">{{ number_format($orderCount) }}</p>
                </div>
                <div class="pt-3 mt-3 text-xs border-t border-border-200 text-ink-700">
                    Rata-rata: <span
                        class="font-semibold text-ink-900">Rp {{ number_format($orderCount ? $revenue / $orderCount : 0, 0, ',', '.') }}</span>
                    / nota
                </div>
            </div>

            <!-- Metric 3: Gross Profit -->
            <div class="flex flex-col justify-between p-5 border shadow-sm bg-surface-0 rounded-xl border-border-200">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold tracking-wider uppercase text-ink-400">Estimasi Laba Kotor</span>
                        <span class="p-2 rounded-lg bg-accent-100 text-accent-700">
                            <x-icon class="fa-solid fa-chart-line" />
                        </span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-ink-900">
                        {{ $grossProfit === null ? '—' : 'Rp ' . number_format($grossProfit, 0, ',', '.') }}
                    </p>
                </div>
                <div class="pt-3 mt-3 text-xs border-t border-border-200 text-ink-700">
                    {{ $grossProfit === null ? 'Lengkapi data modal produk.' : 'Penjualan bersih dikurangi modal HPP.' }}
                </div>
            </div>

            <!-- Metric 4: Cash Variance -->
            <div class="flex flex-col justify-between p-5 border shadow-sm bg-surface-0 rounded-xl border-border-200">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold tracking-wider uppercase text-ink-400">Selisih Kas Shift</span>
                        <span
                            class="p-2 rounded-lg {{ $cashDifference != 0 ? 'bg-amber-100 text-semantic-warning' : 'bg-surface-100 text-ink-400' }}">
                            <x-icon class="fa-solid fa-scale-unbalanced" />
                        </span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-ink-900">{{ $cashDifference < 0 ? '-Rp ' . number_format(abs($cashDifference), 0, ',', '.') : 'Rp ' . number_format($cashDifference, 0, ',', '.') }}
                    </p>
                </div>
                <div class="pt-3 mt-3 text-xs border-t border-border-200">
                    <a href="{{ route('shifts.index') }}"
                        class="inline-flex items-center gap-1 font-semibold text-primary-600 hover:text-primary-700">
                        <span>{{ $shiftIssueCount }} shift perlu diperiksa</span>
                        <x-icon class="fa-solid fa-chevron-right text-[10px]" />
                    </a>
                </div>
            </div>
        </div>

        <!-- Section: Insights & Top Selling -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Warnings & Alerts -->
            <div class="flex flex-col justify-between p-6 border shadow-sm bg-surface-0 rounded-xl border-border-200">
                <div>
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-border-200">
                        <div class="flex items-center gap-2">
                            <x-icon class="fa-solid fa-circle-exclamation text-semantic-warning" />
                            <h2 class="text-base font-bold text-ink-900">Perlu Diperhatikan</h2>
                        </div>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-surface-100 text-ink-700 border border-border-200">
                            Peringatan Otomatis
                        </span>
                    </div>

                    <div class="space-y-3">
                        @if ($change !== null && $change < 0)
                            <a href="{{ route('reports.index', ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString()]) }}"
                                class="flex items-start gap-3 p-3 transition-colors border border-red-100 rounded-lg bg-red-50/50 hover:bg-red-50">
                                <x-icon class="mt-1 fa-solid fa-arrow-trend-down text-semantic-danger" />
                                <div class="text-xs">
                                    <p class="font-bold text-ink-900">Penjualan turun {{ abs($change) }}%</p>
                                    <p class="text-ink-700 mt-0.5">Periode sebelumnya
                                        Rp {{ number_format($previousRevenue, 0, ',', '.') }}. Periksa perbandingan item
                                        terjual.</p>
                                </div>
                            </a>
                        @endif

                        @foreach ($lowStock as $product)
                            <a href="{{ route('inventory.index') }}"
                                class="flex items-start gap-3 p-3 transition-colors border rounded-lg bg-amber-50/50 hover:bg-amber-50 border-amber-100">
                                <x-icon class="mt-1 fa-solid fa-box-open text-semantic-warning" />
                                <div class="text-xs">
                                    <p class="font-bold text-ink-900">Stok Menipis: {{ $product->product_name }}</p>
                                    <p class="text-ink-700 mt-0.5">Sisa {{ \App\Support\NumberFormat::quantity($product->stock) }} (Batas minimal
                                        {{ \App\Support\NumberFormat::quantity($product->min_stock) }}).</p>
                                </div>
                            </a>
                        @endforeach

                        @foreach ($lowMaterials as $material)
                            <a href="{{ route('materials.index') }}"
                                class="flex items-start gap-3 p-3 transition-colors border rounded-lg bg-amber-50/50 hover:bg-amber-50 border-amber-100">
                                <x-icon class="mt-1 fa-solid fa-cubes text-semantic-warning" />
                                <div class="text-xs">
                                    <p class="font-bold text-ink-900">Bahan Baku Menipis: {{ $material->name }}</p>
                                    <p class="text-ink-700 mt-0.5">Sisa {{ \App\Support\NumberFormat::quantity($material->stock) }} {{ $material->unit }}.
                                        Segera jadwalkan pengadaan.</p>
                                </div>
                            </a>
                        @endforeach

                        @foreach ($shiftIssues->take(3) as $shift)
                            <a href="{{ route('shifts.index') }}"
                                class="flex items-start gap-3 p-3 transition-colors rounded-lg bg-surface-100 hover:bg-border-200">
                                <x-icon class="mt-1 fa-solid fa-user-clock text-ink-400" />
                                <div class="text-xs">
                                    <p class="font-bold text-ink-900">Selisih Kas Shift: {{ $shift->user?->name }}</p>
                                    <p class="text-ink-700 mt-0.5">Terdapat perbedaan kas sebesar
                                        Rp {{ number_format($shift->cash_difference, 0, ',', '.') }}.</p>
                                </div>
                            </a>
                        @endforeach

                        @if ($lowStock->isEmpty() && $lowMaterials->isEmpty() && $shiftIssues->isEmpty() && !($change !== null && $change < 0))
                            <div class="py-8 text-center text-ink-400">
                                <x-icon class="mb-2 text-2xl fa-solid fa-circle-check text-semantic-success" />
                                <p class="text-xs font-semibold">Semua aman! Tidak ada kendala stok atau kas shift.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="flex flex-col justify-between p-6 border shadow-sm bg-surface-0 rounded-xl border-border-200">
                <div>
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-border-200">
                        <div class="flex items-center gap-2">
                            <x-icon class="fa-solid fa-fire text-accent-500" />
                            <h2 class="text-base font-bold text-ink-900">Produk Terlaris</h2>
                        </div>
                        <span class="text-xs text-ink-400">Berdasarkan volume</span>
                    </div>

                    <div class="space-y-4">
                        @forelse($topProducts as $product)
                            @php
                                $percent = ($product->quantity / max(1, $topProducts->max('quantity'))) * 100;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1 text-xs">
                                    <span class="font-semibold text-ink-900 truncate max-w-[200px] sm:max-w-[280px]">
                                        {{ $product->product_name }}
                                    </span>
                                    <span class="font-bold text-primary-600">{{ \App\Support\NumberFormat::quantity($product->quantity) }} terjual</span>
                                </div>
                                <progress aria-label="Penjualan {{ $product->product_name }}" value="{{ $percent }}" max="100" class="block w-full h-2 overflow-hidden rounded-full appearance-none border-0 bg-surface-100 [&::-webkit-progress-bar]:bg-surface-100 [&::-webkit-progress-value]:bg-primary-600 [&::-moz-progress-bar]:bg-primary-600"></progress>
                            </div>
                        @empty
                            <div class="py-8 text-center text-ink-400">
                                <x-icon class="mb-2 text-2xl fa-solid fa-chart-bar" />
                                <p class="text-xs">Belum ada transaksi pada periode ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Recent Transactions & Payment Channels -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Recent Transactions -->
            <div class="p-6 border shadow-sm bg-surface-0 rounded-xl border-border-200">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-border-200">
                    <div class="flex items-center gap-2">
                        <x-icon class="fa-solid fa-clock-rotate-left text-ink-400" />
                        <h2 class="text-base font-bold text-ink-900">Transaksi Terbaru</h2>
                    </div>
                    <a href="{{ route('orders.index') }}"
                        class="flex items-center gap-1 text-xs font-semibold text-primary-600 hover:text-primary-700">
                        Semua Nota <x-icon class="fa-solid fa-chevron-right text-[10px]" />
                    </a>
                </div>

                <div class="divide-y divide-border-200">
                    @forelse($recentOrders as $order)
                        <a href="{{ route('orders.show', $order) }}"
                            class="flex items-center justify-between px-2 py-3 transition-colors rounded-lg hover:bg-surface-100/50">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex items-center justify-center w-8 h-8 text-xs font-bold rounded-full bg-primary-50 text-primary-600">
                                    <x-icon class="fa-solid fa-user" />
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-ink-900">
                                        {{ $order->customer?->name ?? 'Pelanggan Umum' }}</p>
                                    <p class="text-[11px] text-ink-400">
                                        {{ $order->user?->name }} •
                                        {{ ($order->sold_at ?? $order->created_at)->format('d M H:i') }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-ink-900">
                                Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                            </span>
                        </a>
                    @empty
                        <div class="py-8 text-center text-ink-400">
                            <p class="text-xs">Belum ada transaksi lunas pada periode ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="flex flex-col justify-between p-6 border shadow-sm bg-surface-0 rounded-xl border-border-200">
                <div>
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-border-200">
                        <div class="flex items-center gap-2">
                            <x-icon class="fa-solid fa-credit-card text-ink-400" />
                            <h2 class="text-base font-bold text-ink-900">Pembayaran Masuk</h2>
                        </div>
                        <span class="text-xs text-ink-400">Berdasarkan Kanal</span>
                    </div>

                    <div class="space-y-3">
                        @forelse($paymentMethods as $method)
                            <div
                                class="flex items-center justify-between p-3 border rounded-lg border-border-200 bg-surface-100/30">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="p-2 text-xs border rounded-lg bg-surface-0 border-border-200 text-primary-600">
                                        <i
                                            class="fa-solid {{ $method->payment_method === 'cash' ? 'fa-money-bill-wave' : 'fa-qrcode' }}"></i>
                                    </span>
                                    <span class="text-xs font-bold text-ink-900">
                                        {{ \App\Support\PaymentMethod::label($method->payment_method) }}
                                    </span>
                                </div>
                                <span class="text-xs font-bold text-ink-900">
                                    Rp {{ number_format($method->amount, 0, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <div class="py-8 text-center text-ink-400">
                                <p class="text-xs">Belum ada pembayaran masuk.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 mt-6 border-t border-border-200">
                    <p class="text-[11px] text-ink-400 leading-relaxed">
                        <x-icon class="fa-solid fa-circle-info" /> Angka penjualan termasuk pajak. Saldo pencairan QRIS
                        dapat berbeda karena potongan MDR/komisi.
                    </p>
                    @can('manage-finance')
                        <a href="{{ route('finance.index') }}"
                            class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 mt-3 text-xs font-semibold transition-colors rounded-lg bg-surface-100 hover:bg-border-200 text-ink-900">
                            <span>Lihat Dompet Toko</span>
                            <x-icon class="fa-solid fa-arrow-right text-[10px]" />
                        </a>
                    @endcan
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
