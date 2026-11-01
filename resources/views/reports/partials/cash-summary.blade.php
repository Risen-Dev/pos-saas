<section class="p-6 my-6 space-y-4 border shadow-sm bg-surface-0 border-border-200 rounded-xl">
    <!-- Section Header -->
    <div class="flex flex-col justify-between gap-2 pb-3 border-b sm:flex-row sm:items-center border-border-200">
        <div class="flex items-center gap-2">
            <span class="p-2 rounded-lg bg-primary-50 text-primary-600">
                <x-icon class="fa-solid fa-money-bill-transfer" />
            </span>
            <div>
                <h2 class="text-base font-bold text-ink-900">Arus Kas Tunai Periode Ini</h2>
                <p class="text-xs text-ink-700">Pencatatan aktual berdasarkan waktu penerimaan & pengeluaran uang.</p>
            </div>
        </div>

        @can('manage-finance')
        <a class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors self-start sm:self-auto"
            href="{{ route('cash.index') }}">
            <span>Periksa Buku Kas</span>
            <x-icon class="fa-solid fa-chevron-right text-[10px]" />
        </a>
        @endcan
    </div>

    <!-- Metrik Arus Kas (Grid 4 Kolom) -->
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 sm:gap-4">
        <!-- Penerimaan -->
        <div class="p-3.5 rounded-lg border border-border-200 bg-surface-100/50 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-1 text-ink-700">
                <span class="text-xs font-semibold">Penerimaan</span>
                <x-icon class="text-xs fa-solid fa-arrow-down-left text-semantic-success" />
            </div>
            <strong class="text-base font-bold sm:text-lg text-ink-900">
                Rp {{ number_format($cashSummary['receipts'], 0, ',', '.') }}
            </strong>
        </div>

        <!-- Refund -->
        <div class="p-3.5 rounded-lg border border-border-200 bg-surface-100/50 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-1 text-ink-700">
                <span class="text-xs font-semibold">Refund</span>
                <x-icon class="text-xs fa-solid fa-rotate-left text-semantic-warning" />
            </div>
            <strong class="text-base font-bold sm:text-lg text-ink-900">
                Rp {{ number_format($cashSummary['refunds'], 0, ',', '.') }}
            </strong>
        </div>

        <!-- Pengeluaran -->
        <div class="p-3.5 rounded-lg border border-border-200 bg-surface-100/50 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-1 text-ink-700">
                <span class="text-xs font-semibold">Pengeluaran</span>
                <x-icon class="text-xs fa-solid fa-arrow-up-right text-semantic-danger" />
            </div>
            <strong class="text-base font-bold sm:text-lg text-ink-900">
                Rp {{ number_format($cashSummary['expenses'], 0, ',', '.') }}
            </strong>
        </div>

        <!-- Perubahan Kas (Net) -->
        <div class="p-3.5 rounded-lg border border-border-200 bg-surface-100/50 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-1 text-ink-700">
                <span class="text-xs font-semibold">Perubahan Kas</span>
                <x-icon class="text-xs fa-solid fa-scale-balanced text-primary-600" />
            </div>
            <strong
                class="text-base sm:text-lg font-bold {{ $cashSummary['net'] >= 0 ? 'text-semantic-success' : 'text-semantic-danger' }}">
                {{ $cashSummary['net'] < 0 ? '-Rp ' . number_format(abs($cashSummary['net']), 0, ',', '.') : 'Rp ' . number_format($cashSummary['net'], 0, ',', '.') }}
            </strong>
        </div>
    </div>

    <!-- Note & Disclaimer -->
    <div class="pt-2">
        <p class="text-[11px] text-ink-400 leading-relaxed flex items-start gap-1.5">
            <x-icon class="fa-solid fa-circle-info text-xs mt-0.5 shrink-0" />
            <span>
                Penjualan lunas di atas mengikuti tanggal transaksi dan belum dikurangi retur. Bon, pajak, refund, dan
                biaya operasional harus diverifikasi sebelum menyimpulkan laba bersih.
            </span>
        </p>
    </div>
</section>
