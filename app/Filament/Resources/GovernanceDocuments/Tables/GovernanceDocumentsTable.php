<?php

namespace App\Filament\Resources\GovernanceDocuments\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GovernanceDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('updated_at')->label('Updated')->date(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
