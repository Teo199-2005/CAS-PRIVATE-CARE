/**
 * ========================================================================
 * CAS Private Care - Payments Composable
 * ========================================================================
 * 
 * Extracted from AdminDashboard.vue to reduce component size.
 * Handles all payment-related state and API calls.
 * 
 * @version 1.0.0
 * @since 2026-01-25
 */

import { ref, computed } from 'vue';
import axios from 'axios';

export function usePayments() {
    // ============================================
    // STATE
    // ============================================
    const loading = ref(false);
    const error = ref(null);
    
    // Payment data
    const clientPayments = ref([]);
    const caregiverPayments = ref([]);
    const housekeeperPayments = ref([]);
    const marketingCommissions = ref([]);
    const trainingCommissions = ref([]);
    const transactions = ref([]);
    
    // Money flow stats
    const moneyFlow = ref({
        today: { revenue: 0, payouts: 0, profit: 0 },
        totals: { revenue: 0, payouts: 0, profit: 0, pending: 0 },
        commissions: { marketing: 0, training: 0 },
    });
    
    // Pagination
    const pagination = ref({
        page: 1,
        itemsPerPage: 10,
        totalItems: 0,
    });

    // ============================================
    // COMPUTED
    // ============================================
    const totalRevenue = computed(() => moneyFlow.value.totals?.revenue || 0);
    const totalPayouts = computed(() => moneyFlow.value.totals?.payouts || 0);
    const totalProfit = computed(() => moneyFlow.value.totals?.profit || 0);
    const pendingPayments = computed(() => moneyFlow.value.totals?.pending || 0);

    // ============================================
    // METHODS
    // ============================================
    
    /**
     * Fetch all payment data from API
     */
    async function fetchPayments() {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.get('/api/admin/payments');
            
            if (response.data) {
                clientPayments.value = response.data.client_payments || [];
                caregiverPayments.value = response.data.caregiver_payments || [];
                housekeeperPayments.value = response.data.housekeeper_payments || [];
                marketingCommissions.value = response.data.marketing_commissions || [];
                trainingCommissions.value = response.data.training_commissions || [];
                transactions.value = response.data.transactions || [];
                
                if (response.data.money_flow) {
                    moneyFlow.value = response.data.money_flow;
                }
            }
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to load payment data';
            console.error('Payments fetch error:', err);
        } finally {
            loading.value = false;
        }
    }
    
    /**
     * Process a caregiver payout
     */
    async function processCaregiverPayout(caregiverId, amount, bookingIds = []) {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.post('/api/admin/payments/caregiver-payout', {
                caregiver_id: caregiverId,
                amount,
                booking_ids: bookingIds,
            });
            
            // Refresh payments after successful payout
            await fetchPayments();
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to process payout';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }
    
    /**
     * Process a housekeeper payout
     */
    async function processHousekeeperPayout(housekeeperId, amount, bookingIds = []) {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.post('/api/admin/payments/housekeeper-payout', {
                housekeeper_id: housekeeperId,
                amount,
                booking_ids: bookingIds,
            });
            
            await fetchPayments();
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to process payout';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }
    
    /**
     * Process marketing commission payment
     */
    async function processMarketingCommission(staffId, amount, referralIds = []) {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.post('/api/admin/payments/marketing-commission', {
                staff_id: staffId,
                amount,
                referral_ids: referralIds,
            });
            
            await fetchPayments();
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to process commission';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }
    
    /**
     * Process training commission payment
     */
    async function processTrainingCommission(centerId, amount, trainingIds = []) {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.post('/api/admin/payments/training-commission', {
                center_id: centerId,
                amount,
                training_ids: trainingIds,
            });
            
            await fetchPayments();
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to process commission';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }
    
    /**
     * Issue a refund
     */
    async function issueRefund(paymentId, amount, reason) {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await axios.post('/api/admin/payments/refund', {
                payment_id: paymentId,
                amount,
                reason,
            });
            
            await fetchPayments();
            
            return { success: true, data: response.data };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to issue refund';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }
    
    /**
     * Get payment status color for UI
     */
    function getPaymentStatusColor(status) {
        const colors = {
            'completed': 'success',
            'paid': 'success',
            'pending': 'warning',
            'processing': 'info',
            'failed': 'error',
            'refunded': 'secondary',
            'cancelled': 'error',
        };
        return colors[status?.toLowerCase()] || 'grey';
    }
    
    /**
     * Get transaction type color for UI
     */
    function getTransactionTypeColor(type) {
        const colors = {
            'Payment': 'success',
            'Salary': 'info',
            'Refund': 'warning',
            'Fee': 'error',
            'Commission': 'purple',
        };
        return colors[type] || 'grey';
    }
    
    /**
     * Format currency value
     */
    function formatCurrency(value) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(value || 0);
    }

    // ============================================
    // RETURN
    // ============================================
    return {
        // State
        loading,
        error,
        clientPayments,
        caregiverPayments,
        housekeeperPayments,
        marketingCommissions,
        trainingCommissions,
        transactions,
        moneyFlow,
        pagination,
        
        // Computed
        totalRevenue,
        totalPayouts,
        totalProfit,
        pendingPayments,
        
        // Methods
        fetchPayments,
        processCaregiverPayout,
        processHousekeeperPayout,
        processMarketingCommission,
        processTrainingCommission,
        issueRefund,
        getPaymentStatusColor,
        getTransactionTypeColor,
        formatCurrency,
    };
}
