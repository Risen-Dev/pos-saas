@props([
    'planId' => null,
    'title',
    'desc',
    'price',
    'period',
    'cta',
    'features' => [],
    'capacity' => null,
    'popular' => false,
    'actionUrl' => null,
])
<article @class([
    'relative flex h-full flex-col rounded-[1.25rem] border p-7 sm:p-8',
    'border-primary-600 bg-[#153F2C] text-white shadow-xl' => $popular,
    'border-[#DFE8E1] bg-white text-[#17372A]' => !$popular,
])>
    @if ($popular)
        <span
            class="mb-6 inline-flex w-fit rounded-full bg-[#D8F1E0] px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest text-[#155B39]">Pilihan
            populer</span>
    @endif
    <h3 class="text-xl font-extrabold tracking-tight">{{ $title }}</h3>
    <p @class([
        'mt-2 min-h-12 text-sm leading-6',
        'text-white/70' => $popular,
        'text-[#6D7D72]' => !$popular,
    ])>{{ $desc }}</p>
    <div class="flex flex-wrap items-baseline gap-2 mt-7"><strong
            class="text-3xl font-extrabold tracking-[-.05em]">{{ $price }}</strong><span
            @class([
                'text-xs',
                'text-white/60' => $popular,
                'text-[#7A8B7E]' => !$popular,
            ])>/ {{ $period }}</span></div>
    @if ($capacity)
        <p @class([
            'mt-4 text-xs leading-6',
            'text-white/70' => $popular,
            'text-[#61766A]' => !$popular,
        ])>{{ $capacity }}</p>
    @endif
    <div @class([
        'my-7 h-px',
        'bg-white/20' => $popular,
        'bg-[#E5EDE7]' => !$popular,
    ])></div>
    <ul class="flex-1 space-y-3 mb-9">
        @foreach ($features as $feature => $included)
            @php
                $enabled = is_int($feature) ? true : (bool) $included;
                $label = is_int($feature) ? $included : $feature;
            @endphp
            <li @class([
                'flex items-start gap-3 text-sm leading-6',
                'opacity-45' => !$enabled,
            ])><span class="mt-0.5 font-bold"
                    aria-hidden="true">{{ $enabled ? '✓' : '–' }}</span><span>{{ $label }}</span></li>
        @endforeach
    </ul>
    @auth
        @if ($planId)
            <form action="{{ route('billing.subscribe') }}" method="POST">@csrf<input type="hidden" name="plan_id"
                    value="{{ $planId }}"><button type="submit" @class([
                        'flex min-h-12 w-full items-center justify-center rounded-lg px-4 text-sm font-bold transition',
                        'bg-white text-[#174C33] hover:bg-[#EAF4EC]' => $popular,
                        'bg-primary-600 text-white hover:bg-primary-900' => !$popular,
                    ])>{{ $cta }}
                    ↗</button></form>
        @else<a href="{{ route('billing.index') }}" @class([
            'flex min-h-12 items-center justify-center rounded-lg px-4 text-sm font-bold',
            'bg-white text-[#174C33]' => $popular,
            'bg-primary-600 text-white' => !$popular,
        ])>{{ $cta }} ↗</a>
        @endif
@else<a href="{{ route('register') }}" @class([
    'flex min-h-12 items-center justify-center rounded-lg px-4 text-sm font-bold transition',
    'bg-white text-[#174C33] hover:bg-[#EAF4EC]' => $popular,
    'bg-primary-600 text-white hover:bg-primary-900' => !$popular,
])>{{ $cta }} ↗</a>@endauth
</article>
