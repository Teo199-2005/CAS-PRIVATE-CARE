<template>
  <v-card elevation="2" class="mb-3 tax-payroll-compact">
    <v-card-title class="pa-3 d-flex align-center">
      <v-icon :color="primaryColor" class="mr-2" size="small">mdi-file-document-outline</v-icon>
      <span class="section-title-sm" :class="`${primaryColor}--text`">Tax & Payroll</span>
      <v-spacer></v-spacer>
      <v-chip 
        :color="onboardingComplete ? 'success' : 'warning'" 
        size="x-small"
        variant="flat"
      >
        {{ onboardingComplete ? 'Complete' : 'Setup Required' }}
      </v-chip>
    </v-card-title>

    <v-card-text class="pa-3 pt-0">
      <!-- Onboarding Progress (Compact) -->
      <div v-if="!onboardingComplete">
        <div class="d-flex align-center justify-space-between mb-2">
          <span class="text-caption font-weight-medium">Complete Your Setup</span>
          <span class="text-caption" :class="`${primaryColor}--text`">{{ onboardingProgress }}%</span>
        </div>
        <v-progress-linear 
          :model-value="onboardingProgress" 
          :color="primaryColor"
          height="4"
          rounded
          class="mb-2"
        ></v-progress-linear>

        <!-- Compact Onboarding Steps -->
        <div class="onboarding-steps-compact">
          <div 
            v-for="step in onboardingSteps" 
            :key="step.key"
            class="step-item d-flex align-center py-1"
            :class="{ 'opacity-50': step.complete }"
          >
            <v-icon 
              :color="step.complete ? 'success' : 'grey'" 
              size="x-small" 
              class="mr-2"
            >
              {{ step.complete ? 'mdi-check-circle' : 'mdi-circle-outline' }}
            </v-icon>
            <span class="text-caption flex-grow-1">{{ step.label }}</span>
            <v-chip 
              v-if="step.complete" 
              color="success" 
              size="x-small" 
              variant="outlined"
            >
              Done
            </v-chip>
            <v-btn 
              v-else-if="step.action"
              variant="text"
              :color="primaryColor"
              size="x-small"
              density="compact"
              @click="step.action"
            >
              {{ step.actionLabel }}
            </v-btn>
          </div>
        </div>
      </div>

      <!-- Quick Stats (when complete) - Compact -->
      <v-row v-if="onboardingComplete" dense>
        <v-col cols="6">
          <div class="text-center">
            <div class="text-subtitle-1 font-weight-bold" :class="`${primaryColor}--text`">
              ${{ formatCurrency(ytdEarnings) }}
            </div>
            <div class="text-caption text-grey">YTD Earnings</div>
          </div>
        </v-col>
        <v-col cols="6">
          <div class="text-center">
            <div class="text-subtitle-1 font-weight-bold warning--text">
              ${{ formatCurrency(estimatedTax) }}
            </div>
            <div class="text-caption text-grey">Est. Tax</div>
          </div>
        </v-col>
      </v-row>

      <!-- Tax Documents Section -->
      <div v-if="onboardingComplete" class="mb-4">
        <v-divider class="my-3"></v-divider>
        <div class="d-flex align-center mb-3">
          <v-icon size="small" class="mr-2" :color="primaryColor">mdi-file-certificate</v-icon>
          <span class="text-subtitle-2">Tax Documents</span>
        </div>

        <!-- W9 Status (In-office submission only) -->
        <v-alert
          :type="w9Status === 'verified' ? 'success' : 'warning'"
          variant="tonal"
          density="compact"
          class="mb-3"
        >
          <div>
            <strong>W9 Form:</strong>
            <span v-if="w9Status === 'verified'">Verified</span>
            <span v-else>Pending in-office verification</span>
          </div>
          <div v-if="w9Status !== 'verified'" class="text-caption mt-1">
            Please visit our New York office to complete your W9. Your account will be verified after admin approval.
          </div>
        </v-alert>

        <!-- Available 1099 Forms -->
        <v-expansion-panels v-if="available1099Forms.length > 0" variant="accordion" class="mt-3">
          <v-expansion-panel>
            <v-expansion-panel-title>
              <v-icon size="small" class="mr-2">mdi-file-chart</v-icon>
              1099-NEC Forms ({{ available1099Forms.length }})
            </v-expansion-panel-title>
            <v-expansion-panel-text>
              <v-list dense>
                <v-list-item 
                  v-for="form in available1099Forms" 
                  :key="form.id"
                >
                  <v-list-item-title>Tax Year {{ form.tax_year }}</v-list-item-title>
                  <v-list-item-subtitle>
                    ${{ formatCurrency(form.total_compensation) }} - {{ form.status_label }}
                  </v-list-item-subtitle>
                  <template #append>
                    <v-btn 
                      v-if="form.can_download"
                      icon
                      size="small"
                      variant="text"
                      @click="download1099(form.id)"
                    >
                      <v-icon>mdi-download</v-icon>
                    </v-btn>
                  </template>
                </v-list-item>
              </v-list>
            </v-expansion-panel-text>
          </v-expansion-panel>
        </v-expansion-panels>
      </div>

      <!-- Payout Preferences -->
      <div v-if="onboardingComplete">
        <v-divider class="my-3"></v-divider>
        <div class="d-flex align-center justify-space-between mb-3">
          <div class="d-flex align-center">
            <v-icon size="small" class="mr-2" :color="primaryColor">mdi-calendar-clock</v-icon>
            <span class="text-subtitle-2">Payout Preferences</span>
          </div>
          <v-btn 
            variant="text" 
            size="small"
            @click="showPayoutPrefsDialog = true"
          >
            Edit
          </v-btn>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <v-chip size="small" variant="outlined">
            <v-icon start size="small">mdi-repeat</v-icon>
            {{ payoutFrequency }}
          </v-chip>
          <v-chip size="small" variant="outlined">
            <v-icon start size="small">mdi-calendar</v-icon>
            {{ payoutDayLabel }}
          </v-chip>
          <v-chip v-if="minimumAmount > 0" size="small" variant="outlined">
            <v-icon start size="small">mdi-currency-usd</v-icon>
            Min: ${{ formatCurrency(minimumAmount) }}
          </v-chip>
        </div>
      </div>
    </v-card-text>

  <!-- W9 Submission Dialog removed (in-office submission & admin verification) -->

    <!-- Payout Preferences Dialog -->
    <v-dialog v-model="showPayoutPrefsDialog" max-width="500">
      <v-card>
        <v-card-title class="d-flex align-center">
          <v-icon class="mr-2">mdi-calendar-clock</v-icon>
          Payout Preferences
        </v-card-title>
        <v-card-text>
          <v-select
            v-model="payoutPrefs.frequency"
            :items="frequencyOptions"
            label="Payout Frequency"
            variant="outlined"
            density="comfortable"
            class="mb-3"
          ></v-select>

          <v-select
            v-model="payoutPrefs.payout_day"
            :items="payoutDayOptions"
            label="Preferred Payout Day"
            variant="outlined"
            density="comfortable"
            class="mb-3"
          ></v-select>

          <v-text-field
            v-model.number="payoutPrefs.minimum_amount"
            label="Minimum Payout Amount"
            type="number"
            prefix="$"
            variant="outlined"
            density="comfortable"
            hint="Payouts below this amount will accumulate"
          ></v-text-field>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="showPayoutPrefsDialog = false">Cancel</v-btn>
          <v-btn 
            :color="primaryColor" 
            variant="flat"
            :loading="savingPrefs"
            @click="savePayoutPreferences"
          >
            Save
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  primaryColor: {
    type: String,
    default: 'primary'
  },
  userType: {
    type: String,
    default: 'caregiver'
  }
});

