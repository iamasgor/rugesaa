<?php

namespace App\Filament\Resources\Members\Pages;

use App\Enums\MembershipStatus;
use App\Filament\Resources\Members\MemberResource;
use App\Models\MembershipPlan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    /**
     * Members entered by the committee are already known to it, so their email counts as
     * verified and the chosen membership is activated straight away.
     *
     * @param  array{name: string, email: string, password: string, membership_plan_id?: int|string|null, membership_starts_at?: string|null}  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        $planId = $data['membership_plan_id'] ?? null;

        if ($planId !== null && $planId !== '') {
            $plan = MembershipPlan::query()->findOrFail($planId);

            $membership = $user->memberships()->create([
                'membership_plan_id' => $plan->id,
                'amount' => $plan->price,
                'status' => MembershipStatus::PendingVerification,
            ]);

            /** @var User $admin */
            $admin = Auth::user();
            $startsAt = filled($data['membership_starts_at'] ?? null)
                ? CarbonImmutable::parse($data['membership_starts_at'])
                : now();

            $membership->approve($admin, $startsAt);
        }

        return $user;
    }
}
