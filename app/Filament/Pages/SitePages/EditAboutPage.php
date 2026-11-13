<?php

namespace App\Filament\Pages\SitePages;

use App\Enums\SitePage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class EditAboutPage extends EditSitePage
{
    protected static ?string $slug = 'pages/about';

    protected static ?string $title = 'About page';

    protected static ?string $navigationLabel = 'About page';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    protected static ?int $navigationSort = -1;

    protected static function sitePage(): SitePage
    {
        return SitePage::About;
    }

    protected function publicUrl(): string
    {
        return route('about');
    }

    /**
     * @return array<Component>
     */
    protected function formComponents(): array
    {
        return [
            Section::make('Page header')
                ->schema([
                    TextInput::make('hero.title')->label('Title')->required()->maxLength(160),
                    Textarea::make('hero.intro')->label('Introduction')->required()->rows(3)->maxLength(600),
                ]),

            Section::make('Mission')
                ->columns(2)
                ->schema([
                    TextInput::make('mission.eyebrow')->label('Small heading')->maxLength(120),
                    TextInput::make('mission.title')->label('Title')->required()->maxLength(160),
                    Textarea::make('mission.body')->label('Text')->rows(4)->maxLength(1000)->columnSpanFull(),
                    Repeater::make('mission.pillars')
                        ->label('Goals')
                        ->schema([
                            TextInput::make('heading')->required()->maxLength(60),
                            Textarea::make('body')->required()->rows(2)->maxLength(240),
                        ])
                        ->columns(2)
                        ->maxItems(8)
                        ->columnSpanFull(),
                ]),

            Section::make('History')
                ->columns(2)
                ->schema([
                    TextInput::make('history.eyebrow')->label('Small heading')->maxLength(120),
                    TextInput::make('history.title')->label('Title')->required()->maxLength(160),
                    Textarea::make('history.body')->label('Text')->rows(4)->maxLength(1500)->columnSpanFull(),
                    Repeater::make('history.milestones')
                        ->label('Timeline')
                        ->schema([
                            TextInput::make('year')->required()->maxLength(20),
                            TextInput::make('heading')->required()->maxLength(80),
                            Textarea::make('body')->required()->rows(2)->maxLength(240),
                        ])
                        ->columns(3)
                        ->maxItems(12)
                        ->columnSpanFull(),
                    FileUpload::make('history.image')
                        ->label('Photo')
                        ->helperText('Leave empty to use the default department building photo.')
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('pages')
                        ->maxSize(4096)
                        ->columnSpanFull(),
                    TextInput::make('history.image_alt')->label('Photo description (for screen readers)')->maxLength(200),
                    TextInput::make('history.image_caption')->label('Photo caption')->maxLength(200),
                ]),

            Section::make('The department today')
                ->columns(2)
                ->schema([
                    TextInput::make('department.eyebrow')->label('Small heading')->maxLength(120),
                    TextInput::make('department.title')->label('Title')->required()->maxLength(160),
                    Textarea::make('department.body')->label('Text')->rows(3)->maxLength(1500)->columnSpanFull(),
                    Repeater::make('department.stats')
                        ->label('Figures')
                        ->schema([
                            TextInput::make('figure')->required()->maxLength(12),
                            TextInput::make('label')->required()->maxLength(80),
                        ])
                        ->columns(2)
                        ->maxItems(8)
                        ->columnSpanFull(),
                    Repeater::make('department.laboratories')
                        ->label('Laboratories')
                        ->schema([
                            TextInput::make('name')->required()->maxLength(120),
                            Textarea::make('description')->required()->rows(2)->maxLength(400),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->columnSpanFull(),
                    TagsInput::make('department.partners_abroad')->label('Partners abroad')->helperText('Press Enter after each partner.'),
                    TagsInput::make('department.partners_local')->label('Partners in Bangladesh')->helperText('Press Enter after each partner.'),
                ]),

            Section::make('Constitution & reports')
                ->description('The documents themselves are managed under Governance documents.')
                ->columns(2)
                ->schema([
                    TextInput::make('documents.eyebrow')->label('Small heading')->maxLength(120),
                    TextInput::make('documents.title')->label('Title')->required()->maxLength(160),
                    Textarea::make('documents.body')->label('Text')->rows(2)->maxLength(500)->columnSpanFull(),
                ]),
        ];
    }
}
