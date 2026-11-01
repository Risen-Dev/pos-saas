<!DOCTYPE html>
<html class="motion-safe:scroll-smooth [&_*]:[-webkit-tap-highlight-color:transparent] motion-reduce:[&_*]:!scroll-auto motion-reduce:[&_*]:!transition-none motion-reduce:[&_*]:!animate-none motion-reduce:[&_*::before]:!animate-none motion-reduce:[&_*::after]:!animate-none" lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail transaksi · GrowPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f5f7f3] text-[#18372d] [font-family:Inter,_system-ui,_sans-serif]">
    <header
        class="sticky top-0 z-20 flex items-center justify-between gap-4 px-4 py-3 bg-white border-b border-[#e2e9df] sm:px-6">
        <div class="flex items-center min-w-0 gap-3">
            <a href="{{ route('pos.index', ['panel' => 'history']) }}"
                class="grid w-10 h-10 place-items-center rounded-lg border border-[#dae4dd] hover:bg-[#f1f7f3]"
                aria-label="Kembali ke riwayat"><x-icon class="fa-solid fa-arrow-left" /></a>
            <div class="min-w-0">
                <p class="text-sm font-bold truncate">Detail transaksi</p>
                <p class="text-[10px] text-[#65796f] truncate">{{ $order->invoice_number }}</p>
            </div>
        </div>
        <a href="{{ route('orders.print', ['id' => $order->id]) }}" target="_blank" rel="noopener"
            class="inline-flex items-center h-10 gap-2 px-4 rounded-lg bg-[#17694d] text-white text-xs font-semibold hover:bg-[#12523d]"><x-icon
                class="fa-solid fa-print" /><span class="hidden sm:inline">Cetak struk</span></a>
    </header>

    <main class="w-full max-w-3xl px-4 py-6 mx-auto sm:px-6">
        <section class="p-5 bg-white border border-[#e1e9e4] rounded-2xl shadow-sm sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4 pb-5 border-b border-[#edf1ee]">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#789085]">Transaksi</p>
                    <h1 class="mt-1 text-lg font-bold">{{ $order->invoice_number }}</h1>
                    <p class="mt-1 text-xs text-[#65796f]">
                        {{ optional($order->sold_at ?? $order->created_at)->format('d M Y · H:i') }} WIB ·
                        {{ $order->customer->name ?? 'Pelanggan umum' }}</p>
                </div>
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold {{ $order->payment_status === 'paid' ? 'bg-[#e4f5ea] text-[#17694d]' : 'bg-amber-50 text-amber-700' }}"><span
                        class="w-1.5 h-1.5 rounded-full {{ $order->payment_status === 'paid' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>{{ $order->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}</span>
            </div>

            <div class="py-2 divide-y divide-[#edf1ee]">
                @foreach ($order->items as $item)
                    <div class="flex items-start justify-between gap-4 py-4">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold">{{ $item->product_name }}</p>
                            <p class="mt-1 text-xs text-[#65796f]">
                                {{ \App\Support\NumberFormat::quantity($item->quantity) }}
                                {{ $item->unit_name ?: 'pcs' }} × Rp {{ number_format($item->price, 0, ',', '.') }}
                            </p>
                            @if ($item->note)
                                <p class="mt-1 text-[10px] text-[#789085]">{{ $item->note }}</p>
                            @endif
                        </div>
                        <p class="text-sm font-bold shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>

            <div class="pt-4 space-y-2 border-t border-[#edf1ee] text-sm">
                <div class="flex justify-between gap-4 text-[#65796f]"><span>Subtotal</span><span>Rp
                        {{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
                @if ($order->discount > 0)
                    <div class="flex justify-between gap-4 text-[#65796f]"><span>Diskon</span><span>-Rp
                            {{ number_format($order->discount, 0, ',', '.') }}</span></div>
                @endif
                @if ($order->loyalty_discount > 0)
                    <div class="flex justify-between gap-4 text-[#65796f]"><span>Potongan poin loyalitas ({{ number_format($order->points_redeemed,0,',','.') }} poin)</span><span>-Rp {{ number_format($order->loyalty_discount,0,',','.') }}</span></div>
                @endif
                @if ($order->tax > 0)
                    <div class="flex justify-between gap-4 text-[#65796f]"><span>Pajak</span><span>Rp
                            {{ number_format($order->tax, 0, ',', '.') }}</span></div>
                @endif
                <div class="flex items-end justify-between gap-4 pt-3 mt-3 border-t border-[#edf1ee]"><span
                        class="font-bold">Total</span><span class="text-xl font-bold">Rp
                        {{ number_format($order->grand_total, 0, ',', '.') }}</span></div>
            </div>

            <div class="grid grid-cols-1 gap-3 mt-5 sm:grid-cols-2">
                <div class="p-4 rounded-xl bg-[#f7f9f6]">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#789085]">Metode pembayaran</p>
                    <p class="mt-1 text-sm font-semibold">
                        {{ $order->payment_method === 'cash' ? 'Tunai' : \App\Support\PaymentMethod::label($order->payment_method) }}</p>
                </div>
                <div class="p-4 rounded-xl bg-[#f7f9f6]">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#789085]">Kasir</p>
                    <p class="mt-1 text-sm font-semibold">{{ $order->user->name ?? 'Kasir' }}</p>
                </div>
            </div>
        </section>
    </main>
</body>

</html>
