<?php

namespace App\Filament\Resources\Members\Actions;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class MemberPasswordActions
{
    /**
     * Set a new password for a member, e.g. when they ask the committee over the phone.
     */
    public static function setPassword(): Action
    {
        return Action::make('setPassword')
            ->label('Reset password')
            ->icon(Heroicon::OutlinedKey)
            ->color('warning')
            ->modalHeading(fn (User $record): string => 'Set a new password for '.$record->name)
            ->modalDescription('Share the new password with the member. They can change it later from the portal.')
            ->schema([
                TextInput::make('password')
                    ->label('New password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->rule(PasswordRule::default())
                    ->confirmed(),
                TextInput::make('password_confirmation')
                    ->label('Confirm new password')
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->action(function (User $record, array $data): void {
                $record->forceFill([
                    'password' => $data['password'],
                    'email_verified_at' => $record->email_verified_at ?? now(),
                ])->save();

                Notification::make()->title('Password updated')->success()->send();
            });
    }

    /**
     * Email the member a link to choose their own password.
     */
    public static function sendResetLink(): Action
    {
        return Action::make('sendResetLink')
            ->label('Email reset link')
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('gray')
            ->requiresConfirmation()
            ->modalDescription(fn (User $record): string => 'A password reset link will be sent to '.$record->email.'.')
            ->action(function (User $record): void {
                $status = Password::sendResetLink(['email' => $record->email]);

                $notification = Notification::make()->title(__($status));

                $status === Password::RESET_LINK_SENT ? $notification->success() : $notification->danger();

                $notification->send();
            });
    }
}
