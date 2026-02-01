import { ref, computed, onMounted, onUnmounted } from 'vue';

/**
 * Composable for mobile detection and responsive breakpoints
 * Extracts mobile detection logic for reuse across components
 */
export function useMobileDetection() {
    const isMobile = ref(window.innerWidth <= 768);
    const isTablet = ref(window.innerWidth <= 1024 && window.innerWidth > 768);
    const isDesktop = ref(window.innerWidth > 1024);
    const screenWidth = ref(window.innerWidth);
    const screenHeight = ref(window.innerHeight);

    // Breakpoint constants
    const BREAKPOINTS = {
        xs: 0,
        sm: 600,
        md: 960,
        lg: 1280,
        xl: 1920,
    };

    const currentBreakpoint = computed(() => {
        const width = screenWidth.value;
        if (width < BREAKPOINTS.sm) return 'xs';
        if (width < BREAKPOINTS.md) return 'sm';
        if (width < BREAKPOINTS.lg) return 'md';
        if (width < BREAKPOINTS.xl) return 'lg';
        return 'xl';
    });

    const handleResize = () => {
        screenWidth.value = window.innerWidth;
        screenHeight.value = window.innerHeight;
        isMobile.value = window.innerWidth <= 768;
        isTablet.value = window.innerWidth <= 1024 && window.innerWidth > 768;
        isDesktop.value = window.innerWidth > 1024;
    };

    // Debounced resize handler for performance
    let resizeTimeout = null;
    const debouncedResize = () => {
        if (resizeTimeout) clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleResize, 100);
    };

    onMounted(() => {
        window.addEventListener('resize', debouncedResize);
        handleResize(); // Initialize on mount
    });

    onUnmounted(() => {
        window.removeEventListener('resize', debouncedResize);
        if (resizeTimeout) clearTimeout(resizeTimeout);
    });

    // Helper functions
    const isBreakpointUp = (breakpoint) => {
        return screenWidth.value >= BREAKPOINTS[breakpoint];
    };

    const isBreakpointDown = (breakpoint) => {
        return screenWidth.value < BREAKPOINTS[breakpoint];
    };

    const isBreakpointBetween = (lower, upper) => {
        return screenWidth.value >= BREAKPOINTS[lower] && screenWidth.value < BREAKPOINTS[upper];
    };

    return {
        isMobile,
        isTablet,
        isDesktop,
        screenWidth,
        screenHeight,
        currentBreakpoint,
        BREAKPOINTS,
        isBreakpointUp,
        isBreakpointDown,
        isBreakpointBetween,
    };
}
