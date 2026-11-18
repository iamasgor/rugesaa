<?php

use App\Enums\DonationFund;
use App\Enums\PaymentStatus;
use App\Models\Donation;
use App\Models\MemberProfile;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\DonationReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Http::preventStrayRequests();
});

function fakeDonationGatewaySession(): void
{
    Http::fake([
        'sandbox.sslcommerz.com/gwprocess/*' => Http::response(['status' => 'SUCCESS', 'GatewayPageURL' => 'https://sandbox.sslcommerz.com/pay/gift']),
    ]);
}

test('the donation page shows the funds and preset amounts', function () {
    $this->get(route('donate'))
        ->assertOk()
        ->assertSee('Make a gift')
        ->assertSee(DonationFund::Scholarship->getLabel())
        ->assertSee(DonationFund::Labs->getLabel())
        ->assertSee('25,000');
});

test('the header links to the donation page and splits membership into new and existing members', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('donate'))
        ->assertSeeInOrder(['Membership', 'New member', 'Existing member'])
        ->assertSee(route('portal'));
});

test('a visitor without an account can give a preset amount and is sent to the gateway', function () {
    fakeDonationGatewaySession();

    $this->post(route('donate.store'), [
        'fund' => DonationFund::FieldResearch->value,
        'amount' => '5000',
        'name' => 'Rahima Akter',
        'email' => 'rahima@example.com',
        'phone' => '01711111111',
    ])->assertRedirect('https://sandbox.sslcommerz.com/pay/gift');

    $donation = Donation::query()->sole();
    $payment = Payment::query()->sole();

    expect($donation->fund)->toBe(DonationFund::FieldResearch)
        ->and($donation->amount)->toBe(5000)
        ->and($donation->user_id)->toBeNull()
        ->and($donation->isPaid())->toBeFalse()
        ->and($payment->user_id)->toBeNull()
        ->and($payment->amount)->toBe(5000)
        ->and($payment->payable->is($donation))->toBeTrue();

    Http::assertSent(fn (Request $request): bool => $request['cus_email'] === 'rahima@example.com'
        && $request['cus_phone'] === '01711111111'
        && $request['product_category'] === 'Donation');
});

test('a signed-in member can give a custom amount and the gift is linked to their account', function () {
    fakeDonationGatewaySession();
    $member = User::factory()->create();
    MemberProfile::factory()->for($member)->create();

    $this->actingAs($member)
        ->post(route('donate.store'), [
            'fund' => DonationFund::Labs->value,
            'amount' => 'other',
            'other_amount' => '750',
            'name' => $member->name,
            'email' => $member->email,
            'phone' => '01722222222',
        ])->assertRedirect('https://sandbox.sslcommerz.com/pay/gift');

    $donation = Donation::query()->sole();

    expect($donation->amount)->toBe(750)
        ->and($donation->user_id)->toBe($member->id)
        ->and(Payment::query()->sole()->user_id)->toBe($member->id);
});

test('a donation needs a valid fund, amount and contact details', function (array $input, string $errorField) {
    $this->from(route('donate'))
        ->post(route('donate.store'), array_merge([
            'fund' => DonationFund::Scholarship->value,
            'amount' => '1000',
            'name' => 'Donor',
            'email' => 'donor@example.com',
            'phone' => '01700000000',
        ], $input))
        ->assertRedirect(route('donate'))
        ->assertSessionHasErrors($errorField);

    expect(Donation::query()->count())->toBe(0);
})->with([
    'unknown fund' => [['fund' => 'yachts'], 'fund'],
    'amount not offered' => [['amount' => '3'], 'amount'],
    'other without an amount' => [['amount' => 'other'], 'other_amount'],
    'other below the minimum' => [['amount' => 'other', 'other_amount' => '5'], 'other_amount'],
    'missing email' => [['email' => ''], 'email'],
    'missing phone' => [['phone' => ''], 'phone'],
]);

test('the donor is returned to the form when the gateway cannot start a session', function () {
    Http::fake([
        'sandbox.sslcommerz.com/gwprocess/*' => Http::response(['status' => 'FAILED', 'failedreason' => 'Store Credential Error']),
    ]);

    $this->post(route('donate.store'), [
        'fund' => DonationFund::Scholarship->value,
        'amount' => '1000',
        'name' => 'Donor',
        'email' => 'donor@example.com',
        'phone' => '01700000000',
    ])->assertRedirect(route('donate'))
        ->assertSessionHas('error');

    expect(Payment::query()->sole()->status)->toBe(PaymentStatus::Failed);
});

test('a confirmed payment marks the donation paid and emails a receipt once', function () {
    Notification::fake();
    $donation = Donation::factory()->create(['amount' => 5000, 'email' => 'giver@example.com']);
    $payment = Payment::factory()->create([
        'user_id' => null,
        'payable_type' => $donation->getMorphClass(),
        'payable_id' => $donation->id,
        'amount' => 5000,
        'tran_id' => 'RUGGIFT1',
    ]);

    Http::fake([
        'sandbox.sslcommerz.com/validator/*' => Http::response([
            'status' => 'VALID',
            'tran_id' => 'RUGGIFT1',
            'currency_type' => 'BDT',
            'currency_amount' => '5000.00',
        ]),
    ]);

    $this->post(route('payments.sslcommerz.ipn'), ['tran_id' => 'RUGGIFT1', 'val_id' => 'VAL1'])->assertNoContent();
    $this->post(route('payments.sslcommerz.success'), ['tran_id' => 'RUGGIFT1', 'val_id' => 'VAL1'])
        ->assertRedirect(route('donate'))
        ->assertSessionHas('status');

    expect($donation->fresh()->isPaid())->toBeTrue()
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Paid);

    Notification::assertSentOnDemandTimes(DonationReceived::class, 1);
    Notification::assertSentOnDemand(DonationReceived::class, fn (DonationReceived $notification, array $channels, object $notifiable): bool => array_key_exists('giver@example.com', $notifiable->routes['mail']));
});
