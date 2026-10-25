@props(['href'])
<a href="{{ $href }}"
    {{ $attributes->class(['text-sm font-semibold text-[#506459] transition-colors hover:text-primary-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary-600']) }}>{{ $slot }}</a>
