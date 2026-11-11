<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NewsCategory: string implements HasLabel
{
    case AlumniImpact = 'alumni_impact';
    case Department = 'department';
    case Association = 'association';
    case Events = 'events';
    case Profile = 'profile';
    case Giving = 'giving';

    public function getLabel(): string
    {
        return match ($this) {
            self::AlumniImpact => 'Alumni impact',
            self::Department => 'Department',
            self::Association => 'Association',
            self::Events => 'Events',
            self::Profile => 'Profiles',
            self::Giving => 'Giving',
        };
    }
}
