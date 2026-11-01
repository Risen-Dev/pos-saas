<x-app-layout>
    @section('title', 'Transaksi Baru')

    <div class="px-4 py-12 mx-auto text-center max-w-modal-sm">
        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-primary-50 text-primary-600">
            <x-icon class="text-2xl fa-solid fa-cash-register" />
        </div>
        <h2 class="mb-2 text-xl font-bold font-heading text-ink-900">Membuka Kasir...</h2>
        <p class="mb-6 text-xs font-body text-ink-700">
            Sistem penginputan transaksi baru dialihkan ke Terminal Kasir GrowPOS yang dilengkapi fitur katalog cepat &
            kalkulasi otomatis.
        </p>
        <a href="{{ route('pos.index') }}"
            class="inline-flex items-center justify-center w-full gap-2 px-6 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 font-body">
            <x-icon class="fa-solid fa-store" />
            <span>Buka POS Kasir Sekarang</span>
        </a>
    </div>

    <script>
        // Redirect otomatis ke terminal POS utama GrowPOS
        window.location.href = "{{ route('pos.index') }}";
    </script>
</x-app-layout>
