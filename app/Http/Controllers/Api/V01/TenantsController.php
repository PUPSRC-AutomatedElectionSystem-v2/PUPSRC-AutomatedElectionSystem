<?php

namespace App\Http\Controllers\Api\V01;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Resources\CentralResources\Tenants\TenantCreatedResource;
use App\Services\CentralServices\Tenants\CreateTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantsController extends Controller
{
    public function store(StoreTenantRequest $request, CreateTenant $action): JsonResponse|RedirectResponse|JsonResource
    {
        $data = $request->validated();

        $result = $action->handle($data);

        return new TenantCreatedResource($result);
    }
}
