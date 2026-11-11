<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Degree: string implements HasLabel
{
    case BscHons = 'bsc_hons';
    case Ms = 'ms';
    case MPhil = 'mphil';
    case Phd = 'phd';
    case CurrentStudent = 'current_student';

    public function getLabel(): string
    {
        return match ($this) {
            self::BscHons => 'BSc (Hons)',
            self::Ms => 'MS',
            self::MPhil => 'MPhil',
            self::Phd => 'PhD',
            self::CurrentStudent => 'Currently a student',
        };
    }
}
