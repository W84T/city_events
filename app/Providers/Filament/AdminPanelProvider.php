<?php

namespace App\Providers\Filament;


use App\Filament\Resources\Records\RecordResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->databaseNotifications()
            ->login()
            ->colors([
                'primary' => [
                    50 => '255, 218, 233',   // Very light pinkish tone
                    100 => '255, 191, 210',   // Soft pastel pink
                    200 => '255, 163, 188',   // Light and subtle pink
                    300 => '255, 135, 167',   // A bit more saturated, still soft
                    400 => '255, 108, 145',   // A rich pink, but not too dark
                    500 => '170, 0, 79',      // Base color: #AA004F (rich magenta)
                    600 => '150, 0, 71',      // Darker, richer magenta
                    700 => '130, 0, 63',      // Even darker, more intense
                    800 => '110, 0, 55',      // Deep, almost maroon tone
                    900 => '90, 0, 47',       // Almost a wine-like shade
                    950 => '60, 0, 31',       // Very dark, close to burgundy
                ],
//                "gray" => Color::Stone,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->brandLogoHeight('4rem')
            ->brandLogo(asset('storage/logo.svg'))
            ->favicon(asset('storage/favicon.svg'))
            ->darkModeBrandLogo(asset('storage/logo_white.svg'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
//                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentApexChartsPlugin::make()
            ])
            ->navigationItems([
                NavigationItem::make('Add Record')
                    ->icon(Phosphor::PlusCircle)
                    ->activeIcon(Phosphor::PlusCircleDuotone)
                    ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.records.create'))
                    ->sort(1)
                    ->url(fn () => RecordResource::getUrl('create')),
            ])
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
