<template>
  <notification-toast
    v-model="notification.show"
    :type="notification.type"
    :title="notification.title"
    :message="notification.message"
    :timeout="notification.timeout"
  />

  <!-- Password Confirmation Modal -->
  <div v-if="showPasswordModal" class="modal-overlay" @click.self="showPasswordModal = false">
    <div class="modal-content password-modal">
      <div class="modal-header">
        <h3><i class="bi bi-shield-lock"></i> Confirm Payment</h3>
        <button class="close-btn" @click="showPasswordModal = false">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <div class="modal-body">
        <p class="password-prompt">Please enter your password to authorize this payment of <strong>${{ totalAmount.toLocaleString() }}</strong></p>
        
        <div class="form-group">
          <label>Password</label>
          <input 
            type="password" 
            v-model="confirmPassword"
            placeholder="Enter your password"
            class="form-input"
            @keyup.enter="confirmPaymentWithPassword"
            ref="passwordInput"
          >
        </div>
        
        <div class="modal-actions">
          <button class="btn-cancel" @click="showPasswordModal = false">Cancel</button>
          <button class="btn-confirm" @click="confirmPaymentWithPassword" :disabled="!confirmPassword || processing">
            <i class="bi bi-lock-fill"></i>
            {{ processing ? 'Processing...' : 'Authorize Payment' }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Payment Method Modal -->
  <div v-if="showAddCardModal" class="modal-overlay" @click.self="showAddCardModal = false">
    <div class="modal-content add-card-modal">
      <div class="modal-header">
        <h3><i class="bi bi-credit-card"></i> Add Payment Method</h3>
        <button class="close-btn" @click="closeAddCardModal">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="saveNewPaymentMethod">
          <!-- Cardholder Name -->
          <div class="form-group">
            <label>Cardholder Name</label>
            <input 
              type="text" 
              v-model="newCardData.name"
              placeholder="John Doe"
              class="form-input"
              required
            >
          </div>

          <!-- Stripe Card Element in Modal -->
          <div class="form-group">
            <label>Card Information</label>
            <div id="card-element-modal" class="stripe-element">
              <!-- Stripe Card Element will be mounted here -->
            </div>
            <div id="card-errors-modal" class="card-errors"></div>
          </div>

          <!-- Billing Address -->
          <div class="form-group">
            <label>Billing Address</label>
            <input 
              type="text" 
              v-model="newCardData.address"
              placeholder="123 Main Street"
              class="form-input"
              required
            >
          </div>

          <!-- ZIP Code -->
          <div class="form-group">
            <label>ZIP Code</label>
            <input 
              type="text" 
              v-model="newCardData.zipCode"
              placeholder="10001"
              maxlength="5"
              class="form-input"
              required
            >
          </div>

          <!-- Save for Future Use -->
          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="newCardData.saveForFuture" checked>
              <span>Save this card for future payments</span>
            </label>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="closeAddCardModal">Cancel</button>
            <button type="submit" class="btn-confirm" :disabled="savingCard">
              <i class="bi bi-plus-circle"></i>
              {{ savingCard ? 'Saving...' : 'Add Card' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="payment-container">
    <!-- Left Side: Payment Form -->
    <div class="payment-form-section">
      <!-- Back Link -->
      <button class="back-button" @click="goBack">
        <i class="bi bi-chevron-left"></i>
        <span>Back to Dashboard</span>
      </button>

      <!-- Logo -->
      <div class="logo-section">
        <img src="/logo flower.png" alt="CAS Private Care" class="logo">
        <div class="company-name">CAS Private Care LLC</div>
        <div class="company-tagline">Professional Caregiving Services</div>
      </div>

      <h1 class="form-title">Payment Method</h1>

      <!-- Saved Payment Methods Section -->
      <div v-if="savedPaymentMethods.length > 0" class="saved-methods-section">
        <h3 class="section-subtitle">
          <i class="bi bi-credit-card-2-front"></i> Saved Payment Methods
        </h3>
        
        <div class="payment-methods-list">
          <div 
            v-for="method in savedPaymentMethods" 
            :key="method.id"
            class="payment-method-card"
            :class="{ 'selected': selectedPaymentMethod?.id === method.id }"
            @click="selectPaymentMethod(method)"
          >
            <div class="method-icon">
              <i :class="getCardIcon(method.card.brand)"></i>
            </div>
            <div class="method-details">
              <div class="method-brand">{{ method.card.brand.toUpperCase() }} •••• {{ method.card.last4 }}</div>
              <div class="method-expiry">Expires {{ method.card.exp_month }}/{{ method.card.exp_year }}</div>
            </div>
            <div class="method-check">
              <i v-if="selectedPaymentMethod?.id === method.id" class="bi bi-check-circle-fill"></i>
              <i v-else class="bi bi-circle"></i>
            </div>
          </div>
        </div>

        <button class="add-method-btn" @click="openAddCardModal">
          <i class="bi bi-plus-circle"></i> Add New Payment Method
        </button>
      </div>

      <!-- No Saved Methods - Show Add Card Button -->
      <div v-else class="no-saved-methods">
        <div class="empty-state">
          <i class="bi bi-credit-card-2-back"></i>
          <p>No saved payment methods</p>
          <button class="add-method-btn-primary" @click="openAddCardModal">
            <i class="bi bi-plus-circle"></i> Add Payment Method
          </button>
        </div>
      </div>

      <!-- Selected Method Summary -->
      <div v-if="selectedPaymentMethod" class="selected-summary">
        <div class="summary-box">
          <i class="bi bi-check-circle-fill text-success"></i>
          <span>Payment will be charged to {{ selectedPaymentMethod.card.brand.toUpperCase() }} ending in {{ selectedPaymentMethod.card.last4 }}</span>
        </div>
      </div>
    </div>

    <!-- Right Side: Booking Summary -->
    <div class="booking-summary-section">
      <h2 class="summary-title">Booking Summary</h2>

      <div class="booking-details" v-if="bookingDetails">
        <div class="detail-row">
          <span class="label">Service Type:</span>
          <span class="value">{{ bookingDetails.duty_type || 'Standard Care' }}</span>
        </div>
        
        <div class="detail-row">
          <span class="label">Duration:</span>
          <span class="value">{{ bookingDetails.duration_days || 15 }} days</span>
        </div>
        
        <div class="detail-row">
          <span class="label">Hourly Rate:</span>
          <span class="value">${{ bookingDetails.hourly_rate || 40 }}/hr</span>
        </div>

        <div class="detail-row">
          <span class="label">Total Hours:</span>
          <span class="value">{{ (bookingDetails.hours || 0) }} hours</span>
        </div>

        <div class="divider"></div>

        <div class="detail-row total">
          <span class="label">Total Amount:</span>
          <span class="value">${{ totalAmount.toLocaleString() }}</span>
        </div>

        <!-- Pay Button -->
        <button 
          class="pay-button" 
          @click="handlePayNow" 
          :disabled="!selectedPaymentMethod || processing"
        >
          <i class="bi bi-lock-fill"></i>
          {{ processing ? 'Processing...' : 'Pay Now' }}
        </button>

        <div class="security-badge">
          <i class="bi bi-shield-check"></i>
          <span>Secure & Encrypted</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import axios from 'axios';
import NotificationToast from './shared/NotificationToast.vue';

const props = defineProps({
  stripeKey: {
    type: String,
    required: true
  }
});

// Stripe instances
let stripe = null;
let cardElementModal = null;

// UI State
const notification = ref({
  show: false,
  type: 'success',
  title: '',
  message: '',
  timeout: 5000
});

const processing = ref(false);
const savingCard = ref(false);
const showPasswordModal = ref(false);
const showAddCardModal = ref(false);
const confirmPassword = ref('');
const passwordInput = ref(null);

// Payment Methods
const savedPaymentMethods = ref([]);
const selectedPaymentMethod = ref(null);

// New Card Data
const newCardData = ref({
  name: '',
  address: '',
  zipCode: '',
  saveForFuture: true
});

// Booking Data
const bookingId = ref(null);
const bookingDetails = ref(null);

// ============================================
// 1. STRIPE INITIALIZATION
// ============================================

const initStripeWhenReady = () => {
  if (typeof window.Stripe !== 'undefined') {
    initializeStripe();
  } else {
    setTimeout(initStripeWhenReady, 100);
  }
};

const initializeStripe = () => {
  try {
    if (typeof window.Stripe === 'undefined') {
      showNotification('error', 'Payment System Error', 'Failed to load payment system');
      return;
    }

    if (!props.stripeKey || props.stripeKey.trim() === '') {
      showNotification('error', 'Configuration Error', 'Payment system not configured');
      return;
    }

    stripe = window.Stripe(props.stripeKey);
  } catch (error) {
    showNotification('error', 'Payment System Error', 'Failed to initialize payment system');
  }
};

// ============================================
// 2. LOAD SAVED PAYMENT METHODS
// ============================================

const loadSavedPaymentMethods = async () => {
  try {
    const response = await axios.get('/api/client/payment-methods');
    savedPaymentMethods.value = response.data.payment_methods || [];
    
    // Auto-select first method if available
    if (savedPaymentMethods.value.length > 0) {
      selectedPaymentMethod.value = savedPaymentMethods.value[0];
    }
  } catch (error) {
    savedPaymentMethods.value = [];
  }
};

// ============================================
// 3. PAYMENT METHOD SELECTION
// ============================================

const selectPaymentMethod = (method) => {
  selectedPaymentMethod.value = method;
};

const getCardIcon = (brand) => {
  const icons = {
    'visa': 'bi bi-credit-card',
    'mastercard': 'bi bi-credit-card-2-front',
    'amex': 'bi bi-credit-card-2-back',
    'discover': 'bi bi-credit-card',
    'diners': 'bi bi-credit-card',
    'jcb': 'bi bi-credit-card',
    'unionpay': 'bi bi-credit-card'
  };
  return icons[brand?.toLowerCase()] || 'bi bi-credit-card';
};

// ============================================
// 4. ADD NEW PAYMENT METHOD MODAL
// ============================================

const openAddCardModal = () => {
  showAddCardModal.value = true;
  
  nextTick(() => {
    // Mount Stripe Element in modal
    if (!cardElementModal && stripe) {
      const elements = stripe.elements();
      cardElementModal = elements.create('card', {
        style: {
          base: {
            fontSize: '16px',
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            '::placeholder': { color: '#aab7c4' },
          },
          invalid: {
            color: '#fa755a',
            iconColor: '#fa755a',
          },
        },
      });
      
      cardElementModal.mount('#card-element-modal');
      
      // Display validation errors
      cardElementModal.on('change', (event) => {
        const displayError = document.getElementById('card-errors-modal');
        if (event.error) {
          displayError.textContent = event.error.message;
        } else {
          displayError.textContent = '';
        }
      });
    }
  });
};

const closeAddCardModal = () => {
  showAddCardModal.value = false;
  
  // Reset form
  newCardData.value = {
    name: '',
    address: '',
    zipCode: '',
    saveForFuture: true
  };
  
  // Unmount and reset card element
  if (cardElementModal) {
    cardElementModal.unmount();
    cardElementModal = null;
  }
};

const saveNewPaymentMethod = async () => {
  if (!stripe || !cardElementModal) {
    showNotification('error', 'Error', 'Payment system not ready');
    return;
  }

  savingCard.value = true;

  try {
    // Create SetupIntent for saving card (not charging)
    const setupResponse = await axios.post('/api/stripe/create-setup-intent');
    const { client_secret } = setupResponse.data;

    // Confirm card setup
    const { error, setupIntent } = await stripe.confirmCardSetup(client_secret, {
      payment_method: {
        card: cardElementModal,
        billing_details: {
          name: newCardData.value.name,
          address: {
            line1: newCardData.value.address,
            postal_code: newCardData.value.zipCode,
          },
        },
      },
    });

    if (error) {
      showNotification('error', 'Card Validation Failed', error.message);
      savingCard.value = false;
      return;
    }

    // Save payment method to customer
    if (newCardData.value.saveForFuture) {
      await axios.post('/api/stripe/attach-payment-method', {
        payment_method_id: setupIntent.payment_method
      });
    }

    showNotification('success', 'Card Added', 'Payment method saved successfully');
    
    // Reload saved methods
    await loadSavedPaymentMethods();
    
    closeAddCardModal();
  } catch (error) {
    showNotification('error', 'Save Failed', error.response?.data?.message || 'Failed to save payment method');
  } finally {
    savingCard.value = false;
  }
};

// ============================================
// 5. PAYMENT PROCESSING
// ============================================

const totalAmount = computed(() => {
  if (!bookingDetails.value) return 0;
  const hours = bookingDetails.value.hours || 0;
  const hourlyRate = bookingDetails.value.hourly_rate || 40;
  return hours * hourlyRate;
});

const handlePayNow = () => {
  if (!selectedPaymentMethod.value) {
    showNotification('warning', 'No Payment Method', 'Please select or add a payment method');
    return;
  }

  // Show password confirmation modal
  showPasswordModal.value = true;
  
  nextTick(() => {
    passwordInput.value?.focus();
  });
};

const confirmPaymentWithPassword = async () => {
  if (!confirmPassword.value) {
    showNotification('warning', 'Password Required', 'Please enter your password');
    return;
  }

  processing.value = true;

  try {
    // Verify password and process payment
    const response = await axios.post('/api/stripe/charge-saved-method', {
      booking_id: bookingId.value,
      payment_method_id: selectedPaymentMethod.value.id,
      password: confirmPassword.value,
      amount: totalAmount.value
    });

    if (response.data.success) {
      showNotification('success', 'Payment Successful', 'Your payment has been processed successfully');
      
      // Close modal and redirect after 2 seconds
      showPasswordModal.value = false;
      setTimeout(() => {
        window.location.href = '/client-dashboard';
      }, 2000);
    } else {
      showNotification('error', 'Payment Failed', response.data.message || 'Payment could not be processed');
    }
  } catch (error) {
    
    if (error.response?.status === 401) {
      showNotification('error', 'Incorrect Password', 'The password you entered is incorrect');
    } else {
      showNotification('error', 'Payment Failed', error.response?.data?.message || 'Failed to process payment');
    }
  } finally {
    processing.value = false;
    confirmPassword.value = '';
  }
};

// ============================================
// 6. LOAD BOOKING DETAILS
// ============================================

const loadBookingDetails = async () => {
  try {
    const response = await axios.get(`/api/bookings/${bookingId.value}`);
    bookingDetails.value = response.data.booking;
  } catch (error) {
    showNotification('error', 'Error', 'Failed to load booking details');
  }
};

// ============================================
// 7. UTILITY FUNCTIONS
// ============================================

const showNotification = (type, title, message) => {
  notification.value = {
    show: true,
    type,
    title,
    message,
    timeout: 5000
  };
};

const goBack = () => {
  window.location.href = '/client-dashboard';
};

// ============================================
// 8. LIFECYCLE
// ============================================

onMounted(() => {
  // Get booking ID from URL
  const urlParams = new URLSearchParams(window.location.search);
  bookingId.value = urlParams.get('booking_id');

  if (!bookingId.value) {
    showNotification('error', 'Invalid Request', 'No booking ID provided');
    return;
  }

  // Initialize Stripe
  initStripeWhenReady();
  
  // Load data
  loadSavedPaymentMethods();
  loadBookingDetails();
});
</script>

<style scoped>
/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

/* Modal Content */
.modal-content {
  background: white;
  border-radius: 16px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  font-size: 20px;
  font-weight: 600;
  color: #111827;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #6b7280;
  padding: 4px;
  transition: color 0.2s;
}

.close-btn:hover {
  color: #111827;
}

.modal-body {
  padding: 24px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

.btn-cancel, .btn-confirm {
  flex: 1;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-cancel {
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  color: #374151;
}

.btn-cancel:hover {
  background: #e5e7eb;
}

.btn-confirm {
  background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
  border: none;
  color: white;
}

.btn-confirm:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
}

.btn-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Password Modal Specific */
.password-prompt {
  color: #374151;
  margin-bottom: 20px;
  line-height: 1.6;
}

/* Form Elements */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
}

.form-input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 15px;
  color: #111827;
  transition: all 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.stripe-element {
  padding: 12px 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: white;
  transition: border-color 0.2s;
}

.stripe-element:focus-within {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.card-errors {
  color: #ef4444;
  font-size: 13px;
  margin-top: 8px;
  min-height: 20px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  color: #374151;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

/* Payment Container */
.payment-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 100vh;
  gap: 40px;
  padding: 40px;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
}

@media (max-width: 1024px) {
  .payment-container {
    grid-template-columns: 1fr;
    padding: 20px;
  }
}

/* Payment Form Section */
.payment-form-section {
  background: white;
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.back-button {
  display: flex;
  align-items: center;
  gap: 8px;
  background: none;
  border: none;
  color: #2563eb;
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  padding: 8px 0;
  margin-bottom: 24px;
  transition: color 0.2s;
}

.back-button:hover {
  color: #1e40af;
}

.logo-section {
  text-align: center;
  margin-bottom: 32px;
}

.logo {
  width: 80px;
  height: 80px;
  object-fit: contain;
  margin-bottom: 12px;
}

.company-name {
  font-size: 22px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 4px;
}

.company-tagline {
  font-size: 14px;
  color: #6b7280;
}

.form-title {
  font-size: 28px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 24px;
}

/* Saved Methods Section */
.saved-methods-section {
  margin-top: 24px;
}

.section-subtitle {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.payment-methods-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 16px;
}

.payment-method-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.payment-method-card:hover {
  border-color: #2563eb;
  background: #f0f9ff;
}

.payment-method-card.selected {
  border-color: #2563eb;
  background: #eff6ff;
}

.method-icon {
  font-size: 32px;
  color: #2563eb;
}

.method-details {
  flex: 1;
}

.method-brand {
  font-size: 15px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 4px;
}

.method-expiry {
  font-size: 13px;
  color: #6b7280;
}

.method-check {
  font-size: 24px;
  color: #2563eb;
}

.add-method-btn {
  width: 100%;
  padding: 12px;
  background: #f3f4f6;
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  color: #374151;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.add-method-btn:hover {
  background: #e5e7eb;
  border-color: #9ca3af;
}

/* No Saved Methods */
.no-saved-methods {
  padding: 40px 20px;
}

.empty-state {
  text-align: center;
}

.empty-state i {
  font-size: 64px;
  color: #d1d5db;
  margin-bottom: 16px;
}

.empty-state p {
  font-size: 16px;
  color: #6b7280;
  margin-bottom: 20px;
}

.add-method-btn-primary {
  padding: 14px 28px;
  background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
  border: none;
  border-radius: 12px;
  color: white;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.add-method-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
}

/* Selected Summary */
.selected-summary {
  margin-top: 24px;
}

.summary-box {
  padding: 16px;
  background: #ecfdf5;
  border: 1px solid #10b981;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  color: #047857;
  font-size: 14px;
}

.text-success {
  color: #10b981;
  font-size: 20px;
}

/* Booking Summary Section */
.booking-summary-section {
  background: white;
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  height: fit-content;
  position: sticky;
  top: 40px;
}

.summary-title {
  font-size: 24px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 24px;
}

.booking-details {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 15px;
}

.detail-row .label {
  color: #6b7280;
  font-weight: 500;
}

.detail-row .value {
  color: #111827;
  font-weight: 600;
}

.divider {
  height: 1px;
  background: #e5e7eb;
  margin: 8px 0;
}

.detail-row.total {
  font-size: 20px;
  font-weight: 700;
  color: #111827;
  padding-top: 16px;
  border-top: 2px solid #e5e7eb;
}

.pay-button {
  width: 100%;
  padding: 16px;
  margin-top: 24px;
  background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
  border: none;
  border-radius: 12px;
  color: white;
  font-size: 18px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.pay-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
}

.pay-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.security-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 16px;
  color: #10b981;
  font-size: 14px;
  font-weight: 500;
}

.security-badge i {
  font-size: 18px;
}
</style>
