<?php

namespace Database\Factories;

use App\Enums\NewsCategory;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(6),
            'category' => fake()->randomElement(NewsCategory::cases()),
            'excerpt' => fake()->sentence(15),
            'body' => fake()->paragraphs(4, true),
            'is_featured' => false,
            'published_at' => now()->subDays(fake()->numberBetween(1, 60)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['published_at' => null]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => ['published_at' => now()->addWeek()]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['is_featured' => true]);
    }
}
