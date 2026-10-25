<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

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
                            image="hero-screen.png" /></div>
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
        <section id="pengalaman" class="py-20 bg-white sm:py-28">
            <div class="grid items-center gap-12 px-5 mx-auto max-w-7xl sm:px-8 lg:grid-cols-12 lg:gap-20">
                <div class="lg:col-span-5">
                    <p class="mb-5 text-xs font-bold uppercase tracking-[.22em] text-primary-600">DIBUAT UNTUK HARI-HARI
                        SIBUK</p>
                    <h2 class="text-4xl font-extrabold leading-[1.12] tracking-[-.055em] text-[#172E24] sm:text-5xl">
                        Fokus ke pelanggan.<br>Bukan ke sistem.</h2>
                    <p class="mt-6 max-w-md text-base leading-8 text-[#65766B]">Dari membuka toko sampai menutup shift,
                        GrowPOS membantu pekerjaan sehari-hari terasa lebih ringan.</p>
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center gap-2 pb-2 mt-8 text-sm font-bold transition border-b-2 border-primary-600 text-primary-700 hover:gap-4">Mulai
                        gunakan GrowPOS <span aria-hidden="true">↗</span></a>
                </div>
                <div class="relative lg:col-span-7">
                    <div class="aspect-[5/4] overflow-hidden rounded-[1.5rem] bg-[#DCE9DF]">
                        <img src="{{ asset('img/growpos-retail.png') }}" alt="Aktivitas toko retail" loading="lazy"
                            class="object-cover w-full h-full" onerror="this.style.display='none'">
                        {{-- <div class="flex h-full items-center justify-center px-8 text-center text-[#517662]"
                            aria-hidden="true"><span class="text-lg font-semibold">Foto aktivitas toko GrowPOS</span>
                        </div> --}}
                    </div>
                    <div
                        class="absolute -bottom-5 left-5 rounded-xl border border-[#E0E9E2] bg-white px-6 py-4 shadow-xl sm:left-[-24px]">
                        <p class="text-xs font-semibold tracking-widest uppercase text-primary-600">GrowPOS</p>
                        <p class="mt-1 text-lg font-bold text-[#17372A]">Kerja lebih simpel.</p>
                    </div>
                </div>
            </div>
        </section>
        <section id="harga" class="bg-[#F6F8F5] py-20 sm:py-28">
            <div class="px-5 mx-auto max-w-7xl sm:px-8">
                <div class="max-w-2xl mx-auto text-center mb-14">
                    <p class="mb-4 text-xs font-bold uppercase tracking-[.22em] text-primary-600">HARGA TRANSPARAN</p>
                    <h2 class="text-4xl font-extrabold tracking-[-.055em] text-[#172E24] sm:text-5xl">Pilih yang pas
                        untuk usahamu.</h2>
                    <p class="mt-5 text-sm leading-7 text-[#65766B]">Bandingkan fitur dan kapasitas paket sebelum
                        memulai.</p>
                </div>
                @if (isset($plans) && $plans->count() > 0)
                    <div class="mx-auto grid max-w-6xl items-stretch gap-5 md:grid-cols-{{ min($plans->count(), 3) }}">
                        @foreach ($plans as $plan)
                            <x-landing.pricing-card :plan-id="$plan->id" :popular="$plan->slug === 'growth'" :title="$plan->name"
                                :desc="$plan->description" :price="$plan->price == 0
                                    ? 'Rp 0'
                                    : 'Rp ' . number_format($plan->price, 0, ',', '.')" :period="$plan->duration_days . ' hari'" :cta="auth()->check()
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
