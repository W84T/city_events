<?php

namespace App\Filament\Resources\Records\Tables;

use App\Filament\CustomFilters\TextFilter;
use App\Models\Association;
use App\Models\Record;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class RecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchOnBlur()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->columns([

                TextColumn::make('exhibition.name')
                    ->label(__('form.exhibition'))
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: false,
                        query: function ($query, $search) {

                            $hiddenSpace = "\u{00A0}";

                            $search = str_replace($hiddenSpace, ' ', $search);

                            $query->whereHas('exhibition', function ($q) use ($search) {
                                $q->where('name', '=', $search);
                            });
                        }
                    )
                    ->toggleable(),


                TextColumn::make('sector.name')
                    ->label(__('form.sector'))
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: false,
                        query: function ($query, $search) {
                            $hiddenSpace = "\u{00A0}";

                            $search = str_replace($hiddenSpace, ' ', $search);
                            $query->whereHas('sector', function ($q) use ($search) {
                                $q->where('name', '=', $search);
                            });
                        }
                    )
                    ->toggleable(),

                TextColumn::make('resource.name')
                    ->label(__('form.resource'))
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        isGlobal: false,
                        query: function ($query, $search) {
                            $hiddenSpace = "\u{00A0}";

                            $search = str_replace($hiddenSpace, ' ', $search);
                            $query->whereHas('resource', function ($q) use ($search) {
                                $q->where('name', '=', $search);
                            });
                        }
                    )
                    ->toggleable(),

                TextColumn::make('full_name')
                    ->label(__('form.full_name'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(isIndividual: true, isGlobal: false, query: fn($query, $search) => $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]))
                    ->sortable(query: fn($query, $direction) => $query->orderByRaw("CONCAT(first_name, ' ', last_name) {$direction}")),
                TextColumn::make('gender')
                    ->label(__('form.gender'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                TextColumn::make('email')
                    ->copyable()
                    ->toggleable()
                    ->copyMessage(__('Email copied'))
                    ->label(__('form.email'))
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                PhoneColumn::make('mobile_number')
                    ->displayFormat(PhoneInputNumberType::INTERNATIONAL)
                    ->copyable()
                    ->toggleable()
                    ->copyMessage('mobile number copied')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                TextColumn::make('phone')
                    ->getStateUsing(fn ($record) => collect(
                        is_string($record->phone) ? json_decode($record->phone, true) : $record->phone
                    )
                        ->pluck('number')
                        ->all())
                    ->listWithLineBreaks()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->sortable(),

        TextColumn::make('countryRelation.name')
                    ->label(__('form.country'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                TextColumn::make('stateRelation.name')
                    ->label(__('form.city'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('company')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('form.title'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('website')
                    ->label(__('form.website'))
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('job_title')
                    ->label(__('form.job_title'))
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->reorderableColumns()
            ->striped()
            ->deferColumnManager(false)
            ->toggleColumnsTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Toggle columns'),
            )
            ->filters([
                Filter::make('exhibition_filter')
                    ->form([
                        Group::make([
                            Select::make('exhibition_id')
                                ->label(__('form.exhibition'))
                                ->live()
                                ->options(
                                    Association::query()
                                        ->where('type', 'exhibition')
                                        ->orderByRaw("CASE WHEN name = 'other' THEN 2 WHEN name = 'SFDA' THEN 1 ELSE 0 END")
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray()
                                ),

                            Select::make('sector_id')
                                ->label(__('form.sector'))
                                ->options(function ($get) {
                                    $exhibitionId = $get('exhibition_id');

                                    if (!$exhibitionId) return [];

                                    return Record::query()
                                        ->where('exhibition_id', $exhibitionId)
                                        ->select('sector_id')
                                        ->distinct()
                                        ->with('sector')
                                        ->get()
                                        ->mapWithKeys(function ($record) {
                                            return [$record->sector_id => optional($record->sector)->name];
                                        })
                                        ->filter()
                                        ->toArray();
                                }),

                            Select::make('resource_id')
                                ->label(__('form.resource'))
                                ->options(function ($get) {
                                    $exhibitionId = $get('exhibition_id');

                                    if (!$exhibitionId) return [];

                                    return Record::query()
                                        ->where('exhibition_id', $exhibitionId)
                                        ->select('resource_id')
                                        ->distinct()
                                        ->with('resource')
                                        ->get()
                                        ->mapWithKeys(function ($record) {
                                            return [$record->resource_id => optional($record->resource)->name];
                                        })
                                        ->filter()
                                        ->toArray();
                                }),
                        ])->columns(3)
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['exhibition_id'] ?? null) {
                            $query->where('exhibition_id', $data['exhibition_id']);
                        }

                        if ($data['sector_id'] ?? null) {
                            $query->where('sector_id', $data['sector_id']);
                        }

                        if ($data['resource_id'] ?? null) {
                            $query->where('resource_id', $data['resource_id']);
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (!empty($data['exhibition_id'])) {
                            $exhibition = Association::find($data['exhibition_id']);
                            if ($exhibition) {
                                $indicators[] = __('form.exhibition') . ': ' . $exhibition->name;
                            }
                        }

                        if (!empty($data['sector_id'])) {
                            $sector = Association::find($data['sector_id']);
                            if ($sector) {
                                $indicators[] = __('form.sector') . ': ' . $sector->name;
                            }
                        }

                        if (!empty($data['resource_id'])) {
                            $resource = Association::find($data['resource_id']);
                            if ($resource) {
                                $indicators[] = __('form.resource') . ': ' . $resource->name;
                            }
                        }
                        return $indicators;
                    }),


                TextFilter::make('first_name')
                    ->default(TextFilter::CLAUSE_CONTAIN),

                TextFilter::make('last_name')
                    ->default(TextFilter::CLAUSE_CONTAIN),

                TextFilter::make('email')
                    ->default(TextFilter::CLAUSE_CONTAIN),

                TextFilter::make('mobile_number')
                    ->default(TextFilter::CLAUSE_CONTAIN),

                SelectFilter::make('country')
                    ->label(__('form.country'))
                    ->relationship('countryRelation', 'name')
                    ->preload(),

                SelectFilter::make('gender')
                    ->label(__('form.gender'))
                    ->options([
                        'male' => __('form.male'),
                        'female' => __('form.female'),
                    ]),
            ], layout: FiltersLayout::Modal)
            ->deferFilters()
            ->filtersFormWidth(Width::SixExtraLarge)
            ->filtersFormSchema(fn(array $filters): array => [
                Section::make()
                    ->description()
                    ->schema([
                        Group::make([
                            $filters['first_name'],
                            $filters['last_name'],
                            $filters['email'],
                            $filters['mobile_number'],
                        ])->columns(4),
                    ])
                    ->columns(1),

                Section::make()
                    ->description()
                    ->schema([
                        Group::make([
                            $filters['exhibition_filter'],
                        ])->columns(1),
                    ])
                    ->columns(1),

                Section::make()
                    ->description()
                    ->schema([
                        Group::make([
                            $filters['country'],
                            $filters['gender'],
                        ])->columns(2),
                    ])
                    ->columns(1),
            ])
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->deferFilters()
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                BulkAction::make('deleteAll')
                    ->label(fn($livewire) => __('Delete All ' . $livewire->getFilteredTableQuery()
                            ->count() . ' Records'))
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->action(function ($livewire) {
                        if (method_exists($livewire, 'getFilteredTableQuery')) {
                            $filteredQuery = $livewire->getFilteredTableQuery();
                            $filteredQuery->delete();
                        } else {
                            Record::query()
                                ->delete();
                        }
                    })

            ]);
    }
}
