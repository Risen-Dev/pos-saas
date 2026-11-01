<x-app-layout>
    @section('title', 'Detail Pelanggan')
    <div class="max-w-6xl p-4 mx-auto space-y-6 sm:p-6" x-data="{ copied: false, async copyCard(url) { try { await navigator.clipboard.writeText(url); this.copied = true; setTimeout(() => this.copied = false, 1800); } catch { window.prompt('Salin tautan kartu', url); } } }">
        <a href="{{ route('customers.index') }}" class="font-semibold underline text-primary-700">Kembali ke pelanggan</a>
        <div><h1 class="text-2xl font-bold">{{ $customer->name }}</h1><p class="mt-1 text-sm text-ink-700">{{ $customer->phone ?: 'Nomor kontak belum diisi' }}</p></div>
        @if (session('success'))
            <div role="status" class="p-4 text-sm border rounded-lg bg-primary-50 border-primary-200 text-primary-900">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="p-4 text-sm border rounded-lg bg-red-50 border-red-200 text-red-900">{{ $errors->first() }}</div>
        @endif
        <dl class="grid gap-4 sm:grid-cols-3">
            @can('feature-loyalty')
            <div class="p-5 bg-white border rounded-lg border-border-200"><dt>Status</dt><dd class="mt-2 font-bold">{{ $customer->is_member ? 'Member' : 'Pelanggan umum' }}</dd></div>
            <div class="p-5 bg-white border rounded-lg border-border-200"><dt>Poin loyalitas</dt><dd class="mt-2 font-bold">{{ number_format($customer->points,0,',','.') }}</dd></div>
            @endcan
            <div class="p-5 bg-white border rounded-lg border-border-200"><dt>Sisa piutang</dt><dd class="mt-2 font-bold">Rp {{ number_format($customer->total_debt,0,',','.') }}</dd></div>
        </dl>
        @can('feature-loyalty')
            @if ($customer->is_member && $loyaltyCardUrl)
                <section class="p-5 space-y-3 bg-white border rounded-lg border-border-200">
                    <div>
                        <h2 class="font-semibold">Kartu loyalitas digital</h2>
                        <p class="mt-1 text-sm text-ink-700">Kode member: <strong>{{ $customer->loyalty_member_code }}</strong></p>
                    </div>
                    <label class="block text-xs font-semibold">Tautan kartu
                        <input readonly value="{{ $loyaltyCardUrl }}" class="block w-full mt-1 text-sm rounded-md border-border-200" aria-label="Tautan kartu loyalitas">
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="copyCard(@js($loyaltyCardUrl))" class="inline-flex items-center min-h-11 px-4 text-sm font-semibold text-white rounded-md bg-primary-700"> <span x-text="copied ? 'Tautan tersalin' : 'Salin tautan'"></span></button>
                        @if ($whatsappShareUrl)
                            <a href="{{ $whatsappShareUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center min-h-11 px-4 text-sm font-semibold border rounded-md border-border-200 text-ink-900">Bagikan lewat WhatsApp</a>
                        @endif
                        <a href="{{ $loyaltyCardUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center min-h-11 px-4 text-sm font-semibold border rounded-md border-border-200 text-ink-900">Lihat kartu</a>
                    </div>
                    <p class="text-xs text-ink-700">Admin meninjau pesan di WhatsApp lalu mengirimkannya. Tautan ini tetap sama untuk pembelian berikutnya.</p>
                </section>
            @elseif ($customer->phone)
                <form method="POST" action="{{ route('customers.loyalty.join', $customer) }}" class="flex flex-wrap items-center justify-between gap-3 p-5 bg-white border rounded-lg border-border-200">
                    @csrf
                    <div><h2 class="font-semibold">Buat kartu loyalitas</h2><p class="mt-1 text-sm text-ink-700">Pelanggan dapat melihat poin melalui tautan kartu digital.</p></div>
                    <button class="min-h-11 px-4 text-sm font-semibold text-white rounded-md bg-primary-700">Daftarkan sebagai member</button>
                </form>
            @endif
        @endcan
        @can('feature-loyalty')
            <section class="p-5 bg-white border rounded-lg border-border-200">
                <h2 class="font-semibold">Riwayat poin</h2>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm text-left"><thead><tr class="border-b"><th class="py-2 pr-4">Aktivitas</th><th class="py-2 pr-4">Tanggal</th><th class="py-2 text-right">Perubahan</th></tr></thead>
                        <tbody>@forelse($loyaltyActivity as $entry)<tr class="border-b"><td class="py-3 pr-4">{{ $entry->description ?: str($entry->type)->replace('_', ' ')->ucfirst() }}</td><td class="py-3 pr-4 whitespace-nowrap">{{ $entry->created_at->format('d/m/Y H:i') }}</td><td class="py-3 text-right font-semibold {{ $entry->points_delta > 0 ? 'text-primary-700' : '' }}">{{ $entry->points_delta > 0 ? '+' : '' }}{{ number_format($entry->points_delta,0,',','.') }}</td></tr>@empty<tr><td colspan="3" class="py-4 text-center text-ink-700">Belum ada aktivitas poin.</td></tr>@endforelse</tbody>
                    </table>
                </div>
            </section>
        @endcan
        <div class="overflow-x-auto bg-white border rounded-lg border-border-200">
            <table class="w-full text-sm text-left"><caption class="p-4 font-semibold text-left">Riwayat transaksi{{ auth()->user()->role === 'kasir' ? ' yang Anda proses' : '' }}. Buka nota untuk menerima pembayaran bon.</caption>
                <thead><tr class="border-b"><th class="p-4">Nota</th><th class="p-4">Tanggal</th><th class="p-4">Total</th><th class="p-4">Pembayaran</th></tr></thead>
                <tbody>@forelse($orders as $order)<tr class="border-b"><td class="p-4"><a class="underline text-primary-700" href="{{ route('orders.show',$order) }}">{{ $order->invoice_number }}</a></td><td class="p-4 whitespace-nowrap">{{ ($order->sold_at ?? $order->created_at)->format('d/m/Y H:i') }}</td><td class="p-4 whitespace-nowrap">Rp {{ number_format($order->grand_total,0,',','.') }}</td><td class="p-4">{{ $order->order_status === 'cancelled' ? 'Dibatalkan' : ($order->payment_status === 'paid' ? 'Lunas' : 'Belum lunas') }}</td></tr>@empty<tr><td colspan="4" class="p-6 text-center">Belum ada transaksi yang dapat ditampilkan.</td></tr>@endforelse</tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>
</x-app-layout>
