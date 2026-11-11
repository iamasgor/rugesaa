<?php

namespace App\Filament\Resources\CommitteeMembers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CommitteeMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('position')
                    ->placeholder('e.g. President')
                    ->required()
                    ->maxLength(255),
                TextInput::make('batch')
                    ->placeholder('e.g. 1998-99')
                    ->maxLength(20),
                TextInput::make('current_role')
                    ->label('Current role, organization')
                    ->maxLength(255),
                FileUpload::make('photo_path')
                    ->label('Portrait')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('committee')
                    ->maxSize(2048),
                Toggle::make('is_featured')
                    ->label('Show with portrait (office bearers)')
                    ->helperText('Other members are listed by name under the portraits.'),
            ]);
    }
}
