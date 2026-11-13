<x-layouts.site title="Gallery" active="gallery">
    <x-site.page-hero
        title="Gallery"
        breadcrumb="Gallery"
        intro="Photos from our reunions, field days, seminars and other programs." />

    <section aria-label="Albums" class="mx-auto max-w-7xl px-4 pt-16 pb-28 sm:px-8">
        @if ($albums->isEmpty())
            <p class="py-8 text-base text-muted">No photos yet — check back after our next program.</p>
        @else
            <ul class="grid grid-cols-1 gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($albums as $album)
                    <li>
                        <a href="{{ route('gallery.show', $album) }}" class="group flex flex-col gap-4 text-ink no-underline hover:text-ink">
                            <div class="relative overflow-hidden rounded-sm bg-paper-dim">
                                <img src="{{ $album->coverPhoto?->url() }}" alt="" loading="lazy"
                                     class="block aspect-[4/3] w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                                <span class="absolute right-3 bottom-3 rounded-xs bg-ink/80 px-2.5 py-1 font-mono text-xs text-paper">
                                    {{ trans_choice('{1} :count photo|[2,*] :count photos', $album->photos_count) }}
                                </span>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                @if ($album->held_on)
                                    <x-site.eyebrow>{{ $album->held_on->format('j M Y') }}</x-site.eyebrow>
                                @endif
                                <h2 class="text-[22px] leading-tight font-bold group-hover:underline">{{ $album->title }}</h2>
                                @if ($album->description)
                                    <p class="text-[15px] leading-relaxed text-muted">{{ $album->description }}</p>
                                @endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="pt-12">{{ $albums->links() }}</div>
        @endif
    </section>
</x-layouts.site>
