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
            'classification' => 'Classification',
            'badge' => 'Badge',
            'sector' => 'Sector',
            'subsector' => 'Sub-sector',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter ?? 'all';

        $categories = [];
        $counts = [];

        if ($filter === 'all' || $filter === 'classification') {
            $classificationCounts = Record::query()
                ->selectRaw('classification, COUNT(*) as count')
                ->whereNotNull('classification')
                ->groupBy('classification')
                ->pluck('count', 'classification')
                ->toArray();

            $categories = array_merge($categories, array_keys($classificationCounts));
            $counts = array_merge($counts, array_values($classificationCounts));
        }

        if ($filter === 'all' || $filter === 'badge') {
            $badgeCounts = Record::query()
                ->selectRaw('badge, COUNT(*) as count')
                ->whereNotNull('badge')
                ->groupBy('badge')
                ->pluck('count', 'badge')
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

        if ($filter === 'all' || $filter === 'subsector') {
            $subsectorCounts = Record::query()
                ->selectRaw('subsector, COUNT(*) as count')
                ->whereNotNull('subsector')
                ->groupBy('subsector')
                ->pluck('count', 'subsector')
                ->toArray();

            $categories = array_merge($categories, array_keys($subsectorCounts));
            $counts = array_merge($counts, array_values($subsectorCounts));
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

    protected function getType(): string
    {
        return 'bar';
    }
}
