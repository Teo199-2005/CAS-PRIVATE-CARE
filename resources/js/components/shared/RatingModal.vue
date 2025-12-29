<template>
  <v-dialog v-model="dialog" max-width="600" persistent>
    <v-card>
      <v-card-title class="pa-6" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
        <div class="d-flex align-center justify-space-between w-100">
          <div class="d-flex align-center">
            <v-icon size="28" class="mr-3">mdi-star</v-icon>
            <span style="font-size: 1.5rem; font-weight: 700;">Rate Your Experience</span>
          </div>
          <v-btn icon variant="text" @click="close" style="color: white;">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>
      </v-card-title>

      <v-card-text class="pa-6">
        <!-- Service Info -->
        <div class="service-info mb-6">
          <div class="d-flex align-center mb-3">
            <v-icon color="primary" class="mr-2">mdi-medical-bag</v-icon>
            <span class="text-h6">{{ booking?.service_type || 'Service' }}</span>
          </div>
          <div class="text-body-2 text-grey">
            <v-icon size="16" class="mr-1">mdi-calendar</v-icon>
            {{ formatDate(booking?.service_date) }}
          </div>
        </div>

        <!-- Select Caregiver (if multiple) -->
        <v-select
          v-if="caregivers.length > 1"
          v-model="selectedCaregiver"
          :items="caregivers"
          item-title="name"
          item-value="id"
          label="Select Caregiver to Review"
          variant="outlined"
          class="mb-4"
          prepend-inner-icon="mdi-account"
        >
          <template v-slot:item="{ props, item }">
            <v-list-item v-bind="props" :title="item.raw.name">
              <template v-slot:prepend>
                <v-avatar color="primary" size="32">
                  <span class="text-white">{{ item.raw.name?.charAt(0) }}</span>
                </v-avatar>
              </template>
            </v-list-item>
          </template>
        </v-select>

        <div v-if="caregivers.length === 1" class="selected-caregiver mb-4">
          <div class="d-flex align-center pa-3" style="background: #f3f4f6; border-radius: 8px;">
            <v-avatar color="primary" size="40" class="mr-3">
              <span class="text-white text-h6">{{ caregivers[0].name?.charAt(0) }}</span>
            </v-avatar>
            <div>
              <div class="font-weight-bold">{{ caregivers[0].name }}</div>
              <div class="text-caption text-grey">Your Caregiver</div>
            </div>
          </div>
        </div>

        <!-- Rating Stars -->
        <div class="rating-section mb-6">
          <label class="text-subtitle-1 font-weight-bold mb-3 d-block">How would you rate the service?</label>
          <div class="d-flex align-center justify-center mb-2">
            <v-rating
              v-model="form.rating"
              :length="5"
              :size="48"
              color="amber"
              active-color="amber"
              hover
            ></v-rating>
          </div>
          <div class="text-center text-body-2 text-grey">
            {{ ratingText }}
          </div>
        </div>

        <!-- Recommendation -->
        <div class="recommendation-section mb-4">
          <label class="text-subtitle-1 font-weight-bold mb-3 d-block">
            Would you recommend this caregiver?
          </label>
          <v-btn-toggle
            v-model="form.recommend"
            mandatory
            color="primary"
            class="w-100"
            style="height: 56px;"
          >
            <v-btn :value="true" class="flex-grow-1">
              <v-icon start>mdi-thumb-up</v-icon>
              Yes
            </v-btn>
            <v-btn :value="false" class="flex-grow-1">
              <v-icon start>mdi-thumb-down</v-icon>
              No
            </v-btn>
          </v-btn-toggle>
        </div>

        <!-- Comment -->
        <v-textarea
          v-model="form.comment"
          label="Share your experience (optional)"
          variant="outlined"
          rows="4"
          counter="1000"
          maxlength="1000"
          placeholder="Tell us about your experience with this caregiver..."
          prepend-inner-icon="mdi-comment-text"
        ></v-textarea>

        <v-alert v-if="error" type="error" class="mt-4">
          {{ error }}
        </v-alert>
      </v-card-text>

      <v-card-actions class="pa-6 pt-0">
        <v-btn color="grey" variant="outlined" @click="close" :disabled="loading">
          Cancel
        </v-btn>
        <v-spacer></v-spacer>
        <v-btn
          color="primary"
          variant="flat"
          @click="submit"
          :loading="loading"
          :disabled="!isValid"
          size="large"
        >
          <v-icon start>mdi-send</v-icon>
          Submit Review
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  modelValue: Boolean,
  booking: Object,
  caregivers: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue', 'submitted']);

const dialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const selectedCaregiver = ref(null);
const form = ref({
  rating: 0,
  comment: '',
  recommend: true
});

const loading = ref(false);
const error = ref('');

// Auto-select if only one caregiver
watch(() => props.caregivers, (newCaregivers) => {
  if (newCaregivers.length === 1) {
    selectedCaregiver.value = newCaregivers[0].id;
  } else {
    selectedCaregiver.value = null;
  }
}, { immediate: true });

const ratingText = computed(() => {
  const texts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
  return texts[form.value.rating] || 'Select a rating';
});

const isValid = computed(() => {
  return form.value.rating > 0 && selectedCaregiver.value !== null;
});

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const submit = async () => {
  if (!isValid.value) {
    error.value = 'Please provide a rating and select a caregiver';
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    const response = await fetch('/api/reviews', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        booking_id: props.booking.id,
        caregiver_id: selectedCaregiver.value,
        rating: form.value.rating,
        comment: form.value.comment,
        recommend: form.value.recommend
      })
    });

    const data = await response.json();

    if (data.success) {
      emit('submitted', data);
      close();
      resetForm();
    } else {
      error.value = data.message || 'Failed to submit review';
    }
  } catch (err) {
    error.value = 'An error occurred while submitting your review';
    console.error('Review submission error:', err);
  } finally {
    loading.value = false;
  }
};

const close = () => {
  dialog.value = false;
};

const resetForm = () => {
  form.value = {
    rating: 0,
    comment: '',
    recommend: true
  };
  selectedCaregiver.value = null;
  error.value = '';
};
</script>

<style scoped>
.service-info {
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  padding: 16px;
  border-radius: 12px;
  border: 1px solid #bfdbfe;
}

.rating-section {
  padding: 20px;
  background: #fef3c7;
  border-radius: 12px;
  border: 2px solid #fbbf24;
}

.recommendation-section {
  padding: 20px;
  background: #f3f4f6;
  border-radius: 12px;
}

.selected-caregiver {
  border: 2px solid #3b82f6;
  border-radius: 12px;
  overflow: hidden;
}
</style>
