<?php

namespace App\Filament\Imports;

use App\Models\Association;
use App\Models\Country;
use App\Models\Record;
use App\Models\State;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\Rules\Phone;

class RecordImporter extends Importer
{
    protected static ?string $model = Record::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('sector')
                ->requiredMapping(),
            ImportColumn::make('resource')
                ->requiredMapping(),
            ImportColumn::make('exhibition')
                ->requiredMapping(),
            ImportColumn::make('title')
                ->requiredMapping(),
            ImportColumn::make('first_name')
                ->requiredMapping(),
            ImportColumn::make('last_name')
                ->requiredMapping(),
            ImportColumn::make('gender')
                ->requiredMapping(),
            ImportColumn::make('company')
                ->requiredMapping(),
            ImportColumn::make('email')
                ->rules([
                    'nullable',
                    'email',
                    'unique:records,email',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                ])
                ->requiredMapping(),

            ImportColumn::make('mobile_number')
                ->rules([
                    'nullable',
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
            ImportColumn::make('phone')
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
        // Ignore "Select" value for title
        if (isset($this->data['title']) && strtolower($this->data['title']) === 'select') {
            $this->data['title'] = null;
        }

        // Fetch the country ID based on the code first, then name
        $country = Country::where('code', $this->data['country'])->first()
            ?? Country::where('name', $this->data['country'])->first();

        if ($country) {
            $this->data['country'] = $country->id;
            $state = State::where('name', $this->data['city'])->first();
            $this->data['city'] = $state ? $state->id : null;
        } else {
            $this->data['country'] = null;
            $this->data['city'] = null;
        }

        $this->data['sector'] = $this->resolveAssociationId($this->data['sector'], 'sector');
        $this->data['resource'] = $this->resolveAssociationId($this->data['resource'], 'resource');
        $this->data['exhibition'] = $this->resolveAssociationId($this->data['exhibition'], 'exhibition');

        // Convert scientific notation in mobile_number
        if (!empty($this->data['mobile_number'])) {
            if (stripos($this->data['mobile_number'], 'E') !== false) {
                $this->data['mobile_number'] = sprintf('%.0f', (float)$this->data['mobile_number']);
            }
            if (!str_starts_with($this->data['mobile_number'], '+')) {
                $this->data['mobile_number'] = '+' . $this->data['mobile_number'];
            }
        }

        // Process phone numbers
        if (!empty($this->data['phone'])) {
            // Split phone numbers by commas ONLY
            $numbers = preg_split('/,\s*/', $this->data['phone']);

            // Validate each number format
            $validPhones = array_filter($numbers, function ($num) {
                $num = trim($num);
                return preg_match('/^\+?\d[\d\s\-()]*$/', $num); // Allow valid formats
            });

            // If mobile_number is empty, assign the first valid phone number to it
            if (empty($this->data['mobile_number']) && !empty($validPhones)) {
                $firstValidPhone = array_shift($validPhones); // Get and remove the first valid phone

                // Convert scientific notation (e.g., 5.0E+12)
                if (stripos($firstValidPhone, 'E') !== false) {
                    $firstValidPhone = sprintf('%.0f', (float)$firstValidPhone);
                }

                // Ensure it starts with "+"
                if (!str_starts_with($firstValidPhone, '+')) {
                    $firstValidPhone = '+' . $firstValidPhone;
                }

                // Validate with LaravelPhone
                if (Validator::make(['phone' => $firstValidPhone], ['phone' => ['required', new Phone()]])->passes()) {
                    $this->data['mobile_number'] = $firstValidPhone;
                }
            }

            // Store the remaining valid numbers in phone as an array of ['number' => value]
            $this->data['phone'] = !empty($validPhones)
                ? array_map(fn($num) => ['number' => trim($num)], $validPhones)
                : null;

        } else {
            $this->data['phone'] = null;
        }

        return empty($this->data['email']) ? new Record() : Record::firstOrNew(['email' => $this->data['email']]);
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

        if (empty($name) || strtolower($name) === 'select') {
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
