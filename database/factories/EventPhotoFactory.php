<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventPhoto>
 */
class EventPhotoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'image_path' => 'events/photos/'.fake()->uuid().'.jpg',
            'title' => fake()->optional()->sentence(3),
            'sort_order' => 0,
        ];
    }
}
