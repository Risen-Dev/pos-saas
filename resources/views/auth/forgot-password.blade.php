<x-guest-layout>
    @section('title', 'Lupa Kata Sandi')
    <x-auth.shell eyebrow="ACCOUNT RECOVERY" heading="Kembali bekerja dengan tenang." description="Pemulihan akun melalui email yang terdaftar di GrowPOS.">
        <div x-data="{submitting:false}">
            <a href="{{ route('login') }}" class="mb-9 inline-flex text-sm font-medium text-ink-700 hover:text-primary-600">← Kembali ke masuk</a>
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-primary-600">PEMULIHAN AKUN</p>
            <h1 class="text-4xl font-semibold tracking-[-0.055em]">Lupa kata sandi?</h1>
            <p class="mb-8 mt-3 text-sm leading-7 text-ink-700">Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.</p>
            @if(session('status'))<div role="status" class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>@endif
            <form method="POST" action="{{ route('password.email') }}" class="space-y-6" @submit="submitting=true">
                @csrf
                <x-auth.field name="email" label="Alamat email" type="email" :value="old('email')" autocomplete="email" placeholder="nama@bisnis.com" :autofocus="true" />
                <button type="submit" :disabled="submitting" class="w-full rounded-xl bg-primary-600 px-5 py-3.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60"><span x-text="submitting ? 'Mengirim tautan...' : 'Kirim tautan pemulihan →'"></span></button>
            </form>
        </div>
    </x-auth.shell>
</x-guest-layout>
