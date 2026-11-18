<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DonationFund: string implements HasLabel
{
    case Scholarship = 'scholarship';
    case FieldResearch = 'field_research';
    case Labs = 'labs';

    public function getLabel(): string
    {
        return match ($this) {
            self::Scholarship => 'Student scholarship fund',
            self::FieldResearch => 'Field research fund',
            self::Labs => 'Labs & campus fund',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Scholarship => 'Tuition and living support for students in need',
            self::FieldResearch => 'Travel, equipment and lodging for fieldwork',
            self::Labs => 'GIS workstations, map library and facilities',
        };
    }
}
