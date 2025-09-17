<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Events\DomainCreated;
use Symfony\Component\Console\Output\ConsoleOutput;

class PrintHostsEntryForDomain
{
    /**
     * Handle the event.
     */
    public function handle(DomainCreated $event): void
    {
        $centralDomains = (array) Config::get('tenancy.central_domains', []);
        $centralFromConfig = $centralDomains[0] ?? null;
        $appUrlHost = (string) parse_url((string) Config::get('app.url'), PHP_URL_HOST) ?: '';
        $central = (string) ($centralFromConfig ?: ($appUrlHost ?: 'pupsraes.test'));
        $domainValue = (string) $event->domain->domain;
        $fqdn = str_contains($domainValue, '.') ? $domainValue : ($domainValue.'.'.$central);

        $line = sprintf('127.0.0.1 %s', $fqdn);
        $psCmd = sprintf('powershell -ExecutionPolicy Bypass -File scripts/add-host.ps1 -Host %s -IP 127.0.0.1', $fqdn);

        $message = "Add this to your Windows hosts file to access the tenant locally:\n  $line\nOr run:\n  $psCmd";

        // Log to laravel.log
        Log::notice($message);

        // If running via CLI, also print to stdout for convenience
        if (PHP_SAPI === 'cli') {
            (new ConsoleOutput)->writeln($message);
        }
    }
}
