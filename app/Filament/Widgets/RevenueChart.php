<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Sales';

    protected ?string $description = 'Paid transactions, memberships and events stacked.';

    protected int|string|array $columnSpan = ['lg' => 2];

    protected ?string $maxHeight = '280px';

    public ?string $filter = 'year';

    /**
     * @return array<string, string>
     */
    protected function getFilters(): array
    {
        return [
            'week' => 'Last 7 days',
            'month' => 'Last 30 days',
            'year' => 'Last 12 months',
        ];
    }

    /**
     * @return array{datasets: list<array<string, mixed>>, labels: array<int, string>}
     */
    protected function getData(): array
    {
        $buckets = $this->buckets();
        $payments = Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->where('paid_at', '>=', $buckets->first()['from'])
            ->get(['amount', 'paid_at', 'payable_type']);

        $totalFor = fn (string $payableType): array => $buckets
            ->map(fn (array $bucket): int => (int) $payments
                ->where('payable_type', $payableType)
                ->filter(fn (Payment $payment): bool => $payment->paid_at !== null
                    && $payment->paid_at->betweenIncluded($bucket['from'], $bucket['until']))
                ->sum('amount'))
            ->values()
            ->all();

        return [
            'datasets' => [
                [
                    'label' => 'Memberships',
                    'data' => $totalFor('membership'),
                    'backgroundColor' => '#1D4337',
                    'borderColor' => '#1D4337',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Events',
                    'data' => $totalFor('event_registration'),
                    'backgroundColor' => '#E0A43A',
                    'borderColor' => '#E0A43A',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $buckets->map(fn (array $bucket): string => $bucket['label'])->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => true, 'grid' => ['display' => false]],
                'y' => ['stacked' => true, 'beginAtZero' => true],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }

    /**
     * The time slots the chart is split into for the selected filter, oldest first.
     *
     * @return Collection<int, array{label: string, from: CarbonImmutable, until: CarbonImmutable}>
     */
    private function buckets(): Collection
    {
        if ($this->filter === 'year') {
            return collect(range(11, 0))->map(fn (int $monthsAgo): array => [
                'label' => now()->subMonthsNoOverflow($monthsAgo)->format('M y'),
                'from' => now()->subMonthsNoOverflow($monthsAgo)->startOfMonth(),
                'until' => now()->subMonthsNoOverflow($monthsAgo)->endOfMonth(),
            ]);
        }

        $days = $this->filter === 'week' ? 7 : 30;

        return collect(range($days - 1, 0))->map(fn (int $daysAgo): array => [
            'label' => today()->subDays($daysAgo)->format('j M'),
            'from' => today()->subDays($daysAgo)->startOfDay(),
            'until' => today()->subDays($daysAgo)->endOfDay(),
        ]);
    }
}
