<?php

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Models\Membership;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();

    $this->membership = Membership::factory()->create(['amount' => 5000]);
    $this->payment = Payment::factory()->forPayable($this->membership)->create(['tran_id' => 'RUGTEST123']);
});

function fakeValidation(array $overrides = []): void
{
    Http::fake([
        'sandbox.sslcommerz.com/validator/*' => Http::response(array_merge([
            'status' => 'VALID',
            'tran_id' => 'RUGTEST123',
            'amount' => '5000.00',
            'currency_type' => 'BDT',
            'currency_amount' => '5000.00',
            'card_type' => 'BKASH-BKash',
        ], $overrides)),
    ]);
}

test('a validated success marks the payment paid and queues the member for verification', function () {
    fakeValidation();

    $this->post(route('payments.sslcommerz.success'), ['tran_id' => 'RUGTEST123', 'val_id' => 'VAL1'])
        ->assertRedirect(route('portal'))
        ->assertSessionHas('status');

    $payment = $this->payment->fresh();

    expect($payment->status)->toBe(PaymentStatus::Paid)
        ->and($payment->val_id)->toBe('VAL1')
        ->and($payment->card_type)->toBe('BKASH-BKash')
        ->and($this->membership->fresh()->status)->toBe(MembershipStatus::PendingVerification);
});

test('a success post is not trusted when the validation API disagrees', function (array $validation) {
    fakeValidation($validation);

    $this->post(route('payments.sslcommerz.success'), ['tran_id' => 'RUGTEST123', 'val_id' => 'VAL1'])
        ->assertSessionHas('error');

    expect($this->payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and($this->membership->fresh()->status)->toBe(MembershipStatus::PendingPayment);
})->with([
    'invalid status' => [['status' => 'INVALID_TRANSACTION']],
    'lower amount' => [['amount' => '10.00', 'currency_amount' => '10.00']],
    'other transaction' => [['tran_id' => 'RUGOTHER']],
    'other currency' => [['currency_type' => 'USD']],
]);

test('a success post without a validation id is not trusted', function () {
    $this->post(route('payments.sslcommerz.success'), ['tran_id' => 'RUGTEST123'])
        ->assertSessionHas('error');

    expect($this->payment->fresh()->status)->toBe(PaymentStatus::Pending);
});

test('the IPN confirms the payment and a later success redirect does not apply it twice', function () {
    fakeValidation();

    $this->post(route('payments.sslcommerz.ipn'), ['tran_id' => 'RUGTEST123', 'val_id' => 'VAL1'])->assertNoContent();
    $this->post(route('payments.sslcommerz.success'), ['tran_id' => 'RUGTEST123', 'val_id' => 'VAL1'])->assertSessionHas('status');

    expect($this->payment->fresh()->status)->toBe(PaymentStatus::Paid);
    Http::assertSentCount(1);
});

test('a failed or cancelled payment is recorded', function (string $route, PaymentStatus $expected) {
    $this->post(route($route), ['tran_id' => 'RUGTEST123'])
        ->assertRedirect(route('portal'))
        ->assertSessionHas('error');

    expect($this->payment->fresh()->status)->toBe($expected);
})->with([
    'fail' => ['payments.sslcommerz.fail', PaymentStatus::Failed],
    'cancel' => ['payments.sslcommerz.cancel', PaymentStatus::Cancelled],
]);

test('a late fail callback does not undo a paid payment', function () {
    $this->payment->update(['status' => PaymentStatus::Paid]);

    $this->post(route('payments.sslcommerz.fail'), ['tran_id' => 'RUGTEST123']);

    expect($this->payment->fresh()->status)->toBe(PaymentStatus::Paid);
});

test('callbacks are accepted without a CSRF token', function () {
    $this->withMiddleware()->post(route('payments.sslcommerz.cancel'), ['tran_id' => 'RUGTEST123'])
        ->assertRedirect(route('portal'));
});
