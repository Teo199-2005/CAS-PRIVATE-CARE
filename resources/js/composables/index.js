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

// Utility composables
export { useMobileDetection } from './useMobileDetection';
export { useDataTable } from './useDataTable';
export { useApi, useResourceApi } from './useApi';
export { useForm, validators } from './useForm';
export { useNotifications } from './useNotifications';

// NY ZIP Code validation composable
export { 
    useNYZipCode,
    isValidNYZip,
    isValidZipFormat,
    getBaseZip,
    getNYRegion,
    validateNYZip
} from './useNYZipCode';

// Performance composables
export { 
    useDebouncedValue,
    useThrottle,
    useLazyLoad,
    useWebVitals,
    useRenderTime,
    useScrollPosition,
    useWindowSize
} from './usePerformance';

// CSRF and Authentication composables
export { getCsrfToken, getAuthHeaders, authFetch, refreshToken } from './useCsrfToken';

// Debounce composable
export { debounce } from './useDebounce';

// Keyboard navigation composables
export { useRovingTabindex } from './useRovingTabindex';

// Add more composables here as they are created
// export { useReports } from './useReports';
// export { useAnnouncements } from './useAnnouncements';

