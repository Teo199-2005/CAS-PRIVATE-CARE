<template>
  <div class="admin-staff-settings">
    <!-- Settings Navigation -->
    <v-row>
      <!-- Sidebar Navigation -->
      <v-col cols="12" md="3">
        <v-card elevation="0" class="settings-nav-card">
          <v-list density="compact" nav>
            <v-list-item
              v-for="section in settingsSections"
              :key="section.key"
              :active="activeSection === section.key"
              :value="section.key"
              color="primary"
              @click="activeSection = section.key"
            >
              <template #prepend>
                <v-icon :icon="section.icon" />
              </template>
              <v-list-item-title>{{ section.title }}</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <!-- Settings Content -->
      <v-col cols="12" md="9">
        <!-- General Settings -->
        <v-card v-if="activeSection === 'general'" elevation="0" class="settings-card">
          <v-card-title class="d-flex align-center">
            <v-icon start color="primary">mdi-cog</v-icon>
            General Settings
          </v-card-title>
          <v-card-text>
            <v-form ref="generalForm" v-model="formsValid.general">
              <v-row>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model="settings.general.companyName"
                    label="Company Name"
                    variant="outlined"
                    density="comfortable"
                    :rules="[rules.required]"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model="settings.general.supportEmail"
                    label="Support Email"
                    variant="outlined"
                    density="comfortable"
                    type="email"
                    :rules="[rules.required, rules.email]"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model="settings.general.supportPhone"
                    label="Support Phone"
                    variant="outlined"
                    density="comfortable"
                    :rules="[rules.phone]"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-select
                    v-model="settings.general.timezone"
                    :items="timezones"
                    label="Timezone"
                    variant="outlined"
                    density="comfortable"
                  />
                </v-col>
                <v-col cols="12">
                  <v-textarea
                    v-model="settings.general.companyAddress"
                    label="Company Address"
                    variant="outlined"
                    rows="2"
                  />
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions class="pa-4 pt-0">
            <v-spacer />
            <v-btn 
              color="primary" 
              :loading="saving" 
              :disabled="!formsValid.general"
              @click="saveSettings('general')"
            >
              Save Changes
            </v-btn>
          </v-card-actions>
        </v-card>

        <!-- Booking Settings -->
        <v-card v-if="activeSection === 'booking'" elevation="0" class="settings-card">
          <v-card-title class="d-flex align-center">
            <v-icon start color="success">mdi-calendar-check</v-icon>
            Booking Settings
          </v-card-title>
          <v-card-text>
            <v-form ref="bookingForm" v-model="formsValid.booking">
              <v-row>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="settings.booking.minAdvanceBookingHours"
                    label="Minimum Advance Booking (hours)"
                    variant="outlined"
                    type="number"
                    min="0"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="settings.booking.maxAdvanceBookingDays"
                    label="Maximum Advance Booking (days)"
                    variant="outlined"
                    type="number"
                    min="1"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="settings.booking.minServiceHours"
                    label="Minimum Service Hours"
                    variant="outlined"
                    type="number"
                    min="1"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="settings.booking.maxServiceHours"
                    label="Maximum Service Hours"
                    variant="outlined"
                    type="number"
                    min="1"
                  />
                </v-col>
                <v-col cols="12">
                  <v-switch
                    v-model="settings.booking.requirePaymentUpfront"
                    label="Require Payment Upfront"
                    color="primary"
                    hide-details
                    class="mb-3"
                  />
                </v-col>
                <v-col cols="12">
                  <v-switch
                    v-model="settings.booking.autoApproveBookings"
                    label="Auto-Approve Bookings"
                    color="primary"
                    hide-details
                    class="mb-3"
                  />
                </v-col>
                <v-col cols="12">
                  <v-switch
                    v-model="settings.booking.allowCancellation"
                    label="Allow Booking Cancellation"
                    color="primary"
                    hide-details
                  />
                </v-col>
                <v-col v-if="settings.booking.allowCancellation" cols="12" sm="6">
                  <v-text-field
                    v-model.number="settings.booking.cancellationWindowHours"
                    label="Cancellation Window (hours before service)"
                    variant="outlined"
                    type="number"
                    min="0"
                  />
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions class="pa-4 pt-0">
            <v-spacer />
            <v-btn 
              color="primary" 
              :loading="saving"
              @click="saveSettings('booking')"
            >
              Save Changes
            </v-btn>
          </v-card-actions>
        </v-card>

        <!-- Payment Settings -->
        <v-card v-if="activeSection === 'payment'" elevation="0" class="settings-card">
          <v-card-title class="d-flex align-center">
            <v-icon start color="warning">mdi-credit-card</v-icon>
            Payment Settings
          </v-card-title>
          <v-card-text>
            <v-alert type="info" variant="tonal" class="mb-4">
              Payment processing is handled through Stripe. Configure your Stripe keys in the .env file.
            </v-alert>
            <v-form ref="paymentForm" v-model="formsValid.payment">
              <v-row>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="settings.payment.platformFeePercent"
                    label="Platform Fee (%)"
                    variant="outlined"
                    type="number"
                    min="0"
                    max="100"
                    step="0.5"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-select
                    v-model="settings.payment.currency"
                    :items="currencies"
                    label="Currency"
                    variant="outlined"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="settings.payment.caregiverBaseRate"
                    label="Caregiver Base Rate ($/hr)"
                    variant="outlined"
                    type="number"
                    min="0"
                    prefix="$"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="settings.payment.housekeeperBaseRate"
                    label="Housekeeper Base Rate ($/hr)"
                    variant="outlined"
                    type="number"
                    min="0"
                    prefix="$"
                  />
                </v-col>
                <v-col cols="12">
                  <v-switch
                    v-model="settings.payment.automaticPayouts"
                    label="Enable Automatic Payouts to Caregivers"
                    color="primary"
                    hide-details
                  />
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions class="pa-4 pt-0">
            <v-spacer />
            <v-btn 
              color="primary" 
              :loading="saving"
              @click="saveSettings('payment')"
            >
              Save Changes
            </v-btn>
          </v-card-actions>
        </v-card>

        <!-- Notification Settings -->
        <v-card v-if="activeSection === 'notifications'" elevation="0" class="settings-card">
          <v-card-title class="d-flex align-center">
            <v-icon start color="info">mdi-bell</v-icon>
            Notification Settings
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="12">
                <h4 class="text-subtitle-1 mb-3">Email Notifications</h4>
                <v-switch
                  v-model="settings.notifications.emailNewBooking"
                  label="New Booking Notifications"
                  color="primary"
                  hide-details
                  class="mb-2"
                />
                <v-switch
                  v-model="settings.notifications.emailBookingUpdates"
                  label="Booking Update Notifications"
                  color="primary"
                  hide-details
                  class="mb-2"
                />
                <v-switch
                  v-model="settings.notifications.emailPaymentReceived"
                  label="Payment Received Notifications"
                  color="primary"
                  hide-details
                  class="mb-2"
                />
                <v-switch
                  v-model="settings.notifications.emailNewCaregiver"
                  label="New Caregiver Registration"
                  color="primary"
                  hide-details
                  class="mb-4"
                />
              </v-col>
              <v-col cols="12">
                <v-divider class="mb-4" />
                <h4 class="text-subtitle-1 mb-3">SMS Notifications</h4>
                <v-switch
                  v-model="settings.notifications.smsEnabled"
                  label="Enable SMS Notifications"
                  color="primary"
                  hide-details
                  class="mb-2"
                />
                <v-switch
                  v-model="settings.notifications.smsBookingReminders"
                  label="Booking Reminder SMS"
                  color="primary"
                  hide-details
                  :disabled="!settings.notifications.smsEnabled"
                />
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="pa-4 pt-0">
            <v-spacer />
            <v-btn 
              color="primary" 
              :loading="saving"
              @click="saveSettings('notifications')"
            >
              Save Changes
            </v-btn>
          </v-card-actions>
        </v-card>

        <!-- Security Settings -->
        <v-card v-if="activeSection === 'security'" elevation="0" class="settings-card">
          <v-card-title class="d-flex align-center">
            <v-icon start color="error">mdi-shield-lock</v-icon>
            Security Settings
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model.number="settings.security.sessionTimeoutMinutes"
                  label="Session Timeout (minutes)"
                  variant="outlined"
                  type="number"
                  min="5"
                />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model.number="settings.security.maxLoginAttempts"
                  label="Max Login Attempts"
                  variant="outlined"
                  type="number"
                  min="3"
                />
              </v-col>
              <v-col cols="12">
                <v-switch
                  v-model="settings.security.require2FA"
                  label="Require Two-Factor Authentication for Admins"
                  color="primary"
                  hide-details
                  class="mb-2"
                />
              </v-col>
              <v-col cols="12">
                <v-switch
                  v-model="settings.security.logAllActions"
                  label="Log All Admin Actions (Audit Trail)"
                  color="primary"
                  hide-details
                />
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="pa-4 pt-0">
            <v-spacer />
            <v-btn 
              color="primary" 
              :loading="saving"
              @click="saveSettings('security')"
            >
              Save Changes
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- Success Snackbar -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
      {{ snackbar.message }}
      <template #actions>
        <v-btn variant="text" @click="snackbar.show = false">Close</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

