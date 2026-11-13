@php
    $user = auth()->user();
    $isOpen = $event->isOpenForRegistration();
    $isFull = $seatsLeft !== null && $seatsLeft === 0 && ! $registration?->isConfirmed();
@endphp

<x-layouts.site :title="$event->title" active="events">
    <section aria-labelledby="event-title" class="bg-forest text-paper">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 px-4 pt-16 pb-18 sm:px-8">
            <nav aria-label="Breadcrumb" class="flex flex-wrap gap-2 font-mono text-[13px] tracking-wider text-mist-dim">
                <a href="{{ route('home') }}" class="text-mist">Home</a><span aria-hidden="true">/</span>
                <a href="{{ route('events.index') }}" class="text-mist">Events</a><span aria-hidden="true">/</span>
                <span>{{ $event->type->getLabel() }}</span>
            </nav>
            <div class="flex flex-wrap items-center gap-3">
                <x-event.type-tag :type="$event->type" />
                <span class="font-mono text-[13px] text-mist">{{ $event->whenLabel() }}</span>
                @unless ($event->isPublished())
                    <span class="rounded-xs bg-amber px-2.5 py-1 text-[13px] font-bold text-ink">Draft — only admins can see this</span>
                @endunless
            </div>
            <h1 id="event-title" class="max-w-4xl text-[clamp(40px,5vw,72px)] leading-[0.98] font-extrabold tracking-tight text-balance">{{ $event->title }}</h1>
            <p class="text-[19px] leading-relaxed text-mist">{{ $event->summary }}</p>
        </div>
    </section>

    <div class="mx-auto flex max-w-7xl flex-wrap items-start gap-x-16 gap-y-12 px-4 pt-14 sm:px-8">
        <div class="flex min-w-0 flex-[999_1_560px] flex-col gap-10">
            @if ($event->coverImageUrl())
                <img src="{{ $event->coverImageUrl() }}" alt="" class="aspect-[16/9] w-full rounded-sm bg-paper-dim object-cover">
            @endif

            @if (filled($event->schedule))
                <section aria-labelledby="schedule-title" class="flex flex-col gap-4">
                    <h2 id="schedule-title" class="text-[28px] font-extrabold">Schedule</h2>
                    <ol class="flex flex-col border-t border-line">
                        @foreach ($event->schedule as $item)
                            <li class="grid grid-cols-[96px_minmax(0,1fr)] gap-4 border-b border-line py-3 text-base"><span class="font-mono text-muted">{{ $item['time'] }}</span><span>{{ $item['label'] }}</span></li>
                        @endforeach
                    </ol>
                </section>
            @endif

            @if ($event->description)
                <section aria-labelledby="about-event-title" class="flex flex-col gap-4">
                    <h2 id="about-event-title" class="text-[28px] font-extrabold">About this event</h2>
                    <div class="prose-rugesaa flex flex-col gap-4 text-[17px] leading-relaxed text-body">
                        {!! Str::markdown($event->description, ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
                    </div>
                </section>
            @endif
        </div>

        <aside aria-label="Registration" class="flex flex-[1_1_340px] flex-col gap-5 rounded-sm border border-line bg-card p-6 lg:sticky lg:top-6">
            <dl class="flex flex-col gap-3 text-[15px]">
                <div class="flex flex-col gap-0.5"><dt class="text-muted">When</dt><dd class="font-semibold">{{ $event->starts_at->format('l j F Y, g:i A') }}@if ($event->ends_at) – {{ $event->ends_at->isSameDay($event->starts_at) ? $event->ends_at->format('g:i A') : $event->ends_at->format('j F, g:i A') }}@endif</dd></div>
                <div class="flex flex-col gap-0.5"><dt class="text-muted">Where</dt><dd class="font-semibold">{{ $event->venue }}</dd></div>
                <div class="flex flex-col gap-0.5"><dt class="text-muted">Fee</dt><dd class="font-semibold">{{ $event->feeLabel() }}</dd></div>
                @if ($event->registration_closes_at)
                    <div class="flex flex-col gap-0.5"><dt class="text-muted">Register by</dt><dd class="font-semibold">{{ $event->registration_closes_at->format('j M Y, g:i A') }}</dd></div>
                @endif
                @if ($seatsLeft !== null && $isOpen)
                    <div class="flex flex-col gap-0.5"><dt class="text-muted">Seats left</dt><dd class="font-semibold">{{ $seatsLeft }}</dd></div>
                @endif
            </dl>

            <div class="flex flex-col gap-4 border-t border-line pt-5">
                @if ($registration?->isConfirmed())
                    <p role="status" class="flex items-center gap-2 text-lg font-bold text-forest">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
                        You're going{{ $registration->guests > 0 ? ' with '.$registration->guests.' '.Str::plural('guest', $registration->guests) : '' }}
                    </p>
                    @if ($event->online_url)
                        <a href="{{ $event->online_url }}" rel="noopener" class="flex min-h-12 items-center justify-center rounded-xs bg-forest px-5 font-bold text-paper no-underline hover:bg-ink hover:text-paper">Join online</a>
                    @endif
                    @if ($registration->canBeCancelledByMember())
                        <form method="POST" action="{{ route('events.registration.destroy', $event) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="cursor-pointer text-[15px] font-semibold text-forest underline">Cancel my registration</button>
                        </form>
                    @else
                        <p class="text-sm text-muted">Can't make it? <a href="{{ route('contact') }}">Contact us</a> about a refund.</p>
                    @endif
                @elseif ($registration?->needsPayment())
                    <p class="text-[15px]">Your {{ $registration->guests > 0 ? 'seats are' : 'seat is' }} held. Pay <strong>BDT {{ number_format($registration->amount) }}</strong> to confirm.</p>
                    @if ($isOpen)
                        <form method="POST" action="{{ route('events.registration.pay', $event) }}">
                            @csrf
                            <x-form.button class="w-full">Complete payment</x-form.button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('events.registration.destroy', $event) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="cursor-pointer text-[15px] font-semibold text-forest underline">Release my seat</button>
                    </form>
                @elseif (! $isOpen)
                    <p class="text-[15px] font-semibold text-muted">Registration is closed.</p>
                @elseif ($isFull)
                    <p class="text-[15px] font-semibold text-muted">This event is full.</p>
                @elseif ($user === null)
                    <p class="text-[15px]">Registration is open to active members.</p>
                    <a href="{{ route('login') }}" class="flex min-h-12 items-center justify-center rounded-xs bg-forest px-5 font-bold text-paper no-underline hover:bg-ink hover:text-paper">Log in to register</a>
                    <a href="{{ route('register') }}" class="text-[15px] font-semibold">Not a member yet? Join →</a>
                @elseif (! $user->hasMemberAccess())
                    <p class="text-[15px]">Registration opens once your membership is active. <a href="{{ route('portal') }}">Check your membership</a>.</p>
                @else
                    <form method="POST" action="{{ route('events.register', $event) }}" class="flex flex-col gap-4">
                        @csrf
                        @if ($event->max_guests > 0)
                            <x-form.select name="guests" label="Guests" :options="collect(range(0, $event->max_guests))->mapWithKeys(fn ($count) => [$count => $count === 0 ? 'Just me' : 'Me + '.$count.' '.Str::plural('guest', $count).($event->guest_fee > 0 ? ' (BDT '.number_format($event->feeFor($count)).')' : '')])->all()" />
                        @endif
                        <x-form.button class="w-full">{{ $event->isFree() ? 'Register' : 'Register & pay BDT '.number_format($event->member_fee).($event->max_guests > 0 && $event->guest_fee > 0 ? '+' : '') }}</x-form.button>
                    </form>
                @endif
            </div>

            <a href="{{ route('events.calendar', $event) }}" class="border-t border-line pt-4 text-[15px] font-semibold">Add to calendar</a>
        </aside>
    </div>

    @if (filled($event->committee))
        <section aria-labelledby="committee-title" class="mx-auto flex max-w-7xl flex-col gap-6 px-4 pt-20 sm:px-8">
            <h2 id="committee-title" class="text-[28px] font-extrabold">Organising committee</h2>
            <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($event->committee as $member)
                    <li class="flex items-center gap-4 rounded-sm border border-line bg-card p-4">
                        @if (filled($member['photo_path'] ?? null))
                            <img src="{{ Storage::disk('public')->url($member['photo_path']) }}" alt="" loading="lazy" class="size-16 shrink-0 rounded-full bg-paper-dim object-cover">
                        @else
                            <span aria-hidden="true" class="flex size-16 shrink-0 items-center justify-center rounded-full bg-forest text-xl font-bold text-paper">{{ Str::of($member['name'])->explode(' ')->filter()->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('') }}</span>
                        @endif
                        <div class="flex min-w-0 flex-col gap-0.5">
                            <span class="font-mono text-[12px] tracking-[0.1em] text-amber-deep uppercase">{{ $member['role'] }}</span>
                            <span class="text-[17px] leading-tight font-bold">{{ $member['name'] }}</span>
                            @if (filled($member['batch'] ?? null))
                                <span class="text-[14px] text-muted">Batch {{ $member['batch'] }}</span>
                            @endif
                            @if (filled($member['phone'] ?? null))
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $member['phone']) }}" class="text-[14px] font-semibold">{{ $member['phone'] }}</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($photos->isNotEmpty())
        <section aria-labelledby="photos-title" class="mx-auto flex max-w-7xl flex-col gap-6 px-4 pt-20 sm:px-8">
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <h2 id="photos-title" class="text-[28px] font-extrabold">Photos</h2>
                <span class="font-mono text-[13px] text-muted">{{ trans_choice('{1} :count photo|[2,*] :count photos', $photos->count()) }}</span>
            </div>
            <x-gallery.photo-grid :photos="$photos" />
        </section>

        <x-gallery.lightbox />
    @endif

    <div class="pb-28"></div>
</x-layouts.site>
