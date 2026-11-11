<?php

namespace App\Models;

use App\Contracts\Payable;
use App\Enums\MembershipStatus;
use App\Notifications\MembershipActivated;
use App\Notifications\MembershipRejected;
use Carbon\CarbonInterface;
use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $membership_plan_id
 * @property int|null $renewal_of_id
 * @property int $amount
 * @property MembershipStatus $status
 * @property Carbon|null $starts_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $expiry_reminder_sent_at
 * @property Carbon|null $verified_at
 * @property int|null $verified_by
 * @property string|null $rejection_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['membership_plan_id', 'renewal_of_id', 'amount', 'status', 'starts_at', 'expires_at', 'rejection_reason'])]
class Membership extends Model implements Payable
{
    /** @use HasFactory<MembershipFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => MembershipStatus::class,
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'expiry_reminder_sent_at' => 'datetime',
            'verified_at' => 'datetime',
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
     * @return BelongsTo<MembershipPlan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * The earlier, already verified membership this one renews.
     *
     * @return BelongsTo<Membership, $this>
     */
    public function renewalOf(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'renewal_of_id');
    }

    /**
     * Active memberships that have not run out yet (the daily expiry job may not have run).
     *
     * @param  Builder<Membership>  $query
     */
    public function scopeCurrent(Builder $query): void
    {
        $query->where('status', MembershipStatus::Active)
            ->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /**
     * @return MorphMany<Payment, $this>
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function isRenewal(): bool
    {
        return $this->renewal_of_id !== null;
    }

    public function isCurrent(): bool
    {
        return $this->status === MembershipStatus::Active
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function needsPayment(): bool
    {
        return $this->status === MembershipStatus::PendingPayment && $this->amount > 0;
    }

    public function paymentAmount(): int
    {
        return $this->amount;
    }

    public function paymentDescription(): string
    {
        return 'RUGESAA '.$this->plan->name.' membership';
    }

    public function paymentCategory(): string
    {
        return 'Membership';
    }

    public function paymentReturnUrl(): string
    {
        return route('portal');
    }

    public function paymentSuccessMessage(): string
    {
        return $this->isRenewal()
            ? 'Payment received. Your membership has been renewed.'
            : 'Payment received. We will verify your batch and activate your membership.';
    }

    /**
     * New members wait for the committee to verify their batch; renewals were verified before, so they activate at once.
     */
    public function markAsPaid(Payment $payment): void
    {
        if ($this->status !== MembershipStatus::PendingPayment) {
            return;
        }

        if ($this->isRenewal()) {
            $this->activateRenewal();

            return;
        }

        $this->update(['status' => MembershipStatus::PendingVerification]);
    }

    public function approve(User $admin): void
    {
        $this->activate(startsAt: now(), verifiedBy: $admin);
    }

    /**
     * A renewal starts when the membership it renews runs out, so paying early loses no time.
     * Upgrading to a lifetime plan starts at once.
     */
    public function activateRenewal(): void
    {
        $previousExpiry = $this->renewalOf?->expires_at;

        $startsAt = ! $this->plan->isLifetime() && $previousExpiry !== null && $previousExpiry->isFuture()
            ? $previousExpiry
            : now();

        $this->activate(startsAt: $startsAt, verifiedBy: null);
    }

    private function activate(CarbonInterface $startsAt, ?User $verifiedBy): void
    {
        $this->forceFill([
            'status' => MembershipStatus::Active,
            'starts_at' => $startsAt,
            'expires_at' => $this->plan->isLifetime() ? null : $startsAt->copy()->addMonths($this->plan->duration_months),
            'verified_at' => now(),
            'verified_by' => $verifiedBy?->id,
            'rejection_reason' => null,
        ])->save();

        $this->user->notify(new MembershipActivated($this));
    }

    public function reject(User $admin, string $reason): void
    {
        $this->forceFill([
            'status' => MembershipStatus::Rejected,
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'rejection_reason' => $reason,
        ])->save();

        $this->user->notify(new MembershipRejected($this));
    }
}
