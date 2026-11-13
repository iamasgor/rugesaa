@php
    use App\Enums\EventType;
@endphp

<x-layouts.site title="Events" active="events">
    <x-site.page-hero
        title="Events"
        breadcrumb="Events"
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

    <section aria-labelledby="upcoming-title" class="mx-auto flex max-w-7xl flex-col gap-7 px-4 pt-22 sm:px-8">
        <div class="flex flex-col gap-3">
            <x-site.eyebrow>Calendar</x-site.eyebrow>
            <h2 id="upcoming-title" class="text-[clamp(32px,3.6vw,48px)] leading-[1.02] font-extrabold tracking-tight">Upcoming events</h2>
        </div>
        @if ($upcoming->isEmpty())
            <p class="py-8 text-base text-muted">No upcoming events right now — check back soon or <a href="{{ route('contact') }}">suggest an event</a>.</p>
        @else
            <ul class="flex flex-col border-t border-line">
                @foreach ($upcoming as $event)
                    <x-event.row :event="$event" :registration="$registrations->get($event->id)" />
                @endforeach
            </ul>
            {{ $upcoming->links() }}
        @endif
    </section>

    @php
        $pastFilterUrl = fn (array $changes): string => route('events.index', array_filter([
            'type' => $activeType?->value,
            'year' => $activeYear,
            ...$changes,
        ])).'#past-events';

        $pillClasses = fn (bool $isActive): string => \Illuminate\Support\Arr::toCssClasses([
            'flex min-h-11 items-center rounded-full border px-4.5 text-[15px] no-underline',
            'border-ink bg-ink font-semibold text-paper hover:text-paper' => $isActive,
            'border-line-strong bg-card font-medium text-ink hover:border-forest hover:text-ink' => ! $isActive,
        ]);
    @endphp

    <section id="past-events" aria-labelledby="past-title" class="mx-auto flex max-w-7xl scroll-mt-6 flex-col gap-7 px-4 pt-22 pb-28 sm:px-8">
        <div class="flex flex-col gap-3">
            <x-site.eyebrow>Archive</x-site.eyebrow>
            <h2 id="past-title" class="text-[clamp(32px,3.6vw,48px)] leading-[1.02] font-extrabold tracking-tight">Past events</h2>
        </div>

        <div class="flex flex-col gap-4">
            <nav aria-label="Filter past events by type" class="flex flex-wrap items-center gap-2">
                <span class="w-14 font-mono text-[13px] tracking-wide text-muted uppercase">Type</span>
                @foreach ([null => 'All types'] + collect(EventType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->pluralLabel()])->all() as $value => $label)
                    @php($isActive = ($activeType?->value ?? '') === (string) $value)
                    <a href="{{ $pastFilterUrl(['type' => $value ?: null]) }}" @if ($isActive) aria-current="true" @endif class="{{ $pillClasses($isActive) }}">{{ $label }}</a>
                @endforeach
            </nav>

            @if ($pastYears->count() > 1)
                <nav aria-label="Filter past events by year" class="flex flex-wrap items-center gap-2">
                    <span class="w-14 font-mono text-[13px] tracking-wide text-muted uppercase">Year</span>
                    <a href="{{ $pastFilterUrl(['year' => null]) }}" @if ($activeYear === null) aria-current="true" @endif class="{{ $pillClasses($activeYear === null) }}">All years</a>
                    @foreach ($pastYears as $year)
                        <a href="{{ $pastFilterUrl(['year' => $year]) }}" @if ($activeYear === $year) aria-current="true" @endif class="{{ $pillClasses($activeYear === $year) }}">{{ $year }}</a>
                    @endforeach
                </nav>
            @endif
        </div>

        @if ($past->isEmpty())
            <p class="py-8 text-base text-muted">
                @if ($activeType || $activeYear)
                    No past events match these filters. <a href="{{ route('events.index') }}#past-events">Clear filters</a>
                @else
                    No past events yet.
                @endif
            </p>
        @else
            <ul class="flex flex-col border-t border-line">
                @foreach ($past as $event)
                    <x-event.row :event="$event" />
                @endforeach
            </ul>
            {{ $past->links() }}
        @endif
    </section>
</x-layouts.site>
