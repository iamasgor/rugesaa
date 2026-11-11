<?php

use App\Enums\Sector;
use App\Enums\Specialization;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\User;
use Livewire\Livewire;

/**
 * Create a member with an active membership and a directory profile.
 *
 * @param  array<string, mixed>  $profile
 */
function activeMember(string $name, array $profile = []): User
{
    $user = User::factory()->create(['name' => $name]);
    Membership::factory()->active()->for($user)->create();
    MemberProfile::factory()->for($user)->create(array_merge(['show_in_directory' => true], $profile));

    return $user;
}

test('active members can open the directory', function () {
    $this->actingAs(activeMember('Nusrat Jahan'))
        ->get(route('directory'))
        ->assertOk()
        ->assertSee('Alumni directory')
        ->assertSee('Nusrat Jahan');
});

test('members awaiting verification are sent back to the portal', function () {
    $membership = Membership::factory()->pendingVerification()->create();

    $this->actingAs($membership->user)
        ->get(route('directory'))
        ->assertRedirect(route('portal'))
        ->assertSessionHas('error');
});

test('admins can open the directory without a membership', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('directory'))
        ->assertOk();
});

test('guests must log in to see the directory', function () {
    $this->get(route('directory'))->assertRedirect(route('login'));
});

test('the directory component refuses members who are not active', function () {
    $membership = Membership::factory()->pendingVerification()->create();

    $this->actingAs($membership->user);

    Livewire::test('member-directory')->assertForbidden();
});

test('only active members who opted in are listed', function () {
    $viewer = activeMember('Viewer Member');
    activeMember('Listed Member');
    activeMember('Hidden Member', ['show_in_directory' => false]);

    $pending = User::factory()->create(['name' => 'Pending Member']);
    Membership::factory()->pendingVerification()->for($pending)->create();
    MemberProfile::factory()->for($pending)->create();

    $this->actingAs($viewer);

    Livewire::test('member-directory')
        ->assertSee('Listed Member')
        ->assertDontSee('Hidden Member')
        ->assertDontSee('Pending Member');
});

test('private contact details are never shown in the directory', function () {
    $viewer = activeMember('Viewer Member');
    $other = activeMember('Other Member', ['phone' => '+8801711000222', 'student_id' => 'SID-998877']);

    $this->actingAs($viewer);

    Livewire::test('member-directory')
        ->assertSee('Other Member')
        ->assertDontSee('+8801711000222')
        ->assertDontSee('SID-998877')
        ->assertDontSee($other->email);
});

test('members can search by name, organization or city', function (string $search) {
    $this->actingAs(activeMember('Viewer Member', ['organization' => 'Nowhere Ltd', 'location' => 'Sylhet']));
    activeMember('Tasnim Chowdhury', ['organization' => 'CEGIS', 'location' => 'Toronto, Canada']);
    activeMember('Rakib Hasan', ['organization' => 'BRAC', 'location' => 'Dhaka, Bangladesh']);

    Livewire::test('member-directory')
        ->set('search', $search)
        ->assertSee('Tasnim Chowdhury')
        ->assertDontSee('Rakib Hasan');
})->with(['tasnim', 'CEGIS', 'Toronto']);

test('members can filter by batch decade, specialization, sector and mentoring', function () {
    $this->actingAs(activeMember('Viewer Member', ['batch' => '1985-86']));
    activeMember('Match Member', [
        'batch' => '2008-09',
        'specialization' => Specialization::GisRemoteSensing,
        'sector' => Sector::Development,
        'mentor_opt_in' => true,
    ]);
    activeMember('Wrong Decade', ['batch' => '1998-99', 'specialization' => Specialization::GisRemoteSensing, 'sector' => Sector::Development, 'mentor_opt_in' => true]);
    activeMember('Wrong Sector', ['batch' => '2009-10', 'specialization' => Specialization::GisRemoteSensing, 'sector' => Sector::Government, 'mentor_opt_in' => true]);
    activeMember('Not Mentoring', ['batch' => '2005-06', 'specialization' => Specialization::GisRemoteSensing, 'sector' => Sector::Development, 'mentor_opt_in' => false]);

    Livewire::test('member-directory')
        ->set('decade', '2000')
        ->set('specialization', Specialization::GisRemoteSensing->value)
        ->set('sector', Sector::Development->value)
        ->set('mentorsOnly', true)
        ->assertSee('Match Member')
        ->assertDontSee('Wrong Decade')
        ->assertDontSee('Wrong Sector')
        ->assertDontSee('Not Mentoring')
        ->call('clearFilters')
        ->assertSee('Wrong Decade')
        ->assertSee('Not Mentoring');
});

test('the home page search box carries the keyword into the directory', function () {
    $this->actingAs(activeMember('Viewer Member'));
    activeMember('Tasnim Chowdhury');

    $this->get(route('directory', ['search' => 'Tasnim']))
        ->assertOk()
        ->assertSee('Tasnim Chowdhury')
        ->assertDontSee('Viewer Member</h3>', false);
});
