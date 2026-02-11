import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import vuetify from 'vite-plugin-vuetify';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/common.css', 'resources/css/mobile-fixes.css', 'resources/js/app.js', 'resources/js/app-complex.js'],
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
        // Vuetify plugin for automatic component tree-shaking
        vuetify({
            autoImport: true
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
                        if (id.includes('@stripe')) {
                            return 'vendor-stripe';
                        }
                        if (id.includes('lodash')) {
                            return 'vendor-lodash';
                        }
                        if (id.includes('date-fns') || id.includes('moment')) {
                            return 'vendor-dates';
                        }
                        return 'vendor';
                    }
                    
                    // PERFORMANCE FIX: Route-based code splitting for dashboards
                    // Each dashboard type gets its own chunk for better initial load
                    if (id.includes('AdminDashboard.vue')) {
                        return 'chunk-admin';
                    }
                    if (id.includes('ClientDashboard.vue')) {
                        return 'chunk-client';
                    }
                    if (id.includes('CaregiverDashboard.vue')) {
                        return 'chunk-caregiver';
                    }
                    if (id.includes('HousekeeperDashboard.vue')) {
                        return 'chunk-housekeeper';
                    }
                    if (id.includes('MarketingDashboard.vue')) {
                        return 'chunk-marketing';
                    }
                    if (id.includes('TrainingDashboard.vue')) {
                        return 'chunk-training';
                    }
                    
                    // Separate client sub-components for lazy loading
                    if (id.includes('/components/client/')) {
                        return 'client-components';
                    }
                    // Separate admin sub-components for lazy loading
                    if (id.includes('/components/admin/')) {
                        return 'admin-components';
                    }
                    // Separate shared components
                    if (id.includes('/components/shared/')) {
                        return 'shared-components';
                    }
                    // Separate dashboard components for lazy loading
                    if (id.includes('/components/') && id.includes('Dashboard')) {
                        const match = id.match(/([A-Za-z]+Dashboard)/);
                        if (match) {
                            return `dashboard-${match[1].toLowerCase()}`;
                        }
                        return 'dashboards';
                    }
                    // Payment components
                    if (id.includes('/components/') && (id.includes('Payment') || id.includes('Stripe'))) {
                        return 'payment-components';
                    }
                    // Composables and utilities
                    if (id.includes('/composables/')) {
                        return 'composables';
                    }
                },
                // Optimize asset file names for caching
                chunkFileNames: 'assets/js/[name]-[hash].js',
                entryFileNames: 'assets/js/[name]-[hash].js',
                assetFileNames: 'assets/[ext]/[name]-[hash].[ext]',
            },
            // Treeshake for smaller bundles
            treeshake: {
                moduleSideEffects: 'no-external',
                propertyReadSideEffects: false,
            },
        },
        // Disable source maps in production for smaller bundle
        sourcemap: false,
        // Minify output with esbuild (faster than terser)
        minify: 'esbuild',
        // Set chunk size warning limit (vendor-vue will exceed this, that's OK)
        chunkSizeWarningLimit: 700,
        // Optimize assets
        assetsInlineLimit: 4096, // Inline assets < 4kb as base64
    },
});
