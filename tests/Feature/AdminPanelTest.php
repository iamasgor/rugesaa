<?php

use App\Enums\MembershipStatus;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\RelationManagers\RegistrationsRelationManager;
use App\Filament\Resources\MembershipPlans\Pages\EditMembershipPlan;
use App\Filament\Resources\Memberships\Pages\ViewMembership;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Models\ContactMessage;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

test('members cannot open the admin panel', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

test('guests are sent to the site login from the admin panel', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

test('admins can open the admin panel', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertOk();
});

test('an admin can change a plan price', function () {
    $this->actingAs(User::factory()->admin()->create());
    $plan = MembershipPlan::factory()->create(['price' => 5000]);

    Livewire::test(EditMembershipPlan::class, ['record' => $plan->getRouteKey()])
        ->fillForm(['price' => 6000])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($plan->fresh()->price)->toBe(6000);
});

test('an admin approves a verified yearly membership for twelve months', function () {
    $admin = User::factory()->admin()->create();
    $plan = MembershipPlan::factory()->create(['duration_months' => 12]);
    $membership = Membership::factory()->pendingVerification()->for($plan, 'plan')->create();
    MemberProfile::factory()->for($membership->user)->create();

    $this->actingAs($admin);

    Livewire::test(ViewMembership::class, ['record' => $membership->getRouteKey()])
        ->callAction('approve');

    $membership->refresh();

    expect($membership->status)->toBe(MembershipStatus::Active)
        ->and($membership->verified_by)->toBe($admin->id)
        ->and($membership->expires_at->toDateString())->toBe(now()->addMonths(12)->toDateString());
});

test('a life membership never expires once approved', function () {
    $admin = User::factory()->admin()->create();
    $membership = Membership::factory()->pendingVerification()->for(MembershipPlan::factory()->lifetime(), 'plan')->create();

    $membership->approve($admin);

    expect($membership->fresh()->expires_at)->toBeNull();
});

test('an admin rejects a membership with a reason', function () {
    $this->actingAs(User::factory()->admin()->create());
    $membership = Membership::factory()->pendingVerification()->create();
    MemberProfile::factory()->for($membership->user)->create();

    Livewire::test(ViewMembership::class, ['record' => $membership->getRouteKey()])
        ->callAction('reject', ['reason' => 'Student ID not found in department records']);

    expect($membership->fresh())
        ->status->toBe(MembershipStatus::Rejected)
        ->rejection_reason->toBe('Student ID not found in department records');
});

test('opening a contact message in the admin panel marks it read', function () {
    $this->actingAs(User::factory()->admin()->create());
    $message = ContactMessage::factory()->create();

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->assertSee($message->subject);

    expect($message->fresh()->read_at)->not->toBeNull();
});

test('every admin list page loads', function (string $path) {
    MembershipPlan::factory()->create();
    Membership::factory()->pendingVerification()->create();
    ContactMessage::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin/'.$path)
        ->assertOk();
})->with([
    'membership-plans',
    'memberships',
    'payments',
    'committee-members',
    'governance-documents',
    'contact-messages',
    'events',
    'posts',
]);

test('an admin can create an event that gets a slug', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateEvent::class)
        ->fillForm([
            'title' => 'Grand Reunion 2026',
            'type' => 'reunion',
            'venue' => 'Sir J.C. Bose Academic Building',
            'summary' => 'Every batch, one day on campus.',
            'starts_at' => now()->addMonth()->format('Y-m-d H:i'),
            'member_fee' => 2000,
            'max_guests' => 2,
            'guest_fee' => 1000,
            'published_at' => now()->format('Y-m-d H:i'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Event::query()->sole())
        ->slug->toBe('grand-reunion-2026')
        ->member_fee->toBe(2000);
});

test('an admin can see the attendees of an event', function () {
    $this->actingAs(User::factory()->admin()->create());
    $registration = EventRegistration::factory()->create();
    MemberProfile::factory()->for($registration->user)->create();

    $this->get('/admin/events/'.$registration->event->slug.'/edit')->assertOk();

    Livewire::test(RegistrationsRelationManager::class, ['ownerRecord' => $registration->event, 'pageClass' => EditEvent::class])
        ->assertSee($registration->user->name);
});

test('a story written in the admin panel records its author', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Election schedule announced',
            'excerpt' => 'Nominations open next month.',
            'body' => 'Full details of the election.',
            'category' => 'association',
            'published_at' => now()->format('Y-m-d H:i'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()->sole())
        ->author_id->toBe($admin->id)
        ->slug->toBe('election-schedule-announced');
});
