<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentGatewayException;
use App\Models\Membership;
use App\Services\SslCommerzGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MembershipCheckoutController extends Controller
{
    public function show(Request $request, Membership $membership): View|RedirectResponse
    {
        Gate::authorize('pay', $membership);

        if (! $membership->needsPayment()) {
            return to_route('portal');
        }

        return view('memberships.checkout', [
            'membership' => $membership->load('plan'),
        ]);
    }

    public function store(Request $request, Membership $membership, SslCommerzGateway $gateway): RedirectResponse
    {
        Gate::authorize('pay', $membership);

        if (! $membership->needsPayment()) {
            return to_route('portal');
        }

        try {
            $gatewayPageUrl = $gateway->initiate($membership->load('plan'), $request->user());
        } catch (PaymentGatewayException) {
            return back()->with('error', 'We could not start the payment right now. Please try again in a few minutes.');
        }

        return redirect()->away($gatewayPageUrl);
    }
}
