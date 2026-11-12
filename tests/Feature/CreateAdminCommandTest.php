<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('it creates a verified admin with a generated password', function () {
    $this->artisan('app:create-admin', ['email' => 'Chair@Rugesaa.org'])
        ->expectsOutputToContain('Save this password now')
        ->assertSuccessful();

    $admin = User::query()->sole();

    expect($admin->email)->toBe('chair@rugesaa.org')
        ->and($admin->name)->toBe('RUGESAA Admin')
        ->and($admin->is_admin)->toBeTrue()
        ->and($admin->hasVerifiedEmail())->toBeTrue()
        ->and(Hash::check('password', $admin->password))->toBeFalse();
});

test('the generated password lets the admin log into the panel', function () {
    $this->artisan('app:create-admin', ['email' => 'chair@rugesaa.org', '--name' => 'Committee Chair'])
        ->assertSuccessful();

    $admin = User::query()->sole();

    expect($admin->name)->toBe('Committee Chair');

    $this->actingAs($admin)->get('/admin')->assertOk();
});

test('the password can be typed instead of generated', function () {
    $this->artisan('app:create-admin', ['email' => 'chair@rugesaa.org', '--ask-password' => true])
        ->expectsQuestion('Password', 'Typed-Secret-123!')
        ->doesntExpectOutputToContain('Save this password now')
        ->assertSuccessful();

    expect(Hash::check('Typed-Secret-123!', User::query()->sole()->password))->toBeTrue();
});

test('an existing member is promoted and their password reset after confirmation', function () {
    $member = User::factory()->unverified()->create(['email' => 'member@rugesaa.org', 'name' => 'Rahim']);

    $this->artisan('app:create-admin', ['email' => 'member@rugesaa.org'])
        ->expectsConfirmation('A user with member@rugesaa.org already exists. Make them an admin and reset their password?', 'yes')
        ->assertSuccessful();

    $member->refresh();

    expect(User::query()->count())->toBe(1)
        ->and($member->name)->toBe('Rahim')
        ->and($member->is_admin)->toBeTrue()
        ->and($member->hasVerifiedEmail())->toBeTrue()
        ->and(Hash::check('password', $member->password))->toBeFalse();
});

test('an existing user is left untouched when the promotion is declined', function () {
    $member = User::factory()->create(['email' => 'member@rugesaa.org']);

    $this->artisan('app:create-admin', ['email' => 'member@rugesaa.org'])
        ->expectsConfirmation('A user with member@rugesaa.org already exists. Make them an admin and reset their password?', 'no')
        ->assertFailed();

    expect($member->fresh()->is_admin)->toBeFalse()
        ->and(Hash::check('password', $member->fresh()->password))->toBeTrue();
});

test('an invalid email is rejected', function () {
    $this->artisan('app:create-admin', ['email' => 'not-an-email'])->assertFailed();

    expect(User::query()->count())->toBe(0);
});
