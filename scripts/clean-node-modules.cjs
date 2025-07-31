const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');

function isWindows() {
    return process.platform === 'win32';
}

function runPowerShellRemove() {
    // Use PowerShell's Remove-Item to safely delete with admin-friendly behaviour
    const script = `Remove-Item -Recurse -Force node_modules; if (Test-Path package-lock.json) { Remove-Item -Force package-lock.json }`;
    const res = spawnSync('powershell.exe', ['-NoProfile', '-NonInteractive', '-Command', script], { stdio: 'inherit' });
    return res.status === 0;
}

function runPosixRemove() {
    const res = spawnSync('rm', ['-rf', 'node_modules', 'package-lock.json'], { stdio: 'inherit' });
    return res.status === 0;
}

function main() {
    console.log('[clean-node-modules] running cross-platform cleanup');
    if (isWindows()) {
        console.log('[clean-node-modules] detected Windows; using PowerShell Remove-Item');
        if (!runPowerShellRemove()) {
            console.error('[clean-node-modules] PowerShell cleanup failed');
            process.exit(1);
        }
    } else {
        console.log('[clean-node-modules] detected POSIX; running rm -rf');
        if (!runPosixRemove()) {
            console.error('[clean-node-modules] rm cleanup failed');
            process.exit(1);
        }
    }

    console.log('[clean-node-modules] done');
}

if (require.main === module) {
    main();
}

module.exports = { main };
