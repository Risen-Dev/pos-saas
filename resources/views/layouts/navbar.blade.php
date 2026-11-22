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

        @include('layouts.user-menu')
    </div>
</header>
