<?php

namespace App\Filament\Resources\Records;

use App\Filament\Resources\Records\Pages\CreateRecord;
use App\Filament\Resources\Records\Pages\EditRecord;
use App\Filament\Resources\Records\Pages\ListRecords;
use App\Filament\Resources\Records\Schemas\RecordForm;
use App\Filament\Resources\Records\Tables\RecordsTable;
use App\Models\Record;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class RecordResource extends Resource
{
    protected static ?string $model = Record::class;


    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Phosphor::Table->regular();
    }

    public static function getActiveNavigationIcon(): string|BackedEnum|null
    {
        return Phosphor::Table->duotone();
    }

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return RecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make()
                ->label(__('Records'))
                ->icon(static::getNavigationIcon())
                ->url(static::getUrl('index'))
                ->isActiveWhen(fn() => request()->routeIs([
                    'filament.admin.resources.records.index',
                    'filament.admin.resources.records.edit',
                ])),
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => ListRecords::route('/'),
            'create' => CreateRecord::route('/create'),
            'edit' => EditRecord::route('/{record}/edit'),
        ];
    }
}
