<?php

namespace App\Events;

use App\Models\Central\Organization;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Events\TenantCreated as BaseTenantCreated;

class TenantCreated extends BaseTenantCreated
{
    public function __construct(Tenant $tenant, Organization $newOrganization)
    {
        parent::__construct($tenant);

        $this->tenant->orgContact = $newOrganization->orgContact;
    }
}
