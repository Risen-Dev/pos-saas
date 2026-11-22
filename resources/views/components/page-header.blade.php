@props(['split' => false])

<header {{ $attributes->merge(['class' => 'flex flex-col items-start gap-4 p-5 mb-6 bg-surface-0 border border-border-200 rounded-lg shadow-sm ' . ($split ? 'sm:flex-row sm:items-center sm:justify-between' : '')]) }}>
    {{ $slot }}
</header>
