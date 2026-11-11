<?php

namespace App\Filament\Resources\MembershipPlans\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MembershipPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (?string $state, callable $set, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                TextInput::make('description')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Repeater::make('benefits')
                    ->simple(TextInput::make('benefit')->required()->maxLength(120))
                    ->addActionLabel('Add benefit')
                    ->reorderable()
                    ->helperText('Listed on the Membership page. Only promise what the association offers today.')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Price (BDT)')
                    ->helperText('Enter 0 for a free plan. Changes apply to new registrations only.')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('BDT'),
                TextInput::make('duration_months')
                    ->label('Duration (months)')
                    ->helperText('Leave empty for a lifetime plan.')
                    ->integer()
                    ->minValue(1),
                TextInput::make('sort_order')
                    ->required()
                    ->integer()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Open for new registrations')
                    ->default(true),
            ]);
    }
}
