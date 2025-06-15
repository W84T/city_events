<?php

namespace App\Filament\Resources\RecordResource\Pages;

use App\Filament\Exports\RecordExporter;
use App\Filament\Imports\RecordImporter;
use App\Filament\Resources\RecordResource;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords as BaseListRecords;
use Filament\Tables\Actions\ImportAction as ImportTableAction;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString; 

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
    ->modalDescription(function (ImportAction | ImportTableAction $action): Htmlable {
        return new HtmlString(
            Blade::render('filament.import-modal-description', [
                'downloadExampleAction' => $action->getModalAction('downloadExample'),
            ])
        );
    })
    ->icon('heroicon-o-arrow-down-tray')
    ->color('primary'),
        ];
    }

    public function setPage($page, $pageName = 'page'): void
    {
        parent::setPage($page, $pageName);

        $this->dispatch('scroll-to-top');
    }

//    protected function paginateTableQuery(Builder $query): Paginator
//    {
//        return $query->simplePaginate(($this->getTableRecordsPerPage() === 'all') ? $query->count() : $this->getTableRecordsPerPage());
//    }
}
