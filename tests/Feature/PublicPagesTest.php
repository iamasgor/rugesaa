<?php

use App\Models\CommitteeMember;
use App\Models\GovernanceDocument;
use App\Models\MembershipPlan;
use App\Models\User;

test('the home page shows current paid plan prices', function () {
    MembershipPlan::factory()->create(['name' => 'General member', 'price' => 5000, 'duration_months' => 12]);
    MembershipPlan::factory()->lifetime()->create(['name' => 'Life member', 'price' => 10000]);
    MembershipPlan::factory()->free()->create(['name' => 'Student associate']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Stay on the map.')
        ->assertSeeInOrder(['General member', 'BDT 5,000', 'Life member', 'BDT 10,000'])
        ->assertDontSee('Student associate');
});

test('the about page lists the committee and governance documents', function () {
    CommitteeMember::factory()->featured()->create(['name' => 'Ayesha Siddiqua', 'position' => 'President']);
    CommitteeMember::factory()->create(['name' => 'Kamal Hossain', 'position' => 'Events Secretary']);
    GovernanceDocument::factory()->create(['title' => 'RUGESAA constitution', 'file_path' => 'documents/constitution.pdf']);

    $this->get(route('about'))
        ->assertOk()
        ->assertSee('Ayesha Siddiqua')
        ->assertSee('Events Secretary · Kamal Hossain')
        ->assertSee('RUGESAA constitution')
        ->assertSee('documents/constitution.pdf');
});

test('the about page hides committee and documents sections until they are added', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertDontSee('Executive committee')
        ->assertDontSee('Constitution &amp; reports', false);
});

test('the contact page shows the department office and hides unset association details', function () {
    config(['rugesaa.email' => null, 'rugesaa.phone' => null, 'rugesaa.social' => []]);

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('lrsarker@ru.ac.bd')
        ->assertDontSee('RUGESAA secretariat')
        ->assertDontSee('aria-label="Social media"', false);
});

test('configured social links appear on the contact page', function () {
    config(['rugesaa.social' => ['Facebook' => 'https://facebook.com/rugesaa']]);

    $this->get(route('contact'))->assertSee('https://facebook.com/rugesaa');
});

test('the membership page lists open plans with their prices and benefits', function () {
    MembershipPlan::factory()->free()->create(['name' => 'Student associate', 'slug' => 'student', 'sort_order' => 1, 'benefits' => ['Webinars and field days']]);
    MembershipPlan::factory()->create(['name' => 'General member', 'slug' => 'general', 'price' => 5000, 'duration_months' => 12, 'sort_order' => 2]);
    MembershipPlan::factory()->lifetime()->create(['name' => 'Life member', 'slug' => 'life', 'price' => 10000, 'sort_order' => 3]);
    MembershipPlan::factory()->inactive()->create(['name' => 'Closed plan']);

    $this->get(route('membership'))
        ->assertOk()
        ->assertSeeInOrder(['Student associate', 'Free', 'General member', 'BDT 5,000', '/ year', 'Life member', 'BDT 10,000', 'once'])
        ->assertSee('Webinars and field days')
        ->assertSee(route('register', ['plan' => 'life']))
        ->assertDontSee('Closed plan');
});

test('the membership page shows a login form to guests only', function () {
    $this->get(route('membership'))->assertSee('aria-label="Portal login"', false);

    $this->actingAs(User::factory()->create())
        ->get(route('membership'))
        ->assertDontSee('aria-label="Portal login"', false)
        ->assertSee('Open the portal');
});

test('the registration form preselects the plan chosen on the membership page', function () {
    MembershipPlan::factory()->create(['slug' => 'general', 'name' => 'General member']);
    $life = MembershipPlan::factory()->lifetime()->create(['slug' => 'life', 'name' => 'Life member']);

    $this->get(route('register', ['plan' => 'life']))
        ->assertSee('value="'.$life->id.'" checked', false);
});
