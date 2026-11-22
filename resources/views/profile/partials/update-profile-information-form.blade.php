<section>
    <div class="pb-3 mb-4 border-b border-border-200">
        <h3 class="text-base font-semibold font-heading text-ink-900">
            Informasi Personal
        </h3>
        <p class="font-body text-xs text-ink-700 mt-0.5">
            Perbarui nama lengkap dan alamat email utama akun Anda.
        </p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                Nama Lengkap <span class="text-semantic-danger">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                autofocus autocomplete="name"
                class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                Alamat Email <span class="text-semantic-danger">*</span>
            </label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                autocomplete="username"
                class="w-full px-3 font-mono text-xs transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
            <x-input-error class="mt-1" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div class="p-3 mt-2 border rounded-md bg-amber-50 border-amber-200">
                    <p class="text-xs font-body text-amber-800">
                        {{ __('Alamat email Anda belum terverifikasi.') }}

                        <button form="send-verification"
                            class="font-semibold underline text-amber-900 hover:text-ink-900">
                            {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                        </button>
                    </p>

                </div>
            @endif
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="px-6 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body">
                Simpan Perubahan
            </button>

        </div>
    </form>
</section>
