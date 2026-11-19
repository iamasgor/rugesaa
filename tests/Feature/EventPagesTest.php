<?php

use App\Enums\EventType;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;

test('the events page lists upcoming published events soonest first', function () {
    Event::factory()->create(['title' => 'Later webinar', 'starts_at' => now()->addMonths(2)]);
    Event::factory()->create(['title' => 'Sooner field day', 'starts_at' => now()->addWeek()]);
    Event::factory()->draft()->create(['title' => 'Draft reunion']);

    $this->get(route('events.index'))
        ->assertOk()
        ->assertSeeInOrder(['Upcoming events', 'Sooner field day', 'Later webinar'])
        ->assertDontSee('Draft reunion');
});

test('past events are listed below the upcoming ones, newest first', function () {
    Event::factory()->create(['title' => 'Future meetup']);
    Event::factory()->past()->create(['title' => 'Older meetup', 'starts_at' => now()->subYear(), 'ends_at' => null]);
    Event::factory()->past()->create(['title' => 'Recent meetup', 'starts_at' => now()->subMonth(), 'ends_at' => null]);

    $this->get(route('events.index'))
        ->assertOk()
        ->assertSeeInOrder(['Upcoming events', 'Future meetup', 'Past events', 'Recent meetup', 'Older meetup']);
});

test('past events can be filtered by type without hiding upcoming events', function () {
    Event::factory()->create(['title' => 'Upcoming reunion', 'type' => EventType::Reunion]);
    Event::factory()->past()->create(['title' => 'Old field day', 'type' => EventType::FieldDay]);
    Event::factory()->past()->create(['title' => 'Old flood webinar', 'type' => EventType::Webinar]);

    $this->get(route('events.index', ['type' => 'webinar']))
        ->assertSee('Upcoming reunion')
        ->assertSee('Old flood webinar')
        ->assertDontSee('Old field day');
});

test('past events can be filtered by year', function () {
    Event::factory()->past()->create(['title' => 'Meetup in 2024', 'starts_at' => '2024-03-10 10:00', 'ends_at' => null]);
    Event::factory()->past()->create(['title' => 'Meetup in 2025', 'type' => EventType::Webinar, 'starts_at' => '2025-03-10 10:00', 'ends_at' => null]);

    $this->get(route('events.index', ['year' => 2024]))
        ->assertSee('Meetup in 2024')
        ->assertDontSee('Meetup in 2025');
});

test('an unknown year is ignored rather than emptying the archive', function () {
    Event::factory()->past()->create(['title' => 'Meetup in 2025', 'type' => EventType::Webinar, 'starts_at' => '2025-03-10 10:00', 'ends_at' => null]);

    $this->get(route('events.index', ['year' => 1990]))
        ->assertOk()
        ->assertSee('Meetup in 2025');
});

test('the featured event is highlighted on the events and home pages', function () {
    Event::factory()->featured()->create(['title' => 'Grand Reunion 2026', 'schedule' => [['time' => '9:00 AM', 'label' => 'Registration & breakfast']]]);

    $this->get(route('events.index'))->assertSee('Featured')->assertSee('Registration &amp; breakfast', false);
    $this->get(route('home'))->assertSee('Grand Reunion 2026');
});

test('draft events are hidden from visitors but previewable by admins', function () {
    $event = Event::factory()->draft()->create();

    $this->get(route('events.show', $event))->assertNotFound();
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('events.show', $event))
        ->assertOk()
        ->assertSee('Draft');
});

test('the joining link is only shown to confirmed attendees', function () {
    $event = Event::factory()->create(['online_url' => 'https://meet.example.com/rugesaa']);
    $registration = EventRegistration::factory()->for($event)->create();

    $this->get(route('events.show', $event))->assertDontSee('https://meet.example.com/rugesaa');
    $this->actingAs($registration->user)->get(route('events.show', $event))->assertSee('https://meet.example.com/rugesaa');
});

