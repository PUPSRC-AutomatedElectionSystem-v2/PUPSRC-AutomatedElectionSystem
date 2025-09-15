<?php

declare(strict_types=1);

namespace App\Services\CentralServices\Tenants;

use RuntimeException;

class LinuxHostRegistrar implements HostRegistrarInterface
{
    private string $hostsPath;

    public function __construct(string $hostsPath = '/etc/hosts')
    {
        $this->hostsPath = $hostsPath;
    }

    public function register(string $host, string $ip = '127.0.0.1'): void
    {
        if ($this->exists($host)) {
            return;
        }

        $line = $ip . "\t" . $host . PHP_EOL;
        $this->atomicAppend($this->hostsPath, $line);
    }

    public function unregister(string $host): void
    {
        if (! file_exists($this->hostsPath)) {
            return;
        }

        $contents = file($this->hostsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $filtered = array_filter($contents, function ($line) use ($host) {
            return stripos($line, $host) === false;
        });

        $this->atomicReplace($this->hostsPath, implode(PHP_EOL, $filtered) . PHP_EOL);
    }

    public function exists(string $host): bool
    {
        if (! file_exists($this->hostsPath)) {
            return false;
        }

        $contents = file_get_contents($this->hostsPath);
        return stripos($contents, $host) !== false;
    }

    private function atomicAppend(string $path, string $data): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'hosts');
        if ($tmp === false) {
            throw new RuntimeException('Unable to create temp file');
        }

        if (file_put_contents($tmp, $data, FILE_APPEND) === false) {
            throw new RuntimeException('Unable to write to temp file');
        }

        if (! @rename($tmp, $this->hostsPath)) {
            // Attempt sudo copy on systems where process is not root (best-effort)
            $cmd = sprintf('sudo cp %s %s', escapeshellarg($tmp), escapeshellarg($this->hostsPath));
            exec($cmd, $output, $exit);
            unlink($tmp);
            if ($exit !== 0) {
                throw new RuntimeException('Unable to append to hosts file; permission denied');
            }
        }
    }

    private function atomicReplace(string $path, string $data): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'hosts');
        if ($tmp === false) {
            throw new RuntimeException('Unable to create temp file');
        }

        if (file_put_contents($tmp, $data) === false) {
            throw new RuntimeException('Unable to write to temp file');
        }

        if (! @rename($tmp, $this->hostsPath)) {
            $cmd = sprintf('sudo cp %s %s', escapeshellarg($tmp), escapeshellarg($this->hostsPath));
            exec($cmd, $output, $exit);
            unlink($tmp);
            if ($exit !== 0) {
                throw new RuntimeException('Unable to replace hosts file; permission denied');
            }
        }
    }
}
