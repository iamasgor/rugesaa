<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('tran_id')->label('Transaction ID')->copyable(),
                TextEntry::make('val_id')->label('Validation ID')->placeholder('—')->copyable(),
                TextEntry::make('status')->badge(),
                TextEntry::make('user.name')->label('Member'),
                TextEntry::make('user.email')->label('Email'),
                TextEntry::make('amount')->formatStateUsing(fn (int $state): string => 'BDT '.number_format($state)),
                TextEntry::make('card_type')->label('Method')->placeholder('—'),
                TextEntry::make('paid_at')->dateTime()->placeholder('—'),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
