<?php

namespace App\Filament\Exports;

use App\Models\Record;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;

class RecordExporter extends Exporter
{
    protected static ?string $model = Record::class;

    public static function getColumns(): array
    {
        return [
//            ExportColumn::make('id')
//                ->label('ID'),
            ExportColumn::make('exhibition'),
            ExportColumn::make('resource'),
            ExportColumn::make('sector'),
//            ExportColumn::make('title'),
//            ExportColumn::make('first_name'),
//            ExportColumn::make('last_name'),
            ExportColumn::make('full_name'),
            ExportColumn::make('email'),
            ExportColumn::make('mobile_number'),
            ExportColumn::make('gender'),
            ExportColumn::make('countryRelation.name'),
            ExportColumn::make('stateRelation.name'),
            ExportColumn::make('phone')
                ->formatStateUsing(function ($state) {
                    // Convert JSON array to comma-separated string
                    return is_array($state) ? implode(', ', $state) : '';
                }),
            ExportColumn::make('company'),
            ExportColumn::make('job_title'),
            ExportColumn::make('website'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your record export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

}
