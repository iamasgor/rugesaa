<?php

namespace App\Models;

use App\Contracts\Payable;
use App\Enums\DonationFund;
use App\Notifications\DonationReceived;
use Database\Factories\DonationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

/**
 * A one-off gift to one of the association's funds, from a member or a visitor.
 *
 * @property int $id
 * @property int|null $user_id
 * @property DonationFund $fund
 * @property int $amount
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['fund', 'amount', 'name', 'email', 'phone'])]
class Donation extends Model implements Payable
{
    /** @use HasFactory<DonationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fund' => DonationFund::class,
            'amount' => 'integer',
            'paid_at' => 'datetime',
        ];
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

    /**
     * @param  Builder<Donation>  $query
     */
    public function scopePaid(Builder $query): void
    {
        $query->whereNotNull('paid_at');
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function paymentAmount(): int
    {
        return $this->amount;
    }

    public function paymentDescription(): string
    {
        return 'RUGESAA donation: '.$this->fund->getLabel();
    }

    public function paymentCategory(): string
    {
        return 'Donation';
    }

    public function paymentReturnUrl(): string
    {
        return route('donate');
    }

    public function paymentSuccessMessage(): string
    {
        return 'Thank you! Your gift of BDT '.number_format($this->amount).' to the '.$this->fund->getLabel().' was received. A receipt is on its way to '.$this->email.'.';
    }

    public function markAsPaid(Payment $payment): void
    {
        if ($this->isPaid()) {
            return;
        }

        $this->forceFill(['paid_at' => now()])->save();

        Notification::route('mail', [$this->email => $this->name])->notify(new DonationReceived($this));
    }
}
