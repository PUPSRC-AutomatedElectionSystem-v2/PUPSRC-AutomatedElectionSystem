<?php

namespace App\Http\Resources\CentralResources\Tenants;

use App\Services\DomainResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainResource extends JsonResource
{
    public function __construct(
        $resource,
        protected bool $includeTimestamp = false
    ) {
        parent::__construct($resource);
        $this->includeTimestamp = $includeTimestamp;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $storedDomainName = $this->resource->domain;
        $domain = DomainResolver::fullDomain($storedDomainName);

        return [
            'domain' => $domain,
            'tenant_id' => $this->resource->tenant_id,

            $this->mergeWhen($this->includeTimestamp, [
                'created_at' => $this->resource->created_at,
                'updated_at' => $this->resource->updated_at,
            ]),
        ];
    }
}
