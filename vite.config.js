import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/common.css', 'resources/js/app.js', 'resources/js/app-complex.js'],
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
            // Use full build with template compiler for runtime template compilation
            // Required because blade templates use inline Vue component tags like <admin-dashboard>
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    build: {
        // Enable CSS minification
        cssMinify: true,
        // Target modern browsers for smaller bundle size
        target: 'es2020',
        // Optimize chunk splitting for better caching
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Separate vendor chunks for better caching
                    if (id.includes('node_modules')) {
                        if (id.includes('vue') || id.includes('@vue')) {
                            return 'vendor-vue';
                        }
                        if (id.includes('vuetify') || id.includes('@mdi')) {
                            return 'vendor-vuetify';
                        }
                        if (id.includes('chart.js')) {
                            return 'vendor-charts';
                        }
                        if (id.includes('axios')) {
                            return 'vendor-axios';
                        }
                        return 'vendor';
                    }
                    // Separate dashboard components for lazy loading
                    if (id.includes('/components/') && id.includes('Dashboard')) {
                        return 'dashboards';
                    }
                },
                // Optimize asset file names for caching
                chunkFileNames: 'assets/js/[name]-[hash].js',
                entryFileNames: 'assets/js/[name]-[hash].js',
                assetFileNames: 'assets/[ext]/[name]-[hash].[ext]',
            },
        },
        // Disable source maps in production for smaller bundle
        sourcemap: false,
        // Minify output with esbuild (faster than terser)
        minify: 'esbuild',
        // Set chunk size warning limit
        chunkSizeWarningLimit: 500,
        // Optimize assets
        assetsInlineLimit: 4096, // Inline assets < 4kb as base64
    },
});
