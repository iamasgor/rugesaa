<?php

namespace App\Filament\Resources\GalleryAlbums\Pages;

use App\Filament\Resources\GalleryAlbums\GalleryAlbumResource;
use App\Models\GalleryAlbum;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateGalleryAlbum extends CreateRecord
{
    protected static string $resource = GalleryAlbumResource::class;

    /**
     * Creates the album, then one photo per uploaded file in the order chosen.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        /** @var list<string> $uploads */
        $uploads = array_values(Arr::wrap($data['uploads'] ?? []));

        $album = GalleryAlbum::query()->create(Arr::except($data, 'uploads'));

        foreach ($uploads as $position => $path) {
            $album->photos()->create(['image_path' => $path, 'sort_order' => $position + 1]);
        }

        return $album;
    }

    protected function getRedirectUrl(): string
    {
        return GalleryAlbumResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
