<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Fills an empty `slug` from the `title` on save, adding -2, -3… when it is taken.
 *
 * @mixin Model
 */
trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::saving(function (self $model): void {
            if (blank($model->getAttribute('slug'))) {
                $model->setAttribute('slug', $model->uniqueSlugFor((string) $model->getAttribute('title')));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function uniqueSlugFor(string $title): string
    {
        $base = Str::slug($title) ?: Str::lower(class_basename($this));
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
