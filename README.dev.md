Developer notes
================

Post-generation dedupe
----------------------

This project uses Laravel Wayfinder to generate TypeScript definitions for routes and controller actions into `resources/js`. In some development environments Wayfinder may generate the same named route multiple times with different host variants (for example `//localhost`, `//127.0.0.1`, or `//my-site.test`). Multiple `export const <name>` declarations in the same TypeScript file cause ESBuild/TypeScript to fail the build with "Multiple exports with the same name" errors.

To avoid editing vendor files, we use a small post-generation dedupe script that runs automatically during the Vite build. The script removes duplicate `export const <name>` declarations (keeps the first occurrence) as well as duplicate helper declarations (e.g. `const loginForm`).

Files:

 - `resources/js/buildtime/wayfinder/dedupe-wayfinder.cjs` — CommonJS script that deduplicates generated `.ts` files under `resources/js/routes`.
- `vite.config.ts` — runs the dedupe script as a `post` Vite plugin named `wayfinder-dedupe`.

Manual workflow:

1. Regenerate types (Wayfinder):

```powershell
php artisan wayfinder:generate
```

2. Run dedupe (optional — build will also run it automatically):

```powershell
node resources/js/buildtime/wayfinder/dedupe-wayfinder.cjs
```

3. Build frontend assets:

```powershell
npm run build
```

Cross-platform cleanup (node_modules + lockfile)
-----------------------------------------------

On POSIX systems people often run `rm -rf node_modules package-lock.json`. On Windows PowerShell that exact command fails. Use the provided cross-platform helper instead.

1. Run the helper (node must be installed):

```powershell
node scripts/clean-node-modules.cjs
```

2. Reinstall and build:

```powershell
npm install
npm run build
```

If you prefer direct PowerShell commands:

```powershell
Remove-Item -Recurse -Force node_modules
Remove-Item -Force package-lock.json
```

Notes for contributors
----------------------
- This post-generation script is a local workaround. If you maintain Wayfinder please consider upstreaming a dedupe option or configuration to control host variant generation.
