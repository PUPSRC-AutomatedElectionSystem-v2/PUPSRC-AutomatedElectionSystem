<?php

namespace App\Rules\Tenant;

use App\Services\DomainResolver;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Stancl\Tenancy\Database\Models\Domain;

class UpdateTenantRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tenantId = $value['id'] ?? null;

        if (! $tenantId) {
            $fail('The tenant id is required for updates.');

            return;
        }

        // Get current domains for this tenant
        $currentDomains = Domain::where('tenant_id', $tenantId)
            ->pluck('domain')
            ->toArray();

        $rules = [
            $attribute.'.id' => 'required|string|max:255|unique:tenants,id,'.$tenantId,
        ];

        // Validate domain and use_default_domain together if domain is being updated
        if (isset($value['domain'])) {
            $rules[$attribute.'.domain'] = 'required|string|max:255';
            $rules[$attribute.'.use_default_domain'] = 'required|boolean:strict';

            $newDomain = $value['domain'];
            $useDefaultDomain = $value['use_default_domain'] ?? false;

            // Validate domain format using centralized validation
            $errorMessage = DomainResolver::validateDomainWithMessage($newDomain, $useDefaultDomain);
            if ($errorMessage) {
                $fail($errorMessage);

                return;
            }

            // Check uniqueness only if domain is actually changing
            if (! in_array($newDomain, $currentDomains)) {
                $rules[$attribute.'.domain'] .= '|unique:domains,domain';
            }
        } elseif (isset($value['use_default_domain'])) {
            // If only use_default_domain is provided without domain, that's invalid
            $fail('use_default_domain can only be updated together with domain.');

            return;
        }

        $validator = Validator::make([$attribute => $value], $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $fail($error);
            }
        }
    }
}
