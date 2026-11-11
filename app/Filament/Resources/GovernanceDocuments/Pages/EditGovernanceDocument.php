<?php

namespace App\Filament\Resources\GovernanceDocuments\Pages;

use App\Filament\Resources\GovernanceDocuments\GovernanceDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGovernanceDocument extends EditRecord
{
    protected static string $resource = GovernanceDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
