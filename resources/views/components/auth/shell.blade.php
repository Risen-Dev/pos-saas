@props(['eyebrow' => 'GROWPOS WORKSPACE', 'heading' => 'Kelola bisnis, tanpa ribet.', 'description' => 'Satu tempat untuk penjualan, inventaris, dan operasional bisnis Anda.', 'showcase' => false])
<div class="min-h-screen bg-surface-0 text-ink-900 lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)]">
    <main class="flex min-h-screen flex-col px-6 py-7 sm:px-10 lg:px-16 xl:px-24">
        <header class="flex items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 text-xl font-bold tracking-[-0.04em] text-ink-900" aria-label="GrowPOS beranda">
                <img src="{{ asset('img/growpos_logo.png') }}" alt="" class="h-9 w-9 object-contain">
                <span>GrowPOS<span class="text-primary-600">.</span></span>
            </a>
            <span class="hidden text-xs font-medium text-ink-400 sm:block">YOUR BUSINESS WORKSPACE</span>
        </header>
        <div class="mx-auto flex w-full max-w-[420px] flex-1 flex-col justify-center py-12 lg:py-16">
            {{ $slot }}
        </div>
        <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-border-200 pt-5 text-xs text-ink-400">
            <span>© {{ date('Y') }} GrowPOS</span>
            <span>Dirancang untuk bisnis yang terus berkembang.</span>
        </footer>
    </main>
    <aside class="relative hidden min-h-screen flex-col justify-between overflow-hidden bg-primary-900 p-12 text-white lg:flex xl:p-16">
        <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full border border-white/10"></div>
        <div class="pointer-events-none absolute -right-16 -top-16 h-72 w-72 rounded-full border border-white/10"></div>
        <div class="relative z-10 flex items-center justify-between text-xs font-semibold tracking-[0.22em] text-white/65">
            <span>{{ $eyebrow }}</span><span class="h-2 w-2 rounded-full bg-primary-300"></span>
        </div>
        <div class="relative z-10 my-14 space-y-9">
            <div class="space-y-4"><h2 class="max-w-lg text-4xl font-semibold leading-[1.12] tracking-[-0.055em] xl:text-5xl">{{ $heading }}</h2>
            <p class="max-w-md text-sm leading-7 text-white/70">{{ $description }}</p></div>
            @if ($showcase)
                <div class="max-w-lg overflow-hidden rounded-2xl border border-white/15 bg-white p-5 text-ink-900 shadow-2xl shadow-black/15 sm:p-7">
                    <div class="mb-7 flex items-center justify-between"><div><p class="text-xs font-semibold text-ink-400">RINGKASAN BISNIS</p><p class="mt-1 text-lg font-semibold tracking-tight">Overview</p></div><span class="rounded-full bg-primary-50 px-3 py-1 text-xs font-medium text-primary-700">Dashboard</span></div>
                    <div class="grid grid-cols-2 gap-3"><div class="rounded-xl bg-surface-100 p-4"><p class="text-xs text-ink-400">Penjualan</p><p class="mt-2 text-xl font-bold">Rp 12,4 jt</p></div><div class="rounded-xl bg-surface-100 p-4"><p class="text-xs text-ink-400">Transaksi</p><p class="mt-2 text-xl font-bold">128</p></div></div>
                    <div class="mt-5 flex h-24 items-end gap-2 border-b border-border-200 pb-1" aria-hidden="true">@foreach ([36, 58, 45, 68, 51, 82, 65, 91, 75, 100, 86, 96] as $bar)<span class="flex-1 rounded-t bg-primary-600/80" style="height: {{ $bar }}%"></span>@endforeach</div>
                    <p class="mt-3 text-[11px] text-ink-400">Ilustrasi tampilan • Bukan data transaksi aktual</p>
                </div>
            @endif
        </div>
        <p class="relative z-10 text-xs text-white/55">Sederhana untuk digunakan. Siap mendukung pertumbuhan.</p>
    </aside>
</div>
