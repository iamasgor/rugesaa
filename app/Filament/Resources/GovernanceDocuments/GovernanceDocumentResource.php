<?php

namespace App\Filament\Resources\GovernanceDocuments;

use App\Filament\Resources\GovernanceDocuments\Pages\CreateGovernanceDocument;
use App\Filament\Resources\GovernanceDocuments\Pages\EditGovernanceDocument;
use App\Filament\Resources\GovernanceDocuments\Pages\ListGovernanceDocuments;
use App\Filament\Resources\GovernanceDocuments\Schemas\GovernanceDocumentForm;
use App\Filament\Resources\GovernanceDocuments\Tables\GovernanceDocumentsTable;
use App\Models\GovernanceDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GovernanceDocumentResource extends Resource
{
    protected static ?string $model = GovernanceDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Constitution & reports';

    public static function form(Schema $schema): Schema
    {
        return GovernanceDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GovernanceDocumentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGovernanceDocuments::route('/'),
            'create' => CreateGovernanceDocument::route('/create'),
            'edit' => EditGovernanceDocument::route('/{record}/edit'),
        ];
    }
}
