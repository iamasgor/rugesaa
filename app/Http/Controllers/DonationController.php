<?php

namespace App\Http\Controllers;

use App\Enums\DonationFund;
use App\Exceptions\PaymentGatewayException;
use App\Http\Requests\StoreDonationRequest;
use App\Models\Donation;
use App\Services\SslCommerzGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function __construct(private readonly SslCommerzGateway $gateway) {}

    public function show(Request $request): View
    {
        $user = $request->user()?->loadMissing('profile');

        return view('pages.donate', [
            'funds' => DonationFund::cases(),
            'presetAmounts' => StoreDonationRequest::PRESET_AMOUNTS,
            'minimumAmount' => StoreDonationRequest::MINIMUM_AMOUNT,
            'maximumAmount' => StoreDonationRequest::MAXIMUM_AMOUNT,
            'donor' => [
                'name' => $user->name ?? '',
                'email' => $user->email ?? '',
                'phone' => $user->profile->phone ?? '',
            ],
        ]);
    }

    /**
     * Record the pledge and send the donor to SSLCommerz to pay it.
     */
    public function store(StoreDonationRequest $request): RedirectResponse
    {
        $donation = new Donation([
            ...$request->safe()->only(['fund', 'name', 'email', 'phone']),
            'amount' => $request->donationAmount(),
        ]);
        $donation->user()->associate($request->user());
        $donation->save();

        try {
            return redirect()->away($this->gateway->initiate($donation, $request->user(), [
                'name' => $donation->name,
                'email' => $donation->email,
                'phone' => $donation->phone,
            ]));
        } catch (PaymentGatewayException) {
            return to_route('donate')
                ->withInput()
                ->with('error', 'We could not start the payment. Please try again in a few minutes.');
        }
    }
}
