<?php

namespace App\Services;

use App\Enums\MembershipStatus;
use App\Exceptions\MembershipRenewalException;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MembershipRenewal
{
    /**
     * Start a renewal (or a switch to another plan, e.g. student to general, general to life).
     * Free renewals activate at once; paid ones wait for the payment.
     *
     * @throws MembershipRenewalException
     */
    public function start(User $user, MembershipPlan $plan): Membership
    {
        if (! $plan->is_active) {
            throw new MembershipRenewalException('That membership type is not open right now.');
        }

        if (! $user->canRenewMembership()) {
            throw new MembershipRenewalException('Your membership cannot be renewed yet.');
        }

        return DB::transaction(function () use ($user, $plan): Membership {
            /** @var Membership $renewal */
            $renewal = $user->memberships()->create([
                'membership_plan_id' => $plan->id,
                'renewal_of_id' => $user->lastVerifiedMembership()?->id,
                'amount' => $plan->price,
                'status' => MembershipStatus::PendingPayment,
            ]);

            if ($plan->isFree()) {
                $renewal->activateRenewal();
            }

            return $renewal;
        });
    }
}
