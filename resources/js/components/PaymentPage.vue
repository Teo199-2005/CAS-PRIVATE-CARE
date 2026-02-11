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

  <!-- Delete Payment Method Confirmation Modal -->
  <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="cancelDelete">
    <div class="modal-content delete-confirm-modal" role="alertdialog" aria-labelledby="delete-modal-title" aria-describedby="delete-modal-desc">
      <div class="modal-header">
        <h3 id="delete-modal-title"><i class="bi bi-exclamation-triangle text-warning"></i> Delete Payment Method</h3>
        <button class="close-btn" @click="cancelDelete" aria-label="Close">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <div class="modal-body">
        <p id="delete-modal-desc" class="delete-prompt">
          Are you sure you want to delete the payment method 
          <strong v-if="deleteConfirmMethod">
            {{ deleteConfirmMethod.card.brand.toUpperCase() }} •••• {{ deleteConfirmMethod.card.last4 }}
          </strong>?
        </p>
        <p class="delete-warning">This action cannot be undone.</p>
        
        <div class="modal-actions">
          <button class="btn-cancel" @click="cancelDelete">Cancel</button>
          <button class="btn-delete" @click="deletePaymentMethod" :disabled="deletingMethodId">
            <i v-if="deletingMethodId" class="bi bi-hourglass-split"></i>
            <i v-else class="bi bi-trash"></i>
            {{ deletingMethodId ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
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
            <div class="method-actions">
              <div class="method-check">
                <i v-if="selectedPaymentMethod?.id === method.id" class="bi bi-check-circle-fill"></i>
                <i v-else class="bi bi-circle"></i>
              </div>
              <button 
                class="delete-method-btn"
                @click.stop="confirmDeletePaymentMethod(method)"
                :disabled="deletingMethodId === method.id"
                :aria-label="'Delete payment method ending in ' + method.card.last4"
                title="Delete payment method"
              >
                <i v-if="deletingMethodId === method.id" class="bi bi-hourglass-split"></i>
                <i v-else class="bi bi-trash"></i>
              </button>
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

    <!-- Right Side: Order Summary -->
    <div class="order-summary-section">
      <div class="summary-card">
        <h2 class="summary-title">Order Summary</h2>

        <!-- Service Details -->
        <div class="summary-item">
          <div class="item-label">
            <span class="item-name">Service:</span>
            <span class="item-detail">{{ booking.duration_days || 15 }} days × {{ extractHours(booking.duty_type) }} hrs × ${{ booking.hourly_rate || 45 }}/hr</span>
          </div>
          <div class="item-value">${{ subtotal.toLocaleString() }}</div>
        </div>

        <div class="divider"></div>

        <!-- Sales Tax -->
        <div class="summary-item" v-if="salesTax > 0">
          <div class="item-label">
            <span class="item-name">Estimated Sales Tax (8.875%):</span>
          </div>
          <div class="item-value">${{ salesTax.toLocaleString() }}</div>
        </div>

        <div class="divider-bold"></div>

        <!-- Total -->
        <div class="summary-total">
          <span class="total-label">Today's Total</span>
          <span class="total-value">${{ totalAmount.toLocaleString() }}</span>
        </div>

        <!-- Promo Code -->
        <div class="promo-section">
          <input 
            type="text" 
            v-model="promoCode"
            placeholder="Redeem promo code"
            class="promo-input"
          >
        </div>

        <!-- Terms -->
        <div class="terms-section">
          <p class="terms-text">
            By subscribing, you authorize us to charge your card, and you agree to our terms.
          </p>
          <p class="terms-text">
            By clicking "Pay Now", your subscription will auto-renew every {{ booking.duration_days || 15 }} days for ${{ totalAmount.toLocaleString() }} plus applicable taxes until you cancel or modify via Account Settings. Once charged, all purchases, including renewals, are non-refundable.
          </p>
        </div>

        <!-- Pay Button -->
        <button 
          class="pay-button" 
          @click="processPayment" 
          :disabled="processing"
          type="button"
        >
          <i class="bi bi-lock-fill" v-if="!processing"></i>
          <span v-if="!processing">Pay Now</span>
          <span v-else>Processing...</span>
        </button>

        <!-- Security Badge -->
        <div class="security-notice">
          <i class="bi bi-shield-check"></i>
          <span>Secure & Encrypted Payment</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import NotificationToast from './shared/NotificationToast.vue';

const props = defineProps({
  bookingData: {
    type: Object,
    required: true
  },
  bookingId: {
    type: String,
    required: true
  },
  stripeKey: {
    type: String,
    required: true
  }
});

const booking = ref(props.bookingData || {});
const processing = ref(false);
const promoCode = ref('');

// Payment method management
const savedPaymentMethods = ref([]);
const selectedPaymentMethod = ref(null);
const showAddCardModal = ref(false);
const showPasswordModal = ref(false);
const confirmPassword = ref('');
const savingCard = ref(false);
const deletingMethodId = ref(null);
const deleteConfirmMethod = ref(null);
const showDeleteConfirm = ref(false);
const newCardData = ref({
  name: '',
  address: '',
  zipCode: '',
  saveForFuture: true
});

// Stripe instances
let stripe = null;
let cardElement = null;

const paymentData = ref({
  cardName: '',
  streetAddress: '',
  zipCode: '',
  birthDate: ''
});

const notification = ref({
  show: false,
  type: 'success',
  title: '',
  message: '',
  timeout: 3000
});

const extractHours = (dutyType) => {
  if (dutyType && typeof dutyType === 'string') {
    const match = dutyType.match(/(\d+)\s*Hours?/i);
    return match ? parseInt(match[1]) : 8;
  }
  return 8;
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
};

const subtotal = computed(() => {
  const hours = extractHours(booking.value.duty_type);
  const days = booking.value.duration_days || 15;
  const rate = booking.value.hourly_rate || 45;
  return hours * days * rate;
});

const salesTax = computed(() => {
  // Healthcare/home care services are tax-exempt in New York
  // No sales tax is charged for personal care services
  return 0;
});

const totalAmount = computed(() => {
  return Math.round((subtotal.value + salesTax.value) * 100) / 100;
});

const hasDiscount = computed(() => {
  return booking.value.referral_code_id || booking.value.referral_discount_applied;
});

const discountAmount = computed(() => {
  if (!hasDiscount.value) return 0;
  const hours = extractHours(booking.value.duty_type);
  const days = booking.value.duration_days || 15;
  const discount = booking.value.referral_discount_applied || 3;
  return hours * days * discount;
});

const finalAmount = computed(() => {
  return totalAmount.value - discountAmount.value;
});

const taxRate = computed(() => {
  return 0; // Healthcare/home care services are tax-exempt in New York
});

const taxAmount = computed(() => {
  return 0; // No tax on healthcare services
});

const finalAmountWithTax = computed(() => {
  return Math.round((finalAmount.value + taxAmount.value) * 100) / 100;
});

const success = (message, title = 'Success') => {
  notification.value = {
    show: true,
    type: 'success',
    title,
    message,
    timeout: 3000
  };
};

const error = (message, title = 'Error') => {
  notification.value = {
    show: true,
    type: 'error',
    title,
    message,
    timeout: 5000
  };
};

const goBack = () => {
  window.location.href = '/client-dashboard';
};

// Payment method helper functions
const getCardIcon = (brand) => {
  const icons = {
    visa: 'bi bi-credit-card-2-front',
    mastercard: 'bi bi-credit-card-2-back',
    amex: 'bi bi-credit-card',
    discover: 'bi bi-credit-card-fill',
    default: 'bi bi-credit-card'
  };
  return icons[brand?.toLowerCase()] || icons.default;
};

const selectPaymentMethod = (method) => {
  selectedPaymentMethod.value = method;
};

const openAddCardModal = () => {
  showAddCardModal.value = true;
};

const closeAddCardModal = () => {
  showAddCardModal.value = false;
  newCardData.value = {
    name: '',
    address: '',
    zipCode: '',
    saveForFuture: true
  };
};

// Confirm delete payment method
const confirmDeletePaymentMethod = (method) => {
  deleteConfirmMethod.value = method;
  showDeleteConfirm.value = true;
};

// Delete payment method
const deletePaymentMethod = async () => {
  if (!deleteConfirmMethod.value) return;
  
  const method = deleteConfirmMethod.value;
  deletingMethodId.value = method.id;
  
  try {
    const response = await fetch(`/api/stripe/payment-methods/${method.id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Remove from local array
      savedPaymentMethods.value = savedPaymentMethods.value.filter(m => m.id !== method.id);
      
      // Clear selection if deleted method was selected
      if (selectedPaymentMethod.value?.id === method.id) {
        selectedPaymentMethod.value = savedPaymentMethods.value[0] || null;
      }
      
      success('Payment method deleted successfully');
    } else {
      error(data.message || 'Failed to delete payment method');
    }
  } catch (err) {
    error('An error occurred while deleting the payment method');
  } finally {
    deletingMethodId.value = null;
    deleteConfirmMethod.value = null;
    showDeleteConfirm.value = false;
  }
};

const cancelDelete = () => {
  deleteConfirmMethod.value = null;
  showDeleteConfirm.value = false;
};

// Fetch saved payment methods
const fetchSavedPaymentMethods = async () => {
  try {
    const response = await fetch('/api/stripe/payment-methods', {
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    });
    
    const data = await response.json();
    
    if (data.success && data.payment_methods) {
      savedPaymentMethods.value = data.payment_methods;
      // Auto-select first method if available
      if (savedPaymentMethods.value.length > 0) {
        selectedPaymentMethod.value = savedPaymentMethods.value[0];
      }
    }
  } catch (err) {
    console.error('Failed to fetch payment methods:', err);
  }
};

// Save new payment method
const saveNewPaymentMethod = async () => {
  savingCard.value = true;
  
  try {
    // Create payment method with Stripe
    const { error: stripeError, paymentMethod } = await stripe.createPaymentMethod({
      type: 'card',
      card: modalCardElement,
      billing_details: {
        name: newCardData.value.name,
        address: {
          line1: newCardData.value.address,
          postal_code: newCardData.value.zipCode
        }
      }
    });
    
    if (stripeError) {
      error(stripeError.message || 'Failed to save payment method');
      savingCard.value = false;
      return;
    }
    
    // Attach to customer on backend
    const response = await fetch('/api/stripe/attach-payment-method', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        payment_method_id: paymentMethod.id
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Refresh the saved methods list
      await fetchSavedPaymentMethods();
      closeAddCardModal();
      success('Payment method added successfully');
    } else {
      error(data.message || 'Failed to save payment method');
    }
  } catch (err) {
    error('An error occurred while saving the payment method');
  } finally {
    savingCard.value = false;
  }
};

let modalCardElement = null;

// Initialize Stripe Elements
const initializeStripe = () => {
  try {
    
    // Check if Stripe.js is loaded
    if (typeof window.Stripe === 'undefined') {
      error('Payment system not loaded. Please refresh the page.');
      return;
    }
    
    // Check if we have a Stripe key
    if (!props.stripeKey || props.stripeKey.trim() === '') {
      error('Payment configuration error. Please contact support.');
      return;
    }
    
    // Initialize Stripe with your publishable key
    stripe = window.Stripe(props.stripeKey);
    
    // Create an instance of Elements
    const elements = stripe.elements();
    
    // Custom styling to match your design
    const style = {
      base: {
        color: '#1f2937',
        fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        fontSmoothing: 'antialiased',
        fontSize: '15px',
        '::placeholder': {
          color: '#9ca3af'
        },
        lineHeight: '48px',
        padding: '12px 16px'
      },
      invalid: {
        color: '#ef4444',
        iconColor: '#ef4444'
      }
    };
    
    // Create and mount the Card Element
    cardElement = elements.create('card', { 
      style: style,
      hidePostalCode: true // We'll use our own ZIP code field
    });
    
    cardElement.mount('#card-element');
    
    // Handle real-time validation errors from the Card Element
    cardElement.on('change', (event) => {
      const displayError = document.getElementById('card-errors');
      if (event.error) {
        displayError.textContent = event.error.message;
      } else {
        displayError.textContent = '';
      }
    });
    
  } catch (err) {
    error('Failed to load payment form. Please refresh the page.');
  }
};

// Process Payment with Stripe
const processPayment = async () => {
  if (processing.value) return;
  
  // Validate form
  if (!paymentData.value.cardName) {
    error('Please enter cardholder name');
    return;
  }
  
  if (!paymentData.value.streetAddress) {
    error('Please enter billing address');
    return;
  }
  
  if (!paymentData.value.zipCode || paymentData.value.zipCode.length !== 5) {
    error('Please enter a valid 5-digit ZIP code');
    return;
  }
  
  processing.value = true;
  
  try {
    
    // Create Payment Method with Stripe
    const { paymentMethod, error: stripeError } = await stripe.createPaymentMethod({
      type: 'card',
      card: cardElement,
      billing_details: {
        name: paymentData.value.cardName,
        address: {
          line1: paymentData.value.streetAddress,
          postal_code: paymentData.value.zipCode
        }
      }
    });
    
    if (stripeError) {
      error(stripeError.message || 'Payment failed. Please check your card details.');
      processing.value = false;
      return;
    }

// Send payment method to your backend
    const response = await fetch('/api/stripe/setup-intent', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        payment_method_id: paymentMethod.id,
        booking_id: props.bookingId,
        amount: Math.round(totalAmount.value * 100), // Amount in cents
        cardholder_name: paymentData.value.cardName,
        billing_address: paymentData.value.streetAddress,
        zip_code: paymentData.value.zipCode
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      success('Payment processed successfully! Redirecting...', 'Payment Successful');
      
      // Redirect to success page or dashboard
      setTimeout(() => {
        window.location.href = `/booking-confirmation/${props.bookingId}`;
      }, 2000);
    } else {
      error(data.message || 'Payment failed. Please try again.');
    }
    
  } catch (err) {
    error('An error occurred while processing your payment. Please try again.');
  } finally {
    processing.value = false;
  }
};

onMounted(() => {
  // Wait for Stripe.js to load (it's loaded from CDN)
  const initStripeWhenReady = () => {
    if (typeof window.Stripe !== 'undefined') {
      // Stripe.js is loaded, initialize it
      initializeStripe();
    } else {
      // Wait a bit longer and try again
      setTimeout(initStripeWhenReady, 100);
    }
  };
  
  initStripeWhenReady();
  
  // Fetch saved payment methods
  fetchSavedPaymentMethods();
  
  // Load booking data if not provided
  if (!booking.value || Object.keys(booking.value).length === 0) {
    // Fetch booking data
    fetch(`/api/bookings/${props.bookingId}`)
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          booking.value = data.data;
        }
      })
      .catch(err => {
        error('Failed to load booking details');
      });
  }
});
</script>

<style scoped>
.payment-container {
  display: grid;
  grid-template-columns: 1fr 480px;
  gap: 2rem;
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  min-height: 100vh;
  align-items: start;
}

/* Left Side - Payment Form */
.payment-form-section {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  max-height: calc(100vh - 4rem);
  overflow-y: auto;
}

/* Custom Scrollbar */
.payment-form-section::-webkit-scrollbar {
  width: 8px;
}

.payment-form-section::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.payment-form-section::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.payment-form-section::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.back-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: none;
  border: none;
  color: #3b82f6;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
  margin-bottom: 1.25rem;
  transition: color 0.2s;
}

.back-button:hover {
  color: #1e40af;
}

.back-button i {
  font-size: 1rem;
}

.logo-section {
  text-align: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.logo {
  height: 70px;
  width: auto;
  display: block;
  margin: 0 auto 0.75rem auto;
}

.company-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.25rem;
  letter-spacing: -0.025em;
}

.company-tagline {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

.form-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1.5rem;
  text-align: center;
}

.payment-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.form-group label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #475569;
}

.form-input {
  width: 100%;
  padding: 0.75rem 0.875rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.9375rem;
  color: #1e293b;
  transition: all 0.2s;
  background: white;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input::placeholder {
  color: #94a3b8;
}

/* Stripe Element Styling */
.stripe-element {
  width: 100%;
  padding: 0.75rem 0.875rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  background: white;
  transition: all 0.2s;
  min-height: 48px;
}

.stripe-element:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.card-errors {
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 0.5rem;
  min-height: 20px;
  font-weight: 500;
}

.input-wrapper {
  position: relative;
}

.card-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 1.25rem;
}

.form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.verification-section {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 2px solid #f1f5f9;
}

.subsection-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1rem;
}

/* Right Side - Order Summary */
.order-summary-section {
  position: sticky;
  top: 2rem;
  height: fit-content;
}

.summary-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.summary-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f1f5f9;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  gap: 1rem;
}

.item-label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  flex: 1;
}

.item-name {
  font-size: 0.9375rem;
  font-weight: 600;
  color: #475569;
}

.item-detail {
  font-size: 0.8125rem;
  color: #64748b;
}

.item-value {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
}

.divider {
  height: 1px;
  background: #e2e8f0;
  margin: 1rem 0;
}

.divider-bold {
  height: 2px;
  background: #cbd5e1;
  margin: 1.5rem 0;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.total-label {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
}

.total-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #3b82f6;
}

.promo-section {
  margin-bottom: 1.5rem;
}

.promo-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.9375rem;
  color: #1e293b;
  transition: all 0.2s;
}

.promo-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.promo-input::placeholder {
  color: #94a3b8;
}

.terms-section {
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
}

.terms-text {
  font-size: 0.8125rem;
  color: #475569;
  line-height: 1.6;
  margin-bottom: 0.75rem;
}

.terms-text:last-child {
  margin-bottom: 0;
}

.pay-button {
  width: 100%;
  padding: 1.125rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.0625rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.625rem;
  transition: all 0.3s;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.pay-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 24px rgba(59, 130, 246, 0.4);
}

.pay-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.pay-button i {
  font-size: 1.125rem;
}

.security-notice {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 1rem;
  padding: 0.75rem;
  background: #dcfce7;
  border: 1px solid #86efac;
  border-radius: 10px;
}

.security-notice i {
  color: #16a34a;
  font-size: 1.125rem;
}

.security-notice span {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #15803d;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .payment-container {
    grid-template-columns: 1fr;
    gap: 2rem;
  }

  .order-summary-section {
    position: relative;
    top: 0;
  }
}

@media (max-width: 640px) {
  .payment-container {
    padding: 1rem;
  }

  .payment-form-section,
  .summary-card {
    padding: 1.5rem;
  }

  .form-row-2 {
    grid-template-columns: 1fr;
  }

  .logo {
    height: 60px;
  }

  .form-title {
    font-size: 1.5rem;
  }

  .summary-title {
    font-size: 1.25rem;
  }
}

/* Payment Method Actions */
.method-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.delete-method-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.2s ease;
}

.delete-method-btn:hover:not(:disabled) {
  background: #fee2e2;
  color: #dc2626;
}

.delete-method-btn:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

/* Delete Confirmation Modal */
.delete-confirm-modal {
  max-width: 420px;
}

.delete-prompt {
  font-size: 1rem;
  color: #475569;
  margin-bottom: 0.5rem;
}

.delete-warning {
  font-size: 0.875rem;
  color: #dc2626;
  margin-bottom: 1.5rem;
}

.btn-delete {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-delete:hover:not(:disabled) {
  background: #b91c1c;
}

.btn-delete:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.text-warning {
  color: #f59e0b;
}
</style>