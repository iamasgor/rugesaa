@props(['name', 'checked' => false])

<label class="flex min-h-11 cursor-pointer items-start gap-3 py-2.5 text-[15px] leading-normal">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked ? '1' : '0') === '1') class="mt-0.5 size-5 flex-none accent-forest">
    <span>{{ $slot }}</span>
</label>
@error($name)
    <p class="text-sm font-semibold text-amber-deep">{{ $message }}</p>
@enderror
