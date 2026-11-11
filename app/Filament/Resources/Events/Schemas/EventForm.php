<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Event')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                        Select::make('type')->options(EventType::class)->required(),
                        TextInput::make('venue')->required()->maxLength(255)->placeholder('e.g. Online, or Sir J.C. Bose Academic Building'),
                        Textarea::make('summary')->required()->maxLength(500)->rows(2)->columnSpanFull()
                            ->helperText('One or two sentences shown in lists and at the top of the event page.'),
                        MarkdownEditor::make('description')->columnSpanFull(),
                        Repeater::make('schedule')
                            ->columnSpanFull()
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Add schedule item')
                            ->schema([
                                TextInput::make('time')->required()->placeholder('9:00 AM'),
                                TextInput::make('label')->required()->columnSpan(2)->placeholder('Registration & breakfast'),
                            ]),
                        FileUpload::make('cover_image_path')
                            ->label('Cover image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('events')
                            ->maxSize(4096)
                            ->columnSpanFull(),
                    ]),
                Section::make('When & registration')
                    ->columnSpan(1)
                    ->schema([
                        DateTimePicker::make('starts_at')->required()->seconds(false),
                        DateTimePicker::make('ends_at')->seconds(false)->after('starts_at'),
                        DateTimePicker::make('registration_closes_at')->label('Registration closes')->seconds(false)->before('starts_at')
                            ->helperText('Leave empty to close when the event starts.'),
                        TextInput::make('member_fee')->label('Member fee (BDT)')->integer()->minValue(0)->default(0)->required()->prefix('BDT'),
                        TextInput::make('max_guests')->label('Guests per member')->integer()->minValue(0)->maxValue(10)->default(0)->required(),
                        TextInput::make('guest_fee')->label('Fee per guest (BDT)')->integer()->minValue(0)->default(0)->required()->prefix('BDT'),
                        TextInput::make('capacity')->label('Total seats')->integer()->minValue(1)
                            ->helperText('Members plus guests. Leave empty for no limit.'),
                        TextInput::make('online_url')->label('Joining link')->url()->maxLength(255)
                            ->helperText('Shown only to registered members and in their confirmation email.'),
                        Toggle::make('is_featured')->label('Feature on the home and events pages'),
                        DateTimePicker::make('published_at')->label('Publish at')->seconds(false)
                            ->helperText('Leave empty to keep as a draft.'),
                    ]),
            ]);
    }
}
