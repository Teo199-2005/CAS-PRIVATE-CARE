<template>
  <div>
    <!-- Header Section -->
    <div class="browse-header mb-6">
      <div class="d-flex justify-space-between align-items-center flex-wrap gap-2 mb-4">
        <div>
          <h1 class="browse-title">Browse Caregivers & Housekeepers</h1>
          <p class="browse-subtitle">Find the perfect caregiver or housekeeper for your needs</p>
        </div>
        <v-btn-toggle v-model="browseTab" mandatory color="primary" density="comfortable">
          <v-btn value="caregivers">Caregivers ({{ filteredCaregivers.length }})</v-btn>
          <v-btn value="housekeepers">Housekeepers ({{ filteredHousekeepers.length }})</v-btn>
        </v-btn-toggle>
      </div>
    </div>

    <!-- Search, sort, and availability -->
    <v-card elevation="0" class="filter-card mb-6">
      <v-card-text class="pa-4">
        <v-row align="center">
          <v-col cols="12" md="5">
            <v-text-field
              v-model="searchQuery"
              prepend-inner-icon="mdi-magnify"
              :label="browseTab === 'caregivers' ? 'Search by name or specialty...' : 'Search by name or service...'"
              variant="outlined"
              density="comfortable"
              hide-details
              clearable
            />
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="sortBy"
              :items="sortOptions"
              item-title="title"
              item-value="value"
              label="Sort by"
              variant="outlined"
              density="comfortable"
              hide-details
            />
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="availabilityFilter"
              :items="[{ title: 'All', value: 'all' }, { title: 'Available now', value: 'available' }]"
              item-title="title"
              item-value="value"
              label="Availability"
              variant="outlined"
              density="comfortable"
              hide-details
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
      <p class="mt-4 text-grey">Loading caregivers...</p>
    </div>

    <!-- Caregivers Grid -->
    <v-row v-else-if="browseTab === 'caregivers'">
      <v-col
        v-for="caregiver in filteredCaregivers"
        :key="'cg-' + caregiver.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <v-card class="caregiver-card type-indicator type-caregiver" elevation="2">
          <div class="caregiver-image-wrapper">
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
            <v-chip color="primary" size="x-small" class="type-chip" density="compact">Caregiver</v-chip>
            <v-chip :color="caregiver.availability === 'available' ? 'success' : 'warning'" size="small" class="availability-chip">
              {{ caregiver.availability === 'available' ? 'Available' : 'Ongoing Contract' }}
            </v-chip>
          </div>
          <v-card-text class="pa-4">
            <h3 class="caregiver-name mb-1">{{ caregiver.name }}</h3>
            <p class="caregiver-specialty mb-2">{{ caregiver.specialty }}</p>
            <div class="d-flex align-center mb-2">
              <v-rating :model-value="parseFloat(caregiver.rating || 0)" :length="5" :size="18" color="amber" active-color="amber" half-increments readonly density="compact"></v-rating>
              <span class="ml-2 text-caption text-grey">{{ parseFloat(caregiver.rating || 0).toFixed(1) }} ({{ caregiver.reviews || caregiver.total_reviews || 0 }})</span>
            </div>
            <div class="info-grid mb-2">
              <div class="info-item">
                <v-icon size="16" color="grey-darken-1">mdi-briefcase</v-icon>
                <span class="info-text">{{ caregiver.experience }} years</span>
              </div>
              <div class="info-item">
                <v-icon size="16" color="grey-darken-1">mdi-certificate</v-icon>
                <span class="info-text">{{ caregiver.certifications }}</span>
              </div>
              <div class="info-item">
                <v-icon size="16" color="grey-darken-1">mdi-cash</v-icon>
                <span class="info-text">${{ caregiver.hourly_rate || caregiver.preferred_hourly_rate_min || 20 }}/hr</span>
              </div>
            </div>
            <div class="contact-preview text-caption text-grey mb-2">
              <span v-if="caregiver.email">{{ caregiver.email }}</span>
              <span v-if="caregiver.phone" class="ml-1">{{ caregiver.phone }}</span>
            </div>
            <v-btn color="primary" variant="outlined" size="small" block prepend-icon="mdi-information" @click="viewDetails(caregiver, 'caregiver')">Details</v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Housekeepers Grid -->
    <v-row v-else>
      <v-col
        v-for="hk in filteredHousekeepers"
        :key="'hk-' + hk.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <v-card class="caregiver-card type-indicator type-housekeeper" elevation="2">
          <div class="caregiver-image-wrapper">
            <v-img
              v-if="hk.hasCustomAvatar && hk.avatar"
              :src="hk.avatar"
              height="200"
              cover
              class="caregiver-image"
            >
              <template v-slot:error>
                <div class="caregiver-avatar-fallback d-flex align-center justify-center" style="height: 200px; background: linear-gradient(135deg, #6A1B9A 0%, #4a148c 100%);">
                  <span class="text-h2 font-weight-bold text-white">{{ hk.initials || getInitials(hk.name) }}</span>
                </div>
              </template>
            </v-img>
            <div v-else class="caregiver-avatar-fallback d-flex align-center justify-center" style="height: 200px; background: linear-gradient(135deg, #6A1B9A 0%, #4a148c 100%);">
              <span class="text-h2 font-weight-bold text-white">{{ hk.initials || getInitials(hk.name) }}</span>
            </div>
            <v-chip color="deep-purple" size="x-small" class="type-chip" density="compact">Housekeeper</v-chip>
            <v-chip :color="hk.availability === 'available' ? 'success' : 'warning'" size="small" class="availability-chip">
              {{ hk.availability === 'available' ? 'Available' : 'Ongoing Contract' }}
            </v-chip>
          </div>
          <v-card-text class="pa-4">
            <h3 class="caregiver-name mb-1">{{ hk.name }}</h3>
            <p class="caregiver-specialty mb-2">{{ hk.specialty }}</p>
            <div class="d-flex align-center mb-2">
              <v-rating :model-value="parseFloat(hk.rating || 0)" :length="5" :size="18" color="amber" active-color="amber" half-increments readonly density="compact"></v-rating>
              <span class="ml-2 text-caption text-grey">{{ parseFloat(hk.rating || 0).toFixed(1) }} ({{ hk.reviews || hk.total_reviews || 0 }})</span>
            </div>
            <div class="info-grid mb-2">
              <div class="info-item">
                <v-icon size="16" color="grey-darken-1">mdi-briefcase</v-icon>
                <span class="info-text">{{ hk.experience }} years</span>
              </div>
              <div class="info-item">
                <v-icon size="16" color="grey-darken-1">mdi-certificate</v-icon>
                <span class="info-text">{{ hk.certifications }}</span>
              </div>
              <div class="info-item">
                <v-icon size="16" color="grey-darken-1">mdi-cash</v-icon>
                <span class="info-text">${{ hk.hourly_rate || 20 }}/hr</span>
              </div>
            </div>
            <div class="contact-preview text-caption text-grey mb-2">
              <span v-if="hk.email">{{ hk.email }}</span>
              <span v-if="hk.phone" class="ml-1">{{ hk.phone }}</span>
            </div>
            <v-btn color="deep-purple" variant="outlined" size="small" block prepend-icon="mdi-information" @click="viewDetails(hk, 'housekeeper')">Details</v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Empty State -->
    <v-card v-if="!loading && (browseTab === 'caregivers' ? filteredCaregivers.length === 0 : filteredHousekeepers.length === 0)" elevation="0" class="empty-state mt-6">
      <v-card-text class="pa-12 text-center">
        <v-icon size="80" color="grey-lighten-1">mdi-account-search</v-icon>
        <h2 class="mt-6 mb-3">{{ browseTab === 'caregivers' ? 'No Caregivers Found' : 'No Housekeepers Found' }}</h2>
        <p class="text-grey mb-4">Try clearing search or changing sort and availability</p>
        <v-btn color="primary" variant="outlined" @click="resetFilters">Clear search & reset</v-btn>
      </v-card-text>
    </v-card>

    <!-- Caregiver / Housekeeper Details Dialog (matches admin View Caregiver Details) -->
    <v-dialog v-model="detailsDialog" max-width="800" scrollable>
      <v-card v-if="selectedCaregiver">
        <v-card-title class="pa-6 view-caregiver-modal-header">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="section-title text-white">{{ selectedContractorType === 'housekeeper' ? 'Housekeeper' : 'Caregiver' }} Details</span>
            <v-btn icon="mdi-close" variant="text" color="white" @click="detailsDialog = false" aria-label="Close" />
          </div>
        </v-card-title>
        <v-card-text class="pa-6 view-caregiver-modal-body">
          <!-- Centered profile (same as admin) -->
          <v-row>
            <v-col cols="12" class="text-center mb-4">
              <v-avatar size="120" color="success" class="mb-3">
                <v-img v-if="selectedCaregiver.hasCustomAvatar && selectedCaregiver.avatar" :src="selectedCaregiver.avatar">
                  <template v-slot:error>
                    <span class="text-h3 font-weight-bold text-white">{{ selectedCaregiver.initials || getInitials(selectedCaregiver.name) }}</span>
                  </template>
                </v-img>
                <span v-else class="text-h3 font-weight-bold text-white">{{ selectedCaregiver.initials || getInitials(selectedCaregiver.name) }}</span>
              </v-avatar>
              <h2 class="text-h5 font-weight-bold mb-2">{{ selectedCaregiver.name }}</h2>
              <v-chip :color="selectedCaregiver.availability === 'available' ? 'success' : 'warning'" class="mt-2">{{ selectedCaregiver.availability === 'available' ? 'Available Now' : 'Ongoing Contract' }}</v-chip>
              <v-chip color="warning" class="mt-2 ml-2">
                <v-icon size="16" class="mr-1">mdi-account-group</v-icon>
                {{ (selectedCaregiver.clients_served ?? 0) }} Clients
              </v-chip>
              <div class="mt-4">
                <div class="d-flex align-center justify-center">
                  <v-rating
                    :model-value="parseFloat(selectedCaregiver.rating || 0)"
                    :length="5"
                    :size="32"
                    color="amber"
                    active-color="amber"
                    half-increments
                    readonly
                    density="compact"
                  />
                  <span class="ml-2 text-h6">{{ parseFloat(selectedCaregiver.rating || 0).toFixed(1) }}</span>
                </div>
                <div class="text-caption text-grey mt-1">
                  {{ (selectedCaregiver.reviews || selectedCaregiver.total_reviews || 0) }} {{ (selectedCaregiver.reviews || selectedCaregiver.total_reviews || 0) === 1 ? 'Review' : 'Reviews' }}
                </div>
              </div>
            </v-col>
          </v-row>

          <v-divider class="mb-4" />

          <!-- Detail grid: v-row v-col + detail-section (same as admin) -->
          <v-row>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">First Name</div>
                <div class="detail-value">{{ selectedCaregiver.first_name || (selectedCaregiver.name || '').split(' ')[0] || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Last Name</div>
                <div class="detail-value">{{ selectedCaregiver.last_name || (selectedCaregiver.name || '').split(' ').slice(1).join(' ') || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Birthdate</div>
                <div class="detail-value">{{ selectedCaregiver.birthdate ? formatDate(selectedCaregiver.birthdate) : 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Age</div>
                <div class="detail-value">{{ selectedCaregiver.age != null ? selectedCaregiver.age : 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Address</div>
                <div class="detail-value">{{ selectedCaregiver.address || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">State</div>
                <div class="detail-value">{{ selectedCaregiver.state || 'New York' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">County/Borough</div>
                <div class="detail-value">{{ selectedCaregiver.county || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">City</div>
                <div class="detail-value">{{ selectedCaregiver.city || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">ZIP Code</div>
                <div class="detail-value">{{ selectedCaregiver.zip_code || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Location</div>
                <div class="detail-value">{{ selectedCaregiver.location || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Clients Served</div>
                <div class="detail-value">{{ selectedCaregiver.clients_served ?? 0 }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Joined</div>
                <div class="detail-value">{{ selectedCaregiver.joined || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Preferred Hourly Rate</div>
                <div class="detail-value">
                  <v-chip color="success" size="small">
                    <v-icon size="14" class="mr-1">mdi-cash</v-icon>
                    <template v-if="selectedCaregiver.preferred_hourly_rate_min != null && selectedCaregiver.preferred_hourly_rate_max != null">
                      ${{ Number(selectedCaregiver.preferred_hourly_rate_min).toFixed(0) }} - ${{ Number(selectedCaregiver.preferred_hourly_rate_max).toFixed(0) }}/hr
                    </template>
                    <template v-else>
                      ${{ Number(selectedCaregiver.hourly_rate || 20).toFixed(0) }}/hr
                    </template>
                  </v-chip>
                </div>
              </div>
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Professional Certifications (same section style as admin) -->
          <v-row>
            <v-col cols="12">
              <div class="detail-section">
                <div class="detail-label mb-3">
                  <v-icon class="mr-2">mdi-certificate</v-icon>
                  Professional Certifications
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <template v-if="(selectedCaregiver.certifications_list && selectedCaregiver.certifications_list.length) || selectedCaregiver.certifications">
                    <v-chip v-for="(cert, i) in (selectedCaregiver.certifications_list || (selectedCaregiver.certifications ? selectedCaregiver.certifications.split(',').map(c => c.trim()) : []))" :key="i" size="small" variant="tonal" color="primary">{{ cert }}</v-chip>
                  </template>
                  <span v-else class="text-body-2 text-grey">N/A</span>
                </div>
              </div>
            </v-col>
          </v-row>

          <v-divider class="my-4" />

          <!-- Ratings & Reviews (same as admin: detail-label + icon, cards or alert) -->
          <v-row>
            <v-col cols="12">
              <div class="detail-section">
                <div class="detail-label mb-3">
                  <v-icon class="mr-2">mdi-star</v-icon>
                  Ratings & Reviews
                </div>
                <div v-if="caregiverReviews.length > 0">
                  <v-card v-for="review in caregiverReviews.slice(0, 5)" :key="review.id" class="mb-3 pa-4" elevation="1">
                    <div class="d-flex justify-space-between align-start mb-2">
                      <div>
                        <div class="font-weight-bold">{{ review.client_name }}</div>
                        <div class="text-caption text-grey">{{ review.service_type }} - {{ review.created_at }}</div>
                      </div>
                      <v-chip :color="review.recommend ? 'success' : 'grey'" size="x-small">
                        <v-icon size="14" start>{{ review.recommend ? 'mdi-thumb-up' : 'mdi-thumb-down' }}</v-icon>
                        {{ review.recommend ? 'Recommended' : 'Not Recommended' }}
                      </v-chip>
                    </div>
                    <v-rating :model-value="review.rating" :length="5" :size="20" color="amber" active-color="amber" readonly density="compact" class="mb-2" />
                    <p v-if="review.comment" class="text-body-2 mb-1">{{ review.comment }}</p>
                    <div class="text-caption text-grey">{{ review.created_at }}</div>
                  </v-card>
                  <div v-if="caregiverReviews.length > 5" class="text-center">
                    <span class="text-caption text-grey">+{{ caregiverReviews.length - 5 }} more reviews</span>
                  </div>
                </div>
                <v-alert v-else type="info" variant="tonal" density="compact">
                  <v-icon>mdi-information</v-icon>
                  No reviews yet for this {{ selectedContractorType === 'housekeeper' ? 'housekeeper' : 'caregiver' }}
                </v-alert>
              </div>
            </v-col>
          </v-row>

        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="detailsDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['request-booking']);

const searchQuery = ref('');
const sortBy = ref('rating');
const availabilityFilter = ref('all');

const sortOptions = [
  { title: 'Highest rating', value: 'rating' },
  { title: 'Lowest hourly rate', value: 'rate' },
  { title: 'Name A–Z', value: 'name' },
];
const browseTab = ref('caregivers');
const detailsDialog = ref(false);
const selectedCaregiver = ref(null);
const selectedContractorType = ref('caregiver');
const loading = ref(true);
const caregiverReviews = ref([]);
const loadingReviews = ref(false);
const housekeepers = ref([]);


const caregivers = ref([]);

const fetchCaregivers = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/caregivers');
    caregivers.value = response.data.caregivers;
  } catch (error) {
  } finally {
    loading.value = false;
  }
};

const fetchHousekeepers = async () => {
  try {
    const response = await axios.get('/api/housekeepers');
    housekeepers.value = response.data.housekeepers || [];
  } catch (error) {
    housekeepers.value = [];
  }
};

onMounted(() => {
  fetchCaregivers();
  fetchHousekeepers();
});

const filteredCaregivers = computed(() => {
  let list = caregivers.value.filter(c => {
    const matchesSearch = !searchQuery.value ||
      (c.name || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (c.specialty || '').toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesAvailability = availabilityFilter.value === 'all' || c.availability === 'available';
    return matchesSearch && matchesAvailability;
  });
  const order = sortBy.value;
  if (order === 'rating') {
    list = [...list].sort((a, b) => parseFloat(b.rating || 0) - parseFloat(a.rating || 0));
  } else if (order === 'rate') {
    list = [...list].sort((a, b) => (Number(a.hourly_rate || a.preferred_hourly_rate_min || 999) - Number(b.hourly_rate || b.preferred_hourly_rate_min || 999)));
  } else if (order === 'name') {
    list = [...list].sort((a, b) => (a.name || '').localeCompare(b.name || ''));
  }
  return list;
});

const filteredHousekeepers = computed(() => {
  let list = housekeepers.value.filter(h => {
    const matchesSearch = !searchQuery.value ||
      (h.name || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (h.specialty || '').toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesAvailability = availabilityFilter.value === 'all' || h.availability === 'available';
    return matchesSearch && matchesAvailability;
  });
  const order = sortBy.value;
  if (order === 'rating') {
    list = [...list].sort((a, b) => parseFloat(b.rating || 0) - parseFloat(a.rating || 0));
  } else if (order === 'rate') {
    list = [...list].sort((a, b) => (Number(a.hourly_rate || 999) - Number(b.hourly_rate || 999)));
  } else if (order === 'name') {
    list = [...list].sort((a, b) => (a.name || '').localeCompare(b.name || ''));
  }
  return list;
});

const resetFilters = () => {
  searchQuery.value = '';
  availabilityFilter.value = 'all';
  sortBy.value = 'rating';
};

const viewDetails = async (person, type) => {
  selectedCaregiver.value = person;
  selectedContractorType.value = type || 'caregiver';
  detailsDialog.value = true;
  if (type === 'caregiver') {
    await loadCaregiverReviews(person.id);
  } else {
    caregiverReviews.value = [];
  }
};

const loadCaregiverReviews = async (caregiverId) => {
  if (!caregiverId) return;
  
  loadingReviews.value = true;
  caregiverReviews.value = [];
  
  try {
    const response = await axios.get(`/api/reviews/caregiver/${caregiverId}`);
    if (response.data.success) {
      caregiverReviews.value = response.data.reviews || [];
    }
  } catch (error) {
  } finally {
    loadingReviews.value = false;
  }
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

const formatDate = (d) => {
  if (!d) return 'N/A';
  const date = new Date(d);
  if (isNaN(date.getTime())) return 'N/A';
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
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

.type-indicator {
  position: relative;
}

.type-indicator::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  z-index: 1;
  border-radius: 16px 16px 0 0;
}

.type-indicator.type-caregiver::before {
  background: linear-gradient(90deg, #1976d2 0%, #42a5f5 100%);
}

.type-indicator.type-housekeeper::before {
  background: linear-gradient(90deg, #6a1b9a 0%, #ab47bc 100%);
}

.type-chip {
  position: absolute;
  top: 12px;
  left: 12px;
  font-weight: 600;
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

.contact-preview {
  word-break: break-word;
  line-height: 1.3;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px 24px;
}

.detail-row {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.detail-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.detail-value {
  font-size: 0.9375rem;
  color: #1a1a1a;
  font-weight: 500;
}

@media (max-width: 600px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }
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

/* Caregiver/Housekeeper Details modal – match admin View Caregiver Details */
.view-caregiver-modal-header {
  background: #1976d2 !important;
  color: white;
}

.view-caregiver-modal-header .section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: white !important;
}

.view-caregiver-modal-body {
  max-height: 60vh;
  overflow-y: auto;
}

.view-caregiver-modal-body .detail-section {
  margin-bottom: 16px;
}

.view-caregiver-modal-body .detail-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.view-caregiver-modal-body .detail-value {
  font-size: 1rem;
  color: #1f2937;
  font-weight: 500;
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
