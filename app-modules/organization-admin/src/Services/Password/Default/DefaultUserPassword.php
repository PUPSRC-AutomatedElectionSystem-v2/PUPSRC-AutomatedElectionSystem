<?php

namespace Modules\OrganizationAdmin\Services\Password\Default;

class DefaultUserPassword
{
    /**
     * Get the default plaintext password for users.
     */
    public function get(): string
    {
        return config('auth.passwords.users.default');
    }
}
