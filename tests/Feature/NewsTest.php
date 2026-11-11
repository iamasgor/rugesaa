<?php

use App\Enums\NewsCategory;
use App\Models\Post;
use App\Models\User;

test('the news page shows published stories with the featured one on top', function () {
    Post::factory()->featured()->create(['title' => 'Alumni map flood-affected unions']);
    Post::factory()->create(['title' => 'Lab gets new workstations']);
    Post::factory()->draft()->create(['title' => 'Unfinished draft']);
    Post::factory()->scheduled()->create(['title' => 'Scheduled for next week']);

    $this->get(route('news.index'))
        ->assertOk()
        ->assertSeeInOrder(['Featured', 'Alumni map flood-affected unions', 'Lab gets new workstations'])
        ->assertDontSee('Unfinished draft')
        ->assertDontSee('Scheduled for next week');
});

test('stories can be filtered by category', function () {
    Post::factory()->create(['title' => 'Department news item', 'category' => NewsCategory::Department]);
    Post::factory()->create(['title' => 'Giving news item', 'category' => NewsCategory::Giving]);

    $this->get(route('news.index', ['category' => 'giving']))
        ->assertSee('Giving news item')
        ->assertDontSee('Department news item');
});

test('a story page renders markdown safely and links related stories', function () {
    $post = Post::factory()->create([
        'category' => NewsCategory::Profile,
        'body' => "## In the field\n\nShe **mapped** every union.\n\n<img src=x onerror=alert(1)>",
    ]);
    Post::factory()->create(['title' => 'Another profile', 'category' => NewsCategory::Profile]);
    Post::factory()->create(['title' => 'Unrelated giving story', 'category' => NewsCategory::Giving]);

    $this->get(route('news.show', $post))
        ->assertOk()
        ->assertSee('<h2>In the field</h2>', false)
        ->assertSee('<strong>mapped</strong>', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false)
        ->assertSee('Another profile')
        ->assertDontSee('Unrelated giving story');
});

test('unpublished stories are hidden from visitors and previewable by admins', function () {
    $post = Post::factory()->draft()->create();

    $this->get(route('news.show', $post))->assertNotFound();
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('news.show', $post))
        ->assertOk()
        ->assertSee('only admins can see this');
});

test('stories get unique slugs from their titles', function () {
    $first = Post::factory()->create(['title' => 'Election schedule announced']);
    $second = Post::factory()->create(['title' => 'Election schedule announced']);

    expect($first->slug)->toBe('election-schedule-announced')
        ->and($second->slug)->toBe('election-schedule-announced-2');
});

test('the home page shows the latest stories', function () {
    Post::factory()->create(['title' => 'Newest story', 'published_at' => now()->subDay()]);
    Post::factory()->create(['title' => 'Older story', 'published_at' => now()->subWeek()]);

    $this->get(route('home'))
        ->assertSee('News &amp; stories', false)
        ->assertSeeInOrder(['Newest story', 'Older story']);
});
