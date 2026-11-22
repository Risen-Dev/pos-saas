<div class="relative" x-data="{ userMenu: false }"
    @keydown.escape.window="if (userMenu) { userMenu = false; $nextTick(() => $refs.trigger.focus()); }">
    <button type="button" x-ref="trigger" aria-label="Buka menu akun" aria-controls="account-menu"
        :aria-expanded="userMenu.toString()" @click="userMenu = !userMenu"
        class="inline-flex items-center justify-center w-11 h-11 text-ink-700 transition-colors border rounded-full border-border-200 bg-surface-0 hover:bg-surface-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
        <x-icon class="text-base fa-regular fa-user" />
    </button>

    <div id="account-menu" x-show="userMenu" @click.away="userMenu = false" x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        class="[&[x-cloak]]:!hidden absolute right-0 z-50 w-64 p-1.5 mt-2 border rounded-lg shadow-lg bg-surface-0 border-border-200">
        <div class="px-3 py-2 mb-1 border-b border-border-200">
            <p class="text-xs font-semibold truncate text-ink-900">{{ auth()->user()->name }}</p>
            <p class="text-[11px] text-ink-700 truncate">{{ auth()->user()->email }}</p>
        </div>

        <nav aria-label="Menu akun">
            <ul class="space-y-0.5">
                @if (auth()->user()->role === 'admin')
                    <li>
                        <a href="{{ route('workspace.settings') }}" data-turbo-frame="app-content" data-nav-patterns="workspace.settings,business.*,employees.*,settings.*,tenants.*,billing.*,activity-logs.*"
                            data-nav-active-class="bg-primary-50 text-primary-700" data-nav-idle-class="text-ink-700 hover:bg-surface-100"
                            @if (request()->routeIs('workspace.settings', 'business.*', 'employees.*', 'settings.*', 'tenants.*', 'billing.*', 'activity-logs.*')) aria-current="page" @endif
                            @click="userMenu = false"
                            class="flex items-center gap-2.5 min-h-11 px-3 text-xs font-medium rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600 {{ request()->routeIs('workspace.settings', 'business.*', 'employees.*', 'settings.*', 'tenants.*', 'billing.*', 'activity-logs.*') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
                            <x-icon class="w-4 text-center fa-solid fa-gears" />
                            <span>Pengaturan usaha</span>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('help') }}" data-turbo-frame="app-content" data-nav-patterns="help" data-nav-active-class="bg-primary-50 text-primary-700" data-nav-idle-class="text-ink-700 hover:bg-surface-100" @if (request()->routeIs('help')) aria-current="page" @endif
                        @click="userMenu = false"
                        class="flex items-center gap-2.5 min-h-11 px-3 text-xs font-medium rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600 {{ request()->routeIs('help') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
                        <x-icon class="w-4 text-center fa-regular fa-circle-question" />
                        <span>Bantuan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile.index') }}" data-turbo-frame="app-content" data-nav-patterns="profile.*" data-nav-active-class="bg-primary-50 text-primary-700" data-nav-idle-class="text-ink-700 hover:bg-surface-100" @if (request()->routeIs('profile.*')) aria-current="page" @endif
                        @click="userMenu = false"
                        class="flex items-center gap-2.5 min-h-11 px-3 text-xs font-medium rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600 {{ request()->routeIs('profile.*') ? 'bg-primary-50 text-primary-700' : 'text-ink-700 hover:bg-surface-100' }}">
                        <x-icon class="w-4 text-center fa-regular fa-user" />
                        <span>Akun</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="px-3 py-2 mt-1 border-t border-border-200" x-data="connectionStatus">
            <p class="flex items-center gap-2 text-xs font-semibold text-ink-900" role="status" aria-live="polite">
                <span class="w-2 h-2 rounded-full shrink-0"
                    :class="online ? 'bg-emerald-500' : 'bg-semantic-danger'"></span>
                <span x-text="label"></span>
            </p>
            <p class="mt-1 text-[11px] text-ink-700">Status koneksi perangkat</p>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-1 pt-1 border-t border-border-200">
            @csrf
            <button type="submit"
                class="flex items-center gap-2.5 w-full min-h-11 px-3 text-xs font-semibold text-semantic-danger rounded-md hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-red-600">
                <x-icon class="w-4 text-center fa-solid fa-right-from-bracket" />
                <span>Keluar</span>
            </button>
        </form>
    </div>
</div>
