<template>
  <v-dialog v-model="dialog" max-width="600px">
    <template v-slot:activator="{ props }">
      <v-btn
        v-bind="props"
        color="warning"
        size="small"
        variant="outlined"
        prepend-icon="mdi-star-remove"
      >
        Remove Ratings
      </v-btn>
    </template>

    <v-card>
      <v-card-title class="text-h5 pa-6 bg-warning text-white">
        <v-icon class="mr-2">mdi-star-remove</v-icon>
        Remove Caregiver Ratings
      </v-card-title>

      <v-card-text class="pa-6">
        <v-alert
          type="warning"
          variant="tonal"
          class="mb-4"
        >
          <v-icon>mdi-alert-circle</v-icon>
          This action will permanently remove all ratings for the selected caregiver(s). This cannot be undone.
        </v-alert>

        <v-select
          v-model="selectedCaregivers"
          :items="caregivers"
          item-title="name"
          item-value="id"
          label="Select Caregivers"
          multiple
          chips
          closable-chips
          variant="outlined"
          class="mb-4"
        >
          <template v-slot:chip="{ props, item }">
            <v-chip
              v-bind="props"
              :text="item.raw.name"
              color="primary"
              size="small"
            />
          </template>
        </v-select>

        <v-textarea
          v-model="reason"
          label="Reason for removal (optional)"
          variant="outlined"
          rows="3"
          placeholder="Enter reason for removing ratings..."
        />
      </v-card-text>

      <v-card-actions class="pa-6 pt-0">
        <v-spacer />
        <v-btn
          color="grey"
          variant="outlined"
          @click="closeDialog"
        >
          Cancel
        </v-btn>
        <v-btn
          color="warning"
          :loading="loading"
          :disabled="!selectedCaregivers.length"
          @click="removeRatings"
        >
          Remove Ratings
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue'

const props = defineProps({
  caregivers: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['ratings-removed'])

const dialog = ref(false)
const selectedCaregivers = ref([])
const reason = ref('')
const loading = ref(false)

const removeRatings = async () => {
  loading.value = true
  
  try {
    const response = await fetch('/api/admin/caregivers/remove-ratings', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        caregiver_ids: selectedCaregivers.value,
        reason: reason.value
      })
    })

    const data = await response.json()

    if (response.ok) {
      emit('ratings-removed', {
        caregivers: selectedCaregivers.value,
        message: data.message
      })
      closeDialog()
    } else {
      throw new Error(data.message || 'Failed to remove ratings')
    }
  } catch (error) {
    alert('Error: ' + error.message)
  } finally {
    loading.value = false
  }
}

const closeDialog = () => {
  dialog.value = false
  selectedCaregivers.value = []
  reason.value = ''
}
</script>