<template>
  <div>
    <!-- Header Section -->
    <div class="browse-header mb-6">
      <div class="d-flex justify-space-between align-items-center mb-4">
        <div>
          <h1 class="browse-title">Browse Caregivers</h1>
          <p class="browse-subtitle">Find the perfect caregiver for your needs</p>
        </div>
        <v-chip color="primary" size="large" class="caregiver-count-chip">
          {{ filteredCaregivers.length }} caregivers available
        </v-chip>
      </div>
    </div>

    <!-- Search and Filters -->
    <v-card elevation="0" class="filter-card mb-6">
      <v-card-text class="pa-6">
        <v-row align="center">
          <v-col cols="12" md="4">
            <v-text-field
              v-model="searchQuery"
              prepend-inner-icon="mdi-magnify"
              label="Search caregivers, specialties..."
              variant="outlined"
              density="comfortable"
              hide-details
              clearable
            />
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="filterSpecialty"
              :items="specialties"
              label="All Categories"
              variant="outlined"
              density="comfortable"
              hide-details
            />
          </v-col>
          <v-col cols="12" md="2">
            <v-select
              v-model="filterLocation"
              :items="locationOptions"
              label="All Locations"
              variant="outlined"
              density="comfortable"
              hide-details
            />
          </v-col>
          <v-col cols="12" md="2">
            <v-select
              v-model="filterAvailability"
              :items="['All', 'Available', 'Ongoing Contract']"
              label="All"
              variant="outlined"
              density="comfortable"
              hide-details
            />
          </v-col>
          <v-col cols="12" md="2">
            <v-btn
              color="grey-darken-1"
              variant="outlined"
              block
              size="large"
              prepend-icon="mdi-filter-outline"
              @click="applyFilters"
            >
              Apply Filters
            </v-btn>
          </v-col>
        </v-row>
        <div class="mt-4" v-if="searchQuery || filterSpecialty !== 'All' || filterLocation !== 'All' || filterAvailability !== 'All'">
          <v-chip
            closable
            color="primary"
            variant="outlined"
            class="mr-2"
            @click:close="resetFilters"
          >
            <v-icon start>mdi-filter-remove</v-icon>
            Reset Filters
          </v-chip>
        </div>
      </v-card-text>
    </v-card>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
      <p class="mt-4 text-grey">Loading caregivers...</p>
    </div>

    <!-- Caregivers Grid -->
    <v-row v-else>
      <v-col
        v-for="caregiver in filteredCaregivers"
        :key="caregiver.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <v-card class="caregiver-card" elevation="2">
          <!-- Caregiver Image/Avatar -->
          <div class="caregiver-image-wrapper">
            <!-- Show uploaded avatar if available, otherwise show initials -->
            <v-img
              v-if="caregiver.hasCustomAvatar && caregiver.avatar"
              :src="caregiver.avatar"
              height="200"
              cover
              class="caregiver-image"
            >
              <template v-slot:error>
                <div class="caregiver-avatar-fallback d-flex align-center justify-center" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                  <span class="text-h2 font-weight-bold text-white">{{ caregiver.initials || getInitials(caregiver.name) }}</span>
                </div>
              </template>
            </v-img>
            <div v-else class="caregiver-avatar-fallback d-flex align-center justify-center" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
              <span class="text-h2 font-weight-bold text-white">{{ caregiver.initials || getInitials(caregiver.name) }}</span>
            </div>
            <v-chip
              :color="caregiver.availability === 'available' ? 'success' : 'warning'"
              size="small"
              class="availability-chip"
            >
              {{ caregiver.availability === 'available' ? 'Available' : 'Ongoing Contract' }}
            </v-chip>
          </div>

          <!-- Caregiver Info -->
          <v-card-text class="pa-4">
            <h3 class="caregiver-name mb-1">{{ caregiver.name }}</h3>
            <p class="caregiver-specialty mb-3">{{ caregiver.specialty }}</p>



            <!-- Experience & Certifications -->
            <div class="info-grid mb-3">
              <div class="info-item">
                <v-icon size="16" color="grey-darken-1">mdi-briefcase</v-icon>
                <span class="info-text">{{ caregiver.experience }} years</span>
              </div>
              <div class="info-item">
                <v-icon size="16" color="grey-darken-1">mdi-certificate</v-icon>
                <span class="info-text">{{ caregiver.certifications }}</span>
              </div>
            </div>

            <!-- Action Button -->
            <v-btn
              color="primary"
              variant="outlined"
              size="small"
              block
              prepend-icon="mdi-information"
              @click="viewDetails(caregiver)"
            >
              Details
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Empty State -->
    <v-card v-if="!loading && filteredCaregivers.length === 0" elevation="0" class="empty-state mt-6">
      <v-card-text class="pa-12 text-center">
        <v-icon size="80" color="grey-lighten-1">mdi-account-search</v-icon>
        <h2 class="mt-6 mb-3">No Caregivers Found</h2>
        <p class="text-grey mb-4">Try adjusting your filters to see more results</p>
        <v-btn color="primary" variant="outlined" @click="resetFilters">Reset Filters</v-btn>
      </v-card-text>
    </v-card>

    <!-- Caregiver Details Dialog -->
    <v-dialog v-model="detailsDialog" max-width="700">
      <v-card v-if="selectedCaregiver" class="modal-card">
        <div class="modal-header">
          <v-btn icon="mdi-close" variant="text" size="small" class="close-btn" @click="detailsDialog = false" />
        </div>
        
        <v-card-text class="pa-8">
          <div class="text-center mb-6">
            <v-avatar size="140" class="mb-4 profile-avatar" :color="selectedCaregiver.hasCustomAvatar ? '' : 'primary'" :style="!selectedCaregiver.hasCustomAvatar ? 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);' : ''">
              <v-img v-if="selectedCaregiver.hasCustomAvatar && selectedCaregiver.avatar" :src="selectedCaregiver.avatar">
                <template v-slot:error>
                  <span class="text-h2 font-weight-bold text-white">{{ selectedCaregiver.initials || getInitials(selectedCaregiver.name) }}</span>
                </template>
              </v-img>
              <span v-else class="text-h2 font-weight-bold text-white">{{ selectedCaregiver.initials || getInitials(selectedCaregiver.name) }}</span>
            </v-avatar>
            <h2 class="modal-name mb-2">{{ selectedCaregiver.name }}</h2>
            <p class="modal-specialty mb-3">{{ selectedCaregiver.specialty }}</p>
            <v-chip
              :color="selectedCaregiver.availability === 'available' ? 'success' : 'warning'"
              size="large"
              class="status-chip"
            >
              {{ selectedCaregiver.availability === 'available' ? 'Available Now' : 'Ongoing Contract' }}
            </v-chip>
          </div>

          <v-divider class="my-6" />

          <!-- Bio Section -->
          <div class="bio-section mb-6">
            <h3 class="section-title mb-3">About</h3>
            <p class="bio-text">{{ selectedCaregiver.bio }}</p>
          </div>

          <!-- Stats Grid -->
          <div class="stats-grid mb-6">
            <div class="stat-card">
              <v-icon color="grey-darken-1" size="28">mdi-briefcase</v-icon>
              <div class="stat-content">
                <div class="stat-value">{{ selectedCaregiver.experience }} years</div>
                <div class="stat-label">Experience</div>
              </div>
            </div>
            <div class="stat-card">
              <v-icon color="grey-darken-1" size="28">mdi-certificate</v-icon>
              <div class="stat-content">
                <div class="stat-value">{{ selectedCaregiver.certifications }}</div>
                <div class="stat-label">Certifications</div>
              </div>
            </div>
          </div>


        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['request-booking']);

