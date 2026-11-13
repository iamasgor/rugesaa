<?php

namespace App\Filament\Resources\GalleryAlbums\RelationManagers;

use App\Filament\Resources\GalleryAlbums\Schemas\GalleryAlbumForm;
use App\Models\GalleryAlbum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;

/**
 * @property GalleryAlbum $ownerRecord
 */
class PhotosRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image_path')
                    ->label('Photo')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('gallery')
                    ->maxSize(8192)
                    ->required(),
                TextInput::make('title')->maxLength(255)->placeholder('Optional caption'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->paginated([25, 50, 100])
            ->columns([
                ImageColumn::make('image_path')->label('Photo')->disk('public')->imageHeight(64),
                TextInputColumn::make('title')->placeholder('Add an optional title')->rules(['nullable', 'max:255']),
            ])
            ->headerActions([
                Action::make('upload')
                    ->label('Upload photos')
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->schema([
                        GalleryAlbumForm::photoUpload('uploads')->label('Photos')->required(),
                    ])
                    ->action(function (array $data): void {
                        $this->addPhotos(array_values($data['uploads']));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Appends uploaded files to the end of the album, in the order chosen.
     *
     * @param  list<string>  $paths
     */
    public function addPhotos(array $paths): void
    {
        $album = $this->ownerRecord;
        $position = (int) $album->photos()->max('sort_order');

        foreach ($paths as $path) {
            $album->photos()->create(['image_path' => $path, 'sort_order' => ++$position]);
        }

        Notification::make()
            ->success()
            ->title(trans_choice('{1} :count photo added|[2,*] :count photos added', count($paths)))
            ->send();
    }
}
