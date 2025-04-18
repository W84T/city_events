<?php

namespace App\Filament\Resources\AssociationResource\Pages;

use App\Filament\Resources\AssociationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAssociations extends ListRecords
{
    protected static string $resource = AssociationResource::class;


    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'Exhibition' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', 'exhibition')),
            'Sector' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', 'sector')),
            'Resource' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', 'resource')),
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
