{{-- Full-screen photo viewer driven by resources/js/gallery.js. One per page, beside an <x-gallery.photo-grid>. --}}
<dialog data-lightbox aria-label="Photo viewer"
        class="m-0 h-dvh max-h-none w-screen max-w-none bg-[#07140F] p-0 text-paper backdrop:bg-[#07140F]">
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <span data-lightbox-counter class="font-mono text-[13px] tracking-wider text-mist"></span>
            <button type="button" data-lightbox-close aria-label="Close"
                    class="flex size-11 items-center justify-center rounded-full text-paper hover:bg-white/10">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <div class="relative flex min-h-0 flex-1 items-center justify-center px-2 sm:px-20">
            <button type="button" data-lightbox-prev aria-label="Previous photo"
                    class="absolute left-2 z-10 flex size-12 items-center justify-center rounded-full bg-black/40 text-paper hover:bg-black/60 sm:left-5">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
            </button>

            <img data-lightbox-image src="" alt="" class="max-h-full max-w-full rounded-xs object-contain select-none">

            <button type="button" data-lightbox-next aria-label="Next photo"
                    class="absolute right-2 z-10 flex size-12 items-center justify-center rounded-full bg-black/40 text-paper hover:bg-black/60 sm:right-5">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
            </button>
        </div>

        <p data-lightbox-title class="min-h-14 px-4 py-4 text-center text-base text-mist sm:px-6"></p>
    </div>
</dialog>
