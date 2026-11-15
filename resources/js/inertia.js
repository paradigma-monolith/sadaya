import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { initSentry } from './sentry';

const ensureAuthenticatedEcho = (pageProps) => {
    if (typeof window === 'undefined' || !pageProps?.auth?.user || window.Echo) {
        return;
    }
    import('./echo')
        .then(({ getEcho }) => {
            getEcho();
        })
        .catch(() => {});
};

createInertiaApp({
    title: (title) => {
        if (!title) return 'SADAYA — BKK Kelas I Bandung';
        return /SADAYA|BKK/i.test(title) ? title : `${title} — SADAYA BKK Bandung`;
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob([
                './Pages/**/*.vue',
                '!./Pages/**/tabs/**',
                '!./Pages/**/components/**',
            ]),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        initSentry(app);
        app.mount(el);

        ensureAuthenticatedEcho(props?.initialPage?.props);
        router.on('navigate', (event) => {
            ensureAuthenticatedEcho(event?.detail?.page?.props);
        });
    },
    progress: {
        color: '#00A99D',
        showSpinner: true,
    },
});
