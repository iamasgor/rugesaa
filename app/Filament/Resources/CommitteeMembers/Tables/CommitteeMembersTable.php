<?php

namespace App\Filament\Resources\CommitteeMembers\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommitteeMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('photo_path')->label('')->disk('public')->circular(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('position'),
                TextColumn::make('batch'),
                IconColumn::make('is_featured')->label('Portrait')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
