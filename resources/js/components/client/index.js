/**
 * Client Dashboard Sub-Components
 * 
 * These components are extracted from the main ClientDashboard.vue
 * to improve maintainability and enable lazy loading for better performance.
 * 
 * Usage in ClientDashboard.vue:
 * 
 * import { 
 *   ClientBookingForm,
 *   ClientMyBookings,
 *   ClientProfile,
 *   ClientPriceSummary,
 *   ClientDashboardStats,
 *   ClientAnalytics,
 *   ClientPaymentSection
 * } from '@/components/client';
 * 
 * Or for lazy loading:
 * 
 * const ClientBookingForm = defineAsyncComponent(() => 
 *   import('@/components/client/ClientBookingForm.vue')
 * );
 * 
 * @version 2.0.0
 * @date January 28, 2026
 */

// Eager exports for frequently used components
export { default as ClientPriceSummary } from './ClientPriceSummary.vue';
export { default as ClientDashboardStats } from './ClientDashboardStats.vue';

// Lazy-loadable components for better code splitting
export { default as ClientBookingForm } from './ClientBookingForm.vue';
export { default as ClientMyBookings } from './ClientMyBookings.vue';
export { default as ClientProfile } from './ClientProfile.vue';
export { default as ClientAnalytics } from './ClientAnalytics.vue';
export { default as ClientPaymentSection } from './ClientPaymentSection.vue';

// Default export with all components as lazy loaders
export default {
  ClientBookingForm: () => import('./ClientBookingForm.vue'),
  ClientMyBookings: () => import('./ClientMyBookings.vue'),
  ClientProfile: () => import('./ClientProfile.vue'),
  ClientPriceSummary: () => import('./ClientPriceSummary.vue'),
  ClientDashboardStats: () => import('./ClientDashboardStats.vue'),
  ClientAnalytics: () => import('./ClientAnalytics.vue'),
  ClientPaymentSection: () => import('./ClientPaymentSection.vue'),
};