test('event descriptions are rendered as markdown with html escaped', function () {
    $event = Event::factory()->create(['description' => "**Bring a hat**\n\n<script>alert(1)</script>"]);

    $this->get(route('events.show', $event))
        ->assertSee('<strong>Bring a hat</strong>', false)
        ->assertDontSee('<script>alert(1)</script>', false);
});

test('an event can be added to a calendar', function () {
    $event = Event::factory()->create(['title' => 'QGIS workshop', 'starts_at' => '2026-12-06 10:00:00']);

    $response = $this->get(route('events.calendar', $event))->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('text/calendar')
        ->and($response->getContent())
        ->toContain('SUMMARY:QGIS workshop')
        ->toContain('DTSTART:20261206T040000Z');
});

test('the event page counts confirmed registrants by membership plan', function () {
    $event = Event::factory()->create();
    $life = MembershipPlan::factory()->lifetime()->create(['price' => 10000]);
    $general = MembershipPlan::factory()->create(['price' => 5000, 'duration_months' => 12]);
    $student = MembershipPlan::factory()->free()->create();

    $registrantWith = function (MembershipPlan $plan) use ($event): void {
        $user = User::factory()->create();
        Membership::factory()->active()->for($user)->for($plan, 'plan')->create(['expires_at' => $plan->isLifetime() ? null : now()->addYear()]);
        EventRegistration::factory()->for($event)->for($user)->create();
    };

    $registrantWith($life);
    $registrantWith($life);
    $registrantWith($general);
    $registrantWith($student);
    EventRegistration::factory()->for($event)->create();
    EventRegistration::factory()->for($event)->pendingPayment()->create();
    EventRegistration::factory()->for($event)->cancelled()->create();

    expect($event->registrationCounts())->toBe(['total' => 5, 'life' => 2, 'general' => 1]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertSeeInOrder(['5', 'Registered', '2', 'Life members', '1', 'General members']);
});

test('the header reunion menu lists published reunions linking to their pages', function () {
    $reunion = Event::factory()->create(['title' => 'Silver jubilee reunion', 'type' => EventType::Reunion]);
    Event::factory()->draft()->create(['title' => 'Draft batch reunion', 'type' => EventType::Reunion]);
    Event::factory()->create(['title' => 'Climate webinar', 'type' => EventType::Webinar]);

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Reunion')
        ->assertSee('Silver jubilee reunion')
        ->assertSee(route('events.show', $reunion))
        ->assertDontSee('Draft batch reunion')
        ->assertDontSee('Climate webinar');
});

test('the header reunion menu shows each date and marks reunions that have not ended as ongoing', function () {
    Event::factory()->create([
        'title' => 'Reunion next year',
        'type' => EventType::Reunion,
        'starts_at' => now()->addYear(),
    ]);
    Event::factory()->create([
        'title' => 'Reunion last year',
        'type' => EventType::Reunion,
        'starts_at' => now()->subYear(),
        'ends_at' => null,
    ]);

    $response = $this->get(route('contact'))->assertOk();

    $response->assertSeeInOrder(['Reunion next year', now()->addYear()->format('j M Y'), 'Ongoing', 'Reunion last year', now()->subYear()->format('j M Y')]);
    expect(substr_count($response->getContent(), '>Ongoing<'))->toBe(1);
});

test('an event has ended after its end time, or after its start day when it has no end time', function () {
    $this->travelTo(now()->setTime(12, 0));

    expect(Event::factory()->make(['starts_at' => now()->addMonth(), 'ends_at' => null])->hasEnded())->toBeFalse()
        ->and(Event::factory()->make(['starts_at' => now()->subHour(), 'ends_at' => now()->addHour()])->hasEnded())->toBeFalse()
        ->and(Event::factory()->make(['starts_at' => now()->subHour(), 'ends_at' => null])->hasEnded())->toBeFalse()
        ->and(Event::factory()->make(['starts_at' => now()->subDays(2), 'ends_at' => now()->subDay()])->hasEnded())->toBeTrue()
        ->and(Event::factory()->make(['starts_at' => now()->subDay(), 'ends_at' => null])->hasEnded())->toBeTrue();
});
