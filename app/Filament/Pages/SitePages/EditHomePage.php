<?php

namespace App\Filament\Pages\SitePages;

use App\Enums\SitePage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class EditHomePage extends EditSitePage
{
    protected static ?string $slug = 'pages/home';

    protected static ?string $title = 'Home page';

    protected static ?string $navigationLabel = 'Home page';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = -2;

    protected static function sitePage(): SitePage
    {
        return SitePage::Home;
    }

    protected function publicUrl(): string
    {
        return route('home');
    }

    /**
     * @return array<Component>
     */
    protected function formComponents(): array
    {
        return [
            Section::make('Hero')
                ->description('The large photo banner at the top of the page.')
                ->columns(2)
                ->schema([
                    TextInput::make('hero.eyebrow')->label('Small line above the title')->maxLength(120),
                    TextInput::make('hero.title')->label('Title')->required()->maxLength(120),
                    Textarea::make('hero.intro')->label('Introduction')->required()->rows(3)->maxLength(500)->columnSpanFull(),
                    self::image('hero.image', 'Background photo', 'Leave empty to use the default campus photo.'),
                ]),

            Section::make('Get involved')
                ->description('The three cards. Their icons and links stay fixed: directory, events, registration.')
                ->columns(2)
                ->schema([
                    TextInput::make('engage.eyebrow')->label('Small heading')->maxLength(120),
                    TextInput::make('engage.title')->label('Title')->required()->maxLength(160),
                    Repeater::make('engage.items')
                        ->label('Cards')
                        ->schema([
                            TextInput::make('title')->required()->maxLength(80),
                            Textarea::make('body')->required()->rows(2)->maxLength(240),
                            TextInput::make('cta')->label('Button label')->required()->maxLength(40),
                        ])
                        ->columns(3)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columnSpanFull(),
                ]),

            Section::make('Member benefits')
                ->columns(2)
                ->schema([
                    TextInput::make('benefits.eyebrow')->label('Small heading')->maxLength(120),
                    TextInput::make('benefits.title')->label('Title')->required()->maxLength(160),
                    Textarea::make('benefits.intro')->label('Introduction')->rows(2)->maxLength(500)->columnSpanFull(),
                    TextInput::make('benefits.directory_title')->label('Directory search title')->required()->maxLength(160),
                    Textarea::make('benefits.directory_body')->label('Directory search text')->rows(2)->maxLength(300),
                    TextInput::make('benefits.steps_title')->label('Steps title')->required()->maxLength(160)->columnSpanFull(),
                    Repeater::make('benefits.steps')
                        ->label('Steps')
                        ->simple(TextInput::make('text')->required()->maxLength(240))
                        ->minItems(1)
                        ->maxItems(6)
                        ->columnSpanFull(),
                ]),

            Section::make('Events')
                ->description('Shown only when no upcoming event is published; otherwise the next event appears here.')
                ->columns(2)
                ->schema([
                    TextInput::make('events.eyebrow')->label('Small heading')->maxLength(120),
                    TextInput::make('events.title')->label('Title')->required()->maxLength(160),
                    Textarea::make('events.body')->label('Text')->rows(2)->maxLength(500)->columnSpanFull(),
                    self::image('events.image', 'Photo', 'Used when the featured event has no cover image. Leave empty for the default reunion photo.'),
                ]),

            Section::make('News')
                ->columns(2)
                ->schema([
                    TextInput::make('news.eyebrow')->label('Small heading')->maxLength(120),
                    TextInput::make('news.title')->label('Title')->required()->maxLength(160),
                    Textarea::make('news.intro')->label('Introduction')->rows(2)->maxLength(300)->columnSpanFull(),
                ]),

            Section::make('Membership banner')
                ->columns(2)
                ->schema([
                    TextInput::make('join.eyebrow')->label('Small heading')->maxLength(120),
                    TextInput::make('join.title')->label('Title')->required()->maxLength(160),
                    Textarea::make('join.body')->label('Text')->rows(2)->maxLength(300)->columnSpanFull(),
                ]),

            Section::make('Contact')
                ->columns(2)
                ->schema([
                    TextInput::make('contact.eyebrow')->label('Small heading')->maxLength(120),
                    TextInput::make('contact.title')->label('Title')->required()->maxLength(160),
                    Textarea::make('contact.body')->label('Text')->rows(2)->maxLength(300)->columnSpanFull(),
                ]),
        ];
    }

    private static function image(string $name, string $label, string $help): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->helperText($help)
            ->image()
            ->imageEditor()
            ->disk('public')
            ->directory('pages')
            ->maxSize(4096)
            ->columnSpanFull();
    }
}
