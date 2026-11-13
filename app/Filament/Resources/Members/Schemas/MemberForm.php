<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Enums\Degree;
use App\Enums\Sector;
use App\Enums\Specialization;
use App\Models\MembershipPlan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Account')
                    ->description('The member signs in to the portal with this email and password.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Full name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rule(Password::default())
                            ->confirmed()
                            ->visibleOn('create'),
                        TextInput::make('password_confirmation')
                            ->label('Confirm password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->dehydrated(false)
                            ->visibleOn('create'),
                    ]),

                Section::make('Profile')
                    ->relationship('profile')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('phone')
                            ->tel()
                            ->placeholder('+8801712345678')
                            ->required()
                            ->maxLength(32)
                            ->regex('/^\+?[0-9\s\-]{8,20}$/'),
                        FileUpload::make('photo_path')
                            ->label('Photo')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('profile-photos')
                            ->maxSize(2048),
                        Select::make('degree')
                            ->options(Degree::class)
                            ->required(),
                        TextInput::make('batch')
                            ->placeholder('e.g. 1998-99')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('student_id')
                            ->label('Student ID')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('hall')
                            ->maxLength(255),
                        Select::make('specialization')
                            ->options(Specialization::class)
                            ->required(),
                        Select::make('sector')
                            ->options(Sector::class)
                            ->required(),
                        TextInput::make('position')
                            ->maxLength(255),
                        TextInput::make('organization')
                            ->maxLength(255),
                        TextInput::make('location')
                            ->maxLength(255),
                        Toggle::make('show_in_directory')
                            ->label('Show in member directory')
                            ->default(true),
                        Toggle::make('mentor_opt_in')
                            ->label('Open to mentoring'),
                        Toggle::make('newsletter_opt_in')
                            ->label('Receives newsletter')
                            ->default(true),
                    ]),

                Section::make('Membership')
                    ->description('Activated immediately, since the committee has already verified this member. Leave the plan empty to only create the account.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->visibleOn('create')
                    ->schema([
                        Select::make('membership_plan_id')
                            ->label('Plan')
                            ->options(fn (): array => MembershipPlan::query()->active()->pluck('name', 'id')->all()),
                        DatePicker::make('membership_starts_at')
                            ->label('Membership starts')
                            ->default(now())
                            ->required(fn ($get): bool => filled($get('membership_plan_id'))),
                    ]),
            ]);
    }
}
