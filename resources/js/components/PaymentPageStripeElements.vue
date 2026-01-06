<template>
  <notification-toast
    v-model="notification.show"
    :type="notification.type"
    :title="notification.title"
    :message="notification.message"
    :timeout="notification.timeout"
  />

  <div class="checkout-container">
    <!-- Left Side: Order Summary -->
    <div class="order-summary-section">
      <button class="back-button" @click="goBack">
        <i class="bi bi-chevron-left"></i>
        <span>Back to Dashboard</span>
      </button>

      <div class="logo-section">
        <img src="/logo flower.png" alt="CAS Private Care" class="logo">
        <div class="company-name">CAS Private Care LLC</div>
      </div>

      <h1 class="checkout-title">Complete Your Booking</h1>

      <!-- Service Card -->
      <div v-if="bookingDetails" class="service-card">
        <div class="service-header">
          <div class="service-icon">
            <i class="bi bi-heart-pulse"></i>
          </div>
          <div class="service-info">
            <h3 class="service-name">{{ bookingDetails.duty_type || 'Caregiving Service' }}</h3>
            <p class="service-description">Professional in-home care</p>
            <p class="service-billing">
              {{ bookingDetails.duration_days || 15 }} days × 
              {{ extractHoursFromDutyType(bookingDetails.duty_type) }} hrs/day × 
              ${{ bookingDetails.hourly_rate || 40 }}/hr
            </p>
          </div>
          <div class="service-price">
            <span class="price-amount">${{ totalAmount.toFixed(2) }}</span>
          </div>
        </div>

        <!-- Toggle Annual Billing (Optional) -->
        <div class="billing-toggle" v-if="false">
          <label class="toggle-container">
            <input type="checkbox" v-model="annualBilling">
            <span class="toggle-slider"></span>
          </label>
          <div class="toggle-info">
            <span class="toggle-label">Save $48 with annual billing</span>
            <span class="toggle-price">$16.00/month</span>
          </div>
        </div>
      </div>

      <!-- Pricing Breakdown -->
      <div class="pricing-breakdown">
        <div class="price-row">
          <span class="price-label">Subtotal</span>
          <span class="price-value">${{ subtotal.toFixed(2) }}</span>
        </div>
        <div class="price-row" v-if="taxAmount > 0">
          <span class="price-label">Tax <i class="bi bi-info-circle" title="Sales tax"></i></span>
          <span class="price-value">${{ taxAmount.toFixed(2) }}</span>
        </div>
        <div class="price-row total-row">
          <span class="price-label">Total due today</span>
          <span class="price-value">${{ totalAmount.toFixed(2) }}</span>
        </div>
      </div>
    </div>

    <!-- Right Side: Payment Form -->
    <div class="payment-section">
      <div class="payment-form-card">
        <h2 class="payment-title">Payment Information</h2>

        <!-- Email Input -->
        <div class="form-group">
          <label class="form-label">Email</label>
          <input 
            type="email" 
            v-model="customerEmail"
            class="form-input"
            placeholder="your@email.com"
            required
          >
          <button v-if="false" class="change-link">Continue with Link</button>
        </div>

        <!-- Stripe Payment Element (Replaces everything) -->
        <div class="form-group">
          <label class="form-label">Payment method</label>
          
          <!-- Stripe Payment Element Container -->
          <div id="payment-element" class="stripe-payment-element">
            <!-- Stripe will inject Payment Element here -->
            <!-- This includes:
                 - Card input
                 - Stripe Link (saved payment methods)
                 - Alternative payment methods (if enabled)
                 - Saved cards display
            -->
          </div>
          
          <div id="payment-element-errors" class="payment-errors"></div>
        </div>

        <!-- Terms and Conditions -->
        <div class="terms-section">
          <p class="terms-text">
            By subscribing, you authorize us to charge you according to the terms until you cancel.
          </p>
        </div>

        <!-- Subscribe Button -->
        <button 
          class="subscribe-button" 
          @click="handleSubmit"
          :disabled="processing || !isFormReady"
        >
          <span v-if="!processing">Subscribe</span>
          <span v-else>
            <i class="bi bi-arrow-clockwise spinner"></i>
            Processing...
          </span>
        </button>

        <!-- Stripe Badge -->
        <div class="powered-by">
          <span>Powered by</span>
          <img src="https://stripe.com/img/v3/home/twitter.png" alt="Stripe" class="stripe-logo">
          <span class="stripe-text">stripe</span>
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
let elements = null;
let paymentElement = null;

