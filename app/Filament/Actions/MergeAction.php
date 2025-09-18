<?php

namespace App\Filament\Actions;

use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use App\Models\Association;
use App\Models\Record;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class MergeAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('merge')
            ->label('Merge')
            ->icon(Phosphor::IntersectDuotone)
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Merge Associations')
            ->modalDescription(
                'Do you want to merge the selected associations into one? ' .
                'This will delete all others and keep the data linked to the selected target.'
            )
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
                    Notification::make()
                        ->title('Merge Failed')
                        ->body('All selected associations must be of the same type.')
                        ->danger()
                        ->send();

                    $action->cancel();
                }
            })
            ->action(function (Collection $records, array $data) {
                $targetId = $data['target_id'];

                if (! $records->pluck('id')->contains($targetId)) {
                    Notification::make()
                        ->title('Invalid Target')
                        ->body('Selected target is not among the selected associations.')
                        ->danger()
                        ->send();

                    return;
                }

                $type = $records->first()->type;

                match ($type) {
                    'resource'   => Record::whereIn('resource_id', $records->pluck('id'))
                        ->update(['resource_id' => $targetId]),
                    'sector'     => Record::whereIn('sector_id', $records->pluck('id'))
                        ->update(['sector_id' => $targetId]),
                    'exhibition' => Record::whereIn('exhibition_id', $records->pluck('id'))
                        ->update(['exhibition_id' => $targetId]),
                };

                $recordsToDelete = $records->where('id', '!=', $targetId);
                Association::destroy($recordsToDelete->pluck('id'));

                Notification::make()
                    ->title('Associations Merged')
                    ->body("Merged {$recordsToDelete->count()} associations into '{$records->find($targetId)?->name}'")
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}
