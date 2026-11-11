<?php

namespace App\Services;

use App\Enums\EventRegistrationStatus;
use App\Exceptions\EventRegistrationException;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EventRegistrar
{
    /**
     * Register a member (plus guests) for an event. Free registrations are confirmed at once;
     * paid ones hold their seats while the member pays.
     *
     * @throws EventRegistrationException
     */
    public function register(Event $event, User $user, int $guests): EventRegistration
    {
        if (! $user->hasMemberAccess()) {
            throw new EventRegistrationException('Event registration opens once your membership is active.');
        }

        if (! $event->isOpenForRegistration()) {
            throw new EventRegistrationException('Registration for this event is closed.');
        }

        if ($guests < 0 || $guests > $event->max_guests) {
            throw new EventRegistrationException($event->max_guests === 0
                ? 'This event is for members only, without guests.'
                : "You can bring up to {$event->max_guests} ".str('guest')->plural($event->max_guests).'.');
        }

        return DB::transaction(function () use ($event, $user, $guests): EventRegistration {
            // Lock the event row so two members cannot take the last seat at once.
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);

            $registration = EventRegistration::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($registration?->isConfirmed()) {
                throw new EventRegistrationException("You're already registered for this event.");
            }

            if ($event->capacity !== null && $event->seatsTaken(exceptUserId: $user->id) + 1 + $guests > $event->capacity) {
                throw new EventRegistrationException('Sorry, there are not enough seats left for your booking.');
            }

            $registration ??= new EventRegistration;
            $registration->event()->associate($event);
            $registration->user()->associate($user);
            $registration->fill([
                'guests' => $guests,
                'amount' => $event->feeFor($guests),
                'status' => EventRegistrationStatus::PendingPayment,
            ]);
            $registration->cancelled_at = null;
            $registration->save();

            if ($registration->amount === 0) {
                $registration->confirm();
            }

            return $registration;
        });
    }

    /**
     * @throws EventRegistrationException
     */
    public function cancel(EventRegistration $registration): void
    {
        if (! $registration->canBeCancelledByMember()) {
            throw new EventRegistrationException('Paid registrations can only be cancelled by the committee. Please contact us about a refund.');
        }

        $registration->cancel();
    }
}
