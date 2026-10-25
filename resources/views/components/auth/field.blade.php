@props(['name', 'label', 'type' => 'text', 'placeholder' => '', 'autocomplete' => null, 'value' => '', 'autofocus' => false])
<div>
    <label for="{{ $name }}" class="mb-2 block text-sm font-medium text-ink-900">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" placeholder="{{ $placeholder }}" @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif @if($autofocus) autofocus @endif required
        {{ $attributes->class(['block w-full rounded-xl border border-border-200 bg-surface-0 px-4 py-3 text-sm text-ink-900 outline-none transition placeholder:text-ink-400 focus:border-primary-600 focus:ring-4 focus:ring-primary-600/10', 'border-red-400' => $errors->has($name)]) }}>
    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
