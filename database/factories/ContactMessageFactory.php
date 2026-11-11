<?php

namespace Database\Factories;

use App\Enums\ContactTopic;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic' => fake()->randomElement(ContactTopic::cases()),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'newsletter_opt_in' => false,
        ];
    }
}