const searchQuery = ref('');
const filterSpecialty = ref('All');
const filterLocation = ref('All');
const filterAvailability = ref('All');
const detailsDialog = ref(false);
const selectedCaregiver = ref(null);
const loading = ref(true);

const specialties = ['All', 'Elderly Care', 'House Cleaning', 'Personal Care', 'Physical Therapy', 'Childcare', 'Companion Care'];
const locationOptions = ['All', 'Manhattan', 'Brooklyn', 'Queens', 'Bronx', 'Staten Island', 'Nassau County', 'Suffolk County', 'Westchester County'];

const caregivers = ref([]);

const fetchCaregivers = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/caregivers');
    caregivers.value = response.data.caregivers;
  } catch (error) {
    console.error('Error fetching caregivers:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchCaregivers();
});

const filteredCaregivers = computed(() => {
  return caregivers.value.filter(c => {
    const matchesSearch = !searchQuery.value || 
      c.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      c.specialty.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesSpecialty = filterSpecialty.value === 'All' || c.specialty.includes(filterSpecialty.value);
    const matchesLocation = filterLocation.value === 'All' || 
      (c.location && c.location.includes(filterLocation.value)) ||
      (c.borough && c.borough.includes(filterLocation.value));
    
    let matchesAvailability = true;
    if (filterAvailability.value !== 'All') {
      if (filterAvailability.value === 'Available') {
        matchesAvailability = c.availability === 'available';
      } else if (filterAvailability.value === 'Ongoing Contract') {
        matchesAvailability = c.availability === 'busy';
      }
    }
    
    return matchesSearch && matchesSpecialty && matchesLocation && matchesAvailability;
  });
});

