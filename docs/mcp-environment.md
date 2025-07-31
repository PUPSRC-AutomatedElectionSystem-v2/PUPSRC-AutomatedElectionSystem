MCP / local environment notes
=================================

This project includes small helper scripts that detect the OS and run appropriate commands so maintainers and CI (or MCP) can reliably perform common development tasks across platforms.

Why
---

Some shell commands commonly used on POSIX systems (for example `rm -rf`) are not understood by PowerShell on Windows. To avoid surprising errors when running cleanup tasks, we detect the OS and run the correct command.

Scripts
-------

- `scripts/clean-node-modules.cjs` — cross-platform script that deletes `node_modules` and `package-lock.json`. It detects `process.platform === 'win32'` and uses PowerShell's `Remove-Item` on Windows, otherwise it invokes `rm -rf`.
 `resources/js/buildtime/wayfinder/dedupe-wayfinder.cjs` — dedupe script for Wayfinder-generated TypeScript files (keeps vendor code untouched).

Usage
 node scripts/clean-node-modules.cjs
 node resources/js/buildtime/wayfinder/dedupe-wayfinder.cjs
From the project root run:

```powershell
node scripts/clean-node-modules.cjs
node scripts/dedupe-wayfinder.cjs
npm install
npm run build
```

CI / MCP
--------

If you need MCP or CI to run these scripts, ensure your task runner executes Node and calls the script directly (for example `node scripts/clean-node-modules.cjs`). Avoid relying on shell-specific commands in CI job steps.
