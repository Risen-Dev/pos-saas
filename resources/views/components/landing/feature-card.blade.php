@props(['title', 'desc', 'eyebrow' => '', 'image' => null, 'variant' => 'large'])
@php
    $dark = $variant === 'dark';
    $large = $variant === 'large';
@endphp
<article @class([
    'group relative flex h-full min-h-[350px] flex-col overflow-hidden rounded-[1.35rem] p-7 sm:p-9',
    'bg-[#163E2C] text-white' => $dark,
    'bg-white text-[#17372A]' => !$dark,
    'lg:min-h-[440px]' => $large,
])>
    <div class="relative z-10 max-w-md">
        <p @class([
            'mb-6 text-[11px] font-bold uppercase tracking-[.19em]',
            'text-[#A5D5B7]' => $dark,
            'text-primary-600' => !$dark,
        ])>{{ $eyebrow }}</p>
        <h3 class="max-w-sm text-3xl font-extrabold leading-[1.13] tracking-[-.05em] sm:text-4xl">{{ $title }}
        </h3>
        <p @class([
            'mt-4 max-w-sm text-sm leading-7',
            'text-white/75' => $dark,
            'text-[#687B70]' => !$dark,
        ])>{{ $desc }}</p>
    </div>
    @if ($image)
        <div class="relative mt-8 min-h-[400px] flex-1 overflow-hidden rounded-xl bg-[#E8F1E9] ring-1 ring-[#E2ECE3]">
            <img src="{{ asset('img/' . $image) }}" loading="lazy" alt="Pratinjau fitur {{ $title }}"
                class="absolute inset-0 h-full w-full object-cover object-top transition-transform duration-700 group-hover:scale-[1.03]"
                onerror="this.style.display='none'">
        </div>
    @else
        <div class="flex justify-end pt-10 mt-auto"><span @class([
            'flex h-16 w-16 items-center justify-center rounded-full border text-3xl',
            'border-white/25 text-white' => $dark,
            'border-primary-100 text-primary-600' => !$dark,
        ]) aria-hidden="true">↗</span></div>
    @endif
</article>
