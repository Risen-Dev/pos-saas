<nav x-data="{ open: false, scrolled: false }"
     x-init="scrolled = window.scrollY > 24; window.addEventListener('scroll', () => { scrolled = window.scrollY > 24 }, { passive: true })"
     class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
     :class="scrolled || open ? 'border-b border-[#E6ECE7] bg-white/95 shadow-sm backdrop-blur-xl' : 'border-b border-transparent bg-transparent'"
     aria-label="Navigasi utama">
    <div class="mx-auto flex h-[76px] max-w-7xl items-center justify-between gap-6 px-5 sm:px-8">
        <a href="/" class="inline-flex shrink-0 items-center gap-2 text-xl font-extrabold tracking-[-.05em] text-[#172E24]">
            <img src="{{ asset('img/growpos_logo.png') }}" class="h-9 w-9 object-contain" alt="">
            <span>GrowPOS<span class="text-primary-600">.</span></span>
        </a>
        <div class="hidden items-center gap-9 md:flex">
            <x-landing.nav-link href="#fitur">Fitur</x-landing.nav-link>
            <x-landing.nav-link href="#pengalaman">Untuk Usaha</x-landing.nav-link>
            <x-landing.nav-link href="#harga">Harga</x-landing.nav-link>
        </div>
        <div class="hidden items-center gap-5 md:flex">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-[#243E30] hover:text-primary-600">Masuk</a>
            <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-primary-600 px-5 text-sm font-bold text-white transition hover:bg-primary-900">Mulai Gratis <span class="ml-2" aria-hidden="true">↗</span></a>
        </div>
        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-[#DCE7DF] bg-white/80 text-[#17372A] md:hidden" @click="open = !open" :aria-expanded="open.toString()" aria-controls="landing-mobile-menu" :aria-label="open ? 'Tutup menu' : 'Buka menu'">
            <span x-text="open ? '✕' : '☰'" aria-hidden="true">☰</span>
        </button>
    </div>
    <div id="landing-mobile-menu" x-show="open" x-cloak @keydown.escape.window="open=false" class="border-t border-[#E6ECE7] bg-white px-5 pb-6 pt-3 md:hidden">
        <div class="flex flex-col gap-1">
            <a href="#fitur" @click="open=false" class="py-3 text-sm font-semibold">Fitur</a>
            <a href="#pengalaman" @click="open=false" class="py-3 text-sm font-semibold">Untuk Usaha</a>
            <a href="#harga" @click="open=false" class="py-3 text-sm font-semibold">Harga</a>
            <a href="{{ route('login') }}" class="py-3 text-sm font-semibold">Masuk</a>
            <a href="{{ route('register') }}" class="mt-3 rounded-xl bg-primary-600 px-5 py-3 text-center text-sm font-bold text-white">Mulai Gratis ↗</a>
        </div>
    </div>
</nav>
