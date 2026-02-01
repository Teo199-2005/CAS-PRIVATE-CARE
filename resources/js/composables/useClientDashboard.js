/**
 * Client Dashboard State Composable
 * 
 * Centralizes all client dashboard state management and API calls.
 * This composable is shared across all client dashboard sections
 * to ensure consistent state and avoid prop drilling.
 * 
 * @version 1.0
 * @date January 28, 2026
 */

import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useApi } from './useApi';

// Singleton state - shared across all components using this composable
const state = reactive({
  // User data
  user: null,
  userLoaded: false,
  
  // Bookings
  bookings: [],
  bookingsLoading: false,
  bookingsLoaded: false,
  
  // Stats
  stats: {
    totalBookings: 0,
    activeBookings: 0,
    completedBookings: 0,
    totalSpent: 0
  },
  statsLoading: false,
  statsLoaded: false,
  
  // Payment methods
  paymentMethods: [],
  paymentMethodsLoading: false,
  paymentMethodsLoaded: false,
  
  // Notifications
  notifications: [],
  unreadCount: 0,
  notificationsLoading: false,
  
  // Analytics data
  spendingData: null,
  availableYears: [],
  topCaregivers: [],
  analyticsLoading: false,
  
  // UI state
  currentSection: 'dashboard',
  selectedBooking: null,
  isPageLoading: true,
  
  // Booking maintenance
  bookingMaintenanceEnabled: false,
  bookingMaintenanceMessage: '',
  
  // Errors
  errors: {}
});

// API helpers
const api = useApi();

/**
 * Client Dashboard Composable
 * Provides reactive state and methods for client dashboard
 */
