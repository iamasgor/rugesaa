<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MembershipCheckoutController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MembershipRenewalController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SslCommerzCallbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/membership', MembershipController::class)->name('membership');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('/events/{event}/calendar.ics', [EventController::class, 'calendar'])->name('events.calendar');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{post}', [NewsController::class, 'show'])->name('news.show');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

Route::middleware('auth')->group(function () {
    Route::get('/memberships/{membership}/checkout', [MembershipCheckoutController::class, 'show'])->name('memberships.checkout');
    Route::post('/memberships/{membership}/checkout', [MembershipCheckoutController::class, 'store'])->name('memberships.pay');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/portal', PortalController::class)->name('portal');
    Route::post('/memberships/renewal', [MembershipRenewalController::class, 'store'])->name('memberships.renew');
    Route::get('/portal/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/portal/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/directory', DirectoryController::class)->name('directory');
    Route::get('/portal/events', [PortalController::class, 'events'])->name('portal.events');
    Route::post('/events/{event}/registration', [EventRegistrationController::class, 'store'])->name('events.register');
    Route::post('/events/{event}/registration/payment', [EventRegistrationController::class, 'pay'])->name('events.registration.pay');
    Route::delete('/events/{event}/registration', [EventRegistrationController::class, 'destroy'])->name('events.registration.destroy');
});

Route::prefix('payments/sslcommerz')->name('payments.sslcommerz.')->controller(SslCommerzCallbackController::class)->group(function () {
    Route::post('/success', 'success')->name('success');
    Route::post('/fail', 'fail')->name('fail');
    Route::post('/cancel', 'cancel')->name('cancel');
    Route::post('/ipn', 'ipn')->name('ipn');
});
