<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Filament\Widgets\ChartWidget;

class ExhibitionPieChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Exhibition Pie Chart';
    protected int | string | array $columnSpan = '4';
    protected function getData(): array
    {
        $count = Record::query()
            ->selectRaw('exhibition, COUNT(*) as count')
            ->whereNotNull('exhibition')
            ->groupBy('exhibition')
            ->pluck('count', 'exhibition')
            ->toArray();

        $backgroundColors = $this->generateConsistentColors(array_keys($count));

        return [
            'datasets' => [
                [
                    'label' => 'Exhibition Count',
                    'data' => array_values($count),
                    'backgroundColor' => $backgroundColors,
                ],
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
        return array_map(fn($category) => $this->stringToColor($category, 30), $categories);
    }

    /**
     * Convert a string (category name) into a unique, consistent hex color.
     */
    private function stringToColor(string $string, int $baseHue, float $percentage = 1): string
    {
        $hash = crc32($string);

        // Dynamic lightness: Small segments get more lightness
        $baseLightness = 50 + ($hash % 25); // 50-65% default
        $adjustedLightness = min(85, $baseLightness + (1 - $percentage) * 30); // Boost small segments

        $saturation = 70 + ($hash % 20); // 70-90% saturation

        return "hsl($baseHue, {$saturation}%, {$adjustedLightness}%)";
    }




}
