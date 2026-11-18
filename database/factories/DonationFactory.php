<?php

namespace Database\Factories;

use App\Enums\DonationFund;
use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Donation>
 */
class DonationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fund' => fake()->randomElement(DonationFund::cases()),
            'amount' => fake()->randomElement([1000, 5000, 10000, 25000]),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '01712345678',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => ['paid_at' => now()]);
    }
}
