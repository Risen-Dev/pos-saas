<x-app-layout>
@if($order->payment_verified_at)<p class="p-4 text-sm bg-primary-50">Pembayaran diperiksa manual oleh petugas toko pada {{ $order->payment_verified_at }}. {{ $order->payment_reference ? 'Referensi: '.$order->payment_reference : '' }} Dana diterima langsung oleh toko.</p>@endif
    @section('title', 'Detail Struk Transaksi')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-modal-lg">

        <!-- Navigation Top Bar -->
        <div class="flex items-center justify-between mb-6 no-print-area">
            <a href="{{ route('orders.index') }}"
                class="inline-flex items-center gap-2 text-xs font-semibold transition-colors font-body text-ink-700 hover:text-primary-600">
                <x-icon class="text-xs fa-solid fa-arrow-left" />
                <span>Kembali ke Riwayat Transaksi</span>
            </a>

            <button id="btnPrintReceipt"
                class="inline-flex items-center h-10 gap-2 px-5 text-xs font-semibold text-white transition-colors rounded-md shadow-sm bg-primary-600 hover:bg-primary-700 font-body">
                <x-icon class="fa-solid fa-print" />
                <span>Cetak Struk Belanja</span>
            </button>
        </div>


        <section class="max-w-xl mx-auto my-6 space-y-4 no-print-area">
            @if(session('success'))
                <div role="status" class="flex items-start gap-3 p-4 text-sm border rounded-lg bg-primary-50 border-primary-100 text-primary-700">
                    <x-icon class="mt-0.5 fa-solid fa-circle-check" /><div><strong>Berhasil.</strong> {{ session('success') }}</div>
                </div>
            @endif
            @if($errors->any())
                <div role="alert" class="p-4 text-sm border rounded-lg bg-red-50 border-red-100 text-semantic-danger">
                    <div class="flex items-center gap-2 mb-2 font-semibold"><x-icon class="fa-solid fa-circle-exclamation" /> Tindakan belum dapat diproses</div>
                    <ul class="pl-5 space-y-1 text-xs list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @if($order->order_status === 'cancelled')
                <div class="flex items-start gap-3 p-4 border rounded-lg bg-red-50 border-red-100 text-semantic-danger">
                    <span class="flex items-center justify-center w-9 h-9 rounded-md bg-white/70 shrink-0"><x-icon class="fa-solid fa-ban" /></span>
                    <div><p class="text-sm font-semibold">Transaksi dibatalkan</p><p class="mt-1 text-xs leading-relaxed">{{ $order->cancellation_reason ?? 'Dibatalkan berdasarkan status penyedia pembayaran.' }}</p></div>
                </div>
            @elseif($order->payment_status === 'unpaid' && $order->payment_method === 'cash')
                @php($remainingPayment = max(0, $order->grand_total - $order->paid_amount + $order->change_amount))
                <form method="POST" action="{{ route('orders.payments', $order) }}" class="overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
                    @csrf
                    <input type="hidden" name="operation_key" value="{{ old('operation_key', (string) Illuminate\Support\Str::uuid()) }}">
                    <div class="flex items-start gap-3 p-5 border-b bg-primary-50/60 border-primary-100">
                        <span class="flex items-center justify-center w-10 h-10 rounded-md bg-primary-100 text-primary-700 shrink-0"><x-icon class="fa-solid fa-hand-holding-dollar" /></span>
                        <div class="flex-1"><h2 class="text-sm font-semibold text-ink-900">Terima Pembayaran Bon</h2><p class="mt-0.5 text-[11px] leading-relaxed text-ink-700">Catat uang muka atau pelunasan tunai ke shift aktif.</p></div>
                        <div class="text-right"><p class="text-[10px] uppercase tracking-wider font-bold text-ink-400">Sisa</p><p class="font-mono text-sm font-semibold text-primary-700">Rp {{ number_format($remainingPayment, 0, ',', '.') }}</p></div>
                    </div>
                    <div class="p-5 space-y-4">
                        <div><label class="block mb-1.5 text-xs font-semibold text-ink-700">Nominal Diterima</label><div class="relative"><span class="absolute text-xs font-semibold -translate-y-1/2 left-3 top-1/2 text-ink-400">Rp</span><input class="w-full h-11 pl-10 pr-3 text-sm border rounded-md border-border-200 focus:border-primary-500 focus:ring-primary-500" type="text" data-rupiah-input name="amount" min="1" max="{{ $remainingPayment }}" required placeholder="0"></div></div>
                        <div><label for="field-reason" class="block mb-1.5 text-xs font-semibold text-ink-700">Catatan Pembayaran</label><input id="field-reason" class="w-full h-11 px-3 text-sm border rounded-md border-border-200 focus:border-primary-500 focus:ring-primary-500" name="reason" maxlength="500" required placeholder="Contoh: DP tahap 1 atau pelunasan"></div>
                        <button class="inline-flex items-center justify-center w-full h-11 gap-2 text-xs font-semibold text-white rounded-md bg-primary-600 hover:bg-primary-700"><x-icon class="fa-solid fa-plus" /> Catat Pembayaran</button>
                    </div>
                </form>

                @if(auth()->user()->role === 'admin' && $order->paid_amount == 0)
                    <details class="overflow-hidden border rounded-lg bg-surface-0 border-border-200 group">
                        <summary class="flex items-center justify-between p-4 text-xs font-semibold cursor-pointer text-semantic-danger hover:bg-red-50/50"><span class="flex items-center gap-2"><x-icon class="fa-solid fa-ban" /> Batalkan Bon Tanpa Pembayaran</span><x-icon class="transition-transform fa-solid fa-chevron-down group-open:rotate-180" /></summary>
                        <form method="POST" action="{{ route('orders.cancel', $order) }}" class="p-5 pt-1 space-y-3 border-t border-border-200">
                            @csrf
                            <p class="text-[11px] leading-relaxed text-ink-400">Stok akan dikembalikan dan piutang transaksi ini akan dihapus. Gunakan hanya bila transaksi memang batal.</p>
                            <div><label for="field-reason" class="block mb-1.5 text-xs font-semibold text-ink-700">Alasan Pembatalan</label><input id="field-reason" class="w-full h-11 px-3 text-sm border rounded-md border-border-200 focus:border-semantic-danger focus:ring-red-200" name="reason" maxlength="500" required placeholder="Tuliskan alasan pembatalan"></div>
                            <button class="inline-flex items-center justify-center h-10 gap-2 px-4 text-xs font-semibold text-white rounded-md bg-semantic-danger hover:opacity-90"><x-icon class="fa-solid fa-trash-can" /> Batalkan Bon</button>
                        </form>
                    </details>
                @endif
            @endif

            @if(auth()->user()->role === 'admin' && $order->payment_status === 'paid' && $order->payment_method === 'cash' && $order->order_status === 'completed')
                <details class="overflow-hidden border rounded-lg bg-surface-0 border-border-200 group">
                    <summary class="flex items-center justify-between p-4 text-xs font-semibold cursor-pointer text-accent-700 hover:bg-accent-100/40"><span class="flex items-center gap-2"><x-icon class="fa-solid fa-rotate-left" /> Retur & Refund Tunai</span><x-icon class="transition-transform fa-solid fa-chevron-down group-open:rotate-180" /></summary>
                    <form method="POST" action="{{ route('orders.returns', $order) }}" class="p-5 pt-3 space-y-4 border-t border-border-200">
                        @csrf
                        <input type="hidden" name="operation_key" value="{{ old('operation_key', (string) Illuminate\Support\Str::uuid()) }}">
                        <div class="p-3 text-[11px] leading-relaxed rounded-md bg-accent-100/50 text-accent-700">Refund dihitung proporsional setelah diskon dan pajak. Bahan resep tidak otomatis dikembalikan.</div>
                        <div><label for="field-item_id" class="block mb-1.5 text-xs font-semibold text-ink-700">Item yang Diretur</label><select id="field-item_id" name="item_id" required class="w-full h-11 px-3 text-sm border rounded-md border-border-200 focus:border-primary-500 focus:ring-primary-500">@foreach($order->items as $item)<option value="{{ $item->id }}">{{ $item->product_name }} · {{ \App\Support\NumberFormat::quantity($item->quantity) }} unit dibeli</option>@endforeach</select></div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2"><div><label for="field-quantity" class="block mb-1.5 text-xs font-semibold text-ink-700">Jumlah Retur</label><input id="field-quantity" class="w-full h-11 px-3 text-sm border rounded-md border-border-200 focus:border-primary-500 focus:ring-primary-500" type="number" name="quantity" step="0.001" min="0.001" max="100000" required></div><label class="flex items-start gap-3 p-3 border rounded-md cursor-pointer border-border-200 bg-surface-100"><input class="mt-0.5 rounded border-border-200 text-primary-600 focus:ring-primary-500" type="checkbox" name="restock" value="1"><span><strong class="block text-xs text-ink-900">Kembalikan ke stok</strong><span class="text-[10px] leading-relaxed text-ink-400">Centang hanya jika barang masih layak dijual.</span></span></label></div>
                        <div><label for="field-reason" class="block mb-1.5 text-xs font-semibold text-ink-700">Alasan Retur</label><input id="field-reason" class="w-full h-11 px-3 text-sm border rounded-md border-border-200 focus:border-primary-500 focus:ring-primary-500" name="reason" maxlength="255" required placeholder="Contoh: ukuran tidak sesuai"></div>
                        <button class="inline-flex items-center justify-center h-10 gap-2 px-4 text-xs font-semibold text-white rounded-md bg-accent-700 hover:opacity-90"><x-icon class="fa-solid fa-money-bill-transfer" /> Proses Retur & Uang Keluar</button>
                    </form>
                </details>
            @endif

            @if($returns->isNotEmpty())
                <div class="overflow-hidden border rounded-lg bg-surface-0 border-border-200">
                    <div class="flex items-center justify-between p-4 border-b bg-surface-100 border-border-200"><h2 class="text-xs font-semibold text-ink-900"><x-icon class="mr-2 fa-solid fa-clock-rotate-left text-ink-400" />Riwayat Retur</h2><strong class="font-mono text-xs text-semantic-danger">-Rp {{ number_format($returns->sum('amount'), 0, ',', '.') }}</strong></div>
                    <div class="divide-y divide-border-200">@foreach($returns as $returned)<div class="flex items-start justify-between gap-4 p-4 text-xs"><div><p class="font-semibold text-ink-900">Item #{{ $returned->order_item_id }} · {{ \App\Support\NumberFormat::quantity($returned->quantity) }} unit</p><p class="mt-1 text-[10px] text-ink-400">{{ $returned->restock ? 'Dikembalikan ke stok' : 'Tanpa restok' }}</p></div><span class="font-mono font-semibold whitespace-nowrap text-semantic-danger">-Rp {{ number_format($returned->amount, 0, ',', '.') }}</span></div>@endforeach</div>
                </div>
            @endif
        </section>

        <div class="flex gap-3 justify-center my-4 no-print-area">
            <a class="underline" target="_blank" rel="noopener" href="{{ route('orders.print', ['id' => $order->id, 'paper' => 58]) }}">Cetak 58 mm</a>
            <a class="underline" target="_blank" rel="noopener" href="{{ route('orders.print', ['id' => $order->id, 'paper' => 80]) }}">Cetak 80 mm</a>
        </div>
        <!-- Digital Receipt Card Simulation -->
        <div id="receipt"
            class="mx-auto bg-surface-0 border border-border-200 rounded-lg shadow-sm p-6 md:p-8 max-w-[420px]">

            <!-- Store Brand Header -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold tracking-tight uppercase font-heading text-ink-900">
                    {{ auth()->user()->tenant->name ?? 'GROWPOS STORE' }}
                </h2>
                <p class="font-body text-[11px] text-ink-700 mt-1 leading-snug">
                    {{ auth()->user()->tenant->address ?? 'Alamat Operasional Toko' }}<br>
                    Telp/WA: {{ auth()->user()->tenant->phone ?? '-' }}
                </p>
            </div>

            <div class="my-4 border-b border-dashed border-border-200"></div>

            <!-- Receipt Meta Info -->
            <div class="space-y-1.5 font-mono text-xs text-ink-900">
                {{-- <p>Tipe:
                    <strong>
                        {{ strtoupper($order->order_type ?? 'DINE_IN') }}
                        @if ($order->order_type === 'dine_in' && $order->table_number)
                            (Meja {{ $order->table_number }})
                        @endif
                    </strong>
                </p> --}}
                <div class="flex justify-between">
                    <span class="text-ink-400">Tipe:</span>
                    <span class="font-semibold text-ink-900">{{ strtoupper($order->order_type ?? 'DINE_IN') }}
                        @if ($order->order_type === 'dine_in' && $order->table_number)
                            (Meja {{ $order->table_number }})
                        @endif
                    </span>
                </div>

                <div class="flex justify-between">
                    <span class="text-ink-400">No. Invoice:</span>
                    <span class="font-semibold text-ink-900">{{ $order->invoice_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-400">Waktu:</span>
                    <span>{{ $order->created_at->format('d/m/Y H:i') }} WIB</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-400">Kasir:</span>
                    <span>{{ $order->user->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-400">Pelanggan:</span>
                    <span class="font-semibold text-ink-900">{{ $order->customer->name ?? 'Pelanggan Umum' }}</span>
                </div>
            </div>

            <div class="my-4 border-b border-dashed border-border-200"></div>

            <!-- Purchased Item Details -->
            <div class="space-y-2.5 font-mono text-xs">
                @foreach ($order->items as $item)
                    <div>
                        <div class="flex justify-between font-semibold leading-tight text-ink-900">
                            <span class="pr-2 uppercase truncate">{{ $item->product_name }}</span>
                            <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-[11px] text-ink-400 mt-0.5">
                            {{ \App\Support\NumberFormat::quantity($item->quantity) }}x @ Rp {{ number_format($item->price, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="my-4 border-b border-dashed border-border-200"></div>

            <!-- Totals & Payment Calculations -->
            <div class="space-y-1.5 font-mono text-xs text-ink-900">
                <div class="flex justify-between text-ink-700">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                @if ($order->discount > 0)
                    <div class="flex justify-between text-semantic-danger">
                        <span>Diskon</span>
                        <span>-Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if ($order->loyalty_discount > 0)
                    <div class="flex justify-between text-semantic-danger">
                        <span>{{ $order->loyalty_program_type === 'stamps' ? 'Reward stamp loyalitas ('.number_format($order->stamps_redeemed, 0, ',', '.').' stamp)' : 'Potongan poin loyalitas ('.number_format($order->points_redeemed, 0, ',', '.').' poin)' }}</span>
                        <span>-Rp {{ number_format($order->loyalty_discount, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if ($order->tax > 0)
                    <div class="flex justify-between text-ink-700">
                        <span>Pajak Outlet</span>
                        <span>+Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                    </div>
                @endif

                <div
                    class="flex justify-between pt-2 mt-2 text-sm font-bold border-t border-double border-border-200 text-primary-600">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between pt-2 text-ink-700">
                    <span>Bayar ({{ \App\Support\PaymentMethod::label($order->payment_method) }})</span>
                    <span>Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-ink-700">
                    <span>Kembali</span>
                    <span>Rp {{ number_format($order->change_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Status Badge & Thank You Note -->
            <div class="mt-6 text-center">
                @if ($order->payment_status === 'paid')
                    <span
                        class="inline-flex items-center px-3 py-1 mb-3 text-xs font-bold rounded-full font-heading bg-primary-100 text-primary-700">
                        ✓ LUNAS
                    </span>
                @else
                    <span
                        class="inline-flex items-center px-3 py-1 mb-3 text-xs font-bold rounded-full font-heading bg-accent-100 text-accent-700">
                        ! PIUTANG / BON
                    </span>
                @endif

                <p class="font-body text-[11px] text-ink-700 leading-tight">
                    Terima kasih atas kunjungan Anda!<br>
                    Simpan struk ini sebagai bukti transaksi.
                </p>
                <div class="mt-3 font-mono text-[10px] text-ink-400">
                    Powered by GrowPOS SaaS
                </div>
            </div>
        </div>

        <!-- WhatsApp Share CTA Action -->
        <div class="max-w-[420px] mx-auto mt-4 no-print-area">
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->customer->phone ?? '') }}?text=Terima%20kasih%20sudah%20berbelanja%20di%20{{ urlencode(auth()->user()->tenant->name ?? 'Toko Kami') }}!%20Berikut%20rincian%20struk%20Anda:%20{{ urlencode(route('orders.show', $order->id)) }}"
                target="_blank"
                class="inline-flex items-center justify-center w-full gap-2 px-5 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-semantic-success hover:bg-emerald-600 font-body">
                <x-icon class="text-base fa-brands fa-whatsapp" />
                <span>Kirim Struk Digital ke WhatsApp</span>
            </a>
        </div>
    </div>

    <iframe id="printFrame" class="hidden"></iframe>

    <script>
        const btnPrint = document.getElementById('btnPrintReceipt');

        btnPrint.onclick = function() {
            const url = "{{ route('orders.print', $order->id) }}";

            btnPrint.disabled = true;
            btnPrint.innerHTML = '<x-icon class="fa-solid fa-spinner animate-spin" /> Menyiapkan...';

            const printWindow = window.open(url, '_blank', 'width=400,height=600,top=100,left=100');

            setTimeout(() => {
                btnPrint.disabled = false;
                btnPrint.innerHTML = '<x-icon class="fa-solid fa-print" /> Cetak Struk Belanja';
            }, 1800);
        };
    </script>
</x-app-layout>
