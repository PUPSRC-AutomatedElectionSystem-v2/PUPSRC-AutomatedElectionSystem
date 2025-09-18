<?php

namespace App\Rules\Tenant;

use App\Services\DomainResolver;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class CreateTenantRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $rules = [
            $attribute.'.id' => 'nullable|string|max:255|unique:tenants,id',
            $attribute.'.domain' => 'required|string|max:255',
            $attribute.'.use_default_domain' => 'required|boolean:strict',
        ];

        // Add domain validation based on use_default_domain flag
        if (isset($value['domain']) && isset($value['use_default_domain'])) {
            $domain = $value['domain'];
            $useDefaultDomain = $value['use_default_domain'];

            $errorMessage = DomainResolver::validateDomainWithMessage($domain, $useDefaultDomain);
            if ($errorMessage) {
                $fail($errorMessage);

                return;
            }

            // Add uniqueness check
            $rules[$attribute.'.domain'] .= '|unique:domains,domain';
        }

        $validator = Validator::make([$attribute => $value], $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $fail($error);
            }
        }
    }
}
