<?php

namespace App\Http\Controllers;

use App\Enums\EventRegistrationStatus;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['profile', 'currentMembership.plan', 'latestMembership.plan']);

        $current = $user->currentMembership;
        $latest = $user->latestMembership;

        return view('portal.index', [
            'user' => $user,
            'membership' => $current ?? $latest,
            'pending' => $latest !== null && ! $latest->is($current) && ! $latest->isCurrent() ? $latest : null,
            'canRenew' => $user->canRenewMembership(),
            'renewalPlans' => MembershipPlan::query()->active()->get(),
        ]);
    }

    public function events(Request $request): View
    {
        $registrations = $request->user()->eventRegistrations()
            ->with('event')
            ->whereIn('status', EventRegistrationStatus::holdingSeats())
            ->get()
            ->sortBy(fn ($registration) => $registration->event->starts_at);

        return view('portal.events', [
            'upcoming' => $registrations->filter(fn ($registration) => $registration->event->starts_at->isFuture()),
            'past' => $registrations->reject(fn ($registration) => $registration->event->starts_at->isFuture())->reverse(),
        ]);
    }
}
