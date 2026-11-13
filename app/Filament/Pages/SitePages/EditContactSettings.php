<?php

namespace App\Filament\Pages\SitePages;

use App\Enums\SitePage;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class EditContactSettings extends EditSitePage
{
    protected static ?string $slug = 'settings/contact';

    protected static ?string $title = 'Contact & social media';

    protected static ?string $navigationLabel = 'Contact & social';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?int $navigationSort = 0;

    protected static function sitePage(): SitePage
    {
        return SitePage::Contact;
    }

    protected function publicUrl(): string
    {
        return route('contact');
    }

    /**
     * @return array<Component>
     */
    protected function formComponents(): array
    {
        return [
            Section::make('Association')
                ->description('Shown on the Contact page. Messages from the contact form are emailed to this address. Leave a field empty to hide it.')
                ->columns(3)
                ->schema([
                    TextInput::make('association.email')->label('Email')->email()->maxLength(255),
                    TextInput::make('association.phone')->label('Phone')->tel()->maxLength(40),
                    TextInput::make('association.office_hours')->label('Office hours')->placeholder('e.g. Sun–Thu, 10am–4pm')->maxLength(120),
                ]),

            Section::make('Social media')
                ->description('Shown as buttons on the Home and Contact pages, in this order.')
                ->schema([
                    Repeater::make('social.links')
                        ->hiddenLabel()
                        ->schema([
                            TextInput::make('label')->label('Name')->placeholder('Facebook')->required()->maxLength(40),
                            TextInput::make('url')->label('Link')->placeholder('https://facebook.com/…')->url()->required()->maxLength(255),
                        ])
                        ->columns(2)
                        ->addActionLabel('Add social link')
                        ->defaultItems(0)
                        ->maxItems(10),
                ]),

            Section::make('Department office')
                ->description('Shown on the About and Contact pages.')
                ->columns(2)
                ->schema([
                    TextInput::make('department.chairman')->label('Chairman')->required()->maxLength(120),
                    TextInput::make('department.email')->label('Email')->email()->required()->maxLength(255),
                    TextInput::make('department.phone')->label('Phone & fax')->required()->maxLength(40),
                    Textarea::make('department.address')->label('Address')->required()->rows(2)->maxLength(300)->columnSpanFull(),
                    TextInput::make('department.latitude')->label('Map latitude')->numeric()->required()->minValue(-90)->maxValue(90),
                    TextInput::make('department.longitude')->label('Map longitude')->numeric()->required()->minValue(-180)->maxValue(180),
                ]),
        ];
    }
}
