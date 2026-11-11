<?php

use App\Enums\PaymentStatus;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('a member sees the amount due on checkout', function () {
    $membership = Membership::factory()->create(['amount' => 10000]);

    $this->actingAs($membership->user)
        ->get(route('memberships.checkout', $membership))
        ->assertOk()
        ->assertSee('BDT 10,000');
});

test('a member cannot open another member\'s checkout', function () {
    $membership = Membership::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('memberships.checkout', $membership))
        ->assertForbidden();
});

test('guests are sent to log in before checkout', function () {
    $membership = Membership::factory()->create();

    $this->get(route('memberships.checkout', $membership))->assertRedirect(route('login'));
});

test('a membership that is already paid skips checkout', function () {
    $membership = Membership::factory()->pendingVerification()->create();

    $this->actingAs($membership->user)
        ->get(route('memberships.checkout', $membership))
        ->assertRedirect(route('portal'));
});

test('paying opens an SSLCommerz session and redirects to the gateway', function () {
    $membership = Membership::factory()->create(['amount' => 5000]);
    MemberProfile::factory()->for($membership->user)->create();

    Http::fake([
        'sandbox.sslcommerz.com/gwprocess/*' => Http::response([
            'status' => 'SUCCESS',
            'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/abc',
        ]),
    ]);

    $this->actingAs($membership->user)
        ->post(route('memberships.pay', $membership))
        ->assertRedirect('https://sandbox.sslcommerz.com/EasyCheckOut/abc');

    $payment = Payment::query()->sole();

    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->amount)->toBe(5000)
        ->and($payment->payable->is($membership))->toBeTrue();

    Http::assertSent(fn (Request $request): bool => $request['tran_id'] === $payment->tran_id
        && (int) $request['total_amount'] === 5000
        && $request['ipn_url'] === route('payments.sslcommerz.ipn'));
});

test('a gateway error sends the member back with a message', function () {
    $membership = Membership::factory()->create();

    Http::fake([
        'sandbox.sslcommerz.com/gwprocess/*' => Http::response(['status' => 'FAILED', 'failedreason' => 'Store Credential Error']),
    ]);

    $this->actingAs($membership->user)
        ->from(route('memberships.checkout', $membership))
        ->post(route('memberships.pay', $membership))
        ->assertRedirect(route('memberships.checkout', $membership))
        ->assertSessionHas('error');

    expect(Payment::query()->sole()->status)->toBe(PaymentStatus::Failed);
});
