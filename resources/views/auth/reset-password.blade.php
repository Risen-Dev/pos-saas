<x-guest-layout>
    @section('title', 'Atur Ulang Kata Sandi')
    <x-auth.shell eyebrow="ACCOUNT SECURITY" heading="Keamanan akun, tanpa kompromi." description="Gunakan kata sandi baru yang kuat untuk melindungi akses bisnis Anda.">
        <div x-data="{showPassword:false,showConfirmation:false,password:'',confirmation:'',submitting:false}">
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-primary-600">KEAMANAN AKUN</p>
            <h1 class="text-4xl font-semibold tracking-[-0.055em]">Buat kata sandi baru.</h1>
            <p class="mb-8 mt-3 text-sm leading-7 text-ink-700">Masukkan email terdaftar dan tentukan kata sandi baru untuk akun Anda.</p>
            <form method="POST" action="{{ route('password.store') }}" class="space-y-5" @submit="submitting=true">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <x-auth.field name="email" label="Alamat email" type="email" :value="old('email', $request->email)" autocomplete="username" :autofocus="true" />
                <div><label for="password" class="mb-2 block text-sm font-medium">Kata sandi baru</label><div class="relative"><input id="password" name="password" x-model="password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" required minlength="8" placeholder="Minimal 8 karakter" class="w-full rounded-xl border border-border-200 px-4 py-3 pr-16 text-sm outline-none focus:border-primary-600 focus:ring-4 focus:ring-primary-600/10"><button type="button" @click="showPassword=!showPassword" class="absolute inset-y-0 right-3 px-2 text-xs font-semibold text-ink-700" x-text="showPassword ? 'Tutup' : 'Lihat'"></button></div><p class="mt-2 text-xs" :class="password.length>=8 ? 'text-primary-600' : 'text-ink-400'">Minimal 8 karakter <span x-show="password.length>=8">✓</span></p><x-input-error :messages="$errors->get('password')" class="mt-2" /></div>
                <div><label for="password_confirmation" class="mb-2 block text-sm font-medium">Konfirmasi kata sandi</label><div class="relative"><input id="password_confirmation" name="password_confirmation" x-model="confirmation" :type="showConfirmation ? 'text' : 'password'" autocomplete="new-password" required placeholder="Ulangi kata sandi" class="w-full rounded-xl border border-border-200 px-4 py-3 pr-16 text-sm outline-none focus:border-primary-600 focus:ring-4 focus:ring-primary-600/10"><button type="button" @click="showConfirmation=!showConfirmation" class="absolute inset-y-0 right-3 px-2 text-xs font-semibold text-ink-700" x-text="showConfirmation ? 'Tutup' : 'Lihat'"></button></div><p x-cloak x-show="confirmation.length" class="mt-2 text-xs" :class="confirmation===password ? 'text-primary-600' : 'text-red-600'" x-text="confirmation===password ? 'Kata sandi cocok ✓' : 'Kata sandi belum sama'"></p><x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" /></div>
                <button type="submit" :disabled="submitting || (password.length>0 && (password.length<8 || confirmation!==password))" class="w-full rounded-xl bg-primary-600 px-5 py-3.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60"><span x-text="submitting ? 'Menyimpan...' : 'Simpan kata sandi baru →'"></span></button>
            </form>
            <p class="mt-7 text-center text-sm"><a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:underline">Kembali ke halaman masuk</a></p>
        </div>
    </x-auth.shell>
</x-guest-layout>
