<?php

namespace App\Models;

use Database\Factories\GovernanceDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $title
 * @property string $file_path
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'file_path', 'sort_order'])]
class GovernanceDocument extends Model
{
    /** @use HasFactory<GovernanceDocumentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function fileExtension(): string
    {
        return strtoupper(pathinfo($this->file_path, PATHINFO_EXTENSION));
    }
}
