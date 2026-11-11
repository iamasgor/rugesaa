<?php

namespace App\Models;

use App\Enums\Degree;
use App\Enums\Sector;
use App\Enums\Specialization;
use Database\Factories\MemberProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $user_id
 * @property string $phone
 * @property string|null $photo_path
 * @property Degree $degree
 * @property string $batch
 * @property string $student_id
 * @property string|null $hall
 * @property Specialization $specialization
 * @property string|null $position
 * @property string|null $organization
 * @property Sector $sector
 * @property string|null $location
 * @property bool $mentor_opt_in
 * @property bool $show_in_directory
 * @property bool $newsletter_opt_in
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'phone', 'photo_path', 'degree', 'batch', 'student_id', 'hall', 'specialization',
    'position', 'organization', 'sector', 'location', 'mentor_opt_in', 'show_in_directory', 'newsletter_opt_in',
])]
class MemberProfile extends Model
{
    /** @use HasFactory<MemberProfileFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'degree' => Degree::class,
            'specialization' => Specialization::class,
            'sector' => Sector::class,
            'mentor_opt_in' => 'boolean',
            'show_in_directory' => 'boolean',
            'newsletter_opt_in' => 'boolean',
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
     * Profiles that members can see in the directory: opted in and with an active membership.
     *
     * @param  Builder<MemberProfile>  $query
     */
    public function scopeListedInDirectory(Builder $query): void
    {
        $query->where('show_in_directory', true)
            ->whereHas('user.memberships', fn (Builder $membership) => $membership->current());
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    /**
     * Position and organization joined for display, e.g. "GIS Analyst · CEGIS".
     */
    public function workSummary(): ?string
    {
        $summary = collect([$this->position, $this->organization])->filter()->join(' · ');

        return $summary !== '' ? $summary : null;
    }
}
