<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SaudiPhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Remove spaces and special characters (just in case)
        $value = preg_replace('/\D+/', '', $value);

        // If the number starts with '966' but no '+', add '+'
        if (preg_match('/^966/', $value)) {
            $value = "+$value";
        }

        // If the number is missing '966', assume it's a Saudi number and prepend '+966'
        if (!preg_match('/^\+966/', $value)) {
            if (preg_match('/^5\d{8}$/', $value)) { // Mobile number without country code
                $value = "+966$value";
            } else {
                return false; // Invalid format
            }
        }

        // Remove country code for further checks
        $number = preg_replace('/^\+966/', '', $value);

        // Define valid number patterns for mobile and landline numbers
        $validPatterns = [
            '/^5[0345689]\d{6,8}$/',  // ✅ Mobile numbers (STC, Mobily, Zain, etc.)
            '/^1[0123467]\d{7}$/',    // ✅ Landlines (all valid regions)
            '/^81[1]\d{7}$/',         // ✅ GO nomadic numbers
            '/^57[01245678]\d{6}$/' , // ✅ MVNOs (Virgin, Red Bull, Lebara)
            '/^51\d{7}$/',            // ✅ Salam Mobile (previously BravO!)
        ];


        // Validate against patterns
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
