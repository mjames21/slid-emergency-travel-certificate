<?php

namespace App\Rules;

use App\Support\StaffEmailDomains;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StaffEmailDomain implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! StaffEmailDomains::allows((string) $value)) {
            $domains = implode(', ', StaffEmailDomains::allowedDomains());
            $message = $domains === ''
                ? "The {$attribute} must use a configured staff email domain."
                : "The {$attribute} must use an approved staff email domain: {$domains}.";

            $fail($message);
        }
    }
}
