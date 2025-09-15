<?php

declare(strict_types=1);

namespace App\Services\CentralServices\Tenants;

interface HostRegistrarInterface
{
    /**
     * Register a hostname pointing to an IP address (e.g. 127.0.0.1)
     *
     * @param string $host
     * @param string $ip
     * @return void
     */
    public function register(string $host, string $ip = '127.0.0.1'): void;

    /**
     * Unregister a hostname
     *
     * @param string $host
     * @return void
     */
    public function unregister(string $host): void;

    /**
     * Check whether the host is present
     */
    public function exists(string $host): bool;
}
