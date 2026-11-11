<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContactTopic: string implements HasLabel
{
    case General = 'general';
    case Membership = 'membership';
    case Events = 'events';
    case Donations = 'donations';
    case Profile = 'profile';
    case Mentoring = 'mentoring';

    public function getLabel(): string
    {
        return match ($this) {
            self::General => 'General enquiry',
            self::Membership => 'Membership & registration',
            self::Events => 'Events & reunions',
            self::Donations => 'Donations',
            self::Profile => 'Update my profile',
            self::Mentoring => 'Mentoring & jobs',
        };
    }
}
