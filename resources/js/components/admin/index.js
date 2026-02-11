/**
 * Admin Dashboard Sub-Components
 * ==============================
 * 
 * These components are extracted from the main AdminDashboard.vue
 * to improve maintainability and enable lazy loading for better performance.
 * 
 * Architecture Goals:
 * - Each component is self-contained with its own state, filters, and UI
 * - Components communicate with parent via events (emit)
 * - Props are used for data injection (users, caregivers, etc.)
 * - All components support mobile responsive views
 * - WCAG 2.1 AA accessibility compliance
 * 
 * Usage in AdminDashboard.vue:
 * 
 * import { 
 *   AdminCaregiversManagement,
 *   AdminBookingsManagement,
 *   AdminUsersManagement,
 *   AdminClientsManagement,
 *   AdminHousekeepersManagement,
 *   AdminPendingApplications,
 *   AdminDashboardOverview,
 *   AdminTimeTrackingSection,
 *   AdminReviewsSection
 * } from '@/components/admin';
 * 
 * Or for lazy loading (recommended for code splitting):
 * 
 * const AdminDashboardOverview = defineAsyncComponent(() => 
 *   import('@/components/admin/AdminDashboardOverview.vue')
 * );
 * 
 * @version 2.0.0
 * @date January 28, 2026
 */

// =============================================================================
// Named Exports - Lazy-loadable components for better code splitting
// =============================================================================

// Core Management Components
export { default as AdminCaregiversManagement } from './AdminCaregiversManagement.vue';
export { default as AdminBookingsManagement } from './AdminBookingsManagement.vue';
export { default as AdminUsersManagement } from './AdminUsersManagement.vue';
export { default as AdminClientsManagement } from './AdminClientsManagement.vue';
export { default as AdminHousekeepersManagement } from './AdminHousekeepersManagement.vue';
export { default as AdminMarketingStaffManagement } from './AdminMarketingStaffManagement.vue';
export { default as AdminStaffManagement } from './AdminStaffManagement.vue';
export { default as AdminTrainingCentersManagement } from './AdminTrainingCentersManagement.vue';

// Dashboard & Overview Components
export { default as AdminPendingApplications } from './AdminPendingApplications.vue';
export { default as AdminDashboardOverview } from './AdminDashboardOverview.vue';
export { default as AdminAnalyticsSection } from './AdminAnalyticsSection.vue';

// Feature-Specific Components
export { default as AdminTimeTrackingSection } from './AdminTimeTrackingSection.vue';
export { default as AdminReviewsSection } from './AdminReviewsSection.vue';
export { default as CaregiverRatingManager } from './CaregiverRatingManager.vue';
export { default as EmailMarketingPanel } from './EmailMarketingPanel.vue';

// Payments Section (with sub-components lazy loaded internally)
export { default as AdminPaymentsSection } from './AdminPaymentsSection.vue';

// Additional Admin Sections
export { default as AdminPasswordResets } from './AdminPasswordResets.vue';
export { default as AdminAnnouncements } from './AdminAnnouncements.vue';
export { default as AdminProfile } from './AdminProfile.vue';

// =============================================================================
// Default Export - All components as lazy loaders for dynamic imports
// =============================================================================
export default {
  // Core Management
  AdminCaregiversManagement: () => import('./AdminCaregiversManagement.vue'),
  AdminBookingsManagement: () => import('./AdminBookingsManagement.vue'),
  AdminUsersManagement: () => import('./AdminUsersManagement.vue'),
  AdminClientsManagement: () => import('./AdminClientsManagement.vue'),
  AdminHousekeepersManagement: () => import('./AdminHousekeepersManagement.vue'),
  AdminMarketingStaffManagement: () => import('./AdminMarketingStaffManagement.vue'),
  AdminStaffManagement: () => import('./AdminStaffManagement.vue'),
  AdminTrainingCentersManagement: () => import('./AdminTrainingCentersManagement.vue'),
  
  // Dashboard & Overview
  AdminPendingApplications: () => import('./AdminPendingApplications.vue'),
  AdminDashboardOverview: () => import('./AdminDashboardOverview.vue'),
  AdminAnalyticsSection: () => import('./AdminAnalyticsSection.vue'),
  
  // Feature-Specific
  AdminTimeTrackingSection: () => import('./AdminTimeTrackingSection.vue'),
  AdminReviewsSection: () => import('./AdminReviewsSection.vue'),
  CaregiverRatingManager: () => import('./CaregiverRatingManager.vue'),
  EmailMarketingPanel: () => import('./EmailMarketingPanel.vue'),
  
  // Payments Section (comprehensive component with sub-components)
  AdminPaymentsSection: () => import('./AdminPaymentsSection.vue'),
  
  // Additional Admin Sections
  AdminPasswordResets: () => import('./AdminPasswordResets.vue'),
  AdminAnnouncements: () => import('./AdminAnnouncements.vue'),
  AdminProfile: () => import('./AdminProfile.vue'),
  
  // New Modular Tab Components (100-score implementation)
  AdminUsersTab: () => import('./AdminUsersTab.vue'),
  AdminBookingsTab: () => import('./AdminBookingsTab.vue'),
  AdminPayoutsTab: () => import('./AdminPayoutsTab.vue'),
  AdminReportsTab: () => import('./AdminReportsTab.vue'),
};
