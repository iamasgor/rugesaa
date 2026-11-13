<?php

use App\Filament\Resources\GalleryAlbums\Pages\CreateGalleryAlbum;
use App\Filament\Resources\GalleryAlbums\Pages\EditGalleryAlbum;
use App\Filament\Resources\GalleryAlbums\RelationManagers\PhotosRelationManager;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('the gallery is linked from the site menu', function () {
    $this->get(route('home'))->assertSee(route('gallery.index'));
});

test('the gallery lists published albums with photos, newest program first', function () {
    GalleryAlbum::factory()->has(GalleryPhoto::factory(), 'photos')->create(['title' => 'Field day 2024', 'held_on' => '2024-02-01']);
    GalleryAlbum::factory()->has(GalleryPhoto::factory()->count(3), 'photos')->create(['title' => 'Reunion 2025', 'held_on' => '2025-12-20']);
    GalleryAlbum::factory()->draft()->has(GalleryPhoto::factory(), 'photos')->create(['title' => 'Draft album']);
    GalleryAlbum::factory()->create(['title' => 'Empty album']);

    $this->get(route('gallery.index'))
        ->assertOk()
        ->assertSeeInOrder(['3 photos', 'Reunion 2025', 'Field day 2024'])
        ->assertDontSee('Draft album')
        ->assertDontSee('Empty album');
});

test('an album shows its photos in order with their optional titles', function () {
    $album = GalleryAlbum::factory()->create();
    GalleryPhoto::factory()->for($album, 'album')->create(['title' => 'Group photo', 'sort_order' => 2]);
    GalleryPhoto::factory()->for($album, 'album')->create(['title' => 'Opening speech', 'sort_order' => 1]);
    GalleryPhoto::factory()->for($album, 'album')->create(['title' => null, 'sort_order' => 3]);

    $this->get(route('gallery.show', $album))
        ->assertOk()
        ->assertSee($album->title)
        ->assertSeeInOrder(['Opening speech', 'Group photo'])
        ->assertSee('data-lightbox', false);
});

test('draft albums are hidden from visitors but previewable by admins', function () {
    $album = GalleryAlbum::factory()->draft()->create();

    $this->get(route('gallery.show', $album))->assertNotFound();
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('gallery.show', $album))
        ->assertOk()
        ->assertSee('Draft');
});

test('an admin creates an album with photos in the order they were uploaded', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateGalleryAlbum::class)
        ->fillForm([
            'title' => 'Grand Reunion 2025',
            'held_on' => '2025-12-20',
            'uploads' => [UploadedFile::fake()->image('first.jpg'), UploadedFile::fake()->image('second.jpg')],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $album = GalleryAlbum::query()->sole();

    expect($album->slug)->toBe('grand-reunion-2025')
        ->and($album->photos)->toHaveCount(2)
        ->and($album->photos->pluck('sort_order')->all())->toBe([1, 2]);

    $album->photos->each(fn (GalleryPhoto $photo) => Storage::disk('public')->assertExists($photo->image_path));
});

test('an admin can add more photos to an existing album', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $album = GalleryAlbum::factory()->create();
    GalleryPhoto::factory()->for($album, 'album')->create(['sort_order' => 1]);

    Livewire::test(PhotosRelationManager::class, ['ownerRecord' => $album, 'pageClass' => EditGalleryAlbum::class])
        ->callTableAction('upload', data: ['uploads' => [UploadedFile::fake()->image('more.jpg')]])
        ->assertHasNoTableActionErrors();

    expect($album->photos()->pluck('sort_order')->all())->toBe([1, 2]);
});

test('deleting a photo or an album removes the image files', function () {
    Storage::fake('public');
    $album = GalleryAlbum::factory()->create();
    $photos = collect(['a.jpg', 'b.jpg'])->map(function (string $name) use ($album) {
        $path = UploadedFile::fake()->image($name)->store('gallery', 'public');

        return GalleryPhoto::factory()->for($album, 'album')->create(['image_path' => $path]);
    });

    $photos->first()->delete();
    Storage::disk('public')->assertMissing($photos->first()->image_path);
    Storage::disk('public')->assertExists($photos->last()->image_path);

    $album->delete();
    Storage::disk('public')->assertMissing($photos->last()->image_path);
    expect(GalleryPhoto::query()->count())->toBe(0);
});
