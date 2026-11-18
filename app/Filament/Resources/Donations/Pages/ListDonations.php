<?php

namespace App\Filament\Resources\Donations\Pages;

use App\Filament\Resources\Donations\DonationResource;
use App\Models\Donation;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDonations extends ListRecords
{
    protected static string $resource = DonationResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'paid' => Tab::make('Received')
                ->badge(Donation::query()->paid()->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNotNull('paid_at')),
            'unpaid' => Tab::make('Not paid')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNull('paid_at')),
            'all' => Tab::make('All'),
        ];
    }
}
