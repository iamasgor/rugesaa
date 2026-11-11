<?php

namespace Database\Factories;

use App\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MembershipPlan>
 */
class MembershipPlanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word().' '.fake()->word();

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price' => 5000,
            'duration_months' => 12,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => ['price' => 0]);
    }

    public function lifetime(): static
    {
        return $this->state(fn (array $attributes) => ['price' => 10000, 'duration_months' => null]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
