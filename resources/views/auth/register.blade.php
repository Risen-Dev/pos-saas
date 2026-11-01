<x-guest-layout>
    @section('title', 'Buat Akun GrowPOS')
    <x-auth.shell :showcase="true" eyebrow="START YOUR JOURNEY" heading="Mulai bisnis lebih teratur, dari hari pertama."
        description="Buat akun dan siapkan workspace untuk operasional usaha Anda.">
        <div x-data="{ password: '', confirmation: '', showPassword: false, showConfirmation: false, submitting: false }">
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-primary-600">MULAI DENGAN GROWPOS</p>
            <h1 class="text-4xl font-semibold tracking-[-0.055em]">Buat akun baru.</h1>
            <p class="mt-3 text-sm mb-7 text-ink-700">Beberapa langkah untuk memulai bisnis yang lebih rapi.</p>
            @if (Route::has('auth.google'))
                <a href="{{ route('auth.google') }}"
                    class="flex items-center justify-center w-full gap-3 px-4 py-3 text-sm font-semibold transition border rounded-xl border-border-200 hover:bg-surface-100"><span
                        class="font-bold text-[#4285F4]">
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
                    </span> Daftar dengan Google</a>
                <div class="flex items-center gap-4 my-6 text-xs text-ink-400"><span
                        class="flex-1 h-px bg-border-200"></span>atau dengan email<span
                        class="flex-1 h-px bg-border-200"></span></div>
            @endif
            <form method="POST" action="{{ route('register') }}" class="space-y-4" @submit="submitting=true">
                @csrf
                <x-auth.field name="name" label="Nama lengkap" :value="old('name')" autocomplete="name"
                    placeholder="Nama Anda" :autofocus="true" />
                <x-auth.field name="email" label="Alamat email" type="email" :value="old('email')" autocomplete="email"
                    placeholder="nama@bisnis.com" />
                <div><label for="password" class="block mb-2 text-sm font-medium">Kata sandi</label>
                    <div class="relative"><input id="password" name="password" x-model="password"
                            :type="showPassword ? 'text' : 'password'" required autocomplete="new-password"
                            minlength="8" placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-3 pr-12 text-sm border outline-none rounded-xl border-border-200 bg-surface-0 focus:border-primary-600 focus:ring-4 focus:ring-primary-600/10"><x-password-toggle
                            state="showPassword" control="password" /></div>
                    <p class="mt-2 text-xs" :class="password.length >= 8 ? 'text-primary-600' : 'text-ink-400'">Minimal
                        8
                        karakter <span x-show="password.length>=8">✓</span></p><x-input-error :messages="$errors->get('password')"
                        class="mt-2" />
                </div>
                <div><label for="password_confirmation" class="block mb-2 text-sm font-medium">Konfirmasi kata
                        sandi</label>
                    <div class="relative"><input id="password_confirmation" name="password_confirmation"
                            x-model="confirmation" :type="showConfirmation ? 'text' : 'password'" required
                            autocomplete="new-password" placeholder="Ulangi kata sandi"
                            class="w-full px-4 py-3 pr-12 text-sm border outline-none rounded-xl border-border-200 bg-surface-0 focus:border-primary-600 focus:ring-4 focus:ring-primary-600/10"><x-password-toggle
                            state="showConfirmation" control="password_confirmation" /></div>
                    <p x-cloak x-show="confirmation.length" class="mt-2 text-xs"
                        :class="confirmation === password ? 'text-primary-600' : 'text-red-600'"
                        x-text="confirmation===password ? 'Kata sandi cocok ✓' : 'Kata sandi belum sama'"></p>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
                <button type="submit"
                    :disabled="submitting || (password.length > 0 && (password.length < 8 || confirmation !== password))"
                    class="mt-2 w-full rounded-xl bg-primary-600 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-primary-700 disabled:opacity-60"><span
                        x-text="submitting ? 'Membuat akun...' : 'Buat akun GrowPOS →'"></span></button>
            </form>
            <p class="mt-6 text-sm text-center text-ink-700">Sudah punya akun? <a href="{{ route('login') }}"
                    class="font-semibold text-primary-600 hover:underline">Masuk</a></p>
        </div>
    </x-auth.shell>
</x-guest-layout>
