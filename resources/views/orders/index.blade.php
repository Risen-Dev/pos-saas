<x-app-layout>
    @section('title', 'Riwayat Transaksi')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop">

        <!-- Header Page Section -->
        <x-page-header split>
            <div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                    Riwayat Transaksi
                </h1>
                <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                    Pantau semua catatan penjualan, status pembayaran, dan riwayat struk belanja pelanggan.
                </p>
            </div>

            <a href="{{ route('pos.index') }}"
                class="inline-flex items-center justify-center gap-2 px-5 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body md:text-sm shrink-0">
                <x-icon class="text-xs fa-solid fa-plus" />
                <span>Transaksi Baru (POS)</span>
            </a>
        </x-page-header>

        <!-- Table Container (Spesifikasi GrowPOS: Row Height 48px) -->
        <div class="mb-6 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr
                            class="h-12 text-xs font-semibold tracking-wider uppercase border-b bg-surface-100 border-border-200 font-heading text-ink-700">
                            <th class="px-5 py-3">No. Invoice & Tanggal</th>
                            <th class="px-5 py-3">Pelanggan & Kasir</th>
                            <th class="px-5 py-3 text-center">Status Pembayaran</th>
                            <th class="px-5 py-3 text-right">Total Transaksi</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y font-body md:text-sm text-ink-900 divide-border-200">
                        @forelse($orders as $o)
                            <tr class="h-12 transition-colors hover:bg-surface-100/60">

                                <!-- Invoice Number & Date -->
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center w-8 h-8 border rounded-md bg-primary-50 text-primary-600 border-primary-100 shrink-0">
                                            <x-icon class="text-xs fa-solid fa-receipt" />
                                        </div>
                                        <div>
                                            <span class="block font-mono font-semibold leading-tight text-ink-900">
                                                {{ $o->invoice_number }}
                                            </span>
                                            <span class="text-[11px] font-normal text-ink-400 mt-0.5 block">
                                                {{ $o->created_at->format('d M Y, H:i') }} WIB
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Customer & Operator -->
                                <td class="px-5 py-3">
                                    <div>
                                        <span class="block font-semibold leading-tight text-ink-900">
                                            {{ $o->customer->name ?? 'Pelanggan Umum (Guest)' }}
                                        </span>
                                        <span class="text-[11px] font-normal text-ink-400 mt-0.5 block">
                                            Kasir: {{ $o->user->name ?? 'Sistem' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Payment Status Pill Badge -->
                                <td class="px-5 py-3 text-center">
                                    @if ($o->payment_status === 'paid')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-semibold text-primary-700 bg-primary-100 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary-600"></span>
                                            Lunas
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-semibold text-accent-700 bg-accent-100 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-accent-500"></span>
                                            Bon (Hutang)
                                        </span>
                                    @endif
                                </td>

                                <!-- Grand Total (Monospace Price) -->
                                <td class="px-5 py-3 text-right">
                                    <span class="font-mono text-sm font-semibold text-ink-900">
                                        Rp {{ number_format($o->grand_total, 0, ',', '.') }}
                                    </span>
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-5 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('orders.show', $o->id) }}"
                                            class="inline-flex items-center gap-1.5 h-8 px-3 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-600 hover:text-white rounded-md transition-colors">
                                            <x-icon class="text-xs fa-solid fa-file-invoice" />
                                            <span>Detail & Struk</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 mb-2 rounded-full bg-primary-50 text-primary-600">
                                            <x-icon class="text-xl fa-solid fa-receipt" />
                                        </div>
                                        <p class="text-sm font-semibold font-heading text-ink-900">Belum ada transaksi
                                            recorded</p>
                                        <p class="font-body text-xs text-ink-700 mt-0.5">Semua transaksi kasir baru akan
                                            muncul di sini.</p>
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
            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>
