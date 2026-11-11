<?php

namespace App\Filament\Resources\Memberships\Tables;

use App\Enums\MembershipStatus;
use App\Filament\Resources\Memberships\Actions\MembershipReviewActions;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MembershipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user.profile', 'plan']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Member')
                    ->description(fn ($record): string => $record->user->email)
                    ->searchable(),
                TextColumn::make('user.profile.batch')
                    ->label('Batch')
                    ->searchable(),
                TextColumn::make('user.profile.student_id')
                    ->label('Student ID')
                    ->searchable(),
                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->description(fn ($record): string => $record->isRenewal() ? 'Renewal' : 'New member'),
                TextColumn::make('amount')
                    ->formatStateUsing(fn (int $state): string => 'BDT '.number_format($state))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('expires_at')
                    ->date()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(MembershipStatus::class)
                    ->default(MembershipStatus::PendingVerification->value),
                SelectFilter::make('membership_plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
                MembershipReviewActions::approve(),
                MembershipReviewActions::reject(),
            ]);
    }
}
