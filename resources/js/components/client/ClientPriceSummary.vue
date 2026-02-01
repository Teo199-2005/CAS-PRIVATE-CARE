<template>
  <v-card class="price-summary-card" elevation="3">
    <div class="price-header">
      <v-icon color="white" size="24">mdi-calculator</v-icon>
      <h4>Price Breakdown</h4>
    </div>
    <v-card-text class="pa-4">
      <div class="price-breakdown">
        <div class="price-row">
          <span class="price-label">Service Type:</span>
          <span class="price-value">{{ serviceType }}</span>
        </div>
        <div class="price-row">
          <span class="price-label">Hours per Day:</span>
          <span class="price-value">{{ dutyType }}</span>
        </div>
        <div class="price-row">
          <span class="price-label">Daily Rate:</span>
          <span class="price-value">${{ dailyRate.toFixed(2) }}</span>
        </div>
        <div class="price-row">
          <span class="price-label">Duration:</span>
          <span class="price-value">{{ durationDays }} days</span>
        </div>
        
        <v-divider class="my-3"></v-divider>
        
        <div class="price-row subtotal">
          <span class="price-label">Subtotal:</span>
          <span class="price-value">${{ subtotal.toFixed(2) }}</span>
        </div>
        
        <div v-if="referralDiscount > 0" class="price-row discount">
          <span class="price-label">
            <v-icon size="16" color="success" class="mr-1">mdi-tag</v-icon>
            Referral Discount ({{ referralDiscount }}%):
          </span>
          <span class="price-value text-success">-${{ discountAmount.toFixed(2) }}</span>
        </div>
        
        <div class="price-row service-fee">
          <span class="price-label">
            <v-tooltip location="top">
              <template v-slot:activator="{ props }">
                <span v-bind="props" style="cursor: help;">
                  Platform Fee (15%):
                  <v-icon size="14" class="ml-1">mdi-information-outline</v-icon>
                </span>
              </template>
              <span>Includes insurance, background checks, and 24/7 support</span>
            </v-tooltip>
          </span>
          <span class="price-value">${{ platformFee.toFixed(2) }}</span>
        </div>
        
        <v-divider class="my-3"></v-divider>
        
        <div class="price-row total">
          <span class="price-label">Estimated Total:</span>
          <span class="price-value">${{ estimatedTotal.toFixed(2) }}</span>
        </div>
        
        <div class="weekly-breakdown mt-3">
          <span class="weekly-label">Approximately per week:</span>
          <span class="weekly-value">${{ weeklyRate.toFixed(2) }}</span>
        </div>
      </div>
      
      <v-alert 
        type="info" 
        density="compact" 
        variant="tonal" 
        class="mt-4"
        icon="mdi-information"
      >
        <span class="text-caption">
          Final pricing may vary based on specific care requirements. 
          You'll receive a detailed quote before confirmation.
        </span>
      </v-alert>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  serviceType: {
    type: String,
    default: ''
  },
  dutyType: {
    type: String,
    default: ''
  },
  durationDays: {
    type: Number,
    default: 15
  },
  referralDiscount: {
    type: Number,
    default: 0
  }
});

// Pricing rates based on service type and hours
const RATES = {
  Caregiver: {
    '8 Hours per Day': 280,
    '12 Hours per Day': 380,
    '24 Hours per Day': 550
  },
  Housekeeping: {
    '8 Hours per Day': 200,
    '12 Hours per Day': 280,
    '24 Hours per Day': 400
  }
};

const dailyRate = computed(() => {
  if (!props.serviceType || !props.dutyType) return 0;
  return RATES[props.serviceType]?.[props.dutyType] || 0;
});

const subtotal = computed(() => {
  return dailyRate.value * props.durationDays;
});

const discountAmount = computed(() => {
  return subtotal.value * (props.referralDiscount / 100);
});

const platformFee = computed(() => {
  return (subtotal.value - discountAmount.value) * 0.15;
});

const estimatedTotal = computed(() => {
  return subtotal.value - discountAmount.value + platformFee.value;
});

const weeklyRate = computed(() => {
  if (props.durationDays === 0) return 0;
  return (estimatedTotal.value / props.durationDays) * 7;
});
</script>

<style scoped>
.price-summary-card {
  border-radius: 12px;
  overflow: hidden;
  max-width: 400px;
}

.price-header {
  background: linear-gradient(135deg, #0B4FA2 0%, #1565C0 100%);
  color: white;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.price-header h4 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.price-breakdown {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.25rem 0;
}

.price-label {
  color: #64748b;
  font-size: 0.875rem;
}

.price-value {
  font-weight: 500;
  color: #1e293b;
}

.price-row.subtotal {
  padding-top: 0.5rem;
}

.price-row.subtotal .price-label,
.price-row.subtotal .price-value {
  font-weight: 600;
}

.price-row.discount .price-label {
  color: #10b981;
}

.price-row.service-fee {
  font-size: 0.875rem;
}

.price-row.total {
  background: #f8fafc;
  margin: 0 -1rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
}

.price-row.total .price-label {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
}

.price-row.total .price-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #0B4FA2;
}

.weekly-breakdown {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0.75rem;
  background: #e3f2fd;
  border-radius: 6px;
  font-size: 0.875rem;
}

.weekly-label {
  color: #1565C0;
}

.weekly-value {
  font-weight: 600;
  color: #0B4FA2;
}

@media (max-width: 480px) {
  .price-summary-card {
    max-width: 100%;
  }
}
</style>
