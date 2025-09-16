param(
    [Parameter(Mandatory=$true)]
    [string]$Host,

    [Parameter(Mandatory=$false)]
    [string]$IP = "127.0.0.1"
)

# Ensure script is running as Administrator
function Ensure-Admin() {
    $currentIdentity = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($currentIdentity)
    if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
        Write-Host "Elevating privileges..."
        $psi = New-Object System.Diagnostics.ProcessStartInfo
        $psi.FileName = "powershell.exe"
        $psi.Arguments = "-NoProfile -ExecutionPolicy Bypass -File `"$PSCommandPath`" -Host `"$Host`" -IP `"$IP`""
        $psi.Verb = "runas"
        try {
            $p = [System.Diagnostics.Process]::Start($psi)
            $p.WaitForExit()
            exit $p.ExitCode
        } catch {
            Write-Error "Elevation cancelled or failed."
            exit 1
        }
    }
}

Ensure-Admin

$hostsPath = "$env:SystemRoot\System32\drivers\etc\hosts"
$line = "$IP $Host"

if (-not (Test-Path $hostsPath)) {
    Write-Error "Hosts file not found at $hostsPath"
    exit 1
}

# Check if entry already exists
$existing = Select-String -Path $hostsPath -Pattern "\b$([Regex]::Escape($Host))\b" -SimpleMatch -ErrorAction SilentlyContinue
if ($existing) {
    Write-Host "Hosts entry already exists for $Host"
    exit 0
}

Add-Content -Path $hostsPath -Value $line
Write-Host "Added: $line"
