@props(['variant' => 'text'])

@php
    $variantClass = match ($variant) {
        'circle' => 'h-8 w-8 rounded-full',
        'block' => 'min-h-16 w-full',
        default => 'h-3 w-24',
    };
@endphp

<span {{ $attributes->merge(['class' => 'growpos-skeleton ' . $variantClass, 'aria-hidden' => 'true']) }}></span>
