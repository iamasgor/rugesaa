<?php

namespace Database\Factories;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'membership_plan_id' => MembershipPlan::factory(),
            'amount' => 5000,
            'status' => MembershipStatus::PendingPayment,
        ];
    }

    public function pendingVerification(): static
    {
        return $this->state(fn (array $attributes) => ['status' => MembershipStatus::PendingVerification]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MembershipStatus::Expired,
            'starts_at' => now()->subYear()->subWeek(),
            'expires_at' => now()->subWeek(),
            'verified_at' => now()->subYear(),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MembershipStatus::Active,
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
            'verified_at' => now(),
        ]);
    }
}
