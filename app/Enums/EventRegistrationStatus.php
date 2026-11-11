<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EventRegistrationStatus: string implements HasColor, HasLabel
{
    case PendingPayment = 'pending_payment';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::PendingPayment => 'Awaiting payment',
            self::Confirmed => 'Confirmed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PendingPayment => 'warning',
            self::Confirmed => 'success',
            self::Cancelled => 'gray',
        };
    }

    /**
     * Statuses that hold a seat at the event.
     *
     * @return list<self>
     */
    public static function holdingSeats(): array
    {
        return [self::PendingPayment, self::Confirmed];
    }
}
