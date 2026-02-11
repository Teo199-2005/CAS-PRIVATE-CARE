<template>
  <div class="client-booking-form">
    <!-- Header Section with Branding -->
    <div class="booking-header-section">
      <div class="booking-brand-header">
        <div class="brand-logo-section">
          <div class="brand-logo-wrapper">
            <img src="/logo flower.png" alt="CAS Private Care Logo" class="brand-logo-image" />
          </div>
          <div class="brand-text">
            <h1 class="brand-title">CAS Private Care</h1>
            <p class="brand-subtitle">Professional Care Service Request</p>
          </div>
        </div>
        <div class="trust-indicators">
          <div class="trust-item">
            <v-icon color="white" size="20">mdi-shield-check</v-icon>
            <span>Verified Caregivers</span>
          </div>
          <div class="trust-item">
            <v-icon color="white" size="20">mdi-star-check</v-icon>
            <span>Quality Guaranteed</span>
          </div>
          <div class="trust-item">
            <v-icon color="white" size="20">mdi-lock</v-icon>
            <span>Secure & Insured</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Card -->
    <v-card elevation="0" class="professional-form-card">
      <v-card-text class="pa-0">
        <v-form ref="bookingForm" @submit.prevent="handleSubmit">
          <!-- Service Information Section -->
          <div class="form-section">
            <div class="section-header">
              <div class="section-icon">
                <v-icon color="primary" size="24">mdi-medical-bag</v-icon>
              </div>
              <div class="section-info">
                <h3 class="section-title">Service Information</h3>
                <p class="section-subtitle">Tell us about the care service you need</p>
              </div>
            </div>
            <div class="section-content">
              <v-row>
                <v-col cols="12" md="6">
                  <div class="form-field">
                    <label class="field-label">Service Type *</label>
                    <v-select 
                      v-model="localBookingData.serviceType" 
                      :items="serviceTypes" 
                      variant="outlined" 
                      density="comfortable" 
                      :rules="[v => !!v || 'Service type is required']"
                      placeholder="Choose the type of care needed"
                      class="professional-select"
                    />
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="form-field">
                    <label class="field-label">Hours per Day *</label>
                    <v-select 
                      v-model="localBookingData.dutyType" 
                      :items="dutyTypes" 
                      variant="outlined" 
                      density="comfortable" 
                      :rules="[v => !!v || 'Hours per day is required']"
                      placeholder="Select hours per day"
                      class="professional-select"
                    />
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="form-field">
                    <label class="field-label">Service Date *</label>
                    <v-text-field 
                      v-model="localBookingData.date" 
                      type="date" 
                      variant="outlined" 
                      density="comfortable" 
                      :rules="[v => !!v || 'Date is required']"
                      :min="today"
                      class="professional-field"
                    />
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="form-field">
                    <label class="field-label">Service Duration</label>
                    <v-select 
                      v-model="localBookingData.durationDays" 
                      :items="durationOptions" 
                      variant="outlined" 
                      density="comfortable"
                      placeholder="How long do you need care?"
                      class="professional-select"
                    />
                  </div>
                </v-col>
              </v-row>
              
              <!-- Day of Week Selection -->
              <v-row class="mt-4">
                <v-col cols="12">
                  <div class="form-field">
                    <label class="field-label mb-3 d-block">Select Days of Week *</label>
                    <div v-if="selectedDaysCount < 3" class="text-error mb-2" style="font-size: 0.875rem; font-weight: 600;">
                      <v-icon size="small" color="error" class="mr-1">mdi-alert-circle</v-icon>
                      Minimum 3 days required ({{ selectedDaysCount }}/3 selected)
                    </div>
                    <div v-else class="text-success mb-2" style="font-size: 0.875rem; font-weight: 500;">
                      <v-icon size="small" color="success" class="mr-1">mdi-check-circle</v-icon>
                      {{ selectedDaysCount }} days selected
                    </div>
                    <div class="day-selector-container">
                      <div class="day-buttons-row">
                        <v-btn
                          v-for="(day, key) in localBookingData.selectedDays"
                          :key="key"
                          :variant="day.enabled ? 'flat' : 'outlined'"
                          :color="day.enabled ? 'primary' : 'grey'"
                          size="large"
                          class="day-button"
                          @click="toggleDay(key)"
                        >
                          {{ getDayLabel(key) }}
                        </v-btn>
                      </div>
                      
                      <!-- Selected Days List -->
                      <div v-if="selectedDaysCount > 0" class="selected-days-list mt-4">
                        <div 
                          v-for="(day, key) in localBookingData.selectedDays" 
                          :key="key"
                          v-show="day.enabled"
                          class="selected-day-item"
                        >
                          <span class="day-name">{{ getFullDayName(key) }}</span>
                          <v-text-field
                            v-model="day.startTime"
                            type="time"
                            variant="outlined"
                            density="compact"
                            hide-details
                            class="time-input"
                          />
                          <span class="time-separator">-</span>
                          <v-text-field
                            v-model="day.endTime"
                            type="time"
                            variant="outlined"
                            density="compact"
                            hide-details
                            class="time-input"
                            :disabled="!!localBookingData.dutyType"
                            :readonly="!!localBookingData.dutyType"
                          />
                        </div>
                      </div>
                      <div v-else class="text-grey text-caption mt-2">
                        Please select at least 3 days of the week
                      </div>
                    </div>
                  </div>
                </v-col>
              </v-row>
            </div>
          </div>

          <!-- Location & Preferences Section -->
          <div class="form-section">
            <div class="section-header">
              <div class="section-icon">
                <v-icon color="primary" size="24">mdi-map-marker</v-icon>
              </div>
              <div class="section-info">
                <h3 class="section-title">Location & Preferences</h3>
                <p class="section-subtitle">Where will the service be provided?</p>
              </div>
            </div>
            <div class="section-content">
              <v-row>
                <v-col cols="12" md="6">
                  <div class="form-field">
                    <label class="field-label">ZIP Code *</label>
                    <v-text-field 
                      v-model="localBookingData.zipcode" 
                      variant="outlined" 
                      density="comfortable" 
                      :rules="[v => !!v || 'ZIP code is required', v => /^\d{5}$/.test(v) || 'Please enter a valid 5-digit ZIP code']"
                      placeholder="Enter ZIP code"
                      class="professional-field"
                      maxlength="5"
                      inputmode="numeric"
                      @input="lookupZipCode"
                    >
                      <template v-slot:prepend-inner>
                        <v-icon>mdi-map-marker</v-icon>
                      </template>
                    </v-text-field>
                    <div v-if="zipCodeLocation" class="zip-location-display">
                      {{ zipCodeLocation }}
                    </div>
                  </div>
                </v-col>

                <v-col cols="12" md="8">
                  <div class="form-field">
                    <label class="field-label">Street Address *</label>
                    <v-text-field 
                      v-model="localBookingData.streetAddress" 
                      variant="outlined" 
                      density="comfortable" 
                      :rules="[v => !!v || 'Address is required']"
                      placeholder="Enter your street address"
                      class="professional-field"
                      autocomplete="street-address"
                    />
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="form-field">
                    <label class="field-label">Apartment/Unit Number</label>
                    <v-text-field 
                      v-model="localBookingData.apartmentUnit" 
                      variant="outlined" 
                      density="comfortable"
                      placeholder="Apt, Suite, Unit (optional)"
                      class="professional-field"
                    />
                  </div>
                </v-col>
              </v-row>
            </div>
          </div>

          <!-- Care Requirements Section (Caregiver only) -->
          <div v-if="localBookingData.serviceType === 'Caregiver'" class="form-section">
            <div class="section-header">
              <div class="section-icon">
                <v-icon color="primary" size="24">mdi-account-heart</v-icon>
              </div>
              <div class="section-info">
                <h3 class="section-title">Care Requirements</h3>
                <p class="section-subtitle">Help us match you with the right caregiver</p>
              </div>
            </div>
            <div class="section-content">
              <v-row>
                <v-col cols="12" md="4">
                  <div class="form-field">
                    <label class="field-label">Client Age</label>
                    <v-text-field 
                      v-model="localBookingData.clientAge" 
                      type="number" 
                      variant="outlined" 
                      density="comfortable"
                      placeholder="Age of care recipient"
                      class="professional-field"
                      inputmode="numeric"
                    />
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="form-field">
                    <label class="field-label">Mobility Level</label>
                    <v-select 
                      v-model="localBookingData.mobilityLevel" 
                      :items="mobilityLevels" 
                      variant="outlined" 
                      density="comfortable"
                      placeholder="Select mobility level"
                      class="professional-select"
                    />
                  </div>
                </v-col>

                <v-col cols="12">
                  <div class="form-field">
                    <label class="field-label">Medical Conditions</label>
                    <v-select 
                      v-model="localBookingData.medicalConditions" 
                      :items="medicalConditionsList" 
                      variant="outlined" 
                      density="comfortable" 
                      multiple 
                      chips
                      placeholder="Select any medical conditions"
                      class="professional-select"
                    />
                  </div>
                </v-col>
                <v-col cols="12">
                  <div class="form-field">
                    <label class="field-label">Special Instructions</label>
                    <v-textarea 
                      v-model="localBookingData.notes" 
                      variant="outlined" 
                      rows="4" 
                      placeholder="Please provide any additional information that would help us serve you better..."
                      class="professional-textarea"
                    />
                  </div>
                </v-col>
              </v-row>
            </div>
          </div>
          
          <!-- Special Instructions for Non-Caregiver Services -->
          <div v-else-if="localBookingData.serviceType" class="form-section">
            <div class="section-header">
              <div class="section-icon">
                <v-icon color="primary" size="24">mdi-note-text</v-icon>
              </div>
              <div class="section-info">
                <h3 class="section-title">Service Details</h3>
                <p class="section-subtitle">Tell us about your specific requirements</p>
              </div>
            </div>
            <div class="section-content">
              <v-row>
                <v-col cols="12">
                  <div class="form-field">
                    <label class="field-label">Special Instructions</label>
                    <v-textarea 
                      v-model="localBookingData.notes" 
                      variant="outlined" 
                      rows="4" 
                      placeholder="Please provide any specific requirements or instructions..."
                      class="professional-textarea"
                    />
                  </div>
                </v-col>
              </v-row>
            </div>
          </div>

          <!-- Price Summary & Actions -->
          <div class="form-actions">
            <!-- Price Breakdown -->
            <client-price-summary
              v-if="showPriceSummary"
              :service-type="localBookingData.serviceType"
              :duty-type="localBookingData.dutyType"
              :duration-days="localBookingData.durationDays"
              :referral-discount="referralDiscount"
            />
            
            <!-- Referral Code Section -->
            <div class="referral-section mb-4">
              <v-card class="referral-card" elevation="2">
                <v-card-text class="pa-4">
                  <div class="d-flex gap-3">
                    <v-text-field
                      v-model="localBookingData.referralCode"
                      variant="outlined"
                      density="comfortable"
                      placeholder="Enter referral code for discount"
                      class="referral-field flex-grow-1"
                      :error="!!referralCodeError"
                    />
                    <v-btn
                      color="primary"
                      variant="outlined"
                      size="large"
                      class="apply-btn"
                      @click="applyReferralCode"
                      :disabled="!localBookingData.referralCode"
                    >
                      Apply
                    </v-btn>
                  </div>
                  <v-alert
                    v-if="referralCodeError"
                    type="error"
                    density="compact"
                    class="mt-3"
                    closable
                    @click:close="referralCodeError = ''"
                  >
                    {{ referralCodeError }}
                  </v-alert>
                </v-card-text>
              </v-card>
            </div>
            
            <div class="action-buttons">
              <v-btn 
                variant="outlined" 
                size="x-large" 
                class="cancel-btn" 
                @click="$emit('cancel')"
              >
                <v-icon start>mdi-arrow-left</v-icon>
                Cancel
              </v-btn>
              <v-btn 
                variant="flat"
                size="x-large" 
                class="submit-btn" 
                @click="$emit('submit', localBookingData)"
                :disabled="isSubmitting || !isFormValid"
                :loading="isSubmitting"
              >
                <v-icon start>mdi-check</v-icon>
                {{ isSubmitting ? 'Submitting…' : 'Submit Request' }}
              </v-btn>
            </div>
            <div class="security-notice">
              <v-icon color="success" size="16">mdi-shield-check</v-icon>
              <span>Your information is secure and protected</span>
            </div>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import ClientPriceSummary from './ClientPriceSummary.vue';

