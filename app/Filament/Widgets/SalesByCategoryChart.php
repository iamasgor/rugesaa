<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Membership;
use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SalesByCategoryChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Sales by product';

    protected ?string $description = 'This year, by membership plan and events.';

    protected ?string $maxHeight = '280px';

    /**
     * @return array{datasets: list<array<string, mixed>>, labels: array<int, string>}
     */
    protected function getData(): array
    {
        $totals = Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->where('paid_at', '>=', now()->startOfYear())
            ->with(['payable' => fn (MorphTo $payable) => $payable->morphWith([Membership::class => ['plan']])])
            ->get()
            ->groupBy(fn (Payment $payment): string => $payment->payable instanceof Membership
                ? $payment->payable->plan->name.' membership'
                : 'Events')
            ->map(fn ($payments): int => (int) $payments->sum('amount'))
            ->sortDesc();

        return [
            'datasets' => [
                [
                    'data' => $totals->values()->all(),
                    'backgroundColor' => array_slice(['#1D4337', '#E0A43A', '#5E8C76', '#8C5A0B', '#9AA8A0', '#17372C'], 0, max(1, $totals->count())),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $totals->keys()->map(fn (int|string $label): string => (string) $label)->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'cutout' => '65%',
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }
}
