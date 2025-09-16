<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Domain;

class GenerateHostsEntries extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenancy:generate-hosts {--ip=127.0.0.1 : IP address to map} {--central= : Central domain (defaults to first central_domains entry)}';

    /**
     * The console command description.
     */
    protected $description = 'Generate hosts file entries for all tenant domains/subdomains.';

    public function handle(): int
    {
        $ip = (string) $this->option('ip');
        $central = (string) ($this->option('central') ?: (config('tenancy.central_domains')[0] ?? 'localhost'));

        $domains = Domain::query()->pluck('domain')->all();

        if (empty($domains)) {
            $this->info('# No tenant domains found.');
            return self::SUCCESS;
        }

        $this->line('# Add these lines to your hosts file (run as Administrator on Windows):');
        foreach ($domains as $d) {
            $host = str_contains((string) $d, '.') ? (string) $d : ((string) $d . '.' . $central);
            $this->line(sprintf('%s %s', $ip, $host));
        }

        return self::SUCCESS;
    }
}
