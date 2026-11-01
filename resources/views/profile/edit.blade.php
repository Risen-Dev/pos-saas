<x-app-layout>
    @section('title', 'Pengaturan Akun')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop">

        @php
            $tenant = auth()->user()->tenant;
            $currentPlan = $tenant?->currentPlan();
            $maxOutlets = $currentPlan?->max_outlets ?? 1;
            $ownedTenantsCount = count($tenants);
            $isOutletFull = $ownedTenantsCount >= $maxOutlets;
        @endphp

        <!-- Header Halaman -->
        <div class="pb-6 mb-8 border-b border-border-200">
            <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                Pengaturan Akun & Bisnis
            </h1>
            <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                Perbarui informasi pribadi, keamanan kata sandi, serta manajemen unit bisnis (tenant) Anda.
            </p>
        </div>

        <!-- 2 Column Split Screen Grid -->
        <div class="grid items-start grid-cols-1 gap-6 lg:grid-cols-12">

            <!-- Left Panel (40% / 5 Cols): Forms Personal & Password -->
            <div class="space-y-6 lg:col-span-5">
                <!-- Personal Info Card -->
                <div class="p-6 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <!-- Update Password Card -->
                <div class="p-6 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                    @include('profile.partials.update-password-form')
                </div>

                <!-- Danger Zone Card -->
                <div class="p-6 border border-red-200 rounded-lg shadow-sm bg-surface-0">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            <!-- Right Panel (60% / 7 Cols): Multi-Tenant Management -->
            <div class="space-y-6 lg:col-span-7">
                <div class="p-6 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                    <div
                        class="flex flex-col justify-between gap-4 pb-4 mb-6 border-b sm:flex-row sm:items-center border-border-200">
                        <div>
                            <h3 class="text-base font-semibold font-heading text-ink-900">
                                Daftar Unit Bisnis (Tenant)
                            </h3>
                            <p class="font-body text-xs text-ink-700 mt-0.5">
                                Terpakai <strong
                                    class="{{ $isOutletFull ? 'text-semantic-danger' : 'text-primary-600' }}">{{ $ownedTenantsCount }}</strong>
                                dari {{ $maxOutlets }} kuota cabang (Paket {{ $currentPlan?->name ?? 'Starter' }}).
                            </p>
                        </div>

                        @if ($isOutletFull)
                            <a href="{{ route('billing.index') }}"
                                class="inline-flex items-center justify-center gap-2 px-4 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-amber-600 hover:bg-amber-700 font-body shrink-0"
                                title="Kuota cabang penuh, upgrade paket Anda">
                                <x-icon class="text-xs fa-solid fa-arrow-up-right-from-square" />
                                <span>Upgrade Cabang</span>
                            </a>
                        @else
                            <a href="{{ route('tenants.add') }}"
                                class="inline-flex items-center justify-center gap-2 px-4 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body shrink-0">
                                <x-icon class="text-xs fa-solid fa-plus" />
                                <span>Tambah Tenant</span>
                            </a>
                        @endif
                    </div>

                    <!-- List Tenant -->
                    <div class="space-y-3">
                        @foreach ($tenants as $tenantItem)
                            <div
                                class="flex items-center justify-between p-4 rounded-md border transition-colors {{ auth()->user()->tenant_id == $tenantItem->id ? 'border-primary-600 bg-primary-50/30' : 'border-border-200 bg-surface-100/50' }}">
                                <div class="flex items-center gap-3.5 min-w-0 pr-2">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-md bg-primary-600 font-heading shrink-0">
                                        {{ strtoupper(substr($tenantItem->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p
                                                class="text-xs font-semibold truncate font-heading md:text-sm text-ink-900">
                                                {{ $tenantItem->name }}
                                            </p>
                                            @if (auth()->user()->tenant_id == $tenantItem->id)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-primary-100 text-primary-700">
                                                    Aktif
                                                </span>
                                            @endif
                                        </div>
                                        <p class="font-mono text-[11px] text-ink-400 truncate mt-0.5">
                                            {{ $tenantItem->email }} • {{ $tenantItem->phone ?? '-' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-1.5 shrink-0">
                                    @if (auth()->user()->tenant_id != $tenantItem->id)
                                        <form action="{{ route('tenants.switch', $tenantItem->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="p-2 transition-colors border rounded-md text-ink-700 hover:text-primary-600 bg-surface-0 border-border-200 hover:bg-primary-50"
                                                title="Pindah ke Bisnis Ini">
                                                <x-icon class="text-xs fa-solid fa-arrow-right-arrow-left" />
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('tenants.edit', $tenantItem->id) }}"
                                        class="p-2 transition-colors border rounded-md text-ink-700 hover:text-primary-600 bg-surface-0 border-border-200 hover:bg-primary-50"
                                        title="Edit Tenant">
                                        <x-icon class="text-xs fa-solid fa-pen-to-square" />
                                    </a>

                                    <form action="{{ route('tenants.destroy', $tenantItem->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus tenant ini? Semua data penjualan di dalamnya akan hilang!')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-2 transition-colors border rounded-md text-ink-700 hover:text-semantic-danger bg-surface-0 border-border-200 hover:bg-red-50"
                                            title="Hapus Tenant">
                                            <x-icon class="text-xs fa-solid fa-trash-can" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Info Multi-Outlet Box -->
                <div class="relative p-6 overflow-hidden text-white rounded-lg shadow-sm bg-primary-600">
                    <div class="relative z-10 max-w-md">
                        <h4 class="mb-1 text-lg font-semibold font-heading">Dukungan Kelola Multi-Outlet</h4>
                        <p class="mb-4 text-xs leading-relaxed font-body text-white/80">
                            Satu akun pemilik dapat mendaftarkan dan berpindah cabang toko dengan mudah tanpa perlu
                            login ulang.
                        </p>
                        @if ($isOutletFull)
                            <a href="{{ route('billing.index') }}"
                                class="inline-flex items-center justify-center px-4 text-xs font-semibold text-white transition-colors rounded-md h-9 bg-amber-500 hover:bg-amber-600 font-body">
                                <x-icon class="fa-solid fa-crown mr-1.5 text-[10px]" /> Upgrade Kuota Cabang
                            </a>
                        @else
                            <a href="{{ route('tenants.add') }}"
                                class="inline-flex items-center justify-center px-4 text-xs font-semibold transition-colors rounded-md h-9 bg-surface-0 hover:bg-surface-100 text-primary-600 font-body">
                                + Tambah Cabang Baru
                            </a>
                        @endif
                    </div>
                    <x-icon class="fa-solid fa-store text-primary-700/50 text-9xl absolute right-[-20px] bottom-[-20px]" />
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
