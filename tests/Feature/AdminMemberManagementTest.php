<?php

use App\Enums\Degree;
use App\Enums\MembershipStatus;
use App\Enums\Sector;
use App\Enums\Specialization;
use App\Filament\Resources\Members\Pages\CreateMember;
use App\Filament\Resources\Members\Pages\EditMember;
use App\Filament\Resources\Members\Pages\ListMembers;
use App\Models\MemberProfile;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

/**
 * @return array<string, mixed>
 */
function memberFormData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Rahima Khatun',
        'email' => 'rahima@example.com',
        'password' => 'secret-Pass-123',
        'password_confirmation' => 'secret-Pass-123',
        'profile.phone' => '+8801712345678',
        'profile.degree' => Degree::BscHons->value,
        'profile.batch' => '2005-06',
        'profile.student_id' => '0512345',
        'profile.specialization' => Specialization::cases()[0]->value,
        'profile.sector' => Sector::cases()[0]->value,
    ], $overrides);
}

test('the members list shows members but not admins', function () {
    $member = User::factory()->create();
    MemberProfile::factory()->for($member)->create();

    Livewire::test(ListMembers::class)
        ->assertCanSeeTableRecords([$member])
        ->assertCanNotSeeTableRecords([$this->admin]);
});

test('an admin enters a member who can then sign in with the given password', function () {
    Notification::fake();
    $plan = MembershipPlan::factory()->create(['duration_months' => 12]);

    Livewire::test(CreateMember::class)
        ->fillForm(memberFormData([
            'membership_plan_id' => $plan->id,
            'membership_starts_at' => '2026-01-01',
        ]))
        ->call('create')
        ->assertHasNoFormErrors();

    $member = User::query()->where('email', 'rahima@example.com')->sole();

    expect($member->hasVerifiedEmail())->toBeTrue()
        ->and($member->is_admin)->toBeFalse()
        ->and($member->profile->batch)->toBe('2005-06')
        ->and($member->profile->student_id)->toBe('0512345')
        ->and($member->isActiveMember())->toBeTrue()
        ->and($member->currentMembership->verified_by)->toBe($this->admin->id)
        ->and($member->currentMembership->status)->toBe(MembershipStatus::Active)
        ->and($member->currentMembership->expires_at->toDateString())->toBe('2027-01-01');

    auth()->logout();

    $this->post(route('login'), ['email' => 'rahima@example.com', 'password' => 'secret-Pass-123']);

    $this->assertAuthenticatedAs($member);
});

test('an admin can enter a member without a membership', function () {
    Livewire::test(CreateMember::class)
        ->fillForm(memberFormData())
        ->call('create')
        ->assertHasNoFormErrors();

    expect(User::query()->where('email', 'rahima@example.com')->sole()->memberships()->exists())->toBeFalse();
});

test('member entry requires matching passwords and a unique email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test(CreateMember::class)
        ->fillForm(memberFormData([
            'email' => 'taken@example.com',
            'password_confirmation' => 'something-else-1',
        ]))
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique', 'password' => 'confirmed']);
});

test('an admin updates a member profile', function () {
    $member = User::factory()->create();
    MemberProfile::factory()->for($member)->create(['batch' => '1999-00']);

    Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
        ->fillForm(['profile.batch' => '2000-01'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($member->profile->fresh()->batch)->toBe('2000-01');
});

test('an admin resets a member password', function () {
    $member = User::factory()->create();
    MemberProfile::factory()->for($member)->create();

    Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
        ->callAction('setPassword', [
            'password' => 'brand-New-Pass-9',
            'password_confirmation' => 'brand-New-Pass-9',
        ])
        ->assertHasNoActionErrors();

    expect(Hash::check('brand-New-Pass-9', $member->fresh()->password))->toBeTrue();
});

test('an admin emails a member a password reset link', function () {
    Notification::fake();
    $member = User::factory()->create();
    MemberProfile::factory()->for($member)->create();

    Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
        ->callAction('sendResetLink');

    Notification::assertSentTo($member, ResetPassword::class);
});

test('admin accounts cannot be edited as members', function () {
    $otherAdmin = User::factory()->admin()->create();

    $this->get('/admin/members/'.$otherAdmin->getRouteKey().'/edit')->assertNotFound();
});
