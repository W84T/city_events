<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Filament\Widgets\ChartWidget;

class RecordBarChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Record Distribution';
    protected int | string | array $columnSpan = 'full';
    public ?string $filter = 'all';

    protected function getFilters(): ?array
    {
        return [
            'all' => 'All',
            'exhibition' => 'exhibition',
            'resource' => 'Resource',
            'sector' => 'Sector',

        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter ?? 'all';

        $categories = [];
        $counts = [];

        if ($filter === 'all' || $filter === 'exhibition') {
            $classificationCounts = Record::query()
                ->selectRaw('exhibition, COUNT(*) as count')
                ->whereNotNull('exhibition')
                ->groupBy('exhibition')
                ->pluck('count', 'exhibition')
                ->toArray();

            $categories = array_merge($categories, array_keys($classificationCounts));
            $counts = array_merge($counts, array_values($classificationCounts));
        }

        if ($filter === 'all' || $filter === 'resource') {
            $badgeCounts = Record::query()
                ->selectRaw('resource, COUNT(*) as count')
                ->whereNotNull('resource')
                ->groupBy('resource')
                ->pluck('count', 'resource')
                ->toArray();

            $categories = array_merge($categories, array_keys($badgeCounts));
            $counts = array_merge($counts, array_values($badgeCounts));
        }

        if ($filter === 'all' || $filter === 'sector') {
            $sectorCounts = Record::query()
                ->selectRaw('sector, COUNT(*) as count')
                ->whereNotNull('sector')
                ->groupBy('sector')
                ->pluck('count', 'sector')
                ->toArray();

            $categories = array_merge($categories, array_keys($sectorCounts));
            $counts = array_merge($counts, array_values($sectorCounts));
        }


        return [
            'datasets' => [
                [
                    'label' => ucfirst($filter) . ' Count',
                    'data' => $counts,
                ],
            ],
            'labels' => $categories,
        ];
    }

    // Add the getOptions() method to enable hover over labels
    protected function getOptions(): array
    {
        return [
            'responsive' => true,

            'plugins' => [
                'tooltip' => [
                    'enabled' => true,
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

    protected function getType(): string
    {
        return 'bar';
    }
}
