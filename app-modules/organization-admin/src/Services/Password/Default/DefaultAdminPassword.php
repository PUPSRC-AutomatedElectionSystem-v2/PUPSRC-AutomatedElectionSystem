<?php

namespace Modules\OrganizationAdmin\Services\Password\Default;

class DefaultAdminPassword
{
    /**
     * Get the default plaintext password for admins.
     */
    public function get(): string
    {
        return config('auth.passwords.admin.default');
    }
}
