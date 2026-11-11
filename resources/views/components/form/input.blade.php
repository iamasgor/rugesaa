@props(['name', 'label', 'type' => 'text', 'hint' => null, 'value' => null])

<div class="flex flex-col gap-1.5">
    <label for="{{ $name }}" class="text-sm font-semibold">
        {{ $label }}
        @if ($hint)
            <span class="font-normal text-muted">{{ $hint }}</span>
        @endif
    </label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password' && $type !== 'file') value="{{ old($name, $value) }}" @endif
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        {{ $attributes->class([
            'min-h-12 rounded-xs border bg-white px-3.5 text-base text-ink focus:outline-2 focus:outline-offset-1 focus:outline-forest',
            'border-line-strong' => ! $errors->has($name),
            'border-amber-deep' => $errors->has($name),
            'border-dashed py-2.5 text-sm' => $type === 'file',
        ]) }}
    >
    @error($name)
        <p id="{{ $name }}-error" class="text-sm font-semibold text-amber-deep">{{ $message }}</p>
    @enderror
</div>
