@props(['type'])

@php([$background, $foreground] = $type->tagColors())

<span class="rounded-xs px-2.5 py-1 text-[13px] font-semibold" style="background: {{ $background }}; color: {{ $foreground }}">{{ $type->getLabel() }}</span>
