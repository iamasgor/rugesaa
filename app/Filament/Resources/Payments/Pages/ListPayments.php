<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $countsByStatus = Payment::query()
            ->toBase()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $tabs = ['all' => Tab::make('All')->badge($countsByStatus->sum())];

        foreach (PaymentStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->badge($countsByStatus->get($status->value, 0))
                ->badgeColor($status->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', $status));
        }

        return $tabs;
    }
}
