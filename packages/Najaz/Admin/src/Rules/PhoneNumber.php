<?php

namespace Najaz\Admin\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /**
         * This regular expression validates Jordanian mobile phone numbers with the following conditions:
         * - The phone number must be exactly 9 digits.
         * - The phone number must start with 73, 77, 78, or 71.
         */
        if (! preg_match('/^(73|77|78|71)\d{7}$/', $value)) {
            $fail('Admin::validation.phone-number')->translate();
        }
    }
}

