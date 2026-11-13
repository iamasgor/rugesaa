@php
    use App\Enums\NewsCategory;
@endphp

<x-layouts.site title="Latest news" active="news">
    <x-site.page-hero
        title="Latest news"
        breadcrumb="News"
        intro="What alumni are doing and what's new in the department.">
        <nav aria-label="News categories" class="flex flex-wrap gap-2 pt-2">
            @foreach (['' => 'All'] + collect(NewsCategory::cases())->mapWithKeys(fn ($category) => [$category->value => $category->getLabel()])->all() as $value => $label)
                @php($isActive = ($activeCategory?->value ?? '') === $value)
                <a href="{{ route('news.index', array_filter(['category' => $value])) }}"
                   @if ($isActive) aria-current="page" @endif
                   @class([
                       'flex min-h-11 items-center rounded-full border px-4 text-[15px] no-underline',
                       'border-amber bg-amber font-semibold text-ink hover:text-ink' => $isActive,
                       'border-forest-line text-paper hover:border-amber hover:text-paper' => ! $isActive,
                   ])>{{ $label }}</a>
            @endforeach
        </nav>
    </x-site.page-hero>

    @if ($featured)
        <section aria-label="Featured story" class="mx-auto max-w-7xl px-4 pt-16 sm:px-8">
            <a href="{{ route('news.show', $featured) }}" class="group grid grid-cols-1 items-center gap-10 text-ink no-underline hover:text-ink lg:grid-cols-2">
                @if ($featured->coverImageUrl())
                    <img src="{{ $featured->coverImageUrl() }}" alt="{{ $featured->cover_image_alt }}" class="block aspect-[3/2] w-full rounded-sm bg-[#3C5F53] object-cover">
                @else
                    <div aria-hidden="true" class="aspect-[3/2] rounded-sm bg-[#CBD6CC] bg-[repeating-linear-gradient(135deg,transparent_0_12px,rgba(20,35,30,0.06)_12px_13px)]"></div>
                @endif
                <div class="flex flex-col gap-4.5">
                    <x-site.eyebrow>Featured · {{ $featured->category->getLabel() }} · {{ $featured->published_at->format('M Y') }}</x-site.eyebrow>
                    <h2 class="text-[clamp(34px,3.8vw,54px)] leading-[1.02] font-extrabold tracking-tight text-balance group-hover:underline">{{ $featured->title }}</h2>
                    <p class="text-lg leading-relaxed text-body">{{ $featured->excerpt }}</p>
                    <span class="flex items-center gap-2 text-base font-semibold text-forest">
                        Read the story
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                    </span>
                </div>
            </a>
        </section>
    @endif

    <section aria-label="{{ $activeCategory ? $activeCategory->getLabel().' stories' : 'More stories' }}" class="mx-auto max-w-7xl px-4 pt-18 pb-28 sm:px-8">
        @if ($posts->isEmpty() && ! $featured)
            <p class="text-base text-muted">No stories here yet.</p>
        @else
            <div class="grid grid-cols-1 gap-x-8 gap-y-12 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <x-news.card :post="$post" />
                @endforeach
            </div>
            <div class="pt-12">
                {{ $posts->links() }}
            </div>
        @endif
    </section>
</x-layouts.site>
