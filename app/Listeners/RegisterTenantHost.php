<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Events\TenantCreated;
use App\Services\CentralServices\Tenants\HostRegistrationService;
use App\Models\CentralModels\Tenant as TenantModel;
use Stancl\Tenancy\Events\DomainCreated;

class RegisterTenantHost
{
    public function handle(DomainCreated $event): void
    {
        $domain = $event->domain;

        // Register host on the OS after the central DB transaction commits (so the domain record exists)
        DB::afterCommit(function () use ($domain) {
            $domainName = $domain->domain;
            try {
                /** @var HostRegistrationService $registrar */
                $registrar = app(HostRegistrationService::class);
                $registrar->register($domainName);
            } catch (\Throwable $e) {
                // Log but do not break tenant creation
                logger()->error('Failed to register host for tenant: ' . $e->getMessage(), ['domain' => $domainName]);
            }
        });
    }
}
