<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('tran_id')
                    ->label('Transaction')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('user.name')
                    ->label('Member')
                    ->searchable(),
                TextColumn::make('payable_type')
                    ->label('For')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('amount')
                    ->formatStateUsing(fn (int $state): string => 'BDT '.number_format($state))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('card_type')
                    ->label('Method')
                    ->placeholder('—'),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PaymentStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
