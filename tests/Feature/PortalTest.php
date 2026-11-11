<?php

use App\Models\Membership;
use App\Models\User;

test('members must verify their email before using the portal', function () {
    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('portal'))
        ->assertRedirect(route('verification.notice'));
});

test('the portal asks for payment while the fee is due', function () {
    $membership = Membership::factory()->create(['amount' => 5000]);

    $this->actingAs($membership->user)
        ->get(route('portal'))
        ->assertOk()
        ->assertSee('Awaiting payment')
        ->assertSee(route('memberships.checkout', $membership));
});

test('the portal shows an active membership and its expiry', function () {
    $membership = Membership::factory()->active()->create(['expires_at' => '2027-11-10']);

    $this->actingAs($membership->user)
        ->get(route('portal'))
        ->assertOk()
        ->assertSee('Active')
        ->assertSee('10 Nov 2027')
        ->assertDontSee('Pay now');
});
