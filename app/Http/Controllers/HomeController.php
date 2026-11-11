<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\MembershipPlan;
use App\Models\Post;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $upcomingEvents = Event::query()->published()->upcoming()->limit(6)->get();
        $featuredEvent = $upcomingEvents->firstWhere('is_featured', true) ?? $upcomingEvents->first();

        return view('pages.home', [
            'plans' => MembershipPlan::query()->active()->where('price', '>', 0)->get(),
            'featuredEvent' => $featuredEvent,
            'latestPosts' => Post::query()->published()->limit(4)->get(),
            'moreEvents' => $upcomingEvents->reject(fn (Event $event): bool => $event->is($featuredEvent))->take(2),
        ]);
    }
}
