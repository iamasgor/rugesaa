<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MembershipStatus: string implements HasColor, HasLabel
{
    case PendingPayment = 'pending_payment';
    case PendingVerification = 'pending_verification';
    case Active = 'active';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::PendingPayment => 'Awaiting payment',
            self::PendingVerification => 'Awaiting verification',
            self::Active => 'Active',
            self::Rejected => 'Rejected',
            self::Expired => 'Expired',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PendingPayment => 'warning',
            self::PendingVerification => 'info',
            self::Active => 'success',
            self::Rejected => 'danger',
            self::Expired => 'gray',
        };
    }
}
