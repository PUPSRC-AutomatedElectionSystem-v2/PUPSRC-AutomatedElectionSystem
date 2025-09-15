<?php

declare(strict_types=1);

namespace App\Services\CentralServices\Tenants;

use RuntimeException;

class HostRegistrationService
{
    private HostRegistrarInterface $registrar;

    public function __construct(?HostRegistrarInterface $registrar = null)
    {
        if ($registrar !== null) {
            $this->registrar = $registrar;
            return;
        }

        $os = PHP_OS_FAMILY; // 'Windows', 'Linux', 'Darwin', 'BSD', 'Solaris', 'Unknown'

        if ($os === 'Windows') {
            $this->registrar = new WindowsHostRegistrar();
        } else {
            // Default to Linux semantics for non-Windows
            $this->registrar = new LinuxHostRegistrar();
        }
    }

    /**
     * Usage:
     *
     * // From a controller or listener
     * app(HostRegistrationService::class)->register('elite.pupsraes.test');
     *
     * // From tinker
     * php artisan tinker --execute="app(App\\Services\\CentralServices\\Tenants\\HostRegistrationService::class)->register('elite.pupsraes.test')"
     *
     * Notes:
     * - On Windows this modifies C:\\Windows\\System32\\drivers\\etc\\hosts (requires Admin).
     * - On Linux the service attempts to use /etc/hosts and will fall back to a sudo copy if not root.
     */

    public function register(string $host, string $ip = '127.0.0.1'): void
    {
        $host = $this->normalizeHost($host);
        $this->registrar->register($host, $ip);
    }

    public function unregister(string $host): void
    {
        $host = $this->normalizeHost($host);
        $this->registrar->unregister($host);
    }

    public function exists(string $host): bool
    {
        $host = $this->normalizeHost($host);
        return $this->registrar->exists($host);
    }

    private function normalizeHost(string $host): string
    {
        return trim(mb_strtolower($host));
    }
}
