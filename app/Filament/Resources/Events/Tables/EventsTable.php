<?php

namespace App\Filament\Resources\Events\Tables;

use App\Enums\EventRegistrationStatus;
use App\Enums\EventType;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'registrations as confirmed_count' => fn (Builder $query) => $query->where('status', EventRegistrationStatus::Confirmed),
            ]))
            ->columns([
                TextColumn::make('title')->searchable()->description(fn (Event $record): string => $record->venue),
                TextColumn::make('type')->badge(),
                TextColumn::make('starts_at')->dateTime('j M Y, g:i A')->sortable(),
                TextColumn::make('confirmed_count')->label('Confirmed'),
                TextColumn::make('capacity')->label('Seats')->placeholder('No limit'),
                IconColumn::make('is_featured')->label('Featured')->boolean(),
                TextColumn::make('published_at')->label('Published')->date()->placeholder('Draft'),
            ])
            ->filters([
                SelectFilter::make('type')->options(EventType::class),
                TernaryFilter::make('upcoming')
                    ->queries(
                        true: fn (Builder $query) => $query->where('starts_at', '>=', now()),
                        false: fn (Builder $query) => $query->where('starts_at', '<', now()),
                    ),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View on site')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Event $record): string => route('events.show', $record), shouldOpenInNewTab: true),
                EditAction::make(),
            ]);
    }
}
