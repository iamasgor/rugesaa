<x-layouts.site :title="$post->title" active="news">
    <article aria-labelledby="post-title">
        <header class="bg-forest text-paper">
            <div class="mx-auto flex max-w-7xl flex-col gap-5 px-4 pt-16 pb-18 sm:px-8">
                <nav aria-label="Breadcrumb" class="flex flex-wrap gap-2 font-mono text-[13px] tracking-wider text-mist-dim">
                    <a href="{{ route('home') }}" class="text-mist">Home</a><span aria-hidden="true">/</span>
                    <a href="{{ route('news.index') }}" class="text-mist">News</a><span aria-hidden="true">/</span>
                    <a href="{{ route('news.index', ['category' => $post->category->value]) }}" class="text-mist">{{ $post->category->getLabel() }}</a>
                </nav>
                @unless ($post->isPublished())
                    <span class="self-start rounded-xs bg-amber px-2.5 py-1 text-[13px] font-bold text-ink">{{ $post->published_at ? 'Scheduled for '.$post->published_at->format('j M Y, g:i A') : 'Draft' }} — only admins can see this</span>
                @endunless
                <h1 id="post-title" class="text-[clamp(38px,5vw,68px)] leading-[1.0] font-extrabold tracking-tight text-balance">{{ $post->title }}</h1>
                <p class="text-xl leading-relaxed text-mist">{{ $post->excerpt }}</p>
                <p class="font-mono text-[13px] text-mist-dim">
                    {{ $post->published_at?->format('j F Y') ?? 'Not published' }} · {{ $post->readingMinutes() }} min read
                </p>
            </div>
        </header>

        @if ($post->coverImageUrl())
            <figure class="mx-auto max-w-7xl px-4 pt-14 sm:px-8">
                <img src="{{ $post->coverImageUrl() }}" alt="{{ $post->cover_image_alt }}" class="aspect-[16/9] w-full rounded-sm bg-paper-dim object-cover">
            </figure>
        @endif

        <div class="prose-rugesaa mx-auto flex max-w-3xl flex-col gap-5 px-4 pt-10 text-lg leading-relaxed text-body sm:px-8">
            {!! Str::markdown($post->body, ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section aria-labelledby="related-title" class="mx-auto flex max-w-7xl flex-col gap-8 px-4 pt-24 sm:px-8">
            <h2 id="related-title" class="border-t-2 border-ink pt-7 text-[28px] font-extrabold">More {{ strtolower($post->category->getLabel()) }} stories</h2>
            <div class="grid grid-cols-1 gap-x-8 gap-y-12 md:grid-cols-3">
                @foreach ($related as $relatedPost)
                    <x-news.card :post="$relatedPost" />
                @endforeach
            </div>
        </section>
    @endif

    <div class="pb-28"></div>
</x-layouts.site>
