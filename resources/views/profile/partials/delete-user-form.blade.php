<section class="space-y-4">
    <div class="pb-3 border-b border-red-100">
        <h3 class="text-base font-semibold font-heading text-semantic-danger">
            Hapus Akun Pengguna
        </h3>
        <p class="font-body text-xs text-ink-700 mt-0.5">
            Setelah akun Anda dihapus, semua riwayat data dan akses outlet akan terhapus secara permanen.
        </p>
    </div>

    <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-5 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-semantic-danger hover:bg-red-700 active:bg-red-900 font-body">
        Hapus Akun Permanen
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}"
            class="p-6 border rounded-lg bg-surface-0 max-w-modal-sm border-border-200">
            @csrf
            @method('delete')

            <div
                class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-50 text-semantic-danger">
                <x-icon class="text-xl fa-solid fa-triangle-exclamation" />
            </div>

            <div class="mb-6 text-center">
                <h3 class="text-lg font-semibold font-heading text-ink-900">
                    Konfirmasi Hapus Akun?
                </h3>
                <p class="mt-2 text-xs leading-relaxed font-body text-ink-700">
                    Apakah Anda yakin ingin menghapus akun ini? Masukkan kata sandi konfirmasi Anda untuk melanjutkan.
                </p>
            </div>

            <div class="mb-6">
                <label for="password" class="sr-only">Password</label>
                <div class="relative" x-data="{ passwordVisible: false }"><input type="password" :type="passwordVisible ? 'text' : 'password'" id="password" name="password" placeholder="Masukkan Kata Sandi Anda"
                    class="pr-12 w-full px-3 font-mono text-xs transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-semantic-danger"><x-password-toggle control="password" /></div>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1" />
            </div>

            <div class="flex items-center gap-3">
                <button type="button" x-on:click="$dispatch('close')"
                    class="flex-1 text-xs font-semibold transition-colors rounded-md h-11 bg-surface-100 hover:bg-border-200 text-ink-900 font-body">
                    Batal
                </button>

                <button type="submit"
                    class="flex-1 text-xs font-semibold text-white transition-colors rounded-md h-11 bg-semantic-danger hover:bg-red-700 font-body">
                    Ya, Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</section>
