<?php

namespace App\Filament\Resources\Associations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssociationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make()->schema([
                        Select::make('type')
                            ->label(__('form.type'))
                            ->searchable()
                            ->options([
                                'exhibition' => __('form.exhibition'),
                                'resource' => __('form.resource'),
                                'sector' => __('form.sector'),
                            ]),
                    ]),
                ])->columnSpan(1),
                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')
                            ->label(__('form.name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('other_info')
                            ->label(__('form.other_info'))
                            ->maxLength(255),
                    ])->columns(2),
                ])->columnSpan(2),
            ])->columns(3);
    }
}
