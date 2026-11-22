<header
    class="z-30 flex items-center justify-between h-16 px-4 border-b md:px-6 lg:px-8 bg-surface-0 border-border-200 shrink-0">
    <!-- Left Section: Mobile Menu Trigger & Global Search -->
    <div class="flex items-center flex-1 gap-3">
        <!-- Mobile Sidebar Toggle -->
        <button aria-label="Buka navigasi" aria-controls="business-sidebar" :aria-expanded="sidebarOpen.toString()" @click="sidebarOpen = true"
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

    </div>

    <!-- Right Section: Quick Actions & Profile Menu -->
    <div class="flex items-center gap-3">

        <!-- User Profile Dropdown -->
        <div class="relative" x-data="{ userMenu: false }">
            <button aria-label="Menu pengguna" :aria-expanded="userMenu.toString()" @click="userMenu = !userMenu"
                class="flex items-center gap-2.5 p-1 rounded-md hover:bg-surface-100 transition border border-transparent hover:border-border-200">

                <div
                    class="flex items-center justify-center text-sm font-bold border rounded-full shadow-sm w-9 h-9 bg-primary-100 border-primary-500/20 text-primary-700 font-heading">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>

                <div class="flex-col hidden text-left xl:flex">
                    <span class="text-xs font-semibold leading-tight text-ink-900">{{ auth()->user()->name }}</span>
                    <span class="text-[10px] font-medium text-ink-700 leading-tight">
                        {{ auth()->user()->role === 'admin' ? 'Admin Toko' : auth()->user()->tenant->name ?? 'Kasir Store' }}
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
