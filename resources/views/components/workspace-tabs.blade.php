@props(['section'])
@php
    $admin = auth()->user()->role === 'admin';
    $tenant = auth()->user()->tenant;
    $groups = [
        'catalog' => [['products.index', $tenant?->catalogLabel() ?? 'Produk', true], ['categories.index', 'Kategori', true], ['menu.configure', $tenant?->hasBusinessModule('food') ? 'Varian, tambahan & resep' : 'Varian & pilihan', true]],
        'stock' => [['inventory.index', 'Tambah stok & penyesuaian', true], ['inventory.history', 'Riwayat stok', true], ['stocktakes.index', 'Hitung stok', true], ['purchases.index', 'Pembelian', true], ['suppliers.index', 'Pemasok', true], ['materials.index', 'Bahan baku', $tenant?->hasBusinessModule('food')]],
        'transactions' => [['orders.index', 'Riwayat', true], ['customers.index', 'Pelanggan & bon', \Illuminate\Support\Facades\Gate::allows('feature-crm')], ['discounts.index', 'Promo', true], ['shifts.index', 'Sesi kasir', true], ['payments.review', 'Periksa QRIS otomatis', $admin && \Illuminate\Support\Facades\Gate::allows('feature-qris')]],
        'reports' => [['reports.index', 'Penjualan', true], ['cash.index', 'Kas & pengeluaran', $admin], ['finance.index', 'Saldo QRIS & pencairan', $admin && \Illuminate\Support\Facades\Gate::allows('feature-qris')], ['reports.exports-list', 'Unduhan', true], ['reports.ai', 'Tanya AI', \Illuminate\Support\Facades\Gate::allows('feature-ai-analytics')]],
    ];
@endphp
@if(auth()->user()->role !== 'kasir')
<nav aria-label="Menu {{ $section }}" class="flex flex-wrap gap-2 px-4 py-3 border-b bg-white border-border-200">
    @foreach($groups[$section] ?? [] as [$target, $label, $visible])
        @if($visible)<a href="{{ route($target) }}" class="min-h-11 px-3 py-3 text-sm font-semibold rounded-md {{ request()->routeIs($target) ? 'text-primary-700 bg-primary-50' : 'text-ink-700 hover:bg-surface-100' }}" @if(request()->routeIs($target)) aria-current="page" @endif>{{ $label }}</a>@endif
    @endforeach
</nav>
@endif
