<template>
  <div class="dark-mode-toggle">
    <!-- Simple Toggle Button -->
    <v-btn
      v-if="variant === 'icon'"
      icon
      :size="size"
      variant="text"
      :color="isDarkMode ? 'warning' : 'grey'"
      @click="toggleDarkMode"
      :aria-label="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
    >
      <v-icon>{{ isDarkMode ? 'mdi-weather-sunny' : 'mdi-moon-waning-crescent' }}</v-icon>
      <v-tooltip activator="parent" location="bottom">
        {{ isDarkMode ? 'Light mode' : 'Dark mode' }}
      </v-tooltip>
    </v-btn>

    <!-- Switch with Label -->
    <div v-else-if="variant === 'switch'" class="d-flex align-center">
      <v-icon size="20" class="mr-2" :color="isDarkMode ? 'grey' : 'warning'">
        mdi-weather-sunny
      </v-icon>
      <v-switch
        :model-value="isDarkMode"
        hide-details
        density="compact"
        color="primary"
        @update:model-value="toggleDarkMode"
        :aria-label="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
      />
      <v-icon size="20" class="ml-1" :color="isDarkMode ? 'primary' : 'grey'">
        mdi-moon-waning-crescent
      </v-icon>
    </div>

    <!-- Menu with Options -->
    <v-menu v-else-if="variant === 'menu'" :close-on-content-click="true">
      <template #activator="{ props }">
        <v-btn
          v-bind="props"
          icon
          :size="size"
          variant="text"
        >
          <v-icon>{{ themeIcon }}</v-icon>
          <v-tooltip activator="parent" location="bottom">
            Theme: {{ themeLabel }}
          </v-tooltip>
        </v-btn>
      </template>
      <v-list density="compact" class="theme-menu">
        <v-list-subheader>Theme</v-list-subheader>
        <v-list-item
          v-for="option in themeOptions"
          :key="option.value"
          :active="themePreference === option.value"
          @click="setThemePreference(option.value)"
        >
          <template #prepend>
            <v-icon :icon="option.icon" size="20" />
          </template>
          <v-list-item-title>{{ option.label }}</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>

    <!-- Segmented Button Group -->
    <v-btn-toggle
      v-else-if="variant === 'segmented'"
      :model-value="themePreference"
      mandatory
      density="compact"
      rounded="pill"
      color="primary"
      @update:model-value="setThemePreference"
    >
      <v-btn
        v-for="option in themeOptions"
        :key="option.value"
        :value="option.value"
        size="small"
      >
        <v-icon :icon="option.icon" size="18" class="mr-1" />
        {{ option.label }}
      </v-btn>
    </v-btn-toggle>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useDarkMode } from '@/composables/useDarkMode';

// Props
const props = defineProps({
  variant: {
    type: String,
    default: 'icon', // 'icon' | 'switch' | 'menu' | 'segmented'
    validator: (v) => ['icon', 'switch', 'menu', 'segmented'].includes(v)
  },
  size: {
    type: String,
    default: 'default'
  }
});

// Use dark mode composable
const { isDarkMode, themePreference, setThemePreference, toggleDarkMode } = useDarkMode();

// Theme options
const themeOptions = [
  { value: 'light', label: 'Light', icon: 'mdi-weather-sunny' },
  { value: 'dark', label: 'Dark', icon: 'mdi-moon-waning-crescent' },
  { value: 'system', label: 'System', icon: 'mdi-laptop' },
];

// Computed
const themeIcon = computed(() => {
  const icons = {
    light: 'mdi-weather-sunny',
    dark: 'mdi-moon-waning-crescent',
    system: 'mdi-laptop',
  };
  return icons[themePreference.value] || 'mdi-theme-light-dark';
});

const themeLabel = computed(() => {
  const labels = {
    light: 'Light',
    dark: 'Dark',
    system: 'System',
  };
  return labels[themePreference.value] || 'Unknown';
});
</script>

<style scoped>
.dark-mode-toggle {
  display: inline-flex;
  align-items: center;
}

.theme-menu {
  min-width: 160px;
}

.v-btn-toggle {
  border-radius: 24px;
}

.v-btn-toggle .v-btn {
  text-transform: none;
  font-weight: 500;
}
</style>
