<?php

use App\Enums\Sector;
use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @return array<string, mixed>
 */
function profileUpdate(array $overrides = []): array
{
    return array_merge([
        'phone' => '+8801712345678',
        'position' => 'Senior GIS Analyst',
        'organization' => 'CEGIS',
        'sector' => 'development',
        'location' => 'Dhaka, Bangladesh',
        'specialization' => 'climate_change',
        'hall' => 'Shahid Ziaur Rahman Hall',
        'show_in_directory' => '0',
        'mentor_opt_in' => '1',
        'newsletter_opt_in' => '0',
    ], $overrides);
}

test('members can update their work details and privacy settings', function () {
    $profile = MemberProfile::factory()->create(['show_in_directory' => true, 'batch' => '2008-09']);

    $this->actingAs($profile->user)
        ->put(route('profile.update'), profileUpdate())
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('profile_saved');

    $profile->refresh();

    expect($profile->position)->toBe('Senior GIS Analyst')
        ->and($profile->sector)->toBe(Sector::Development)
        ->and($profile->show_in_directory)->toBeFalse()
        ->and($profile->mentor_opt_in)->toBeTrue()
        ->and($profile->batch)->toBe('2008-09');
});

test('members cannot change their verified batch or student id', function () {
    $profile = MemberProfile::factory()->create(['batch' => '2008-09', 'student_id' => '0812345']);

    $this->actingAs($profile->user)
        ->put(route('profile.update'), profileUpdate(['batch' => '1990-91', 'student_id' => '999']));

    expect($profile->fresh())
        ->batch->toBe('2008-09')
        ->student_id->toBe('0812345');
});

test('a new photo replaces and deletes the old one', function () {
    Storage::fake('public');
    $old = UploadedFile::fake()->image('old.jpg')->store('profile-photos', 'public');
    $profile = MemberProfile::factory()->create(['photo_path' => $old]);

    $this->actingAs($profile->user)
        ->put(route('profile.update'), profileUpdate(['photo' => UploadedFile::fake()->image('new.jpg')]));

    $profile->refresh();

    expect($profile->photo_path)->not->toBe($old);
    Storage::disk('public')->assertExists($profile->photo_path);
    Storage::disk('public')->assertMissing($old);
});

test('members can remove their photo', function () {
    Storage::fake('public');
    $old = UploadedFile::fake()->image('old.jpg')->store('profile-photos', 'public');
    $profile = MemberProfile::factory()->create(['photo_path' => $old]);

    $this->actingAs($profile->user)
        ->put(route('profile.update'), profileUpdate(['remove_photo' => '1']));

    expect($profile->fresh()->photo_path)->toBeNull();
    Storage::disk('public')->assertMissing($old);
});

test('the profile page shows the verified record read-only', function () {
    $profile = MemberProfile::factory()->create(['batch' => '2008-09']);

    $this->actingAs($profile->user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('2008-09')
        ->assertDontSee('name="batch"', false);
});

test('accounts without a profile are sent back to the portal', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('profile.edit'))
        ->assertRedirect(route('portal'));
});

test('members cannot rename themselves through fortify', function () {
    $user = User::factory()->create(['name' => 'Original Name']);

    $this->actingAs($user)
        ->put('/user/profile-information', ['name' => 'Changed', 'email' => $user->email])
        ->assertNotFound();
});
