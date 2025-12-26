import { ref, computed } from 'vue';

// Location data composable for NY counties and cities
export function useLocationData() {
  const locationData = ref({});
  const isLoading = ref(false);
  const error = ref(null);

  // Load location data from JSON file
  const loadLocationData = async () => {
    if (Object.keys(locationData.value).length > 0) {
      return locationData.value; // Already loaded
    }

    isLoading.value = true;
    error.value = null;

    try {
      const response = await fetch('/api/location-data');
      if (!response.ok) {
        throw new Error('Failed to load location data');
      }
      const data = await response.json();
      locationData.value = data;
      return data;
    } catch (err) {
      error.value = err.message;
      console.error('Error loading location data:', err);
      // Fallback to basic data
      locationData.value = getFallbackData();
      return locationData.value;
    } finally {
      isLoading.value = false;
    }
  };

  // Get counties list
  const counties = computed(() => {
    return Object.keys(locationData.value).sort();
  });

  // Get cities for a specific county
  const getCitiesForCounty = (county) => {
    return locationData.value[county] || [];
  };

  // Fallback data in case JSON fails to load
  const getFallbackData = () => {
    return {
      "Albany": ["Albany", "Cohoes", "Watervliet", "Colonie"],
      "Bronx": ["Bronx", "Fordham", "Riverdale"],
      "Kings": ["Brooklyn", "Park Slope", "Williamsburg"],
      "New York": ["Manhattan", "Upper East Side", "SoHo"],
      "Queens": ["Queens", "Flushing", "Jamaica", "Astoria"],
      "Richmond": ["Staten Island", "St. George", "Tottenville"],
      "Nassau": ["Hempstead", "Long Beach", "Glen Cove"],
      "Suffolk": ["Huntington", "Brookhaven", "Islip"],
      "Westchester": ["White Plains", "Yonkers", "New Rochelle"],
      "Erie": ["Buffalo", "Cheektowaga", "West Seneca"]
    };
  };

  // Initialize data loading
  const init = async () => {
    await loadLocationData();
  };

  return {
    locationData: computed(() => locationData.value),
    counties,
    getCitiesForCounty,
    isLoading: computed(() => isLoading.value),
    error: computed(() => error.value),
    init
  };
}