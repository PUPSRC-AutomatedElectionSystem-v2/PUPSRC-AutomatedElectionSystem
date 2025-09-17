<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public function organization(): HasOne
    {
        return $this->hasOne(Organization::class);
    }

    public function orgContact(): HasOneThrough
    {
        return $this->hasOneThrough(OrganizationContact::class, Organization::class);
    }
}
