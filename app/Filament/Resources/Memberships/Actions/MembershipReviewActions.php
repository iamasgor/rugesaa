<?php

namespace App\Filament\Resources\Memberships\Actions;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class MembershipReviewActions
{
    /**
     * Approve a member whose batch has been checked against department records.
     */
    public static function approve(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription('Confirm the batch and student ID match department records.')
            ->visible(fn (Membership $record): bool => $record->status === MembershipStatus::PendingVerification)
            ->action(function (Membership $record): void {
                /** @var User $admin */
                $admin = Auth::user();
                $record->approve($admin);

                Notification::make()->title('Membership approved')->success()->send();
            });
    }

    /**
     * Reject a registration that could not be verified, with a reason shown to the member.
     */
    public static function reject(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->schema([
                Textarea::make('reason')
                    ->label('Reason (shown to the member)')
                    ->required()
                    ->maxLength(255),
            ])
            ->visible(fn (Membership $record): bool => in_array($record->status, [MembershipStatus::PendingVerification, MembershipStatus::PendingPayment], true))
            ->action(function (Membership $record, array $data): void {
                /** @var User $admin */
                $admin = Auth::user();
                $record->reject($admin, $data['reason']);

                Notification::make()->title('Membership rejected')->success()->send();
            });
    }
}
