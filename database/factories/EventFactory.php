<?php

namespace Database\Factories;

use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(4),
            'type' => fake()->randomElement(EventType::cases()),
            'summary' => fake()->sentence(12),
            'description' => fake()->paragraphs(2, true),
            'starts_at' => now()->addWeeks(3)->setTime(10, 0),
            'venue' => 'Department seminar room, University of Rajshahi',
            'member_fee' => 0,
            'guest_fee' => 0,
            'max_guests' => 0,
            'is_featured' => false,
            'published_at' => now()->subDay(),
        ];
    }

    public function paid(int $memberFee = 1500, int $guestFee = 1000, int $maxGuests = 2): static
    {
        return $this->state(fn (array $attributes) => [
            'member_fee' => $memberFee,
            'guest_fee' => $guestFee,
            'max_guests' => $maxGuests,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['published_at' => null]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['is_featured' => true]);
    }

    public function past(): static
    {
        return $this->state(fn (array $attributes) => ['starts_at' => now()->subMonth()]);
    }
}
