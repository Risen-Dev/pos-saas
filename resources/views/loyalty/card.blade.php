<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Kartu Member {{ $tenant->name }} — GrowPOS</title>
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-surface-100 text-ink-900">
    @php
        $availableRewards = (int) floor($customer->points / $step);
        $logo = $tenant->img_logo ? \Illuminate\Support\Facades\Storage::url($tenant->img_logo) : null;
    @endphp
    <main class="max-w-md px-4 py-8 mx-auto space-y-5 sm:py-12">
        <header class="text-center">
            <p class="text-xs font-semibold tracking-[.16em] text-primary-700 uppercase">Kartu Loyalitas</p>
            <div class="flex items-center justify-center gap-3 mt-3">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo {{ $tenant->name }}" class="object-cover w-11 h-11 rounded-xl">
                @else
                    <span class="grid w-11 h-11 font-bold text-white rounded-xl bg-primary-700 place-items-center">{{ mb_substr($tenant->name, 0, 1) }}</span>
                @endif
                <h1 class="text-xl font-bold">{{ $tenant->name }}</h1>
            </div>
        </header>

        <section class="p-6 bg-white border shadow-sm rounded-2xl border-border-200">
            <p class="text-sm text-ink-700">Halo, {{ $customer->name }}</p>
            <div class="flex items-end justify-between gap-4 mt-4">
                <div>
                    <p class="text-xs text-ink-700">Saldo poin</p>
                    <p class="text-4xl font-bold text-primary-700">{{ number_format($customer->points, 0, ',', '.') }}</p>
                </div>
                <div class="px-3 py-2 text-xs font-semibold text-primary-800 rounded-lg bg-primary-50">{{ $customer->loyalty_member_code }}</div>
            </div>

            @if ($programActive)
                <div class="mt-5">
                    <div class="flex justify-between gap-3 text-xs text-ink-700">
                        <span>Progres reward berikutnya</span>
                        <span>{{ number_format($customer->points % $step, 0, ',', '.') }} / {{ number_format($step, 0, ',', '.') }} poin</span>
                    </div>
                    @if ($availableRewards > 0)
                        <p class="mt-3 text-sm font-semibold text-primary-800">Reward tersedia: {{ number_format($availableRewards, 0, ',', '.') }} × potongan Rp {{ number_format($rewardValue, 0, ',', '.') }}.</p>
                    @endif
                    <p class="mt-3 text-xs text-ink-700">{{ number_format($step, 0, ',', '.') }} poin dapat ditukar dengan potongan Rp {{ number_format($rewardValue, 0, ',', '.') }}.</p>
                </div>
            @else
                <p class="p-3 mt-5 text-sm rounded-lg bg-amber-50 text-amber-900">Program poin sedang tidak aktif. Kartu dan saldo poin Anda tetap tersimpan.</p>
            @endif
        </section>

        <section class="p-5 bg-white border shadow-sm rounded-2xl border-border-200">
            <h2 class="font-semibold">Aktivitas poin</h2>
            <ul class="mt-3 divide-y divide-border-200">
                @forelse ($activity as $entry)
                    <li class="flex items-start justify-between gap-3 py-3 text-sm">
                        <span>
                            <span class="block font-medium">{{ match ($entry->type) { 'earned' => 'Poin dari pembelian', 'redeemed' => 'Poin digunakan', 'redemption_reversed' => 'Poin penukaran dikembalikan', 'redemption_restored' => 'Poin dikembalikan karena retur', 'return_reversal' => 'Poin disesuaikan karena retur', default => 'Penyesuaian poin' } }}</span>
                            <time class="text-xs text-ink-700" datetime="{{ $entry->created_at->toIso8601String() }}">{{ $entry->created_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}</time>
                        </span>
                        <strong class="shrink-0 {{ $entry->points_delta > 0 ? 'text-primary-700' : 'text-ink-900' }}">{{ $entry->points_delta > 0 ? '+' : '' }}{{ number_format($entry->points_delta, 0, ',', '.') }}</strong>
                    </li>
                @empty
                    <li class="py-3 text-sm text-ink-700">Belum ada aktivitas poin.</li>
                @endforelse
            </ul>
            <p class="mt-3 text-[11px] text-ink-700">Kode ini digunakan kasir untuk menemukan kartu member Anda. Tunjukkan kode kepada kasir saat bertransaksi.</p>
        </section>

        <footer class="text-xs text-center text-ink-700">Kartu digital oleh GrowPOS</footer>
    </main>
</body>

</html>
