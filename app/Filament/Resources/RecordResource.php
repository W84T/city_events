<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecordResource\Pages;
use App\Models\Association;
use App\Models\Record;
use App\Models\State;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Webbingbrasil\FilamentAdvancedFilter\Filters\TextFilter;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;


class RecordResource extends Resource

{

    protected static ?string $model = Record::class;
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $activeNavigationIcon = 'heroicon-s-table-cells';
    protected static ?int $navigationSort = 0;

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecords::route('/'),
            'create' => Pages\CreateRecord::route('/create'),
            'edit' => Pages\EditRecord::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('panel.record');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.records');
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

    public static function table(Table $table): Table
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
                    ->toggleable()
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
                PhoneColumn::make('mobile_number')->displayFormat(PhoneInputNumberType::INTERNATIONAL)
                    ->copyable()
                    ->toggleable()
                    ->copyMessage('mobile number copied')
                    ->searchable(isIndividual: true, isGlobal: false)
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
                    ->default(TextFilter::CLAUSE_CONTAIN)
                    ->wrapperUsing(fn() => Group::make())
                    ->enableClauseLabel(),

                TextFilter::make('last_name')
                    ->default(TextFilter::CLAUSE_CONTAIN)
                    ->wrapperUsing(fn() => Group::make())
                    ->enableClauseLabel(),

                TextFilter::make('email')
                    ->default(TextFilter::CLAUSE_CONTAIN)
                    ->wrapperUsing(fn() => Group::make())
                    ->enableClauseLabel(),

