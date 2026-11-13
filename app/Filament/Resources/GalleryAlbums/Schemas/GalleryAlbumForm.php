<?php

namespace App\Filament\Resources\GalleryAlbums\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GalleryAlbumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Program')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')->required()->maxLength(255)->placeholder('e.g. Grand Reunion 2025'),
                        Textarea::make('description')->maxLength(500)->rows(2)
                            ->helperText('Optional. A line or two shown under the album title.'),
                        self::photoUpload('uploads')
                            ->label('Photos')
                            ->helperText('Select as many photos as you like. You can add titles and reorder them after saving.')
                            ->visibleOn('create'),
                    ]),
                Section::make('Publishing')
                    ->columnSpan(1)
                    ->schema([
                        DatePicker::make('held_on')->label('Program date')
                            ->helperText('Albums are listed newest program first.'),
                        DateTimePicker::make('published_at')->label('Publish at')->seconds(false)->default(now())
                            ->helperText('Leave empty to keep as a draft.'),
                    ]),
            ]);
    }

    /**
     * The shared multi-photo upload field, used for gallery albums and for
     * event photos.
     */
    public static function photoUpload(string $name, string $directory = 'gallery'): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->multiple()
            ->reorderable()
            ->appendFiles()
            ->disk('public')
            ->directory($directory)
            ->maxSize(8192)
            ->maxFiles(100)
            ->panelLayout('grid');
    }
}