const applyFilters = () => {
  // Filters are reactive, so this is just for UI feedback
};

const resetFilters = () => {
  searchQuery.value = '';
  filterSpecialty.value = 'All';
  filterLocation.value = 'All';
  filterAvailability.value = 'All';
};

const viewDetails = (caregiver) => {
  selectedCaregiver.value = caregiver;
  detailsDialog.value = true;
};

const requestBooking = (caregiver) => {
  detailsDialog.value = false;
  emit('request-booking', caregiver);
};

// Helper function to get initials from name
const getInitials = (name) => {
  if (!name) return '??';
  const words = name.trim().split(' ');
  let initials = '';
  for (const word of words) {
    if (word) {
      initials += word[0].toUpperCase();
    }
  }
  return initials.substring(0, 2);
};
</script>

<style scoped>
.browse-header {
  margin-bottom: 24px;
}

.browse-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1a1a1a;
  letter-spacing: -0.02em;
}

.browse-subtitle {
  font-size: 1rem;
  color: #6b7280;
  margin-top: 4px;
}

.caregiver-count-chip {
  font-weight: 600;
  font-size: 0.95rem;
}

.filter-card {
  border-radius: 16px !important;
  border: 1px solid #e5e7eb !important;
  background: #fafbfc !important;
}

.caregiver-card {
  border-radius: 16px !important;
  overflow: hidden;
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.caregiver-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
}

.caregiver-image-wrapper {
  position: relative;
}

.caregiver-image {
  border-radius: 0;
}

.availability-chip {
  position: absolute;
  top: 12px;
  right: 12px;
  font-weight: 600;
}

.caregiver-name {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1a1a1a;
  letter-spacing: -0.01em;
}

.caregiver-specialty {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.rating-value {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a1a1a;
}

.rating-reviews {
  font-size: 0.875rem;
  color: #6b7280;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 6px;
}

.info-text {
  font-size: 0.875rem;
  color: #4b5563;
  font-weight: 500;
}

.empty-state {
  border-radius: 16px !important;
  border: 2px dashed #e5e7eb !important;
}

.details-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
}

.detail-label {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.detail-value {
  font-size: 1rem;
  color: #1a1a1a;
  font-weight: 600;
}

.modal-card {
  border-radius: 20px !important;
}

.modal-header {
  position: absolute;
  top: 16px;
  right: 16px;
  z-index: 10;
}

.close-btn {
  background: rgba(255, 255, 255, 0.9) !important;
}

.profile-avatar {
  border: 4px solid #f0f0f0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.modal-name {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1a1a1a;
  letter-spacing: -0.02em;
}

.modal-specialty {
  font-size: 1.125rem;
  color: #6b7280;
  font-weight: 500;
}

.status-chip {
  font-weight: 600;
  padding: 8px 16px;
}

.bio-section {
  background: #f9fafb;
  padding: 20px;
  border-radius: 12px;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1a1a1a;
}

.bio-text {
  font-size: 0.95rem;
  color: #4b5563;
  line-height: 1.6;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.stat-card {
  background: #f9fafb;
  padding: 20px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid #e5e7eb;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a1a1a;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin-top: 2px;
}

.contact-section {
  background: #f9fafb;
  padding: 20px;
  border-radius: 12px;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: white;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.contact-label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.contact-value {
  font-size: 0.95rem;
  color: #1a1a1a;
  font-weight: 600;
  margin-top: 2px;
}
</style>
