<?php

namespace App\Filament\Resources\Memberships;

use App\Enums\MembershipStatus;
use App\Filament\Resources\Memberships\Pages\ListMemberships;
use App\Filament\Resources\Memberships\Pages\ViewMembership;
use App\Filament\Resources\Memberships\Schemas\MembershipInfolist;
use App\Filament\Resources\Memberships\Tables\MembershipsTable;
use App\Models\Membership;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MembershipResource extends Resource
{
    protected static ?string $model = Membership::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $pending = Membership::query()->where('status', MembershipStatus::PendingVerification)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return MembershipInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembershipsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberships::route('/'),
            'view' => ViewMembership::route('/{record}'),
        ];
    }
}
