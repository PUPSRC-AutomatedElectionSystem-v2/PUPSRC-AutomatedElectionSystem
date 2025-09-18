<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDomainRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = trim((string) $value);

        if (! self::isValid($domain)) {
            $fail('The '.$attribute.' must be a valid domain name.');
        }
    }

    /**
     * Determine if the provided domain is valid.
     *
     * This supports IDN conversion when available and enforces:
     *  - overall length <= 255
     *  - each label length between 1 and 63
     *  - FILTER_VALIDATE_DOMAIN with HOSTNAME flag
     */
    public static function isValid(string $domain): bool
    {
        $domain = trim($domain);
        if ($domain === '') {
            return false;
        }

        // Convert IDN (unicode) domain to ASCII when available.
        if (function_exists('idn_to_ascii')) {
            $ascii = @idn_to_ascii($domain, IDNA_DEFAULT, defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : 0);
            if ($ascii !== false && $ascii !== '') {
                $domain = $ascii;
            }
        }

        // overall length limit
        if (mb_strlen($domain) > 255) {
            return false;
        }

        // PHP filter for hostnames
        if (! filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return false;
        }

        // check each label length (<=63 and non-empty)
        $labels = explode('.', $domain);
        foreach ($labels as $label) {
            if ($label === '' || mb_strlen($label) > 63) {
                return false;
            }
        }

        return true;
    }
}
