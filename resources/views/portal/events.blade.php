<x-portal.shell title="My events" active="events">
    <section aria-labelledby="my-events-title" class="flex flex-col gap-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h1 id="my-events-title" class="text-[clamp(30px,3.4vw,42px)] leading-[1.05] font-extrabold tracking-tight">My events</h1>
            <a href="{{ route('events.index') }}" class="text-[15px] font-semibold">Browse all events →</a>
        </div>
        @if ($upcoming->isEmpty())
            <p class="rounded-sm bg-paper-dim p-6 text-base text-body">You haven't registered for any upcoming events. <a href="{{ route('events.index') }}">See what's on</a>.</p>
        @else
            <ul class="flex flex-col border-t border-line">
                @foreach ($upcoming as $registration)
                    <x-event.row :event="$registration->event" :registration="$registration" />
                @endforeach
            </ul>
        @endif
    </section>

    @if ($past->isNotEmpty())
        <section aria-labelledby="past-events-title" class="flex flex-col gap-4">
            <h2 id="past-events-title" class="text-2xl font-extrabold">Events you attended</h2>
            <ul class="flex flex-col border-t border-line">
                @foreach ($past as $registration)
                    <li class="flex flex-wrap justify-between gap-2 border-b border-line py-3 text-[15px]">
                        <a href="{{ route('events.show', $registration->event) }}">{{ $registration->event->title }}</a>
                        <span class="font-mono text-muted">{{ $registration->event->starts_at->format('j M Y') }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-portal.shell>
