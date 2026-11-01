@props(['state' => 'passwordVisible', 'control'])
<button type="button" @click="{{ $state }} = !{{ $state }}" aria-controls="{{ $control }}"
    :aria-label="{{ $state }} ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
    :aria-pressed="{{ $state }}.toString()"
    class="absolute inset-y-0 right-0 inline-flex items-center justify-center transition-colors rounded-md w-11 text-ink-700 hover:text-primary-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
    <svg x-show="!{{ $state }}" aria-hidden="true" focusable="false" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
        <circle cx="12" cy="12" r="3" />
    </svg>
    <svg x-show="{{ $state }}" x-cloak aria-hidden="true" focusable="false" class="h-5 w-5 [&[x-cloak]]:!hidden"
        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
        stroke-linejoin="round">
        <path
            d="m3 3 18 18M10.6 5.1A12.4 12.4 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-3 4M6.2 6.2A21 21 0 0 0 2 12s3.5 7 10 7a12 12 0 0 0 5.8-1.8M10 10a3 3 0 0 0 4 4" />
    </svg>
</button>
