/// <reference types="vite/client" />

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import axios from 'axios';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
// initializeTheme();

/*
 |--------------------------------------------------------------------------
 | Axios defaults
 |--------------------------------------------------------------------------
 |
 | Inertia doesn't replace axios; if you use axios in your frontend code
 | you can set global defaults here. There's no `withXSRFToken` option on
 | axios — instead use `withCredentials` and configure the XSRF cookie/header
 | names. Laravel expects the XSRF token in the `X-XSRF-TOKEN` header and
 | ships the `XSRF-TOKEN` cookie by default when using the `web` middleware.
 */

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// axios will automatically read the XSRF token from a cookie named `XSRF-TOKEN`
// and send it in the `X-XSRF-TOKEN` header. If your app uses different names,
// you can set them explicitly:
axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';