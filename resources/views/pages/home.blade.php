@php
    $sectionTitle = 'text-[clamp(34px,4vw,52px)] leading-[1.02] font-extrabold tracking-tight';
    ['hero' => $hero, 'engage' => $engage, 'benefits' => $benefits, 'events' => $eventsCopy, 'news' => $newsCopy, 'join' => $join, 'contact' => $contactCopy] = $content;
@endphp

<x-layouts.site active="home">
    <section aria-labelledby="hero-title" class="relative flex min-h-[660px] items-end overflow-hidden bg-forest text-paper">
        <img src="{{ App\Enums\SitePage::imageUrl($hero['image'], 'images/hero.jpg') }}" alt=""" class="absolute inset-0 size-full object-cover object-[center_58%]">
        <div aria-hidden="true" class="absolute inset-0 bg-[linear-gradient(90deg,rgba(16,41,31,0.9)_0%,rgba(16,41,31,0.72)_42%,rgba(16,41,31,0.12)_78%)]"></div>
        <x-site.contours viewBox="0 0 900 640" aspect="xMinYMax slice" origin="120 600" :rings="[[0.6, 0], [1.1, 8], [1.6, 16], [2.1, 24], [2.6, 32], [3.1, 40], [3.6, 48]]" />
        <div class="relative mx-auto w-full max-w-7xl px-4 pt-34 pb-24 sm:px-8">
            <div class="flex max-w-2xl flex-col gap-6">
                @if (filled($hero['eyebrow']))
                    <x-site.eyebrow tone="text-amber">{{ $hero['eyebrow'] }}</x-site.eyebrow>
                @endif
                <h1 id="hero-title" class="text-[clamp(52px,7vw,104px)] leading-[0.95] font-extrabold tracking-tighter text-balance">{{ $hero['title'] }}</h1>
                <p class="text-xl leading-relaxed text-pretty text-[#E6EDE7]">{{ $hero['intro'] }}</p>
                <div class="flex flex-wrap gap-3 pt-1">
                    <a href="{{ route('register') }}" class="flex min-h-13 items-center gap-2.5 rounded-xs bg-amber px-6.5 text-[17px] font-bold text-ink no-underline hover:text-ink">
                        Join the network
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                    </a>
                    <a href="{{ route('directory') }}" class="flex min-h-13 items-center rounded-xs border-[1.5px] border-mist px-6.5 text-[17px] font-semibold text-paper no-underline hover:text-amber">Find a classmate</a>
                </div>
            </div>
        </div>
    </section>

    <section aria-labelledby="engage-title" class="bg-[#ECEEE6]">
        <div class="mx-auto flex max-w-7xl flex-col gap-12 px-4 pt-22 pb-24 sm:px-8">
            <div class="flex flex-col items-center gap-3 text-center">
                <x-site.eyebrow>{{ $engage['eyebrow'] }}</x-site.eyebrow>
                <h2 id="engage-title" class="text-[clamp(32px,3.6vw,46px)] leading-[1.05] font-extrabold tracking-tight">{{ $engage['title'] }}</h2>
            </div>
            @php
                $wayLinks = [
                    ['href' => route('directory'),
                        'icon' => '<circle cx="20" cy="20" r="13"></circle><path d="M29.5 29.5L42 42"></path><circle cx="20" cy="16.5" r="4"></circle><path d="M12.5 27a8.5 8.5 0 0 1 15 0"></path>'],
                    ['href' => route('events.index'),
                        'icon' => '<rect x="5" y="9" width="34" height="31" rx="3"></rect><path d="M5 18h34M14 5v8M30 5v8"></path><path d="M11 24h4M19 24h4M27 24h4M11 31h4M19 31h4"></path><circle cx="36" cy="36" r="7" fill="#FFFFFF"></circle><path d="M32.8 36l2.2 2.2 4-4.2"></path>'],
                    ['href' => route('register'),
                        'icon' => '<rect x="6" y="10" width="36" height="28" rx="3"></rect><circle cx="17" cy="22" r="5"></circle><path d="M10 33a7.5 7.5 0 0 1 14 0"></path><path d="M28 19h9M28 25h9M28 31h6"></path>'],
                ];
                $ways = collect(array_values($engage['items']))->take(count($wayLinks))->map(fn (array $item, int $index): array => $item + $wayLinks[$index]);
            @endphp
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach ($ways as $way)
                    <div class="flex flex-col items-center gap-5 border-b-4 border-forest bg-white px-7 pt-12 pb-11 text-center">
                        <svg width="68" height="68" viewBox="0 0 48 48" fill="none" stroke="#1D4337" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $way['icon'] !!}</svg>
                        <h3 class="mt-1 text-[23px] font-bold text-forest">{{ $way['title'] }}</h3>
                        <p class="text-base leading-normal text-body">{{ $way['body'] }}</p>
                        <a href="{{ $way['href'] }}" class="mt-auto flex min-h-13 items-center justify-center bg-forest px-6.5 text-sm font-bold tracking-[0.08em] text-white uppercase no-underline hover:bg-ink hover:text-white">{{ $way['cta'] }}</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section aria-labelledby="benefits-title" class="mx-auto flex max-w-7xl flex-col gap-12 px-4 py-26 sm:px-8">
        <div class="flex flex-col items-center gap-4 text-center">
            <x-site.eyebrow>{{ $benefits['eyebrow'] }}</x-site.eyebrow>
            <h2 id="benefits-title" class="{{ $sectionTitle }}">{{ $benefits['title'] }}</h2>
            <p class="max-w-xl text-[17px] leading-relaxed text-body">{{ $benefits['intro'] }}</p>
        </div>
        <div class="grid grid-cols-1 items-stretch gap-6 lg:grid-cols-2">
            <form method="GET" action="{{ route('directory') }}" aria-labelledby="dir-title" class="flex flex-col gap-3.5 rounded-sm bg-paper-dim p-7">
                <h3 id="dir-title" class="text-[22px] font-bold">{{ $benefits['directory_title'] }}</h3>
                <p class="text-[15px] leading-normal text-body">{{ $benefits['directory_body'] }}</p>
                <label for="home-q" class="text-sm font-semibold">Name or keyword</label>
                <input id="home-q" name="search" type="search" placeholder="e.g. hydrology, Rahman, World Bank" class="min-h-12 rounded-xs border border-line-strong bg-white px-3.5 text-base">
                <button type="submit" class="mt-auto flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-xs bg-forest text-base font-bold text-paper hover:bg-ink">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path></svg>
                    Search alumni
                </button>
            </form>
            <div class="flex flex-col gap-5 rounded-sm bg-ink p-7 text-paper">
                <h3 class="text-[22px] font-bold">{{ $benefits['steps_title'] }}</h3>
                <ol class="flex flex-col gap-4">
                    @foreach ($benefits['steps'] as $step)
                        <li class="grid grid-cols-[36px_minmax(0,1fr)] items-start gap-3.5">
                            <span class="flex size-8 items-center justify-center rounded-full border-[1.5px] border-amber font-mono text-[13px] text-amber">{{ $loop->iteration }}</span>
                            <span class="text-base leading-normal text-mist">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
                <div class="mt-auto flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="flex min-h-12 items-center rounded-xs bg-amber px-5.5 text-base font-bold text-ink no-underline hover:text-ink">Register now</a>
                    <a href="{{ route('login') }}" class="flex min-h-12 items-center rounded-xs border-[1.5px] border-mist-dim px-5.5 text-base font-semibold text-paper no-underline hover:text-amber">Members login</a>
                </div>
            </div>
        </div>
    </section>

    <section aria-labelledby="reunion-title" class="bg-forest text-paper">
        <div class="grid grid-cols-1 lg:grid-cols-2">
            <img src="{{ $featuredEvent?->coverImageUrl() ?? App\Enums\SitePage::imageUrl($eventsCopy['image'], 'images/reunion.jpg') }}" alt="" class="block size-full min-h-[460px] object-cover object-[center_85%]">
            <div class="flex flex-col justify-center gap-5 px-6 py-18 sm:px-16 lg:pr-[clamp(32px,6vw,96px)]">
                @if ($featuredEvent)
                    <x-site.eyebrow tone="text-amber">Next {{ strtolower($featuredEvent->type->getLabel()) }} · {{ $featuredEvent->starts_at->format('D j M Y') }}</x-site.eyebrow>
                    <h2 id="reunion-title" class="text-[clamp(36px,4.4vw,60px)] leading-none font-extrabold tracking-tight">{{ $featuredEvent->title }}</h2>
                    <p class="max-w-lg text-lg leading-relaxed text-mist">{{ $featuredEvent->summary }} {{ $featuredEvent->venue }}.</p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('events.show', $featuredEvent) }}" class="flex min-h-13 items-center rounded-xs bg-amber px-6.5 text-[17px] font-bold text-ink no-underline hover:text-ink">RSVP now</a>
                        <a href="{{ route('events.index') }}" class="flex min-h-13 items-center rounded-xs border-[1.5px] border-mist-dim px-6.5 text-[17px] font-semibold text-paper no-underline hover:text-amber">All events</a>
                    </div>
                    @if ($moreEvents->isNotEmpty())
                        <div class="mt-3 flex flex-col">
                            <span class="pb-2.5 font-mono text-xs tracking-widest text-mist-dim uppercase">Also coming up</span>
                            @foreach ($moreEvents as $event)
                                <a href="{{ route('events.show', $event) }}" @class(['flex items-center gap-5 border-t border-[#3C5F53] py-3.5 text-paper no-underline hover:text-amber', 'border-b' => $loop->last])>
                                    <span class="flex-[0_0_64px] font-mono text-sm text-amber uppercase">{{ $event->starts_at->format('d M') }}</span>
                                    <span class="flex flex-col gap-0.5"><span class="text-[17px] font-semibold">{{ $event->title }}</span><span class="text-sm text-mist-dim">{{ $event->type->getLabel() }} · {{ $event->venue }}</span></span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @else
                    <x-site.eyebrow tone="text-amber">{{ $eventsCopy['eyebrow'] }}</x-site.eyebrow>
                    <h2 id="reunion-title" class="text-[clamp(36px,4.4vw,60px)] leading-none font-extrabold tracking-tight">{{ $eventsCopy['title'] }}</h2>
                    <p class="max-w-lg text-lg leading-relaxed text-mist">{{ $eventsCopy['body'] }}</p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('events.index') }}" class="flex min-h-13 items-center rounded-xs bg-amber px-6.5 text-[17px] font-bold text-ink no-underline hover:text-ink">See events</a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($latestPosts->isNotEmpty())
        <section aria-labelledby="news-title" class="mx-auto flex max-w-7xl flex-col gap-10 px-4 py-26 sm:px-8">
            <div class="flex flex-col items-center gap-3 text-center">
                <x-site.eyebrow>{{ $newsCopy['eyebrow'] }}</x-site.eyebrow>
                <h2 id="news-title" class="{{ $sectionTitle }}">{{ $newsCopy['title'] }}</h2>
                <p class="max-w-xl text-[17px] leading-relaxed text-body">{{ $newsCopy['intro'] }}</p>
            </div>
            <ul class="flex flex-col border-t-2 border-ink">
                @foreach ($latestPosts as $post)
                    <li class="flex flex-wrap items-start gap-x-10 gap-y-5 border-b border-line py-8">
                        <div class="flex flex-[0_0_96px] flex-col font-mono text-muted"><span class="text-[26px] font-semibold text-ink uppercase">{{ $post->published_at->format('M') }}</span><span class="text-sm">{{ $post->published_at->format('Y') }}</span></div>
                        <div class="flex min-w-0 flex-[999_1_420px] flex-col items-start gap-3">
                            <x-site.eyebrow class="text-xs!">{{ $post->category->getLabel() }}</x-site.eyebrow>
                            <h3 class="text-[28px] leading-tight font-bold tracking-tight text-balance"><a href="{{ route('news.show', $post) }}" class="text-ink no-underline hover:underline">{{ $post->title }}</a></h3>
                            <p class="text-base leading-relaxed text-muted">{{ $post->excerpt }}</p>
                            <a href="{{ route('news.show', $post) }}" class="mt-1 flex min-h-11 items-center rounded-xs border-[1.5px] border-forest px-4.5 text-[15px] font-semibold text-forest no-underline" aria-label="Read article: {{ $post->title }}">Read article</a>
                        </div>
                        @if ($post->coverImageUrl())
                            <img src="{{ $post->coverImageUrl() }}" alt="{{ $post->cover_image_alt }}" class="block aspect-[3/2] w-full max-w-[360px] flex-[1_1_260px] rounded-sm bg-[#CBD6CC] object-cover">
                        @endif
                    </li>
                @endforeach
            </ul>
            <div class="flex justify-end">
                <a href="{{ route('news.index') }}" class="flex min-h-12 items-center rounded-xs bg-forest px-6 text-base font-bold text-paper no-underline hover:bg-ink hover:text-paper">All stories</a>
            </div>
        </section>
    @endif

    <section aria-labelledby="join-title" class="mx-auto max-w-7xl px-4 pt-26 sm:px-8">
        <div class="grid grid-cols-1 items-center gap-10 rounded-sm bg-ink p-8 text-paper sm:p-12 lg:grid-cols-2">
            <div class="flex flex-col gap-4">
                <x-site.eyebrow tone="text-amber">{{ $join['eyebrow'] }}</x-site.eyebrow>
                <h2 id="join-title" class="text-[clamp(32px,3.6vw,48px)] leading-[1.04] font-extrabold tracking-tight">{{ $join['title'] }}</h2>
                <p class="max-w-lg text-[17px] leading-relaxed text-mist">{{ $join['body'] }}</p>
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="{{ route('register') }}" class="flex min-h-13 items-center rounded-xs bg-amber px-6.5 text-[17px] font-bold text-ink no-underline hover:text-ink">Register now</a>
                    <a href="{{ route('membership') }}" class="flex min-h-13 items-center rounded-xs border-[1.5px] border-mist-dim px-6.5 text-[17px] font-semibold text-paper no-underline hover:text-amber">Compare memberships</a>
                </div>
            </div>
            @if ($plans->isNotEmpty())
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($plans as $plan)
                        <div @class(['flex flex-col gap-2 rounded-sm p-6', 'border-2 border-amber' => $plan->isLifetime(), 'border border-[#3C5F53]' => ! $plan->isLifetime()])>
                            <x-site.eyebrow class="text-xs!" :tone="$plan->isLifetime() ? 'text-amber' : 'text-mist-dim'">{{ $plan->name }}</x-site.eyebrow>
                            <span class="text-[26px] font-extrabold">BDT {{ number_format($plan->price) }}<span class="text-[15px] font-medium text-mist-dim"> {{ $plan->isLifetime() ? 'once' : '/ year' }}</span></span>
                            @if ($plan->description)
                                <span class="text-[15px] leading-normal text-mist">{{ $plan->description }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section aria-labelledby="social-title" class="mx-auto flex max-w-7xl flex-col items-center gap-4 px-4 pt-24 pb-26 text-center sm:px-8">
        <x-site.eyebrow>{{ $contactCopy['eyebrow'] }}</x-site.eyebrow>
        <h2 id="social-title" class="text-[clamp(30px,3.4vw,44px)] leading-[1.05] font-extrabold tracking-tight">{{ $contactCopy['title'] }}</h2>
        <p class="max-w-lg text-[17px] leading-relaxed text-body">{{ $contactCopy['body'] }}</p>
        <a href="{{ route('contact') }}" class="mt-2 flex min-h-12 items-center rounded-xs bg-forest px-6 text-base font-bold text-paper no-underline hover:bg-ink hover:text-paper">Contact the association</a>
        <x-site.social-links class="justify-center pt-2" />
    </section>
</x-layouts.site>
