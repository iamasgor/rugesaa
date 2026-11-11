<?php

use App\Enums\EventRegistrationStatus;
use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\EventRegistrationConfirmed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    Http::preventStrayRequests();
});

function eventMember(): User
{
    $user = User::factory()->create();
    Membership::factory()->active()->for($user)->create();
    MemberProfile::factory()->for($user)->create();

    return $user;
}

function fakeGatewaySession(): void
{
    Http::fake([
        'sandbox.sslcommerz.com/gwprocess/*' => Http::response(['status' => 'SUCCESS', 'GatewayPageURL' => 'https://sandbox.sslcommerz.com/pay/xyz']),
    ]);
}

test('a member registering for a free event is confirmed and emailed', function () {
    $member = eventMember();
    $event = Event::factory()->create();

    $this->actingAs($member)
        ->post(route('events.register', $event))
        ->assertRedirect(route('events.show', $event))
        ->assertSessionHas('status');

    $registration = EventRegistration::query()->sole();

    expect($registration->status)->toBe(EventRegistrationStatus::Confirmed)
        ->and($registration->amount)->toBe(0);

    Notification::assertSentTo($member, EventRegistrationConfirmed::class);
});

test('a paid registration holds the seat and sends the member to the gateway with the right amount', function () {
    fakeGatewaySession();
    $member = eventMember();
    $event = Event::factory()->paid(memberFee: 1500, guestFee: 1000, maxGuests: 2)->create();

    $this->actingAs($member)
        ->post(route('events.register', $event), ['guests' => 2])
        ->assertRedirect('https://sandbox.sslcommerz.com/pay/xyz');

    $registration = EventRegistration::query()->sole();
    $payment = Payment::query()->sole();

    expect($registration->status)->toBe(EventRegistrationStatus::PendingPayment)
        ->and($registration->amount)->toBe(3500)
        ->and($payment->amount)->toBe(3500)
        ->and($payment->payable->is($registration))->toBeTrue();

    Notification::assertNothingSent();
});

test('a validated payment confirms the event registration and returns to the event', function () {
    $event = Event::factory()->paid()->create();
    $registration = EventRegistration::factory()->for($event)->pendingPayment(1500)->create();
    Payment::factory()->create([
        'user_id' => $registration->user_id,
        'payable_type' => 'event_registration',
        'payable_id' => $registration->id,
        'amount' => 1500,
        'tran_id' => 'RUGEVENT1',
    ]);

    Http::fake([
        'sandbox.sslcommerz.com/validator/*' => Http::response(['status' => 'VALID', 'tran_id' => 'RUGEVENT1', 'currency_type' => 'BDT', 'currency_amount' => '1500.00']),
    ]);

    $this->post(route('payments.sslcommerz.success'), ['tran_id' => 'RUGEVENT1', 'val_id' => 'VAL9'])
        ->assertRedirect(route('events.show', $event));

    expect($registration->fresh()->status)->toBe(EventRegistrationStatus::Confirmed)
        ->and(Payment::query()->sole()->status)->toBe(PaymentStatus::Paid);

    Notification::assertSentTo($registration->user, EventRegistrationConfirmed::class);
});

test('members who are not active cannot register', function () {
    $membership = Membership::factory()->pendingVerification()->create();
    $event = Event::factory()->create();

    $this->actingAs($membership->user)
        ->post(route('events.register', $event))
        ->assertSessionHas('error');

    expect(EventRegistration::query()->count())->toBe(0);
});

test('registration is refused once closed', function (array $attributes) {
    $event = Event::factory()->create($attributes);

    $this->actingAs(eventMember())
        ->post(route('events.register', $event))
        ->assertSessionHas('error', 'Registration for this event is closed.');
})->with([
    'deadline passed' => [['registration_closes_at' => now()->subHour()]],
    'event started' => [['starts_at' => now()->subHour()]],
]);

test('members cannot bring more guests than allowed', function () {
    $event = Event::factory()->paid(maxGuests: 1)->create();

    $this->actingAs(eventMember())
        ->post(route('events.register', $event), ['guests' => 3])
        ->assertSessionHasErrors('guests');
});

test('registration is refused when there are not enough seats', function () {
    $event = Event::factory()->create(['capacity' => 3, 'max_guests' => 2]);
    EventRegistration::factory()->for($event)->create(['guests' => 1]);
    EventRegistration::factory()->for($event)->cancelled()->create(['guests' => 2]);

    $this->actingAs(eventMember())
        ->post(route('events.register', $event), ['guests' => 1])
        ->assertSessionHas('error', 'Sorry, there are not enough seats left for your booking.');

    $this->actingAs(eventMember())
        ->post(route('events.register', $event), ['guests' => 0])
        ->assertSessionHas('status');
});

test('members cannot register twice for the same event', function () {
    $member = eventMember();
    $event = Event::factory()->create();
    EventRegistration::factory()->for($event)->for($member)->create();

    $this->actingAs($member)
        ->post(route('events.register', $event))
        ->assertSessionHas('error', "You're already registered for this event.");

    expect(EventRegistration::query()->count())->toBe(1);
});

test('a cancelled registration can be made again', function () {
    $member = eventMember();
    $event = Event::factory()->create();
    EventRegistration::factory()->for($event)->for($member)->cancelled()->create();

    $this->actingAs($member)->post(route('events.register', $event))->assertSessionHas('status');

    expect(EventRegistration::query()->sole()->status)->toBe(EventRegistrationStatus::Confirmed);
});

test('free and unpaid registrations can be cancelled by the member', function () {
    $member = eventMember();
    $event = Event::factory()->create();
    $registration = EventRegistration::factory()->for($event)->for($member)->create();

    $this->actingAs($member)
        ->delete(route('events.registration.destroy', $event))
        ->assertSessionHas('status');

    expect($registration->fresh()->status)->toBe(EventRegistrationStatus::Cancelled);
});

test('paid registrations cannot be cancelled online', function () {
    $member = eventMember();
    $event = Event::factory()->paid()->create();
    $registration = EventRegistration::factory()->for($event)->for($member)->create(['amount' => 1500]);

    $this->actingAs($member)
        ->delete(route('events.registration.destroy', $event))
        ->assertSessionHas('error');

    expect($registration->fresh()->status)->toBe(EventRegistrationStatus::Confirmed);
});

test('a member can resume payment for a held seat', function () {
    fakeGatewaySession();
    $member = eventMember();
    $event = Event::factory()->paid()->create();
    EventRegistration::factory()->for($event)->for($member)->pendingPayment()->create();

    $this->actingAs($member)
        ->post(route('events.registration.pay', $event))
        ->assertRedirect('https://sandbox.sslcommerz.com/pay/xyz');
});

test('my events lists the member\'s upcoming registrations', function () {
    $member = eventMember();
    EventRegistration::factory()->for(Event::factory()->state(['title' => 'Going to this']))->for($member)->create();
    EventRegistration::factory()->for(Event::factory()->state(['title' => 'Cancelled this']))->for($member)->cancelled()->create();

    $this->actingAs($member)
        ->get(route('portal.events'))
        ->assertOk()
        ->assertSee('Going to this')
        ->assertDontSee('Cancelled this');
});
