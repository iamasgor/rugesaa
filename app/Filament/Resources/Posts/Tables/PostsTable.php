<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\NewsCategory;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->limit(70),
                TextColumn::make('category')->badge(),
                IconColumn::make('is_featured')->label('Featured')->boolean(),
                TextColumn::make('published_at')->label('Published')->dateTime('j M Y, g:i A')->placeholder('Draft')->sortable(),
                TextColumn::make('author.name')->label('Author')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('category')->options(NewsCategory::class),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View on site')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Post $record): string => route('news.show', $record), shouldOpenInNewTab: true),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
