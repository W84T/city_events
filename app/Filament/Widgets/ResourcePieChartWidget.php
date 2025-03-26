<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Filament\Widgets\ChartWidget;

class ResourcePieChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Resource Pie Chart';
    protected int | string | array $columnSpan = '4';

    protected function getData(): array
    {
        $countResource = Record::query()
            ->selectRaw('resource, COUNT(*) as count')
            ->whereNotNull('resource')
            ->groupBy('resource')
            ->pluck('count', 'resource')
            ->toArray();

        $backgroundColors = $this->generateConsistentColors(array_keys($countResource));

        return [
            'datasets' => [
                [
                    'label' => 'Resource Count',
                    'data' => array_values($countResource),
                    'backgroundColor' => $backgroundColors, // Assign consistent colors
                ]
            ],
            'labels' => array_keys($countResource)
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
        return array_map(fn($category) => $this->stringToColor($category, 330), $categories);
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
