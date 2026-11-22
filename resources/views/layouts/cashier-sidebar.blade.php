<aside id="cashier-navigation" x-cloak
    :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
    class="fixed inset-y-0 left-0 z-50 flex flex-col w-[248px] h-screen transition-transform duration-200 border-r bg-surface-0 border-border-200 -translate-x-full lg:translate-x-0 lg:static shrink-0">
    <div class="flex items-center gap-3 h-16 px-4 border-b border-border-200 shrink-0">
        <span class="flex items-center justify-center w-10 h-10 text-base font-bold text-white rounded-md bg-primary-600 font-heading shrink-0" aria-hidden="true">GP</span>
        <div class="min-w-0">
            <p class="text-sm font-bold leading-tight truncate font-heading text-ink-900">{{ auth()->user()->tenant?->name ?? 'GrowPOS' }}</p>
            <p class="mt-0.5 text-[10px] font-semibold text-primary-700">Workspace kasir</p>
        </div>
        <button type="button" @click="sidebarOpen = false" class="p-2 ml-auto rounded-md text-ink-700 hover:bg-surface-100 lg:hidden"
            aria-label="Tutup navigasi kasir">
            <x-icon class="fa-solid fa-xmark" />
        </button>
    </div>

    <nav aria-label="Navigasi kasir" class="flex-1 px-3 py-4 overflow-y-auto">
        <ul class="space-y-1">
            <li>
                <a href="{{ route('pos.index') }}" class="flex items-center gap-3 h-11 px-3 text-sm font-semibold rounded-md {{ request()->routeIs('pos.index') && !request()->query('panel') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}"
                    @if (request()->routeIs('pos.index') && !request()->query('panel')) aria-current="page" @endif>
                    <x-icon class="w-5 text-center fa-solid fa-cash-register" />
                    <span>Jual</span>
                </a>
            </li>
            <li>
                <a href="{{ route('pos.index', ['panel' => 'history']) }}" class="flex items-center gap-3 h-11 px-3 text-sm font-semibold rounded-md {{ request()->query('panel') === 'history' ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}"
                    @if (request()->query('panel') === 'history') aria-current="page" @endif>
                    <x-icon class="w-5 text-center fa-solid fa-clock-rotate-left" />
                    <span>Riwayat</span>
                </a>
            </li>
            <li>
                <a href="{{ route('pos.index', ['panel' => 'shift']) }}" class="flex items-center gap-3 h-11 px-3 text-sm font-semibold rounded-md {{ request()->query('panel') === 'shift' ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}"
                    @if (request()->query('panel') === 'shift') aria-current="page" @endif>
                    <x-icon class="w-5 text-center fa-solid fa-clock" />
                    <span>Shift</span>
                </a>
            </li>
            @if (auth()->user()->tenant?->hasBusinessModule('food'))
                <li>
                    <a href="{{ route('kitchen.index') }}" class="flex items-center gap-3 h-11 px-3 text-sm font-semibold rounded-md {{ request()->routeIs('kitchen.*') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}"
                        @if (request()->routeIs('kitchen.*')) aria-current="page" @endif>
                        <x-icon class="w-5 text-center fa-solid fa-utensils" />
                        <span>Dapur</span>
                    </a>
                </li>
            @endif
            @if (auth()->user()->tenant?->hasBusinessModule('services'))
                <li>
                    <a href="{{ route('services.index') }}" class="flex items-center gap-3 h-11 px-3 text-sm font-semibold rounded-md {{ request()->routeIs('services.*') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}"
                        @if (request()->routeIs('services.*')) aria-current="page" @endif>
                        <x-icon class="w-5 text-center fa-solid fa-screwdriver-wrench" />
                        <span>Pengerjaan jasa</span>
                    </a>
                </li>
            @endif
        </ul>
    </nav>

</aside>
