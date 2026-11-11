<?php

namespace App\Filament\Resources\Memberships\Pages;

use App\Filament\Resources\Memberships\Actions\MembershipReviewActions;
use App\Filament\Resources\Memberships\MembershipResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMembership extends ViewRecord
{
    protected static string $resource = MembershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            MembershipReviewActions::approve(),
            MembershipReviewActions::reject(),
        ];
    }
}
