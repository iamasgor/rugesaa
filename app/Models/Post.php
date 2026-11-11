<?php

namespace App\Models;

use App\Enums\NewsCategory;
use App\Models\Concerns\HasUniqueSlug;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int|null $author_id
 * @property string $title
 * @property string $slug
 * @property NewsCategory $category
 * @property string $excerpt
 * @property string $body
 * @property string|null $cover_image_path
 * @property string|null $cover_image_alt
 * @property bool $is_featured
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['author_id', 'title', 'slug', 'category', 'excerpt', 'body', 'cover_image_path', 'cover_image_alt', 'is_featured', 'published_at'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasUniqueSlug;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => NewsCategory::class,
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Published posts, newest first.
     *
     * @param  Builder<Post>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    public function coverImageUrl(): ?string
    {
        return $this->cover_image_path ? Storage::disk('public')->url($this->cover_image_path) : null;
    }

    /**
     * Rough reading time at 200 words a minute, at least one minute.
     */
    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->body)) / 200));
    }
}
