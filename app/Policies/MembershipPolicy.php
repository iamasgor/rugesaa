<?php

namespace App\Policies;

use App\Models\Membership;
use App\Models\User;

class MembershipPolicy
{
    /**
     * Only the member who owns the membership may pay for it.
     */
    public function pay(User $user, Membership $membership): bool
    {
        return $membership->user_id === $user->id;
    }
}
