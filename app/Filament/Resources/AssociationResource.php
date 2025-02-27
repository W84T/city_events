<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssociationResource\Pages;
use App\Filament\Resources\AssociationResource\RelationManagers;
use App\Models\Association;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                                'classification' => __('form.classification'),
                                'badge' => __('form.badge'),
                                'sector' => __('form.sector'),
                                'sub_sector' => __('form.subSector'),
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
                        ->searchable(),
                    Tables\Columns\TextColumn::make('other_info')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('type')->sortable()->searchable(),
                    Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('updated_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])->defaultGroup(Tables\Grouping\Group::make('type')->collapsible())
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'classification' => __('form.classification'),
                        'badge' => __('form.badge'),
                        'sector' => __('form.sector'),
                        'sub_sector' => __('form.subSector'),
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
}
