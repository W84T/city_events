<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssociationResource\Pages;
use App\Models\Association;
use App\Models\Record;
use Filament\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AssociationResource extends Resource
{
    protected static ?string $model = Association::class;
    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?int $navigationSort = 2;

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
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'exhibition' => __('form.exhibition'),
                        'resource' => __('form.resource'),
                        'sector' => __('form.sector'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('merge')
                        ->label(__('Merge'))
                        ->requiresConfirmation()
                        ->modalHeading('Merge Associations')
                        ->icon('heroicon-o-sparkles')
                        ->modalDescription('Do you want to merge the selected associations into one? This will delete all others and keep the data linked to the selected target.')
                        ->modalSubmitActionLabel('Yes, Merge')
                        ->form(fn (Collection $records) => [
                            Select::make('target_id')
                                ->label(false)
                                ->options($records->pluck('name', 'id'))
                                ->required()
                                ->hint('All selected associations will be merged into this one'),
                        ])
                        ->mountUsing(function (Collection $records, BulkAction $action) {
                            $types = $records->pluck('type')->unique();

                            if ($types->count() > 1) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Merge Failed')
                                    ->danger()
                                    ->body('All selected associations must be of the same type.')
                                    ->send();

                                // Cancel modal opening
                                $action->cancel();
                            }
                        })
                        ->action(function (Collection $records, array $data) {
                            $targetId = $data['target_id'];

                            if (! $records->pluck('id')->contains($targetId)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Invalid Target')
                                    ->danger()
                                    ->body('Selected target is not among the selected associations.')
                                    ->send();

                                return;
                            }

                            $type = $records->first()->type;

                            match ($type) {
                                'resource' => Record::whereIn('resource_id', $records->pluck('id'))
                                    ->update(['resource_id' => $targetId]),
                                'sector' => Record::whereIn('sector_id', $records->pluck('id'))
                                    ->update(['sector_id' => $targetId]),
                                'exhibition' => Record::whereIn('exhibition_id', $records->pluck('id'))
                                    ->update(['exhibition_id' => $targetId]),
                            };

                            $recordsToDelete = $records->where('id', '!=', $targetId);
                            Association::destroy($recordsToDelete->pluck('id'));

                            \Filament\Notifications\Notification::make()
                                ->title('Associations Merged')
                                ->success()
                                ->body("Merged " . $recordsToDelete->count() . " associations into '{$records->find($targetId)?->name}'")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()


        ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
