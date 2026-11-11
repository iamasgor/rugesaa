<?php

use App\Enums\EventType;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;

test('the events page lists upcoming published events soonest first', function () {
    Event::factory()->create(['title' => 'Later webinar', 'starts_at' => now()->addMonths(2)]);
    Event::factory()->create(['title' => 'Sooner field day', 'starts_at' => now()->addWeek()]);
    Event::factory()->draft()->create(['title' => 'Draft reunion']);
    Event::factory()->past()->create(['title' => 'Old meetup']);

    $this->get(route('events.index'))
        ->assertOk()
        ->assertSeeInOrder(['Sooner field day', 'Later webinar'])
        ->assertDontSee('Draft reunion')
        ->assertDontSee('Old meetup');
});

test('events can be filtered by type', function () {
    Event::factory()->create(['title' => 'Batch reunion', 'type' => EventType::Reunion]);
    Event::factory()->create(['title' => 'Flood webinar', 'type' => EventType::Webinar]);

    $this->get(route('events.index', ['type' => 'webinar']))
        ->assertSee('Flood webinar')
        ->assertDontSee('Batch reunion');
});

test('past events have their own list', function () {
    Event::factory()->past()->create(['title' => 'Old meetup']);
    Event::factory()->create(['title' => 'Future meetup']);

    $this->get(route('events.index', ['past' => 1]))
        ->assertSee('Old meetup')
        ->assertDontSee('Future meetup');
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
