<?php

namespace App\Filament\Resources\Records\Pages;

use App\Filament\Exports\RecordExporter;
use App\Filament\Imports\RecordDeleterImporter;
use App\Filament\Imports\RecordImporter;
use App\Filament\Resources\Records\RecordResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords as BaseListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ListRecords extends BaseListRecords
{
    protected static string $resource = RecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(RecordExporter::class)
                ->icon(Phosphor::ExportDuotone)
                ->color('primary'),

            ImportAction::make()
                ->importer(RecordImporter::class)
                ->modalDescription(function (ImportAction $action): Htmlable {
                    return new HtmlString(
                        Blade::render('filament.import-modal-description', [
                            'downloadExampleAction' => $action->getModalAction('downloadExample'),
                        ])
                    );
                })
                ->icon(Phosphor::DownloadSimpleDuotone)
                ->color('primary'),

//            ImportAction::make('deleteFromCsv')
//                ->label('Delete by Email (CSV)')
//                ->importer(RecordDeleterImporter::class)
//                ->icon('heroicon-o-trash')
//                ->color('danger')
//                ->modalHeading('Delete Records by Email')
//                ->modalDescription('Upload a CSV file with an "email" column. Matching records will be deleted.'),

        ];
    }

    public function setPage($page, $pageName = 'page'): void
    {
        parent::setPage($page, $pageName);

        $this->dispatch('scroll-to-top');
    }


}
