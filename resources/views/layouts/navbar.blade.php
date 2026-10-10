<header
    class="z-30 flex items-center justify-between h-16 px-4 border-b md:px-6 lg:px-8 bg-surface-0 border-border-200 shrink-0">
    <!-- Left Section: Mobile Menu Trigger & Global Search -->
    <div class="flex items-center flex-1 gap-3">
        <!-- Mobile Sidebar Toggle -->
        <button @click="sidebarOpen = true"
            class="p-2 transition-colors rounded-md text-ink-700 hover:text-primary-600 lg:hidden hover:bg-surface-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
        </button>

        <!-- Desktop Collapse Toggle -->
        <button @click="sidebarCollapsed = !sidebarCollapsed"
            class="hidden p-2 transition-colors rounded-md lg:flex text-ink-700 hover:text-primary-600 hover:bg-surface-100"
            title="Toggle Sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8M4 18h16" />
            </svg>
        </button>

        @if(auth()->user()->role === 'admin')
            <a href="{{ route('getting-started') }}" class="hidden md:flex text-sm font-semibold text-primary-600">Mulai berjualan →</a>
        @endif
    </div>

    <!-- Right Section: Quick Actions & Profile Menu -->
    <div class="flex items-center gap-3">

        <!-- Indikator Badge Paket Langganan Aktif -->
        @php
            $activeSub = auth()->user()->tenant
                ? auth()
                    ->user()
                    ->tenant->subscriptions()
                    ->where('status', 'active')
                    ->where('end_date', '>=', now())
                    ->latest()
                    ->first()
                : null;
        @endphp

        @if(auth()->user()->role === 'admin')
        <a href="{{ route('billing.index') }}"
            class="items-center hidden px-2.5 py-1 text-[11px] font-bold transition-all rounded-full sm:inline-flex gap-1.5 border shadow-2xs hover:opacity-90"
            title="Kelola Paket Berlangganan Toko">
            @if ($activeSub && $activeSub->plan)
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-primary-800 font-heading">{{ $activeSub->plan->name }}</span>
            @else
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span class="text-rose-700 font-heading">Belum Berlangganan</span>
            @endif
        </a>
        @endif

        <!-- Quick POS Terminal Button (Emerald Accent Button) -->
        <a href="{{ route('pos.index') }}"
            class="items-center hidden h-10 gap-2 px-4 text-xs font-semibold text-white transition-all rounded-md shadow-sm sm:inline-flex bg-primary-600 hover:bg-primary-700">
            <x-icon class="fa-solid fa-cash-register" />
            <span>Buka Kasir</span>
        </a>

        <!-- Quick AI Assistant Button -->
        @can('feature-ai-analytics')
            <a href="{{ route('reports.ai') }}"
                class="relative flex items-center justify-center w-10 h-10 transition-all rounded-md text-accent-700 bg-accent-100 hover:bg-accent-500 hover:text-white"
                title="Tanya GrowPOS AI">
                <x-icon class="text-sm fa-solid fa-wand-magic-sparkles" />
                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-accent-500 rounded-full ring-2 ring-surface-0"></span>
            </a>
        @endcan

        <div class="h-6 w-[1px] bg-border-200 mx-1"></div>

        <!-- User Profile Dropdown -->
        <div class="relative" x-data="{ userMenu: false }">
            <button @click="userMenu = !userMenu"
                class="flex items-center gap-2.5 p-1 rounded-md hover:bg-surface-100 transition border border-transparent hover:border-border-200">

                <div
                    class="flex items-center justify-center text-sm font-bold border rounded-full shadow-sm w-9 h-9 bg-primary-100 border-primary-500/20 text-primary-700 font-heading">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>

                <div class="flex-col hidden text-left xl:flex">
                    <span class="text-xs font-semibold leading-tight text-ink-900">{{ auth()->user()->name }}</span>
                    <span class="text-[10px] font-medium text-ink-700 leading-tight">
                        {{ auth()->user()->role === 'admin' ? 'Superadmin' : auth()->user()->tenant->name ?? 'Kasir Store' }}
                    </span>
                </div>

                <x-icon class="fa-solid fa-chevron-down text-[10px] text-ink-400 ml-1" />
            </button>

            <!-- Dropdown Menu Box -->
            <div x-show="userMenu" @click.away="userMenu = false" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-1" x-cloak
                class="[&[x-cloak]]:!hidden absolute right-0 mt-2 w-56 bg-surface-0 rounded-lg shadow-lg border border-border-200 p-1.5 z-50">

                <div class="px-3 py-2 mb-1 border-b border-border-200">
                    <p class="text-xs font-semibold truncate text-ink-900">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-ink-700 truncate">{{ auth()->user()->email }}</p>
                </div>

                <a href="{{ route('profile.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-ink-700 rounded-md hover:bg-primary-50 hover:text-primary-600 transition">
                    <x-icon class="w-4 text-sm fa-regular fa-user" />
                    <span>Profil Saya</span>
                </a>

                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('billing.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-ink-700 rounded-md hover:bg-primary-50 hover:text-primary-600 transition"><x-icon class="w-4 text-sm fa-solid fa-credit-card" /><span>Langganan & Penagihan</span></a>
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-ink-700 rounded-md hover:bg-primary-50 hover:text-primary-600 transition"><x-icon class="w-4 text-sm fa-solid fa-sliders" /><span>Pengaturan Toko</span></a>
                @endif

                <div class="my-1 border-t border-border-200"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2.5 w-full px-3 py-2 text-xs font-semibold text-semantic-danger rounded-md hover:bg-red-50 transition">
                        <x-icon class="w-4 text-sm fa-solid fa-right-from-bracket" />
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
