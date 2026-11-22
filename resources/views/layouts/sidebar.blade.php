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
                ['route' => 'orders.index', 'label' => 'Transaksi', 'icon' => 'fa-receipt', 'patterns' => ['orders.*'], 'visible' => true],
                ['route' => 'customers.index', 'label' => 'Pelanggan & bon', 'icon' => 'fa-users', 'patterns' => ['customers.*'], 'visible' => \Illuminate\Support\Facades\Gate::allows('feature-crm')],
                ['route' => 'discounts.index', 'label' => 'Promo', 'icon' => 'fa-tag', 'patterns' => ['discounts.*'], 'visible' => true],
                ['route' => 'shifts.index', 'label' => 'Shift', 'icon' => 'fa-clock-rotate-left', 'patterns' => ['shifts.*'], 'visible' => true],
                ['route' => 'payments.review', 'label' => 'Periksa QRIS', 'icon' => 'fa-money-bill-transfer', 'patterns' => ['payments.review'], 'visible' => $isAdmin && \Illuminate\Support\Facades\Gate::allows('feature-qris')],
            ],
        ],
        [
            'label' => 'Operasional',
            'items' => [
                [
                    'label' => 'Katalog',
                    'icon' => 'fa-boxes-stacked',
                    'patterns' => ['products.*', 'categories.*', 'menu.configure'],
                    'children' => [
                        ['route' => 'products.index', 'label' => $tenant?->catalogLabel() ?? 'Produk', 'icon' => 'fa-box', 'patterns' => ['products.*']],
                        ['route' => 'categories.index', 'label' => 'Kategori', 'icon' => 'fa-layer-group', 'patterns' => ['categories.*']],
                        ['route' => 'menu.configure', 'label' => $tenant?->hasBusinessModule('food') ? 'Varian, tambahan & resep' : 'Varian & pilihan', 'icon' => 'fa-sliders', 'patterns' => ['menu.configure']],
                    ],
                    'visible' => true,
                ],
                [
                    'label' => 'Persediaan',
                    'icon' => 'fa-cubes',
                    'patterns' => ['inventory.*', 'materials.*', 'suppliers.*', 'purchases.*', 'stocktakes.*', 'stock-movements.*'],
                    'children' => [
                        ['route' => 'inventory.index', 'label' => 'Tambah & sesuaikan stok', 'icon' => 'fa-boxes-packing', 'patterns' => ['inventory.index']],
                        ['route' => 'inventory.history', 'label' => 'Riwayat stok', 'icon' => 'fa-clock-rotate-left', 'patterns' => ['inventory.history']],
                        ['route' => 'stock-movements.index', 'label' => 'Mutasi stok', 'icon' => 'fa-arrow-right-arrow-left', 'patterns' => ['stock-movements.*']],
                        ['route' => 'purchases.index', 'label' => 'Pembelian', 'icon' => 'fa-cart-flatbed', 'patterns' => ['purchases.*']],
                        ['route' => 'suppliers.index', 'label' => 'Pemasok', 'icon' => 'fa-truck-field', 'patterns' => ['suppliers.*']],
                        ['route' => 'stocktakes.index', 'label' => 'Hitung stok', 'icon' => 'fa-clipboard-check', 'patterns' => ['stocktakes.*']],
                        ['route' => 'materials.index', 'label' => 'Bahan baku', 'icon' => 'fa-bowl-food', 'patterns' => ['materials.*'], 'visible' => $tenant?->hasBusinessModule('food')],
                    ],
                    'visible' => $tenant?->hasBusinessModule('goods') || $tenant?->hasBusinessModule('food'),
                ],
                ['route' => 'kitchen.index', 'label' => 'Dapur', 'icon' => 'fa-utensils', 'patterns' => ['kitchen.*'], 'visible' => $tenant?->hasBusinessModule('food')],
                ['route' => 'services.index', 'label' => 'Pengerjaan jasa', 'icon' => 'fa-screwdriver-wrench', 'patterns' => ['services.*'], 'visible' => $tenant?->hasBusinessModule('services')],
            ],
        ],
        [
            'label' => 'Keuangan',
            'items' => [
                ['route' => 'cash.index', 'label' => 'Kas & pengeluaran', 'icon' => 'fa-wallet', 'patterns' => ['cash.*'], 'visible' => $isAdmin],
                ['route' => 'finance.index', 'label' => 'Saldo QRIS & pencairan', 'icon' => 'fa-building-columns', 'patterns' => ['finance.*'], 'visible' => $isAdmin && \Illuminate\Support\Facades\Gate::allows('feature-qris')],
            ],
        ],
        [
            'label' => 'Analisis',
            'items' => [
                ['route' => 'reports.index', 'label' => 'Laporan penjualan', 'icon' => 'fa-chart-line', 'patterns' => ['reports.index'], 'visible' => true],
                ['route' => 'reports.exports-list', 'label' => 'Unduhan laporan', 'icon' => 'fa-file-export', 'patterns' => ['reports.exports-*', 'reports.download-file'], 'visible' => true],
                ['route' => 'reports.ai', 'label' => 'Tanya AI', 'icon' => 'fa-wand-magic-sparkles', 'patterns' => ['reports.ai*'], 'visible' => \Illuminate\Support\Facades\Gate::allows('feature-ai-analytics')],
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
    class="[&[x-cloak]]:!hidden fixed w-[260px] inset-y-0 left-0 z-50 flex flex-col h-screen transition-all duration-200 border-r bg-surface-0 border-border-200 lg:translate-x-0 lg:static lg:inset-0 shrink-0">

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
        class="flex flex-col flex-1 min-h-0 px-3 py-3 space-y-4 overflow-y-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
        <a href="{{ route('dashboard') }}"
            @if (request()->route()?->getName() === 'dashboard') aria-current="page" @endif
            :title="sidebarCollapsed ? 'Ringkasan' : ''"
            class="flex items-center gap-3 h-10 px-3 text-sm font-semibold rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
            <x-icon class="w-5 text-center fa-solid fa-chart-pie" />
            <span :class="{ 'lg:hidden': sidebarCollapsed }">Ringkasan</span>
        </a>

        @foreach ($groups as $groupIndex => $group)
            @php
                $visibleItems = collect($group['items'])->filter(fn ($item) => $item['visible'] ?? true);
            @endphp
            @if ($visibleItems->isNotEmpty())
                <section aria-label="{{ $group['label'] }}">
                    <h2 :class="{ 'lg:hidden': sidebarCollapsed }" class="px-3 mb-1 text-[11px] font-bold text-ink-400">{{ $group['label'] }}</h2>
                    <div class="space-y-0.5">
                        @foreach ($visibleItems as $itemIndex => $item)
                            @php
                                $active = request()->routeIs(...$item['patterns']);
                                $panelId = 'sidebar-group-' . $groupIndex . '-' . $itemIndex;
                            @endphp
                            @if (isset($item['children']))
                                <div x-data="{ open: @js($active) }" class="space-y-0.5">
                                    <button type="button" aria-controls="{{ $panelId }}"
                                        :aria-expanded="open.toString()" @click="open = !open"
                                        :title="sidebarCollapsed ? 'Buka ' + @js($item['label']) : ''"
                                        class="flex items-center w-full gap-3 h-10 px-3 text-sm font-medium text-left rounded-md transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600 {{ $active ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
                                        <x-icon class="w-5 text-center fa-solid {{ $item['icon'] }}" />
                                        <span class="flex-1 truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ $item['label'] }}</span>
                                        <x-icon class="text-[10px] transition-transform duration-150 fa-solid fa-chevron-down" ::class="open ? 'rotate-180' : ''" />
                                    </button>
                                    <div id="{{ $panelId }}" x-show="open" x-cloak
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0 -translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 -translate-y-1"
                                        class="[&[x-cloak]]:!hidden ml-2 space-y-0.5 border-l border-border-200 pl-2">
                                        @foreach ($item['children'] as $child)
                                            @if ($child['visible'] ?? true)
                                                @php($childActive = request()->routeIs(...$child['patterns']))
                                                <a href="{{ route($child['route']) }}"
                                                    @if ($childActive) aria-current="page" @endif
                                                    :title="sidebarCollapsed ? @js($child['label']) : ''"
                                                    class="flex items-center gap-2.5 min-h-9 px-2.5 text-xs font-medium rounded-md transition-colors {{ $childActive ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
                                                    <x-icon class="w-4 text-center fa-solid {{ $child['icon'] }}" />
                                                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ $child['label'] }}</span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a href="{{ route($item['route']) }}"
                                    @if ($active) aria-current="page" @endif
                                    :title="sidebarCollapsed ? @js($item['label']) : ''"
                                    class="flex items-center gap-3 h-10 px-3 text-sm font-medium rounded-md transition-colors {{ $active ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
                                    <x-icon class="w-5 text-center fa-solid {{ $item['icon'] }}" />
                                    <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">{{ $item['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        @if ($platformAdmin)
            <section aria-label="Konsol platform">
                <h2 :class="{ 'lg:hidden': sidebarCollapsed }" class="px-3 mb-1 text-[11px] font-bold text-ink-400">Konsol platform</h2>
                <a href="{{ route('admin.withdrawals.index') }}"
                    @if (request()->routeIs('admin.withdrawals.*')) aria-current="page" @endif
                    :title="sidebarCollapsed ? 'Pencairan' : ''"
                    class="flex items-center gap-3 h-10 px-3 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('admin.withdrawals.*') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
                    <x-icon class="w-5 text-center fa-solid fa-building-columns" />
                    <span :class="{ 'lg:hidden': sidebarCollapsed }">Pencairan</span>
                </a>
            </section>
        @endif
    </nav>
</aside>
