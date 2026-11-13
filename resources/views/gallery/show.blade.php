<x-layouts.site :title="$album->title" active="gallery">
    <section aria-labelledby="album-title" class="mx-auto flex max-w-7xl flex-col gap-5 px-4 pt-14 sm:px-8">
        <nav aria-label="Breadcrumb" class="flex flex-wrap gap-2 font-mono text-[13px] tracking-wider text-muted">
            <a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span>
            <a href="{{ route('gallery.index') }}">Gallery</a><span aria-hidden="true">/</span>
            <span>{{ $album->title }}</span>
        </nav>
        @unless ($album->isPublished())
            <span class="self-start rounded-xs bg-amber px-2.5 py-1 text-[13px] font-bold text-ink">Draft — only admins can see this</span>
        @endunless
        <div class="flex flex-col gap-3 border-b-2 border-ink pb-7">
            <h1 id="album-title" class="text-[clamp(38px,5vw,68px)] leading-[1.0] font-extrabold tracking-tight text-balance">{{ $album->title }}</h1>
            @if ($album->description)
                <p class="text-xl leading-relaxed text-body">{{ $album->description }}</p>
            @endif
            <p class="font-mono text-[13px] text-muted">
                {{ collect([$album->held_on?->format('j F Y'), trans_choice('{0} No photos|{1} :count photo|[2,*] :count photos', $photos->count())])->filter()->implode(' · ') }}
            </p>
        </div>
    </section>

    <section aria-label="Photos" class="mx-auto max-w-7xl px-4 pt-10 pb-28 sm:px-8">
        @if ($photos->isEmpty())
            <p class="py-8 text-base text-muted">Photos for this program are on their way.</p>
        @else
            <x-gallery.photo-grid :photos="$photos" />
        @endif

        <p class="pt-12"><a href="{{ route('gallery.index') }}" class="text-[15px] font-semibold">← All albums</a></p>
    </section>

    <x-gallery.lightbox />
</x-layouts.site>
