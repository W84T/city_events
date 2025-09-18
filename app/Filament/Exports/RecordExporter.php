<?php

namespace App\Filament\Exports;

use App\Models\Record;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class RecordExporter extends Exporter
{
    protected static ?string $model = Record::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('exhibition.name')->label('Exhibition name'),
            ExportColumn::make('resource.name')->label('Resource'),
            ExportColumn::make('sector.name')->label('Sector'),
            ExportColumn::make('full_name'),
            ExportColumn::make('email'),
            ExportColumn::make('mobile_number'),
            ExportColumn::make('gender'),
            ExportColumn::make('countryRelation.name')->label('country'),
            ExportColumn::make('stateRelation.name')->label('city'),
            ExportColumn::make('phone')
                ->formatStateUsing(function ($state) {
                    // Convert JSON array to comma-separated string
                    return is_array($state) ? implode(',', $state) : '';
                }),
            ExportColumn::make('company'),
            ExportColumn::make('job_title'),
            ExportColumn::make('website'),
            ExportColumn::make('title')->enabledByDefault(false),
            ExportColumn::make('first_name')->enabledByDefault(false),
            ExportColumn::make('last_name')->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your record export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
