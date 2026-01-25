/**
 * ========================================================================
 * CAS Private Care - Composables Index
 * ========================================================================
 * 
 * Central export point for all Vue composables.
 * Import composables from this file for cleaner imports.
 * 
 * @example
 * import { usePayments, useBookings, useUserManagement } from '@/composables';
 * 
 * @version 1.0.0
 * @since 2026-01-25
 */

// Admin Dashboard composables
export { usePayments } from './usePayments';
export { useUserManagement } from './useUserManagement';
export { useBookings } from './useBookings';

// Accessibility composables
export { useFocusTrap, vFocusTrap } from './useFocusTrap';

// Add more composables here as they are created
// export { useNotifications } from './useNotifications';
// export { useReports } from './useReports';
// export { useAnnouncements } from './useAnnouncements';
