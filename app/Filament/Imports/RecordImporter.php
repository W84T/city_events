<?php

namespace App\Filament\Imports;

use App\Models\Association;
use App\Models\Country;
use App\Models\Record;
use App\Models\State;
use App\Rules\SaudiPhoneNumber;
use Carbon\CarbonInterface;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
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
                ->requiredMapping()
                ->guess(['Sector', 'sector']),

            ImportColumn::make('resource')
                ->requiredMapping()
                ->guess(['Resource', 'resource']),

            ImportColumn::make('exhibition')
                ->requiredMapping()
                ->guess(['Exhibition', 'exhibition', 'Exhibition Name']),

            ImportColumn::make('title')
                ->requiredMapping()
                ->guess(['Title ', 'title', 'title']),

            ImportColumn::make('first_name')
                ->requiredMapping()
                ->guess(['F Name ', 'first_name']),

            ImportColumn::make('last_name')
                ->requiredMapping()
                ->guess(['L Name ', 'last_name']),

            ImportColumn::make('gender')
                ->requiredMapping()
                ->guess(['Gender', 'gender', 'Sex ']),

            ImportColumn::make('company')
                ->requiredMapping()
                ->guess(['Company ', 'company', 'Company']),

            ImportColumn::make('email')
                ->requiredMapping()
                ->rules([
                    'nullable',
                    'email',
                    'unique:records,email',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                ])
                ->guess(['Email ', 'email', 'Email']),

            ImportColumn::make('mobile_number')
                ->requiredMapping()
                ->rules([
                    'nullable',
                    'unique:records,mobile_number',
                ])
                ->guess(['Mobile ', 'mobile_number', 'Mobile Number']),

            ImportColumn::make('country')
                ->requiredMapping()
                ->guess(['Country ', 'country', 'Country']),

            ImportColumn::make('city')
                ->requiredMapping()
                ->guess(['City ', 'city', 'City']),

            ImportColumn::make('job_title')
                ->requiredMapping()
                ->guess(['Job Title ', 'job_title', 'Job Title']),

            ImportColumn::make('website')
                ->requiredMapping()
                ->guess(['WebSite ', 'website', 'Website']),

            ImportColumn::make('phone')
                ->requiredMapping()
                ->guess(['Phone 1 ', 'phone']),
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
        if ($this->isEmptyRow()) {
            return null;
        }

        $this->sanitizeTitle();
        $this->resolveCountryCity();

        $this->data['sector'] = $this->resolveAssociationId($this->data['sector'], 'sector');
        $this->data['resource'] = $this->resolveAssociationId($this->data['resource'], 'resource');
        $this->data['exhibition'] = $this->resolveAssociationId($this->data['exhibition'], 'exhibition');

        $this->validateAndProcessPhoneNumbers();
        $this->validateEmail();
        if (empty($this->data['email']) && empty($this->data['mobile_number'])) {
            throw new RowImportFailedException("Email or Phone are must be valid");
        }
        return empty($this->data['email']) ? new Record() : Record::firstOrNew(['email' => $this->data['email']]);
    }

    protected function validateAndProcessPhoneNumbers(): void
    {
        $phoneFields = ['mobile_number', 'phone'];

        foreach ($phoneFields as $field) {
            if (!empty($this->data[$field])) {
                $this->data[$field] = trim($this->data[$field]);
                if (!str_starts_with($this->data[$field], '+')) {
                    if (preg_match('/^5\d{8}$/', $this->data[$field])) {
                        $this->data[$field] = '+966' . $this->data[$field];
                    } else {
                        $this->data[$field] = '+' . $this->data[$field];
                    }
                }

                if ($this->isLikelyFakeNumber($this->data[$field]) ||
                    !Validator::make([$field => $this->data[$field]], [
                        $field => [
                            'required',
                            function ($attribute, $value, $fail) {
                                $value = preg_replace('/\D+/', '', $value);
                                if (preg_match('/^(?:\+?966|5\d{8})/', $value)) {
                                    $rule = new SaudiPhoneNumber();
                                    if (!$rule->passes($attribute, $value)) {
                                        $fail($rule->message());
                                    }
                                } else {
                                    $rule = new Phone();
                                }
                            },
                        ],
                    ])->passes()) {
                    $this->data[$field] = null;
                }
            }
        }
    }

    protected function validateEmail(): void
    {
        $emailValid = !empty($this->data['email']) &&
            Validator::make(['email' => $this->data['email']], ['email' => ['email', 'unique:records,email']])->passes();

        if (!$emailValid) {
            $this->data['email'] = null;
        }
    }

    protected function isEmptyRow(): bool
    {
        $requiredFields = ['sector', 'resource', 'exhibition', 'title', 'first_name', 'last_name', 'email', 'mobile_number', 'country'];
        return collect($requiredFields)->every(fn($field) => empty($this->data[$field]));
    }

    protected function sanitizeTitle(): void
    {
        if (!empty($this->data['title']) && strtolower($this->data['title']) === 'select') {
            $this->data['title'] = null;
        }
    }

    protected function resolveCountryCity(): void
    {
        $country = Country::where('code', $this->data['country'])->first() ?? Country::where('name', $this->data['country'])->first();
        if ($country) {
            $this->data['country'] = $country->id;
            $state = State::where('name', $this->data['city'])->first();
            $this->data['city'] = $state ? $state->id : null;
        } else {
            $this->data['country'] = null;
            $this->data['city'] = null;
        }
    }

    protected function resolveAssociationId($name, string $type)
    {
        if (empty($name) || strtolower($name) === 'select') {
            return null;
        }

        $association = Association::where('name', $name)->where('type', $type)->first();
        return $association ? $association->name : Association::create(['name' => $name, 'type' => $type])->name;
    }

    protected function isLikelyFakeNumber(string $number): bool
    {
        // Remove non-numeric characters for more accurate matching
        $normalizedNumber = preg_replace('/\D+/', '', $number);

        // Common patterns of fake/test numbers
        $fakePatterns = [
            '/^1234567890$/',
            '/^1111111111$/',
            '/^0000000000$/',
            '/^9999999999$/',
            '/^11234567890$/',
            '/^15555555555$/',
            '/^19999999999$/',
        ];

        foreach ($fakePatterns as $pattern) {
            if (preg_match($pattern, $normalizedNumber)) {
                return true;
            }
        }

        return false;
    }

}
