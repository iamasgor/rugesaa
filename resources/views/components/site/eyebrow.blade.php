@props(['tone' => 'text-amber-deep'])

<span {{ $attributes->class(['font-mono text-[13px] tracking-[0.12em] uppercase', $tone]) }}>{{ $slot }}</span>
