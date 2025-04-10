<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SaudiPhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Remove all non-digit characters
        $value = preg_replace('/\D+/', '', $value);

        // If the number starts with '9660', remove the 0 after 966
        if (preg_match('/^9660/', $value)) {
            $value = '966' . substr($value, 4); // Remove the 0 after 966
        }

        // If it starts with '966' but no '+', add '+'
        if (preg_match('/^966/', $value)) {
            $value = "+$value";
        }

        // If number doesn't start with '+966'
        if (!preg_match('/^\+966/', $value)) {
            // If it starts with 0 (e.g. 05...), remove the 0
            if (preg_match('/^0\d+$/', $value)) {
                $value = substr($value, 1); // Remove the leading 0
            }

            // Then check if it's a Saudi number (starts with 5 and 8 digits after)
            if (preg_match('/^5\d{8}$/', $value)) {
                $value = "+966$value";
            } else {
                return false; // Not a valid Saudi number
            }
        }

        // Remove +966 for further validation
        $number = preg_replace('/^\+966/', '', $value);

        // Valid number patterns
        $validPatterns = [
            '/^5[0345689]\d{7}$/',   // Mobile numbers
            '/^81[1]\d{7}$/',        // GO nomadic numbers
            '/^57[01245678]\d{6}$/', // MVNOs
            '/^51\d{7}$/',           // Salam Mobile
        ];

        foreach ($validPatterns as $pattern) {
            if (preg_match($pattern, $number)) {
                return true;
            }
        }

        return false;
    }


    public function message()
    {
        return 'Invalid Saudi phone number.';
    }
}
