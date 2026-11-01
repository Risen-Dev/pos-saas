<section>
    <div class="pb-3 mb-4 border-b border-border-200">
        <h3 class="text-base font-semibold font-heading text-ink-900">
            Pembaruan Kata Sandi
        </h3>
        <p class="font-body text-xs text-ink-700 mt-0.5">
            Pastikan akun Anda menggunakan kata sandi yang kuat untuk menjaga keamanan POS.
        </p>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password"
                class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                Kata Sandi Saat Ini
            </label>
            <div class="relative" x-data="{ passwordVisible: false }"><input type="password" :type="passwordVisible ? 'text' : 'password'" id="update_password_current_password" name="current_password"
                autocomplete="current-password"
                class="pr-12 w-full px-3 font-mono text-xs transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100"><x-password-toggle control="update_password_current_password" /></div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
        </div>

        <div>
            <label for="update_password_password" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                Kata Sandi Baru
            </label>
            <div class="relative" x-data="{ passwordVisible: false }"><input type="password" :type="passwordVisible ? 'text' : 'password'" id="update_password_password" name="password" autocomplete="new-password"
                class="pr-12 w-full px-3 font-mono text-xs transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100"><x-password-toggle control="update_password_password" /></div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="update_password_password_confirmation"
                class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                Konfirmasi Kata Sandi Baru
            </label>
            <div class="relative" x-data="{ passwordVisible: false }"><input type="password" :type="passwordVisible ? 'text' : 'password'" id="update_password_password_confirmation" name="password_confirmation"
                autocomplete="new-password"
                class="pr-12 w-full px-3 font-mono text-xs transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100"><x-password-toggle control="update_password_password_confirmation" /></div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="px-6 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body">
                Perbarui Sandi
            </button>

            @if (session('status') === 'password-updated')
                <span x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-xs font-semibold font-body text-primary-600">
                    Sandi Diperbarui.
                </span>
            @endif
        </div>
    </form>
</section>
