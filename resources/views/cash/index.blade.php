<x-app-layout>
    @section('title', 'Kas & Pengeluaran')

    @php
        $kindMeta = [
            'sale' => ['label' => 'Penjualan', 'icon' => 'fa-receipt', 'badge' => 'bg-primary-50 text-primary-700'],
            'payment' => ['label' => 'Pembayaran Bon', 'icon' => 'fa-hand-holding-dollar', 'badge' => 'bg-blue-50 text-blue-700'],
            'expense' => ['label' => 'Pengeluaran', 'icon' => 'fa-arrow-up-from-bracket', 'badge' => 'bg-red-50 text-semantic-danger'],
            'refund' => ['label' => 'Refund', 'icon' => 'fa-rotate-left', 'badge' => 'bg-accent-100 text-accent-700'],
        ];
    @endphp

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop">
        <div class="flex flex-col justify-between gap-4 pb-6 mb-6 border-b md:flex-row md:items-center border-border-200">
            <div>
                <div class="flex items-center gap-2 mb-2 text-[10px] font-bold tracking-widest uppercase text-primary-600">
                    <x-icon class="fa-solid fa-book-open" />
                    Operasional Kas
                </div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">Kas & Pengeluaran</h1>
                <p class="max-w-2xl mt-1 text-xs leading-relaxed font-body text-ink-700 md:text-sm">
                    Pantau uang tunai yang benar-benar bergerak pada shift. Penjualan, pembayaran bon, refund, dan pengeluaran tercatat dalam satu riwayat.
                </p>
            </div>

            <a href="{{ route('shifts.index') }}"
                class="inline-flex items-center justify-center h-10 gap-2 px-4 text-xs font-semibold transition-colors border rounded-md border-border-200 text-ink-700 bg-surface-0 hover:bg-surface-100">
                <x-icon class="fa-solid fa-user-clock" />
                Sesi kasir
            </a>
        </div>

        @if (session('success'))
            <div role="status" class="flex items-start gap-3 p-4 mb-5 text-sm border rounded-lg bg-primary-50 border-primary-100 text-primary-700">
                <x-icon class="mt-0.5 fa-solid fa-circle-check" />
                <div><strong class="font-semibold">Berhasil.</strong> {{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="p-4 mb-5 border rounded-lg bg-red-50 border-red-100 text-semantic-danger">
                <div class="flex items-center gap-2 mb-2 text-sm font-semibold"><x-icon class="fa-solid fa-circle-exclamation" /> Periksa kembali data berikut</div>
                <ul class="pl-5 space-y-1 text-xs list-disc">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 xl:grid-cols-4">
            <div class="p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold tracking-widest uppercase text-ink-400">Kas Shift Saat Ini</span>
                    <span class="flex items-center justify-center w-8 h-8 rounded-md bg-primary-50 text-primary-600"><x-icon class="fa-solid fa-wallet" /></span>
                </div>
                <p class="font-mono text-xl font-semibold text-ink-900">{{ $currentCash === null ? '—' : 'Rp '.number_format($currentCash, 0, ',', '.') }}</p>
                <p class="mt-1 text-[11px] text-ink-400">{{ $currentShift ? 'Shift #'.$currentShift->id.' · '.($currentShift->user?->name ?? 'Admin') : 'Belum ada shift admin yang terbuka' }}</p>
            </div>

            <div class="p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold tracking-widest uppercase text-ink-400">Kas Masuk Hari Ini</span>
                    <span class="flex items-center justify-center w-8 h-8 rounded-md bg-primary-50 text-primary-600"><x-icon class="fa-solid fa-arrow-down" /></span>
                </div>
                <p class="font-mono text-xl font-semibold text-primary-600">Rp {{ number_format($todayIn, 0, ',', '.') }}</p>
                <p class="mt-1 text-[11px] text-ink-400">Penjualan tunai dan pembayaran bon</p>
            </div>

            <div class="p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold tracking-widest uppercase text-ink-400">Kas Keluar Hari Ini</span>
                    <span class="flex items-center justify-center w-8 h-8 rounded-md bg-red-50 text-semantic-danger"><x-icon class="fa-solid fa-arrow-up" /></span>
                </div>
                <p class="font-mono text-xl font-semibold text-semantic-danger">Rp {{ number_format($todayOut, 0, ',', '.') }}</p>
                <p class="mt-1 text-[11px] text-ink-400">Pengeluaran operasional dan refund</p>
            </div>

            <div class="p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold tracking-widest uppercase text-ink-400">Net Hari Ini</span>
                    <span class="flex items-center justify-center w-8 h-8 rounded-md bg-accent-100 text-accent-700"><x-icon class="fa-solid fa-scale-balanced" /></span>
                </div>
                <p class="font-mono text-xl font-semibold {{ ($todayIn - $todayOut) >= 0 ? 'text-ink-900' : 'text-semantic-danger' }}">{{ ($todayIn - $todayOut) < 0 ? '-Rp ' . number_format(abs($todayIn - $todayOut), 0, ',', '.') : 'Rp ' . number_format($todayIn - $todayOut, 0, ',', '.') }}</p>
                <p class="mt-1 text-[11px] text-ink-400">Kas masuk dikurangi kas keluar</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <section class="self-start p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200 lg:sticky lg:top-6">
                <div class="flex items-start gap-3 pb-4 mb-5 border-b border-border-200">
                    <div class="flex items-center justify-center w-10 h-10 rounded-md bg-red-50 text-semantic-danger shrink-0"><x-icon class="fa-solid fa-receipt" /></div>
                    <div>
                        <h2 class="text-base font-semibold font-heading text-ink-900">Catat Pengeluaran Tunai</h2>
                        <p class="mt-0.5 text-[11px] leading-relaxed text-ink-400">Pengeluaran akan mengurangi saldo shift admin yang sedang terbuka.</p>
                    </div>
                </div>

                @if (!$currentShift)
                    <div class="p-3 mb-4 text-xs leading-relaxed border rounded-md bg-accent-100/50 border-accent-500/20 text-accent-700">
                        <x-icon class="mr-1 fa-solid fa-triangle-exclamation" /> Mulai jualan terlebih dahulu sebelum mencatat pengeluaran.
                    </div>
                @endif

                <form action="{{ route('cash.expenses') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="operation_key" value="{{ old('operation_key', (string) Illuminate\Support\Str::uuid()) }}">

                    <div>
                        <label for="cash_amount" class="block mb-1.5 text-xs font-semibold text-ink-700">Nominal Pengeluaran</label>
                        <div class="relative">
                            <span class="absolute text-xs font-semibold -translate-y-1/2 left-3 top-1/2 text-ink-400">Rp</span>
                            <input id="cash_amount" type="text" data-rupiah-input name="amount" min="1" max="999999999999" required value="{{ old('amount') }}"
                                class="w-full h-11 pl-10 pr-3 text-sm border rounded-md border-border-200 bg-surface-0 text-ink-900 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="0">
                        </div>
                    </div>

                    <div>
                        <label for="cash_reason" class="block mb-1.5 text-xs font-semibold text-ink-700">Keperluan / Bukti Pengeluaran</label>
                        <textarea id="cash_reason" name="reason" maxlength="500" required rows="4"
                            class="w-full px-3 py-2.5 text-sm border rounded-md resize-none border-border-200 bg-surface-0 text-ink-900 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Contoh: pembelian gas, parkir, biaya kirim...">{{ old('reason') }}</textarea>
                        <p class="mt-1 text-[10px] text-ink-400">Tuliskan alasan yang cukup jelas agar mudah direkonsiliasi saat tutup penjualan.</p>
                    </div>

                    <button type="submit" @disabled(!$currentShift)
                        class="inline-flex items-center justify-center w-full h-11 gap-2 text-xs font-semibold text-white transition-colors rounded-md shadow-sm bg-primary-600 hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50">
                        <x-icon class="fa-solid fa-plus" />
                        Simpan Pengeluaran
                    </button>
                </form>
            </section>

            <section class="overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200 lg:col-span-2">
                <div class="flex flex-col justify-between gap-3 p-5 border-b sm:flex-row sm:items-center border-border-200">
                    <div>
                        <h2 class="text-base font-semibold font-heading text-ink-900">Riwayat Mutasi Kas</h2>
                        <p class="mt-0.5 text-[11px] text-ink-400">30 mutasi terbaru per halaman.</p>
                    </div>
                    <span class="inline-flex items-center self-start gap-2 px-2.5 py-1 text-[10px] font-semibold rounded-full bg-surface-100 text-ink-700 sm:self-auto">
                        <x-icon class="fa-solid fa-clock-rotate-left" /> {{ $entries->total() }} catatan
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="border-b bg-surface-100 border-border-200">
                            <tr>
                                <th class="px-5 py-3 text-[10px] font-bold tracking-wider uppercase text-ink-400">Waktu</th>
                                <th class="px-5 py-3 text-[10px] font-bold tracking-wider uppercase text-ink-400">Jenis</th>
                                <th class="px-5 py-3 text-[10px] font-bold tracking-wider uppercase text-ink-400">Keterangan</th>
                                <th class="px-5 py-3 text-[10px] font-bold tracking-wider uppercase text-ink-400">Referensi</th>
                                <th class="px-5 py-3 text-[10px] font-bold tracking-wider uppercase text-right text-ink-400">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-200">
                            @forelse ($entries as $entry)
                                @php($meta = $kindMeta[$entry->kind] ?? ['label' => ucfirst($entry->kind), 'icon' => 'fa-circle', 'badge' => 'bg-surface-100 text-ink-700'])
                                <tr class="transition-colors hover:bg-surface-100/60">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <p class="text-xs font-semibold text-ink-900">{{ $entry->created_at->format('d M Y') }}</p>
                                        <p class="text-[10px] text-ink-400">{{ $entry->created_at->format('H:i') }} · Shift #{{ $entry->shift_id }}</p>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $meta['badge'] }}">
                                            <x-icon class="fa-solid {{ $meta['icon'] }}" />{{ $meta['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 min-w-[220px]">
                                        <p class="text-xs leading-relaxed text-ink-700">{{ $entry->reason ?: '—' }}</p>
                                        <p class="mt-1 text-[10px] text-ink-400">{{ $entry->user?->name ?? 'User #'.$entry->user_id }}</p>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @if($entry->order)
                                            <a href="{{ route('orders.show', $entry->order) }}" class="text-xs font-semibold text-primary-600 hover:underline">{{ $entry->order->invoice_number }}</a>
                                        @else
                                            <span class="text-xs text-ink-400">Operasional</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right whitespace-nowrap">
                                        <span class="font-mono text-sm font-semibold {{ $entry->amount >= 0 ? 'text-primary-600' : 'text-semantic-danger' }}">
                                            {{ $entry->amount >= 0 ? '+' : '-' }}Rp {{ number_format(abs((float) $entry->amount), 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-surface-100 text-ink-400"><x-icon class="fa-solid fa-book-open" /></div>
                                    <p class="text-sm font-semibold text-ink-900">Belum ada mutasi kas</p>
                                    <p class="max-w-sm mx-auto mt-1 text-xs leading-relaxed text-ink-400">Penjualan dan aktivitas kas baru akan muncul di sini. Penjualan lama tetap diperhitungkan pada saldo shift.</p>
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($entries->hasPages())
                    <div class="px-5 py-4 border-t border-border-200">{{ $entries->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
