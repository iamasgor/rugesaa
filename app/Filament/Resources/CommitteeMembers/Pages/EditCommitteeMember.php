<?php

namespace App\Filament\Resources\CommitteeMembers\Pages;

use App\Filament\Resources\CommitteeMembers\CommitteeMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommitteeMember extends EditRecord
{
    protected static string $resource = CommitteeMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
