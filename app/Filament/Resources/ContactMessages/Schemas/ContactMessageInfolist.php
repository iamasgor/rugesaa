<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')->copyable(),
                TextEntry::make('phone')->placeholder('—'),
                TextEntry::make('batch')->placeholder('—'),
                TextEntry::make('topic')->badge(),
                IconEntry::make('newsletter_opt_in')->label('Wants newsletter')->boolean(),
                TextEntry::make('subject')->columnSpanFull(),
                TextEntry::make('message')->columnSpanFull()->prose(),
                TextEntry::make('created_at')->label('Received')->dateTime(),
            ]);
    }
}
