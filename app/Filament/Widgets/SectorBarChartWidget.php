<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class SectorBarChartWidget extends ApexChartWidget
{
    protected static ?string $chartId   = 'recordsBySectorChart';
    protected static ?string $heading   = 'Records per Sector';

    protected int|string|array $columnSpan = [
        'sm' => 4,
        'md' => 3,
    ];

    protected function getOptions(): array
    {
        // Get counts grouped by sector
        $data = Record::query()
            ->with('sector')
            ->selectRaw('sector_id, COUNT(*) as total')
            ->groupBy('sector_id')
            ->get();

        $labels = $data->map(fn($row) => optional($row->sector)->name ?? '—')->toArray();
        $values = $data->pluck('total')->toArray();

        return [
            'chart' => [
                'type'   => 'bar',
                'height' => 305,
            ],

            'series' => [
                [
                    'name' => 'Records',
                    'data' => $values,
                ],
            ],

            'xaxis' => [
                'categories' => $labels,
                'labels' => [
                    'style' => ['fontFamily' => 'inherit'],
                    'trim'  => true,
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

            'dataLabels' => ['enabled' => false],

            'tooltip' => [
                'enabled' => true,
                'y' => [
                    'formatter' => $this->js('function(value) { return value + " records"; }'),
                ],
            ],

            'legend' => ['show' => false],

            'colors'=> [
                "#ff073a", "#00f7ff", "#ffea00", "#8000ff", "#00ff44",
                "#ff0099", "#00ffcc", "#ff4d00", "#ff00ff", "#00ff00",
                "#ff4500", "#00ffff", "#ff1493", "#00ffbb", "#ffcc00",
                "#7d00ff", "#ff3300", "#00e6e6", "#ff007f", "#00ff80",
                "#ff9900", "#00d9ff", "#ff00cc", "#33ff00", "#ff0033",
                "#00ffee", "#ff6600", "#6600ff", "#33ffcc", "#ff1100",
                "#00ccff", "#ff0066", "#00ff55", "#ffcc33", "#0033ff",
                "#ff00aa", "#00ffaa", "#ff8800", "#4400ff", "#00ffdd",
                "#ff2200", "#00bbff", "#ff44cc", "#22ff00", "#ff5500",
                "#00aaff", "#ff77cc", "#00ff33", "#ff3300", "#00ffbb",
            ],
        ];

    }
}
