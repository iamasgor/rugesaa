@props(['name', 'label', 'options' => [], 'value' => null])

{{-- $options: array<string, string> of value => label --}}
<div class="flex flex-col gap-1.5">
    <label for="{{ $name }}" class="text-sm font-semibold">{{ $label }}</label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        {{ $attributes->class([
            'min-h-12 rounded-xs border bg-white px-2.5 text-base text-ink focus:outline-2 focus:outline-offset-1 focus:outline-forest',
            'border-line-strong' => ! $errors->has($name),
            'border-amber-deep' => $errors->has($name),
        ]) }}
    >
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($name)
        <p id="{{ $name }}-error" class="text-sm font-semibold text-amber-deep">{{ $message }}</p>
    @enderror
</div>
