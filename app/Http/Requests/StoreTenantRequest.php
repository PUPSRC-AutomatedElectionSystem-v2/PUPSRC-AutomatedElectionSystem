<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\Tenant\CreateOrganizationContactRule;
use App\Rules\Tenant\CreateOrganizationRule;
use App\Rules\Tenant\CreateTenantRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant' => ['required', 'array', new CreateTenantRule],

            'organization' => ['required', 'array', new CreateOrganizationRule],

            'contacts' => ['required', 'array', new CreateOrganizationContactRule],
        ];
    }
}
