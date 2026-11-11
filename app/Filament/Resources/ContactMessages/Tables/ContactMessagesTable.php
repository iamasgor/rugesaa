<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Enums\ContactTopic;
use App\Models\ContactMessage;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('read_at')
                    ->label('Read')
                    ->state(fn (ContactMessage $record): bool => $record->read_at !== null)
                    ->boolean(),
                TextColumn::make('subject')
                    ->description(fn (ContactMessage $record): string => $record->name.' · '.$record->email)
                    ->searchable(['subject', 'name', 'email'])
                    ->weight(fn (ContactMessage $record): ?string => $record->read_at === null ? 'bold' : null),
                TextColumn::make('topic')->badge(),
                TextColumn::make('batch')->placeholder('—'),
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('topic')->options(ContactTopic::class),
                TernaryFilter::make('read_at')->label('Read')->nullable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
