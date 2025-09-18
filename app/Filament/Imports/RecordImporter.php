<?php

namespace App\Filament\Imports;

use App\Models\Association;
use App\Models\Country;
use App\Models\Record;
use App\Models\State;
use App\Rules\SaudiPhoneNumber;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Propaganistas\LaravelPhone\Rules\Phone;

class RecordImporter extends Importer
{
    protected static ?string $model = Record::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('sector_id')
                ->requiredMapping()
                ->guess(['Sector', 'sector']),

            ImportColumn::make('resource_id')
                ->requiredMapping()
                ->guess(['Resource', 'resource']),

            ImportColumn::make('exhibition_id')
                ->requiredMapping()
                ->guess(['Exhibition', 'exhibition', 'Exhibition Name']),

            ImportColumn::make('title')
                ->guess(['Title ', 'title', 'title']),

            ImportColumn::make('first_name')
                ->guess(['F Name ', 'first_name']),

            ImportColumn::make('last_name')
                ->guess(['L Name ', 'last_name']),

            ImportColumn::make('gender')
                ->requiredMapping()
                ->guess(['Gender', 'gender', 'Sex ']),

            ImportColumn::make('company')
                ->requiredMapping()
                ->guess(['Company ', 'company', 'Company']),

            ImportColumn::make('email')
                ->requiredMapping()
                ->guess(['Email ', 'email', 'Email']),

            ImportColumn::make('mobile_number')
                ->requiredMapping()
                ->rules([
                    'nullable',
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
                ->castStateUsing(function ($state) {
                    // If empty, return null
                    if (blank($state)) {
                        return null;
                    }

                    // If it's a string with multiple phone numbers separated by '|', split into array
                    $phones = is_array($state) ? $state : explode(',', $state);

                    return array_map(function ($item) {
                        return ['number' => trim($item)];
                    }, $phones);
                })
                ->guess(['Phone 1 ', 'phone', 'phone 1']),
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

        $this->data['sector_id'] = $this->resolveAssociationId($this->data['sector_id'], 'sector');
        $this->data['resource_id'] = $this->resolveAssociationId($this->data['resource_id'], 'resource');
        $this->data['exhibition_id'] = $this->resolveAssociationId($this->data['exhibition_id'], 'exhibition');

        $this->validateAndProcessPhoneNumbers(); // formats & validates mobile number
        $this->validateEmail(); // formats & validates email

        $email = $this->data['email'] ?? null;
        $mobile = $this->data['mobile_number'] ?? null;

        $emailExists = $email ? Record::where('email', $email)->exists() : false;
        $mobileExists = $mobile ? Record::where('mobile_number', $mobile)->exists() : false;

        if ($email && !$emailExists && $mobile && !$mobileExists) {
            $this->data['mobile_number'] = $mobile;
            $this->data['email'] = $email;
        } elseif ($email && !$emailExists && (!$mobile || $mobileExists)) {
            $this->data['mobile_number'] = null;
        } elseif ($mobile && !$mobileExists && (!$email || $emailExists)) {
            $this->data['email'] = null;
        } else {
            throw new RowImportFailedException("Email and Mobile Number already exist or are invalid.");
        }

        return $this->data['email']
            ? Record::firstOrNew(['email' => $this->data['email']])
            : new Record();
    }

    protected function validateAndProcessPhoneNumbers(): void
    {
        $phoneFields = ['mobile_number'];

        foreach ($phoneFields as $field) {
            if (!empty($this->data[$field])) {
                // Take first line if multiline
                $number = trim(explode("\n", $this->data[$field])[0]);

                // Clean number: remove everything except digits and plus
                $number = preg_replace('/[^\d+]/', '', $number);

                // Replace multiple leading pluses with one plus
                $number = preg_replace('/^\++/', '+', $number);

                // Update the cleaned number back to data
                $this->data[$field] = $number;

                // Now apply your existing logic:
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
        if (!empty($this->data['email'])) {
            // Remove Unicode spaces (e.g., non-breaking space)
            $email = preg_replace('/^\s+|\s+$/u', '', $this->data['email']);

            // Create the validator instance
            $validator = Validator::make(
                ['email' => $email],
                ['email' => ['email', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/']]
            );

            $isValid = $validator->passes();
            $isUnique = !Record::where('email', $email)->exists();

            if (!$isValid || !$isUnique) {
                if (!$isValid) {
                    Log::warning("Email validation failed for '{$email}':", $validator->errors()->all());
                }

                if (!$isUnique) {
                    Log::warning("Email '{$email}' is already taken.");
                }

                $this->data['email'] = null;
            } else {
                $this->data['email'] = $email;
            }
        }
    }

    protected function isEmptyRow(): bool
    {
        $requiredFields = ['sector', 'resource', 'exhibition', 'title', 'first_name', 'last_name', 'email', 'mobile_number', 'country', 'phone'];
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


        if ($association) {
            return $association->id;
        }

        $newAssociation = Association::create([
            'name' => $name,
            'type' => $type,
        ]);

        return $newAssociation->id;
    }

    protected function transformPhoneField(): void
    {
        if (!empty($this->data['phone']) && is_array($this->data['phone'])) {
            $this->data['phone'] = array_map(function ($item) {
                return ['number' => trim($item)];
            }, $this->data['phone']);
        }
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