// State
const notification = ref({
  show: false,
  type: 'success',
  title: '',
  message: '',
  timeout: 5000
});

const processing = ref(false);
const isFormReady = ref(false);
const customerEmail = ref('');
const annualBilling = ref(false);

// Booking Data
const bookingId = ref(null);
const bookingDetails = ref(null);
const clientSecret = ref(null);

// ============================================
// 1. COMPUTED PROPERTIES
// ============================================

const subtotal = computed(() => {
  if (!bookingDetails.value) return 0;
  
  // Try to get hours from multiple sources
  let hours = bookingDetails.value.hours || 0;
  
  // If no hours, calculate from duration_days and duty_type
  if (hours === 0) {
    const days = bookingDetails.value.duration_days || 15;
    const hoursPerDay = extractHoursFromDutyType(bookingDetails.value.duty_type) || 8;
    hours = days * hoursPerDay;
  }
  
  const hourlyRate = bookingDetails.value.hourly_rate || 40;
  return hours * hourlyRate;
});

const taxAmount = computed(() => {
  // Healthcare/home care services are tax-exempt in New York
  return 0;
});

const totalAmount = computed(() => {
  return subtotal.value + taxAmount.value;
});

// Helper function to extract hours from duty type string
const extractHoursFromDutyType = (dutyType) => {
  if (!dutyType) return 8;
  if (typeof dutyType === 'string') {
    const match = dutyType.match(/(\d+)\s*Hours?/i);
    return match ? parseInt(match[1]) : 8;
  }
  return 8;
};

// Calculate total hours for the booking
const calculateTotalHours = () => {
  if (!bookingDetails.value) return 0;
  const days = bookingDetails.value.duration_days || 15;
  const hoursPerDay = extractHoursFromDutyType(bookingDetails.value.duty_type);
  return days * hoursPerDay;
};

// ============================================
// 2. STRIPE INITIALIZATION
// ============================================

const initStripeWhenReady = () => {
  if (typeof window.Stripe !== 'undefined') {
    initializeStripe();
  } else {
    setTimeout(initStripeWhenReady, 100);
  }
};

