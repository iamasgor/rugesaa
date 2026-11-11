<?php

namespace App\Http\Controllers;

use App\Exceptions\MembershipRenewalException;
use App\Models\MembershipPlan;
use App\Services\MembershipRenewal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MembershipRenewalController extends Controller
{
    public function store(Request $request, MembershipRenewal $renewal): RedirectResponse
    {
        $validated = $request->validate([
            'membership_plan_id' => ['required', Rule::exists(MembershipPlan::class, 'id')->where('is_active', true)],
        ]);

        try {
            $membership = $renewal->start($request->user(), MembershipPlan::query()->whereKey($validated['membership_plan_id'])->firstOrFail());
        } catch (MembershipRenewalException $exception) {
            return to_route('portal')->with('error', $exception->getMessage());
        }

        if ($membership->needsPayment()) {
            return to_route('memberships.checkout', $membership);
        }

        return to_route('portal')->with('status', 'Your membership has been renewed.');
    }
}
