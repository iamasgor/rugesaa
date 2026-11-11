<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EventType: string implements HasLabel
{
    case Reunion = 'reunion';
    case Webinar = 'webinar';
    case FieldDay = 'field_day';
    case Career = 'career';

    public function getLabel(): string
    {
        return match ($this) {
            self::Reunion => 'Reunion',
            self::Webinar => 'Webinar',
            self::FieldDay => 'Field day',
            self::Career => 'Career & mentoring',
        };
    }

    /**
     * Plural label used for the filter tabs on the events page.
     */
    public function pluralLabel(): string
    {
        return match ($this) {
            self::Reunion => 'Reunions',
            self::Webinar => 'Webinars',
            self::FieldDay => 'Field days',
            self::Career => 'Career & mentoring',
        };
    }

    /**
     * Tag background and text colours from the design.
     *
     * @return array{0: string, 1: string}
     */
    public function tagColors(): array
    {
        return match ($this) {
            self::Reunion => ['#F4E3C1', '#5E3D07'],
            self::Webinar => ['#D6E4EC', '#1E4A63'],
            self::FieldDay => ['#DCE8D9', '#24502E'],
            self::Career => ['#E8DDEB', '#4E2E5A'],
        };
    }
}
