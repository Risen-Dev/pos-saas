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

    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('growpos:sidebar-collapsed') === '1' }" x-init="$watch('sidebarCollapsed', value => localStorage.setItem('growpos:sidebar-collapsed', value ? '1' : '0'))">

        @if (auth()->user()->role !== 'kasir')
            <!-- Back-office navigation is never rendered for cashier accounts. -->
            @include('layouts.sidebar')
        @endif

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            @if (auth()->user()->role !== 'kasir')
                @include('layouts.navbar')
            @else
                <header
                    class="flex items-center justify-between h-16 gap-3 px-4 border-b bg-surface-0 border-border-200 shrink-0">
                    <div class="flex items-center min-w-0 gap-2">
                        <a href="{{ route('pos.index') }}"
                            class="inline-flex items-center justify-center rounded-md w-9 h-9 bg-primary-50 text-primary-600"
                            aria-label="Kembali ke kasir"><x-icon class="fa-solid fa-cash-register" /></a>
                        <div class="min-w-0">
                            <p class="text-sm font-bold truncate text-ink-900">
                                {{ auth()->user()->tenant?->name ?? 'GrowPOS' }}</p>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-primary-600">Mode Kasir
                            </p>
                        </div>
                    </div>
                    <nav class="flex items-center gap-1 sm:gap-2" aria-label="Navigasi kasir">
                        <a href="{{ route('pos.index') }}"
                            class="px-3 py-2 text-xs font-semibold rounded-md text-ink-700 hover:bg-surface-100">Kasir</a>
                        @if (auth()->user()->tenant?->hasBusinessModule('services'))
                            <a href="{{ route('services.index') }}"
                                class="hidden px-3 py-2 text-xs font-semibold rounded-md sm:inline-flex text-ink-700 hover:bg-surface-100">Pengerjaan
                                jasa</a>
                        @endif
                        @if (auth()->user()->tenant?->hasBusinessModule('food'))
                            <a href="{{ route('kitchen.index') }}"
                                class="hidden px-3 py-2 text-xs font-semibold rounded-md sm:inline-flex text-ink-700 hover:bg-surface-100">Dapur</a>
                        @endif
                        <a href="{{ route('profile.index') }}"
                            class="inline-flex items-center justify-center rounded-md w-9 h-9 text-ink-700 hover:bg-surface-100"
                            aria-label="Akun"><x-icon class="fa-solid fa-user" /></a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button
                                class="inline-flex items-center justify-center rounded-md w-9 h-9 text-semantic-danger hover:bg-red-50"
                                aria-label="Keluar"><x-icon class="fa-solid fa-right-from-bracket" /></button></form>
                    </nav>
                </header>
            @endif

            <!-- Main Content Area -->
            <main id="main-content"
                class="flex-1 overflow-y-auto scroll-smooth focus:outline-none bg-surface-100 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <div class="h-full">

                    <!-- Floating Toast Notification System -->
                    <div class="fixed top-20 right-5 z-[100] flex flex-col gap-3 w-full max-w-sm pointer-events-none">

                        {{-- Pesan Sukses --}}
                        @if (session('success'))
                            <!-- 2. PERBAIKAN: Ditambahkan x-cloak -->
                            <div x-data="{ show: true }" x-show="show" x-cloak x-init="setTimeout(() => show = false, 4000)"
                                x-transition:enter="transform transition ease-out duration-300"
                                x-transition:enter-start="translate-x-full opacity-0"
                                x-transition:enter-end="translate-x-0 opacity-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="translate-x-0 opacity-100"
                                x-transition:leave-end="translate-x-full opacity-0"
                                class="[&[x-cloak]]:!hidden pointer-events-auto flex items-center gap-3 px-4 py-3.5 bg-surface-0 border-l-4 border-primary-600 rounded-lg shadow-md text-ink-900 text-sm font-medium">
                                <x-icon class="text-lg fa-solid fa-circle-check text-primary-600" />
                                <div class="flex-1">{{ session('success') }}</div>
                                <button @click="show = false" class="text-ink-400 hover:text-ink-700">
                                    <x-icon class="fa-solid fa-xmark" />
                                </button>
                            </div>
                        @endif

                        {{-- Pesan Error Session --}}
                        @if (session('error'))
                            <!-- 2. PERBAIKAN: Ditambahkan x-cloak -->
                            <div x-data="{ show: true }" x-show="show" x-cloak x-init="setTimeout(() => show = false, 5000)"
                                x-transition:enter="transform transition ease-out duration-300"
                                x-transition:enter-start="translate-x-full opacity-0"
                                x-transition:enter-end="translate-x-0 opacity-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="translate-x-0 opacity-100"
                                x-transition:leave-end="translate-x-full opacity-0"
                                class="[&[x-cloak]]:!hidden pointer-events-auto flex items-center gap-3 px-4 py-3.5 bg-surface-0 border-l-4 border-semantic-danger rounded-lg shadow-md text-ink-900 text-sm font-medium">
                                <x-icon class="text-lg fa-solid fa-circle-exclamation text-semantic-danger" />
                                <div class="flex-1">{{ session('error') }}</div>
                                <button @click="show = false" class="text-ink-400 hover:text-ink-700">
                                    <x-icon class="fa-solid fa-xmark" />
                                </button>
                            </div>
                        @endif

                        {{-- Pesan Error Validasi --}}
                        @if ($errors->any())
                            <!-- 2. PERBAIKAN: Ditambahkan x-cloak -->
                            <div x-data="{ show: true }" x-show="show" x-cloak x-init="setTimeout(() => show = false, 6000)"
                                x-transition:enter="transform transition ease-out duration-300"
                                x-transition:enter-start="translate-x-full opacity-0"
                                x-transition:enter-end="translate-x-0 opacity-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="translate-x-0 opacity-100"
                                x-transition:leave-end="translate-x-full opacity-0"
                                class="[&[x-cloak]]:!hidden p-4 text-sm border-l-4 rounded-lg shadow-md pointer-events-auto bg-surface-0 border-semantic-danger text-ink-900">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-semibold text-semantic-danger">Ada kendala input:</span>
                                    <button @click="show = false" class="text-ink-400 hover:text-ink-700">
                                        <x-icon class="fa-solid fa-xmark" />
                                    </button>
                                </div>
                                <ul class="list-disc list-inside space-y-0.5 text-xs text-ink-700">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{ $slot ?? ($slot ?? '') }}
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>

</html>
