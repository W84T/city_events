<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ExhibitionPieChartWidget extends ApexChartWidget
{
//    use HasFiltersSchema;
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'exhibitionPieChartWidget';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Exhibition Pie Chart';

    protected int|string|array $columnSpan = [
        'sm' => 4,
        'md' => 1,
    ];

    /**
     * Chart options (series, labels, types, size, animations…)
     *
     * @return array
     */

//    public function filtersSchema(Schema $schema): Schema
//    {
//        return $schema->components([
//            Select::make('chart_type')
//            ->options([
//                'donut' => 'Donut',
//                'pie' => 'Pie',
//            ])
//            ->default('pie'),
//        ]);
//    }

//    public function updatedInteractsWithSchemas(string $statePath): void
//    {
//        $this->updateOptions();
//    }


    protected function getOptions(): array
    {
        $chartType = 'pie';

        $data = Record::query()
            ->select('associations.name as exhibition_name', DB::raw('COUNT(*) as count'))
            ->join('associations', 'records.exhibition_id', '=', 'associations.id')
            ->whereNotNull('records.exhibition_id')
            ->where('associations.type', 'exhibition')
            ->groupBy('associations.name')
            ->pluck('count', 'exhibition_name')
            ->toArray();

        return [
            'chart' => [
                'type' => $chartType,
                'height' => 320,
                'toolbar' => [
                    'show' => true, // Enables download menu
                ],
            ],
            'series' => array_values($data),
            'labels' => array_keys($data),
            'legend' => [
                'labels' => ['fontFamily' => 'inherit'],
                'position' => 'left',
                'floating' => true,
//                'show' => false,
                'offsetX' => -40,
                'offsetY' => -25
            ],
        ];
    }

}
