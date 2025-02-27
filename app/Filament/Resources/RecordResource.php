<?php

namespace App\Filament\Resources;

use App\Actions\SendBulkEmailAction;
use App\Actions\SendBulkSmsAction;
use App\Actions\SendEmailAction;
use App\Actions\SendSmsAction;
use App\Filament\Exports\RecordExporter;
use App\Filament\Imports\RecordImporter;
use App\Filament\Resources\RecordResource\Pages;
use App\Filament\Resources\RecordResource\RelationManagers;
use App\Models\Association;
use App\Models\Record;
use App\Models\state;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Webbingbrasil\FilamentAdvancedFilter\Filters\TextFilter;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class RecordResource extends Resource
{
    protected static ?string $model = Record::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('classification')
                    ->label(__('form.classification'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('badge')
                    ->label(__('form.badge'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sector')
                    ->label(__('form.sector'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('subsector')
                    ->label(__('form.subSector'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('full_name')
                    ->label(__('form.full_name'))
                    ->sortable(query: fn($query, $direction) => $query->orderByRaw("CONCAT(first_name, ' ', last_name) {$direction}")
                    )
                    ->searchable(query: fn($query, $search) => $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                    ),
                TextColumn::make('gender')
                    ->label(__('form.gender'))
                    ->sortable(),
                TextColumn::make('email')
                    ->copyable()
                    ->copyMessage(__('Email copied'))
                    ->label(__('form.email'))
                    ->searchable()
                    ->sortable(),
                PhoneColumn::make('mobile_number')->displayFormat(PhoneInputNumberType::NATIONAL)
                    ->searchable()
                    ->copyable()
                    ->copyMessage('mobile number copied')
                    ->sortable(),
                TextColumn::make('countryRelation.name')
                    ->label(__('form.country'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stateRelation.name')
                    ->label(__('form.city'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('form.title'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('website')
                    ->label(__('form.website'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('scfhs')
                    ->label(__('form.scfhs'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('job_title')
                    ->label(__('form.job_title'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Define your SelectFilters and TextFilters here
                SelectFilter::make('classification')
                    ->label(__('form.classification'))
                    ->searchable()
                    ->options(Association::where('type', 'classification')->pluck('name', 'name'))
                    ->preload(),
                SelectFilter::make('badge')
                    ->label(__('form.badge'))
                    ->searchable()
                    ->options(Association::where('type', 'badge')->pluck('name', 'name'))
                    ->preload(),
                SelectFilter::make('sector')
                    ->label(__('form.sector'))
                    ->searchable()
                    ->options(Association::where('type', 'sector')->pluck('name', 'name'))
                    ->preload(),
                SelectFilter::make('subSector')
                    ->label(__('form.subSector'))
                    ->searchable()
                    ->options(Association::where('type', 'sub_sector')->pluck('name', 'name'))
                    ->preload(),
                TextFilter::make('first_name')
                    ->default(TextFilter::CLAUSE_CONTAIN)
                    ->wrapperUsing(fn() => Group::make())
                    ->enableClauseLabel(),
                TextFilter::make('last_name')
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
//                    ->searchable()
                    ->preload(),
                SelectFilter::make('gender')
                    ->label(__('form.gender'))
                    ->options([
                        'male' => __('form.male'),
                        'female' => __('form.female'),
                    ])
            ], layout: FiltersLayout::Modal)
            ->filtersFormColumns(3) // Ensure the overall form is in 3 columns
            ->filtersFormSchema(fn(array $filters): array => [
                // Organize filters into sections
                Section::make()
                    ->description()
                    ->schema([
                        Group::make([
                            $filters['first_name'],
                            $filters['last_name'],
                            $filters['mobile_number'],
                        ])->columns(3), // Arrange these 3 filters in 3 columns
                    ])
                    ->columns(1),

                Section::make()
                    ->description()
                    ->schema([
                        Group::make([
                            $filters['classification'],
                            $filters['badge'],
                            $filters['sector'],
                            $filters['subSector'],
                        ])->columns(4), // Arrange these 4 filters in 4 columns
                    ])
                    ->columns(1),

                Section::make()
                    ->description()
                    ->schema([
                        Group::make([
                            $filters['country'],
                            $filters['gender'],
                        ])->columns(2), // Arrange these 2 filters in 2 columns
                    ])
                    ->columns(1),

            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    SendEmailAction::make(),
                    SendSmsAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(RecordExporter::class)
                    ->icon('heroicon-o-arrow-down-tray') // Add an icon for export
                    ->color('primary'), // Set the color to primary
                ImportAction::make()
                    ->importer(RecordImporter::class)
                    ->icon('heroicon-o-arrow-up-tray') // Add an icon for import
                    ->color('primary'), // Set the color to primary
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    SendBulkEmailAction::make(),
                    SendBulkSmsAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make(__('form.association'))->schema([

                        Select::make('classification')
                            ->searchable()
                            ->preload()
                            ->label(__('form.classification'))
                            ->options(Association::where('type', 'classification')->pluck('name', 'name')) // Fetch options from the associations table
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('form.name'))
                                    ->maxLength(255),
                                TextInput::make('other_info')
                                    ->label(__('form.other_info'))
                                    ->maxLength(255),
                                TextInput::make('type')
                                    ->hidden() // Hide this field as it will be set programmatically
                                    ->default('classification') // Set the type to 'classification'
                            ]),

                        Select::make('badge')
                            ->searchable()
                            ->preload()
                            ->label(__('form.badge'))
                            ->options(Association::where('type', 'badge')->pluck('name', 'name')) // Fetch options from the associations table
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('form.name'))
                                    ->maxLength(255),
                                TextInput::make('other_info')
                                    ->label(__('form.other_info'))
                                    ->maxLength(255),
                                TextInput::make('type')
                                    ->hidden() // Hide this field as it will be set programmatically
                                    ->default('badge') // Set the type to 'badge'
                            ]),

                        Select::make('sector')
                            ->searchable()
                            ->preload()
                            ->label(__('form.sector'))
                            ->options(Association::where('type', 'sector')->pluck('name', 'name')) // Fetch options from the associations table
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('form.name'))
                                    ->maxLength(255),
                                TextInput::make('other_info')
                                    ->label(__('form.other_info'))
                                    ->maxLength(255),
                                TextInput::make('type')
                                    ->hidden() // Hide this field as it will be set programmatically
                                    ->default('sector') // Set the type to 'sector'
                            ]),

                        Select::make('subsector')
                            ->searchable()
                            ->preload()
                            ->label(__('form.subSector'))
                            ->options(Association::where('type', 'sub_sector')->pluck('name', 'name')) // Fetch options from the associations table
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('form.name'))
                                    ->maxLength(255),
                                TextInput::make('other_info')
                                    ->label(__('form.other_info'))
                                    ->maxLength(255),
                                TextInput::make('type')
                                    ->hidden() // Hide this field as it will be set programmatically
                                    ->default('sub_sector') // Set the type to 'sub_sector'
                            ]),
                    ])
                ])->columnSpan(1),
                Group::make([
                    Section::make(__('form.information'))->schema([
                        TextInput::make('first_name')
                            ->label(__('form.first_name'))
                            ->maxLength(255),

                        TextInput::make('last_name')
                            ->label(__('form.last_name'))
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('form.email'))
                            ->maxLength(255)
                            ->unique(Record::class, 'email', ignoreRecord: true)
                            ->dehydrated()
                            ->regex('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'),


                        PhoneInput::make('mobile_number')
                            ->label(__('form.mobile_number'))
                            ->excludeCountries(['IL'])
                            ->validateFor($country = 'AUTO', $type = null, false),

                        Radio::make('gender')
                            ->options([
                                'male' => __('form.male'),
                                'female' => __('form.female'),
                            ])
                            ->inline()
                            ->inlineLabel(false),

                        Select::make('title')
                            ->label(__('form.title'))
                            ->options([
                                'Mr.' => 'Mr',
                                'Mrs' => 'Ms',
                                'other' => 'Other',
                            ]),

                        Select::make('country')
                            ->live()
                            ->label(__('form.country'))
                            ->relationship('countryRelation', 'name')
                            ->searchable()
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
                            ->preload(),
                    ])->columns(2),
                ])->columnSpan(2)->columns(2),

                Section::make(__('form.other_information'))->schema([
                    Group::make()->schema([
                        TextInput::make('company')
                            ->label(__('form.company'))
                            ->maxLength(255),

                        TextInput::make('job_title')
                            ->label(__('form.job_title'))
                            ->maxLength(255),

                        TextInput::make('website')
                            ->label(__('form.website'))
                            ->maxLength(255),

                        TextInput::make('scfhs')
                            ->label(__('form.scfhs'))
                            ->maxLength(255),
                    ])->columns(2),
                    MarkdownEditor::make('other_information')
                        ->label(__('form.other_information')),
                ])->columnSpan(2),

                // New section for phone numbers on the right side
                Section::make(__('form.phone_numbers'))->schema([
                    Repeater::make('phone')
                        ->label(__('form.phone_numbers'))
                        ->schema([
                            TextInput::make('number')
                                ->label(__('form.phone_number'))
                                ->maxLength(255),
                        ])
                        ->orderable('number')
                        ->defaultItems(1)
                        ->columnSpanFull(),
                ])->columnSpan(1),  // New section for phone numbers (on the right)
            ])->columns(3);  // Ensuring the entire form is 3 columns wide
    }

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
}
