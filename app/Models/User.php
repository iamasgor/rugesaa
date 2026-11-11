<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property bool $is_admin
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * @return HasOne<MemberProfile, $this>
     */
    public function profile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    /**
     * @return HasMany<Membership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * @return HasOne<Membership, $this>
     */
    public function latestMembership(): HasOne
    {
        return $this->hasOne(Membership::class)->latestOfMany();
    }

    /**
     * @return HasMany<EventRegistration, $this>
     */
    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The membership in force right now, preferring the one that runs longest.
     *
     * @return HasOne<Membership, $this>
     */
    public function currentMembership(): HasOne
    {
        return $this->hasOne(Membership::class)->ofMany(
            ['id' => 'max'],
            fn (Builder $query) => $query->scopes('current'),
        );
    }

    public function isActiveMember(): bool
    {
        return $this->currentMembership()->exists();
    }

    /**
     * Members who were verified before can renew themselves, once the current
     * membership is within 30 days of running out (or has run out). Life members never need to.
     */
    public function canRenewMembership(): bool
    {
        $current = $this->currentMembership;

        if ($current !== null && ($current->expires_at === null || $current->expires_at->isAfter(now()->addDays(30)))) {
            return false;
        }

        $hasPendingRenewal = $this->memberships()
            ->whereNotNull('renewal_of_id')
            ->where('status', MembershipStatus::PendingPayment)
            ->exists();

        return ! $hasPendingRenewal && $this->lastVerifiedMembership() !== null;
    }

    public function lastVerifiedMembership(): ?Membership
    {
        return $this->memberships()
            ->whereIn('status', [MembershipStatus::Active, MembershipStatus::Expired])
            ->latest('id')
            ->first();
    }

    /**
     * The member number shown in the portal, e.g. RUGESAA-0042.
     */
    public function memberNumber(): string
    {
        return 'RUGESAA-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Active members (and admins) can use the directory and register for events.
     */
    public function hasMemberAccess(): bool
    {
        return $this->is_admin || $this->isActiveMember();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin && $this->hasVerifiedEmail();
    }
}