const initializeStripe = async () => {
  try {
    if (!props.stripeKey || props.stripeKey.trim() === '') {
      showNotification('error', 'Configuration Error', 'Payment system not configured');
      return;
    }

    // Initialize Stripe
    stripe = window.Stripe(props.stripeKey);
    
    // Create Payment Intent first
    await createPaymentIntent();
    
    if (!clientSecret.value) {
      showNotification('error', 'Error', 'Failed to initialize payment');
      return;
    }

    // Create Elements instance with client secret
    elements = stripe.elements({
      clientSecret: clientSecret.value,
      appearance: {
        theme: 'stripe', // Can be 'stripe', 'night', 'flat'
        variables: {
          colorPrimary: '#0F172A',
          colorBackground: '#ffffff',
          colorText: '#1f2937',
          colorDanger: '#df1b41',
          fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
          spacingUnit: '4px',
          borderRadius: '8px',
        },
        rules: {
          '.Input': {
            border: '1px solid #d1d5db',
            boxShadow: 'none',
          },
          '.Input:focus': {
            border: '1px solid #2563eb',
            boxShadow: '0 0 0 3px rgba(37, 99, 235, 0.1)',
          },
          '.Label': {
            fontSize: '14px',
            fontWeight: '600',
            color: '#374151',
            marginBottom: '8px',
          }
        }
      }
    });

    // Create Payment Element (includes Card, Link, and other payment methods)
    paymentElement = elements.create('payment', {
      layout: {
        type: 'tabs', // 'tabs' or 'accordion'
        defaultCollapsed: false,
        radios: true,
        spacedAccordionItems: true
      },
      fields: {
        billingDetails: {
          name: 'auto',
          email: 'never', // We already have email field
          phone: 'auto', // Enable phone with automatic validation
          address: {
            country: 'auto',
            postalCode: 'auto'
          }
        }
      },
      terms: {
        card: 'never', // We show terms separately
      },
      wallets: {
        applePay: 'auto',
        googlePay: 'auto'
      }
    });

    // Mount the Payment Element
    paymentElement.mount('#payment-element');

    // Listen for changes
    paymentElement.on('change', (event) => {
      if (event.complete) {
        isFormReady.value = true;
      }
      
      const displayError = document.getElementById('payment-element-errors');
      if (event.error) {
        displayError.textContent = event.error.message;
      } else {
        displayError.textContent = '';
      }
    });

    paymentElement.on('ready', () => {
      isFormReady.value = true;
    });

} catch (error) {
    showNotification('error', 'Payment System Error', error.message);
  }
};

// ============================================
// 3. CREATE PAYMENT INTENT
// ============================================

const createPaymentIntent = async () => {
  try {
    console.log('Creating payment intent...');
    console.log('Current bookingDetails:', bookingDetails.value);
    console.log('Current totalAmount:', totalAmount.value);
    
    // Ensure we have loaded booking details first
    if (!bookingDetails.value) {
      console.log('Booking details not loaded, loading now...');
      await loadBookingDetails();
    }
    
    // Check if total amount is valid
    if (totalAmount.value <= 0) {
      // Log booking details for debugging
      const debugInfo = {
        duration_days: bookingDetails.value?.duration_days,
        duty_type: bookingDetails.value?.duty_type,
        hourly_rate: bookingDetails.value?.hourly_rate,
        hours: bookingDetails.value?.hours
      };
      
      console.error('Invalid total amount. Debug info:', debugInfo);
      showNotification('error', 'Booking Configuration Error', 
        'This booking is missing required information (duration, duty type, or hourly rate). Please contact support or update the booking details.');
      return;
    }
    
    const amountInCents = Math.round(totalAmount.value * 100);
    console.log('Amount in cents:', amountInCents);
    
    const response = await axios.post('/api/stripe/create-payment-intent', {
      booking_id: bookingId.value,
      amount: amountInCents,
      currency: 'usd',
      customer_email: customerEmail.value
    });

    console.log('Payment intent response:', response.data);

    if (response.data.success) {
      clientSecret.value = response.data.client_secret;
      console.log('Payment intent created successfully');
    } else {
      throw new Error(response.data.message || 'Failed to create payment intent');
    }
  } catch (error) {
    console.error('Error in createPaymentIntent:', error);
    console.error('Error response:', error.response?.data);
    
    let errorMessage = 'Failed to initialize payment system';
    
    if (error.response) {
      // Server responded with error
      errorMessage = error.response.data.message || errorMessage;
      
      if (error.response.data.errors) {
        // Validation errors
        const validationErrors = Object.values(error.response.data.errors).flat();
        errorMessage = validationErrors.join(', ');
      }
    }
    
    showNotification('error', 'Setup Error', errorMessage);
    throw error;
  }
};

// ============================================
// 4. HANDLE PAYMENT SUBMISSION
// ============================================

