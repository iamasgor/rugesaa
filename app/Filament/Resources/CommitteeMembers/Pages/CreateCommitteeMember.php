<?php

namespace App\Filament\Resources\CommitteeMembers\Pages;

use App\Filament\Resources\CommitteeMembers\CommitteeMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCommitteeMember extends CreateRecord
{
    protected static string $resource = CommitteeMemberResource::class;
}
