<?php

namespace App\Filament\Resources\GalleryAlbums\Tables;

use App\Models\GalleryAlbum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GalleryAlbumsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('coverPhoto')->withCount('photos'))
            ->defaultSort('held_on', 'desc')
            ->columns([
                ImageColumn::make('coverPhoto.image_path')->label('Cover')->disk('public')->square()->imageSize(48),
                TextColumn::make('title')->searchable()->limit(60),
                TextColumn::make('photos_count')->label('Photos')->numeric(),
                TextColumn::make('held_on')->label('Program date')->date('j M Y')->placeholder('—')->sortable(),
                TextColumn::make('published_at')->label('Published')->dateTime('j M Y, g:i A')->placeholder('Draft')->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View on site')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (GalleryAlbum $record): string => route('gallery.show', $record), shouldOpenInNewTab: true),
                EditAction::make(),
                DeleteAction::make()
                    ->modalDescription('The album and all of its photos will be deleted permanently.'),
            ]);
    }
}