const handleSubmit = async () => {
  if (!stripe || !elements) {
    showNotification('error', 'Error', 'Payment system not ready');
    return;
  }

  processing.value = true;

  try {
    // Confirm the payment
    const { error, paymentIntent } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        return_url: `${window.location.origin}/payment-success?booking_id=${bookingId.value}`,
        receipt_email: customerEmail.value,
        payment_method_data: {
          billing_details: {
            email: customerEmail.value,
          }
        }
      },
      redirect: 'if_required' // Don't redirect if not needed
    });

    if (error) {
      // Payment failed
      showNotification('error', 'Payment Failed', error.message);
      processing.value = false;
    } else if (paymentIntent && paymentIntent.status === 'succeeded') {
      // Payment succeeded
      
      // Update booking status and get receipt URL
      const result = await updateBookingStatus(paymentIntent.id);
      
      showNotification('success', 'Payment Successful', 'Your booking has been confirmed!');
      
      // Open receipt in new tab if available
      if (result && result.receipt_url) {
        window.open(result.receipt_url, '_blank');
      }
      
      // Set flag to trigger dashboard refresh
      localStorage.setItem('payment_completed', 'true');
      localStorage.setItem('payment_booking_id', bookingId.value);
      localStorage.setItem('payment_timestamp', Date.now().toString());
      
      // Redirect to dashboard after 3 seconds
      setTimeout(() => {
        window.location.href = '/client/dashboard';
      }, 3000);
    }
  } catch (error) {
    showNotification('error', 'Error', error.message || 'An unexpected error occurred');
    processing.value = false;
  }
};

// ============================================
// 5. UPDATE BOOKING STATUS
// ============================================

const updateBookingStatus = async (paymentIntentId) => {
  try {
    const response = await axios.post('/api/bookings/update-payment-status', {
      booking_id: bookingId.value,
      payment_intent_id: paymentIntentId,
      status: 'paid'
    });
    
    // Store receipt URL if available
    if (response.data.receipt_url) {
      localStorage.setItem(`receipt_${bookingId.value}`, response.data.receipt_url);
    }
    
    return response.data;
  } catch (error) {
    // Don't show error to user as payment succeeded
    return null;
  }
};

// ============================================
// 6. LOAD BOOKING DETAILS
// ============================================