// Emits
const emit = defineEmits(['save']);

// State
const activeSection = ref('general');
const saving = ref(false);

const formsValid = reactive({
  general: false,
  booking: false,
  payment: false,
});

const settings = reactive({
  general: {
    companyName: 'CAS Private Care LLC',
    supportEmail: 'support@casprivatecare.com',
    supportPhone: '(718) 555-0100',
    timezone: 'America/New_York',
    companyAddress: '123 Care Street, New York, NY 10001',
  },
  booking: {
    minAdvanceBookingHours: 24,
    maxAdvanceBookingDays: 90,
    minServiceHours: 3,
    maxServiceHours: 12,
    requirePaymentUpfront: true,
    autoApproveBookings: false,
    allowCancellation: true,
    cancellationWindowHours: 48,
  },
  payment: {
    platformFeePercent: 15,
    currency: 'USD',
    caregiverBaseRate: 25,
    housekeeperBaseRate: 22,
    automaticPayouts: true,
  },
  notifications: {
    emailNewBooking: true,
    emailBookingUpdates: true,
    emailPaymentReceived: true,
    emailNewCaregiver: true,
    smsEnabled: false,
    smsBookingReminders: false,
  },
  security: {
    sessionTimeoutMinutes: 60,
    maxLoginAttempts: 5,
    require2FA: false,
    logAllActions: true,
  },
});

