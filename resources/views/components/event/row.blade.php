@props(['event', 'registration' => null])

<li class="flex flex-wrap items-center gap-x-8 gap-y-5 border-b border-line py-6">
    <div class="flex h-20 flex-[0_0_76px] flex-col items-center justify-center rounded-sm bg-forest text-paper">
        <span class="font-mono text-xs tracking-widest uppercase">{{ $event->starts_at->format('M') }}</span>
        <span class="text-[34px] leading-none font-extrabold">{{ $event->starts_at->format('d') }}</span>
    </div>
    <div class="flex min-w-0 flex-[999_1_420px] flex-col gap-2">
        <div class="flex flex-wrap items-center gap-2.5">
            <x-event.type-tag :type="$event->type" />
            <span class="font-mono text-[13px] text-muted">{{ $event->whenLabel() }}</span>
        </div>
        <h3 class="text-[22px] leading-tight font-bold"><a href="{{ route('events.show', $event) }}" class="text-ink no-underline hover:text-forest hover:underline">{{ $event->title }}</a></h3>
        <span class="text-[15px] text-muted">{{ $event->venue }}</span>
    </div>
    <div class="flex flex-[1_0_auto] flex-wrap items-center justify-end gap-4">
        @if ($registration?->isConfirmed())
            <span class="flex min-h-11 items-center gap-2 rounded-xs border-[1.5px] border-forest bg-paper-dim px-4.5 text-[15px] font-bold text-forest">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
                Going
            </span>
        @elseif ($registration?->needsPayment())
            <a href="{{ route('events.show', $event) }}" class="flex min-h-11 items-center rounded-xs bg-amber px-5 text-[15px] font-bold text-ink no-underline hover:text-ink">Complete payment</a>
        @else
            <a href="{{ route('events.show', $event) }}" aria-label="Details and RSVP for {{ $event->title }}" class="flex min-h-11 items-center rounded-xs bg-forest px-5.5 text-[15px] font-bold text-paper no-underline hover:bg-ink hover:text-paper">{{ $event->starts_at->isFuture() ? 'RSVP' : (($event->photos_count ?? 0) > 0 ? 'View photos' : 'Details') }}</a>
        @endif
    </div>
</li>
