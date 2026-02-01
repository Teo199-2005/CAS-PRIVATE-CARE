/**
 * useAdminStaffState Composable
 * ==============================
 * 
 * Centralized state management for Admin Staff Dashboard components.
 * Provides reactive state and actions for:
 * - User profile management
 * - Dashboard statistics
 * - Booking management
 * - User management (clients, caregivers, housekeepers)
 * - Application approvals
 * - Settings
 * 
 * Created: January 27, 2026
 * Purpose: Enable component splitting while sharing state
 */

import { ref, reactive, computed, watch } from 'vue';
import axios from 'axios';

// Singleton state - shared across all components
const state = reactive({
  // Profile
  profile: {
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    avatar: null,
    permissions: []
  },
  
  // Dashboard Stats
  stats: [],
  platformMetrics: null,
  
  // Data collections
  bookings: [],
  clients: [],
  caregivers: [],
  housekeepers: [],
  applications: [],
  announcements: [],
  
  // Loading states
  loading: {
    profile: false,
    stats: false,
    bookings: false,
    users: false,
    applications: false,
  },
  
  // Current section
  currentSection: localStorage.getItem('adminSection') || 'dashboard',
  
  // Filters
  filters: {
    bookingStatus: 'all',
    userType: 'all',
    dateRange: null,
    searchQuery: '',
  },
  
  // Dialogs
  dialogs: {
    addUser: false,
    viewCaregiver: false,
    announce: false,
    testEmail: false,
    client: false,
    caregiver: false,
  },
  
  // Error tracking
  errors: [],
  
  // Initialization flag
  initialized: false,
});

/**
 * useAdminStaffState composable
 * @returns {Object} State and actions for admin staff dashboard
 */
