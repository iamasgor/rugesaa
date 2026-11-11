<?php

namespace App\Models;

use App\Enums\ContactTopic;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property ContactTopic $topic
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $batch
 * @property string $subject
 * @property string $message
 * @property bool $newsletter_opt_in
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['topic', 'name', 'email', 'phone', 'batch', 'subject', 'message', 'newsletter_opt_in'])]
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'topic' => ContactTopic::class,
            'newsletter_opt_in' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }
}
