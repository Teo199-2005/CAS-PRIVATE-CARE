<template>
  <div class="location-selector">
    <v-row>
      <v-col :cols="countyCol" :md="countyMd">
        <v-select
          v-model="selectedCounty"
          :items="counties"
          :label="countyLabel"
          :variant="variant"
          :density="density"
          :hide-details="hideDetails"
          :disabled="disabled"
          :loading="isLoading"
          @update:model-value="onCountyChange"
        >
          <template #prepend-inner v-if="showIcons">
            <v-icon>mdi-map-marker</v-icon>
          </template>
        </v-select>
      </v-col>
      <v-col :cols="cityCol" :md="cityMd">
        <v-select
          v-model="selectedCity"
          :items="availableCities"
          :label="cityLabel"
          :variant="variant"
          :density="density"
          :hide-details="hideDetails"
          :disabled="disabled || !selectedCounty"
          :loading="isLoading"
          @update:model-value="onCityChange"
        >
          <template #prepend-inner v-if="showIcons">
            <v-icon>mdi-city</v-icon>
          </template>
        </v-select>
        <div v-if="!selectedCounty && showHelperText" class="text-caption text-grey mt-1">
          Please select a county first
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useLocationData } from '../composables/useLocationData.js';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ county: '', city: '' })
  },
  countyLabel: {
    type: String,
    default: 'County'
  },
  cityLabel: {
    type: String,
    default: 'City'
  },
  variant: {
    type: String,
    default: 'outlined'
  },
  density: {
    type: String,
    default: 'default'
  },
  hideDetails: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  showIcons: {
    type: Boolean,
    default: true
  },
  showHelperText: {
    type: Boolean,
    default: true
  },
  countyCol: {
    type: [String, Number],
    default: 12
  },
  cityCol: {
    type: [String, Number],
    default: 12
  },
  countyMd: {
    type: [String, Number],
    default: 6
  },
  cityMd: {
    type: [String, Number],
    default: 6
  }
});

const emit = defineEmits(['update:modelValue', 'county-change', 'city-change']);

const { counties, getCitiesForCounty, isLoading, init } = useLocationData();

const selectedCounty = ref(props.modelValue.county || '');
const selectedCity = ref(props.modelValue.city || '');

const availableCities = computed(() => {
  if (!selectedCounty.value) return [];
  return getCitiesForCounty(selectedCounty.value);
});

const onCountyChange = (county) => {
  selectedCounty.value = county;
  selectedCity.value = ''; // Reset city when county changes
  
  const newValue = { county, city: '' };
  emit('update:modelValue', newValue);
  emit('county-change', county);
};

const onCityChange = (city) => {
  selectedCity.value = city;
  
  const newValue = { county: selectedCounty.value, city };
  emit('update:modelValue', newValue);
  emit('city-change', city);
};

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  if (newValue.county !== selectedCounty.value) {
    selectedCounty.value = newValue.county || '';
  }
  if (newValue.city !== selectedCity.value) {
    selectedCity.value = newValue.city || '';
  }
}, { deep: true });

onMounted(async () => {
  await init();
});
</script>

<style scoped>
.location-selector {
  width: 100%;
}
</style>