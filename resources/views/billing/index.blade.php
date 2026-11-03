<x-app-layout>
    @section('title', 'Langganan & Penagihan')

    <div class="w-full px-4 py-8 mx-auto space-y-8 sm:px-6 lg:px-8 max-w-7xl" x-data="{
        showConfirmModal: false,
        selectedPlan: null,
        billingPeriod: 'monthly',
        openCheckout(plan) {
            this.selectedPlan = plan;
            this.showConfirmModal = true;
        }
    }">

        {{-- Header Section --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold font-heading text-ink-900">Langganan & Penagihan</h1>
                <p class="text-sm font-body text-ink-600">Kelola paket berlangganan toko Anda untuk menikmati seluruh
                    fitur operasional GrowPOS.</p>
            </div>
        </div>


        {{-- Banner Tagihan Unpaid / Pending (Jika Ada Tagihan yang Belum Dibayar) --}}
        @if (isset($pendingInvoice) && $pendingInvoice)
            <div
                class="flex flex-col justify-between gap-4 p-5 border shadow-sm sm:flex-row sm:items-center rounded-2xl bg-amber-50 border-amber-300 text-amber-900">
                <div class="flex items-center gap-3">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-200 text-amber-800 shrink-0">
                        <x-icon class="text-lg fa-solid fa-clock-rotate-left" />
                    </div>
                    <div>
                        <h4 class="text-sm font-bold">Menunggu Pembayaran Tagihan #{{ $pendingInvoice->invoice_number }}
                        </h4>
                        <p class="text-xs text-amber-800 mt-0.5">
                            Tagihan transaksi sebelumnya sebesar <strong>Rp
                                {{ number_format($pendingInvoice->amount, 0, ',', '.') }}</strong> belum diselesaikan.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <form action="{{ route('billing.invoice.cancel', $pendingInvoice->id) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Batalkan tagihan pending ini?')"
                            class="px-3 py-2 text-xs font-semibold transition rounded-lg text-amber-900 hover:bg-amber-200">
                            Batalkan Tagihan
                        </button>
                    </form>
                    <a href="{{ route('billing.invoice', $pendingInvoice->id) }}"
                        class="px-4 py-2 text-xs font-bold text-white transition rounded-lg shadow-sm bg-amber-600 hover:bg-amber-700">
                        Lanjutkan Pembayaran <x-icon class="ml-1 fa-solid fa-arrow-right" />
                    </a>
                </div>
            </div>
        @endif

        {{-- Status Langganan Saat Ini --}}
        <div class="p-6 border shadow-sm bg-surface-0 border-border-200 rounded-2xl">
            <h2 class="flex items-center gap-2 mb-4 text-lg font-bold text-ink-900">
                <x-icon class="fa-solid fa-shield-halved text-primary-600" />
                Status Berlangganan Toko Saat Ini
            </h2>

            @if ($currentSubscription && $currentSubscription->status === 'active' && (!$currentSubscription->end_date || $currentSubscription->end_date >= now()))
                <div
                    class="grid grid-cols-1 gap-4 p-5 border md:grid-cols-3 rounded-xl bg-primary-50/50 border-primary-200">
                    <div>
                        <span class="text-xs font-semibold tracking-wider uppercase text-primary-700">Paket Aktif</span>
                        <p class="mt-1 text-2xl font-black text-primary-900">
                            {{ $currentSubscription->plan->name ?? 'Custom Plan' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold tracking-wider uppercase text-primary-700">Status
                            Layanan</span>
                        <div class="mt-1">
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-bold border rounded-full bg-emerald-100 text-emerald-800 border-emerald-200">
                                <span class="w-2 h-2 mr-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                Berjalan
                            </span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-semibold tracking-wider uppercase text-primary-700">Berlaku
                            Sampai</span>
                        <p class="mt-1 text-base font-bold text-primary-900">
                            @if ($currentSubscription->end_date)
                                {{ \Carbon\Carbon::parse($currentSubscription->end_date)->translatedFormat('d F Y') }}
                                <span class="block text-xs font-normal text-primary-700 mt-0.5">Sisa {{ (int) \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($currentSubscription->end_date)) }} hari</span>
                            @else
                                Aktif selamanya
                                <span class="block text-xs font-normal text-primary-700 mt-0.5">Paket gratis berkelanjutan</span>
                            @endif
                        </p>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 p-5 border md:grid-cols-3 rounded-xl bg-rose-50 border-rose-200">
                    <div>
                        <span class="text-xs font-semibold tracking-wider uppercase text-rose-700">Paket Aktif</span>
                        <p class="mt-1 text-xl font-bold text-rose-900">Belum Berlangganan / Kadaluarsa</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold tracking-wider uppercase text-rose-700">Status</span>
                        <div class="mt-1">
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full bg-rose-100 text-rose-800">
                                <x-icon class="fa-solid fa-circle-xmark mr-1.5 text-[10px]" /> Non-Aktif
                            </span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-semibold tracking-wider uppercase text-rose-700">Aksi
                            Diperlukan</span>
                        <p class="mt-1 text-xs font-medium text-rose-800">Pilih salah satu paket di bawah untuk
                            mengaktifkan seluruh fitur kasir.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Grid Pilihan Paket Langganan --}}
        <div>
            <div class="max-w-xl mx-auto mb-8 text-center">
                <h2 class="text-2xl font-bold text-ink-900">Pilih Paket Pertumbuhan Bisnis</h2>
                <p class="mt-1 text-sm text-ink-600">Klik paket di bawah untuk melihat rincian limit & mengaktifkan
                    layanan.</p>
            </div>

            @if ($plans->contains(fn ($plan) => (float) $plan->price > 0))
                <div class="flex flex-col items-center gap-2 mb-6">
                    <div class="inline-flex gap-1 p-1 border rounded-xl border-border-200 bg-surface-0" role="group" aria-label="Periode pembayaran">
                        <button type="button" @click="billingPeriod = 'monthly'" :aria-pressed="billingPeriod === 'monthly'" :class="billingPeriod === 'monthly' ? 'bg-primary-600 text-white' : 'text-ink-700 hover:bg-surface-100'" class="px-4 py-2 text-xs font-bold rounded-lg">Bulanan</button>
                        <button type="button" @click="billingPeriod = 'yearly'" :aria-pressed="billingPeriod === 'yearly'" :class="billingPeriod === 'yearly' ? 'bg-primary-600 text-white' : 'text-ink-700 hover:bg-surface-100'" class="px-4 py-2 text-xs font-bold rounded-lg">Tahunan <span class="ml-1 text-[10px]">Hemat 2 bulan</span></button>
                    </div>
                    <p class="text-xs text-ink-500">Bayar di muka sesuai periode. Tidak ada perpanjangan otomatis.</p>
                </div>
            @endif

            <div class="grid items-stretch grid-cols-1 gap-6 md:grid-cols-3">
                @forelse ($plans as $plan)
                    @php
                        $isCurrent =
                            $currentSubscription &&
                            $currentSubscription->plan_id === $plan->id &&
                            $currentSubscription->status === 'active' &&
                            (!$currentSubscription->end_date || $currentSubscription->end_date >= now());

                        $currentPrice = $currentSubscription?->plan?->price ?? 0;
                        $isUpgrade = $currentSubscription && $plan->price > $currentPrice;
                        $isDowngrade = $currentSubscription && $plan->price < $currentPrice && !$isCurrent;
                        $isPopular = $plan->slug === 'growth' || $plan->slug === 'pro-monthly';
                    @endphp

                    <div @class([
                        'bg-surface-0 rounded-2xl p-6 transition-all duration-300 flex flex-col justify-between relative',
                        'border-2 border-emerald-500 shadow-md ring-2 ring-emerald-500/20' => $isCurrent,
                        'border-2 border-primary-600 shadow-lg scale-100 md:scale-105 z-10' =>
                            $isPopular && !$isCurrent,
                        'border border-border-200 shadow-sm hover:border-primary-300' =>
                            !$isPopular && !$isCurrent,
                    ])>

                        @if ($isCurrent)
                            <div
                                class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-emerald-600 text-white text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-widest shadow-sm">
                                <x-icon class="mr-1 fa-solid fa-check" /> Paket Anda Saat Ini
                            </div>
                        @elseif ($isPopular)
                            <div
                                class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-primary-600 text-white text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-widest shadow-sm">
                                Paling Populer
                            </div>
                        @endif

                        <div class="pt-2 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xl font-bold text-ink-900">{{ $plan->name }}</h3>
                            </div>

                            <p class="text-xs leading-relaxed text-ink-600 min-h-[36px]">{{ $plan->description }}</p>

                            <div class="flex flex-wrap items-baseline gap-1 font-bold text-primary-600">
                                <span class="text-3xl font-extrabold" x-text="{{ (float) $plan->price }} === 0 ? 'Rp 0' : 'Rp ' + Number({{ (float) $plan->price }} * (billingPeriod === 'yearly' ? 10 : 1)).toLocaleString('id-ID')"></span>
                                <span class="text-xs font-normal text-ink-400" x-text="{{ (float) $plan->price }} === 0 ? 'selamanya' : (billingPeriod === 'yearly' ? '/ tahun' : '/ bulan')"></span>
                            </div>
                            @if ((float) $plan->price > 0)
                                <p class="text-[11px] text-ink-500" x-show="billingPeriod === 'yearly'">Setara 10 bulan, akses penuh 12 bulan.</p>
                            @endif

                            <hr class="border-border-100">

                            {{-- Fitur Ringkas --}}
                            <ul class="space-y-2.5 text-xs text-ink-700">
                                <li class="flex items-center gap-2">
                                    <x-icon class="fa-solid fa-check text-emerald-600" />
                                    <span>Maksimal <strong>{{ $plan->max_users }}</strong> pengguna (termasuk pemilik)</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <x-icon class="fa-solid fa-check text-emerald-600" />
                                    <span>Maksimal <strong>{{ $plan->max_products }}</strong> produk / layanan</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <x-icon class="fa-solid fa-check text-emerald-600" />
                                    <span>Maksimal <strong>{{ $plan->max_outlets }}</strong> bisnis / outlet</span>
                                </li>
                                @foreach($plan->advertisedFeatures() as $feature => $included)
                                    @if($included)<li class="flex items-center gap-2"><x-icon class="fa-solid fa-check text-emerald-600" /><span>{{ $feature }}</span></li>@endif
                                @endforeach
                            </ul>
                        </div>

                        {{-- Tombol Buka Modal Detail / Konfirmasi (Bukan LANGSUNG SUBMIT) --}}
                        <div class="mt-8">
                            <button type="button" @click="openCheckout({{ json_encode($plan) }})"
                                @class([
                                    'w-full py-3 rounded-xl font-bold text-xs transition shadow-sm flex items-center justify-center gap-2',
                                    'bg-emerald-600 hover:bg-emerald-700 text-white' => $isCurrent,
                                    'bg-primary-600 hover:bg-primary-700 text-white' =>
                                        !$isCurrent && !$isDowngrade,
                                    'border border-border-300 bg-surface-100 text-ink-700 hover:bg-surface-200' => $isDowngrade,
                                ])>
                                <x-icon class="fa-solid fa-eye" />
                                <span>{{ $isCurrent ? 'Lihat Detail / Perpanjang' : ($isUpgrade ? 'Detail & Upgrade' : 'Lihat Detail Paket') }}</span>
                            </button>
                        </div>

                    </div>
                @empty
                    <div class="col-span-3 py-12 text-center border bg-surface-0 rounded-2xl border-border-200">
                        <x-icon class="mb-3 text-4xl fa-solid fa-box-open text-ink-300" />
                        <p class="text-sm font-semibold text-ink-700">Belum ada data paket langganan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- MODAL DETAIL & KONFIRMASI PEMBELIAN (Pencegah Bug Pending Invoice) --}}
        <div data-dialog x-show="showConfirmModal" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="[&[x-cloak]]:!hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-900/40 backdrop-blur-sm">

            <div @click.away="showConfirmModal = false"
                class="w-full max-w-lg p-6 border shadow-xl rounded-2xl bg-surface-0 border-border-200">
                <div class="flex items-center justify-between pb-4 border-b border-border-200">
                    <h3 class="text-lg font-bold font-heading text-ink-900">Rincian Paket Langganan</h3>
                    <button @click="showConfirmModal = false" class="p-1 text-ink-400 hover:text-ink-900">
                        <x-icon class="text-lg fa-solid fa-xmark" />
                    </button>
                </div>

                <template x-if="selectedPlan">
                    <div class="py-4 space-y-4">
                        <div class="p-4 border rounded-xl bg-surface-100 border-border-200">
                            <span class="text-xs font-semibold uppercase text-ink-500">Nama Paket</span>
                            <h4 class="text-xl font-black text-primary-600" x-text="selectedPlan.name"></h4>
                            <p class="mt-1 text-xs text-ink-600" x-text="selectedPlan.description"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 border rounded-lg bg-surface-0 border-border-200">
                                <span class="text-ink-500 block mb-0.5">Maksimal Staf/Kasir</span>
                                <strong class="text-sm text-ink-900"
                                    x-text="selectedPlan.max_users + ' Kasir'"></strong>
                            </div>
                            <div class="p-3 border rounded-lg bg-surface-0 border-border-200">
                                <span class="text-ink-500 block mb-0.5">Maksimal Produk</span>
                                <strong class="text-sm text-ink-900"
                                    x-text="selectedPlan.max_products + ' SKU'"></strong>
                            </div>
                            <div class="p-3 border rounded-lg bg-surface-0 border-border-200">
                                <span class="text-ink-500 block mb-0.5">Maksimal Toko/Cabang</span>
                                <strong class="text-sm text-ink-900"
                                    x-text="selectedPlan.max_outlets + ' Outlet'"></strong>
                            </div>
                            <div class="p-3 border rounded-lg bg-surface-0 border-border-200">
                                <span class="text-ink-500 block mb-0.5">Masa Aktif Paket</span>
                                <strong class="text-sm text-ink-900"
                                    x-text="Number(selectedPlan.price) === 0 ? 'Selamanya' : (billingPeriod === 'yearly' ? '12 Bulan' : '1 Bulan')"></strong>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between p-4 border rounded-xl bg-primary-50 border-primary-200">
                            <span class="text-xs font-bold text-primary-900">Total Pembayaran:</span>
                            <span class="text-xl font-black text-primary-700"
                                x-text="Number(selectedPlan.price) == 0 ? 'Rp 0 (Gratis)' : 'Rp ' + Number(Number(selectedPlan.price) * (billingPeriod === 'yearly' ? 10 : 1)).toLocaleString('id-ID')"></span>
                        </div>

                        {{-- Form BARU Di Dalam Modal --}}
                        <form action="{{ route('billing.subscribe') }}" method="POST" class="pt-2">
                            @csrf
                            <input type="hidden" name="plan_id" :value="selectedPlan.id">
                            <input type="hidden" name="billing_period" :value="Number(selectedPlan.price) === 0 ? 'monthly' : billingPeriod">

                            <div class="flex items-center gap-3">
                                <button type="button" @click="showConfirmModal = false"
                                    class="flex-1 py-3 text-xs font-semibold text-ink-700 bg-surface-100 hover:bg-surface-200 rounded-xl">
                                    Kembali (Batal)
                                </button>
                                <button type="submit"
                                    class="flex-1 py-3 text-xs font-bold text-white shadow-sm bg-primary-600 hover:bg-primary-700 rounded-xl">
                                    Lanjut Ke Pembayaran <x-icon class="ml-1 fa-solid fa-arrow-right" />
                                </button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>
