<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ResourceBarChartWidget extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'resourceBarChartWidget';
    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Resource Bar Chart';

    protected int|string|array $columnSpan = [
        'sm' => 4,
        'md' => 4,
    ];

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $data = Record::query()
            ->with('resource')
            ->selectRaw('resource_id, count(*) as count')
            ->groupBy('resource_id')
            ->get();

        $labels = $data->map(fn($row) => optional($row->resource)->name ?? '-')->toArray();
        $values = $data->pluck('count')->toArray();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 550,
            ],
            'series' => [
                [
                    'name' => 'Resource',
                    'data' => $values,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                        'trim' => false,
                    ],
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal'   => false,
                    'borderRadius' => 3,
                    'columnWidth'  => '55%',
                    'distributed'  => true, // <-- add this
                ],
            ],

            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'dataLabels' => ['enabled' => false],
            'legend' => ['show' => false],
            'colors' => [
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
            ],
        ];
    }
}
