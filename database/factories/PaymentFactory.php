<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'payable_type' => (new Membership)->getMorphClass(),
            'payable_id' => Membership::factory(),
            'amount' => 5000,
            'currency' => 'BDT',
            'tran_id' => 'RUG'.Str::upper(Str::random(16)),
            'status' => PaymentStatus::Pending,
        ];
    }

    public function forPayable(Membership $membership): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $membership->user_id,
            'payable_type' => $membership->getMorphClass(),
            'payable_id' => $membership->id,
            'amount' => $membership->amount,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'card_type' => 'BKASH-BKash',
        ]);
    }
}
