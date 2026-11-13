<?php

namespace App\Models;

use Database\Factories\GalleryPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $gallery_album_id
 * @property string $image_path
 * @property string|null $title
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['gallery_album_id', 'image_path', 'title', 'sort_order'])]
class GalleryPhoto extends Model
{
    /** @use HasFactory<GalleryPhotoFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleted(function (GalleryPhoto $photo): void {
            Storage::disk('public')->delete($photo->image_path);
        });

        /* A replaced image leaves no orphaned file behind. */
        static::updated(function (GalleryPhoto $photo): void {
            if ($photo->wasChanged('image_path') && filled($photo->getOriginal('image_path'))) {
                Storage::disk('public')->delete($photo->getOriginal('image_path'));
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<GalleryAlbum, $this>
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
