import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
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
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    build: {
        // Enable CSS minification
        cssMinify: true,
        // Optimize chunk splitting for better caching
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['vue', 'vuetify'],
                },
            },
        },
        // Enable source maps for debugging (disable in production if not needed)
        sourcemap: false,
        // Minify output
        minify: 'esbuild',
    },
});
