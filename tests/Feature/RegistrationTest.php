<?php

use App\Enums\MembershipStatus;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @return array<string, mixed>
 */
function registrationData(MembershipPlan $plan, array $overrides = []): array
{
    return array_merge([
        'name' => 'Nusrat Jahan',
        'email' => 'nusrat@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'phone' => '+8801712345678',
        'degree' => 'ms',
        'batch' => '2008-09',
        'student_id' => '0812345',
        'hall' => 'Begum Rokeya Hall',
        'specialization' => 'gis_remote_sensing',
        'position' => 'GIS Analyst',
        'organization' => 'CEGIS',
        'sector' => 'development',
        'location' => 'Dhaka, Bangladesh',
        'membership_plan_id' => $plan->id,
        'mentor_opt_in' => '1',
        'show_in_directory' => '1',
        'newsletter_opt_in' => '0',
        'terms' => '1',
    ], $overrides);
}

test('registration page lists active plans with their prices', function () {
    MembershipPlan::factory()->create(['name' => 'General member', 'price' => 5000]);
    MembershipPlan::factory()->inactive()->create(['name' => 'Closed plan']);

    $this->get(route('register'))
        ->assertOk()
        ->assertSee('General member')
        ->assertSee('BDT 5,000 / year')
        ->assertDontSee('Closed plan');
});

test('a graduate choosing a paid plan is registered and sent to checkout', function () {
    Storage::fake('public');
    $plan = MembershipPlan::factory()->create(['price' => 5000]);

    $response = $this->post(route('register'), registrationData($plan, [
        'photo' => UploadedFile::fake()->image('me.jpg'),
    ]));

    $user = User::query()->where('email', 'nusrat@example.com')->firstOrFail();
    $membership = $user->latestMembership;

    $response->assertRedirect(route('memberships.checkout', $membership));
    $this->assertAuthenticatedAs($user);

    expect($membership->status)->toBe(MembershipStatus::PendingPayment)
        ->and($membership->amount)->toBe(5000)
        ->and($user->profile->batch)->toBe('2008-09')
        ->and($user->profile->mentor_opt_in)->toBeTrue()
        ->and($user->profile->newsletter_opt_in)->toBeFalse();

    Storage::disk('public')->assertExists($user->profile->photo_path);
});

test('the plan price is copied at registration so later price changes do not affect it', function () {
    $plan = MembershipPlan::factory()->create(['price' => 5000]);

    $this->post(route('register'), registrationData($plan));
    $plan->update(['price' => 6000]);

    expect(User::query()->firstWhere('email', 'nusrat@example.com')->latestMembership->amount)->toBe(5000);
});

test('a student choosing the free plan skips payment and waits for verification', function () {
    $plan = MembershipPlan::factory()->free()->create();

    $this->post(route('register'), registrationData($plan, ['degree' => 'current_student']))
        ->assertRedirect(route('portal'));

    expect(User::query()->firstWhere('email', 'nusrat@example.com')->latestMembership->status)
        ->toBe(MembershipStatus::PendingVerification);
});

test('registration requires accepting the constitution', function () {
    $plan = MembershipPlan::factory()->create();

    $this->post(route('register'), registrationData($plan, ['terms' => '0']))
        ->assertSessionHasErrors('terms');

    $this->assertGuest();
    expect(User::query()->count())->toBe(0);
});

test('registration rejects a plan that is closed', function () {
    $plan = MembershipPlan::factory()->inactive()->create();

    $this->post(route('register'), registrationData($plan))
        ->assertSessionHasErrors('membership_plan_id');
});

test('registration rejects unknown academic options', function () {
    $plan = MembershipPlan::factory()->create();

    $this->post(route('register'), registrationData($plan, ['degree' => 'diploma', 'sector' => 'space']))
        ->assertSessionHasErrors(['degree', 'sector']);
});
