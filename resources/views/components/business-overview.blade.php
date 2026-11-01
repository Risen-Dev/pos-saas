@props(['overview'])
<section class="grid gap-4 my-5 sm:grid-cols-2" aria-label="Penerimaan manual dan piutang">
    <div class="p-5 bg-white border rounded-lg border-border-200"><h2 class="text-sm font-semibold">Transfer & QRIS toko diterima</h2><p class="mt-2 text-xl font-bold">Rp {{ number_format($overview['manual_receipts'], 0, ',', '.') }}</p><p class="mt-2 text-xs text-ink-700">Diperiksa manual pada periode pilihan. Di luar laci tunai dan saldo QRIS otomatis.</p></div>
    <div class="p-5 bg-white border rounded-lg border-border-200"><h2 class="text-sm font-semibold">Total bon belum lunas</h2><p class="mt-2 text-xl font-bold">Rp {{ number_format($overview['debt'], 0, ',', '.') }}</p><p class="mt-2 text-xs text-ink-700">Saldo piutang saat ini, mencakup seluruh tanggal transaksi.</p>@can('feature-crm')<a href="{{ route('customers.index') }}" class="inline-block mt-3 text-sm font-semibold text-primary-700">Lihat pelanggan & bon →</a>@endcan</div>
</section>
