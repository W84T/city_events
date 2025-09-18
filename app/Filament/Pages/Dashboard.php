<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ExhibitionPieChartWidget;
use App\Filament\Widgets\ResourceBarChartWidget;
use App\Filament\Widgets\SectorBarChartWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use ToneGabes\Filament\Icons\Enums\Phosphor;


class Dashboard extends BaseDashboard
{
    public static function getNavigationIcon(): string|null
    {
        return Phosphor::House->regular();
    }

    public static function getActiveNavigationIcon(): string|null
    {
        return Phosphor::House->duotone();
    }

    public function getColumns(): int|array
    {
        return 4;
    }

    public function getWidgets(): array
    {
        return [
            ExhibitionPieChartWidget::class,
            SectorBarChartWidget::class,
            ResourceBarChartWidget::class,
        ];
    }
}
