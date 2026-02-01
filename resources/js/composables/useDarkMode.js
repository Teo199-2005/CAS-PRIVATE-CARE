/**
 * CAS Private Care - Dark Mode Theme System
 * 
 * Comprehensive dark mode support with:
 * - System preference detection
 * - Manual toggle with persistence
 * - Smooth transitions
 * - WCAG AAA compliant contrast ratios
 */

import { ref, watch, computed, onMounted } from 'vue';

// Theme state
const isDarkMode = ref(false);
const themePreference = ref('system'); // 'system' | 'light' | 'dark'
const isInitialized = ref(false);

// Storage key
const STORAGE_KEY = 'cas-theme-preference';

/**
 * Initialize dark mode based on stored preference or system setting
 */
export function initDarkMode() {
    if (typeof window === 'undefined') return;
    
    // Get stored preference
    const stored = localStorage.getItem(STORAGE_KEY);
    
    if (stored && ['light', 'dark', 'system'].includes(stored)) {
        themePreference.value = stored;
    }
    
    // Apply initial theme
    applyTheme();
    
    // Listen for system preference changes
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', (e) => {
        if (themePreference.value === 'system') {
            isDarkMode.value = e.matches;
            updateDOM();
        }
    });
    
    isInitialized.value = true;
}

/**
 * Apply theme based on preference
 */
function applyTheme() {
    if (themePreference.value === 'system') {
        isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    } else {
        isDarkMode.value = themePreference.value === 'dark';
    }
    updateDOM();
}

/**
 * Update DOM with theme class
 */
function updateDOM() {
    const html = document.documentElement;
    
    // Add transition class for smooth switching
    html.classList.add('theme-transition');
    
    if (isDarkMode.value) {
        html.classList.add('dark');
        html.setAttribute('data-theme', 'dark');
    } else {
        html.classList.remove('dark');
        html.setAttribute('data-theme', 'light');
    }
    
    // Update meta theme-color
    const metaThemeColor = document.querySelector('meta[name="theme-color"]');
    if (metaThemeColor) {
        metaThemeColor.setAttribute('content', isDarkMode.value ? '#0f172a' : '#1e40af');
    }
    
    // Remove transition class after animation completes
    setTimeout(() => {
        html.classList.remove('theme-transition');
    }, 300);
}

/**
 * Set theme preference
 */
export function setThemePreference(preference) {
    if (!['light', 'dark', 'system'].includes(preference)) return;
    
    themePreference.value = preference;
    localStorage.setItem(STORAGE_KEY, preference);
    applyTheme();
}

/**
 * Toggle dark mode directly
 */
export function toggleDarkMode() {
    const newPreference = isDarkMode.value ? 'light' : 'dark';
    setThemePreference(newPreference);
}

/**
 * Composable for using dark mode in components
 */
export function useDarkMode() {
    onMounted(() => {
        if (!isInitialized.value) {
            initDarkMode();
        }
    });
    
    return {
        isDarkMode: computed(() => isDarkMode.value),
        themePreference: computed(() => themePreference.value),
        isInitialized: computed(() => isInitialized.value),
        setThemePreference,
        toggleDarkMode,
    };
}

/**
 * Get Vuetify theme object based on current mode
 */
export function getVuetifyTheme() {
    return {
        defaultTheme: isDarkMode.value ? 'dark' : 'light',
        themes: {
            light: {
                dark: false,
                colors: {
                    // Primary colors
                    primary: '#1e40af',
                    'primary-darken-1': '#1e3a8a',
                    'primary-lighten-1': '#3b82f6',
                    
                    // Secondary colors
                    secondary: '#6366f1',
                    'secondary-darken-1': '#4f46e5',
                    'secondary-lighten-1': '#818cf8',
                    
                    // Accent
                    accent: '#8b5cf6',
                    
                    // Status colors
                    success: '#10b981',
                    warning: '#f59e0b',
                    error: '#ef4444',
                    info: '#06b6d4',
                    
                    // Background colors
                    background: '#ffffff',
                    surface: '#ffffff',
                    'surface-variant': '#f8fafc',
                    
                    // Text colors - WCAG AAA compliant
                    'on-background': '#0f172a',
                    'on-surface': '#1e293b',
                    'on-primary': '#ffffff',
                    'on-secondary': '#ffffff',
                    
                    // Custom grays
                    'grey-lighten-5': '#fafafa',
                    'grey-lighten-4': '#f5f5f5',
                    'grey-lighten-3': '#eeeeee',
                    'grey-lighten-2': '#e0e0e0',
                    'grey-lighten-1': '#bdbdbd',
                    'grey': '#9e9e9e',
                    'grey-darken-1': '#757575',
                    'grey-darken-2': '#616161',
                    'grey-darken-3': '#424242',
                    'grey-darken-4': '#212121',
                }
            },
            dark: {
                dark: true,
                colors: {
                    // Primary colors (adjusted for dark mode)
                    primary: '#60a5fa',
                    'primary-darken-1': '#3b82f6',
                    'primary-lighten-1': '#93c5fd',
                    
                    // Secondary colors
                    secondary: '#a5b4fc',
                    'secondary-darken-1': '#818cf8',
                    'secondary-lighten-1': '#c7d2fe',
                    
                    // Accent
                    accent: '#c4b5fd',
                    
                    // Status colors (adjusted for dark backgrounds)
                    success: '#34d399',
                    warning: '#fbbf24',
                    error: '#f87171',
                    info: '#22d3ee',
                    
                    // Background colors
                    background: '#0f172a',
                    surface: '#1e293b',
                    'surface-variant': '#334155',
                    
                    // Text colors - WCAG AAA compliant on dark
                    'on-background': '#f1f5f9',
                    'on-surface': '#e2e8f0',
                    'on-primary': '#0f172a',
                    'on-secondary': '#0f172a',
                    
                    // Custom grays for dark mode
                    'grey-lighten-5': '#334155',
                    'grey-lighten-4': '#475569',
                    'grey-lighten-3': '#64748b',
                    'grey-lighten-2': '#94a3b8',
                    'grey-lighten-1': '#cbd5e1',
                    'grey': '#e2e8f0',
                    'grey-darken-1': '#f1f5f9',
                    'grey-darken-2': '#f8fafc',
                    'grey-darken-3': '#ffffff',
                    'grey-darken-4': '#ffffff',
                }
            }
        }
    };
}

export default {
    isDarkMode,
    themePreference,
    isInitialized,
    initDarkMode,
    setThemePreference,
    toggleDarkMode,
    useDarkMode,
    getVuetifyTheme,
};
