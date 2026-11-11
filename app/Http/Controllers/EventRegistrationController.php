<?php

namespace App\Http\Controllers;

use App\Exceptions\EventRegistrationException;
use App\Exceptions\PaymentGatewayException;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\EventRegistrar;
use App\Services\SslCommerzGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventRegistrationController extends Controller
{
    public function __construct(
        private readonly EventRegistrar $registrar,
        private readonly SslCommerzGateway $gateway,
    ) {}

    public function store(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'guests' => ['nullable', 'integer', 'min:0', 'max:'.$event->max_guests],
        ]);

        try {
            $registration = $this->registrar->register($event, $request->user(), (int) ($validated['guests'] ?? 0));
        } catch (EventRegistrationException $exception) {
            return to_route('events.show', $event)->with('error', $exception->getMessage());
        }

        if ($registration->needsPayment()) {
            return $this->sendToGateway($request, $registration);
        }

        return to_route('events.show', $event)->with('status', "You're registered — we've emailed your confirmation.");
    }

    public function pay(Request $request, Event $event): RedirectResponse
    {
        $registration = $this->registrationFor($request, $event);

        if (! $registration->needsPayment()) {
            return to_route('events.show', $event);
        }

        if (! $event->isOpenForRegistration()) {
            return to_route('events.show', $event)->with('error', 'Registration for this event is closed.');
        }

        return $this->sendToGateway($request, $registration);
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        try {
            $this->registrar->cancel($this->registrationFor($request, $event));
        } catch (EventRegistrationException $exception) {
            return to_route('events.show', $event)->with('error', $exception->getMessage());
        }

        return to_route('events.show', $event)->with('status', 'Your registration was cancelled.');
    }

    private function registrationFor(Request $request, Event $event): EventRegistration
    {
        return EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    private function sendToGateway(Request $request, EventRegistration $registration): RedirectResponse
    {
        try {
            return redirect()->away($this->gateway->initiate($registration->load('event'), $request->user()));
        } catch (PaymentGatewayException) {
            return to_route('events.show', $registration->event)
                ->with('error', 'Your seat is held, but we could not start the payment. Please try "Complete payment" again in a few minutes.');
        }
    }
}
