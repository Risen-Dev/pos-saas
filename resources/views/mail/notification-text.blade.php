{{ $greeting ?? 'Halo!' }}

@foreach ($introLines ?? [] as $line)
{{ $line }}

@endforeach
@isset($actionText)
{{ $actionText }}: {{ $actionUrl }}
@endisset

@foreach ($outroLines ?? [] as $line)
{{ $line }}

@endforeach

{{ $salutation ?? 'Tim GrowPOS' }}
