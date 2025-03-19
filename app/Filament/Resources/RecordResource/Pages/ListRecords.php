<?php

namespace App\Filament\Resources\RecordResource\Pages;

use App\Filament\Exports\RecordExporter;
use App\Filament\Imports\RecordImporter;
use App\Filament\Resources\RecordResource;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords as BaseListRecords;


class ListRecords extends BaseListRecords
{
    protected static string $resource = RecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
            ExportAction::make()
                ->exporter(RecordExporter::class)
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary'),
            ImportAction::make()
                ->importer(RecordImporter::class)
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary'),
        ];
    }
}
