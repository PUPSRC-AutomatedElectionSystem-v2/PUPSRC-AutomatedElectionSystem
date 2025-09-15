<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Configuration\Exceptions;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;

/**
 * Registers tenancy related exception renderers.
 */
final class TenancyExceptionHandlers
{
    /**
     * Register renderable callbacks for tenant identification exceptions.
     */
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->renderable(function (TenantCouldNotBeIdentifiedOnDomainException $e, $request) {

            if (app()->environment('production')) {
                report($e);
                abort(404);
            }

            return null;
        });
    }
}
