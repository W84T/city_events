<?php

namespace App\Filament\Resources\Records\Schemas;

use App\Models\Association;
use App\Models\Record;
use App\Models\State;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Filament\Support\Enums\Width;

class RecordForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    $set('sector_id', null);
                                    $set('resource_id', null);
                                })
                                ->label(__('form.exhibition'))
                                ->createOptionForm([
                                    TextInput::make('name')->label(__('form.name'))->maxLength(255),
                                    TextInput::make('other_info')->label(__('form.other_info'))->maxLength(255),
                                    TextInput::make('type')->hidden()->default('exhibition'),
                                ])
                                ->required()
                                ->createOptionUsing(fn($data) => Association::create([
                                    'name' => $data['name'],
                                    'other_info' => $data['other_info'] ?? null,
                                    'type' => 'exhibition',
                                ])->id),

                            Select::make('resource_id')
                                ->relationship('resource', 'name')
                                ->searchable()
                                ->preload()
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
                                })
                                ->label(__('form.resource'))
                                ->createOptionForm([
                                    TextInput::make('name')->label(__('form.name'))->maxLength(255),
                                    TextInput::make('other_info')->label(__('form.other_info'))->maxLength(255),
                                    TextInput::make('type')->hidden()->default('resource'),
                                ])
                                ->required()
                                ->createOptionUsing(fn($data) => Association::create([
                                    'name' => $data['name'],
                                    'other_info' => $data['other_info'] ?? null,
                                    'type' => 'resource',
                                ])->id),

                            Select::make('sector_id')
                                ->relationship('sector', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
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
                                })
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
