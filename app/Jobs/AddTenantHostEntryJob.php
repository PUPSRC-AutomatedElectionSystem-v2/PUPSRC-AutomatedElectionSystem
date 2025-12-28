<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class AddTenantHostEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $hostname,
        public string $hostAddress = '127.0.0.1',
        public ?string $customPath = null,
    ) {}

    public function handle(): void
    {
        // Use config() instead of env() per project conventions
        $python = config('programs.python_path', 'python');
        $script = base_path('scripts/add-host.py');

        $command = [$python, $script, $this->hostname, '--host_address', $this->hostAddress];

        if ($this->customPath) {
            $command[] = '--custom_path';
            $command[] = $this->customPath;
        }

        $process = new Process($command);
        $process->setTimeout(60);

        try {
            $process->run();

            $output = $process->getOutput();
            $errorOutput = $process->getErrorOutput();
            $combined = strtolower($output."\n".$errorOutput);

            $permissionDeniedDetected = str_contains($combined, 'permission denied');

            if (! $process->isSuccessful() || $permissionDeniedDetected) {
                Log::error('AddTenantHostEntryJob failed', [
                    'hostname' => $this->hostname,
                    'exit_code' => $process->getExitCode(),
                    'output' => $output,
                    'error' => $errorOutput,
                ]);
            } else {
                Log::info('AddTenantHostEntryJob succeeded', [
                    'hostname' => $this->hostname,
                    'output' => $output,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('AddTenantHostEntryJob exception', [
                'hostname' => $this->hostname,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
