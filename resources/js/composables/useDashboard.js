/**
 * useDashboard Composable
 * 
 * Shared dashboard functionality extracted from ClientDashboard, CaregiverDashboard,
 * and HousekeeperDashboard components to reduce code duplication.
 * 
 * Features:
 * - User data management
 * - Avatar handling
 * - Navigation state
 * - Common API calls
 * - Error handling
 * - Loading states
 */

import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

export function useDashboard(userRole) {
  // ============================================
  // STATE
  // ============================================
  
  // User data
  const userData = ref(null);
  const userName = ref('');
  const userEmail = ref('');
  const userAvatar = ref(null);
  const userInitials = ref('');
  
  // Loading states
  const isPageLoading = ref(true);
  const isLoading = ref(false);
  const isSaving = ref(false);
  
  // Error states
  const error = ref(null);
  const errorMessage = ref('');
  
  // Navigation
  const currentSection = ref('dashboard');
  
  // Notifications
  const notification = ref({
    show: false,
    type: 'success',
    title: '',
    message: '',
    timeout: 5000
  });

  // ============================================
  // COMPUTED
  // ============================================
  
  const welcomeMessage = computed(() => {
    const hour = new Date().getHours();
    let greeting = 'Good morning';
    if (hour >= 12 && hour < 17) greeting = 'Good afternoon';
    if (hour >= 17) greeting = 'Good evening';
    return `${greeting}, ${userName.value || 'User'}!`;
  });

  const hasAvatar = computed(() => {
    return userAvatar.value && userAvatar.value.length > 0;
  });

  // ============================================
  // METHODS
  // ============================================
  
  /**
   * Fetch user dashboard data
   */
  const fetchDashboardData = async (endpoint) => {
    try {
      isLoading.value = true;
      error.value = null;
      
      const response = await axios.get(endpoint);
      
      if (response.data) {
        userData.value = response.data;
        
        // Extract user info
        if (response.data.user) {
          userName.value = response.data.user.name || '';
          userEmail.value = response.data.user.email || '';
          
          // Handle avatar
          if (response.data.user.avatar) {
            userAvatar.value = response.data.user.avatar.startsWith('/') 
              ? response.data.user.avatar 
              : '/storage/' + response.data.user.avatar;
          }
          
          // Generate initials
          if (userName.value) {
            const names = userName.value.split(' ');
            userInitials.value = names.length > 1 
              ? (names[0][0] + names[names.length - 1][0]).toUpperCase()
              : userName.value.substring(0, 2).toUpperCase();
          }
        }
      }
      
      return response.data;
    } catch (err) {
      console.error('Failed to fetch dashboard data:', err);
      error.value = err;
      errorMessage.value = err.response?.data?.message || 'Failed to load dashboard data';
      throw err;
    } finally {
      isLoading.value = false;
      isPageLoading.value = false;
    }
  };

  /**
   * Update user profile
   */
  const updateProfile = async (endpoint, profileData) => {
    try {
      isSaving.value = true;
      error.value = null;
      
      const response = await axios.put(endpoint, profileData);
      
      if (response.data.success) {
        showNotification('success', 'Success', 'Profile updated successfully');
        
        // Update local state
        if (profileData.name) userName.value = profileData.name;
        if (profileData.email) userEmail.value = profileData.email;
      }
      
      return response.data;
    } catch (err) {
      console.error('Failed to update profile:', err);
      showNotification('error', 'Error', err.response?.data?.message || 'Failed to update profile');
      throw err;
    } finally {
      isSaving.value = false;
    }
  };

  /**
   * Upload avatar
   */
  const uploadAvatar = async (endpoint, file) => {
    try {
      isSaving.value = true;
      
      const formData = new FormData();
      formData.append('avatar', file);
      
      const response = await axios.post(endpoint, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      
      if (response.data.avatar) {
        userAvatar.value = response.data.avatar.startsWith('/') 
          ? response.data.avatar 
          : '/storage/' + response.data.avatar;
        showNotification('success', 'Success', 'Avatar updated successfully');
      }
      
      return response.data;
    } catch (err) {
      console.error('Failed to upload avatar:', err);
      showNotification('error', 'Error', 'Failed to upload avatar');
      throw err;
    } finally {
      isSaving.value = false;
    }
  };

  /**
   * Change password
   */
  const changePassword = async (endpoint, passwords) => {
    try {
      isSaving.value = true;
      
      const response = await axios.post(endpoint, passwords);
      
      if (response.data.success) {
        showNotification('success', 'Success', 'Password changed successfully');
      }
      
      return response.data;
    } catch (err) {
      console.error('Failed to change password:', err);
      showNotification('error', 'Error', err.response?.data?.message || 'Failed to change password');
      throw err;
    } finally {
      isSaving.value = false;
    }
  };

  /**
   * Show notification toast
   */
  const showNotification = (type, title, message, timeout = 5000) => {
    notification.value = {
      show: true,
      type,
      title,
      message,
      timeout
    };
  };

  /**
   * Handle section change
   */
  const handleSectionChange = (section) => {
    currentSection.value = section;
    
    // Update URL without reload (for bookmarking)
    const url = new URL(window.location);
    url.searchParams.set('section', section);
    window.history.replaceState({}, '', url);
  };

  /**
   * Logout
   */
  const logout = async () => {
    try {
      await axios.post('/logout');
      window.location.href = '/login';
    } catch (err) {
      // Fallback: submit form directly
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/logout';
      
      const csrfToken = document.querySelector('meta[name="csrf-token"]');
      if (csrfToken) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_token';
        input.value = csrfToken.content;
        form.appendChild(input);
      }
      
      document.body.appendChild(form);
      form.submit();
    }
  };

  /**
   * Format currency
   */
  const formatCurrency = (amount, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency
    }).format(amount);
  };

  /**
   * Format date
   */
  const formatDate = (date, options = {}) => {
    if (!date) return 'N/A';
    
    const defaultOptions = {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      ...options
    };
    
    return new Date(date).toLocaleDateString('en-US', defaultOptions);
  };

  /**
   * Format time
   */
  const formatTime = (date) => {
    if (!date) return 'N/A';
    
    return new Date(date).toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  // ============================================
  // LIFECYCLE
  // ============================================
  
  onMounted(() => {
    // Check for section in URL
    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section');
    if (section) {
      currentSection.value = section;
    }
  });

  // ============================================
  // RETURN
  // ============================================
  
  return {
    // State
    userData,
    userName,
    userEmail,
    userAvatar,
    userInitials,
    isPageLoading,
    isLoading,
    isSaving,
    error,
    errorMessage,
    currentSection,
    notification,
    
    // Computed
    welcomeMessage,
    hasAvatar,
    
    // Methods
    fetchDashboardData,
    updateProfile,
    uploadAvatar,
    changePassword,
    showNotification,
    handleSectionChange,
    logout,
    formatCurrency,
    formatDate,
    formatTime
  };
}

export default useDashboard;
