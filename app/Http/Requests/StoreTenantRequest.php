<?php

declare(strict_types=1);

namespace App\Http\Requests;

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
            'tenant' => ['required', 'array'],
            'tenant.id' => ['nullable', 'string', 'max:255', 'unique:tenants,id'],
            'tenant.domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
            'tenant.use_default_domain' => ['required', 'boolean:strict'],

            'organization' => ['required', 'array'],
            'organization.short_name' => ['required', 'string', 'max:100'],
            'organization.name' => ['required', 'string', 'max:255'],
            'organization.category_name' => ['required', 'string', 'max:150'],
            'organization.should_copy_from_other_org' => ['sometimes', 'boolean'],
            'organization.allow_cross_membership' => ['sometimes', 'boolean'],
            'organization.theme' => ['sometimes', 'array'],
            'organization.order' => ['sometimes', 'integer', 'min:0'],

            'contacts' => ['required', 'array'],
            'contacts.email' => ['required', 'email:rfc'],
            'contacts.website' => ['nullable', 'url'],
            'contacts.facebook' => ['nullable', 'string', 'max:255'],
            'contacts.twitter' => ['nullable', 'string', 'max:255'],
            'contacts.instagram' => ['nullable', 'string', 'max:255'],
            'contacts.threads' => ['nullable', 'string', 'max:255'],
            'contacts.discord' => ['nullable', 'string', 'max:255'],
        ];
    }
}
