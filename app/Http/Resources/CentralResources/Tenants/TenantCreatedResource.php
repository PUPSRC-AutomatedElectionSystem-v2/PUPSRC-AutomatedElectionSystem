<?php

declare(strict_types=1);

namespace App\Http\Resources\CentralResources\Tenants;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @extends JsonResource<array<string, mixed>>
 */
class TenantCreatedResource extends JsonResource
{
    /**
     * Disable the default 'data' wrapping to keep top-level keys consistent with current API.
     */
    public static $wrap = null;

    /**
     * @param  array{tenant: \App\Models\Tenant, domain: \Stancl\Tenancy\Database\Models\Domain, organization: \App\Models\Tenants\Organizations, contacts: \App\Models\Tenants\OrganizationContacts}  $resource
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => 'Tenant created successfully.',
            'tenant' => [
                'id' => $this->resource['tenant']->id,
            ],
            'domain' => [
                'domain' => $this->resource['domain']->domain,
            ],
            'organization' => [
                'id' => $this->resource['organization']->id,
                'short_name' => $this->resource['organization']->short_name,
                'name' => $this->resource['organization']->name,
            ],
            'contacts' => [
                'id' => $this->resource['contacts']->id,
                'email' => $this->resource['contacts']->email,
            ],
        ];
    }
}
