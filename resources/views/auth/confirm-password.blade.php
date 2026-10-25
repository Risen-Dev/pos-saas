<x-guest-layout>
    @section('title', 'Konfirmasi Kata Sandi')
    <x-auth.shell eyebrow="SECURITY CHECKPOINT" heading="Akses penting membutuhkan konfirmasi." description="Konfirmasi identitas Anda sebelum melanjutkan ke area sensitif.">
        <div x-data="{showPassword:false,submitting:false}">
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-primary-600">VERIFIKASI KEAMANAN</p>
            <h1 class="text-4xl font-semibold tracking-[-0.055em]">Konfirmasi identitas.</h1>
            <p class="mb-8 mt-3 text-sm leading-7 text-ink-700">Area ini dilindungi. Masukkan kembali kata sandi Anda untuk melanjutkan.</p>
            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6" @submit="submitting=true">
                @csrf
                <div><label for="password" class="mb-2 block text-sm font-medium">Kata sandi saat ini</label><div class="relative"><input id="password" name="password" :type="showPassword ? 'text' : 'password'" required autofocus autocomplete="current-password" placeholder="Masukkan kata sandi" class="w-full rounded-xl border border-border-200 px-4 py-3 pr-16 text-sm outline-none focus:border-primary-600 focus:ring-4 focus:ring-primary-600/10"><button type="button" @click="showPassword=!showPassword" class="absolute inset-y-0 right-3 px-2 text-xs font-semibold text-ink-700" x-text="showPassword ? 'Tutup' : 'Lihat'"></button></div><x-input-error :messages="$errors->get('password')" class="mt-2" /></div>
                <button type="submit" :disabled="submitting" class="w-full rounded-xl bg-primary-600 px-5 py-3.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60"><span x-text="submitting ? 'Memverifikasi...' : 'Konfirmasi dan lanjutkan →'"></span></button>
            </form>
        </div>
    </x-auth.shell>
</x-guest-layout>
