@php
    $user = auth()->user();
    $tenant = $user->tenant;
    $isAdmin = $user->role === 'admin';
    $platformAdmin = \Illuminate\Support\Facades\Gate::allows('platform-admin');
    $businessCount = $isAdmin ? $user->tenants()->count() : 1;

    $groups = [
        [
            'label' => 'Penjualan',
            'items' => [
                ['orders.index', 'Transaksi', 'fa-receipt', 'orders.*', true],
                ['discounts.index', 'Promo', 'fa-tag', 'discounts.*', true],
                ['shifts.index', 'Shift', 'fa-clock-rotate-left', 'shifts.*', true],
                ['payments.review', 'Periksa QRIS', 'fa-money-bill-transfer', 'payments.review', $isAdmin && \Illuminate\Support\Facades\Gate::allows('feature-qris')],
            ],
        ],
        [
            'label' => 'Kelola usaha',
            'items' => [
                ['products.index', 'Katalog', 'fa-boxes-stacked', 'products.*,categories.*,menu.configure', true],
                ['inventory.index', 'Persediaan', 'fa-cubes', 'inventory.*,materials.*,suppliers.*,purchases.*,stocktakes.*,stock-movements.*', $tenant?->hasBusinessModule('goods') || $tenant?->hasBusinessModule('food')],
                ['customers.index', 'Pelanggan', 'fa-users', 'customers.*', \Illuminate\Support\Facades\Gate::allows('feature-crm')],
                ['kitchen.index', 'Dapur', 'fa-utensils', 'kitchen.*', $tenant?->hasBusinessModule('food')],
                ['services.index', 'Pengerjaan jasa', 'fa-screwdriver-wrench', 'services.*', $tenant?->hasBusinessModule('services')],
            ],
        ],
        [
            'label' => 'Analisis',
            'items' => [
                ['reports.index', 'Laporan', 'fa-chart-line', 'reports.*,cash.*,finance.*', true],
            ],
        ],
    ];
@endphp

