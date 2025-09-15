<?php

declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class HostRegistrar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\CentralServices\Tenants\HostRegistrationService::class;
    }
}
