<template>
  <modal-template 
    v-model="isOpen" 
    :title="`${client?.name} - Client Profile`" 
    max-width="700"
    :show-actions="false"
  >
    <div v-if="client">
      <v-row>
        <v-col cols="12" md="4" class="text-center">
          <v-avatar :color="client.avatarColor" size="120" class="mb-4">
            <span class="text-h3 font-weight-bold">{{ client.initials }}</span>
          </v-avatar>
          <h3 class="client-name">{{ client.name }}</h3>
          <p class="client-age">Age: {{ client.age }}</p>
          <v-chip :color="getServiceColor(client.careType)" class="mt-2">{{ client.careType }}</v-chip>
        </v-col>
        <v-col cols="12" md="8">
          <div class="client-details">
            <div class="detail-section mb-4">
              <h4 class="section-title">Contact Information</h4>
              <div class="detail-item">
                <v-icon size="small" class="mr-2">mdi-phone</v-icon>
                <span>{{ client.phone || '(646) 282-8282' }}</span>
              </div>
              <div class="detail-item">
                <v-icon size="small" class="mr-2">mdi-email</v-icon>
                <span>{{ client.email || 'client@example.com' }}</span>
              </div>
              <div class="detail-item">
                <v-icon size="small" class="mr-2">mdi-map-marker</v-icon>
                <span>{{ client.address || '123 Main St, New York, NY' }}</span>
              </div>
            </div>
            
            <div class="detail-section mb-4">
              <h4 class="section-title">Care Information</h4>
              <div class="detail-item">
                <v-icon size="small" class="mr-2">mdi-calendar</v-icon>
                <span>Client since: {{ client.since }}</span>
              </div>
              <div class="detail-item">
                <v-icon size="small" class="mr-2">mdi-clock</v-icon>
                <span>Next visit: {{ client.nextVisit }}</span>
              </div>
              <div class="detail-item">
                <v-icon size="small" class="mr-2">mdi-heart-pulse</v-icon>
                <span>Care type: {{ client.careType }}</span>
              </div>
            </div>

            <div class="detail-section">
              <h4 class="section-title">Emergency Contact</h4>
              <div class="detail-item">
                <v-icon size="small" class="mr-2">mdi-account</v-icon>
                <span>{{ client.emergencyContact || 'Jane Doe (Daughter)' }}</span>
              </div>
              <div class="detail-item">
                <v-icon size="small" class="mr-2">mdi-phone</v-icon>
                <span>{{ client.emergencyPhone || '(646) 282-8282' }}</span>
              </div>
            </div>
          </div>
        </v-col>
      </v-row>
      
      <v-divider class="my-4" />
      
      <div class="modal-actions d-flex justify-end gap-2">
        <v-btn variant="outlined" color="primary" prepend-icon="mdi-calendar">Schedule Visit</v-btn>
        <v-btn variant="outlined" color="success" prepend-icon="mdi-phone">Call Client</v-btn>
        <v-btn color="primary" prepend-icon="mdi-pencil">Edit Profile</v-btn>
      </div>
    </div>
  </modal-template>
</template>

<script setup>
import { computed } from 'vue';
import ModalTemplate from './ModalTemplate.vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  client: { type: Object, default: null }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const getServiceColor = (careType) => {
  const colors = {
    'Elderly Care': 'success',
    'Personal Care': 'primary',
    'Companion Care': 'purple',
    'Physical Therapy': 'orange'
  };
  return colors[careType] || 'grey';
};
</script>

<style scoped>
.client-name {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 4px;
}

.client-age {
  color: #666;
  font-size: 1rem;
  margin: 0;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 12px;
}

.detail-item {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
  font-size: 0.95rem;
  color: #4b5563;
}

.detail-section {
  background: #f8f9fa;
  padding: 16px;
  border-radius: 8px;
}
</style>