export function useAdminStaffState() {
  // Computed properties
  const isLoading = computed(() => {
    return Object.values(state.loading).some(v => v);
  });
  
  const hasPermission = (permission) => {
    if (!state.profile.permissions) return false;
    // Admin has all permissions
    if (state.profile.permissions.includes('all')) return true;
    return state.profile.permissions.includes(permission);
  };
  
  const filteredBookings = computed(() => {
    let result = [...state.bookings];
    
    if (state.filters.bookingStatus !== 'all') {
      result = result.filter(b => b.status === state.filters.bookingStatus);
    }
    
    if (state.filters.searchQuery) {
      const query = state.filters.searchQuery.toLowerCase();
      result = result.filter(b => 
        b.client?.name?.toLowerCase().includes(query) ||
        b.service_type?.toLowerCase().includes(query) ||
        b.id?.toString().includes(query)
      );
    }
    
    return result;
  });
  
  const pendingApplications = computed(() => {
    return state.applications.filter(a => a.status === 'pending');
  });
  
  const activeUsers = computed(() => {
    return [...state.clients, ...state.caregivers, ...state.housekeepers]
      .filter(u => u.status === 'Active');
  });
  
  // Actions
  const setSection = (section) => {
    state.currentSection = section;
    localStorage.setItem('adminSection', section);
  };
  
  const setFilter = (key, value) => {
    state.filters[key] = value;
  };
  
  const openDialog = (dialogName) => {
    if (state.dialogs.hasOwnProperty(dialogName)) {
      state.dialogs[dialogName] = true;
    }
  };
  
  const closeDialog = (dialogName) => {
    if (state.dialogs.hasOwnProperty(dialogName)) {
      state.dialogs[dialogName] = false;
    }
  };
  
  const closeAllDialogs = () => {
    Object.keys(state.dialogs).forEach(key => {
      state.dialogs[key] = false;
    });
  };
  
  // API Actions
  const fetchProfile = async () => {
    state.loading.profile = true;
    try {
      const response = await axios.get('/api/profile');
      if (response.data) {
        Object.assign(state.profile, response.data);
      }
    } catch (error) {
      console.error('Failed to fetch profile:', error);
      state.errors.push({ type: 'profile', message: error.message });
    } finally {
      state.loading.profile = false;
    }
  };
  
  const fetchStats = async () => {
    state.loading.stats = true;
    try {
      const response = await axios.get('/api/admin/stats');
      if (response.data?.success) {
        state.stats = response.data.stats || [];
      }
    } catch (error) {
      console.error('Failed to fetch stats:', error);
    } finally {
      state.loading.stats = false;
    }
  };
  
  const fetchBookings = async () => {
    state.loading.bookings = true;
    try {
      const response = await axios.get('/api/admin/bookings');
      if (response.data?.success) {
        state.bookings = response.data.data || [];
      }
    } catch (error) {
      console.error('Failed to fetch bookings:', error);
    } finally {
      state.loading.bookings = false;
    }
  };
  
  const fetchUsers = async () => {
    state.loading.users = true;
    try {
      const [clientsRes, caregiversRes, housekeepersRes] = await Promise.all([
        axios.get('/api/admin/clients').catch(() => ({ data: { data: [] } })),
        axios.get('/api/admin/caregivers').catch(() => ({ data: { data: [] } })),
        axios.get('/api/admin/housekeepers').catch(() => ({ data: { data: [] } })),
      ]);
      
      state.clients = clientsRes.data?.data || [];
      state.caregivers = caregiversRes.data?.data || [];
      state.housekeepers = housekeepersRes.data?.data || [];
    } catch (error) {
      console.error('Failed to fetch users:', error);
    } finally {
      state.loading.users = false;
    }
  };
  
  const fetchApplications = async () => {
    state.loading.applications = true;
    try {
      const response = await axios.get('/api/admin/applications');
      if (response.data?.success) {
        state.applications = response.data.data || [];
      }
    } catch (error) {
      console.error('Failed to fetch applications:', error);
    } finally {
      state.loading.applications = false;
    }
  };
  
  const approveApplication = async (id) => {
    try {
      const response = await axios.post(`/api/admin/applications/${id}/approve`);
      if (response.data?.success) {
        // Update local state
        const index = state.applications.findIndex(a => a.id === id);
        if (index !== -1) {
          state.applications[index].status = 'approved';
        }
        return { success: true };
      }
      return { success: false, error: response.data?.message };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };
  
  const rejectApplication = async (id) => {
    try {
      const response = await axios.post(`/api/admin/applications/${id}/reject`);
      if (response.data?.success) {
        const index = state.applications.findIndex(a => a.id === id);
        if (index !== -1) {
          state.applications[index].status = 'rejected';
        }
        return { success: true };
      }
      return { success: false, error: response.data?.message };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };
  
  const updateBookingStatus = async (id, status) => {
    try {
      const response = await axios.put(`/api/admin/bookings/${id}/status`, { status });
      if (response.data?.success) {
        const index = state.bookings.findIndex(b => b.id === id);
        if (index !== -1) {
          state.bookings[index].status = status;
        }
        return { success: true };
      }
      return { success: false, error: response.data?.message };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };
  
  const sendAnnouncement = async (announcement) => {
    try {
      const response = await axios.post('/api/admin/announcements', announcement);
      if (response.data?.success) {
        state.announcements.unshift(response.data.announcement);
        return { success: true };
      }
      return { success: false, error: response.data?.message };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };
  
  // Initialize
  const initialize = async () => {
    if (state.initialized) return;
    
    await Promise.all([
      fetchProfile(),
      fetchStats(),
      fetchBookings(),
    ]);
    
    state.initialized = true;
  };
  
  // Refresh all data
  const refresh = async () => {
    await Promise.all([
      fetchStats(),
      fetchBookings(),
      fetchUsers(),
      fetchApplications(),
    ]);
  };
  
  return {
    // State (reactive)
    state,
    
    // Computed
    isLoading,
    filteredBookings,
    pendingApplications,
    activeUsers,
    
    // Permission check
    hasPermission,
    
    // Navigation
    setSection,
    
    // Filters
    setFilter,
    
    // Dialogs
    openDialog,
    closeDialog,
    closeAllDialogs,
    
    // API Actions
    fetchProfile,
    fetchStats,
    fetchBookings,
    fetchUsers,
    fetchApplications,
    approveApplication,
    rejectApplication,
    updateBookingStatus,
    sendAnnouncement,
    
    // Lifecycle
    initialize,
    refresh,
  };
}

export default useAdminStaffState;
