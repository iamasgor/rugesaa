<?php

namespace App\Filament\Resources\MembershipPlans\Tables;

use App\Models\MembershipPlan;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembershipPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->description(fn (MembershipPlan $record): ?string => $record->description),
                TextColumn::make('price')
                    ->label('Price')
                    ->formatStateUsing(fn (MembershipPlan $record): string => $record->formattedPrice()),
                TextColumn::make('memberships_count')
                    ->label('Members')
                    ->counts('memberships'),
                IconColumn::make('is_active')
                    ->label('Open')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
