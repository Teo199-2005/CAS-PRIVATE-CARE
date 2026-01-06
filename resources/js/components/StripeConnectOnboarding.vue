<template>
  <div class="onboarding-container">
    <v-container fluid class="fill-height pa-0">
      <v-row no-gutters class="fill-height">
        <!-- Left Column - Branding (Dark) -->
        <v-col cols="12" md="5" class="left-column d-flex flex-column justify-center align-center pa-8">
          <div class="branding-content text-center">
            <div class="logo-container mb-8">
              <img src="/images/logo.png" alt="CAS Private Care" class="logo" />
            </div>
            
            <h1 class="welcome-title mb-4">Connect Your Payout Method</h1>
            <p class="welcome-subtitle mb-8">
              Securely connect your bank account or debit card to receive weekly payments for your care services.
            </p>
            
            <!-- Benefits -->
            <v-list class="benefits-list" lines="two">
              <v-list-item prepend-icon="mdi-lock-check" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">
                  Secure & Encrypted
                </v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">
                  Bank-level security powered by Stripe
                </v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item prepend-icon="mdi-clock-fast" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">
                  Fast Payouts
                </v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">
                  Weekly automatic payments to your account
                </v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item prepend-icon="mdi-shield-check" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">
                  Protected Information
                </v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">
                  We never see your full account details
                </v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item prepend-icon="mdi-wallet-plus" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">
                  Multiple Options
                </v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">
                  Bank account, debit card, or digital wallet
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
            
            <!-- Powered by Stripe -->
            <div class="powered-by mt-8">
              <v-divider class="mb-4 opacity-30"></v-divider>
              <p class="text-caption text-grey-lighten-1 mb-2">Powered by</p>
              <v-img 
                src="https://stripe.com/img/v3/home/social.png" 
                alt="Stripe" 
                max-width="100"
                class="mx-auto"
              />
            </div>
          </div>
        </v-col>

        <!-- Right Column - Stripe Connect Component (Light) -->
        <v-col cols="12" md="7" class="right-column d-flex flex-column justify-center pa-8">
          <div class="form-content">
            <!-- Header -->
            <div class="form-header mb-8">
              <h2 class="form-title mb-2">Let's get you connected</h2>
              <p class="form-subtitle text-grey-darken-1">
                Fill out a few details to set up your payout method. This only takes a few minutes.
              </p>
            </div>

            <!-- Stripe Connect Embedded Component -->
            <v-card elevation="0" class="stripe-connect-card pa-6 mb-6" :loading="loading">
              <div v-if="error" class="mb-4">
                <v-alert type="error" variant="tonal" closable @click:close="error = null">
                  <div class="font-weight-bold mb-1">Connection Error</div>
                  <div class="text-body-2">{{ error }}</div>
                </v-alert>
              </div>

              <div v-if="success" class="text-center py-8">
                <v-icon size="80" color="success" class="mb-4">mdi-check-circle</v-icon>
                <h3 class="text-h5 mb-2">Successfully Connected!</h3>
                <p class="text-body-1 text-grey-darken-1 mb-6">
                  Your payout method has been connected. You'll receive weekly payments for your completed services.
                </p>
                <v-btn color="success" size="large" prepend-icon="mdi-arrow-left" @click="returnToDashboard">
                  Return to Dashboard
                </v-btn>
              </div>

              <!-- Stripe Connect Embedded Iframe -->
              <div v-else id="stripe-connect-container" ref="connectContainer">
                <!-- Stripe will inject the onboarding form here -->
                <div class="text-center py-8" v-if="!stripeAccountSession">
                  <v-progress-circular indeterminate color="success" size="64" width="4"></v-progress-circular>
                  <p class="text-body-2 text-grey-darken-1 mt-4">Loading secure connection...</p>
                </div>
              </div>
            </v-card>

            <!-- Info Cards -->
            <v-row>
              <v-col cols="12" sm="6">
                <v-card elevation="0" class="info-card pa-4" color="blue-lighten-5">
                  <div class="d-flex align-center">
                    <v-icon size="32" color="blue-darken-2" class="mr-3">mdi-information</v-icon>
                    <div>
                      <div class="text-body-2 font-weight-bold text-blue-darken-4">What You'll Need</div>
                      <div class="text-caption text-blue-darken-2">Bank routing & account number or debit card</div>
                    </div>
                  </div>
                </v-card>
              </v-col>
              <v-col cols="12" sm="6">
                <v-card elevation="0" class="info-card pa-4" color="green-lighten-5">
                  <div class="d-flex align-center">
                    <v-icon size="32" color="green-darken-2" class="mr-3">mdi-calendar-check</v-icon>
                    <div>
                      <div class="text-body-2 font-weight-bold text-green-darken-4">Payout Schedule</div>
                      <div class="text-caption text-green-darken-2">Every Friday at 5:00 PM EST</div>
                    </div>
                  </div>
                </v-card>
              </v-col>
            </v-row>

            <!-- Back Link -->
            <div class="text-center mt-6">
              <v-btn variant="text" color="grey-darken-1" prepend-icon="mdi-arrow-left" @click="returnToDashboard">
                Back to Dashboard
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const error = ref(null);
const success = ref(false);
const onboardingUrl = ref(null);
const connectContainer = ref(null);

