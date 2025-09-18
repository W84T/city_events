<?php

namespace App\Filament\Resources\Associations\Tables;

use App\Filament\Actions\MergeAction;
use App\Models\Association;
use App\Models\Record;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class AssociationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('form.name'))
                    ->searchable(),
                TextColumn::make('other_info')
                    ->label(__('form.other_info'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('form.type'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'exhibition' => __('form.exhibition'),
                        'resource' => __('form.resource'),
                        'sector' => __('form.sector'),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    MergeAction::make(),
                ]),
            ]);
    }
}
