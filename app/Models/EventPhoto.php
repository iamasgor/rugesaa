<?php

namespace App\Models;

use Database\Factories\EventPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * A photo taken at an event, shown on the event's page.
 *
 * @property int $id
 * @property int $event_id
 * @property string $image_path
 * @property string|null $title
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['event_id', 'image_path', 'title', 'sort_order'])]
class EventPhoto extends Model
{
    /** @use HasFactory<EventPhotoFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleted(function (EventPhoto $photo): void {
            Storage::disk('public')->delete($photo->image_path);
        });

        /* A replaced image leaves no orphaned file behind. */
        static::updated(function (EventPhoto $photo): void {
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
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
