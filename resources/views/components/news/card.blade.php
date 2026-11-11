@props(['post'])

<a href="{{ route('news.show', $post) }}" class="group flex flex-col gap-3.5 text-ink no-underline hover:text-ink">
    @if ($post->coverImageUrl())
        <img src="{{ $post->coverImageUrl() }}" alt="{{ $post->cover_image_alt }}" class="block aspect-[16/10] w-full rounded-sm bg-[#CBD6CC] object-cover">
    @else
        <div aria-hidden="true" class="aspect-[16/10] rounded-sm bg-[#CBD6CC] bg-[repeating-linear-gradient(135deg,transparent_0_12px,rgba(20,35,30,0.06)_12px_13px)]"></div>
    @endif
    <span class="font-mono text-xs tracking-widest text-amber-deep uppercase">{{ $post->category->getLabel() }} · {{ $post->published_at->format('M Y') }}</span>
    <h3 class="text-[23px] leading-tight font-bold group-hover:underline">{{ $post->title }}</h3>
    <p class="text-[15px] leading-relaxed text-muted">{{ $post->excerpt }}</p>
</a>
