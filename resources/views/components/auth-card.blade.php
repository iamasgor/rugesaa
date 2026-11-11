@props(['title', 'heading', 'intro' => null])

<x-layouts.site :title="$title">
    <div class="mx-auto flex max-w-xl flex-col gap-6 px-4 pt-14 pb-28 sm:px-8">
        <h1 class="text-[clamp(36px,4vw,52px)] leading-none font-extrabold tracking-tight">{{ $heading }}</h1>
        @if ($intro)
            <p class="text-lg leading-relaxed text-body">{{ $intro }}</p>
        @endif
        <div class="flex flex-col gap-6 rounded-sm border border-line bg-card p-6 sm:p-10">
            {{ $slot }}
        </div>
    </div>
</x-layouts.site>
