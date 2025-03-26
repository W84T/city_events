<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Filament\Widgets\ChartWidget;

class SectorPieChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Sector Pie Chart';
    protected int|string|array $columnSpan = '4';

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
            ->selectRaw('sector, COUNT(*) as count')
            ->wherenotNull('sector')
            ->groupBy('sector')
            ->pluck('count', 'sector')
            ->toArray();

        $backgroundColors = $this->assignColors(array_keys($count));

        return [
            'datasets' => [
                [
                    'label' => 'Sector Count',
                    'data' => array_values($count),
                    'backgroundColor' => $backgroundColors,
                ]
            ],
            'labels' => array_keys($count),
        ];
    }

    /**
     * Assign colors from a predefined palette.
     */
    private function assignColors(array $categories): array
    {
        $colorPalette = [
            "#ff073a", "#00f7ff", "#ffea00", "#8000ff", "#00ff44",
            "#ff0099", "#00ffcc", "#ff4d00", "#ff00ff", "#00ff00",
            "#ff4500", "#00ffff", "#ff1493", "#00ffbb", "#ffcc00",
            "#7d00ff", "#ff3300", "#00e6e6", "#ff007f", "#00ff80",
            "#ff9900", "#00d9ff", "#ff00cc", "#33ff00", "#ff0033",
            "#00ffee", "#ff6600", "#6600ff", "#33ffcc", "#ff1100",
            "#00ccff", "#ff0066", "#00ff55", "#ffcc33", "#0033ff",
            "#ff00aa", "#00ffaa", "#ff8800", "#4400ff", "#00ffdd",
            "#ff2200", "#00bbff", "#ff44cc", "#22ff00", "#ff5500",
            "#00aaff", "#ff77cc", "#00ff33", "#ff3300", "#00ffbb"
        ];
        $colors = [];
        foreach ($categories as $index => $category) {
            $colors[] = $colorPalette[$index % count($colorPalette)];
        }

        return $colors;
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
