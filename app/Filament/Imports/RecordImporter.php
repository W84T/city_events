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

        // Process mobile number
        if (!empty($this->data['mobile_number'])) {
            $this->data['mobile_number'] = trim($this->data['mobile_number']);

            // Convert scientific notation (e.g., 5.0E+12)
//            if (stripos($this->data['mobile_number'], 'E') !== false) {
//                $this->data['mobile_number'] = sprintf('%.0f', (float)$this->data['mobile_number']);
//            }

            // If the number does NOT start with "+" and seems to be a Saudi number
            if (!str_starts_with($this->data['mobile_number'], '+')) {
                if (preg_match('/^5\d{8}$/', $this->data['mobile_number'])) {
                    // Prepend Saudi country code +966
                    $this->data['mobile_number'] = '+966' . $this->data['mobile_number'];
                } else {
                    // Otherwise, assume it's an international number
                    $this->data['mobile_number'] = '+' . $this->data['mobile_number'];
                }
            }

            // Check for fake numbers and validate format
            if ($this->isLikelyFakeNumber($this->data['mobile_number']) ||
                !Validator::make(
                    ['mobile_number' => $this->data['mobile_number']],
                    [
                        'mobile_number' =>
                            [
                                'required',
                                function ($attribute, $value, $fail) {
                                    // Normalize number: Remove spaces, dashes, and special characters
                                    $value = preg_replace('/\D+/', '', $value);

                                    // Detect Saudi numbers (even if +966 is missing)
                                    if (preg_match('/^(?:\+?966|5\d{8})/', $value)) {
                                        // Saudi number: Apply custom validation
                                        $rule = new SaudiPhoneNumber();
                                        if (!$rule->passes($attribute, $value)) {
                                            $fail($rule->message());
                                        }
                                    } else {
                                        $rule = new Phone();
                                    }
                                },
                            ]
                    ])
                    ->passes()) {
                $this->data['mobile_number'] = null;
            }
        }

        // Process phone numbers
        if (!empty($this->data['phone'])) {
            // Split phone numbers by commas ONLY
            $numbers = preg_split('/,\s*/', $this->data['phone']);

            // Validate each number format and filter out fake numbers
            $validPhones = array_filter($numbers, function ($num) {
                $num = trim($num);
                return preg_match('/^\+?\d[\d\s\-()]*$/', $num) && !$this->isLikelyFakeNumber($num);
            });

            // If mobile_number is empty, assign the first valid phone number to it
            if (empty($this->data['mobile_number']) && !empty($validPhones)) {
                $firstValidPhone = array_shift($validPhones);

                // Convert scientific notation
                if (stripos($firstValidPhone, 'E') !== false) {
                    $firstValidPhone = sprintf('%.0f', (float)$firstValidPhone);
                }

                // Ensure it starts with "+"
                if (!str_starts_with($firstValidPhone, '+')) {
                    $firstValidPhone = '+' . $firstValidPhone;
                }

                // Final validation before assignment
                if (Validator::make(['phone' => $firstValidPhone], ['phone' => ['required',
                    function ($attribute, $value, $fail) {
                        // Normalize number: Remove spaces, dashes, and special characters
                        $value = preg_replace('/\D+/', '', $value);

                        // Detect Saudi numbers (even if +966 is missing)
                        if (preg_match('/^(?:\+?966|5\d{8})/', $value)) {
                            // Saudi number: Apply custom validation
                            $rule = new SaudiPhoneNumber();
                            if (!$rule->passes($attribute, $value)) {
                                $fail($rule->message());
                            }
                        } else {
                            $rule = new Phone();
                        }
                    },]])->passes()) {
                    $this->data['mobile_number'] = $firstValidPhone;
                }
            }

            // Store remaining valid numbers
            $this->data['phone'] = !empty($validPhones)
                ? array_map(fn($num) => ['number' => trim($num)], $validPhones)
                : null;
        } else {
            $this->data['phone'] = null;
        }

        // Validate email
        $emailValid = !empty($this->data['email']) &&
            Validator::make(
                ['email' => $this->data['email']],
                ['email' => ['email', 'unique:records,email', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/']]
            )->passes();

        // Validate mobile number (after all processing)
        $mobileValid = !empty($this->data['mobile_number']);

        if (!$emailValid && !$mobileValid) {
            throw new RowImportFailedException("Email or Phone are must be valid");
        }

        // Accept if either is valid but set invalid one to null
        if (!$emailValid) {
            $this->data['email'] = null;
        }
        if (!$mobileValid) {
            $this->data['mobile_number'] = null;
        }

        return empty($this->data['email']) ? new Record() : Record::firstOrNew(['email' => $this->data['email']]);
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
    }

    protected function resolveAssociationId($name, string $type)
    {
        if (empty($name) || strtolower($name) === 'select') {
            $this->data['import_failures'] = [
                'email' => 'At least one valid contact method (email or phone) is required',
                'mobile_number' => 'At least one valid contact method (email or phone) is required'
            ];
            return null;
        }

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

    protected function isLikelyFakeNumber(string $number): bool
    {
        // Remove all non-digit characters
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);

        // 1. All zeros (e.g., 0000000000 or +966000000000)
        if (preg_match('/^\+?[0-9]{1,3}0+$/', $number)) {
            return true;
        }

        // 2. Repeated digits (e.g., 5555555555)
        if (preg_match('/^(\d)\1{7,}$/', $cleanNumber)) {
            return true;
        }

        // 3. Sequential digits (e.g., 1234567890 or 9876543210)
        if (preg_match('/^0?1?2?3?4?5?6?7?8?9?$/', $cleanNumber) ||
            preg_match('/^9?8?7?6?5?4?3?2?1?0?$/', $cleanNumber)) {
            return true;
        }

        // 4. Common fake patterns in Saudi numbers (e.g., 966506000000)
        if (str_starts_with($cleanNumber, '966')) {
            $localPart = substr($cleanNumber, 3);

            // Patterns like 50X000000 where X is often 6,5, etc.
            if (preg_match('/^50[0-9]0{6}$/', $localPart)) {
                return true;
            }

            // Patterns like 55X000000
            if (preg_match('/^55[0-9]0{6}$/', $localPart)) {
                return true;
            }

            // Patterns where last 6 digits are zeros
            if (preg_match('/^.{5}0{6}$/', $localPart)) {
                return true;
            }
        }

        // 5. Add patterns for other countries as needed
        // Example for UAE numbers (971)
        if (str_starts_with($cleanNumber, '971')) {
            $localPart = substr($cleanNumber, 3);
            if (preg_match('/^50[0-9]0{6}$/', $localPart)) {
                return true;
            }
        }

        return false;
    }

    public function getJobRetryUntil(): ?CarbonInterface
    {
        return now()->addMinutes(5);
    }
}
