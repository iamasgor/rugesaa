<?php

use App\Enums\ContactTopic;
use App\Enums\SitePage;
use App\Filament\Pages\SitePages\EditAboutPage;
use App\Filament\Pages\SitePages\EditContactSettings;
use App\Filament\Pages\SitePages\EditHomePage;
use App\Models\PageContent;
use App\Models\User;
use App\Notifications\NewContactMessage;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('the home and about pages show the default copy before anything is saved', function () {
    $this->get(route('home'))->assertOk()->assertSee('Stay on the map.');
    $this->get(route('about'))->assertOk()->assertSee('Keep the cohort together long after the final field report.');
});

test('an admin edits the home page copy', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditHomePage::class)
        ->assertSchemaStateSet(['hero.title' => 'Stay on the map.'])
        ->fillForm([
            'hero.title' => 'Welcome home, geographers',
            'benefits.steps' => [['text' => 'Sign up online.'], ['text' => 'Get verified.']],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(SitePage::Home->content()['benefits']['steps'])->toBe(['Sign up online.', 'Get verified.']);

    $this->get(route('home'))
        ->assertSee('Welcome home, geographers')
        ->assertSee('Get verified.')
        ->assertDontSee('Stay on the map.')
        ->assertSee('Three ways to stay connected');
});

test('an admin edits the about page lists', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditAboutPage::class)
        ->fillForm([
            'mission.title' => 'Geographers for life',
            'history.milestones' => [
                ['year' => '1955', 'heading' => 'Founded', 'body' => 'The department opens.'],
                ['year' => '2026', 'heading' => 'New building', 'body' => 'A new home for the labs.'],
            ],
            'department.partners_local' => ['SPARRSO'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $content = SitePage::About->content();

    expect($content['history']['milestones'])->toHaveCount(2)
        ->and(array_is_list($content['history']['milestones']))->toBeTrue();

    $this->get(route('about'))
        ->assertSee('Geographers for life')
        ->assertSee('A new home for the labs.')
        ->assertDontSee('Integrated honours');
});

test('a required title cannot be cleared', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditHomePage::class)
        ->fillForm(['hero.title' => ''])
        ->call('save')
        ->assertHasFormErrors(['hero.title' => 'required']);

    expect(PageContent::query()->exists())->toBeFalse();
});

test('sections missing from saved content fall back to the defaults', function () {
    PageContent::factory()->create(['content' => ['hero' => ['title' => 'Only the hero was saved']]]);

    $content = SitePage::Home->content();

    expect($content['hero']['title'])->toBe('Only the hero was saved')
        ->and($content['hero']['intro'])->toBe(SitePage::Home->defaults()['hero']['intro'])
        ->and($content['join']['title'])->toBe('Your degree came with a network. Claim it.');
});

test('members cannot open the page editors', function () {
    $this->actingAs(User::factory()->create())
        ->get(EditHomePage::getUrl())
        ->assertForbidden();
});

test('admins can open the page editors', function (string $page) {
    $this->actingAs(User::factory()->admin()->create())
        ->get($page::getUrl())
        ->assertOk();
})->with([EditHomePage::class, EditAboutPage::class, EditContactSettings::class]);

test('an admin updates contact details and social links shown across the site', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditContactSettings::class)
        ->fillForm([
            'association.email' => 'office@rugesaa.org',
            'association.phone' => '+880 1711-000000',
            'association.office_hours' => 'Sun–Thu, 10am–4pm',
            'social.links' => [
                ['label' => 'Facebook', 'url' => 'https://facebook.com/rugesaa.official'],
                ['label' => 'Instagram', 'url' => 'https://instagram.com/rugesaa'],
            ],
            'department.chairman' => 'Dr. New Chairman',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    auth()->logout();

    $this->get(route('contact'))
        ->assertSee('office@rugesaa.org')
        ->assertSee('Sun–Thu, 10am–4pm')
        ->assertSee('https://instagram.com/rugesaa')
        ->assertSee('Dr. New Chairman');

    $this->get(route('about'))->assertSee('Dr. New Chairman');
});

test('contact form messages go to the email saved in the admin panel', function () {
    Notification::fake();
    PageContent::factory()->create([
        'page' => SitePage::Contact,
        'content' => ['association' => ['email' => 'saved@rugesaa.org']],
    ]);

    $this->post(route('contact.store'), [
        'name' => 'Karim',
        'email' => 'karim@example.com',
        'topic' => ContactTopic::General->value,
        'subject' => 'Hello',
        'message' => 'A question about membership.',
    ])->assertSessionHasNoErrors();

    Notification::assertSentOnDemand(
        NewContactMessage::class,
        fn (NewContactMessage $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'saved@rugesaa.org',
    );
});

test('social links and contact details must be valid', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(EditContactSettings::class)
        ->fillForm([
            'association.email' => 'not-an-email',
            'social.links' => [['label' => 'Facebook', 'url' => 'facebook']],
        ])
        ->call('save')
        ->assertHasFormErrors(['association.email' => 'email']);
});
