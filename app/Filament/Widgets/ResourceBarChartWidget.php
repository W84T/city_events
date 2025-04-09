<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ResourceBarChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Resource Bar Chart';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '500px';

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [

                'legend' => [
                    'display' => false, // hide legend
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'intersect' => false,
            ],
            'hover' => [
                'mode' => 'nearest',
                'intersect' => true,
            ],
        ];
    }


    protected function getData(): array
    {
        $data = Record::query()
            ->select('associations.name as resource_name', DB::raw('COUNT(*) as count'))
            ->join('associations', 'records.resource_id', '=', 'associations.id')
            ->whereNotNull('records.resource_id')
            ->where('associations.type', 'resource')
            ->groupBy('associations.name')
            ->pluck('count', 'resource_name')
            ->toArray();

        $backgroundColors = $this->assignResourceColors(array_keys($data));

        return [
            'datasets' => [
                [
                    'label' => '',
                    'data' => array_values($data),
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 0,
                    'borderRadius' => 7,
                ]
            ],
            'labels' => array_keys($data)
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

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
