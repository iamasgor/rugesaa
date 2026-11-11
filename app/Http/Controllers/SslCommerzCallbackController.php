<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Services\SslCommerzGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Handles the browser redirects and the server-to-server IPN that SSLCommerz posts back.
 * These are cross-site POSTs, so the session cookie may be missing: nothing here relies on auth.
 */
class SslCommerzCallbackController extends Controller
{
    public function __construct(private readonly SslCommerzGateway $gateway) {}

    public function success(Request $request): RedirectResponse
    {
        $payment = $this->gateway->confirm((string) $request->input('tran_id'), $request->input('val_id'));

        $returnUrl = $payment?->payable?->paymentReturnUrl() ?? route('portal');

        if ($payment?->status === PaymentStatus::Paid) {
            return redirect($returnUrl)->with('status', $payment->payable->paymentSuccessMessage());
        }

        return redirect($returnUrl)->with('error', 'We could not confirm your payment yet. If money was deducted, it will be confirmed automatically shortly.');
    }

    public function fail(Request $request): RedirectResponse
    {
        $payment = $this->gateway->close((string) $request->input('tran_id'), PaymentStatus::Failed);

        return redirect($payment?->payable?->paymentReturnUrl() ?? route('portal'))->with('error', 'The payment failed. No money was taken. You can try again.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $payment = $this->gateway->close((string) $request->input('tran_id'), PaymentStatus::Cancelled);

        return redirect($payment?->payable?->paymentReturnUrl() ?? route('portal'))->with('error', 'The payment was cancelled. You can pay again any time.');
    }

    public function ipn(Request $request): Response
    {
        $this->gateway->confirm((string) $request->input('tran_id'), $request->input('val_id'));

        return response()->noContent();
    }
}
