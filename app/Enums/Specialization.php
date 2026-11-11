<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Specialization: string implements HasLabel
{
    case GisRemoteSensing = 'gis_remote_sensing';
    case PhysicalGeography = 'physical_geography';
    case AgriculturalGeography = 'agricultural_geography';
    case EnvironmentDisaster = 'environment_disaster';
    case CoastalEcosystems = 'coastal_ecosystems';
    case LandUseChange = 'land_use_change';
    case ClimateChange = 'climate_change';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::GisRemoteSensing => 'GIS & Remote Sensing',
            self::PhysicalGeography => 'Physical Geography',
            self::AgriculturalGeography => 'Agricultural Geography',
            self::EnvironmentDisaster => 'Environment & Disaster Management',
            self::CoastalEcosystems => 'Coastal Ecosystems',
            self::LandUseChange => 'Land Use Change',
            self::ClimateChange => 'Climate Change',
            self::Other => 'Other',
        };
    }
}
