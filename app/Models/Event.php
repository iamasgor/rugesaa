<?php

namespace App\Models;

use App\Enums\EventRegistrationStatus;
use App\Enums\EventType;
use App\Models\Concerns\HasUniqueSlug;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property EventType $type
 * @property string $summary
 * @property string|null $description
 * @property list<array{time: string, label: string}>|null $schedule
 * @property string|null $cover_image_path
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 * @property string $venue
 * @property string|null $online_url
 * @property int $member_fee
 * @property int $guest_fee
 * @property int $max_guests
 * @property int|null $capacity
 * @property Carbon|null $registration_closes_at
 * @property bool $is_featured
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'title', 'slug', 'type', 'summary', 'description', 'schedule', 'cover_image_path', 'starts_at', 'ends_at',
    'venue', 'online_url', 'member_fee', 'guest_fee', 'max_guests', 'capacity', 'registration_closes_at',
    'is_featured', 'published_at',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, HasUniqueSlug;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => EventType::class,
            'schedule' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'registration_closes_at' => 'datetime',
            'published_at' => 'datetime',
            'member_fee' => 'integer',
            'guest_fee' => 'integer',
            'max_guests' => 'integer',
            'capacity' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * @return HasMany<EventRegistration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * @param  Builder<Event>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Events that have not finished yet, soonest first.
     *
     * @param  Builder<Event>  $query
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where(fn (Builder $query) => $query
            ->where('starts_at', '>=', now())
            ->orWhere('ends_at', '>=', now()))
            ->orderBy('starts_at');
    }

    /**
     * @param  Builder<Event>  $query
     */
    public function scopePast(Builder $query): void
    {
        $query->where('starts_at', '<', now())
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '<', now()))
            ->orderByDesc('starts_at');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    public function isFree(): bool
    {
        return $this->member_fee === 0 && $this->guest_fee === 0;
    }

    public function isOpenForRegistration(): bool
    {
        $closesAt = $this->registration_closes_at ?? $this->starts_at;

        return $this->isPublished() && $closesAt->isFuture();
    }

    public function feeFor(int $guests): int
    {
        return $this->member_fee + ($guests * $this->guest_fee);
    }

    /**
     * Seats held by confirmed and unpaid registrations, counting each registrant plus their guests.
     */
    public function seatsTaken(?int $exceptUserId = null): int
    {
        return (int) $this->registrations()
            ->whereIn('status', EventRegistrationStatus::holdingSeats())
            ->when($exceptUserId, fn (Builder $query) => $query->where('user_id', '!=', $exceptUserId))
            ->sum(DB::raw('1 + guests'));
    }

    public function seatsLeft(): ?int
    {
        return $this->capacity === null ? null : max(0, $this->capacity - $this->seatsTaken());
    }

    public function coverImageUrl(): ?string
    {
        return $this->cover_image_path ? Storage::disk('public')->url($this->cover_image_path) : null;
    }

    public function whenLabel(): string
    {
        if ($this->ends_at !== null && ! $this->ends_at->isSameDay($this->starts_at)) {
            return $this->starts_at->format('D j M').' – '.$this->ends_at->format('D j M');
        }

        return $this->starts_at->format('D j M · g:i A');
    }

    public function feeLabel(): string
    {
        if ($this->isFree()) {
            return 'Free for members';
        }

        $label = 'BDT '.number_format($this->member_fee).' per member';

        if ($this->max_guests > 0 && $this->guest_fee > 0) {
            $label .= ', BDT '.number_format($this->guest_fee).' per guest';
        }

        return $label;
    }
}
