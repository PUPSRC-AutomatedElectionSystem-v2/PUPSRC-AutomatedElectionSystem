<?php

namespace App\Http\Resources\CentralResources\Tenants;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
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
        return [
            'id' => $this->resource->id,
            'data' => $this->when(! empty($this->resource->data), $this->resource->data),

            $this->mergeWhen($this->includeTimestamp, [
                'created_at' => $this->resource->created_at,
                'updated_at' => $this->resource->updated_at,
            ]),
        ];
    }
}
