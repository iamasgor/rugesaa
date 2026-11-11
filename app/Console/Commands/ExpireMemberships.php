<?php

namespace App\Console\Commands;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Notifications\MembershipExpired;
use App\Notifications\MembershipExpiringSoon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

#[Signature('memberships:expire')]
#[Description('Send renewal reminders 30 days ahead and mark memberships that have run out as expired')]
class ExpireMemberships extends Command
{
    public function handle(): int
    {
        $reminded = 0;

        Membership::query()
            ->with(['user', 'plan'])
            ->where('status', MembershipStatus::Active)
            ->whereNull('expiry_reminder_sent_at')
            ->whereBetween('expires_at', [now(), now()->addDays(30)])
            ->whereDoesntHave('user.memberships', fn (Builder $query) => $query
                ->current()
                ->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()->addDays(30))))
            ->eachById(function (Membership $membership) use (&$reminded): void {
                $membership->user->notify(new MembershipExpiringSoon($membership));
                $membership->forceFill(['expiry_reminder_sent_at' => now()])->save();
                $reminded++;
            });

        $expired = 0;

        Membership::query()
            ->with(['user', 'plan'])
            ->where('status', MembershipStatus::Active)
            ->where('expires_at', '<=', now())
            ->eachById(function (Membership $membership) use (&$expired): void {
                $membership->update(['status' => MembershipStatus::Expired]);
                $expired++;

                if (! $membership->user->isActiveMember()) {
                    $membership->user->notify(new MembershipExpired($membership));
                }
            });

        $this->components->info("Sent {$reminded} renewal reminders and expired {$expired} memberships.");

        return self::SUCCESS;
    }
}