onMounted(async () => {
  await initializeStripeConnect();
});

const initializeStripeConnect = async () => {
  try {
    loading.value = true;
    error.value = null;

    // Get Stripe Connect Onboarding Link from backend
    const response = await axios.post('/api/stripe/create-onboarding-link');
    
    if (response.data.success && response.data.url) {
      onboardingUrl.value = response.data.url;
      
      // Redirect to Stripe's onboarding (opens in same tab with styled wrapper)
      // Or you can use an iframe if you prefer
      window.location.href = response.data.url;
      
    } else {
      throw new Error(response.data.error || 'Failed to create onboarding link');
    }
  } catch (err) {
    error.value = err.response?.data?.error || err.message || 'Failed to load connection form';
    loading.value = false;
  }
};

const returnToDashboard = () => {
  window.location.href = '/caregiver-dashboard?section=payment';
};

</script>

<style scoped>
.onboarding-container {
  min-height: 100vh;
  background: #f5f5f5;
}

/* Left Column - Dark Theme */
.left-column {
  background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #3949ab 100%);
  color: white;
  position: relative;
  overflow: hidden;
}

.left-column::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
  animation: pulse 15s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.3; }
  50% { transform: scale(1.1); opacity: 0.5; }
}

.branding-content {
  position: relative;
  z-index: 1;
  max-width: 500px;
}

.logo {
  max-width: 200px;
  height: auto;
  filter: brightness(0) invert(1);
}

.welcome-title {
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1.2;
  color: white;
}

.welcome-subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.6;
}

.benefits-list {
  background: transparent !important;
  text-align: left;
}

.benefits-list :deep(.v-list-item__prepend) {
  color: #4caf50 !important;
}

.powered-by {
  opacity: 0.8;
}

/* Right Column - Light Theme */
.right-column {
  background: white;
}

.form-content {
  max-width: 600px;
  width: 100%;
  margin: 0 auto;
}

.form-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1a237e;
}

.form-subtitle {
  font-size: 1rem;
  line-height: 1.6;
}

.stripe-connect-card {
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  background: white;
}

#stripe-connect-container {
  min-height: 400px;
}

.info-card {
  border-radius: 8px;
  border: 1px solid transparent;
  transition: all 0.3s ease;
}

.info-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Responsive */
@media (max-width: 960px) {
  .left-column {
    min-height: 400px;
  }
  
  .welcome-title {
    font-size: 2rem;
  }
  
  .form-title {
    font-size: 1.5rem;
  }
}

@media (max-width: 600px) {
  .welcome-title {
    font-size: 1.75rem;
  }
  
  .form-title {
    font-size: 1.25rem;
  }
  
  .branding-content,
  .form-content {
    padding: 1rem;
  }
}
</style>
