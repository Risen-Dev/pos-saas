<x-guest-layout>
    @section('title', 'Masuk ke GrowPOS')
    <x-auth.shell :showcase="true" heading="Lebih fokus pada bisnis. Lebih sedikit pekerjaan manual."
        description="Mulai dari transaksi hingga laporan, semua terhubung dalam satu workspace.">
        <div x-data="{ showPassword: false, submitting: false, capsLock: false }" @keydown.window="capsLock = $event.getModifierState('CapsLock')">
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-primary-600">SELAMAT DATANG KEMBALI</p>
            <h1 class="text-4xl font-semibold tracking-[-0.055em] text-ink-900">Masuk ke akun Anda.</h1>
            <p class="mt-3 mb-8 text-sm leading-6 text-ink-700">Lanjutkan pekerjaan Anda di GrowPOS.</p>
            <x-auth-session-status class="mb-5" :status="session('status')" />
            @if (request('expired'))
                <p role="alert" class="p-3 mb-5 text-sm border rounded-xl border-amber-200 bg-amber-50 text-amber-800">
                    Sesi Anda telah berakhir. Silakan masuk kembali.</p>
            @endif
            @if (Route::has('auth.google'))
                <a href="{{ route('auth.google') }}"
                    class="flex items-center justify-center w-full gap-3 px-4 py-3 text-sm font-semibold transition border rounded-xl border-border-200 text-ink-900 hover:bg-surface-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600">
                    <svg class="w-5 h-5" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335"
                            d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                        <path fill="#4285F4"
                            d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.27 5.48-4.8 7.18l7.73 6C44.38 38.03 46.98 31.87 46.98 24.55z" />
                        <path fill="#FBBC05"
                            d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.2A23.85 23.85 0 0 0 0 24c0 3.87.93 7.53 2.56 10.78l7.97-6.19z" />
                        <path fill="#34A853"
                            d="M24 48c6.48 0 11.93-2.13 15.91-5.8l-7.73-6c-2.15 1.45-4.92 2.3-8.18 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.97 6.19C6.51 42.62 14.62 48 24 48z" />
                    </svg>
                    Lanjutkan dengan Google
                </a>
                <div class="flex items-center gap-4 text-xs my-7 text-ink-400"><span
                        class="flex-1 h-px bg-border-200"></span>atau dengan email<span
                        class="flex-1 h-px bg-border-200"></span></div>
            @endif
            <form method="POST" action="{{ route('login') }}" class="space-y-5" @submit="submitting = true">
                @csrf
                <x-auth.field name="email" label="Alamat email" type="email" :value="old('email')"
                    autocomplete="username" placeholder="nama@bisnis.com" :autofocus="true" />
                <div>
                    <div class="flex items-center justify-between mb-2"><label for="password"
                            class="text-sm font-medium text-ink-900">Kata sandi</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-xs font-semibold text-primary-600 hover:underline">Lupa kata sandi?</a>
                        @endif
                    </div>
                    <div class="relative"><input id="password" name="password"
                            :type="showPassword ? 'text' : 'password'" required autocomplete="current-password"
                            placeholder="Masukkan kata sandi"
                            class="block w-full px-4 py-3 pr-16 text-sm transition border outline-none rounded-xl border-border-200 bg-surface-0 text-ink-900 focus:border-primary-600 focus:ring-4 focus:ring-primary-600/10"><button
                            type="button" @click="showPassword = !showPassword"
                            :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            class="absolute inset-y-0 px-2 text-xs font-semibold right-3 text-ink-700 hover:text-primary-600"
                            x-text="showPassword ? 'Tutup' : 'Lihat'"></button></div>
                    <p x-cloak x-show="capsLock" class="mt-2 text-xs text-amber-700">Caps Lock sedang aktif.</p>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <label class="inline-flex cursor-pointer items-center gap-2.5 text-sm text-ink-700"><input
                        type="checkbox" name="remember"
                        class="rounded border-border-200 text-primary-600 focus:ring-primary-600">Ingat saya di
                    perangkat ini</label>
                <button type="submit" :disabled="submitting"
                    class="flex w-full items-center justify-center rounded-xl bg-primary-600 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-primary-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:opacity-60"><span
                        x-text="submitting ? 'Sedang masuk...' : 'Masuk ke GrowPOS'"></span><span class="ml-2"
                        aria-hidden="true">→</span></button>
            </form>
            <p class="mt-8 text-sm text-center text-ink-700">Belum punya akun? <a href="{{ route('register') }}"
                    class="font-semibold text-primary-600 hover:underline">Daftar gratis</a></p>
        </div>
    </x-auth.shell>
</x-guest-layout>
