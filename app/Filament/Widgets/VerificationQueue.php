<?php

namespace App\Filament\Widgets;

use App\Enums\MembershipStatus;
use App\Filament\Resources\Memberships\MembershipResource;
use App\Models\Membership;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class VerificationQueue extends TableWidget
{
    protected static ?int $sort = 5;

    protected static ?string $heading = 'Verification queue';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Membership::query()
                ->with(['user.profile', 'plan'])
                ->where('status', MembershipStatus::PendingVerification)
                ->oldest()
                ->limit(6))
            ->paginated(false)
            ->columns([
                TextColumn::make('user.name')
                    ->label('Member')
                    ->description(fn (Membership $record): string => 'Batch '.($record->user->profile->batch ?? '—')),
                TextColumn::make('plan.name')
                    ->label('Plan'),
                TextColumn::make('created_at')
                    ->label('Waiting')
                    ->since(),
            ])
            ->recordUrl(fn (Membership $record): string => MembershipResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('All caught up')
            ->emptyStateDescription('No memberships are waiting for verification.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
