<?php

namespace App\Filament\Imports;

use App\Models\Association;
use App\Models\Country;
use App\Models\Record;
use App\Models\State;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Propaganistas\LaravelPhone\Rules\Phone;

class RecordImporter extends Importer
{
    protected static ?string $model = Record::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('classification')
                ->requiredMapping(),
            ImportColumn::make('badge')
                ->requiredMapping(),
            ImportColumn::make('sector')
                ->requiredMapping(),
            ImportColumn::make('subsector')
                ->requiredMapping(),
            ImportColumn::make('title')
                ->requiredMapping(),
            ImportColumn::make('first_name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('last_name')
                ->requiredMapping()
                ->rules(['required', 'max:32']),
            ImportColumn::make('gender')
                ->requiredMapping(),
            ImportColumn::make('company')
                ->requiredMapping(),
            ImportColumn::make('email')
                ->rules([
                    'required',
                    'email',
                    'unique:records,email',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                ])
                ->requiredMapping(),

            ImportColumn::make('mobile_number')
                ->rules([
                    new Phone(),
                    'unique:records,mobile_number',
                ])
                ->requiredMapping(),
            ImportColumn::make('country')
                ->requiredMapping(),
            ImportColumn::make('city')
                ->requiredMapping(),
            ImportColumn::make('job_title')
                ->requiredMapping(),
            ImportColumn::make('website')
                ->requiredMapping(),
            ImportColumn::make('scfhs')
                ->requiredMapping(),
            ImportColumn::make('phone')
                ->requiredMapping(),
            ImportColumn::make('other_information')
                ->requiredMapping(),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your record import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function resolveRecord(): ?Record
    {
        // Fetch the country ID based on the country name
        $country = Country::where('name', $this->data['country'])->first();
        if ($country) {
            $this->data['country'] = $country->id;
        } else {
            // Handle the case where the country does not exist
            $this->data['country'] = null; // or set a default country ID
        }

        // Fetch the state ID based on the city name
        $state = State::where('name', $this->data['city'])->first();
        if ($state) {
            $this->data['city'] = $state->id;
        } else {
            // Handle the case where the state does not exist
            $this->data['city'] = null; // or set a default state ID
        }

        $this->data['classification'] = $this->resolveAssociationId($this->data['classification'], 'classification');
        $this->data['badge'] = $this->resolveAssociationId($this->data['badge'], 'badge');
        $this->data['sector'] = $this->resolveAssociationId($this->data['sector'], 'sector');
        $this->data['subsector'] = $this->resolveAssociationId($this->data['subsector'], 'sub_sector');


        // Convert phone numbers into array format
        if (!empty($this->data['phone'])) {
            // Split numbers by comma or space if multiple numbers exist
            $numbers = preg_split('/[\s,]+/', $this->data['phone']);

            // Convert to the required array format
            $this->data['phone'] = array_map(fn($num) => ['number' => trim($num)], $numbers);
        }


        // Create or update the record
        return Record::firstOrNew([
            'email' => $this->data['email'],
        ]);
    }

    /**
     * Resolve the association ID based on the name and type.
     *
     * @param string $name The name of the association.
     * @param string $type The type of the association (classification, badge, sector, sub_sector).
     * @return int|null The ID of the association, or null if the name is invalid.
     */
    protected function resolveAssociationId($name, string $type)
    {
        if (empty($name)) {
            return null;
        }

        // Check if the association already exists
        $association = Association::where('name', $name)
            ->where('type', $type)
            ->first();

        if ($association != null) {
            return $association->name;
        } else {
            $newAssociation = Association::create([
                'name' => $name,
                'type' => $type,
            ]);
            return $newAssociation->name;
        }

    }
}
