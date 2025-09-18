<?php

namespace Modules\OrganizationAdmin\Services\Password\Default;

class MakeDefaultPassword
{
    public function __construct(
        private DefaultUserPassword $defaultUserPassword,
        private DefaultAdminPassword $defaultAdminPassword,
    ) {}

    /**
     * Get the default plaintext password based on account type.
     */
    public function getDefaultPassword(string $accountType): string
    {
        return match ($accountType) {
            'voter' => $this->defaultUserPassword->get(),
            'committee' => $this->defaultAdminPassword->get(),
            default => throw new \InvalidArgumentException("Invalid account type: {$accountType}"),
        };
    }
}
