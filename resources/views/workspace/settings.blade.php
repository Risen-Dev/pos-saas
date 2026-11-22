<x-app-layout>
    @section('title', 'Pengaturan usaha')

    <div class="max-w-4xl px-4 py-6 mx-auto sm:px-6">
        <x-page-header>
            <div>
                <h1 class="text-2xl font-bold text-ink-900">Pengaturan usaha</h1>
                <p class="mt-2 text-sm text-ink-700">Atur bisnis aktif, akses tim, dan konfigurasi penjualan.</p>
            </div>
        </x-page-header>

        @if (auth()->user()->role !== 'admin')
            <div class="p-4 mt-6 text-sm border rounded-md bg-surface-0 border-border-200 text-ink-700">
                Pengaturan usaha dikelola oleh admin bisnis. Hubungi admin jika Anda memerlukan perubahan.
            </div>
        @else
            <div class="mt-6 space-y-6" data-motion-stagger>
                <section aria-labelledby="business-access-heading" class="p-5 bg-white border rounded-lg border-border-200">
                    <h2 id="business-access-heading" class="text-base font-semibold text-ink-900">Bisnis &amp; akses</h2>
                    <ul class="mt-3 divide-y divide-border-200">
                        <li>
                            <a href="{{ route('tenants.index') }}" class="flex items-center justify-between gap-4 py-3 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600">
                                <span><span class="block text-sm font-semibold text-ink-900">Bisnis yang dikelola</span><span class="block mt-1 text-xs text-ink-700">Beralih atau tambahkan bisnis ke akun ini.</span></span>
                                <x-icon class="text-xs text-ink-400 fa-solid fa-chevron-right" />
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('business.edit') }}" class="flex items-center justify-between gap-4 py-3 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600">
                                <span><span class="block text-sm font-semibold text-ink-900">Jenis usaha &amp; fitur</span><span class="block mt-1 text-xs text-ink-700">Pilih kebutuhan barang, jasa, atau dapur.</span></span>
                                <x-icon class="text-xs text-ink-400 fa-solid fa-chevron-right" />
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('employees.index') }}" class="flex items-center justify-between gap-4 py-3 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600">
                                <span><span class="block text-sm font-semibold text-ink-900">Pegawai &amp; akses</span><span class="block mt-1 text-xs text-ink-700">Kelola pemilik, pengelola, dan kasir.</span></span>
                                <x-icon class="text-xs text-ink-400 fa-solid fa-chevron-right" />
                            </a>
                        </li>
                    </ul>
                </section>

                <section aria-labelledby="sales-settings-heading" class="p-5 bg-white border rounded-lg border-border-200">
                    <h2 id="sales-settings-heading" class="text-base font-semibold text-ink-900">Aturan penjualan &amp; paket</h2>
                    <ul class="mt-3 divide-y divide-border-200">
                        <li>
                            <a href="{{ route('settings.index') }}" class="flex items-center justify-between gap-4 py-3 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600">
                                <span><span class="block text-sm font-semibold text-ink-900">Pajak &amp; loyalitas</span><span class="block mt-1 text-xs text-ink-700">Atur pajak penjualan dan program pelanggan.</span></span>
                                <x-icon class="text-xs text-ink-400 fa-solid fa-chevron-right" />
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('billing.index') }}" class="flex items-center justify-between gap-4 py-3 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600">
                                <span><span class="block text-sm font-semibold text-ink-900">Paket &amp; langganan</span><span class="block mt-1 text-xs text-ink-700">Lihat paket, masa aktif, dan tagihan.</span></span>
                                <x-icon class="text-xs text-ink-400 fa-solid fa-chevron-right" />
                            </a>
                        </li>
                    </ul>
                </section>

                <section aria-labelledby="activity-heading" class="p-5 bg-white border rounded-lg border-border-200">
                    <h2 id="activity-heading" class="text-base font-semibold text-ink-900">Aktivitas usaha</h2>
                    <ul class="mt-3 divide-y divide-border-200">
                        <li>
                            <a href="{{ route('activity-logs.index') }}" class="flex items-center justify-between gap-4 py-3 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600">
                                <span><span class="block text-sm font-semibold text-ink-900">Riwayat perubahan</span><span class="block mt-1 text-xs text-ink-700">Telusuri perubahan penting pada bisnis ini.</span></span>
                                <x-icon class="text-xs text-ink-400 fa-solid fa-chevron-right" />
                            </a>
                        </li>
                    </ul>
                </section>
            </div>
        @endif
    </div>
</x-app-layout>
