<?php

namespace App\Filament\Resources\GovernanceDocuments\Pages;

use App\Filament\Resources\GovernanceDocuments\GovernanceDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGovernanceDocuments extends ListRecords
{
    protected static string $resource = GovernanceDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
