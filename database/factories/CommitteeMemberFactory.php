<?php

namespace Database\Factories;

use App\Models\CommitteeMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommitteeMember>
 */
class CommitteeMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'position' => fake()->randomElement(['Joint Secretary', 'Organizing Secretary', 'Events Secretary']),
            'batch' => '2001-02',
            'current_role' => fake()->jobTitle().', '.fake()->company(),
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['is_featured' => true]);
    }
}
