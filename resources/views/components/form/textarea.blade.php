@props(['name', 'label', 'rows' => 6])

<div class="flex flex-col gap-1.5">
    <label for="{{ $name }}" class="text-sm font-semibold">{{ $label }}</label>
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        {{ $attributes->class([
            'rounded-xs border bg-white px-3.5 py-3 text-base leading-normal text-ink focus:outline-2 focus:outline-offset-1 focus:outline-forest',
            'border-line-strong' => ! $errors->has($name),
            'border-amber-deep' => $errors->has($name),
        ]) }}
    >{{ old($name) }}</textarea>
    @error($name)
        <p id="{{ $name }}-error" class="text-sm font-semibold text-amber-deep">{{ $message }}</p>
    @enderror
</div>
