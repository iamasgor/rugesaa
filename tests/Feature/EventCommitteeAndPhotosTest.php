<?php

use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\RelationManagers\PhotosRelationManager;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('the event edit page is split into tabs, including photos and attendees', function () {
    $event = Event::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(EditEvent::getUrl(['record' => $event]))
        ->assertOk()
        ->assertSeeInOrder(['Event', 'When &amp; registration', 'Schedule', 'Committee', 'Photos', 'Attendees'], false);
});

test('an admin can save the event committee', function () {
    $this->actingAs(User::factory()->admin()->create());
    $event = Event::factory()->create();

    Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
        ->fillForm([
            'committee' => [
                ['name' => 'Rahima Khatun', 'role' => 'Convener', 'batch' => '1998–99', 'phone' => '01711-000000', 'photo_path' => null],
                ['name' => 'Karim Uddin', 'role' => 'Member secretary', 'batch' => null, 'phone' => null, 'photo_path' => null],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($event->fresh()->committee)->toHaveCount(2)
        ->and($event->fresh()->committee[0])->toMatchArray(['name' => 'Rahima Khatun', 'role' => 'Convener']);
});

test('an admin can upload photos to an event', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $event = Event::factory()->past()->create();

    Livewire::test(PhotosRelationManager::class, ['ownerRecord' => $event, 'pageClass' => EditEvent::class])
        ->callTableAction('upload', data: ['uploads' => [UploadedFile::fake()->image('stage.jpg'), UploadedFile::fake()->image('crowd.jpg')]])
        ->assertHasNoTableActionErrors();

    expect($event->photos()->pluck('sort_order')->all())->toBe([1, 2]);
    $event->photos->each(fn (EventPhoto $photo) => Storage::disk('public')->assertExists($photo->image_path));
});

test('the event page shows the committee and the photos', function () {
    $event = Event::factory()->past()->create([
        'committee' => [
            ['name' => 'Rahima Khatun', 'role' => 'Convener', 'batch' => '1998–99', 'phone' => '01711-000000'],
        ],
    ]);
    EventPhoto::factory()->for($event)->create(['title' => 'Cultural evening', 'sort_order' => 2]);
    EventPhoto::factory()->for($event)->create(['title' => 'Opening ceremony', 'sort_order' => 1]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertSee('Organising committee')
        ->assertSee('Rahima Khatun')
        ->assertSee('Batch 1998–99')
        ->assertSee('tel:01711000000', false)
        ->assertSeeInOrder(['Opening ceremony', 'Cultural evening'])
        ->assertSee('data-lightbox', false);
});

test('events without a committee or photos show neither section', function () {
    $event = Event::factory()->create(['committee' => null]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertDontSee('Organising committee')
        ->assertDontSee('data-lightbox', false);
});

test('past events with photos invite visitors to view them', function () {
    $event = Event::factory()->past()->create();
    EventPhoto::factory()->for($event)->create();

    $this->get(route('events.index'))->assertSee('View photos');
});

test('deleting an event removes its photo files', function () {
    Storage::fake('public');
    $event = Event::factory()->create();
    $path = UploadedFile::fake()->image('a.jpg')->store('events/photos', 'public');
    EventPhoto::factory()->for($event)->create(['image_path' => $path]);

    $event->delete();

    Storage::disk('public')->assertMissing($path);
    expect(EventPhoto::query()->count())->toBe(0);
});
