<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Filament\Widgets\ChartWidget;

class ExhibitionPieChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Exhibition Pie Chart';
    protected int | string | array $columnSpan = '4';

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'x' => ['display' => false], // Ensure X-axis is hidden
                'y' => ['display' => false], // Ensure Y-axis is hidden
            ],
        ];
    }

    protected function getData(): array
    {
        $count = Record::query()
            ->selectRaw('exhibition, COUNT(*) as count')
            ->whereNotNull('exhibition')
            ->groupBy('exhibition')
            ->pluck('count', 'exhibition')
            ->toArray();

        $backgroundColors = $this->assignExhibitionColors(array_keys($count));

        return [
            'datasets' => [
                [
                    'label' => 'Exhibition Count',
                    'data' => array_values($count),
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => array_keys($count),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * Assign colors from a predefined exhibition-specific palette.
     */
    private function assignExhibitionColors(array $categories): array
    {
        $exhibitionColorPalette = [
            "#e74c3c", "#1abc9c", "#d35400", "#2e4053", "#16a085",
            "#2c3e50", "#8e44ad", "#c0392b", "#34495e", "#9b59b6",
            "#6c3483", "#a93226", "#154360", "#5b2c6f", "#d68910",
            "#f39c12", "#7f8c8d", "#b03a2e", "#4a235a", "#283747",
            "#0e6251", "#512e5f", "#943126", "#1b4f72", "#7d3c98",
            "#641e16", "#2471a3", "#229954", "#d4ac0d", "#117864",
            "#784212", "#212f3c", "#1c2833", "#7e5109", "#196f3d",
            "#873600", "#283747", "#a04000", "#4a235a", "#145a32",
            "#7d6608", "#0b5345", "#424949", "#4d5656", "#626567",
        ];

        $colors = [];
        foreach ($categories as $index => $category) {
            $colors[] = $exhibitionColorPalette[$index % count($exhibitionColorPalette)];
        }

        return $colors;
    }
}
