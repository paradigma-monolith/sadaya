    import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/public.css',
                'resources/js/inertia.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    build: {
        chunkSizeWarningLimit: 1200,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) return;
                    if (id.includes('echarts') || id.includes('zrender')) return 'vendor-echarts';
                    if (id.includes('maplibre-gl')) return 'vendor-maplibre';
                    if (id.includes('lucide-vue-next')) return 'vendor-icons';
                    if (id.includes('reka-ui') || id.includes('vue-sonner')) return 'vendor-ui';
                    if (id.includes('laravel-echo') || id.includes('pusher-js')) return 'vendor-realtime';
                },
            },
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
