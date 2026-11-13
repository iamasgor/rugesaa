<?php

namespace App\Filament\Resources\Members\Tables;

use App\Filament\Resources\Members\Actions\MemberPasswordActions;
use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['profile', 'currentMembership.plan']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->description(fn (User $record): string => $record->email)
                    ->searchable(['name', 'email'])
                    ->sortable(),
                TextColumn::make('profile.phone')
                    ->label('Phone')
                    ->searchable(),
                TextColumn::make('profile.batch')
                    ->label('Batch')
                    ->searchable(),
                TextColumn::make('profile.student_id')
                    ->label('Student ID')
                    ->searchable(),
                TextColumn::make('currentMembership.plan.name')
                    ->label('Membership')
                    ->placeholder('Not active')
                    ->badge()
                    ->color('success'),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    MemberPasswordActions::setPassword(),
                    MemberPasswordActions::sendResetLink(),
                ]),
            ]);
    }
}
