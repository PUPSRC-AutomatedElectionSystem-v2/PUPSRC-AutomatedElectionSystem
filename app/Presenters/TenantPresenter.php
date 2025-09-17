<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\Tenant;
use App\Models\Tenants\Organization;
use App\Models\Tenants\OrganizationContact;
use Stancl\Tenancy\Database\Models\Domain;

class TenantPresenter
{
    /**
     * @param  array{tenant: Tenant, domain: Domain, organization: Organization, contacts: OrganizationContact}  $data
     * @return array<string, mixed>
     */
    public function created(array $data): array
    {
        return [
            'message' => 'Tenant created successfully.',
            'tenant' => [
                'id' => $data['tenant']->id,
            ],
            'domain' => [
                'domain' => $data['domain']->domain,
            ],
            'organization' => [
                'id' => $data['organization']->id,
                'short_name' => $data['organization']->short_name,
                'name' => $data['organization']->name,
            ],
            'contacts' => [
                'id' => $data['contacts']->id,
                'email' => $data['contacts']->email,
            ],
        ];
    }
}
