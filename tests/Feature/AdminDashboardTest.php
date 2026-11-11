<?php

use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Widgets\EventInventory;
use App\Filament\Widgets\LatestTransactions;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\SalesByCategoryChart;
use App\Filament\Widgets\SalesOverview;
use App\Filament\Widgets\VerificationQueue;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the dashboard shows the sales overview with real data', function () {
    $membership = Membership::factory()->active()->for(MembershipPlan::factory()->state(['name' => 'General']), 'plan')->create();
    Payment::factory()->forPayable($membership)->paid()->create(['amount' => 5000]);
    Payment::factory()->create();

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Overview')
        ->assertSee('New event');

    Livewire::test(SalesOverview::class)
        ->assertSee("Today's sales")
        ->assertSee('BDT 5,000')
        ->assertSee('1 unpaid checkouts');
});

test('the sales charts split revenue into memberships and events', function (string $filter) {
    $membership = Membership::factory()->active()->for(MembershipPlan::factory()->state(['name' => 'Life']), 'plan')->create();
    Payment::factory()->forPayable($membership)->paid()->create(['amount' => 10000]);

    Livewire::test(RevenueChart::class)
        ->set('filter', $filter)
        ->assertOk();

    Livewire::test(SalesByCategoryChart::class)->assertOk();
})->with(['week', 'month', 'year']);

test('latest transactions and the verification queue list pending work', function () {
    $pending = Membership::factory()->pendingVerification()->create();
    MemberProfile::factory()->for($pending->user)->create();
    $payment = Payment::factory()->forPayable($pending)->paid()->create();

    Livewire::test(LatestTransactions::class)->assertCanSeeTableRecords([$payment]);
    Livewire::test(VerificationQueue::class)->assertCanSeeTableRecords([$pending]);
});

test('event inventory counts each registrant and their guests as sold seats', function () {
    $event = Event::factory()->create(['capacity' => 10]);
    EventRegistration::factory()->for($event)->create(['guests' => 2]);
    EventRegistration::factory()->for($event)->cancelled()->create(['guests' => 1]);
    $pastEvent = Event::factory()->past()->create();

    Livewire::test(EventInventory::class)
        ->assertCanSeeTableRecords([$event])
        ->assertCanNotSeeTableRecords([$pastEvent])
        ->assertSee('3 / 10');
});

test('transactions can be filtered by status tabs', function () {
    $paid = Payment::factory()->paid()->create();
    $pending = Payment::factory()->create();

    Livewire::test(ListPayments::class)
        ->set('activeTab', 'paid')
        ->assertCanSeeTableRecords([$paid])
        ->assertCanNotSeeTableRecords([$pending]);
});
