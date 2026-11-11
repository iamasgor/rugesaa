<?php

namespace App\Filament\Widgets;

use App\Enums\EventRegistrationStatus;
use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class EventInventory extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Upcoming events — seats & sales';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Event::query()
                ->upcoming()
                ->withCount(['registrations as held_registrations_count' => fn (Builder $query) => $query
                    ->whereIn('status', EventRegistrationStatus::holdingSeats())])
                ->withSum(['registrations as held_guests_sum' => fn (Builder $query) => $query
                    ->whereIn('status', EventRegistrationStatus::holdingSeats())], 'guests')
                ->withSum(['registrations as confirmed_amount_sum' => fn (Builder $query) => $query
                    ->where('status', EventRegistrationStatus::Confirmed)], 'amount')
                ->limit(6))
            ->paginated(false)
            ->columns([
                TextColumn::make('title')
                    ->label('Event')
                    ->description(fn (Event $record): string => $record->venue)
                    ->weight('semibold'),
                TextColumn::make('starts_at')
                    ->label('Date')
                    ->dateTime('D j M, g:i A'),
                TextColumn::make('seats')
                    ->label('Seats sold')
                    ->badge()
                    ->state(fn (Event $record): string => $record->capacity === null
                        ? self::seatsHeld($record).' / unlimited'
                        : self::seatsHeld($record).' / '.$record->capacity)
                    ->color(fn (Event $record): string => match (true) {
                        $record->capacity === null => 'gray',
                        self::seatsHeld($record) >= $record->capacity => 'danger',
                        self::seatsHeld($record) >= $record->capacity * 0.8 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('confirmed_amount_sum')
                    ->label('Sales')
                    ->formatStateUsing(fn (mixed $state): string => 'BDT '.number_format((int) $state))
                    ->placeholder('BDT 0')
                    ->alignEnd(),
                TextColumn::make('published_at')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Event $record): string => $record->isPublished() ? 'Live' : 'Draft')
                    ->color(fn (string $state): string => $state === 'Live' ? 'success' : 'gray'),
            ])
            ->recordUrl(fn (Event $record): string => EventResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('No upcoming events');
    }

    /**
     * Registrants plus their guests, matching Event::seatsTaken() without a query per row.
     */
    private static function seatsHeld(Event $event): int
    {
        return (int) $event->getAttribute('held_registrations_count') + (int) $event->getAttribute('held_guests_sum');
    }
}
