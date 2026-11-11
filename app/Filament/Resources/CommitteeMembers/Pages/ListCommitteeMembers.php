<?php

namespace App\Filament\Resources\CommitteeMembers\Pages;

use App\Filament\Resources\CommitteeMembers\CommitteeMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommitteeMembers extends ListRecords
{
    protected static string $resource = CommitteeMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
