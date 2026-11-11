@php
    use App\Enums\EventType;
@endphp

<x-layouts.site title="Events & reunions" active="events">
    <x-site.page-hero
        title="Events & reunions"
        breadcrumb="Events & Reunions"
        intro="Reunions, webinars, field days and career evenings. Register online — we'll email your confirmation and the joining link." />

    @if ($featured)
        <section aria-labelledby="feat-title" class="mx-auto max-w-7xl px-4 pt-16 sm:px-8">
            <div class="grid grid-cols-1 overflow-hidden rounded-sm border border-line bg-card lg:grid-cols-2">
                <img src="{{ $featured->coverImageUrl() ?? asset('images/reunion.jpg') }}" alt="" class="block size-full min-h-[420px] bg-[#3C5F53] object-cover object-[center_85%]">
                <div class="flex flex-col gap-5 p-8 sm:p-10">
                    <x-site.eyebrow>Featured {{ strtolower($featured->type->getLabel()) }} · {{ $featured->starts_at->format('D j M Y') }}</x-site.eyebrow>
                    <h2 id="feat-title" class="text-[clamp(32px,3.6vw,48px)] leading-[1.02] font-extrabold tracking-tight">{{ $featured->title }}</h2>
                    <p class="text-[17px] leading-relaxed text-body">{{ $featured->summary }}</p>
                    @if (filled($featured->schedule))
                        <ol class="flex flex-col border-t border-line">
                            @foreach ($featured->schedule as $item)
                                <li class="grid grid-cols-[96px_minmax(0,1fr)] gap-4 border-b border-line py-2.5 text-[15px]"><span class="font-mono text-muted">{{ $item['time'] }}</span><span>{{ $item['label'] }}</span></li>
                            @endforeach
                        </ol>
                    @endif
                    <div class="flex flex-wrap gap-x-6 gap-y-2 text-[15px] text-body">
                        <span><strong class="text-ink">Fee</strong> · {{ $featured->feeLabel() }}</span>
                        @if ($featured->registration_closes_at)
                            <span><strong class="text-ink">Register by</strong> · {{ $featured->registration_closes_at->format('j M Y') }}</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        @if ($registrations->get($featured->id)?->isConfirmed())
                            <span class="flex min-h-13 items-center gap-2 rounded-xs border-[1.5px] border-forest bg-paper-dim px-6 text-[17px] font-bold text-forest">You're going</span>
                        @else
                            <a href="{{ route('events.show', $featured) }}" class="flex min-h-13 items-center rounded-xs bg-forest px-6.5 text-[17px] font-bold text-paper no-underline hover:bg-ink hover:text-paper">{{ $featured->isFree() ? 'RSVP' : 'RSVP & pay fee' }}</a>
                        @endif
                        <a href="{{ route('events.calendar', $featured) }}" class="text-[15px] font-semibold">Add to calendar</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section aria-labelledby="list-title" class="mx-auto flex max-w-7xl flex-col gap-7 px-4 pt-22 sm:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-col gap-3">
                <x-site.eyebrow>Calendar</x-site.eyebrow>
                <h2 id="list-title" class="text-[clamp(32px,3.6vw,48px)] leading-[1.02] font-extrabold tracking-tight">{{ $showPast ? 'Past events' : 'All upcoming events' }}</h2>
            </div>
            <a href="{{ $showPast ? route('events.index') : route('events.index', ['past' => 1]) }}" class="text-[15px] font-semibold">{{ $showPast ? '← Upcoming events' : 'Past events →' }}</a>
        </div>
        <nav aria-label="Filter events by type" class="flex flex-wrap gap-2">
            @foreach ([null => 'All events'] + collect(EventType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->pluralLabel()])->all() as $value => $label)
                @php($isActive = ($activeType?->value ?? '') === (string) $value)
                <a href="{{ route('events.index', array_filter(['type' => $value ?: null, 'past' => $showPast ? 1 : null])) }}"
                   @if ($isActive) aria-current="true" @endif
                   @class([
                       'flex min-h-11 items-center rounded-full border px-4.5 text-[15px] no-underline',
                       'border-ink bg-ink font-semibold text-paper hover:text-paper' => $isActive,
                       'border-line-strong bg-card font-medium text-ink hover:border-forest hover:text-ink' => ! $isActive,
                   ])>{{ $label }}</a>
            @endforeach
        </nav>
        @if ($events->isEmpty())
            <p class="py-8 text-base text-muted">Nothing here yet — check back soon or <a href="{{ route('contact') }}">suggest an event</a>.</p>
        @else
            <ul class="flex flex-col border-t border-line">
                @foreach ($events as $event)
                    <x-event.row :event="$event" :registration="$registrations->get($event->id)" />
                @endforeach
            </ul>
            {{ $events->links() }}
        @endif
    </section>

    <section aria-labelledby="host-title" class="mx-auto max-w-7xl px-4 pt-24 pb-28 sm:px-8">
        <div class="flex flex-col gap-3.5 rounded-sm bg-ink p-9 text-paper lg:max-w-2xl">
            <h2 id="host-title" class="text-[28px] leading-tight font-extrabold">Planning a batch reunion?</h2>
            <p class="text-base leading-relaxed text-mist">We'll help with the guest list, a venue on campus, registration and fee collection.</p>
            <a href="{{ route('contact') }}" class="mt-2 flex min-h-12 items-center self-start rounded-xs bg-amber px-5.5 text-base font-bold text-ink no-underline hover:text-ink">Propose a reunion</a>
        </div>
    </section>
</x-layouts.site>
