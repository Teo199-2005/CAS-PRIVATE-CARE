/**
 * ========================================================================
 * CAS Private Care - User Management Composable
 * ========================================================================
 * 
 * Extracted from AdminDashboard.vue to reduce component size.
 * Handles all user management state and API calls.
 * 
 * @version 1.0.0
 * @since 2026-01-25
 */

import { ref, computed } from 'vue';
import axios from 'axios';

export function useUserManagement() {
    // ============================================
    // STATE
    // ============================================
    const loading = ref(false);
    const error = ref(null);
    
    // User lists
    const clients = ref([]);
    const caregivers = ref([]);
    const housekeepers = ref([]);
    const marketingStaff = ref([]);
    const adminStaff = ref([]);
    const trainingCenters = ref([]);
    
    // Selected user for editing
    const selectedUser = ref(null);
    const selectedUserType = ref(null);
    
    // Dialog states
    const showEditDialog = ref(false);
    const showViewDialog = ref(false);
    const showConfirmDialog = ref(false);
    
    // Form data
    const clientForm = ref({});
    const caregiverForm = ref({});
    const housekeeperForm = ref({});
    const marketingStaffForm = ref({});
    const adminStaffForm = ref({});
    const trainingCenterForm = ref({});
    
    // Search and filters
    const searchQuery = ref('');
    const statusFilter = ref('all');
    const sortBy = ref('created_at');
    const sortOrder = ref('desc');

    // ============================================
    // COMPUTED
    // ============================================
    const totalClients = computed(() => clients.value.length);
    const activeClients = computed(() => clients.value.filter(c => c.status === 'active').length);
    const totalCaregivers = computed(() => caregivers.value.length);
    const activeCaregivers = computed(() => caregivers.value.filter(c => c.status === 'active').length);
    const totalHousekeepers = computed(() => housekeepers.value.length);
    const activeHousekeepers = computed(() => housekeepers.value.filter(h => h.status === 'active').length);

    // ============================================
    // FETCH METHODS
    // ============================================
    
    async function fetchClients() {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.get('/api/admin/users', {
                params: { type: 'client', search: searchQuery.value, status: statusFilter.value }
            });
            clients.value = response.data.data || response.data || [];
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to load clients';
            console.error('Clients fetch error:', err);
        } finally {
            loading.value = false;
        }
    }
    
    async function fetchCaregivers() {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.get('/api/admin/caregivers');
            caregivers.value = response.data.data || response.data || [];
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to load caregivers';
            console.error('Caregivers fetch error:', err);
        } finally {
            loading.value = false;
        }
    }
    
    async function fetchHousekeepers() {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.get('/api/admin/housekeepers');
            housekeepers.value = response.data.data || response.data || [];
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to load housekeepers';
            console.error('Housekeepers fetch error:', err);
        } finally {
            loading.value = false;
        }
    }
    
    async function fetchMarketingStaff() {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.get('/api/admin/marketing-staff');
            marketingStaff.value = response.data.data || response.data || [];
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to load marketing staff';
            console.error('Marketing staff fetch error:', err);
        } finally {
            loading.value = false;
        }
    }
    
    async function fetchAdminStaff() {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.get('/api/admin/admin-staff');
            adminStaff.value = response.data.data || response.data || [];
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to load admin staff';
            console.error('Admin staff fetch error:', err);
        } finally {
            loading.value = false;
        }
    }
    
    async function fetchTrainingCenters() {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.get('/api/admin/training-centers');
            trainingCenters.value = response.data.data || response.data || [];
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to load training centers';
            console.error('Training centers fetch error:', err);
        } finally {
            loading.value = false;
        }
    }
    
    async function fetchAllUsers() {
        await Promise.all([
            fetchClients(),
            fetchCaregivers(),
            fetchHousekeepers(),
            fetchMarketingStaff(),
            fetchAdminStaff(),
            fetchTrainingCenters(),
        ]);
    }

    // ============================================
    // UPDATE METHODS
    // ============================================
    
    async function updateUserStatus(userId, userType, status) {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.put(`/api/admin/users/${userId}/status`, {
                status,
                user_type: userType,
            });
            
            // Refresh the appropriate user list
            switch (userType) {
                case 'client':
                    await fetchClients();
                    break;
                case 'caregiver':
                    await fetchCaregivers();
                    break;
                case 'housekeeper':
                    await fetchHousekeepers();
                    break;
                case 'marketing':
                    await fetchMarketingStaff();
                    break;
                case 'adminstaff':
                    await fetchAdminStaff();
                    break;
            }
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to update status';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }
    
    async function saveUser(userType, formData) {
        loading.value = true;
        error.value = null;
        
        const isNew = !formData.id;
        const endpoint = getEndpointForUserType(userType);
        
        try {
            let response;
            if (isNew) {
                response = await axios.post(endpoint, formData);
            } else {
                response = await axios.put(`${endpoint}/${formData.id}`, formData);
            }
            
            // Refresh the appropriate user list
            await refreshUserList(userType);
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to save user';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }
    
    async function deleteUser(userId, userType) {
        loading.value = true;
        error.value = null;
        
        const endpoint = getEndpointForUserType(userType);
        
        try {
            await axios.delete(`${endpoint}/${userId}`);
            await refreshUserList(userType);
            
            return { success: true };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete user';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }

    // ============================================
    // HELPER METHODS
    // ============================================
    
    function getEndpointForUserType(userType) {
        const endpoints = {
            'client': '/api/admin/users',
            'caregiver': '/api/admin/caregivers',
            'housekeeper': '/api/admin/housekeepers',
            'marketing': '/api/admin/marketing-staff',
            'adminstaff': '/api/admin/admin-staff',
            'trainingcenter': '/api/admin/training-centers',
        };
        return endpoints[userType] || '/api/admin/users';
    }
    
    async function refreshUserList(userType) {
        switch (userType) {
            case 'client':
                await fetchClients();
                break;
            case 'caregiver':
                await fetchCaregivers();
                break;
            case 'housekeeper':
                await fetchHousekeepers();
                break;
            case 'marketing':
                await fetchMarketingStaff();
                break;
            case 'adminstaff':
                await fetchAdminStaff();
                break;
            case 'trainingcenter':
                await fetchTrainingCenters();
                break;
        }
    }
    
    function getStatusColor(status) {
        const colors = {
            'active': 'success',
            'approved': 'success',
            'pending': 'warning',
            'under_review': 'info',
            'suspended': 'error',
            'rejected': 'error',
            'inactive': 'grey',
            'blocked': 'error',
        };
        return colors[status?.toLowerCase()] || 'grey';
    }
    
    function getUserTypeLabel(type) {
        const labels = {
            'client': 'Client',
            'caregiver': 'Caregiver',
            'housekeeper': 'Housekeeper',
            'marketing': 'Marketing Staff',
            'adminstaff': 'Admin Staff',
            'trainingcenter': 'Training Center',
            'admin': 'Administrator',
        };
        return labels[type?.toLowerCase()] || type;
    }
    
    function initializeForm(userType) {
        const forms = {
            'client': {
                id: null,
                name: '',
                email: '',
                phone: '',
                address: '',
                city: '',
                state: 'NY',
                zip_code: '',
                status: 'active',
                notes: '',
            },
            'caregiver': {
                id: null,
                user_id: null,
                name: '',
                email: '',
                phone: '',
                address: '',
                city: '',
                state: 'NY',
                zip_code: '',
                status: 'pending',
                hourly_rate: 25,
                experience_years: 0,
                certifications: [],
                availability: {},
                languages: ['English'],
                notes: '',
            },
            'housekeeper': {
                id: null,
                user_id: null,
                name: '',
                email: '',
                phone: '',
                address: '',
                city: '',
                state: 'NY',
                zip_code: '',
                status: 'pending',
                hourly_rate: 20,
                services: [],
                availability: {},
                notes: '',
            },
            'marketing': {
                id: null,
                name: '',
                email: '',
                phone: '',
                referral_code: '',
                commission_rate: 10,
                status: 'active',
            },
            'adminstaff': {
                id: null,
                name: '',
                email: '',
                password: '',
                permissions: {},
                status: 'active',
            },
            'trainingcenter': {
                id: null,
                name: '',
                email: '',
                phone: '',
                address: '',
                city: '',
                state: 'NY',
                zip_code: '',
                commission_rate: 5,
                status: 'active',
            },
        };
        
        return forms[userType] || {};
    }

    // ============================================
    // RETURN
    // ============================================
    return {
        // State
        loading,
        error,
        clients,
        caregivers,
        housekeepers,
        marketingStaff,
        adminStaff,
        trainingCenters,
        selectedUser,
        selectedUserType,
        showEditDialog,
        showViewDialog,
        showConfirmDialog,
        clientForm,
        caregiverForm,
        housekeeperForm,
        marketingStaffForm,
        adminStaffForm,
        trainingCenterForm,
        searchQuery,
        statusFilter,
        sortBy,
        sortOrder,
        
        // Computed
        totalClients,
        activeClients,
        totalCaregivers,
        activeCaregivers,
        totalHousekeepers,
        activeHousekeepers,
        
        // Methods
        fetchClients,
        fetchCaregivers,
        fetchHousekeepers,
        fetchMarketingStaff,
        fetchAdminStaff,
        fetchTrainingCenters,
        fetchAllUsers,
        updateUserStatus,
        saveUser,
        deleteUser,
        getStatusColor,
        getUserTypeLabel,
        initializeForm,
    };
}
