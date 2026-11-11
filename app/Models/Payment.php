<?php

namespace App\Models;

use App\Contracts\Payable;
use App\Enums\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $payable_type
 * @property int $payable_id
 * @property int $amount
 * @property string $currency
 * @property string $tran_id
 * @property string|null $val_id
 * @property PaymentStatus $status
 * @property string|null $card_type
 * @property Carbon|null $paid_at
 * @property array<string, mixed>|null $gateway_response
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Payable&Model $payable
 */
#[Fillable(['amount', 'currency', 'tran_id', 'status'])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'gateway_response' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending;
    }
}
