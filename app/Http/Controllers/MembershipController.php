<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.membership', [
            'plans' => MembershipPlan::query()->active()->get(),
        ]);
    }
}
