<?php

namespace App\Contracts;

use App\Models\Payment;

/**
 * A model that can be paid for through the payment gateway.
 */
interface Payable
{
    /**
     * The amount due, in whole BDT.
     */
    public function paymentAmount(): int;

    /**
     * A short product name shown on the gateway page.
     */
    public function paymentDescription(): string;

    /**
     * The gateway's product category, e.g. "Membership".
     */
    public function paymentCategory(): string;

    /**
     * Where the member lands after the gateway sends them back.
     */
    public function paymentReturnUrl(): string;

    /**
     * The flash message shown after a confirmed payment.
     */
    public function paymentSuccessMessage(): string;

    /**
     * Called once, inside a transaction, after the gateway confirms the payment.
     */
    public function markAsPaid(Payment $payment): void;
}