export function useClientDashboard() {
  
  // ==========================================
  // COMPUTED PROPERTIES
  // ==========================================
  
  const userName = computed(() => {
    if (!state.user) return 'Client';
    return state.user.name || state.user.email?.split('@')[0] || 'Client';
  });
  
  const userInitials = computed(() => {
    const name = userName.value;
    return name.split(' ')
      .map(part => part[0])
      .join('')
      .toUpperCase()
      .substring(0, 2);
  });
  
  const userAvatar = computed(() => state.user?.avatar || null);
  
  const welcomeMessage = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
  });
  
  // Booking filters
  const pendingBookings = computed(() => 
    state.bookings.filter(b => b.status === 'pending')
  );
  
  const confirmedBookings = computed(() => 
    state.bookings.filter(b => b.status === 'approved' || b.status === 'confirmed')
  );
  
  const completedBookings = computed(() => 
    state.bookings.filter(b => b.status === 'completed')
  );
  
  const allClientBookings = computed(() => state.bookings);
  
  // Stats cards
  const statsCards = computed(() => [
    {
      title: 'Total Bookings',
      value: state.stats.totalBookings,
      icon: 'mdi-calendar-check',
      change: null,
      changeColor: 'grey'
    },
    {
      title: 'Active Bookings',
      value: state.stats.activeBookings,
      icon: 'mdi-calendar-clock',
      change: null,
      changeColor: 'success'
    },
    {
      title: 'Completed',
      value: state.stats.completedBookings,
      icon: 'mdi-check-circle',
      change: null,
      changeColor: 'grey'
    },
    {
      title: 'Total Spent',
      value: `$${formatCurrency(state.stats.totalSpent)}`,
      icon: 'mdi-currency-usd',
      change: null,
      changeColor: 'grey'
    }
  ]);
  
  // ==========================================
  // DATA FETCHING METHODS
  // ==========================================
  
  /**
   * Load user profile data
   */
  async function loadUserProfile() {
    try {
      const response = await fetch('/api/profile', {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken()
        },
        credentials: 'same-origin'
      });
      
      if (response.ok) {
        const data = await response.json();
        state.user = data.user || data;
        state.userLoaded = true;
      }
    } catch (error) {
      console.error('Failed to load user profile:', error);
      state.errors.profile = error.message;
    }
  }
  
  /**
   * Load client bookings
   */
  async function loadBookings(force = false) {
    if (state.bookingsLoaded && !force) return;
    
    state.bookingsLoading = true;
    try {
      const response = await fetch('/api/bookings', {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken()
        },
        credentials: 'same-origin'
      });
      
      if (response.ok) {
        const data = await response.json();
        state.bookings = transformBookings(data.data || data.bookings || []);
        state.bookingsLoaded = true;
      }
    } catch (error) {
      console.error('Failed to load bookings:', error);
      state.errors.bookings = error.message;
    } finally {
      state.bookingsLoading = false;
    }
  }
  
  /**
   * Load client stats
   */
  async function loadStats(force = false) {
    if (state.statsLoaded && !force) return;
    
    state.statsLoading = true;
    try {
      const response = await fetch('/api/client/stats', {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken()
        },
        credentials: 'same-origin'
      });
      
      if (response.ok) {
        const data = await response.json();
        state.stats = {
          totalBookings: data.total_bookings || 0,
          activeBookings: data.active_bookings || 0,
          completedBookings: data.completed_bookings || 0,
          totalSpent: data.total_spent || 0
        };
        state.statsLoaded = true;
      }
    } catch (error) {
      console.error('Failed to load stats:', error);
      state.errors.stats = error.message;
    } finally {
      state.statsLoading = false;
    }
  }
  
  /**
   * Load payment methods
   */
  async function loadPaymentMethods(force = false) {
    if (state.paymentMethodsLoaded && !force) return;
    
    state.paymentMethodsLoading = true;
    try {
      const response = await fetch('/api/stripe/payment-methods', {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken()
        },
        credentials: 'same-origin'
      });
      
      if (response.ok) {
        const data = await response.json();
        state.paymentMethods = data.payment_methods || [];
        state.paymentMethodsLoaded = true;
      }
    } catch (error) {
      console.error('Failed to load payment methods:', error);
      state.errors.paymentMethods = error.message;
    } finally {
      state.paymentMethodsLoading = false;
    }
  }
  
  /**
   * Load notifications
   */
  async function loadNotifications() {
    state.notificationsLoading = true;
    try {
      const response = await fetch('/api/notifications', {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken()
        },
        credentials: 'same-origin'
      });
      
      if (response.ok) {
        const data = await response.json();
        state.notifications = data.notifications || [];
        state.unreadCount = data.unread_count || 
          state.notifications.filter(n => !n.read_at).length;
      }
    } catch (error) {
      console.error('Failed to load notifications:', error);
    } finally {
      state.notificationsLoading = false;
    }
  }
  
  /**
   * Load analytics data
   */
  async function loadAnalytics(year = null) {
    state.analyticsLoading = true;
    try {
      // Load available years
      const yearsResponse = await fetch('/api/client/available-years', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCSRFToken() },
        credentials: 'same-origin'
      });
      
      if (yearsResponse.ok) {
        const yearsData = await yearsResponse.json();
        state.availableYears = yearsData.years || [new Date().getFullYear()];
      }
      
      // Load spending data
      const selectedYear = year || new Date().getFullYear();
      const spendingResponse = await fetch(`/api/client/spending-data?year=${selectedYear}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCSRFToken() },
        credentials: 'same-origin'
      });
      
      if (spendingResponse.ok) {
        state.spendingData = await spendingResponse.json();
      }
      
      // Load top caregivers
      const caregiversResponse = await fetch('/api/client/top-caregivers', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCSRFToken() },
        credentials: 'same-origin'
      });
      
      if (caregiversResponse.ok) {
        const caregiversData = await caregiversResponse.json();
        state.topCaregivers = caregiversData.caregivers || [];
      }
      
    } catch (error) {
      console.error('Failed to load analytics:', error);
      state.errors.analytics = error.message;
    } finally {
      state.analyticsLoading = false;
    }
  }
  
  /**
   * Check booking maintenance status
   */
  async function checkMaintenanceStatus() {
    try {
      const response = await fetch('/api/booking-maintenance-status', {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
      });
      
      if (response.ok) {
        const data = await response.json();
        state.bookingMaintenanceEnabled = data.enabled || false;
        state.bookingMaintenanceMessage = data.message || 'Booking is temporarily unavailable.';
      }
    } catch (error) {
      console.error('Failed to check maintenance status:', error);
    }
  }
  
  /**
   * Initialize dashboard - loads essential data
   */
  async function initialize() {
    state.isPageLoading = true;
    
    try {
      // Load essential data in parallel
      await Promise.all([
        loadUserProfile(),
        loadBookings(),
        loadStats(),
        loadNotifications(),
        checkMaintenanceStatus()
      ]);
    } catch (error) {
      console.error('Dashboard initialization failed:', error);
    } finally {
      state.isPageLoading = false;
    }
  }
  
  // ==========================================
  // ACTION METHODS
  // ==========================================
  
  /**
   * Navigate to a section
   */
  function navigateToSection(section) {
    state.currentSection = section;
    
    // Lazy load section-specific data
    if (section === 'payment' && !state.paymentMethodsLoaded) {
      loadPaymentMethods();
    }
    if (section === 'analytics' && !state.spendingData) {
      loadAnalytics();
    }
  }
  
  /**
   * Select a booking for payment or details
   */
  function selectBooking(booking) {
    state.selectedBooking = booking;
  }
  
  /**
   * Mark notification as read
   */
  async function markNotificationRead(notificationId) {
    try {
      await fetch(`/api/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken()
        },
        credentials: 'same-origin'
      });
      
      // Update local state
      const notification = state.notifications.find(n => n.id === notificationId);
      if (notification) {
        notification.read_at = new Date().toISOString();
        state.unreadCount = Math.max(0, state.unreadCount - 1);
      }
    } catch (error) {
      console.error('Failed to mark notification read:', error);
    }
  }
  
  /**
   * Logout
   */
  async function logout() {
    try {
      await fetch('/logout', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken()
        },
        credentials: 'same-origin'
      });
      window.location.href = '/login';
    } catch (error) {
      console.error('Logout failed:', error);
      window.location.href = '/login';
    }
  }
  
  /**
   * Refresh all data
   */
  async function refresh() {
    await Promise.all([
      loadBookings(true),
      loadStats(true),
      loadNotifications()
    ]);
  }
  
  // ==========================================
  // HELPER FUNCTIONS
  // ==========================================
  
  function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
  }
  
  function formatCurrency(amount) {
    return parseFloat(amount || 0).toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }
  
  function transformBookings(bookings) {
    return bookings.map(b => ({
      ...b,
      service: b.service_type || b.service,
      date: b.service_date,
      location: b.city || b.borough || 'New York',
      startingTime: b.start_time,
      hoursPerDay: b.hours_per_day || 8,
      duration: b.duration_days || 1,
      price: b.total_budget
    }));
  }
  
  // ==========================================
  // RETURN PUBLIC API
  // ==========================================
  
  return {
    // State (reactive)
    state,
    
    // Computed
    userName,
    userInitials,
    userAvatar,
    welcomeMessage,
    pendingBookings,
    confirmedBookings,
    completedBookings,
    allClientBookings,
    statsCards,
    
    // Data loading
    initialize,
    loadBookings,
    loadStats,
    loadPaymentMethods,
    loadNotifications,
    loadAnalytics,
    refresh,
    
    // Actions
    navigateToSection,
    selectBooking,
    markNotificationRead,
    logout
  };
}

// Export singleton state for debugging
export { state as clientDashboardState };
