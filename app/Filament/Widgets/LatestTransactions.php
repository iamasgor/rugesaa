<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestTransactions extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['lg' => 2];

    protected static ?string $heading = 'Latest transactions';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Payment::query()->with('user')->latest()->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('tran_id')
                    ->label('Transaction')
                    ->fontFamily('mono')
                    ->size('xs'),
                TextColumn::make('user.name')
                    ->label('Member'),
                TextColumn::make('payable_type')
                    ->label('Item')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => $state === 'membership' ? 'Membership' : 'Event'),
                TextColumn::make('amount')
                    ->formatStateUsing(fn (int $state): string => 'BDT '.number_format($state))
                    ->weight('semibold')
                    ->alignEnd(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('When')
                    ->since(),
            ])
            ->recordUrl(fn (Payment $record): string => PaymentResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all')
                    ->link()
                    ->url(PaymentResource::getUrl('index')),
            ])
            ->emptyStateHeading('No transactions yet');
    }
}