const snackbar = reactive({
  show: false,
  message: '',
  color: 'success',
});

// Settings sections
const settingsSections = [
  { key: 'general', title: 'General', icon: 'mdi-cog' },
  { key: 'booking', title: 'Booking', icon: 'mdi-calendar-check' },
  { key: 'payment', title: 'Payment', icon: 'mdi-credit-card' },
  { key: 'notifications', title: 'Notifications', icon: 'mdi-bell' },
  { key: 'security', title: 'Security', icon: 'mdi-shield-lock' },
];

// Options
const timezones = [
  { title: 'Eastern Time (US)', value: 'America/New_York' },
  { title: 'Central Time (US)', value: 'America/Chicago' },
  { title: 'Mountain Time (US)', value: 'America/Denver' },
  { title: 'Pacific Time (US)', value: 'America/Los_Angeles' },
];

const currencies = [
  { title: 'US Dollar (USD)', value: 'USD' },
  { title: 'Euro (EUR)', value: 'EUR' },
  { title: 'British Pound (GBP)', value: 'GBP' },
];

// Validation rules
const rules = {
  required: (v) => !!v || 'This field is required',
  email: (v) => /.+@.+\..+/.test(v) || 'Invalid email format',
  phone: (v) => !v || /^[\d\s\-()]+$/.test(v) || 'Invalid phone format',
};

// Methods
const saveSettings = async (section) => {
  saving.value = true;
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    emit('save', { section, data: settings[section] });
    
    snackbar.message = `${section.charAt(0).toUpperCase() + section.slice(1)} settings saved successfully`;
    snackbar.color = 'success';
    snackbar.show = true;
  } catch (error) {
    snackbar.message = 'Failed to save settings. Please try again.';
    snackbar.color = 'error';
    snackbar.show = true;
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.admin-staff-settings {
  width: 100%;
}

.settings-nav-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  position: sticky;
  top: 80px;
}

.settings-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  margin-bottom: 16px;
}

.settings-card .v-card-title {
  font-size: 1.125rem;
  font-weight: 600;
  border-bottom: 1px solid #e5e7eb;
  padding: 16px 20px;
}

.settings-card .v-card-text {
  padding: 20px;
}

/* Mobile Responsive */
@media (max-width: 960px) {
  .settings-nav-card {
    position: relative;
    top: 0;
    margin-bottom: 16px;
  }
  
  .settings-nav-card .v-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  
  .settings-nav-card .v-list-item {
    flex: 0 0 auto;
    min-width: fit-content;
  }
}
</style>
