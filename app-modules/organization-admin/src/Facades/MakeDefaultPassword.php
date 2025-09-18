<?php

namespace Modules\OrganizationAdmin\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getDefaultPassword(string $accountType)
 */
class MakeDefaultPassword extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'make-default-password';
    }
}
