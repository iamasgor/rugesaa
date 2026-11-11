<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\NewsCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Story')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')->required()->maxLength(255),
                        Textarea::make('excerpt')->required()->maxLength(500)->rows(2)
                            ->helperText('One or two sentences shown in news lists and under the headline.'),
                        MarkdownEditor::make('body')->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('news/attachments'),
                    ]),
                Section::make('Publishing')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('category')->options(NewsCategory::class)->required(),
                        FileUpload::make('cover_image_path')
                            ->label('Cover image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('news')
                            ->maxSize(4096),
                        TextInput::make('cover_image_alt')
                            ->label('Image description')
                            ->maxLength(255)
                            ->helperText('What the photo shows, for screen readers.'),
                        Toggle::make('is_featured')->label('Feature at the top of the news page'),
                        DateTimePicker::make('published_at')->label('Publish at')->seconds(false)
                            ->helperText('Leave empty to keep as a draft. A future date schedules the story.'),
                    ]),
            ]);
    }
}
