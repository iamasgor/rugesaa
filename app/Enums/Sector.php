<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Sector: string implements HasLabel
{
    case Academia = 'academia';
    case Government = 'government';
    case Development = 'development';
    case PrivateSector = 'private';
    case SelfEmployed = 'self_employed';
    case Studying = 'studying';

    public function getLabel(): string
    {
        return match ($this) {
            self::Academia => 'Academia & research',
            self::Government => 'Government',
            self::Development => 'Development & NGOs',
            self::PrivateSector => 'Private sector',
            self::SelfEmployed => 'Self-employed',
            self::Studying => 'Studying',
        };
    }
}