                TextFilter::make('mobile_number')
                    ->default(TextFilter::CLAUSE_CONTAIN)
                    ->wrapperUsing(fn() => Group::make())
                    ->enableClauseLabel(),

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
            ->filtersFormColumns(4)
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
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
//                    SendEmailAction::make(),
//                    SendSmsAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->selectCurrentPageOnly()
            ->paginated([5, 10, 25, 50, 100])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    SendBulkEmailAction::make(),
//                    SendBulkSmsAction::make(),
                    DeleteBulkAction::make(),
                ]),
                BulkAction::make('deleteAll')
                    ->label(fn($livewire) => __('Delete All ' . $livewire->getFilteredTableQuery()->count() . ' Records'))
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->action(function ($livewire) {
                        if (method_exists($livewire, 'getFilteredTableQuery')) {
                            $filteredQuery = $livewire->getFilteredTableQuery();
                            $filteredQuery->delete();
                        } else {
                            Record::query()->delete();
                        }
                    })
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make(__('form.association'))
                        ->columns(3)
                        ->schema([
                            Select::make('exhibition_id')
                                ->relationship(
                                    'exhibition',
                                    'name',
                                    modifyQueryUsing: fn($query) => $query->orderByRaw("CASE WHEN name = 'other' THEN 2 WHEN name = 'SFDA' THEN 1 ELSE 0 END")->orderBy('name'))
                                ->searchable()
                                ->preload()
                                ->label(__('form.exhibition'))
                                ->createOptionForm([
                                    TextInput::make('name')->label(__('form.name'))->maxLength(255),
                                    TextInput::make('other_info')->label(__('form.other_info'))->maxLength(255),
                                    TextInput::make('type')->hidden()->default('exhibition'),
                                ])
                                ->createOptionUsing(fn($data) => Association::create([
                                    'name' => $data['name'],
                                    'other_info' => $data['other_info'] ?? null,
                                    'type' => 'exhibition',
                                ])->id),

                            Select::make('resource_id')
                                ->relationship('resource', 'name')
                                ->searchable()
                                ->preload()
                                ->label(__('form.resource'))
                                ->createOptionForm([
                                    TextInput::make('name')->label(__('form.name'))->maxLength(255),
                                    TextInput::make('other_info')->label(__('form.other_info'))->maxLength(255),
                                    TextInput::make('type')->hidden()->default('resource'),
                                ])
                                ->createOptionUsing(fn($data) => Association::create([
                                    'name' => $data['name'],
                                    'other_info' => $data['other_info'] ?? null,
                                    'type' => 'resource',
                                ])->id),

                            Select::make('sector_id')
                                ->relationship('sector', 'name')
                                ->searchable()
                                ->preload()
                                ->label(__('form.sector'))
                                ->createOptionForm([
                                    TextInput::make('name')->label(__('form.name'))->maxLength(255),
                                    TextInput::make('other_info')->label(__('form.other_info'))->maxLength(255),
                                    TextInput::make('type')->hidden()->default('sector'),
                                ])
                                ->createOptionUsing(fn($data) => Association::create([
                                    'name' => $data['name'],
                                    'other_info' => $data['other_info'] ?? null,
                                    'type' => 'sector',
                                ])->id),
                        ]),
                    Section::make(__('form.information'))
                        ->columns(4) // Ensures proper layout per row
                        ->schema([
                            // Row 1: Title | First Name | Last Name | Gender
                            TextInput::make('title')
                                ->label(__('form.title'))
                                ->autocomplete(false)
                                ->datalist([
                                    'Mr.',
                                    'Mrs.',
                                    'Ms.',
                                    'Miss',
                                    'Mx.',
                                    'Dr.',
                                    'Prof.',
                                    'Eng.',
                                    'Arch.',
                                ]),


                            TextInput::make('first_name')
                                ->label(__('form.first_name'))
                                ->maxLength(255),

                            TextInput::make('last_name')
                                ->label(__('form.last_name'))
                                ->maxLength(255),

                            Radio::make('gender')
                                ->options([
                                    'male' => __('form.male'),
                                    'female' => __('form.female'),
                                ])
                                ->inline()
                                ->inlineLabel(false),

                            // Row 2: Email | Mobile Number
                            TextInput::make('email')
                                ->label(__('form.email'))
                                ->maxLength(255)
                                ->unique(Record::class, 'email', ignoreRecord: true)
                                ->dehydrated()
                                ->regex('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')
                                ->columnSpan(2),

                            PhoneInput::make('mobile_number')
                                ->label(__('form.mobile_number'))
                                ->unique(Record::class, 'mobile_number', ignoreRecord: true)
                                ->excludeCountries(['IL'])
                                ->validateFor($country = 'AUTO', $type = null, false)
                                ->columnSpan(2),

                            // Row 3: Country | City
                            Select::make('country')
                                ->live()
                                ->label(__('form.country'))
                                ->relationship('countryRelation', 'name')
                                ->searchable()
                                ->columnSpan(2)
                                ->preload(),

                            Select::make('city')
                                ->label(__('form.city'))
                                ->options(function (Get $get): Collection {
                                    $countryId = $get('country');

                                    if (!$countryId) {
                                        return collect();
                                    }

                                    return State::query()
                                        ->where('country_id', $countryId)
                                        ->pluck('name', 'id');
                                })
                                ->searchable()
                                ->columnSpan(2)
                                ->preload(),

                            // Row 4: Job Title | Company | Website
                            TextInput::make('company')
                                ->label(__('form.company'))
                                ->columnSpan(2)
                                ->maxLength(255),

                            TextInput::make('job_title')
                                ->label(__('form.job_title'))
                                ->maxLength(255),

                            TextInput::make('website')
                                ->label(__('form.website'))
                                ->maxLength(255),

                            // Row 5: Phone Numbers (Repeater)
                            Repeater::make('phone')
                                ->label(__('form.phone_numbers'))
                                ->schema([
                                    TextInput::make('number')
                                        ->label(__('form.phone_number'))
                                        ->tel()
                                        ->maxLength(255),
                                ])
                                ->orderable('number')
                                ->defaultItems(1)
                                ->columnSpanFull(),
                        ]),
                ])
            ])->columns(1);
    }


}
