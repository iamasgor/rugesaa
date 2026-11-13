@props(['photos'])

{{-- Photos with optional titles; each opens in <x-gallery.lightbox>, which the page must include. --}}
{{-- Each link opens the full photo on its own, so the gallery works
     without JavaScript; the lightbox script takes over when it loads. --}}
<ul data-gallery class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
    @foreach ($photos as $photo)
        <li>
            <figure class="flex flex-col gap-2">
                <a href="{{ $photo->url() }}" data-gallery-item data-title="{{ $photo->title }}"
                   aria-label="Open photo {{ $loop->iteration }}{{ $photo->title ? ': '.$photo->title : '' }}"
                   class="group block overflow-hidden rounded-sm bg-paper-dim">
                    <img src="{{ $photo->url() }}" alt="{{ $photo->title ?? '' }}" loading="lazy"
                         class="block aspect-square w-full object-cover transition-transform duration-300 group-hover:scale-[1.04]">
                </a>
                @if ($photo->title)
                    <figcaption class="text-[14px] leading-snug text-body">{{ $photo->title }}</figcaption>
                @endif
            </figure>
        </li>
    @endforeach
</ul>
