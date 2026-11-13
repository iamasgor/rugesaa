<?php

namespace App\Models;

use App\Enums\SitePage;
use Database\Factories\PageContentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * The editable copy and images of one public page, stored as sections of fields.
 *
 * @property int $id
 * @property SitePage $page
 * @property array<string, array<string, mixed>> $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['page', 'content'])]
class PageContent extends Model
{
    /** @use HasFactory<PageContentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page' => SitePage::class,
            'content' => 'array',
        ];
    }
}
