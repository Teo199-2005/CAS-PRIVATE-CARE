<template>
  <v-card>
    <v-card-title class="bg-success text-white">
      <v-icon start>mdi-bank</v-icon>
      Payment Setup - Add Bank Account
    </v-card-title>

    <v-card-text class="pa-6">
      <!-- Onboarding Complete -->
      <div v-if="onboardingComplete">
        <v-alert type="success" variant="tonal" class="mb-4">
          <div class="d-flex align-center">
            <v-icon size="48" color="success" class="mr-4">mdi-check-circle</v-icon>
            <div>
              <h3>✅ Bank Account Connected!</h3>
              <p class="mb-0">You're all set to receive payments. Earnings will be deposited weekly every Friday.</p>
            </div>
          </div>
        </v-alert>

        <v-card variant="outlined" class="pa-4">
          <h4 class="mb-3">Payment Schedule</h4>
          <v-row>
            <v-col cols="12" md="6">
              <div class="payment-info-item">
                <v-icon color="primary" class="mr-2">mdi-calendar-week</v-icon>
                <div>
                  <div class="text-caption text-grey">Payout Frequency</div>
                  <div class="font-weight-bold">Weekly (Friday)</div>
                </div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="payment-info-item">
                <v-icon color="primary" class="mr-2">mdi-cash-multiple</v-icon>
                <div>
                  <div class="text-caption text-grey">Hourly Rate</div>
                  <div class="font-weight-bold">$28.00/hour</div>
                </div>
              </div>
            </v-col>
            <v-col cols="12">
              <div class="payment-info-item">
                <v-icon color="info" class="mr-2">mdi-information</v-icon>
                <div>
                  <div class="text-caption text-grey">Next Payout Date</div>
                  <div class="font-weight-bold">{{ nextPayoutDate }}</div>
                </div>
              </div>
            </v-col>
          </v-row>
        </v-card>

        <v-btn
          variant="outlined"
          color="primary"
          class="mt-4"
          @click="checkStatus"
          :loading="checking"
        >
          <v-icon start>mdi-refresh</v-icon>
          Refresh Status
        </v-btn>
      </div>

      <!-- Needs Onboarding -->
      <div v-else>
        <v-alert type="info" variant="tonal" class="mb-4">
          <div class="d-flex align-center">
            <v-icon size="48" color="info" class="mr-4">mdi-bank-outline</v-icon>
            <div>
              <h3>🏦 Connect Your Bank Account</h3>
              <p class="mb-0">
                To receive your earnings, you need to securely connect your bank account through Stripe. 
                This is a one-time setup that takes about 2 minutes.
              </p>
            </div>
          </div>
        </v-alert>

        <v-card variant="outlined" class="pa-4 mb-4">
          <h4 class="mb-3">What you'll need:</h4>
          <v-list density="compact">
            <v-list-item>
              <template v-slot:prepend>
                <v-icon color="success">mdi-check</v-icon>
              </template>
              <v-list-item-title>Government-issued ID (Driver's License, Passport)</v-list-item-title>
            </v-list-item>
            <v-list-item>
              <template v-slot:prepend>
                <v-icon color="success">mdi-check</v-icon>
              </template>
              <v-list-item-title>Bank account and routing number</v-list-item-title>
            </v-list-item>
            <v-list-item>
              <template v-slot:prepend>
                <v-icon color="success">mdi-check</v-icon>
              </template>
              <v-list-item-title>Social Security Number (for tax reporting)</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-card>

        <v-card variant="outlined" class="pa-4 mb-4 bg-grey-lighten-5">
          <h4 class="mb-3">
            <v-icon color="success" class="mr-2">mdi-shield-check</v-icon>
            Security & Privacy
          </h4>
          <ul class="text-body-2">
            <li>Your information is encrypted and secure</li>
            <li>Stripe handles all sensitive data - we never see your bank details</li>
            <li>You can update or remove your account anytime</li>
            <li>All payments comply with banking regulations</li>
          </ul>
        </v-card>

        <v-alert v-if="errorMessage" type="error" class="mb-4" dismissible @click:close="errorMessage = ''">
          {{ errorMessage }}
        </v-alert>

        <v-btn
          color="success"
          size="large"
          block
          :loading="loading"
          @click="startOnboarding"
        >
          <v-icon start>mdi-bank-plus</v-icon>
          Connect Bank Account Securely
        </v-btn>

        <p class="text-caption text-center text-grey mt-3">
          <v-icon size="16">mdi-lock</v-icon>
          Powered by Stripe - Industry-leading payment security
        </p>
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const emit = defineEmits(['complete']);

// State
const loading = ref(false);
const checking = ref(false);
const onboardingComplete = ref(false);
const hasConnectId = ref(false);
const errorMessage = ref('');

// Computed
const nextPayoutDate = computed(() => {
  const now = new Date();
  const dayOfWeek = now.getDay();
  const daysUntilFriday = (5 - dayOfWeek + 7) % 7 || 7;
  const nextFriday = new Date(now);
  nextFriday.setDate(now.getDate() + daysUntilFriday);
  
  return nextFriday.toLocaleDateString('en-US', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
});

onMounted(() => {
  checkStatus();

  // Check URL params for success/refresh
  const params = new URLSearchParams(window.location.search);
  if (params.get('success') === 'true') {
    checkStatus();
  }
});

const checkStatus = async () => {
  checking.value = true;
  errorMessage.value = '';

  try {
    const response = await fetch('/api/stripe/onboarding-status', {
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.error || 'Failed to check status');
    }

    onboardingComplete.value = data.complete;
    hasConnectId.value = data.has_connect_id;

    if (data.complete) {
      emit('complete');
    }
  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    checking.value = false;
  }
};

const startOnboarding = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await fetch('/api/stripe/create-onboarding-link', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });

    const data = await response.json();

    if (!data.success) {
      throw new Error(data.error || 'Failed to create onboarding link');
    }

    // Redirect to Stripe onboarding
    window.location.href = data.url;
  } catch (error) {
    errorMessage.value = error.message;
    loading.value = false;
  }
};
</script>

<style scoped>
.payment-info-item {
  display: flex;
  align-items: flex-start;
  padding: 12px;
  background-color: #f8f9fa;
  border-radius: 8px;
}

ul {
  padding-left: 20px;
}

ul li {
  margin-bottom: 8px;
}
</style>
