<?php

namespace App\Filament\Widgets;

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Memberships\MembershipResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Membership;
use App\Models\Payment;
use Carbon\CarbonInterface;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class SalesOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        return [
            $this->todaysSales(),
            $this->monthlyRevenue(),
            $this->pendingOrders(),
            $this->activeMembers(),
        ];
    }

    private function todaysSales(): Stat
    {
        $today = $this->paidBetween(today(), now());

        return Stat::make("Today's sales", 'BDT '.number_format((int) $today->sum('amount')))
            ->description($today->count().' paid transactions')
            ->descriptionIcon(Heroicon::OutlinedReceiptPercent)
            ->chart($this->dailyTotals(days: 7))
            ->color('primary')
            ->url(PaymentResource::getUrl('index'));
    }

    private function monthlyRevenue(): Stat
    {
        $thisMonth = (int) $this->paidBetween(now()->startOfMonth(), now())->sum('amount');
        $lastMonthSamePeriod = (int) $this->paidBetween(
            now()->subMonthNoOverflow()->startOfMonth(),
            now()->subMonthNoOverflow(),
        )->sum('amount');

        $stat = Stat::make('Revenue this month', 'BDT '.number_format($thisMonth))
            ->chart($this->dailyTotals(days: (int) now()->day));

        if ($lastMonthSamePeriod === 0) {
            return $stat->description('No sales last month to compare')->color('gray');
        }

        $change = (int) round(($thisMonth - $lastMonthSamePeriod) / $lastMonthSamePeriod * 100);

        return $change >= 0
            ? $stat->description("{$change}% up on last month")->descriptionIcon(Heroicon::ArrowTrendingUp)->color('success')
            : $stat->description(abs($change).'% down on last month')->descriptionIcon(Heroicon::ArrowTrendingDown)->color('danger');
    }

    private function pendingOrders(): Stat
    {
        $awaitingVerification = Membership::query()->where('status', MembershipStatus::PendingVerification)->count();
        $unpaidCheckouts = Payment::query()->where('status', PaymentStatus::Pending)->count();

        return Stat::make('Awaiting verification', number_format($awaitingVerification))
            ->description(number_format($unpaidCheckouts).' unpaid checkouts')
            ->descriptionIcon(Heroicon::OutlinedClock)
            ->color($awaitingVerification > 0 ? 'warning' : 'gray')
            ->url(MembershipResource::getUrl('index'));
    }

    private function activeMembers(): Stat
    {
        $activeMembers = Membership::query()->current()->distinct()->count('user_id');
        $joinedThisMonth = Membership::query()
            ->where('status', MembershipStatus::Active)
            ->whereNull('renewal_of_id')
            ->where('verified_at', '>=', now()->startOfMonth())
            ->count();

        return Stat::make('Active members', number_format($activeMembers))
            ->description("+{$joinedThisMonth} joined this month")
            ->descriptionIcon(Heroicon::OutlinedUserPlus)
            ->color('info');
    }

    /**
     * @return Builder<Payment>
     */
    private function paidBetween(CarbonInterface $from, CarbonInterface $until): Builder
    {
        return Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [$from, $until]);
    }

    /**
     * Paid totals for each of the last few days, oldest first, for the stat sparkline.
     *
     * @return array<float>
     */
    private function dailyTotals(int $days): array
    {
        $totalsByDay = $this->paidBetween(today()->subDays($days - 1), now())
            ->get(['amount', 'paid_at'])
            ->groupBy(fn (Payment $payment): string => $payment->paid_at?->toDateString() ?? '')
            ->map(fn ($payments): int => (int) $payments->sum('amount'));

        return collect(range($days - 1, 0))
            ->map(fn (int $daysAgo): float => (float) $totalsByDay->get(today()->subDays($daysAgo)->toDateString(), 0))
            ->all();
    }
}
