<?php

namespace Database\Factories;

use App\Enums\EventRegistrationStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventRegistration>
 */
class EventRegistrationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'guests' => 0,
            'amount' => 0,
            'status' => EventRegistrationStatus::Confirmed,
            'confirmed_at' => now(),
        ];
    }

    public function pendingPayment(int $amount = 1500): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventRegistrationStatus::PendingPayment,
            'amount' => $amount,
            'confirmed_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventRegistrationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