const props = defineProps({
  bookingData: {
    type: Object,
    default: () => ({})
  },
  isSubmitting: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['submit', 'cancel', 'update:bookingData']);

// Local copy of booking data
const localBookingData = ref({
  serviceType: '',
  dutyType: '',
  date: '',
  durationDays: 15,
  zipcode: '',
  streetAddress: '',
  apartmentUnit: '',
  clientAge: '',
  mobilityLevel: '',
  medicalConditions: [],
  notes: '',
  referralCode: '',
  selectedDays: {
    monday: { enabled: false, startTime: '09:00', endTime: '17:00' },
    tuesday: { enabled: false, startTime: '09:00', endTime: '17:00' },
    wednesday: { enabled: false, startTime: '09:00', endTime: '17:00' },
    thursday: { enabled: false, startTime: '09:00', endTime: '17:00' },
    friday: { enabled: false, startTime: '09:00', endTime: '17:00' },
    saturday: { enabled: false, startTime: '09:00', endTime: '17:00' },
    sunday: { enabled: false, startTime: '09:00', endTime: '17:00' },
  },
  ...props.bookingData
});

const referralDiscount = ref(0);
const referralCodeError = ref('');
const zipCodeLocation = ref('');
const bookingForm = ref(null);

// Static options
const serviceTypes = ['Caregiver', 'Housekeeping'];
const dutyTypes = [
  { title: '8 Hours per Day', value: '8 Hours per Day' },
  { title: '12 Hours per Day', value: '12 Hours per Day' },
  { title: '24 Hours per Day', value: '24 Hours per Day' }
];
const durationOptions = [
  { title: '15 Days', value: 15 },
  { title: '30 Days', value: 30 },
  { title: '60 Days', value: 60 },
  { title: '90 Days', value: 90 }
];
const mobilityLevels = [
  { title: 'Independent', value: 'independent' },
  { title: 'Assisted', value: 'assisted' },
  { title: 'Wheelchair', value: 'wheelchair' },
  { title: 'Bedridden', value: 'bedridden' }
];
const medicalConditionsList = [
  'Diabetes',
  'Heart Disease',
  'Alzheimer\'s/Dementia',
  'Parkinson\'s Disease',
  'Stroke Recovery',
  'Cancer',
  'Arthritis',
  'COPD',
  'Kidney Disease',
  'Other'
];

// Computed
const today = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const selectedDaysCount = computed(() => {
  return Object.values(localBookingData.value.selectedDays).filter(d => d.enabled).length;
});

const showPriceSummary = computed(() => {
  return localBookingData.value.serviceType && 
         localBookingData.value.dutyType && 
         localBookingData.value.durationDays;
});

const isFormValid = computed(() => {
  return localBookingData.value.serviceType &&
         localBookingData.value.dutyType &&
         localBookingData.value.date &&
         localBookingData.value.zipcode &&
         localBookingData.value.streetAddress &&
         selectedDaysCount.value >= 3;
});

// Methods
const toggleDay = (dayKey) => {
  localBookingData.value.selectedDays[dayKey].enabled = 
    !localBookingData.value.selectedDays[dayKey].enabled;
};

const getDayLabel = (key) => {
  const labels = {
    monday: 'Mon',
    tuesday: 'Tue',
    wednesday: 'Wed',
    thursday: 'Thu',
    friday: 'Fri',
    saturday: 'Sat',
    sunday: 'Sun'
  };
  return labels[key] || key;
};

const getFullDayName = (key) => {
  const names = {
    monday: 'Monday',
    tuesday: 'Tuesday',
    wednesday: 'Wednesday',
    thursday: 'Thursday',
    friday: 'Friday',
    saturday: 'Saturday',
    sunday: 'Sunday'
  };
  return names[key] || key;
};

/**
 * NY ZIP Code Validation Helper
 * Valid NY ZIPs: 10xxx-14xxx range OR special cases (00501, 00544, 06390)
 */
const isValidNYZip = (zip) => {
  if (!zip) return false;
  const nyZipRegex = /^(00501|00544|06390|1[0-4]\d{3})(-\d{4})?$/;
  return nyZipRegex.test(zip);
};

const getNYRegionFromZip = (zip) => {
  if (!zip || !isValidNYZip(zip)) return null;
  const prefix = parseInt(zip.substring(0, 3), 10);
  if (prefix >= 100 && prefix <= 102) return 'Manhattan, NY';
  if (prefix === 103) return 'Staten Island, NY';
  if (prefix === 104) return 'Bronx, NY';
  if (prefix >= 105 && prefix <= 109) return 'Westchester, NY';
  if (prefix >= 110 && prefix <= 111) return 'Long Island, NY';
  if (prefix === 112) return 'Brooklyn, NY';
  if (prefix >= 113 && prefix <= 119) return 'Long Island, NY';
  if (prefix >= 120 && prefix <= 129) return 'Capital Region, NY';
  if (prefix >= 130 && prefix <= 139) return 'Central NY';
  if (prefix >= 140 && prefix <= 149) return 'Western NY';
  return 'New York, NY';
};

const lookupZipCode = async () => {
  const zip = localBookingData.value.zipcode;
  if (zip && zip.length === 5) {
    // Client-side NY validation first
    if (!isValidNYZip(zip)) {
      zipCodeLocation.value = 'Not a NY ZIP (10xxx-14xxx)';
      return;
    }
    
    try {
      const response = await fetch(`/api/zipcode-lookup/${zip}`);
      if (response.ok) {
        const data = await response.json();
        if (data.success && data.location) {
          zipCodeLocation.value = data.location;
          return;
        }
        zipCodeLocation.value = `${data.city}, ${data.state}`;
      } else {
        // Fallback to region for valid NY ZIPs
        zipCodeLocation.value = getNYRegionFromZip(zip) || 'New York, NY';
      }
    } catch (error) {
      console.error('ZIP code lookup failed:', error);
      // Fallback to region
      zipCodeLocation.value = getNYRegionFromZip(zip) || 'New York, NY';
    }
  } else {
    zipCodeLocation.value = '';
  }
};

const applyReferralCode = async () => {
  if (!localBookingData.value.referralCode) return;
  
  try {
    const response = await fetch(`/api/referral/validate/${localBookingData.value.referralCode}`);
    if (response.ok) {
      const data = await response.json();
      if (data.valid) {
        referralDiscount.value = data.discount || 3;
        referralCodeError.value = '';
      } else {
        referralCodeError.value = data.message || 'Invalid referral code';
        referralDiscount.value = 0;
      }
    } else {
      referralCodeError.value = 'Invalid referral code';
      referralDiscount.value = 0;
    }
  } catch (error) {
    referralCodeError.value = 'Unable to validate code';
    referralDiscount.value = 0;
  }
};

// Watch for changes and emit
watch(localBookingData, (newVal) => {
  emit('update:bookingData', newVal);
}, { deep: true });

// Initialize from props
onMounted(() => {
  if (props.bookingData && Object.keys(props.bookingData).length > 0) {
    localBookingData.value = { ...localBookingData.value, ...props.bookingData };
  }
});
</script>

<style scoped>
.client-booking-form {
  max-width: 1000px;
  margin: 0 auto;
}

.booking-header-section {
  margin-bottom: 2rem;
}

.booking-brand-header {
  background: linear-gradient(135deg, #0B4FA2 0%, #1565C0 100%);
  border-radius: 16px;
  padding: 2rem;
  color: white;
}

.brand-logo-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.brand-logo-wrapper {
  width: 60px;
  height: 60px;
  background: white;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
}

.brand-logo-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.brand-title {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0;
}

.brand-subtitle {
  font-size: 0.95rem;
  opacity: 0.9;
  margin: 0;
}

.trust-indicators {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.trust-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.professional-form-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
}

.form-section {
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid #e5e7eb;
}

.form-section:last-of-type {
  border-bottom: none;
}

.section-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.section-icon {
  width: 48px;
  height: 48px;
  background: #e3f2fd;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.section-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0.25rem 0 0 0;
}

.form-field {
  margin-bottom: 0.5rem;
}

.field-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.day-buttons-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.day-button {
  min-width: 60px;
}

.selected-days-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.selected-day-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  background: #f8fafc;
  border-radius: 8px;
}

.day-name {
  min-width: 100px;
  font-weight: 500;
}

.time-input {
  max-width: 120px;
}

.time-separator {
  color: #64748b;
}

.zip-location-display {
  font-size: 0.875rem;
  color: #10b981;
  margin-top: 0.25rem;
  padding-left: 0.5rem;
}

.form-actions {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #e5e7eb;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.cancel-btn {
  color: #64748b;
}

.submit-btn {
  background: linear-gradient(135deg, #0B4FA2 0%, #1565C0 100%);
  color: white;
  min-width: 200px;
}

.security-notice {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 1rem;
  font-size: 0.875rem;
  color: #64748b;
}

.referral-section {
  max-width: 500px;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .booking-brand-header {
    padding: 1.5rem;
  }

  .brand-title {
    font-size: 1.5rem;
  }

  .trust-indicators {
    flex-direction: column;
    gap: 0.75rem;
  }

  .professional-form-card {
    padding: 1rem;
  }

  .section-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .action-buttons {
    flex-direction: column;
  }

  .action-buttons .v-btn {
    width: 100%;
  }

  .selected-day-item {
    flex-wrap: wrap;
  }

  .day-name {
    min-width: 100%;
    margin-bottom: 0.5rem;
  }
}

@media (max-width: 480px) {
  .day-buttons-row {
    justify-content: center;
  }

  .day-button {
    min-width: 44px;
    padding: 0 8px;
  }
}
</style>
