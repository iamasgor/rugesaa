<?php

namespace App\Models;

use Database\Factories\CommitteeMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $name
 * @property string $position
 * @property string|null $batch
 * @property string|null $current_role
 * @property string|null $photo_path
 * @property bool $is_featured
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'position', 'batch', 'current_role', 'photo_path', 'is_featured', 'sort_order'])]
class CommitteeMember extends Model
{
    /** @use HasFactory<CommitteeMemberFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<CommitteeMember>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }
}
