<aside
    :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full': !sidebarOpen,
        'lg:w-[260px]': !sidebarCollapsed,
        'lg:w-[72px]': sidebarCollapsed
    }"
    class="fixed inset-y-0 left-0 z-50 flex flex-col h-screen transition-all duration-300 border-r bg-surface-0 border-border-200 lg:translate-x-0 lg:static lg:inset-0 shrink-0">

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

        <!-- Group: Utama -->
        <div>
            <p x-show="!sidebarCollapsed"
                class="px-3 text-[10px] font-bold text-ink-400 uppercase tracking-widest mb-2">
                Utama
            </p>
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                    :title="sidebarCollapsed ? 'Dashboard' : ''">
                    <x-icon class="w-5 text-base text-center fa-solid fa-chart-pie" />
                    <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
                </a>
            </div>
        </div>

        @if (auth()->user()->role === 'admin' || auth()->user()->can('platform-admin'))
            <!-- Group: SaaS Admin -->
            <div>
                <p x-show="!sidebarCollapsed"
                    class="px-3 text-[10px] font-bold text-ink-400 uppercase tracking-widest mb-2">
                    SaaS Management
                </p>
                <div class="space-y-1">
                    <a href="{{ route('tenants.index') }}"
                        class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('tenants.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                        :title="sidebarCollapsed ? 'Manajemen Tenant' : ''">
                        <x-icon class="w-5 text-base text-center fa-solid fa-store" />
                        <span x-show="!sidebarCollapsed" class="truncate">Manajemen Tenant</span>
                    </a>

                    @can('platform-admin')
                        <a href="{{ route('admin.withdrawals.index') }}"
                            class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('admin.withdrawals.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                            :title="sidebarCollapsed ? 'Approval Penarikan' : ''">
                            <x-icon class="w-5 text-base text-center fa-solid fa-money-check-dollar" />
                            <span x-show="!sidebarCollapsed" class="truncate">Approval Penarikan</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endif

        <!-- Group: Kasir & Operasional -->
        <div>
            <p x-show="!sidebarCollapsed"
                class="px-3 text-[10px] font-bold text-ink-400 uppercase tracking-widest mb-2">
                Kasir & Transaksi
            </p>
            <div class="space-y-1">
                <a href="{{ route('pos.index') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('pos.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                    :title="sidebarCollapsed ? 'Kasir POS' : ''">
                    <x-icon class="w-5 text-base text-center fa-solid fa-cash-register" />
                    <span x-show="!sidebarCollapsed" class="truncate">POS Terminal</span>
                </a>

                <a href="{{ route('orders.index') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('orders.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                    :title="sidebarCollapsed ? 'Riwayat Transaksi' : ''">
                    <x-icon class="w-5 text-base text-center fa-solid fa-receipt" />
                    <span x-show="!sidebarCollapsed" class="truncate">Riwayat Transaksi</span>
                </a>

                @can('feature-crm')
                    <a href="{{ route('customers.index') }}"
                        class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('customers.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                        :title="sidebarCollapsed ? 'Pelanggan / CRM' : ''">
                        <x-icon class="w-5 text-base text-center fa-solid fa-users" />
                        <span x-show="!sidebarCollapsed" class="truncate">Pelanggan / CRM</span>
                    </a>
                @else
                    <a href="{{ auth()->user()->role === 'admin' ? route('billing.index') : '#' }}"
                        class="flex items-center justify-between px-3 text-xs font-semibold transition-colors rounded-md h-11 text-ink-400 hover:bg-surface-100 opacity-60"
                        :title="sidebarCollapsed ? 'Pelanggan / CRM (Upgrade Growth)' : ''">
                        <div class="flex items-center gap-3 truncate">
                            <x-icon class="w-5 text-base text-center fa-solid fa-users" />
                            <span x-show="!sidebarCollapsed" class="truncate">Pelanggan / CRM</span>
                        </div>
                        <span x-show="!sidebarCollapsed"
                            class="px-1.5 py-0.5 text-[9px] font-bold text-amber-800 bg-amber-100 rounded uppercase">GROWTH</span>
                    </a>
                @endcan

                <a href="{{ route('discounts.index') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('discounts.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                    :title="sidebarCollapsed ? 'Promo & Diskon' : ''">
                    <x-icon class="w-5 text-base text-center fa-solid fa-tags" />
                    <span x-show="!sidebarCollapsed" class="truncate">Promo & Diskon</span>
                </a>

                <a href="{{ route('shifts.index') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('shifts.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                    :title="sidebarCollapsed ? 'Shift Kasir' : ''">
                    <x-icon class="w-5 text-base text-center fa-solid fa-user-clock" />
                    <span x-show="!sidebarCollapsed" class="truncate">Shift Kasir</span>
                </a>

                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('payments.review') }}"
                        class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors
        {{ request()->routeIs('payments.review')
            ? 'bg-primary-50 text-primary-600'
            : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                        :title="sidebarCollapsed ? 'Pemeriksaan pembayaran' : ''">

                        <x-icon class="w-5 text-base text-center fa-solid fa-money-check-dollar" />

                        <span x-show="!sidebarCollapsed" class="truncate">
                            Pemeriksaan pembayaran
                        </span>
                    </a>
                @endif

                @if(auth()->user()->role === 'admin')
                <a href="{{ route('employees.index') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('employees.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                    :title="sidebarCollapsed ? 'Karyawan' : ''">
                    <x-icon class="w-5 text-base text-center fa-solid fa-id-card" />
                    <span x-show="!sidebarCollapsed" class="truncate">Karyawan</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Group: Inventori & Bahan -->
        <div>
            <p x-show="!sidebarCollapsed"
                class="px-3 text-[10px] font-bold text-ink-400 uppercase tracking-widest mb-2">
                Produk & Stok
            </p>
            <div class="space-y-1">
                <a href="{{ route('products.index') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('products.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                    :title="sidebarCollapsed ? 'Produk & Inventory' : ''">
                    <x-icon class="w-5 text-base text-center fa-solid fa-boxes-stacked" />
                    <span x-show="!sidebarCollapsed"
                        class="truncate">{{ auth()->user()->tenant?->catalogLabel() ?? 'Produk & layanan' }}</span>
                </a>

                <a href="{{ route('categories.index') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('categories.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                    :title="sidebarCollapsed ? 'Kategori Produk' : ''">
                    <x-icon class="w-5 text-base text-center fa-solid fa-layer-group" />
                    <span x-show="!sidebarCollapsed" class="truncate">Kategori Produk</span>
                </a>

                @if (auth()->user()->tenant?->hasBusinessModule('food'))
                    <a href="{{ route('materials.index') }}"
                        class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('materials.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                        :title="sidebarCollapsed ? 'Bahan Baku' : ''">
                        <x-icon class="w-5 text-base text-center fa-solid fa-cubes" />
                        <span x-show="!sidebarCollapsed" class="truncate">Bahan Baku</span>
                    </a>
                @endif

                <a href="{{ route('suppliers.index') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('suppliers.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                    :title="sidebarCollapsed ? 'Supplier' : ''">
                    <x-icon class="w-5 text-base text-center fa-solid fa-truck-field" />
                    <span x-show="!sidebarCollapsed" class="truncate">Supplier</span>
                </a>

                <a href="{{ route('purchases.index') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('purchases.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}">
                    <x-icon class="w-5 text-base text-center fa-solid fa-cart-flatbed" />
                    <span x-show="!sidebarCollapsed" class="truncate">Pembelian & Penerimaan</span>
                </a>
                <a href="{{ route('stocktakes.index') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('stocktakes.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}">
                    <x-icon class="w-5 text-base text-center fa-solid fa-clipboard-check" />
                    <span x-show="!sidebarCollapsed" class="truncate">Stock Opname</span>
                </a>
            </div>
        </div>

        <!-- Group: Laporan & AI -->
        <div>
            <p x-show="!sidebarCollapsed"
                class="px-3 text-[10px] font-bold text-ink-400 uppercase tracking-widest mb-2">
                Analitik & AI
            </p>
            <div class="space-y-1">
                <a href="{{ route('reports.index') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('reports.index') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                    :title="sidebarCollapsed ? 'Laporan Penjualan' : ''">
                    <x-icon class="w-5 text-base text-center fa-solid fa-chart-line" />
                    <span x-show="!sidebarCollapsed" class="truncate">Laporan Penjualan</span>
                </a>

                @can('feature-ai-analytics')
                    <a href="{{ route('reports.ai') }}"
                        class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('reports.ai') ? 'bg-accent-100 text-accent-700 font-bold' : 'text-ink-700 hover:bg-accent-100/50 hover:text-accent-700' }}"
                        :title="sidebarCollapsed ? 'Tanya GrowPOS AI' : ''">
                        <x-icon class="w-5 text-base text-center fa-solid fa-wand-magic-sparkles text-accent-500" />
                        <span x-show="!sidebarCollapsed" class="truncate">Tanya GrowPOS AI</span>
                    </a>
                @else
                    <a href="{{ auth()->user()->role === 'admin' ? route('billing.index') : '#' }}"
                        class="flex items-center justify-between px-3 text-xs font-semibold transition-colors rounded-md h-11 text-ink-400 hover:bg-surface-100 opacity-60"
                        :title="sidebarCollapsed ? 'Tanya GrowPOS AI (Upgrade Scale)' : ''">
                        <div class="flex items-center gap-3 truncate">
                            <x-icon class="w-5 text-base text-center text-purple-400 fa-solid fa-wand-magic-sparkles" />
                            <span x-show="!sidebarCollapsed" class="truncate">Tanya GrowPOS AI</span>
                        </div>
                        <span x-show="!sidebarCollapsed"
                            class="px-1.5 py-0.5 text-[9px] font-bold text-purple-800 bg-purple-100 rounded uppercase">SCALE</span>
                    </a>
                @endcan

                @can('manage-finance')
                    <a href="{{ route('finance.index') }}"
                        class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('finance.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                        :title="sidebarCollapsed ? 'Keuangan Toko' : ''">
                        <x-icon class="w-5 text-base text-center fa-solid fa-wallet" />
                        <span x-show="!sidebarCollapsed" class="truncate">Keuangan Toko</span>
                    </a>
                @endcan
            </div>
        </div>
        <!-- Group: Settings & Help -->
        <div>
            <p x-show="!sidebarCollapsed"
                class="px-3 text-[10px] font-bold text-ink-400 uppercase tracking-widest mb-2">
                Pengaturan & Bantuan
            </p>
            <div class="space-y-1">
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('getting-started') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('getting-started') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                    :title="sidebarCollapsed ? 'Mulai Berjualan' : ''">
                    <x-icon class="w-5 text-base text-center fa-solid fa-rocket" />
                    <span x-show="!sidebarCollapsed" class="truncate">Mulai berjualan</span>
                </a>
                @endif

                <a href="{{ route('help') }}"
                    class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('help') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                    :title="sidebarCollapsed ? 'Panduan Singkat' : ''">
                    <x-icon class="w-5 text-base text-center fa-solid fa-circle-question" />
                    <span x-show="!sidebarCollapsed" class="truncate">Panduan singkat</span>
                </a>

                @if (auth()->user()->tenant?->hasBusinessModule('food'))
                    <a href="{{ route('kitchen.index') }}"
                        class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('kitchen.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                        :title="sidebarCollapsed ? 'Antrean Dapur' : ''">
                        <x-icon class="w-5 text-base text-center fa-solid fa-utensils" />
                        <span x-show="!sidebarCollapsed" class="truncate">Antrean dapur</span>
                    </a>
                @endif

                @if (auth()->user()->tenant?->hasBusinessModule('services'))
                    <a href="{{ route('services.index') }}"
                        class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('services.*') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                        :title="sidebarCollapsed ? 'Pengerjaan Jasa' : ''">
                        <x-icon class="w-5 text-base text-center fa-solid fa-screwdriver-wrench" />
                        <span x-show="!sidebarCollapsed" class="truncate">Pengerjaan jasa</span>
                    </a>
                @endif

                @if (in_array(auth()->user()->role, ['admin', 'manager'], true))
                    <a href="{{ route('menu.configure') }}"
                        class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('menu.configure') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                        :title="sidebarCollapsed ? (
                            {{ json_encode(auth()->user()->tenant?->hasBusinessModule('food') ? 'Varian, Tambahan & Resep' : 'Varian & Pilihan') }}
                        ) : ''">
                        <x-icon class="w-5 text-base text-center fa-solid fa-sliders" />
                        <span x-show="!sidebarCollapsed" class="truncate">
                            {{ auth()->user()->tenant?->hasBusinessModule('food') ? 'Varian, tambahan & resep' : 'Varian & pilihan' }}
                        </span>
                    </a>

                @endif

                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('business.edit') }}"
                        class="flex items-center gap-3 px-3 h-11 text-xs font-semibold rounded-md transition-colors {{ request()->routeIs('business.edit') ? 'bg-primary-50 text-primary-600' : 'text-ink-700 hover:bg-surface-100 hover:text-ink-900' }}"
                        :title="sidebarCollapsed ? 'Pengaturan Usaha' : ''">
                        <x-icon class="w-5 text-base text-center fa-solid fa-gears" />
                        <span x-show="!sidebarCollapsed" class="truncate">Pengaturan usaha</span>
                    </a>
                @endif
            </div>
        </div>


    </nav>

    <!-- Sidebar Footer / Store Status Badge -->
    <div class="p-3 border-t border-border-200 bg-surface-100/50 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-2.5 h-2.5 rounded-full bg-semantic-success animate-pulse shrink-0"></div>
            <div x-show="!sidebarCollapsed" class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold text-ink-900 truncate">Sistem Online</p>
                <p class="text-[10px] text-ink-700 truncate">Terhubung ke Cloud</p>
            </div>
        </div>
    </div>
</aside>
