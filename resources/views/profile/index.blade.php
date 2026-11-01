<x-app-layout>
    @section('title', 'Profil Pengguna')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop">

        @php
            $tenant = auth()->user()->tenant;
            $currentSubscription = $tenant
                ? $tenant
                    ->subscriptions()
                    ->where('status', 'active')
                    ->where('end_date', '>=', now())
                    ->latest('id')
                    ->first()
                : null;

            $currentPlan = $currentSubscription?->plan ?? $tenant?->currentPlan();
            $maxOutlets = $currentPlan?->max_outlets ?? 1;
            $ownedTenantsCount = auth()->user()->tenants()->count();

            // Perhitungan Sisa Hari Integer Presisi (Tanpa Koma)
            $remainingDays = $currentSubscription
                ? (int) ceil(now()->diffInHours(\Carbon\Carbon::parse($currentSubscription->end_date)) / 24)
                : 0;
        @endphp

        <!-- Banner Header Profil Utama -->
        <div class="relative mb-8 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <div class="h-28 bg-primary-600"></div>

            <div class="relative flex flex-col justify-between gap-4 px-6 pb-6 -mt-12 md:flex-row md:items-end">
                <div class="flex flex-col items-center gap-4 text-center sm:flex-row sm:items-end sm:text-left">
                    <div class="w-24 h-24 rounded-lg bg-surface-0 p-1.5 shadow-md border border-border-200 shrink-0">
                        <div
                            class="flex items-center justify-center w-full h-full text-3xl font-bold rounded-md bg-primary-100 text-primary-700 font-heading">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>

                    <div class="pb-1">
                        <h1 class="text-xl font-bold leading-tight font-heading md:text-2xl text-ink-900">
                            {{ auth()->user()->name }}
                        </h1>
                        <p class="font-mono text-xs text-ink-400 mt-0.5">
                            {{ auth()->user()->email }}
                        </p>
                        <div class="flex items-center justify-center gap-2 mt-2 sm:justify-start">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-100 text-primary-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary-600"></span>
                                Akun Terverifikasi
                            </span>

                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                                <x-icon class="fa-solid fa-crown text-amber-600 text-[10px]" />
                                Paket {{ $currentPlan?->name ?? 'Starter' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center sm:justify-end">
                    <a href="{{ route('profile.edit') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 text-xs font-semibold transition-colors border rounded-md shadow-sm h-11 bg-surface-0 hover:bg-surface-100 border-border-200 text-ink-900 font-body md:text-sm">
                        <x-icon class="text-xs fa-solid fa-user-pen" />
                        <span>Pengaturan Akun & Tenant</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 3 Column Overview Grid -->
        <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">

            <!-- Card 1: Status Berlangganan (SaaS Status & Sisa Hari Integer) -->
            <div class="flex flex-col justify-between p-6 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-border-200">
                        <h3 class="text-base font-semibold font-heading text-ink-900">Paket Langganan</h3>
                        <div
                            class="flex items-center justify-center w-8 h-8 text-xs rounded-md bg-amber-50 text-amber-600">
                            <x-icon class="fa-solid fa-crown" />
                        </div>
                    </div>

                    @if ($currentSubscription && $remainingDays > 0)
                        <div class="p-4 space-y-2 border rounded-md bg-emerald-50/50 border-emerald-200">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold tracking-wider uppercase text-emerald-900">
                                    {{ $currentPlan?->name ?? 'Pro Plan' }}
                                </span>
                                <span
                                    class="px-2 py-0.5 text-[10px] font-extrabold bg-emerald-100 text-emerald-800 rounded-full">
                                    Aktif
                                </span>
                            </div>
                            <p class="text-xs text-ink-700">
                                Masa berlaku hingga:
                                <strong>{{ \Carbon\Carbon::parse($currentSubscription->end_date)->translatedFormat('d M Y') }}</strong>
                            </p>
                        </div>
                    @else
                        <div class="p-4 space-y-1 border rounded-md bg-rose-50 border-rose-200">
                            <span class="text-xs font-bold uppercase text-rose-900">Starter / Non-Aktif</span>
                            <p class="text-xs text-rose-700">Fitur terbatas pada kuota gratis.</p>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-4 mt-6 text-xs border-t border-border-200 font-body">
                    <span class="text-ink-700">Masa Aktif Tersisa:</span>
                    <span
                        class="font-mono font-bold {{ $remainingDays <= 5 ? 'text-semantic-danger' : 'text-primary-600' }}">
                        {{ $remainingDays }} Hari
                    </span>
                </div>
            </div>

            <!-- Card 2: Bisnis Aktif & Kuota Cabang -->
            <div class="flex flex-col justify-between p-6 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-border-200">
                        <h3 class="text-base font-semibold font-heading text-ink-900">Bisnis & Outlet Aktif</h3>
                        <div
                            class="flex items-center justify-center w-8 h-8 text-xs rounded-md bg-primary-50 text-primary-600">
                            <x-icon class="fa-solid fa-store" />
                        </div>
                    </div>

                    @if (auth()->user()->tenant)
                        <div class="flex items-center gap-3 p-4 border rounded-md bg-surface-100 border-border-200">
                            <div
                                class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-md bg-primary-600 font-heading shrink-0">
                                {{ strtoupper(substr(auth()->user()->tenant->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate font-heading text-ink-900">
                                    {{ auth()->user()->tenant->name }}
                                </p>
                                <p class="font-mono text-xs text-ink-400 truncate mt-0.5">
                                    {{ auth()->user()->tenant->email ?? auth()->user()->email }}
                                </p>
                            </div>
                        </div>
                    @else
                        <p class="text-xs italic font-body text-ink-400">Belum ada unit bisnis yang diaktifkan.</p>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-4 mt-6 text-xs border-t border-border-200 font-body">
                    <span class="text-ink-700">Penggunaan Outlet:</span>
                    <span class="font-mono font-semibold text-ink-900">
                        {{ $ownedTenantsCount }} / {{ $maxOutlets }} Kuota Cabang
                    </span>
                </div>
            </div>

            <!-- Card 3: Keamanan Akun -->
            <div class="flex flex-col justify-between p-6 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-border-200">
                        <h3 class="text-base font-semibold font-heading text-ink-900">Status Keamanan</h3>
                        <div
                            class="flex items-center justify-center w-8 h-8 text-xs rounded-md bg-primary-50 text-primary-600">
                            <x-icon class="fa-solid fa-shield-halved" />
                        </div>
                    </div>

                    <div class="space-y-3 text-xs font-body">
                        <div
                            class="flex items-center justify-between p-3 border rounded-md bg-surface-100 border-border-200">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary-600"></span>
                                <span class="font-semibold text-ink-900">Hash Kata Sandi</span>
                            </div>
                            <span
                                class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold text-primary-700 bg-primary-100 rounded-full">
                                BCRYPT Active
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between p-3 border rounded-md bg-surface-100 border-border-200">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary-600"></span>
                                <span class="font-semibold text-ink-900">Verifikasi Email</span>
                            </div>
                            <span class="font-mono text-[11px] font-semibold text-ink-700">
                                {{ auth()->user()->email_verified_at ? 'Terverifikasi' : 'Belum' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-6 border-t border-border-200 font-mono text-[11px] text-ink-400">
                    Aktivitas Sesi: Sesi Aktif (WIB)
                </div>
            </div>

        </div>

        <!-- Quick Nav Buttons Grid -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <a href="{{ route('billing.index') }}"
                class="p-4 text-center transition-colors border rounded-lg shadow-sm bg-surface-0 border-border-200 hover:border-amber-500 group">
                <p
                    class="font-heading text-[11px] font-semibold text-ink-400 group-hover:text-amber-600 uppercase tracking-wider">
                    Langganan
                </p>
                <p class="mt-1 text-sm font-semibold font-heading text-ink-900">Billing & Upgrade</p>
            </a>

            <a href="{{ route('profile.edit') }}"
                class="p-4 text-center transition-colors border rounded-lg shadow-sm bg-surface-0 border-border-200 hover:border-primary-600 group">
                <p
                    class="font-heading text-[11px] font-semibold text-ink-400 group-hover:text-primary-600 uppercase tracking-wider">
                    Kelola
                </p>
                <p class="mt-1 text-sm font-semibold font-heading text-ink-900">Tenant & Outlet</p>
            </a>

            <a href="{{ route('tenants.index') }}"
                class="p-4 text-center transition-colors border rounded-lg shadow-sm bg-surface-0 border-border-200 hover:border-primary-600 group">
                <p
                    class="font-heading text-[11px] font-semibold text-ink-400 group-hover:text-primary-600 uppercase tracking-wider">
                    Daftar
                </p>
                <p class="mt-1 text-sm font-semibold font-heading text-ink-900">Manajemen Toko</p>
            </a>

            <div
                class="p-4 text-center border rounded-lg cursor-not-allowed bg-surface-100 border-border-200 opacity-60">
                <p class="font-heading text-[11px] font-semibold text-ink-400 uppercase tracking-wider">Log Sesi</p>
                <p class="mt-1 text-sm font-semibold font-heading text-ink-400">Aktivitas Kasir</p>
            </div>
        </div>

    </div>
</x-app-layout>
