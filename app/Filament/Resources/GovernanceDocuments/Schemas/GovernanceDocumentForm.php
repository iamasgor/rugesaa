<?php

namespace App\Filament\Resources\GovernanceDocuments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GovernanceDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->placeholder('e.g. RUGESAA constitution')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('file_path')
                    ->label('File')
                    ->required()
                    ->disk('public')
                    ->directory('documents')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->helperText('PDF, up to 10 MB. Shown publicly on the About page.'),
            ]);
    }
}
