<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventType;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\RelationManagers\PhotosRelationManager;
use App\Filament\Resources\Events\RelationManagers\RegistrationsRelationManager;
use App\Models\Event;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Event')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        self::detailsTab(),
                        self::registrationTab(),
                        self::scheduleTab(),
                        self::committeeTab(),
                        self::relatedRecordsTab('Photos', Heroicon::OutlinedPhoto, PhotosRelationManager::class, 'Save the event first, then upload its photos here.'),
                        self::relatedRecordsTab('Attendees', Heroicon::OutlinedUserGroup, RegistrationsRelationManager::class, 'Attendees appear here once the event is saved and members register.'),
                    ]),
            ]);
    }

    private static function detailsTab(): Tab
    {
        return Tab::make('Event')
            ->icon(Heroicon::OutlinedInformationCircle)
            ->columns(2)
            ->schema([
                TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Select::make('type')->options(EventType::class)->required(),
                TextInput::make('venue')->required()->maxLength(255)->placeholder('e.g. Online, or Sir J.C. Bose Academic Building'),
                Textarea::make('summary')->required()->maxLength(500)->rows(2)->columnSpanFull()
                    ->helperText('One or two sentences shown in lists and at the top of the event page.'),
                MarkdownEditor::make('description')->columnSpanFull(),
                FileUpload::make('cover_image_path')
                    ->label('Cover image')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('events')
                    ->maxSize(4096)
                    ->columnSpanFull(),
            ]);
    }

    private static function registrationTab(): Tab
    {
        return Tab::make('When & registration')
            ->icon(Heroicon::OutlinedCalendarDays)
            ->schema([
                Grid::make(3)->schema([
                    DateTimePicker::make('starts_at')->required()->seconds(false),
                    DateTimePicker::make('ends_at')->seconds(false)->after('starts_at'),
                    DateTimePicker::make('registration_closes_at')->label('Registration closes')->seconds(false)->before('starts_at')
                        ->helperText('Leave empty to close when the event starts.'),
                ]),
                Grid::make(4)->schema([
                    TextInput::make('member_fee')->label('Member fee (BDT)')->integer()->minValue(0)->default(0)->required()->prefix('BDT'),
                    TextInput::make('max_guests')->label('Guests per member')->integer()->minValue(0)->maxValue(10)->default(0)->required(),
                    TextInput::make('guest_fee')->label('Fee per guest (BDT)')->integer()->minValue(0)->default(0)->required()->prefix('BDT'),
                    TextInput::make('capacity')->label('Total seats')->integer()->minValue(1)
                        ->helperText('Members plus guests. Leave empty for no limit.'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('online_url')->label('Joining link')->url()->maxLength(255)
                        ->helperText('Shown only to registered members and in their confirmation email.'),
                    DateTimePicker::make('published_at')->label('Publish at')->seconds(false)
                        ->helperText('Leave empty to keep as a draft.'),
                ]),
                Toggle::make('is_featured')->label('Feature on the home and events pages'),
            ]);
    }

    private static function scheduleTab(): Tab
    {
        return Tab::make('Schedule')
            ->icon(Heroicon::OutlinedClock)
            ->schema([
                Repeater::make('schedule')
                    ->hiddenLabel()
                    ->columns(3)
                    ->defaultItems(0)
                    ->reorderable()
                    ->addActionLabel('Add schedule item')
                    ->schema([
                        TextInput::make('time')->required()->placeholder('9:00 AM'),
                        TextInput::make('label')->required()->columnSpan(2)->placeholder('Registration & breakfast'),
                    ]),
            ]);
    }

    private static function committeeTab(): Tab
    {
        return Tab::make('Committee')
            ->icon(Heroicon::OutlinedIdentification)
            ->schema([
                Text::make('The people organising this event. Everything entered here is shown on the event page.'),
                Repeater::make('committee')
                    ->hiddenLabel()
                    ->columns(4)
                    ->defaultItems(0)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => filled($state['name'] ?? null)
                        ? $state['name'].(filled($state['role'] ?? null) ? ' — '.$state['role'] : '')
                        : null)
                    ->addActionLabel('Add committee member')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('role')->required()->maxLength(255)->placeholder('e.g. Convener'),
                        TextInput::make('batch')->maxLength(50)->placeholder('e.g. 1998–99'),
                        TextInput::make('phone')->label('Mobile')->tel()->maxLength(30),
                        FileUpload::make('photo_path')
                            ->label('Photo')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('events/committee')
                            ->maxSize(2048),
                    ]),
            ]);
    }

    /**
     * A tab holding one of the event's related-record tables. Those tables need a
     * saved event, so a new event shows a short note in their place.
     *
     * @param  class-string  $relationManager
     */
    private static function relatedRecordsTab(string $label, Heroicon $icon, string $relationManager, string $unsavedNote): Tab
    {
        return Tab::make($label)
            ->icon($icon)
            ->schema([
                Text::make($unsavedNote)->hiddenOn('edit'),
                Livewire::make($relationManager, fn (?Event $record): array => [
                    'ownerRecord' => $record,
                    'pageClass' => EditEvent::class,
                ])
                    ->key(str($label)->slug()->prepend('event-')->toString())
                    ->visibleOn('edit'),
            ]);
    }
}
