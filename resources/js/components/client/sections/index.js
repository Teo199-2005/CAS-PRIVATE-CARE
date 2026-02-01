/**
 * Client Dashboard Sections Index
 * 
 * This file exports all lazy-loadable section components for the ClientDashboard.
 * Using dynamic imports enables code splitting and reduces initial bundle size.
 * 
 * Usage:
 * ```js
 * import { ClientBookingsSection, ClientAnalyticsSection } from './client/sections';
 * 
 * // Or for lazy loading:
 * const ClientBookingsSection = defineAsyncComponent(() => 
 *   import('./client/sections/ClientBookingsSection.vue')
 * );
 * ```
 */

// Direct exports for regular imports
export { default as ClientBookingsSection } from './ClientBookingsSection.vue';
export { default as ClientAnalyticsSection } from './ClientAnalyticsSection.vue';
export { default as BookingCard } from './BookingCard.vue';

// Lazy load factory functions for code splitting
export const lazyLoadClientBookings = () => import('./ClientBookingsSection.vue');
export const lazyLoadClientAnalytics = () => import('./ClientAnalyticsSection.vue');

// Section configuration for dynamic rendering
export const clientSections = {
  'my-bookings': {
    component: () => import('./ClientBookingsSection.vue'),
    name: 'ClientBookingsSection',
    title: 'My Bookings',
    icon: 'mdi-calendar-check',
    preload: true // Preload since it's commonly accessed
  },
  'analytics': {
    component: () => import('./ClientAnalyticsSection.vue'),
    name: 'ClientAnalyticsSection',
    title: 'Analytics',
    icon: 'mdi-chart-line',
    preload: false
  }
};

/**
 * Preload critical sections after initial page load
 * Call this in the main ClientDashboard onMounted hook
 */
export function preloadCriticalSections() {
  // Use requestIdleCallback for non-blocking preload
  const preload = () => {
    Object.values(clientSections)
      .filter(section => section.preload)
      .forEach(section => {
        section.component();
      });
  };

  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(preload);
  } else {
    setTimeout(preload, 2000);
  }
}
