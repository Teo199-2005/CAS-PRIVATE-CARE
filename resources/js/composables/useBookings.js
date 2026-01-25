/**
 * ========================================================================
 * CAS Private Care - Bookings Composable
 * ========================================================================
 * 
 * Extracted from AdminDashboard.vue to reduce component size.
 * Handles all booking-related state and API calls.
 * 
 * @version 1.0.0
 * @since 2026-01-25
 */

import { ref, computed } from 'vue';
import axios from 'axios';

export function useBookings() {
    // ============================================
    // STATE
    // ============================================
    const loading = ref(false);
    const error = ref(null);
    
    // Booking data
    const bookings = ref([]);
    const pendingBookings = ref([]);
    const activeBookings = ref([]);
    const completedBookings = ref([]);
    const cancelledBookings = ref([]);
    
    // Selected booking for editing/viewing
    const selectedBooking = ref(null);
    
    // Dialog states
    const showBookingDialog = ref(false);
    const showAssignDialog = ref(false);
    const showCancelDialog = ref(false);
    
    // Form data
    const bookingForm = ref({
        client_id: null,
        service_type: 'caregiving',
        start_date: null,
        end_date: null,
        start_time: null,
        end_time: null,
        address: '',
        city: '',
        state: 'NY',
        zip_code: '',
        notes: '',
        recurring: false,
        recurring_pattern: null,
    });
    
    // Filters
    const dateFilter = ref('all');
    const statusFilter = ref('all');
    const serviceFilter = ref('all');
    const searchQuery = ref('');
    
    // Pagination
    const pagination = ref({
        page: 1,
        itemsPerPage: 15,
        totalItems: 0,
    });

    // ============================================
    // COMPUTED
    // ============================================
    const totalBookings = computed(() => bookings.value.length);
    const pendingCount = computed(() => pendingBookings.value.length);
    const activeCount = computed(() => activeBookings.value.length);
    const completedCount = computed(() => completedBookings.value.length);
    
    const filteredBookings = computed(() => {
        let result = [...bookings.value];
        
        // Apply status filter
        if (statusFilter.value !== 'all') {
            result = result.filter(b => b.status === statusFilter.value);
        }
        
        // Apply service filter
        if (serviceFilter.value !== 'all') {
            result = result.filter(b => b.service_type === serviceFilter.value);
        }
        
        // Apply search
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            result = result.filter(b => 
                b.client_name?.toLowerCase().includes(query) ||
                b.provider_name?.toLowerCase().includes(query) ||
                b.address?.toLowerCase().includes(query)
            );
        }
        
        return result;
    });

    // ============================================
    // METHODS
    // ============================================
    
    async function fetchBookings() {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.get('/api/admin/bookings', {
                params: {
                    status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
                    service_type: serviceFilter.value !== 'all' ? serviceFilter.value : undefined,
                    search: searchQuery.value || undefined,
                    page: pagination.value.page,
                    per_page: pagination.value.itemsPerPage,
                }
            });
            
            const data = response.data.data || response.data || [];
            bookings.value = data;
            
            // Categorize bookings
            pendingBookings.value = data.filter(b => b.status === 'pending');
            activeBookings.value = data.filter(b => ['confirmed', 'in_progress'].includes(b.status));
            completedBookings.value = data.filter(b => b.status === 'completed');
            cancelledBookings.value = data.filter(b => b.status === 'cancelled');
            
            if (response.data.meta) {
                pagination.value.totalItems = response.data.meta.total;
            }
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to load bookings';
            console.error('Bookings fetch error:', err);
        } finally {
            loading.value = false;
        }
    }
    
    async function createBooking(formData) {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.post('/api/admin/bookings', formData);
            await fetchBookings();
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create booking';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }
    
    async function updateBooking(bookingId, formData) {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.put(`/api/admin/bookings/${bookingId}`, formData);
            await fetchBookings();
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to update booking';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }
    
    async function cancelBooking(bookingId, reason = '') {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.post(`/api/admin/bookings/${bookingId}/cancel`, {
                reason
            });
            await fetchBookings();
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to cancel booking';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }
    
    async function assignProvider(bookingId, providerId, providerType) {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.post(`/api/admin/bookings/${bookingId}/assign`, {
                provider_id: providerId,
                provider_type: providerType,
            });
            await fetchBookings();
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to assign provider';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }
    
    async function unassignProvider(bookingId, assignmentId) {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.delete(`/api/admin/bookings/${bookingId}/assign/${assignmentId}`);
            await fetchBookings();
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to unassign provider';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }
    
    async function completeBooking(bookingId) {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.post(`/api/admin/bookings/${bookingId}/complete`);
            await fetchBookings();
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to complete booking';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }

    // ============================================
    // HELPER METHODS
    // ============================================
    
    function getBookingStatusColor(status) {
        const colors = {
            'pending': 'warning',
            'confirmed': 'info',
            'in_progress': 'primary',
            'completed': 'success',
            'cancelled': 'error',
            'no_show': 'error',
        };
        return colors[status?.toLowerCase()] || 'grey';
    }
    
    function getServiceTypeColor(type) {
        const colors = {
            'caregiving': 'primary',
            'housekeeping': 'secondary',
            'both': 'purple',
        };
        return colors[type?.toLowerCase()] || 'grey';
    }
    
    function getServiceTypeIcon(type) {
        const icons = {
            'caregiving': 'mdi-heart-pulse',
            'housekeeping': 'mdi-broom',
            'both': 'mdi-home-heart',
        };
        return icons[type?.toLowerCase()] || 'mdi-calendar';
    }
    
    function formatBookingDate(date) {
        if (!date) return 'N/A';
        return new Date(date).toLocaleDateString('en-US', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }
    
    function formatBookingTime(time) {
        if (!time) return 'N/A';
        return new Date(`1970-01-01T${time}`).toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        });
    }
    
    function resetBookingForm() {
        bookingForm.value = {
            client_id: null,
            service_type: 'caregiving',
            start_date: null,
            end_date: null,
            start_time: null,
            end_time: null,
            address: '',
            city: '',
            state: 'NY',
            zip_code: '',
            notes: '',
            recurring: false,
            recurring_pattern: null,
        };
    }
    
    function setBookingForm(booking) {
        bookingForm.value = { ...booking };
        selectedBooking.value = booking;
    }

    // ============================================
    // RETURN
    // ============================================
    return {
        // State
        loading,
        error,
        bookings,
        pendingBookings,
        activeBookings,
        completedBookings,
        cancelledBookings,
        selectedBooking,
        showBookingDialog,
        showAssignDialog,
        showCancelDialog,
        bookingForm,
        dateFilter,
        statusFilter,
        serviceFilter,
        searchQuery,
        pagination,
        
        // Computed
        totalBookings,
        pendingCount,
        activeCount,
        completedCount,
        filteredBookings,
        
        // Methods
        fetchBookings,
        createBooking,
        updateBooking,
        cancelBooking,
        assignProvider,
        unassignProvider,
        completeBooking,
        getBookingStatusColor,
        getServiceTypeColor,
        getServiceTypeIcon,
        formatBookingDate,
        formatBookingTime,
        resetBookingForm,
        setBookingForm,
    };
}