const loadBookingDetails = async () => {
  try {
    console.log('Loading booking details for ID:', bookingId.value);
    const response = await axios.get(`/api/bookings/${bookingId.value}`);
    console.log('API Response:', response.data);
    
    bookingDetails.value = response.data.booking || response.data;
    console.log('Booking Details:', bookingDetails.value);

    // Set customer email from booking or user
    if (bookingDetails.value.client_email) {
      customerEmail.value = bookingDetails.value.client_email;
    } else if (bookingDetails.value.client && bookingDetails.value.client.email) {
      customerEmail.value = bookingDetails.value.client.email;
    }
    
    console.log('Customer Email:', customerEmail.value);
    console.log('Calculated Subtotal:', subtotal.value);
    console.log('Calculated Total:', totalAmount.value);
    
  } catch (error) {
    console.error('Error loading booking details:', error);
    console.error('Error response:', error.response?.data);
    showNotification('error', 'Error', 'Failed to load booking details: ' + (error.response?.data?.message || error.message));
    throw error;
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

onMounted(async () => {
  // Get booking ID from URL
  const urlParams = new URLSearchParams(window.location.search);
  bookingId.value = urlParams.get('booking_id');

  if (!bookingId.value) {
    showNotification('error', 'Invalid Request', 'No booking ID provided');
    return;
  }

  // Load booking details first
  await loadBookingDetails();
  
  // Then initialize Stripe
  initStripeWhenReady();
});
</script>

<style scoped>
/* Container Layout */
.checkout-container {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  min-height: 100vh;
  background: #f9fafb;
}

@media (max-width: 1024px) {
  .checkout-container {
    grid-template-columns: 1fr;
  }
}

/* Left Side: Order Summary */
.order-summary-section {
  background: #0F172A;
  color: white;
  padding: 48px 40px;
  display: flex;
  flex-direction: column;
}

.back-button {
  display: flex;
  align-items: center;
  gap: 8px;
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  padding: 8px 0;
  margin-bottom: 32px;
  transition: color 0.2s;
  width: fit-content;
}

.back-button:hover {
  color: white;
}

.logo-section {
  text-align: left;
  margin-bottom: 40px;
}

.logo {
  width: 64px;
  height: 64px;
  object-fit: contain;
  margin-bottom: 16px;
  filter: brightness(0) invert(1);
}

.company-name {
  font-size: 18px;
  font-weight: 600;
  color: white;
}

.checkout-title {
  font-size: 32px;
  font-weight: 700;
  color: white;
  margin-bottom: 32px;
}

/* Service Card */
.service-card {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
}

.service-header {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.service-icon {
  width: 48px;
  height: 48px;
  background: rgba(59, 130, 246, 0.2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #60a5fa;
  flex-shrink: 0;
}

.service-info {
  flex: 1;
}

.service-name {
  font-size: 18px;
  font-weight: 600;
  color: white;
  margin: 0 0 4px 0;
}

.service-description {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.6);
  margin: 0 0 8px 0;
}

.service-billing {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.5);
  margin: 0;
}

.service-price {
  text-align: right;
}

.price-amount {
  font-size: 28px;
  font-weight: 700;
  color: white;
}

/* Billing Toggle */
.billing-toggle {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.toggle-container {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}

.toggle-container input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.2);
  transition: .4s;
  border-radius: 24px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .toggle-slider {
  background-color: #10b981;
}

input:checked + .toggle-slider:before {
  transform: translateX(20px);
}

.toggle-info {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.toggle-label {
  font-size: 14px;
  color: white;
  font-weight: 500;
}

.toggle-label .badge {
  background: #10b981;
  color: white;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
  margin-left: 8px;
}

.toggle-price {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.7);
}

/* Pricing Breakdown */
.pricing-breakdown {
  margin-top: auto;
  padding-top: 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  font-size: 15px;
}

.price-label {
  color: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  gap: 6px;
}

.price-label i {
  font-size: 14px;
  cursor: help;
}

.price-value {
  color: white;
  font-weight: 500;
}

.total-row {
  font-size: 18px;
  font-weight: 700;
  padding-top: 16px;
  margin-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.total-row .price-label,
.total-row .price-value {
  color: white;
}

/* Right Side: Payment Form */
.payment-section {
  background: white;
  padding: 48px 40px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
}

.payment-form-card {
  width: 100%;
  max-width: 480px;
}

.payment-title {
  font-size: 24px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 32px 0;
}

/* Form Elements */
.form-group {
  margin-bottom: 24px;
  position: relative;
}

.form-label {
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
  background: #f9fafb;
  transition: all 0.2s;
}

.form-input:disabled {
  cursor: not-allowed;
}

.change-link {
  position: absolute;
  right: 16px;
  top: 38px;
  background: none;
  border: none;
  color: #2563eb;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
}

.change-link:hover {
  text-decoration: underline;
}

/* Stripe Payment Element */
.stripe-payment-element {
  border-radius: 8px;
  /* Stripe Elements will style themselves */
}

.payment-errors {
  color: #ef4444;
  font-size: 13px;
  margin-top: 8px;
  min-height: 20px;
}

/* Terms */
.terms-section {
  margin: 24px 0;
}

.terms-text {
  font-size: 13px;
  color: #6b7280;
  line-height: 1.6;
  margin: 0;
}

/* Subscribe Button */
.subscribe-button {
  width: 100%;
  padding: 16px;
  background: #0F172A;
  border: none;
  border-radius: 8px;
  color: white;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.subscribe-button:hover:not(:disabled) {
  background: #1e293b;
  transform: translateY(-1px);
}

.subscribe-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Powered by Stripe */
.powered-by {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 24px;
  font-size: 13px;
  color: #9ca3af;
}

.stripe-logo {
  width: 16px;
  height: 16px;
  opacity: 0.6;
}

.stripe-text {
  font-weight: 600;
  color: #6b7280;
}
</style>
