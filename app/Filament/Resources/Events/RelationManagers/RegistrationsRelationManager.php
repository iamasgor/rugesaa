<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Enums\EventRegistrationStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Attendees';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user.profile'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('Member')->searchable()->description(fn (EventRegistration $record): string => $record->user->email),
                TextColumn::make('user.profile.batch')->label('Batch'),
                TextColumn::make('user.profile.phone')->label('Mobile'),
                TextColumn::make('guests'),
                TextColumn::make('amount')->formatStateUsing(fn (int $state): string => $state === 0 ? 'Free' : 'BDT '.number_format($state)),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->label('Registered')->since(),
            ])
            ->filters([
                SelectFilter::make('status')->options(EventRegistrationStatus::class)->default(EventRegistrationStatus::Confirmed->value),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Download attendee list (CSV)')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(fn (): StreamedResponse => $this->downloadAttendees()),
            ])
            ->recordActions([
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Frees the seats. Refund any payment separately through SSLCommerz.')
                    ->visible(fn (EventRegistration $record): bool => $record->status !== EventRegistrationStatus::Cancelled)
                    ->action(function (EventRegistration $record): void {
                        $record->cancel();
                        Notification::make()->title('Registration cancelled')->success()->send();
                    }),
            ]);
    }

    private function downloadAttendees(): StreamedResponse
    {
        /** @var Event $event */
        $event = $this->getOwnerRecord();

        $registrations = $event->registrations()
            ->with('user.profile')
            ->where('status', EventRegistrationStatus::Confirmed)
            ->orderBy('created_at')
            ->get();

        return response()->streamDownload(function () use ($registrations): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fputcsv($output, ['Name', 'Email', 'Mobile', 'Batch', 'Guests', 'Paid (BDT)', 'Registered']);

            foreach ($registrations as $registration) {
                fputcsv($output, [
                    $registration->user->name,
                    $registration->user->email,
                    $registration->user->profile?->phone,
                    $registration->user->profile?->batch,
                    $registration->guests,
                    $registration->amount,
                    $registration->created_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($output);
        }, str($event->slug)->append('-attendees.csv')->toString(), ['Content-Type' => 'text/csv']);
    }
}
