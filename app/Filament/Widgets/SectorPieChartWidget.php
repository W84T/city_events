<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Filament\Widgets\ChartWidget;

class SectorPieChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Sector Pie Chart';
    protected int | string | array $columnSpan = '4';
    protected function getData(): array
    {
        $count = Record::query()
            ->selectRaw('sector, COUNT(*) as count')
            ->wherenotNull('sector')
            ->groupBy('sector')
            ->pluck('count', 'sector')
            ->toArray();

        $backgroundColors = $this->generateConsistentColors(array_keys($count));
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

    protected function getType(): string
    {
        return 'pie';
    }

    /**
     * Generate a consistent color for each category based on its name.
     */
    private function generateConsistentColors(array $categories): array
    {
        return array_map(fn($category) => $this->stringToColor($category, 220), $categories);
    }

    /**
     * Convert a string (category name) into a unique, consistent hex color.
     */
    private function stringToColor(string $string, int $baseHue): string
    {
        $hash = crc32($string);

        // Generate different saturation and lightness for variety
        $saturation = 60 + ($hash % 20); // 60-80% saturation
        $lightness = 50 + ($hash % 20); // 50-70% lightness

        return "hsl($baseHue, {$saturation}%, {$lightness}%)";
    }
}
