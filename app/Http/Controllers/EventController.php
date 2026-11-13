<?php

namespace App\Http\Controllers;

use App\Enums\EventRegistrationStatus;
use App\Enums\EventType;
use App\Models\Event;
use App\Models\EventRegistration;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Upcoming events first, then the archive of past events, which visitors
     * can narrow by type and year without affecting the upcoming list.
     */
    public function index(Request $request): View
    {
        $upcoming = Event::query()
            ->published()
            ->upcoming()
            ->paginate(10, pageName: 'upcoming_page')
            ->withQueryString();

        $pastYears = Event::query()
            ->published()
            ->past()
            ->pluck('starts_at')
            ->map(fn (CarbonInterface $startsAt): int => $startsAt->year)
            ->unique()
            ->values();

        $type = EventType::tryFrom((string) $request->query('type'));
        $year = $pastYears->contains($request->integer('year')) ? $request->integer('year') : null;

        $past = Event::query()
            ->published()
            ->past()
            ->when($type, fn ($query, EventType $type) => $query->where('type', $type))
            ->when($year, fn ($query, int $year) => $query->whereYear('starts_at', $year))
            ->withCount('photos')
            ->paginate(10, pageName: 'past_page')
            ->withQueryString()
            ->fragment('past-events');

        $featured = Event::query()->published()->upcoming()->where('is_featured', true)->first();

        return view('events.index', [
            'upcoming' => $upcoming,
            'past' => $past,
            'pastYears' => $pastYears,
            'featured' => $featured,
            'activeType' => $type,
            'activeYear' => $year,
            'registrations' => $this->registrationsFor($request, collect($upcoming->items())->push($featured)->filter()),
        ]);
    }

    public function show(Request $request, Event $event): View
    {
        abort_unless($event->isPublished() || $request->user()?->is_admin, 404);

        $registration = $request->user()
            ? EventRegistration::query()->where('event_id', $event->id)->where('user_id', $request->user()->id)->first()
            : null;

        return view('events.show', [
            'event' => $event,
            'registration' => $registration,
            'seatsLeft' => $event->seatsLeft(),
            'photos' => $event->photos()->get(),
        ]);
    }

    /**
     * A one-event iCalendar file for "Add to calendar".
     */
    public function calendar(Event $event): Response
    {
        abort_unless($event->isPublished(), 404);

        $format = fn ($date): string => $date->utc()->format('Ymd\THis\Z');
        $escape = fn (string $text): string => addcslashes(str_replace(["\r\n", "\n"], '\n', $text), ',;\\');
        $endsAt = $event->ends_at ?? $event->starts_at->addHours(2);

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//RUGESAA//Events//EN',
            'BEGIN:VEVENT',
            'UID:event-'.$event->id.'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'DTSTAMP:'.$format(now()),
            'DTSTART:'.$format($event->starts_at),
            'DTEND:'.$format($endsAt),
            'SUMMARY:'.$escape($event->title),
            'LOCATION:'.$escape($event->venue),
            'DESCRIPTION:'.$escape($event->summary),
            'URL:'.route('events.show', $event),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.Str::slug($event->title).'.ics"',
        ]);
    }

    /**
     * The signed-in member's live registrations for the listed events, keyed by event id.
     *
     * @param  Collection<int, Event>  $events
     * @return Collection<int, EventRegistration>
     */
    private function registrationsFor(Request $request, Collection $events): Collection
    {
        if ($request->user() === null || $events->isEmpty()) {
            return collect();
        }

        return EventRegistration::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('event_id', $events->pluck('id'))
            ->whereIn('status', EventRegistrationStatus::holdingSeats())
            ->get()
            ->keyBy('event_id');
    }
}
