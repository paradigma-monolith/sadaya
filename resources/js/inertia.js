import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { initSentry } from './sentry';
import './echo';

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
    },
    progress: {
        color: '#00A99D',
        showSpinner: true,
    },
});
