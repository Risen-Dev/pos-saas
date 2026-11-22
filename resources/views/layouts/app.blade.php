<!DOCTYPE html>
<html
    class="motion-safe:scroll-smooth [&_*]:[-webkit-tap-highlight-color:transparent] motion-reduce:[&_*]:!scroll-auto motion-reduce:[&_*]:!transition-none motion-reduce:[&_*]:!animate-none motion-reduce:[&_*::before]:!animate-none motion-reduce:[&_*::after]:!animate-none"
    lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <title>GrowPOS - @yield('title', 'SaaS POS UMKM')</title>
    <!-- Google Fonts Google Jakarta Sans, Inter, IBM Plex Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@600&family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased font-body text-ink-900 bg-surface-100 selection:bg-primary-100 selection:text-primary-700">

    <!-- Offline Mode Banner -->
    <div x-data="{ isOffline: !navigator.onLine }" x-init="window.addEventListener('online', () => isOffline = false);
    window.addEventListener('offline', () => isOffline = true);" x-show="isOffline" x-cloak
        class="[&[x-cloak]]:!hidden fixed top-0 inset-x-0 z-[110] bg-accent-500 text-white text-xs font-semibold py-2 px-4 text-center flex items-center justify-center gap-2 shadow-sm">
        <x-icon class="fa-solid fa-wifi-slash" />
        <span>Koneksi terputus. Buka layar kasir untuk melihat draft dan antrean transaksi di perangkat ini.</span>
    </div>

    <div id="app-shell" class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('growpos:sidebar-collapsed') === '1' }" x-init="$watch('sidebarCollapsed', value => localStorage.setItem('growpos:sidebar-collapsed', value ? '1' : '0'))" @keydown.escape.window="sidebarOpen = false">

        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/30 lg:hidden" aria-hidden="true"></div>
        @if (auth()->user()->role === 'kasir')
            @include('layouts.cashier-sidebar')
        @else
            @include('layouts.sidebar')
        @endif

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            @if (auth()->user()->role !== 'kasir')
                @include('layouts.navbar')
            @else
                <header class="flex items-center h-16 gap-3 px-4 border-b bg-surface-0 border-border-200 shrink-0">
                    <button type="button" aria-label="Buka navigasi kasir" aria-controls="cashier-navigation"
                        :aria-expanded="sidebarOpen.toString()" @click="sidebarOpen = true"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-md text-ink-700 hover:bg-surface-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600 lg:hidden">
                        <x-icon class="fa-solid fa-bars" />
                    </button>
                    <div class="min-w-0">
                        <p data-shell-page-title class="text-sm font-bold truncate text-ink-900">@yield('title', 'Operasional')</p>
                        <p class="text-xs text-ink-700">{{ auth()->user()->tenant?->name ?? 'GrowPOS' }} · Mode Kasir</p>
                    </div>
                    <div class="ml-auto">
                        @include('layouts.user-menu')
                    </div>
                </header>
            @endif

            <!-- Main Content Area -->
            <main id="main-content"
                class="flex-1 overflow-y-auto scroll-smooth focus:outline-none bg-surface-100 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <turbo-frame id="app-content" data-turbo-action="advance" class="block min-h-full">
                    <div data-page-context data-route-name="{{ request()->route()?->getName() }}"
                        data-page-title="GrowPOS - @yield('title', 'SaaS POS UMKM')" class="min-h-full">

                    @if ($errors->any())
                        <div role="alert" class="p-4 m-4 border border-red-200 rounded-lg bg-red-50">
                            <p class="font-semibold">Periksa kembali isian berikut.</p>
                            <ul class="list-disc list-inside">
                                @foreach ($errors->getMessages() as $field => $messages)
                                    <li id="error-{{ str_replace('.', '-', $field) }}"
                                        data-field-error="{{ $field }}">{{ implode(' ', $messages) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <x-toast-stack />

                    {{ $slot ?? ($slot ?? '') }}
                    @yield('content')
                    </div>
                </turbo-frame>
            </main>
        </div>
    </div>
</body>

</html>
