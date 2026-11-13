<footer class="relative overflow-hidden bg-forest-deep text-mist">
    <svg viewBox="0 0 600 400" preserveAspectRatio="xMaxYMax slice" aria-hidden="true" class="pointer-events-none absolute right-0 bottom-0 h-[420px] w-[640px] max-w-full">
        <g transform="translate(520 360)" fill="none" stroke="#F2F3EC" stroke-opacity="0.08" stroke-width="1">
            @foreach ([[0.5, 0], [1, 8], [1.5, 16], [2, 24], [2.5, 32], [3, 40], [3.5, 48]] as [$scale, $rotate])
                <path transform="scale({{ $scale }}) rotate({{ $rotate }})" vector-effect="non-scaling-stroke" d="M0,-100C60,-105,110,-60,105,0C100,55,70,95,10,100C-50,105,-105,70,-100,10C-95,-50,-60,-95,0,-100Z"></path>
            @endforeach
        </g>
    </svg>
    <div class="relative mx-auto flex max-w-7xl flex-col gap-14 px-4 pt-18 pb-8 sm:px-8">
        <div class="grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-5">
            <div class="flex max-w-md flex-col gap-4 sm:col-span-2">
                <span class="text-[28px] font-extrabold tracking-tight text-paper">RUGESAA</span>
                <p class="text-[15px] leading-relaxed text-mist-dim">Rajshahi University Geography &amp; Environmental Studies Alumni Association, Department of Geography &amp; Environmental Studies, University of Rajshahi.</p>
            </div>
            <nav aria-label="Association" class="flex flex-col gap-2.5 text-[15px]">
                <span class="font-mono text-xs tracking-widest text-amber uppercase">Association</span>
                <a href="{{ route('about') }}" class="text-mist no-underline hover:text-amber">About us</a>
                <a href="{{ route('news.index') }}" class="text-mist no-underline hover:text-amber">Latest news</a>
                <a href="{{ route('contact') }}" class="text-mist no-underline hover:text-amber">Contact us</a>
            </nav>
            <nav aria-label="Engage" class="flex flex-col gap-2.5 text-[15px]">
                <span class="font-mono text-xs tracking-widest text-amber uppercase">Engage</span>
                <a href="{{ route('events.index') }}" class="text-mist no-underline hover:text-amber">Events</a>
                <a href="{{ route('gallery.index') }}" class="text-mist no-underline hover:text-amber">Gallery</a>
                <a href="{{ route('directory') }}" class="text-mist no-underline hover:text-amber">Alumni directory</a>
            </nav>
            <nav aria-label="Members" class="flex flex-col gap-2.5 text-[15px]">
                <span class="font-mono text-xs tracking-widest text-amber uppercase">Members</span>
                <a href="{{ route('register') }}" class="text-mist no-underline hover:text-amber">Register</a>
                <a href="{{ route('membership') }}" class="text-mist no-underline hover:text-amber">Membership</a>
                <a href="{{ route('portal') }}" class="text-mist no-underline hover:text-amber">Members portal</a>
            </nav>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-3 border-t border-forest-line pt-6 text-[13px] text-mist-dim">
            <span>© {{ now()->year }} Rajshahi University Geography &amp; Environmental Studies Alumni Association (RUGESAA)</span>
        </div>
    </div>
</footer>
