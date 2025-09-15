<?php

declare(strict_types=1);

namespace App\Services\CentralServices\Tenants;

use RuntimeException;

class WindowsHostRegistrar implements HostRegistrarInterface
{
    private string $hostsPath;

    public function __construct(string $hostsPath = '')
    {
        $this->hostsPath = $hostsPath ?: (getenv('WINDIR') ?: 'C:\\Windows') . '\\System32\\drivers\\etc\\hosts';
    }

    public function register(string $host, string $ip = '127.0.0.1'): void
    {
        $contents = file_exists($this->hostsPath)
            ? file($this->hostsPath, FILE_IGNORE_NEW_LINES)
            : [];

        $startMarker = '# Herd generated Hosts. Do not change.';
        $endMarker = '# End Herd generated Hosts';

        // If host already present anywhere, do nothing
        foreach ($contents as $line) {
            if (stripos($line, $host) !== false) {
                return;
            }
        }

        // Ensure markers exist; if not, append a new block at the end
        $startIndex = null;
        $endIndex = null;
        foreach ($contents as $i => $line) {
            if (trim($line) === $startMarker) {
                $startIndex = $i;
            }
            if (trim($line) === $endMarker) {
                $endIndex = $i;
                break;
            }
        }

        if ($startIndex === null || $endIndex === null) {
            // create new block at end
            $contents[] = $startMarker;
            $contents[] = $ip . "\t" . $host;
            $contents[] = $endMarker;
        } else {
            // insert before end marker
            $newContents = [];
            for ($i = 0; $i < count($contents); $i++) {
                if ($i === $endIndex) {
                    $newContents[] = $ip . "\t" . $host;
                }
                $newContents[] = $contents[$i];
            }

            $contents = $newContents;
        }

        $this->atomicReplace($this->hostsPath, implode(PHP_EOL, $contents) . PHP_EOL);
    }

    public function unregister(string $host): void
    {
        if (! file_exists($this->hostsPath)) {
            return;
        }

        $contents = file($this->hostsPath, FILE_IGNORE_NEW_LINES);
        $startMarker = '# Herd generated Hosts. Do not change.';
        $endMarker = '# End Herd generated Hosts';

        $inBlock = false;
        $new = [];

        foreach ($contents as $line) {
            $trim = trim($line);
            if ($trim === $startMarker) {
                $inBlock = true;
                $new[] = $line; // keep start marker
                continue;
            }

            if ($trim === $endMarker) {
                $inBlock = false;
                $new[] = $line; // keep end marker
                continue;
            }

            if ($inBlock) {
                // skip lines inside block that match host
                if (stripos($line, $host) !== false) {
                    continue;
                }
                $new[] = $line;
            } else {
                $new[] = $line;
            }
        }

        $this->atomicReplace($this->hostsPath, implode(PHP_EOL, $new) . PHP_EOL);
    }

    public function exists(string $host): bool
    {
        if (! file_exists($this->hostsPath)) {
            return false;
        }

        $contents = file_get_contents($this->hostsPath);
        // Prefer checking within Herd block if present
        $startMarker = '# Herd generated Hosts. Do not change.';
        $endMarker = '# End Herd generated Hosts';

        $startPos = stripos($contents, $startMarker);
        $endPos = stripos($contents, $endMarker);

        if ($startPos !== false && $endPos !== false && $endPos > $startPos) {
            $block = substr($contents, $startPos, $endPos - $startPos + strlen($endMarker));
            return stripos($block, $host) !== false;
        }

        return stripos($contents, $host) !== false;
    }

    private function atomicAppend(string $path, string $data): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'hosts');
        if ($tmp === false) {
            throw new RuntimeException('Unable to create temp file');
        }

        if (file_put_contents($tmp, $data, FILE_APPEND) === false) {
            unlink($tmp);
            throw new RuntimeException('Unable to write to temp file');
        }

        $handle = @fopen($this->hostsPath, 'a');
        if (! $handle) {
            // Attempt to leave a temp file for manual installation and raise clear error
            $psCmd = 'Copy-Item -Path ' . escapeshellarg($tmp) . ' -Destination ' . escapeshellarg($this->hostsPath) . ' -Force';
            $manualCmd = 'Start-Process powershell -Verb runAs -ArgumentList ' . escapeshellarg('-NoProfile -Command "' . $psCmd . '"');
            throw new RuntimeException("Unable to open hosts file for append (permission denied). A temp file with the intended content was written to: {$tmp}\nRun the following in an elevated PowerShell to install it:\n\n{$manualCmd}");
        }

        // Acquire exclusive lock while appending
        if (! flock($handle, LOCK_EX)) {
            fclose($handle);
            unlink($tmp);
            throw new RuntimeException('Unable to lock hosts file');
        }

        if (fwrite($handle, $data) === false) {
            flock($handle, LOCK_UN);
            fclose($handle);
            unlink($tmp);
            throw new RuntimeException('Unable to append to hosts file');
        }

        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
        // remove temp file after successful append
        @unlink($tmp);
    }

    private function atomicReplace(string $path, string $data): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'hosts');
        if ($tmp === false) {
            throw new RuntimeException('Unable to create temp file');
        }

        if (file_put_contents($tmp, $data) === false) {
            @unlink($tmp);
            throw new RuntimeException('Unable to write to temp file');
        }

        // Try atomic rename first
        if (@rename($tmp, $this->hostsPath)) {
            return;
        }

        // If rename failed, try copy fallback
        if (@copy($tmp, $this->hostsPath)) {
            @unlink($tmp);
            return;
        }

        // Both rename and copy failed => likely permission denied.
        // Keep the temp file for manual installation and give an elevation command.
        $psCmd = 'Copy-Item -Path ' . escapeshellarg($tmp) . ' -Destination ' . escapeshellarg($this->hostsPath) . ' -Force; Write-Host "Hosts updated"';
        $manualCmd = 'Start-Process powershell -Verb runAs -ArgumentList ' . escapeshellarg('-NoProfile -Command "' . $psCmd . '"');

        // Do not unlink $tmp so user can run the manual command.
        throw new RuntimeException(
            "Unable to replace hosts file due to permission errors. " .
                "A temporary file containing the new hosts content was created at: {$tmp}\n\n" .
                "Run the following command in an elevated PowerShell to install it:\n\n{$manualCmd}\n\n" .
                "Or run your PHP/Artisan process as Administrator or use Herd/wildcard DNS to avoid editing the hosts file from the web process."
        );
    }
}
