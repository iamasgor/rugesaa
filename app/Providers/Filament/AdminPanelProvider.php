<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    /**
     * The website's forest green as a full scale, anchored so primary-600
     * (Filament's button colour) is exactly the site's #1D4337.
     *
     * @var array<int, string>
     */
    private const array FOREST = [
        50 => '#EEF4F0',
        100 => '#DCE8E0',
        200 => '#B9CFC1',
        300 => '#8DB09C',
        400 => '#5F8C76',
        500 => '#3A6B57',
        600 => '#1D4337',
        700 => '#17372C',
        800 => '#10291F',
        900 => '#0C2018',
        950 => '#07140F',
    ];

    /**
     * Warm, slightly green neutrals taken from the site's paper, line and ink colours.
     *
     * @var array<int, string>
     */
    private const array PAPER = [
        50 => '#F4F5EF',
        100 => '#E9ECE4',
        200 => '#DCE2D9',
        300 => '#C9D1C7',
        400 => '#9AA8A0',
        500 => '#6E7D76',
        600 => '#4B5B54',
        700 => '#33433D',
        800 => '#22312B',
        900 => '#14231E',
        950 => '#0B1612',
    ];

    public function boot(): void
    {
        /*
         * List screens filter inline beside the search box and apply as soon as
         * a value changes, matching the shop back office's toolbar.
         */
        Table::configureUsing(fn (Table $table): Table => $table
            ->filtersLayout(FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->paginationPageOptions([10, 25, 50, 100])
            ->defaultPaginationPageOption(25));
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName('RUGESAA Admin')
            ->brandLogo(fn (): View => view('filament.admin.brand'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('favicon-32x32.png'))
            ->colors([
                'primary' => self::FOREST,
                'warning' => Color::hex('#E0A43A'),
                'gray' => self::PAPER,
            ])
            ->font('Archivo')
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->maxContentWidth(Width::Full)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups(['Sales', 'Catalog', 'Website', 'Support'])
            ->unsavedChangesAlerts()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
