<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="GrowPOS membantu usaha retail mencatat penjualan, mengelola stok, dan memantau bisnis dalam satu tempat.">
    <title>GrowPOS — Kasir simpel untuk usaha yang terus tumbuh</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-white text-ink-900 selection:bg-primary-100">
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:z-[100] focus:bg-white focus:p-3">Lewati navigasi</a>
    <x-landing.navbar />
    <main id="main-content">
        <x-landing.hero />
        @php
            $demoVideoUrl = trim((string) config('services.growpos.demo_video_url', '/video/growpos-demo.mp4'));
            $demoVideoPath = parse_url($demoVideoUrl, PHP_URL_PATH) ?? $demoVideoUrl;
            $demoVideoType = str_ends_with(strtolower($demoVideoPath), '.webm') ? 'video/webm' : 'video/mp4';
        @endphp
        <section id="pengalaman" class="scroll-mt-20 overflow-hidden bg-white py-20 sm:py-28">
            <div class="mx-auto grid max-w-7xl items-center gap-10 px-5 sm:px-8 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-5">
                    <p class="mb-5 text-xs font-bold uppercase tracking-[.22em] text-primary-600">DEMO SINGKAT GROWPOS</p>
                    <h2 class="text-4xl font-extrabold leading-[1.12] tracking-[-.055em] text-[#172E24] sm:text-5xl">
                        Lihat alur usaha jadi lebih sederhana.
                    </h2>
                    <p class="mt-6 max-w-md text-base leading-8 text-[#65766B]">
                        Kenali cara GrowPOS membantu mencatat penjualan, menjaga stok, dan membaca hasil usaha dalam satu alur.
                    </p>
                    <div class="mt-7 flex flex-wrap gap-2 text-xs font-semibold text-[#476C59] sm:text-sm">
                        <span class="rounded-full bg-[#F1F6F0] px-3 py-2">Kasir</span>
                        <span class="rounded-full bg-[#F1F6F0] px-3 py-2">Stok</span>
                        <span class="rounded-full bg-[#F1F6F0] px-3 py-2">Laporan</span>
                    </div>
                    <a href="{{ route('register') }}"
                        class="mt-8 inline-flex min-h-12 items-center gap-2 rounded-xl bg-primary-600 px-6 text-sm font-bold text-white transition hover:bg-primary-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                        Coba GrowPOS <span aria-hidden="true">↗</span>
                    </a>
                </div>
                <div class="lg:col-span-7">
                    <div class="group relative aspect-video overflow-hidden rounded-2xl border border-[#DCE7DF] bg-[#E7F0E9] shadow-[0_24px_70px_-32px_rgba(23,55,42,.42)] sm:rounded-[1.75rem]">
                        @if ($demoVideoUrl !== '')
                            <video class="h-full w-full object-cover" controls playsinline preload="metadata"
                                poster="{{ asset('img/growpos-retail-1920.webp') }}">
                                <source src="{{ $demoVideoUrl }}" type="{{ $demoVideoType }}">
                                Browser Anda tidak mendukung pemutar video.
                            </video>
                        @else
                            <img src="{{ asset('img/growpos-retail-1920.webp') }}" width="1672" height="941"
                                alt="Preview GrowPOS untuk membantu operasional toko" loading="lazy" decoding="async"
                                class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.02]">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#10281E]/75 via-[#10281E]/10 to-transparent"></div>
                            <div class="absolute inset-x-0 bottom-0 flex flex-col gap-4 p-5 text-white sm:flex-row sm:items-end sm:justify-between sm:p-7">
                                <div>
                                    <span class="inline-flex rounded-full border border-white/30 bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-[.18em] backdrop-blur-sm">Preview produk</span>
                                    <p class="mt-3 text-lg font-bold sm:text-2xl">Kasir, stok, dan laporan dalam satu alur.</p>
                                </div>
                                <span aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-white/40 bg-white/20 text-lg backdrop-blur-sm sm:h-14 sm:w-14">▶</span>
                            </div>
                            <p class="absolute right-4 top-4 rounded-full border border-white/50 bg-white/90 px-3 py-1.5 text-[10px] font-bold text-[#17694D] shadow-sm sm:right-5 sm:top-5 sm:text-xs">
                                Video demo segera hadir
                            </p>
                        @endif
                    </div>
                    <p class="mt-3 text-xs leading-5 text-[#78877E]">
                        @if ($demoVideoUrl === '')
                            Video belum dipasang. Tambahkan URL MP4 melalui <code>GROWPOS_DEMO_VIDEO_URL</code> saat aset demo siap.
                        @else
                            Putar video untuk melihat alur kerja GrowPOS.
                        @endif
                    </p>
                </div>
            </div>
        </section>
        <section id="fitur" class="overflow-hidden bg-[#F6F8F5] py-20 sm:py-28">
            <div class="px-5 mx-auto max-w-7xl sm:px-8">
                <div class="flex flex-col justify-between gap-5 mb-12 md:flex-row md:items-end">
                    <div>
                        <p class="mb-4 text-xs font-bold uppercase tracking-[.22em] text-primary-600">SATU SISTEM, LEBIH
                            SEDERHANA</p>
                        <h2
                            class="max-w-2xl text-4xl font-extrabold leading-[1.12] tracking-[-.055em] text-[#172E24] sm:text-5xl">
                            Lebih sedikit urusan.<br><span class="text-primary-600">Lebih banyak kemajuan.</span></h2>
                    </div>
                    <p class="max-w-xs text-sm leading-7 text-[#65766B]">Mulai dari transaksi di kasir hingga laporan
                        yang membantu Anda mengambil keputusan.</p>
                </div>
                <div class="grid gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-7"><x-landing.feature-card variant="large" eyebrow="01 / PENJUALAN"
                            title="Kasir yang mengikuti ritme toko."
                            desc="Cari produk, catat pembayaran, dan lanjutkan ke pelanggan berikutnya."
                            image="hero-screen-960.webp" /></div>
                    <div class="lg:col-span-5"><x-landing.feature-card variant="dark" eyebrow="02 / PERSEDIAAN"
                            title="Stok lebih terkendali."
                            desc="Pantau barang masuk, keluar, dan perubahan persediaan dalam satu alur." /></div>
                    <div class="lg:col-span-5"><x-landing.feature-card variant="light" eyebrow="03 / OPERASIONAL"
                            title="Semua lebih tertata."
                            desc="Kelola produk, supplier, dan aktivitas toko tanpa berpindah aplikasi." /></div>
                    <div class="lg:col-span-7"><x-landing.feature-card variant="large" eyebrow="04 / LAPORAN"
                            title="Angka yang mudah dipahami."
                            desc="Lihat hasil penjualan dan ringkasan operasional dari transaksi yang tercatat."
                            image="warehouse.png" /></div>
                </div>
            </div>
        </section>
        <section id="harga" class="bg-[#F6F8F5] py-20 sm:py-28" x-data="{ billingPeriod: 'monthly' }">
            <div class="px-5 mx-auto max-w-7xl sm:px-8">
                <div class="max-w-2xl mx-auto text-center mb-14">
                    <p class="mb-4 text-xs font-bold uppercase tracking-[.22em] text-primary-600">HARGA TRANSPARAN</p>
                    <h2 class="text-4xl font-extrabold tracking-[-.055em] text-[#172E24] sm:text-5xl">Pilih yang pas
                        untuk usahamu.</h2>
                    <p class="mt-5 text-sm leading-7 text-[#65766B]">Bandingkan fitur dan kapasitas paket sebelum
                        memulai.</p>
                </div>
                @if (isset($plans) && $plans->count() > 0)
                    @if ($plans->contains(fn ($plan) => (float) $plan->price > 0))
                        <div class="flex flex-col items-center gap-2 mb-8">
                            <div class="inline-flex gap-1 p-1 border rounded-xl border-[#DCE7DF] bg-white" role="group" aria-label="Periode pembayaran">
                                <button type="button" @click="billingPeriod = 'monthly'" :aria-pressed="billingPeriod === 'monthly'" :class="billingPeriod === 'monthly' ? 'bg-[#17694D] text-white' : 'text-[#52685D] hover:bg-[#F1F6F0]'" class="px-4 py-2 text-xs font-bold rounded-lg">Bulanan</button>
                                <button type="button" @click="billingPeriod = 'yearly'" :aria-pressed="billingPeriod === 'yearly'" :class="billingPeriod === 'yearly' ? 'bg-[#17694D] text-white' : 'text-[#52685D] hover:bg-[#F1F6F0]'" class="px-4 py-2 text-xs font-bold rounded-lg">Tahunan <span class="ml-1 text-[10px]">Hemat 2 bulan</span></button>
                            </div>
                            <p class="text-xs text-[#65766B]">Bayar di muka sesuai periode. Tidak ada perpanjangan otomatis.</p>
                        </div>
                    @endif
                    <div class="mx-auto grid max-w-6xl items-stretch gap-5 md:grid-cols-{{ min($plans->count(), 3) }}">
                        @foreach ($plans as $plan)
                            <x-landing.pricing-card :plan-id="$plan->id" :popular="$plan->slug === 'growth'" :title="$plan->name"
                                :desc="$plan->description" :monthly-price="$plan->price" :price="$plan->price == 0
                                    ? 'Rp 0'
                                    : 'Rp ' . number_format($plan->price, 0, ',', '.')" :period="$plan->price == 0 ? 'selamanya' : 'bulan'" :cta="auth()->check()
                                    ? ($plan->price == 0
                                        ? 'Pilih Starter'
                                        : 'Langganan Sekarang')
                                    : 'Mulai Sekarang'"
                                :capacity="$plan->max_outlets .
                                    ' outlet · ' .
                                    $plan->max_users .
                                    ' pengguna · ' .
                                    $plan->max_products .
                                    ' produk'" :features="$plan->advertisedFeatures()" />
                        @endforeach
                    </div>
                @else
                    <p
                        class="mx-auto max-w-xl rounded-xl border border-[#DCE7DF] bg-white p-8 text-center text-sm text-[#65766B]">
                        Paket belum tersedia. Informasi harga akan muncul setelah paket diaktifkan.</p>
                @endif
                <p class="mx-auto mt-9 max-w-4xl text-center text-xs leading-6 text-[#65766B]">Pembayaran QRIS
                    bergantung pada paket dan konfigurasi payment gateway. Komisi platform saat ini
                    {{ number_format(config('platform.commission_rate', 0.015) * 100, 2, ',', '.') }}% dari transaksi
                    QRIS. Rincian saldo dan pencairan tersedia di aplikasi.</p>
            </div>
        </section>
        <x-landing.cta-section />
    </main>
    <x-landing.footer />
</body>

</html>
