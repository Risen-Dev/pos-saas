<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
            <meta name="theme-color" content="#eaf3ee">
    <title>Kartu Member {{ $tenant->name }} — GrowPOS</title>
    @vite(['resources/css/app.css', 'resources/css/loyalty-card.css', 'resources/js/loyalty-card.js'])
</head>

<body>
    @php
        $availableRewards = (int) floor($customer->points / $step);
        $pointsIntoStep = (int) $customer->points % $step;
        $pointsToNextReward = $pointsIntoStep === 0 ? 0 : $step - $pointsIntoStep;
        $progressPercent = min(100, (int) round(($pointsIntoStep / $step) * 100));
        $visiblePointBalance = max(0, (int) $customer->points);
        $stampProgressLabel = min($stampGoal, max(0, $stampProgress));
        $logo = $tenant->img_logo ? \Illuminate\Support\Facades\Storage::url($tenant->img_logo) : null;
        $memberCode = (string) ($customer->loyalty_member_code ?? '');
    @endphp

    <main class="loyalty-page">
        <header class="loyalty-header">
            <div class="merchant">
                <span class="merchant-mark" aria-hidden="true">
                    @if ($logo)<img src="{{ $logo }}" alt="">@else{{ mb_substr($tenant->name, 0, 1) }}@endif
                </span>
                <div class="merchant-copy"><h1>{{ $tenant->name }}</h1><p>Kartu loyalitas digital</p></div>
            </div>
            <span class="membership-state"><span class="state-dot" aria-hidden="true"></span>Member aktif</span>
        </header>

        <section class="loyalty-content" aria-label="Kartu loyalitas {{ $customer->name }}">
            <div class="card-column">
                <div class="card-shell" id="loyalty-card">
                    <article class="card-face card-front" aria-label="Sisi depan kartu member">
                        <svg class="card-ornament" viewBox="0 0 240 240" fill="none" aria-hidden="true">
                            <defs><linearGradient id="leafFoil" x1="38" y1="196" x2="192" y2="36" gradientUnits="userSpaceOnUse"><stop stop-color="#8de8ad" stop-opacity=".22"/><stop offset=".55" stop-color="#e7f8ad" stop-opacity=".7"/><stop offset="1" stop-color="#9affd1" stop-opacity=".36"/></linearGradient></defs>
                            <circle class="ornament-core" cx="132" cy="108" r="74" stroke-width=".8"/>
                            <circle cx="132" cy="108" r="89" stroke="currentColor" stroke-opacity=".26" stroke-width=".7" stroke-dasharray="1 5"/>
                            <path class="ornament-leaf" d="M127 155c-2-31 8-57 31-77 14-12 31-18 47-20-1 22-8 40-23 54-16 16-34 22-55 21Z"/>
                            <path class="ornament-leaf" d="M124 143c-22-3-39-13-50-31-7-12-10-25-9-38 18 3 32 10 42 23 12 15 17 30 17 46Z"/>
                            <path class="ornament-vein" d="M119 172c3-35 20-65 53-91M119 150c-13-22-27-37-44-47M120 172v-52"/>
                            <path d="M43 188c28-18 54-21 79-9 24 11 51 11 81-2" stroke="currentColor" stroke-opacity=".38" stroke-width=".8"/>
                            <circle cx="185" cy="43" r="2" fill="#dfffc2" fill-opacity=".9"/><path d="M185 36v14M178 43h14" stroke="#e8ffd4" stroke-opacity=".75" stroke-width=".7"/>
                        </svg>
                        <div class="card-front-inner">
                            <div>
                                <div class="card-topline">
                                    <div class="card-brand">
                                        <span class="card-brand-mark" aria-hidden="true">@if ($logo)<img src="{{ $logo }}" alt="">@else G.@endif</span>
                                        <span><strong>{{ $tenant->name }}</strong><small>LOYALTY MEMBER</small></span>
                                    </div>
                                    <span class="card-status">MEMBER AKTIF</span>
                                </div>
                                <div class="card-member">
                                    <span class="eyebrow">Kartu milik</span>
                                    <strong class="member-name">{{ $customer->name }}</strong>
                                    <span class="member-code">{{ $memberCode ?: 'Member GrowPOS' }}</span>
                                </div>
                            </div>

                            <div class="card-lower">
                                <div>
                                    <span class="balance-label">{{ $isStampProgram ? 'Stamp terkumpul' : 'Saldo poin Anda' }}</span>
                                    <p class="balance-value">{{ number_format($isStampProgram ? $visibleStampBalance : $visiblePointBalance, 0, ',', '.') }} <span>{{ $isStampProgram ? 'stamp' : 'poin' }}</span></p>
                                </div>
                                @if ($programActive)
                                    @if ($isStampProgram)
                                        <div class="progress-copy"><strong>{{ $stampProgressLabel }} / {{ $stampGoal }}</strong><span>stamp ke reward</span></div>
                                        <div class="stamp-row" role="img" aria-label="{{ $stampProgressLabel }} dari {{ $stampGoal }} stamp menuju reward">
                                            @for ($stamp = 1; $stamp <= $stampGoal; $stamp++)
                                                <span class="stamp-token {{ $stamp <= $stampProgressLabel ? 'is-earned' : '' }}" data-stamp-index="{{ $stamp }}">
                                                    <img src="{{ asset('img/growpos-logo-96.webp') }}" alt="" aria-hidden="true" draggable="false">
                                                </span>
                                            @endfor
                                        </div>
                                        <div class="card-progress" role="progressbar" aria-label="Progres menuju reward berikutnya" aria-valuemin="0" aria-valuemax="{{ $stampGoal }}" aria-valuenow="{{ $stampProgressLabel }}"><div class="card-progress-fill" data-progress="{{ $stampProgressPercent }}"></div></div>
                                        <p class="reward-line">
                                            @if ($stampRewardsAvailable > 0) {{ number_format($stampRewardsAvailable, 0, ',', '.') }} reward siap ditukar.
                                            @elseif ($stampBalance < 0) Stamp sedang disesuaikan karena transaksi retur penuh.
                                            @else {{ number_format($stampGoal - $stampProgressLabel, 0, ',', '.') }} stamp lagi untuk potongan Rp {{ number_format($stampRewardValue, 0, ',', '.') }}.
                                            @endif
                                        </p>
                                    @else
                                        <div class="progress-copy"><strong>{{ number_format($pointsIntoStep, 0, ',', '.') }} / {{ number_format($step, 0, ',', '.') }}</strong><span>poin ke reward</span></div>
                                        <div class="card-progress" role="progressbar" aria-label="Progres menuju reward berikutnya" aria-valuemin="0" aria-valuemax="{{ $step }}" aria-valuenow="{{ $pointsIntoStep }}"><div class="card-progress-fill" data-progress="{{ $progressPercent }}"></div></div>
                                        <p class="reward-line">
                                            @if ($pointsToNextReward === 0) Reward berikutnya sudah bisa ditukar.
                                            @else {{ number_format($pointsToNextReward, 0, ',', '.') }} poin lagi untuk potongan Rp {{ number_format($rewardValue, 0, ',', '.') }}.
                                            @endif
                                        </p>
                                    @endif
                                @else
                                    <div class="progress-copy"><strong>—</strong><span>program loyalty</span></div>
                                    <p class="reward-line">Program loyalty sedang tidak aktif.</p>
                                @endif
                            </div>
                        </div>
                    </article>

                    <article class="card-face card-back" aria-label="Sisi belakang kartu member">
                        <div class="back-copy">
                            <span class="eyebrow">Akses member</span>
                            <h2>Kartu digital<br>Anda, selalu siap.</h2>
                            <p>Tunjukkan kode QR ini kepada kasir agar member Anda mudah ditemukan saat transaksi.</p>
                            <div class="back-merchant">
                                <span class="back-merchant-mark" aria-hidden="true">@if ($logo)<img src="{{ $logo }}" alt="">@else{{ mb_substr($tenant->name, 0, 1) }}@endif</span>
                                <span>{{ $tenant->name }}<br><span class="back-member-code">{{ $memberCode }}</span></span>
                            </div>
                        </div>
                        <button class="qr-trigger" type="button" data-qr-open aria-label="Perbesar kode QR member {{ $memberCode }}">
                            <canvas class="member-qr" data-qr-value="{{ $memberCode }}" aria-label="Kode QR untuk nomor member {{ $memberCode }}"></canvas>
                            <span>Ketuk untuk memperbesar</span>
                        </button>
                        <button class="flip-back" type="button" data-card-flip aria-label="Balik ke sisi depan kartu">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9a7 7 0 0 1 11.8-2L20 9M4 15l2.6 2a7 7 0 0 0 11.8-2"/></svg>Balik kartu
                        </button>
                    </article>
                </div>
                <button class="flip-hint" type="button" data-card-flip aria-pressed="false" aria-controls="loyalty-card">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9a7 7 0 0 1 11.8-2L20 9M4 15l2.6 2a7 7 0 0 0 11.8-2"/></svg>
                    <span data-flip-label>Lihat sisi belakang</span>
                </button>
            </div>

            <aside class="member-panel" aria-labelledby="activity-heading">
                <div class="panel-heading">
                    <div><span class="eyebrow">Keanggotaan Anda</span><h2 id="activity-heading">Ringkasan member</h2></div>
                    <span class="member-mini-code">{{ $memberCode }}</span>
                </div>

                <div class="overview">
                    <div>
                        <div class="overview-label">Reward berikutnya</div>
                        <div class="overview-reward">
                            @if ($programActive)
                                Potongan Rp {{ number_format($isStampProgram ? $stampRewardValue : $rewardValue, 0, ',', '.') }}
                                <span>{{ $isStampProgram ? ($stampRewardsAvailable > 0 ? number_format($stampRewardsAvailable, 0, ',', '.') . ' reward siap ditukar' : number_format($stampGoal - $stampProgressLabel, 0, ',', '.') . ' stamp lagi untuk reward') : ($availableRewards > 0 ? number_format($availableRewards, 0, ',', '.') . ' reward siap ditukar' : number_format($pointsToNextReward, 0, ',', '.') . ' poin lagi untuk reward') }}</span>
                            @else
                                Program loyalty tidak aktif
                                <span>Riwayat loyalty tetap tersimpan.</span>
                            @endif
                        </div>
                    </div>
                    <div class="reward-count">{{ number_format($isStampProgram ? $visibleStampBalance : $visiblePointBalance, 0, ',', '.') }}<small>{{ $isStampProgram ? 'total stamp' : 'total poin' }}</small></div>
                </div>

                <div class="section-heading"><h3>{{ $isStampProgram ? 'Aktivitas stamp' : 'Aktivitas poin' }}</h3><span>{{ $activity->count() }} terbaru</span></div>
                <ul class="activity-list" tabindex="0" aria-label="Daftar aktivitas loyalty, dapat digulir">
                    @forelse ($activity as $entry)
                        @php($delta = (int) ($isStampProgram ? $entry->stamps_delta : $entry->points_delta))
                        <li class="activity-item">
                            <span class="activity-marker" aria-hidden="true">{{ $delta > 0 ? '＋' : ($delta < 0 ? '−' : '•') }}</span>
                            <span class="activity-copy">
                                <strong>{{ $isStampProgram ? match ($entry->type) { 'earned' => 'Stamp dari pembelian', 'redeemed' => 'Stamp ditukar', 'redemption_reversed' => 'Stamp penukaran dikembalikan', 'redemption_restored' => 'Stamp dikembalikan karena retur', 'return_reversal' => 'Stamp disesuaikan karena retur', default => 'Penyesuaian stamp' } : match ($entry->type) { 'earned' => 'Poin dari pembelian', 'redeemed' => 'Poin digunakan', 'redemption_reversed' => 'Poin penukaran dikembalikan', 'redemption_restored' => 'Poin dikembalikan karena retur', 'return_reversal' => 'Poin disesuaikan karena retur', default => 'Penyesuaian poin' } }}</strong>
                                <time datetime="{{ $entry->created_at->toIso8601String() }}">{{ $entry->created_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}</time>
                            </span>
                            <strong class="activity-points {{ $delta > 0 ? 'is-earned' : '' }}">{{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 0, ',', '.') }} {{ $isStampProgram ? 'stamp' : 'poin' }}</strong>
                        </li>
                    @empty
                        <li class="activity-empty">Belum ada aktivitas {{ $isStampProgram ? 'stamp' : 'poin' }}.<span>Transaksi berikutnya akan muncul di sini.</span></li>
                    @endforelse
                </ul>
                <div class="panel-foot">
                    <span>Tunjukkan kartu ke kasir</span>
                    <button class="utility-button" type="button" data-copy-member="{{ $memberCode }}" aria-label="Salin nomor member {{ $memberCode }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/></svg>
                        <span data-copy-label>Salin ID</span>
                    </button>
                </div>
            </aside>
        </section>

        <footer class="loyalty-footer"><span class="footer-rule" aria-hidden="true"></span><span>Digital membership experience</span><strong>GrowPOS</strong><span class="footer-rule" aria-hidden="true"></span></footer>
    </main>

    <dialog class="qr-dialog" data-qr-dialog aria-labelledby="qr-dialog-title">
        <div class="dialog-card">
            <span class="eyebrow">{{ $tenant->name }}</span>
            <h2 id="qr-dialog-title">Kode QR member</h2>
            <canvas class="dialog-qr" data-qr-large data-qr-value="{{ $memberCode }}" aria-label="Kode QR untuk nomor member {{ $memberCode }}"></canvas>
            <p>{{ $memberCode }} · Tunjukkan kepada kasir saat transaksi</p>
            <button class="dialog-close" type="button" data-qr-close>Tutup</button>
        </div>
    </dialog>
</body>

</html>
