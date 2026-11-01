<aside x-cloak
    :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full': !sidebarOpen,
        'lg:w-[260px]': !sidebarCollapsed,
        'lg:w-[72px]': sidebarCollapsed
    }"
    class="fixed w-[260px] inset-y-0 left-0 z-50 flex flex-col h-screen transition-all duration-300 border-r bg-surface-0 border-border-200 lg:translate-x-0 lg:static lg:inset-0 shrink-0">

    <!-- Sidebar Header (Brand & Logo) -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-border-200 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">

            @if (auth()->user()->tenant && auth()->user()->tenant->img_logo)
                <div
                    class="flex items-center justify-center w-10 h-10 overflow-hidden border rounded-md border-border-200 bg-surface-100 shrink-0">
                    <img src="{{ asset('storage/' . auth()->user()->tenant->img_logo) }}"
                        alt="Logo {{ auth()->user()->tenant->name }}" class="object-cover w-full h-full">
                </div>
            @else
                <div
                    class="flex items-center justify-center w-10 h-10 text-base font-bold text-white rounded-md shadow-sm bg-primary-600 font-heading shrink-0">
                    GP
                </div>
            @endif

            <div class="flex flex-col min-w-0 transition-opacity duration-200"
                :class="{ 'lg:hidden': sidebarCollapsed }">
                <span class="text-sm font-bold leading-tight tracking-tight truncate font-heading text-ink-900">
                    {{ auth()->user()->tenant ? auth()->user()->tenant->name : 'GrowPOS' }}
                </span>
                <span class="text-[10px] font-semibold text-primary-600 uppercase tracking-wider mt-0.5">
                    {{ auth()->user()->tenant ? \App\Support\BusinessProfile::TYPES[auth()->user()->tenant->businessType()] : 'Teman UMKM' }}
                </span>
            </div>
        </a>

        <!-- Mobile Close Button -->
        <button @click="sidebarOpen = false" class="p-1 text-ink-400 hover:text-ink-900 lg:hidden"
            aria-label="Tutup Sidebar">
            <x-icon class="text-lg fa-solid fa-xmark" />
        </button>
    </div>

    <!-- Navigation Body -->
    <nav x-ref="sidebarNav" x-init="$nextTick(() => { $refs.sidebarNav.scrollTop = Number(sessionStorage.getItem('growpos:sidebar-scroll') || 0) })"
        @scroll.debounce.100ms="sessionStorage.setItem('growpos:sidebar-scroll', $refs.sidebarNav.scrollTop)"
        class="flex-1 min-h-0 px-3 py-4 space-y-6 overflow-y-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">

        @php
            $tenant = auth()->user()->tenant;
            $items = [
                ['dashboard', 'Ringkasan', 'fa-chart-pie', true, 'dashboard'],
                ['pos.index', 'Kasir', 'fa-cash-register', true, 'pos.*'],
                ['orders.index', 'Transaksi', 'fa-receipt', true, 'orders.*,customers.*,discounts.*,shifts.*,payments.review'],
                ['kitchen.index', 'Antrean dapur', 'fa-utensils', $tenant?->hasBusinessModule('food'), 'kitchen.*'],
                ['services.index', 'Pengerjaan jasa', 'fa-screwdriver-wrench', $tenant?->hasBusinessModule('services'), 'services.*'],
                ['products.index', $tenant?->catalogLabel() ?? 'Produk', 'fa-boxes-stacked', true, 'products.*,categories.*,menu.configure'],
                ['inventory.index', 'Stok', 'fa-cubes', $tenant?->hasBusinessModule('goods') || $tenant?->hasBusinessModule('food'), 'inventory.*,materials.*,suppliers.*,purchases.*,stocktakes.*,stock-movements.*'],
                ['reports.index', 'Laporan', 'fa-chart-line', true, 'reports.*,cash.*,finance.*'],
                ['workspace.settings', 'Pengaturan', 'fa-gears', true, 'workspace.settings,business.*,employees.*,settings.*,tenants.*,billing.*,getting-started,help,activity-logs.*'],
            ];
        @endphp
        <div class="space-y-1">
            @foreach($items as [$route, $label, $icon, $visible, $patterns])
                @if($visible)
                    <a href="{{ route($route) }}" @if(request()->routeIs(...explode(',', $patterns))) aria-current="page" @endif
                        class="flex items-center gap-3 px-3 h-11 text-sm font-semibold rounded-md transition-colors {{ request()->routeIs(...explode(',', $patterns)) ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100' }}"
                        :title="sidebarCollapsed ? @js($label) : ''">
                        <x-icon class="w-5 text-base text-center fa-solid {{ $icon }}" />
                        <span x-show="!sidebarCollapsed" class="truncate">{{ $label }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </nav>

    <div class="p-3 border-t border-border-200" x-data="connectionStatus">
        <p class="text-xs font-semibold" x-text="label" role="status"></p>
        <p x-show="!sidebarCollapsed" class="mt-1 text-xs text-ink-700">Status koneksi perangkat</p>
    </div>
</aside>
