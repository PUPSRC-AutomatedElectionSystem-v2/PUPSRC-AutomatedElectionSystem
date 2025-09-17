<?php

declare(strict_types=1);

namespace App\Http\Resources\CentralResources\Tenants;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @extends JsonResource<array<string, mixed>>
 */
class TenantCreatedResource extends JsonResource
{
    public $responseMessage = 'Tenant created successfully.';

    public function toArray(Request $request): array
    {
        return [
            'message' => $this->responseMessage,
            'tenant' => new TenantResource($this->resource['tenant']->makeHidden(['data'])),
            'domain' => new DomainResource($this->resource['domain']),
            'organization' => new OrganizationResource($this->resource['organization']),
            'contact' => new OrganizationContactResource($this->resource['contacts']),
        ];
    }

    /**
     * Customize the outgoing response for the resource.
     */
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->setStatusCode(201, $this->responseMessage);
    }
}
