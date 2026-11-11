<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Memberships\MembershipResource;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Overview';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    /**
     * @return array<string, int|null>
     */
    public function getColumns(): array
    {
        return ['default' => 1, 'lg' => 3];
    }

    public function getSubheading(): string
    {
        return now()->format('l, j F Y').' · sales, orders and seats at a glance';
    }

    /**
     * Quick actions in the page header.
     *
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('reviewMemberships')
                ->label('Review memberships')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->color('gray')
                ->url(MembershipResource::getUrl('index')),
            Action::make('newPost')
                ->label('New post')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->color('gray')
                ->url(PostResource::getUrl('create')),
            Action::make('newEvent')
                ->label('New event')
                ->icon(Heroicon::OutlinedPlus)
                ->url(EventResource::getUrl('create')),
        ];
    }
}
