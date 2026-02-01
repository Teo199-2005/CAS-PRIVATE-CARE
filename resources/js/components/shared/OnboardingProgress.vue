<template>
  <div 
    class="onboarding-progress" 
    :class="{ 'onboarding-complete': isComplete }"
    v-if="!isComplete || showCompleted"
  >
    <!-- Header Row -->
    <div class="onboarding-header">
      <div class="d-flex align-center">
        <span class="onboarding-title">{{ isComplete ? 'Setup Complete!' : 'Complete Your Profile' }}</span>
        <v-chip 
          :color="isComplete ? 'success' : progressColor" 
          size="x-small"
          variant="flat"
          class="ml-2"
        >
          {{ completedSteps }}/{{ totalSteps }}
        </v-chip>
      </div>
      <v-progress-linear
        :model-value="progressPercent"
        :color="isComplete ? 'success' : progressColor"
        height="4"
        rounded
        class="mt-2"
        bg-color="grey-lighten-3"
      />
    </div>

    <!-- Compact Steps -->
    <div class="steps-container">
      <div 
        v-for="(step, index) in steps" 
        :key="step.id"
        class="step-item"
        :class="{ 
          'step-completed': step.completed,
          'step-current': !step.completed && index === currentStepIndex
        }"
        @click="goToStep(step)"
      >
        <div class="step-indicator">
          <v-icon 
            v-if="step.completed" 
            size="14" 
            color="white"
          >mdi-check</v-icon>
          <span v-else class="step-number">{{ index + 1 }}</span>
        </div>
        <span class="step-label">{{ step.title }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
  role: { type: String, default: 'caregiver' },
  userData: { type: Object, default: () => ({}) },
  applicationStatus: { type: String, default: 'pending' },
  bankConnected: { type: Boolean, default: false },
  w9Submitted: { type: Boolean, default: false },
  profileComplete: { type: Boolean, default: false },
  showCompleted: { type: Boolean, default: false }
});

const emit = defineEmits(['step-click', 'complete']);

const steps = computed(() => [
  {
    id: 'application',
    title: 'Application',
    completed: ['pending', 'approved', 'active'].includes(props.applicationStatus),
    action: 'view-application'
  },
  {
    id: 'approval',
    title: 'Approval',
    completed: props.applicationStatus === 'approved' || props.applicationStatus === 'active',
    action: null
  },
  {
    id: 'w9',
    title: 'W9 Form',
    completed: props.w9Submitted,
    action: 'submit-w9'
  },
  {
    id: 'bank',
    title: 'Bank Account',
    completed: props.bankConnected,
    action: 'connect-bank'
  },
  {
    id: 'profile',
    title: 'Profile',
    completed: props.profileComplete,
    action: 'edit-profile'
  }
]);

const completedSteps = computed(() => steps.value.filter(s => s.completed).length);
const totalSteps = computed(() => steps.value.length);
const progressPercent = computed(() => Math.round((completedSteps.value / totalSteps.value) * 100));
const isComplete = computed(() => completedSteps.value === totalSteps.value);

const progressColor = computed(() => {
  if (progressPercent.value >= 80) return 'success';
  if (progressPercent.value >= 50) return 'warning';
  return 'primary';
});

const currentStepIndex = computed(() => {
  const idx = steps.value.findIndex(s => !s.completed);
  return idx === -1 ? steps.value.length - 1 : idx;
});

const goToStep = (step) => {
  if (step.action) {
    emit('step-click', step);
  }
};

watch(isComplete, (complete) => {
  if (complete) emit('complete');
});
</script>

<style scoped>
.onboarding-progress {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
}

.onboarding-progress.onboarding-complete {
  background: #f0fdf4;
  border-color: #86efac;
}

.onboarding-header {
  margin-bottom: 12px;
}

.onboarding-title {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.onboarding-complete .onboarding-title {
  color: #059669;
}

.steps-container {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.step-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.15s ease;
  font-size: 12px;
}

.step-item:hover {
  border-color: #10b981;
  background: #f0fdf4;
}

.step-completed {
  background: #ecfdf5;
  border-color: #10b981;
}

.step-current {
  border-color: #fbbf24;
  background: #fffbeb;
}

.step-indicator {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e5e7eb;
  flex-shrink: 0;
}

.step-completed .step-indicator {
  background: #10b981;
}

.step-current .step-indicator {
  background: #fbbf24;
}

.step-number {
  font-size: 10px;
  font-weight: 600;
  color: #6b7280;
}

.step-current .step-number {
  color: white;
}

.step-label {
  color: #6b7280;
  font-weight: 500;
  white-space: nowrap;
}

.step-completed .step-label {
  color: #059669;
}

.step-current .step-label {
  color: #92400e;
}

/* Mobile adjustments */
@media (max-width: 600px) {
  .onboarding-progress {
    padding: 12px;
  }
  
  .steps-container {
    gap: 4px;
  }
  
  .step-item {
    padding: 4px 8px;
    font-size: 11px;
  }
  
  .step-indicator {
    width: 16px;
    height: 16px;
  }
  
  .step-number {
    font-size: 9px;
  }
}
</style>