<aside x-cloak
    :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full': !sidebarOpen,
        'lg:w-[260px]': !sidebarCollapsed,
        'lg:w-[72px]': sidebarCollapsed
    }"
    class="fixed w-[260px] inset-y-0 left-0 z-50 flex flex-col h-screen transition-all duration-200 border-r bg-surface-0 border-border-200 lg:translate-x-0 lg:static lg:inset-0 shrink-0">

    <div class="flex items-center justify-between h-16 px-4 border-b border-border-200 shrink-0">
        <div class="flex items-center min-w-0 gap-3">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0" aria-label="Ringkasan usaha">
                @if ($tenant?->img_logo)
                    <span class="flex items-center justify-center w-10 h-10 overflow-hidden border rounded-md border-border-200 bg-surface-100 shrink-0">
                        <img src="{{ asset('storage/' . $tenant->img_logo) }}" alt="" class="object-cover w-full h-full">
                    </span>
                @else
                    <span class="flex items-center justify-center w-10 h-10 text-base font-bold text-white rounded-md bg-primary-600 font-heading shrink-0" aria-hidden="true">GP</span>
                @endif
            </a>
            <div class="flex flex-col min-w-0" :class="{ 'lg:hidden': sidebarCollapsed }">
                <span class="text-sm font-bold leading-tight truncate font-heading text-ink-900">{{ $tenant?->name ?? 'GrowPOS' }}</span>
                <span class="mt-0.5 text-[10px] font-semibold text-primary-700">{{ $tenant ? \App\Support\BusinessProfile::TYPES[$tenant->businessType()] : 'Workspace usaha' }}</span>
            </div>
        </div>

        <button type="button" @click="sidebarOpen = false" class="p-2 rounded-md text-ink-700 hover:bg-surface-100 lg:hidden"
            aria-label="Tutup navigasi">
            <x-icon class="text-lg fa-solid fa-xmark" />
        </button>
    </div>

    @if ($isAdmin && $businessCount > 1)
        <a href="{{ route('tenants.index') }}" :class="{ 'lg:hidden': sidebarCollapsed }"
            class="flex items-center justify-between gap-2 px-3 py-2 mx-3 mt-3 text-xs font-semibold rounded-md bg-surface-100 text-ink-700 hover:bg-primary-50 hover:text-primary-700"
            aria-label="Pilih bisnis aktif: {{ $tenant?->name }}">
            <span class="truncate">{{ $tenant?->name ?? 'Pilih bisnis' }}</span>
            <span class="shrink-0">Ganti</span>
        </a>
    @endif

    <div class="px-3 pt-3 shrink-0">
        <a href="{{ route('pos.index') }}"
            @if (request()->route()?->getName() === 'pos.index') aria-current="page" @endif
            :title="sidebarCollapsed ? 'Kasir' : ''"
            class="flex items-center justify-center gap-3 h-11 px-3 text-sm font-semibold text-white rounded-md bg-primary-700 hover:bg-primary-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
            <x-icon class="w-5 text-center fa-solid fa-cash-register" />
            <span :class="{ 'lg:hidden': sidebarCollapsed }">Kasir</span>
        </a>
    </div>

    <nav id="business-sidebar" aria-label="Navigasi usaha" x-ref="sidebarNav"
        x-init="$nextTick(() => { $refs.sidebarNav.scrollTop = Number(sessionStorage.getItem('growpos:sidebar-scroll') || 0) })"
        @scroll.debounce.100ms="sessionStorage.setItem('growpos:sidebar-scroll', $refs.sidebarNav.scrollTop)"
        class="flex flex-col flex-1 min-h-0 px-3 py-4 space-y-5 overflow-y-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
        <a href="{{ route('dashboard') }}"
            @if (request()->route()?->getName() === 'dashboard') aria-current="page" @endif
            :title="sidebarCollapsed ? 'Ringkasan' : ''"
            class="flex items-center gap-3 h-10 px-3 text-sm font-semibold rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
            <x-icon class="w-5 text-center fa-solid fa-chart-pie" />
            <span :class="{ 'lg:hidden': sidebarCollapsed }">Ringkasan</span>
        </a>

        @foreach ($groups as $group)
            @php
                $visibleItems = collect($group['items'])->filter(fn ($item) => $item[4]);
            @endphp
            @if ($visibleItems->isNotEmpty())
                <section aria-label="{{ $group['label'] }}">
                    <h2 :class="{ 'lg:hidden': sidebarCollapsed }" class="px-3 mb-1 text-[11px] font-bold text-ink-400">{{ $group['label'] }}</h2>
                    <div class="space-y-1">
                        @foreach ($visibleItems as [$route, $label, $icon, $patterns, $visible])
                            @php
                                $active = request()->routeIs(...explode(',', $patterns));
                                $currentPage = request()->route()?->getName() === $route;
                            @endphp
                            <a href="{{ route($route) }}"
                                @if ($currentPage) aria-current="page" @endif
                                :title="sidebarCollapsed ? @js($label) : ''"
                                class="flex items-center gap-3 h-10 px-3 text-sm font-medium rounded-md transition-colors {{ $active ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
                                <x-icon class="w-5 text-center fa-solid {{ $icon }}" />
                                <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        @if ($platformAdmin)
            <section aria-label="Konsol platform">
                <h2 :class="{ 'lg:hidden': sidebarCollapsed }" class="px-3 mb-1 text-[11px] font-bold text-ink-400">Konsol platform</h2>
                <a href="{{ route('admin.withdrawals.index') }}"
                    @if (request()->route()?->getName() === 'admin.withdrawals.index') aria-current="page" @endif
                    :title="sidebarCollapsed ? 'Pencairan' : ''"
                    class="flex items-center gap-3 h-10 px-3 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.withdrawals.*') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
                    <x-icon class="w-5 text-center fa-solid fa-building-columns" />
                    <span :class="{ 'lg:hidden': sidebarCollapsed }">Pencairan</span>
                </a>
            </section>
        @endif

        <div class="pt-3 mt-auto space-y-1 border-t border-border-200">
            @if ($isAdmin)
                <a href="{{ route('workspace.settings') }}"
                    @if (request()->route()?->getName() === 'workspace.settings') aria-current="page" @endif
                    :title="sidebarCollapsed ? 'Pengaturan usaha' : ''"
                    class="flex items-center gap-3 h-10 px-3 text-sm font-medium rounded-md {{ request()->routeIs('workspace.settings', 'business.*', 'employees.*', 'settings.*', 'tenants.*', 'billing.*', 'activity-logs.*') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
                    <x-icon class="w-5 text-center fa-solid fa-gears" />
                    <span :class="{ 'lg:hidden': sidebarCollapsed }">Pengaturan usaha</span>
                </a>
            @endif
            <a href="{{ route('help') }}"
                @if (request()->route()?->getName() === 'help') aria-current="page" @endif
                :title="sidebarCollapsed ? 'Bantuan' : ''"
                class="flex items-center gap-3 h-10 px-3 text-sm font-medium rounded-md {{ request()->routeIs('help') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
                <x-icon class="w-5 text-center fa-regular fa-circle-question" />
                <span :class="{ 'lg:hidden': sidebarCollapsed }">Bantuan</span>
            </a>
            <a href="{{ route('profile.index') }}"
                @if (request()->route()?->getName() === 'profile.index') aria-current="page" @endif
                :title="sidebarCollapsed ? 'Akun' : ''"
                class="flex items-center gap-3 h-10 px-3 text-sm font-medium rounded-md {{ request()->routeIs('profile.*') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
                <x-icon class="w-5 text-center fa-regular fa-user" />
                <span :class="{ 'lg:hidden': sidebarCollapsed }">Akun</span>
            </a>
            <div x-data="connectionStatus" class="px-3 pt-2">
                <p class="text-xs font-semibold" x-text="label" role="status"></p>
                <p x-show="!sidebarCollapsed" class="mt-1 text-xs text-ink-700">Status koneksi perangkat</p>
            </div>
        </div>
    </nav>
</aside>
