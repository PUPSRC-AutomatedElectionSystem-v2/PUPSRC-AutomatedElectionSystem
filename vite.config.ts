import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { execSync } from 'child_process';
import path from 'path';
import compression from 'vite-plugin-compression';
import fs from 'fs';

const viteHost = (() => {
    try {
        const url = new URL(process.env.APP_URL ?? 'https://pupsrc-aes.test');
        return url.hostname;
    } catch {
        return 'pupsrc-aes.test';
    }
})();

const domain = viteHost.split('.').slice(-2).join('.');
console.log(domain);

export default defineConfig({
    server: {
        https: {
            // adjust these paths to wherever Herd is storing your .key/.crt
            key: fs.readFileSync(path.resolve(process.env.USERPROFILE!, '.config', 'herd', 'config', 'valet', 'Certificates', 'pupsrc-aes.test.key')),
            cert: fs.readFileSync(path.resolve(process.env.USERPROFILE!, '.config', 'herd', 'config', 'valet', 'Certificates', 'pupsrc-aes.test.crt')),
        },
        port: 5173,
        // ensure Access-Control-Allow-Origin matches https://pupsrc-aes.test
        cors: {
            origin: new RegExp(`^https?://(.*\\.)?${domain.replace('.', '\\.')}$`),
            credentials: true,
        },
        host: viteHost,          // listen on all interfaces
        hmr: {
            protocol: 'wss',
            host: viteHost, // derived from APP_URL (pupsrc-aes.test)
            clientPort: 5173,
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/js/app.ts',
                'app-modules/tenant-auth/resources/js/app.ts',
                'app-modules/organization-admin/resources/js/app.ts',
                'app-modules/organization-voting/resources/js/app.ts'
            ],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        // Core app Wayfinder outputs (resources/js)
        wayfinder({
            formVariants: true,
            // default path is resources/js, leaving implicit
        }),
        // Generate Wayfinder outputs for each module as well
        wayfinder({
            formVariants: true,
            path: 'app-modules/tenant-auth/resources/js',
        }),
        wayfinder({
            formVariants: true,
            path: 'app-modules/organization-admin/resources/js',
        }),
        wayfinder({
            formVariants: true,
            path: 'app-modules/organization-voting/resources/js',
        }),
        // Run a small post-generation dedupe to remove duplicate named exports
        // produced by Wayfinder when multiple hosts/roots exist. This keeps
        // vendor files unchanged and cleans the generated files in resources.
        {
            name: 'wayfinder-dedupe',
            enforce: 'post',
            buildStart() {
                try {
                    const script = path.resolve(__dirname, 'resources', 'js', 'buildtime', 'wayfinder', 'dedupe-wayfinder.cjs');
                    execSync(`node "${script}"`, { stdio: 'inherit' });
                } catch (e) {
                    // don't fail the build if dedupe script has issues
                    const err: any = e;
                    console.warn('wayfinder-dedupe failed:', err && err.message ? err.message : err);
                }
            },
        },
        compression({ algorithm: 'brotliCompress', ext: '.br' }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});