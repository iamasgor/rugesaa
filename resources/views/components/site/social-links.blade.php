@php($links = config('rugesaa.social'))

@if (count($links) > 0)
    <nav aria-label="Social media" {{ $attributes->class('flex flex-wrap gap-2.5') }}>
        @foreach ($links as $label => $url)
            <a href="{{ $url }}" rel="noopener" class="flex min-h-11 items-center rounded-full border-[1.5px] border-forest px-5 text-[15px] font-semibold text-forest no-underline hover:bg-forest hover:text-paper">{{ $label }}</a>
        @endforeach
    </nav>
@endif
