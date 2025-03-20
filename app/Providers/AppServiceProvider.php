<?php

namespace App\Providers;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
//        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
//            $switch
//                ->locales(['ar','en'])
//                ->displayLocale('en');
//        });

        FilamentView::registerRenderHook(
            PanelsRenderHook::SCRIPTS_AFTER,
            fn (): string => new HtmlString('
        <script>document.addEventListener("scroll-to-top", () => window.scrollTo(0, 0))</script>
            '),
        );
    }
}
