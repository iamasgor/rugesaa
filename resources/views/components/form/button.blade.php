<button {{ $attributes->merge(['type' => 'submit'])->class('inline-flex min-h-14 cursor-pointer items-center justify-center gap-2.5 rounded-xs bg-forest px-7 text-[17px] font-extrabold text-paper hover:bg-ink disabled:cursor-wait disabled:opacity-70') }}>
    {{ $slot }}
</button>
