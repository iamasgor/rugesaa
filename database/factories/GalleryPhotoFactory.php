<?php

namespace Database\Factories;

use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryPhoto>
 */
class GalleryPhotoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gallery_album_id' => GalleryAlbum::factory(),
            'image_path' => 'gallery/'.fake()->uuid().'.jpg',
            'title' => fake()->optional()->sentence(3),
            'sort_order' => 0,
        ];
    }
}
