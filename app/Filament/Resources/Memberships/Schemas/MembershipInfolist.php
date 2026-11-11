<?php

namespace App\Filament\Resources\Memberships\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MembershipInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Membership')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('plan.name')->label('Plan'),
                        TextEntry::make('amount')->formatStateUsing(fn (int $state): string => 'BDT '.number_format($state)),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('starts_at')->date()->placeholder('—'),
                        TextEntry::make('expires_at')->date()->placeholder('—'),
                        TextEntry::make('verifier.name')->label('Verified by')->placeholder('—'),
                        TextEntry::make('rejection_reason')->placeholder('—')->columnSpanFull(),
                    ]),
                Section::make('Member')
                    ->columns(3)
                    ->schema([
                        ImageEntry::make('user.profile.photo_path')->label('Photo')->disk('public')->circular()->placeholder('No photo'),
                        TextEntry::make('user.name')->label('Name'),
                        TextEntry::make('user.email')->label('Email')->copyable(),
                        TextEntry::make('user.profile.phone')->label('Mobile'),
                        TextEntry::make('user.profile.location')->label('City & country')->placeholder('—'),
                    ]),
                Section::make('Academic record (check against department records)')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.profile.degree')->label('Degree'),
                        TextEntry::make('user.profile.batch')->label('Batch'),
                        TextEntry::make('user.profile.student_id')->label('Student ID')->copyable(),
                        TextEntry::make('user.profile.hall')->label('Hall')->placeholder('—'),
                        TextEntry::make('user.profile.specialization')->label('Specialization'),
                    ]),
                Section::make('Work & preferences')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('user.profile.position')->label('Position')->placeholder('—'),
                        TextEntry::make('user.profile.organization')->label('Organization')->placeholder('—'),
                        TextEntry::make('user.profile.sector')->label('Sector'),
                        IconEntry::make('user.profile.mentor_opt_in')->label('Mentor')->boolean(),
                        IconEntry::make('user.profile.show_in_directory')->label('In directory')->boolean(),
                        IconEntry::make('user.profile.newsletter_opt_in')->label('Newsletter')->boolean(),
                    ]),
            ]);
    }
}
