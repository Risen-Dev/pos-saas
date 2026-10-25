<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full motion-safe:scroll-smooth [&_*]:[-webkit-tap-highlight-color:transparent]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <title>GrowPOS - @yield('title', 'SaaS POS UMKM')</title>

    {{-- <title>{{ config('app.name', 'POS SaaS') }}</title> --}}

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome (Optional, pastikan memaintain lisensi jika perlu) -->
</head>

{{-- Pastikan class h-full diterapkan pada html dan body agar min-h-screen di child bekerja sempurna --}}

<body class="h-full font-sans antialiased text-gray-900">

    {{-- Slot konten akan merender grid full screen dari login.blade.php --}}
    {{ $slot }}

</body>

</html>
