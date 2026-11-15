import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { toast } from 'vue-sonner';
import { initSentry } from './sentry';

const CHUNK_RELOAD_KEY = 'sadaya:chunk_reload_at';

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

if (typeof window !== 'undefined') {
    // Auto-recover once when a new Vite deployment invalidates stale dynamic chunk hashes.
    window.addEventListener('vite:preloadError', () => {
        try {
            const lastReload = Number(window.sessionStorage.getItem(CHUNK_RELOAD_KEY) || 0);
            if (Date.now() - lastReload > 15000) {
                window.sessionStorage.setItem(CHUNK_RELOAD_KEY, String(Date.now()));
                window.location.reload();
            }
        } catch {
            window.location.reload();
        }
    });

    if (import.meta.env.PROD && 'serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }
}

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

        let sentryClient = null;
        initSentry(app)
            .then((client) => {
                sentryClient = client;
            })
            .catch(() => {});

        app.config.errorHandler = (err, _instance, info) => {
            if (import.meta.env.DEV) {
                console.error(`[Vue ErrorHandler] (${info})`, err);
            }
            sentryClient?.captureException?.(err);
            toast.error('Terjadi kendala pada tampilan antarmuka. Muat ulang halaman bila diperlukan.');
        };

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
