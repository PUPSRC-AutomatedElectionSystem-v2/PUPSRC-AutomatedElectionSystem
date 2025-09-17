<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantRequest;
use App\Http\Resources\CentralResources\Tenants\TenantCreatedResource;
use App\Http\Resources\CentralResources\Tenants\ViewModels\CreateTenantViewModel;
use App\Services\CentralServices\Tenants\CreateTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\View\View;

class TenantsController extends Controller
{
    public function create(Request $request): View
    {
        $vm = new CreateTenantViewModel;

        return view('tenants.create', $vm->toArray());
    }

    public function store(StoreTenantRequest $request, CreateTenant $action): JsonResponse|RedirectResponse|JsonResource
    {
        $data = $request->validated();

        $result = $action->handle($data);

        return new TenantCreatedResource($result);
    }
}