const emit = defineEmits(['onboarding-complete', 'error']);

// State
const loading = ref(true);
const showPayoutPrefsDialog = ref(false);
const savingPrefs = ref(false);

// Onboarding Data
const onboardingStatus = ref({
  steps: {},
  all_complete: false,
  completion_percentage: 0
});

// W9 Data
const w9Status = ref('not_submitted');

// Tax Data
const ytdEarnings = ref(0);
const estimatedTax = ref(0);
const available1099Forms = ref([]);

// Payout Data
const payoutFrequency = ref('Weekly');
const payoutDay = ref(5);
const minimumAmount = ref(0);
const nextPayoutDate = ref('--');
const payoutPrefs = ref({
  frequency: 'weekly',
  payout_day: 5,
  minimum_amount: 0
});

// Options

const frequencyOptions = [
  { title: 'Weekly (Every Friday)', value: 'weekly' },
  { title: 'Bi-weekly (Every Other Friday)', value: 'biweekly' },
  { title: 'Monthly (1st of Month)', value: 'monthly' }
];

const payoutDayOptions = [
  { title: 'Monday', value: 1 },
  { title: 'Tuesday', value: 2 },
  { title: 'Wednesday', value: 3 },
  { title: 'Thursday', value: 4 },
  { title: 'Friday', value: 5 }
];

// Computed
const onboardingComplete = computed(() => onboardingStatus.value.all_complete);
const onboardingProgress = computed(() => onboardingStatus.value.completion_percentage);

const onboardingSteps = computed(() => {
  const steps = onboardingStatus.value.steps || {};
  return Object.entries(steps).map(([key, step]) => ({
    key,
    ...step,
    action: getStepAction(key),
    actionLabel: getStepActionLabel(key)
  }));
});

