@props(['title', 'breadcrumb', 'intro' => null])

<section aria-labelledby="page-title" class="relative overflow-hidden bg-forest text-paper">
    <x-site.contours viewBox="0 0 900 400" aspect="xMaxYMid slice" origin="780 200" :rings="[[0.4, 0], [0.8, 10], [1.2, 20], [1.6, 30], [2, 40], [2.4, 50], [2.8, 60]]" />
    <div class="relative mx-auto flex max-w-7xl flex-col gap-5 px-4 pt-16 pb-22 sm:px-8">
        <nav aria-label="Breadcrumb" class="flex flex-wrap gap-2 font-mono text-[13px] tracking-wider text-mist-dim">
            <a href="{{ route('home') }}" class="text-mist">Home</a><span aria-hidden="true">/</span><span>{{ $breadcrumb }}</span>
        </nav>
        <h1 id="page-title" class="max-w-4xl text-[clamp(44px,5.6vw,84px)] leading-[0.98] font-extrabold tracking-tight text-balance">{{ $title }}</h1>
        @if ($intro)
            <p class="max-w-2xl text-[19px] leading-relaxed text-mist">{{ $intro }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
