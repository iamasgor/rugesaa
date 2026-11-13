<?php

namespace App\Filament\Resources\GalleryAlbums;

use App\Filament\Resources\GalleryAlbums\Pages\CreateGalleryAlbum;
use App\Filament\Resources\GalleryAlbums\Pages\EditGalleryAlbum;
use App\Filament\Resources\GalleryAlbums\Pages\ListGalleryAlbums;
use App\Filament\Resources\GalleryAlbums\RelationManagers\PhotosRelationManager;
use App\Filament\Resources\GalleryAlbums\Schemas\GalleryAlbumForm;
use App\Filament\Resources\GalleryAlbums\Tables\GalleryAlbumsTable;
use App\Models\GalleryAlbum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GalleryAlbumResource extends Resource
{
    protected static ?string $model = GalleryAlbum::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Gallery';

    protected static ?string $modelLabel = 'album';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return GalleryAlbumForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GalleryAlbumsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PhotosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleryAlbums::route('/'),
            'create' => CreateGalleryAlbum::route('/create'),
            'edit' => EditGalleryAlbum::route('/{record}/edit'),
        ];
    }
}
