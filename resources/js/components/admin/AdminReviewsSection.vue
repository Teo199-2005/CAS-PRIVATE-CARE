<template>
  <div class="admin-reviews-section">
    <v-card elevation="0">
      <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
        <div>
          <span class="section-title error--text">Reviews & Ratings</span>
          <div class="text-caption text-grey mt-1">Manage client feedback and caregiver ratings</div>
        </div>
        <v-chip color="info" variant="flat" size="large">
          <v-icon start>mdi-star</v-icon>
          {{ reviews.length }} Total Reviews
        </v-chip>
      </v-card-title>
      
      <v-card-text class="pa-0">
        <!-- Desktop Table View -->
        <v-data-table
          v-if="!isMobile"
          :headers="headers"
          :items="reviews"
          :items-per-page="10"
          :loading="loading"
          class="admin-table"
          item-value="id"
        >
          <template v-slot:item.rating="{ item }">
            <div class="d-flex align-center">
              <v-rating
                :model-value="item.rating"
                readonly
                density="compact"
                size="small"
                color="amber"
                active-color="amber"
              ></v-rating>
              <span class="ml-2 font-weight-bold">{{ item.rating }}.0</span>
            </div>
          </template>
          
          <template v-slot:item.recommend="{ item }">
            <v-chip
              :color="item.recommend ? 'success' : 'error'"
              size="small"
              variant="flat"
            >
              <v-icon start>{{ item.recommend ? 'mdi-thumb-up' : 'mdi-thumb-down' }}</v-icon>
              {{ item.recommend ? 'Yes' : 'No' }}
            </v-chip>
          </template>
          
          <template v-slot:item.comment="{ item }">
            <div class="text-truncate" style="max-width: 200px;">
              {{ item.comment || 'No comment' }}
            </div>
          </template>
          
          <template v-slot:item.created_at="{ item }">
            <span class="text-grey">{{ item.created_at }}</span>
          </template>
          
          <template v-slot:item.actions="{ item }">
            <div class="d-flex gap-2">
              <v-btn 
                color="info" 
                variant="outlined" 
                size="small" 
                icon="mdi-eye" 
                @click="$emit('view', item)"
              ></v-btn>
              <v-btn 
                color="error" 
                variant="outlined" 
                size="small" 
                icon="mdi-delete" 
                @click="$emit('delete', item.id)"
              ></v-btn>
            </div>
          </template>
        </v-data-table>

        <!-- Mobile Card View -->
        <div v-else class="mobile-cards-container pa-3">
          <div v-if="loading" class="text-center py-8">
            <v-progress-circular indeterminate color="primary"></v-progress-circular>
          </div>
          <div v-else-if="reviews.length === 0" class="text-center py-8 text-grey">
            No reviews found
          </div>
          <v-card 
            v-else 
            v-for="item in reviews" 
            :key="item.id" 
            class="mobile-data-card mb-3" 
            elevation="2" 
            rounded="lg"
          >
            <v-card-text class="pa-0">
              <div 
                class="mobile-card-header d-flex align-center justify-space-between pa-3" 
                style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);"
              >
                <span class="text-white font-weight-bold text-body-1">{{ item.caregiver_name || item.caregiver }}</span>
                <v-chip
                  :color="item.recommend ? 'success' : 'error'"
                  size="small"
                  variant="flat"
                >
                  <v-icon start size="small">{{ item.recommend ? 'mdi-thumb-up' : 'mdi-thumb-down' }}</v-icon>
                  {{ item.recommend ? 'Yes' : 'No' }}
                </v-chip>
              </div>
              <div class="mobile-card-body pa-3">
                <div class="mobile-card-row d-flex justify-space-between align-center py-2" style="border-bottom: 1px solid #f3f4f6;">
                  <span class="mobile-card-label text-grey-darken-1">Rating:</span>
                  <div class="d-flex align-center">
                    <v-rating
                      :model-value="item.rating"
                      readonly
                      density="compact"
                      size="x-small"
                      color="amber"
                      active-color="amber"
                    ></v-rating>
                    <span class="ml-1 font-weight-bold text-caption">{{ item.rating }}.0</span>
                  </div>
                </div>
                <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                  <span class="mobile-card-label text-grey-darken-1">Client:</span>
                  <span class="mobile-card-value">{{ item.client_name || item.client }}</span>
                </div>
                <div class="mobile-card-row py-2" style="border-bottom: 1px solid #f3f4f6;">
                  <span class="mobile-card-label text-grey-darken-1 d-block mb-1">Comment:</span>
                  <span class="mobile-card-value text-caption">{{ item.comment || 'No comment' }}</span>
                </div>
                <div class="mobile-card-row d-flex justify-space-between py-2">
                  <span class="mobile-card-label text-grey-darken-1">Date:</span>
                  <span class="mobile-card-value text-grey">{{ item.created_at }}</span>
                </div>
              </div>
              <div 
                class="mobile-card-actions d-flex justify-center ga-2 pa-3" 
                style="background: #f9fafb; border-top: 1px solid #e5e7eb;"
              >
                <v-btn 
                  color="info" 
                  variant="tonal" 
                  size="small" 
                  prepend-icon="mdi-eye" 
                  @click="$emit('view', item)"
                >
                  View
                </v-btn>
                <v-btn 
                  color="error" 
                  variant="tonal" 
                  size="small" 
                  prepend-icon="mdi-delete" 
                  @click="$emit('delete', item.id)"
                >
                  Delete
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </div>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup>
// Props
defineProps({
  reviews: {
    type: Array,
    required: true,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  isMobile: {
    type: Boolean,
    default: false
  }
});

// Emits
defineEmits(['view', 'delete']);

// Table headers
const headers = [
  { title: 'Caregiver', key: 'caregiver_name' },
  { title: 'Client', key: 'client_name' },
  { title: 'Rating', key: 'rating' },
  { title: 'Recommend', key: 'recommend' },
  { title: 'Comment', key: 'comment' },
  { title: 'Date', key: 'created_at' },
  { title: 'Actions', key: 'actions', sortable: false }
];
</script>

<style scoped>
.admin-reviews-section {
  width: 100%;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
}

.mobile-card-label {
  font-size: 0.75rem;
  font-weight: 500;
}

.mobile-card-value {
  font-size: 0.875rem;
}

.mobile-data-card {
  overflow: hidden;
}

.admin-table :deep(.v-data-table-header th) {
  background-color: #f8fafc;
  font-weight: 600;
  color: #475569;
}

@media (max-width: 600px) {
  .section-title {
    font-size: 1rem;
  }
}
</style>
