<template>
  <notification-toast
    v-model="notification.show"
    :type="notification.type"
    :title="notification.title"
    :message="notification.message"
    :timeout="notification.timeout"
  />

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

      <h1 class="form-title">Payment method</h1>

      <form class="payment-form" @submit.prevent="processPayment">
        <!-- Name on Card -->
        <div class="form-group">
          <label>Name on card</label>
          <input 
            type="text" 
            v-model="paymentData.cardName"
            placeholder="Enter cardholder name"
            class="form-input"
            required
          >
        </div>

        <!-- Credit Card Number -->
        <div class="form-group">
          <label>Credit card number</label>
          <div class="input-wrapper">
            <input 
              type="text" 
              v-model="paymentData.cardNumber"
              placeholder="1234 5678 9012 3456"
              maxlength="19"
              class="form-input"
              required
            >
            <i class="bi bi-credit-card card-icon"></i>
          </div>
        </div>

        <!-- Exp Date and CVV -->
        <div class="form-row-2">
          <div class="form-group">
            <label>Exp date (mm/yy)</label>
            <input 
              type="text" 
              v-model="paymentData.expiryDate"
              placeholder="MM/YY"
              maxlength="5"
              class="form-input"
              required
            >
          </div>

          <div class="form-group">
            <label>CVV</label>
            <input 
              type="password" 
              v-model="paymentData.cvc"
              placeholder="•••"
              maxlength="4"
              class="form-input"
              required
            >
          </div>
        </div>

        <!-- Billing Address -->
        <div class="form-group">
          <label>Billing street address</label>
          <input 
            type="text" 
            v-model="paymentData.streetAddress"
            placeholder="Enter address"
            class="form-input"
            required
          >
        </div>

        <!-- ZIP Code -->
        <div class="form-group">
          <label>Billing ZIP code</label>
          <input 
            type="text" 
            v-model="paymentData.zipCode"
            placeholder="10001"
            maxlength="5"
            class="form-input"
            required
          >
        </div>

        <!-- Additional Verification -->
        <div class="verification-section">
          <h2 class="subsection-title">Additional verification</h2>
          <div class="form-group">
            <label>Your birth date (mm/dd/yyyy)</label>
            <input 
              type="text" 
              v-model="paymentData.birthDate"
              placeholder="MM/DD/YYYY"
              maxlength="10"
              class="form-input"
              required
            >
          </div>
        </div>
      </form>
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
        <div class="summary-item">
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
  }
});

const booking = ref(props.bookingData || {});
const processing = ref(false);
const promoCode = ref('');

const paymentData = ref({
  cardName: '',
  cardNumber: '',
  expiryDate: '',
  cvc: '',
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

const rules = {
  required: value => !!value || 'This field is required',
  cardNumber: value => {
    const cleaned = value.replace(/\s/g, '');
    return cleaned.length === 16 || 'Card number must be 16 digits';
  },
  expiry: value => {
    const regex = /^(0[1-9]|1[0-2])\/\d{2}$/;
    return regex.test(value) || 'Format must be MM/YY';
  },
  cvc: value => {
    return (value.length === 3 || value.length === 4) || 'CVC must be 3 or 4 digits';
  },
  zipCode: value => {
    return value.length === 5 || 'ZIP code must be 5 digits';
  }
};

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
  const taxRate = 8.875; // NYC tax rate
  return Math.round(subtotal.value * (taxRate / 100) * 100) / 100;
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
  const discount = booking.value.referral_discount_applied || 5;
  return hours * days * discount;
});

const finalAmount = computed(() => {
  return totalAmount.value - discountAmount.value;
});

const taxRate = computed(() => {
  return 8.875; // NYC tax rate
});

const taxAmount = computed(() => {
  return Math.round(finalAmount.value * (taxRate.value / 100) * 100) / 100;
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

const processPayment = async () => {
  processing.value = true;
  
  // Simulate payment processing
  setTimeout(() => {
    processing.value = false;
    success('Payment processed successfully! This is a prototype - Stripe integration coming soon.', 'Payment Successful');
    
    // Redirect back to dashboard after 2 seconds
    setTimeout(() => {
      goBack();
    }, 2000);
  }, 1500);
};

onMounted(() => {
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
        console.error('Error loading booking:', err);
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
</style>