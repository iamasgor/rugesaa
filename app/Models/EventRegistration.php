<?php

namespace App\Models;

use App\Contracts\Payable;
use App\Enums\EventRegistrationStatus;
use App\Notifications\EventRegistrationConfirmed;
use Database\Factories\EventRegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property int $guests
 * @property int $amount
 * @property EventRegistrationStatus $status
 * @property Carbon|null $confirmed_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['guests', 'amount', 'status'])]
class EventRegistration extends Model implements Payable
{
    /** @use HasFactory<EventRegistrationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'guests' => 'integer',
            'amount' => 'integer',
            'status' => EventRegistrationStatus::class,
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphMany<Payment, $this>
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function isConfirmed(): bool
    {
        return $this->status === EventRegistrationStatus::Confirmed;
    }

    public function needsPayment(): bool
    {
        return $this->status === EventRegistrationStatus::PendingPayment && $this->amount > 0;
    }

    /**
     * Unpaid and free registrations can be cancelled online; paid ones need a refund from the committee.
     */
    public function canBeCancelledByMember(): bool
    {
        return $this->status === EventRegistrationStatus::PendingPayment
            || ($this->isConfirmed() && $this->amount === 0);
    }

    public function confirm(): void
    {
        $this->forceFill([
            'status' => EventRegistrationStatus::Confirmed,
            'confirmed_at' => now(),
            'cancelled_at' => null,
        ])->save();

        $this->user->notify(new EventRegistrationConfirmed($this));
    }

    public function cancel(): void
    {
        $this->forceFill([
            'status' => EventRegistrationStatus::Cancelled,
            'cancelled_at' => now(),
        ])->save();
    }

    public function paymentAmount(): int
    {
        return $this->amount;
    }

    public function paymentDescription(): string
    {
        return 'RUGESAA event: '.$this->event->title;
    }

    public function paymentCategory(): string
    {
        return 'Event';
    }

    public function paymentReturnUrl(): string
    {
        return route('events.show', $this->event);
    }

    public function paymentSuccessMessage(): string
    {
        return "Payment received. You're registered — we've emailed your confirmation.";
    }

    /**
     * Money was taken, so confirm the seat even if the member cancelled while paying.
     */
    public function markAsPaid(Payment $payment): void
    {
        if (! $this->isConfirmed()) {
            $this->confirm();
        }
    }
}
