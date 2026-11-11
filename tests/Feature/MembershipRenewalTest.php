<?php

use App\Enums\MembershipStatus;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\MembershipActivated;
use App\Notifications\MembershipExpired;
use App\Notifications\MembershipExpiringSoon;
use App\Notifications\MembershipRejected;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $this->generalPlan = MembershipPlan::factory()->create(['slug' => 'general', 'price' => 5000, 'duration_months' => 12]);
});

test('the daily job reminds members 30 days before their membership ends, once', function () {
    $membership = Membership::factory()->active()->for($this->generalPlan, 'plan')->create(['expires_at' => now()->addDays(20)]);
    Membership::factory()->active()->for($this->generalPlan, 'plan')->create(['expires_at' => now()->addDays(60)]);

    $this->artisan('memberships:expire')->assertSuccessful();
    $this->artisan('memberships:expire')->assertSuccessful();

    Notification::assertSentToTimes($membership->user, MembershipExpiringSoon::class, 1);
    Notification::assertCount(1);
});

test('members who already renewed get no reminder', function () {
    $old = Membership::factory()->active()->for($this->generalPlan, 'plan')->create(['expires_at' => now()->addDays(20)]);
    Membership::factory()->active()->for($this->generalPlan, 'plan')->for($old->user)->create([
        'renewal_of_id' => $old->id,
        'starts_at' => $old->expires_at,
        'expires_at' => $old->expires_at->addYear(),
    ]);

    $this->artisan('memberships:expire');

    Notification::assertNothingSent();
});

test('the daily job expires memberships that have run out and tells the member', function () {
    $membership = Membership::factory()->active()->for($this->generalPlan, 'plan')->create(['expires_at' => now()->subHour()]);
    $life = Membership::factory()->active()->for(MembershipPlan::factory()->lifetime(), 'plan')->create(['expires_at' => null]);

    $this->artisan('memberships:expire');

    expect($membership->fresh()->status)->toBe(MembershipStatus::Expired)
        ->and($life->fresh()->status)->toBe(MembershipStatus::Active)
        ->and($membership->user->fresh()->isActiveMember())->toBeFalse();

    Notification::assertSentTo($membership->user, MembershipExpired::class);
});

test('an expired membership no longer opens the directory, even before the job runs', function () {
    $membership = Membership::factory()->active()->create(['expires_at' => now()->subMinute()]);

    $this->actingAs($membership->user)->get(route('directory'))->assertRedirect(route('portal'));
});

test('an expired member can renew and goes straight to checkout', function () {
    $expired = Membership::factory()->expired()->for($this->generalPlan, 'plan')->create();
    MemberProfile::factory()->for($expired->user)->create();

    $this->actingAs($expired->user)
        ->get(route('portal'))
        ->assertSee('Renew your membership');

    $response = $this->actingAs($expired->user)
        ->post(route('memberships.renew'), ['membership_plan_id' => $this->generalPlan->id]);

    $renewal = Membership::query()->latest('id')->first();

    $response->assertRedirect(route('memberships.checkout', $renewal));

    expect($renewal->renewal_of_id)->toBe($expired->id)
        ->and($renewal->status)->toBe(MembershipStatus::PendingPayment)
        ->and($renewal->amount)->toBe(5000);
});

test('a paid renewal activates without verification and starts when the current period ends', function () {
    $current = Membership::factory()->active()->for($this->generalPlan, 'plan')->create(['expires_at' => now()->addDays(10)->startOfMinute()]);
    $renewal = Membership::factory()->for($this->generalPlan, 'plan')->for($current->user)->create(['renewal_of_id' => $current->id, 'amount' => 5000]);
    Payment::factory()->forPayable($renewal)->create(['tran_id' => 'RUGRENEW1']);

    Http::fake([
        'sandbox.sslcommerz.com/validator/*' => Http::response(['status' => 'VALID', 'tran_id' => 'RUGRENEW1', 'currency_type' => 'BDT', 'currency_amount' => '5000.00']),
    ]);

    $this->post(route('payments.sslcommerz.success'), ['tran_id' => 'RUGRENEW1', 'val_id' => 'V1'])
        ->assertRedirect(route('portal'))
        ->assertSessionHas('status', 'Payment received. Your membership has been renewed.');

    $renewal->refresh();

    expect($renewal->status)->toBe(MembershipStatus::Active)
        ->and($renewal->starts_at->toDateTimeString())->toBe($current->expires_at->toDateTimeString())
        ->and($renewal->expires_at->toDateTimeString())->toBe($current->expires_at->addYear()->toDateTimeString());

    Notification::assertSentTo($current->user, MembershipActivated::class);
});

test('upgrading to life membership starts at once and never expires', function () {
    $life = MembershipPlan::factory()->lifetime()->create();
    $current = Membership::factory()->active()->for($this->generalPlan, 'plan')->create(['expires_at' => now()->addDays(10)]);
    $renewal = Membership::factory()->for($life, 'plan')->for($current->user)->create(['renewal_of_id' => $current->id, 'amount' => 10000]);

    $renewal->activateRenewal();

    expect($renewal->fresh())
        ->status->toBe(MembershipStatus::Active)
        ->expires_at->toBeNull();
});

test('a free student renewal activates at once', function () {
    $student = MembershipPlan::factory()->free()->create();
    $expired = Membership::factory()->expired()->for($student, 'plan')->create();

    $this->actingAs($expired->user)
        ->post(route('memberships.renew'), ['membership_plan_id' => $student->id])
        ->assertRedirect(route('portal'))
        ->assertSessionHas('status');

    expect($expired->user->fresh()->isActiveMember())->toBeTrue();
});

test('members cannot renew early or without a verified membership', function (string $state) {
    $membership = match ($state) {
        'more than 30 days left' => Membership::factory()->active()->create(['expires_at' => now()->addDays(90)]),
        'life member' => Membership::factory()->active()->for(MembershipPlan::factory()->lifetime(), 'plan')->create(['expires_at' => null]),
        'never verified' => Membership::factory()->pendingVerification()->create(),
    };

    $this->actingAs($membership->user)
        ->post(route('memberships.renew'), ['membership_plan_id' => $this->generalPlan->id])
        ->assertSessionHas('error');

    $this->actingAs($membership->user)->get(route('portal'))->assertDontSee('Renew membership');
})->with(['more than 30 days left', 'life member', 'never verified']);

test('a member with an unpaid renewal is asked to pay it instead of starting another', function () {
    $expired = Membership::factory()->expired()->for($this->generalPlan, 'plan')->create();
    $renewal = Membership::factory()->for($this->generalPlan, 'plan')->for($expired->user)->create(['renewal_of_id' => $expired->id]);

    $this->actingAs($expired->user)
        ->get(route('portal'))
        ->assertSee(route('memberships.checkout', $renewal))
        ->assertDontSee('Renew membership');
});

test('members are emailed when the committee approves or rejects them', function () {
    $admin = User::factory()->admin()->create();
    $approved = Membership::factory()->pendingVerification()->for($this->generalPlan, 'plan')->create();
    $rejected = Membership::factory()->pendingVerification()->create();

    $approved->approve($admin);
    $rejected->reject($admin, 'Student ID not found');

    Notification::assertSentTo($approved->user, MembershipActivated::class);
    Notification::assertSentTo($rejected->user, MembershipRejected::class);
});

test('the expiry job is scheduled daily', function () {
    $this->artisan('schedule:list')->expectsOutputToContain('memberships:expire');
});
