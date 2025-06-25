<?php

namespace App\Filament\Imports;

use App\Models\Record;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class RecordDeleterImporter extends Importer
{
    protected static ?string $model = Record::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('email'),
        ];
    }

    public function resolveRecord(): ?Record
    {
        $email = preg_replace('/^\s+|\s+$/u', '', $this->data['email']);
        // Skip empty email rows
        if (empty($email)) {
            return null;
        }

        // Get all matching records
        $records = Record::where('email', $email)->get();

        foreach ($records as $record) {
            if (!empty($record->mobile_number)) {
                // Has mobile, just clear email
                $record->email = null;
                $record->save();
            } else {
                // No mobile, safe to delete
                $record->delete();
            }
        }

        return null;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your record deletion import has completed. ';

        return $body;
    }
}
