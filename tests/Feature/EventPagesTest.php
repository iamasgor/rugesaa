<?php

use App\Enums\EventType;
use App\Models\Event;
use App\Models\EventRegistration;
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
    Event::factory()->past()->create(['title' => 'Old batch reunion', 'type' => EventType::Reunion]);
    Event::factory()->past()->create(['title' => 'Old flood webinar', 'type' => EventType::Webinar]);

    $this->get(route('events.index', ['type' => 'webinar']))
        ->assertSee('Upcoming reunion')
        ->assertSee('Old flood webinar')
        ->assertDontSee('Old batch reunion');
});

test('past events can be filtered by year', function () {
    Event::factory()->past()->create(['title' => 'Meetup in 2024', 'starts_at' => '2024-03-10 10:00', 'ends_at' => null]);
    Event::factory()->past()->create(['title' => 'Meetup in 2025', 'starts_at' => '2025-03-10 10:00', 'ends_at' => null]);

    $this->get(route('events.index', ['year' => 2024]))
        ->assertSee('Meetup in 2024')
        ->assertDontSee('Meetup in 2025');
});

test('an unknown year is ignored rather than emptying the archive', function () {
    Event::factory()->past()->create(['title' => 'Meetup in 2025', 'starts_at' => '2025-03-10 10:00', 'ends_at' => null]);

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
