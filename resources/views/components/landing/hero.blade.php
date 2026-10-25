<section id="beranda" class="relative isolate min-h-[100svh] overflow-hidden bg-[#F1F6F0]">
    {{-- Full-bleed photograph --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('img/growpos-hero.png') }}" alt="Aktivitas kasir di toko menggunakan GrowPOS"
            fetchpriority="high" class="h-full w-full object-cover object-[58%_center] lg:object-center">
    </div>

    {{-- Soft editorial blend: foreground is always readable --}}
    <div aria-hidden="true"
        class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-b from-[#F1F6F0]/95 via-[#F1F6F0]/80 to-[#F1F6F0]/30 lg:bg-gradient-to-r lg:from-[#F1F6F0] lg:via-[#F1F6F0]/35 lg:to-transparent">
    </div>
    <div aria-hidden="true"
        class="pointer-events-none absolute inset-0 z-[1] hidden lg:block [background:linear-gradient(50deg,#F1F6F0_0%,rgba(241,246,240,.96)_25%,rgba(241,246,240,.66)_43%,rgba(241,246,240,0)_70%)]">
    </div>
    <div aria-hidden="true"
        class="pointer-events-none absolute inset-x-0 bottom-0 z-[1] h-24 bg-gradient-to-t from-[#F1F6F0]/35 to-transparent">
    </div>

    <div
        class="relative z-10 mx-auto flex min-h-[100svh] w-full max-w-7xl flex-col justify-center px-5 pb-28 pt-32 sm:px-8 lg:pb-24 lg:pt-28">
        <div class="max-w-[660px]">
            <div
                class="mb-7 inline-flex items-center gap-2 rounded-full border border-[#D7E7D9] bg-white/80 px-4 py-2 text-[10px] font-extrabold uppercase tracking-[.15em] text-[#17694D] backdrop-blur-sm sm:text-xs">
                <span class="w-2 h-2 rounded-full bg-primary-600"></span>
                Kasir &amp; operasional usaha
            </div>
            <h1
                class="text-[clamp(3.1rem,6.2vw,6.25rem)] font-extrabold leading-[1.02] tracking-[-.075em] text-[#153B2B]">
                Bisnis jalan.<br><span class="text-primary-600">Pikiran tenang.</span>
            </h1>
            <p class="mt-7 max-w-[440px] text-base leading-7 text-[#60776B] sm:text-lg sm:leading-8">
                Penjualan, stok, dan laporan usaha. Semua dalam satu GrowPOS.
            </p>
            <div class="flex flex-wrap gap-3 mt-8">
                <a href="{{ route('register') }}"
                    class="inline-flex items-center justify-center text-sm font-bold text-white transition min-h-12 rounded-xl bg-primary-600 px-7 hover:bg-primary-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">Mulai
                    Sekarang <span class="ml-3" aria-hidden="true">↗</span></a>
                <a href="#fitur"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl border border-[#C8DACD] bg-white/85 px-6 text-sm font-bold text-[#1E4832] backdrop-blur-sm transition hover:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">Jelajahi
                    Fitur</a>
            </div>
            <div class="mt-9 flex flex-wrap gap-x-6 gap-y-2 text-xs font-semibold text-[#476C59] sm:text-sm">
                <span>✓ Transaksi lebih rapi</span><span>✓ Stok terpantau</span><span>✓ Laporan mudah dibaca</span>
            </div>
        </div>
    </div>

    <div
        class="absolute bottom-6 right-5 z-20 hidden max-w-[290px] rounded-2xl border border-white/70 bg-white/90 p-5 shadow-xl backdrop-blur-lg lg:block xl:right-[max(32px,calc((100vw-1280px)/2))]">
        <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-primary-700">Satu workspace</p>
        <p class="mt-2 text-lg font-extrabold text-[#17372A]">Kasir. Stok. Laporan.</p>
        <p class="mt-1 text-xs leading-5 text-[#65766B]">Operasional harian dalam satu tempat.</p>
    </div>
</section>
