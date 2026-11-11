<?php

use App\Enums\ContactTopic;
use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\NewContactMessage;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

/**
 * @return array<string, string>
 */
function contactData(array $overrides = []): array
{
    return array_merge([
        'topic' => 'membership',
        'name' => 'Rafiq Ahmed',
        'email' => 'rafiq@example.com',
        'batch' => '2004-05',
        'subject' => 'Life membership',
        'message' => 'Can I upgrade from general to life membership?',
        'newsletter_opt_in' => '1',
    ], $overrides);
}

test('a visitor can send a message and the association is emailed', function () {
    Notification::fake();
    config(['rugesaa.email' => 'office@rugesaa.org']);

    $this->post(route('contact.store'), contactData())
        ->assertRedirect(route('contact'))
        ->assertSessionHas('contact_sent', 'Membership & registration');

    $message = ContactMessage::query()->sole();

    expect($message->topic)->toBe(ContactTopic::Membership)
        ->and($message->newsletter_opt_in)->toBeTrue()
        ->and($message->read_at)->toBeNull();

    Notification::assertSentOnDemand(
        NewContactMessage::class,
        fn (NewContactMessage $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'office@rugesaa.org'
            && $notification->contactMessage->is($message),
    );
});

test('admins are emailed when no association email is configured', function () {
    Notification::fake();
    config(['rugesaa.email' => null]);
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    $this->post(route('contact.store'), contactData());

    Notification::assertSentTo($admin, NewContactMessage::class);
    Notification::assertNotSentTo($member, NewContactMessage::class);
});

test('the confirmation is shown after sending', function () {
    Notification::fake();

    $this->followingRedirects()
        ->post(route('contact.store'), contactData())
        ->assertSee('Message sent')
        ->assertSee('Membership &amp; registration', false);
});

test('the contact form validates required fields', function () {
    $this->post(route('contact.store'), contactData(['email' => 'not-an-email', 'message' => '', 'topic' => 'spam']))
        ->assertSessionHasErrors(['email', 'message', 'topic']);

    expect(ContactMessage::query()->count())->toBe(0);
});

test('the contact form is rate limited per visitor', function () {
    Notification::fake();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('contact.store'), contactData())->assertRedirect();
    }

    $this->post(route('contact.store'), contactData())->assertTooManyRequests();
});
