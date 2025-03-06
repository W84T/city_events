<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssociationResource\Pages;
use App\Filament\Resources\AssociationResource\RelationManagers;
use App\Models\Association;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssociationResource extends Resource
{
    protected static ?string $model = Association::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make()->schema([
                        Select::make('type')
                            ->label(__('form.type'))
                            ->searchable()
                            ->options([
                                'exhibition' => __('form.exhibition'),
                                'resource' => __('form.resource'),
                                'sector' => __('form.sector'),
                            ])
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
                    ])->columns(2)
                ])->columnSpan(2)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('form.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('other_info')
                    ->label(__('form.other_info'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('form.type'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultGroup(
                Tables\Grouping\Group::make('type')
                    ->label(__('form.type'))
                    ->collapsible()
            )
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'exhibition' => __('form.exhibition'),
                        'resource' => __('form.resource'),
                        'sector' => __('form.sector'),
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListAssociations::route('/'),
            'create' => Pages\CreateAssociation::route('/create'),
            'edit' => Pages\EditAssociation::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('panel.association');
    }


    public static function getPluralModelLabel(): string
    {
        return __('panel.associations');
    }
}
