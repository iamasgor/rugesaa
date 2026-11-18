<?php

namespace App\Filament\Resources\Donations\Tables;

use App\Enums\DonationFund;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DonationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Donor')
                    ->description(fn ($record): string => $record->email)
                    ->searchable(['name', 'email']),
                TextColumn::make('phone')
                    ->label('Mobile')
                    ->toggleable(),
                TextColumn::make('fund')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('amount')
                    ->formatStateUsing(fn (int $state): string => 'BDT '.number_format($state))
                    ->weight('semibold')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Member account')
                    ->placeholder('Guest')
                    ->toggleable(),
                TextColumn::make('paid_at')
                    ->label('Received')
                    ->dateTime()
                    ->placeholder('Not paid')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('fund')
                    ->options(DonationFund::class),
            ]);
    }
}
