<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Filament\Widgets\ChartWidget;

class ResourcePieChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Resource Pie Chart';
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
        $countResource = Record::query()
            ->selectRaw('resource, COUNT(*) as count')
            ->whereNotNull('resource')
            ->groupBy('resource')
            ->pluck('count', 'resource')
            ->toArray();

        $backgroundColors = $this->assignResourceColors(array_keys($countResource));

        return [
            'datasets' => [
                [
                    'label' => 'Resource Count',
                    'data' => array_values($countResource),
                    'backgroundColor' => $backgroundColors,
                ]
            ],
            'labels' => array_keys($countResource)
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * Assign colors from a different resource-specific palette.
     */
    private function assignResourceColors(array $categories): array
    {
        $colorPalette = [
            "#8e44ad", "#3498db", "#f1c40f", "#e74c3c", "#d35400",
            "#16a085", "#f1c40f", "#f39c12", "#c0392b", "#9b59b6",
            "#e74c3c", "#f39c12", "#16a085", "#27ae60", "#2980b9",
            "#f39c12", "#e67e22", "#d35400", "#e74c3c", "#c0392b",
            "#9b59b6", "#8e44ad", "#34495e", "#2ecc71", "#1abc9c",
            "#2ecc71", "#34495e", "#9b59b6", "#16a085", "#f39c12",
            "#1abc9c", "#e67e22", "#c0392b", "#8e44ad", "#7f8c8d",
            "#2980b9", "#f39c12", "#2ecc71", "#f1c40f", "#d35400",
            "#9b59b6", "#16a085", "#1abc9c", "#e74c3c", "#e67e22",
            "#f39c12", "#8e44ad", "#c0392b", "#3498db", "#7f8c8d"
        ];

        $colors = [];
        foreach ($categories as $index => $category) {
            $colors[] = $colorPalette[$index % count($colorPalette)];
        }

        return $colors;
    }
}
