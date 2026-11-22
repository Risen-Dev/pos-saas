@props(['split' => false, 'stacked' => false])

@php($bottomSpacing = $stacked ? '' : 'mb-4')

<header {{ $attributes->merge(['class' => 'flex flex-col items-start gap-3 p-4 ' . $bottomSpacing . ' bg-surface-0 border border-border-200 rounded-lg shadow-sm ' . ($split ? 'sm:flex-row sm:items-center sm:justify-between' : '')]) }}>
    {{ $slot }}
</header>
