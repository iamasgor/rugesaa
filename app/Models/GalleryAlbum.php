<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Database\Factories\GalleryAlbumFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * A program's photos: a reunion, a field day, a seminar.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property Carbon|null $held_on
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'slug', 'description', 'held_on', 'published_at'])]
class GalleryAlbum extends Model
{
    /** @use HasFactory<GalleryAlbumFactory> */
    use HasFactory, HasUniqueSlug;

    protected static function booted(): void
    {
        /*
         * Delete photos one by one, not by the foreign-key cascade, so each
         * photo's file is removed from storage too.
         */
        static::deleting(function (GalleryAlbum $album): void {
            $album->photos()->each(fn (GalleryPhoto $photo): ?bool => $photo->delete());
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'held_on' => 'date',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<GalleryPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The album's cover: its first photo in display order.
     *
     * @return HasOne<GalleryPhoto, $this>
     */
    public function coverPhoto(): HasOne
    {
        return $this->hasOne(GalleryPhoto::class)->ofMany(['sort_order' => 'min', 'id' => 'min']);
    }

    /**
     * Published albums, most recent program first.
     *
     * @param  Builder<GalleryAlbum>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('held_on')
            ->orderByDesc('published_at');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }
}