const w9StatusLabel = computed(() => {
  switch (w9Status.value) {
    case 'verified': return 'Verified ✓';
    case 'submitted': return 'Submitted - Pending Review';
    case 'rejected': return 'Rejected - Please Resubmit';
    default: return 'Not Submitted';
  }
});

const payoutDayLabel = computed(() => {
  const day = payoutDayOptions.find(d => d.value === payoutDay.value);
  return day ? day.title : 'Friday';
});

// Methods
const formatCurrency = (value) => {
  return parseFloat(value || 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
};

const getStepAction = (key) => {
  switch (key) {
  // W9 is handled in-office; the portal should not collect tax IDs.
  case 'tax_info': return null;
    case 'bank_account': return () => { window.location.href = '/connect-bank-account'; };
    default: return null;
  }
};

const getStepActionLabel = (key) => {
  switch (key) {
  case 'tax_info': return 'In Office';
    case 'bank_account': return 'Connect Bank';
    case 'profile': return 'Complete';
    case 'certification': return 'Add';
    case 'background_check': return 'Start';
    default: return 'Complete';
  }
};

const loadOnboardingStatus = async () => {
  try {
    const response = await axios.get('/api/payroll/onboarding');
    if (response.data.success) {
      onboardingStatus.value = response.data;
    }
  } catch (error) {
    console.error('Error loading onboarding status:', error);
  }
};

const loadW9Status = async () => {
  try {
    const response = await axios.get('/api/tax/w9-status');
    if (response.data.success) {
      w9Status.value = response.data.status;
    }
  } catch (error) {
    console.error('Error loading W9 status:', error);
  }
};

const loadTaxEstimate = async () => {
  try {
    const response = await axios.get('/api/tax/estimate');
    if (response.data.success) {
      ytdEarnings.value = response.data.tax_estimate.ytd_earnings;
      estimatedTax.value = response.data.tax_estimate.estimated_tax;
    }
  } catch (error) {
    console.error('Error loading tax estimate:', error);
  }
};

const load1099Forms = async () => {
  try {
    const response = await axios.get('/api/tax/1099');
    if (response.data.success) {
      available1099Forms.value = response.data.forms;
    }
  } catch (error) {
    console.error('Error loading 1099 forms:', error);
  }
};

const loadPayoutSchedule = async () => {
  try {
    const response = await axios.get('/api/payroll/schedule');
    if (response.data.success) {
      const schedule = response.data.schedule;
      payoutFrequency.value = schedule.frequency_label;
      payoutDay.value = schedule.payout_day;
      minimumAmount.value = schedule.minimum_amount;
      nextPayoutDate.value = schedule.next_payout_date;
      payoutPrefs.value = {
        frequency: schedule.frequency,
        payout_day: schedule.payout_day,
        minimum_amount: schedule.minimum_amount
      };
    }
  } catch (error) {
    console.error('Error loading payout schedule:', error);
  }
};

const savePayoutPreferences = async () => {
  savingPrefs.value = true;
  try {
    const response = await axios.post('/api/payroll/preferences', payoutPrefs.value);
    if (response.data.success) {
      await loadPayoutSchedule();
      showPayoutPrefsDialog.value = false;
    }
  } catch (error) {
    console.error('Error saving payout preferences:', error);
    emit('error', error.response?.data?.error || 'Failed to save preferences');
  } finally {
    savingPrefs.value = false;
  }
};

const download1099 = (formId) => {
  window.open(`/api/tax/1099/${formId}/download`, '_blank');
};

// Initialize
onMounted(async () => {
  loading.value = true;
  await Promise.all([
    loadOnboardingStatus(),
    loadW9Status(),
    loadTaxEstimate(),
    load1099Forms(),
    loadPayoutSchedule()
  ]);
  loading.value = false;
});
</script>

<style scoped>
.tax-payroll-compact {
  border-radius: 16px !important;
  border: 1px solid #e5e7eb !important;
}

/* W9 modal contrast fixes (some global styles were forcing white-on-white) */
.w9-modal-card {
  background: #ffffff !important;
  color: #111827 !important;
}

.w9-modal-title {
  background: #ffffff !important;
  border-bottom: 1px solid #e5e7eb !important;
  color: #111827 !important;
}

.w9-modal-title :deep(*) {
  color: #111827 !important;
}

.w9-modal-title-icon {
  color: #111827 !important;
}

.w9-modal-body {
  color: #111827 !important;
}

.w9-modal-caption {
  color: #111827 !important;
}

.section-title-sm {
  font-size: 0.95rem;
  font-weight: 600;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
}

.onboarding-steps-compact .step-item {
  border-bottom: 1px solid #f3f4f6;
}

.onboarding-steps-compact .step-item:last-child {
  border-bottom: none;
}

.opacity-50 {
  opacity: 0.5;
}

.opacity-60 {
  opacity: 0.6;
}

.gap-2 {
  gap: 8px;
}
</style>
