<?php

namespace App\Models;

use Database\Factories\MembershipPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property list<string>|null $benefits
 * @property int $price
 * @property int|null $duration_months
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug', 'description', 'benefits', 'price', 'duration_months', 'is_active', 'sort_order'])]
class MembershipPlan extends Model
{
    /** @use HasFactory<MembershipPlanFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'price' => 'integer',
            'duration_months' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Membership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * @param  Builder<MembershipPlan>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }

    public function isFree(): bool
    {
        return $this->price === 0;
    }

    public function isLifetime(): bool
    {
        return $this->duration_months === null;
    }

    public function formattedPrice(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        $suffix = $this->isLifetime() ? 'once' : ($this->duration_months === 12 ? '/ year' : "/ {$this->duration_months} months");

        return 'BDT '.number_format($this->price).' '.$suffix;
    }
}
