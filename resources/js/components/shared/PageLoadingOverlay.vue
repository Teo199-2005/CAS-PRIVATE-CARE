<template>
  <Transition name="fade">
    <div 
      v-if="show" 
      class="page-loading-overlay"
      :class="{ 'transparent': transparent }"
      role="progressbar"
      aria-label="Loading content"
    >
      <div class="loading-content">
        <!-- Logo/Branding (optional) -->
        <div v-if="showLogo" class="loading-logo mb-4">
          <img 
            :src="logoSrc || '/logo flower.png'" 
            alt="CAS Private Care" 
            class="logo-image"
          />
        </div>

        <!-- Spinner -->
        <div class="spinner-container">
          <v-progress-circular
            :size="size"
            :width="width"
            :color="color"
            indeterminate
          />
        </div>

        <!-- Loading Message -->
        <div v-if="message" class="loading-message mt-4">
          <span class="message-text">{{ message }}</span>
          <span class="loading-dots">
            <span class="dot">.</span>
            <span class="dot">.</span>
            <span class="dot">.</span>
          </span>
        </div>

        <!-- Progress Bar (for determinate loading) -->
        <div v-if="showProgress && progress !== null" class="progress-section mt-4">
          <v-progress-linear
            :model-value="progress"
            :color="color"
            height="6"
            rounded
            class="progress-bar"
          />
          <span class="progress-text">{{ Math.round(progress) }}%</span>
        </div>

        <!-- Cancel Button (optional) -->
        <v-btn
          v-if="cancellable"
          variant="text"
          :color="color"
          class="mt-6"
          @click="$emit('cancel')"
        >
          Cancel
        </v-btn>
      </div>
    </div>
  </Transition>
</template>

<script setup>
defineProps({
  /**
   * Whether to show the overlay
   */
  show: {
    type: Boolean,
    default: false
  },
  /**
   * Loading message to display
   */
  message: {
    type: String,
    default: 'Loading'
  },
  /**
   * Spinner/progress color
   */
  color: {
    type: String,
    default: 'primary'
  },
  /**
   * Spinner size
   */
  size: {
    type: [Number, String],
    default: 64
  },
  /**
   * Spinner stroke width
   */
  width: {
    type: Number,
    default: 4
  },
  /**
   * Whether to show the logo
   */
  showLogo: {
    type: Boolean,
    default: false
  },
  /**
   * Custom logo source
   */
  logoSrc: {
    type: String,
    default: ''
  },
  /**
   * Use transparent background
   */
  transparent: {
    type: Boolean,
    default: false
  },
  /**
   * Show determinate progress bar
   */
  showProgress: {
    type: Boolean,
    default: false
  },
  /**
   * Progress value (0-100)
   */
  progress: {
    type: Number,
    default: null
  },
  /**
   * Allow cancellation
   */
  cancellable: {
    type: Boolean,
    default: false
  }
});

defineEmits(['cancel']);
</script>

<style scoped>
.page-loading-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #ffffff;
  background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
}

.page-loading-overlay.transparent {
  background-color: rgba(255, 255, 255, 0.95);
  background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
}

.v-theme--dark .page-loading-overlay {
  background-color: #121212;
  background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
}

.v-theme--dark .page-loading-overlay.transparent {
  background-color: rgba(18, 18, 18, 0.95);
  background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
}

.loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 2rem;
}

.loading-logo {
  animation: logo-float 2.5s ease-in-out infinite;
}

@keyframes logo-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

.logo-image {
  width: 100px;
  height: 100px;
  object-fit: contain;
  filter: drop-shadow(0 10px 30px rgba(11, 79, 162, 0.15));
}

.spinner-container {
  display: flex;
  align-items: center;
  justify-content: center;
}

.loading-message {
  display: flex;
  align-items: baseline;
  font-size: 1.1rem;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.8);
}

.message-text {
  margin-right: 2px;
}

.loading-dots {
  display: inline-flex;
}

.dot {
  animation: dotPulse 1.4s infinite ease-in-out both;
  margin-left: 1px;
}

.dot:nth-child(1) {
  animation-delay: 0s;
}

.dot:nth-child(2) {
  animation-delay: 0.2s;
}

.dot:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes dotPulse {
  0%, 80%, 100% {
    opacity: 0.3;
  }
  40% {
    opacity: 1;
  }
}

.progress-section {
  width: 200px;
  text-align: center;
}

.progress-bar {
  border-radius: 3px;
}

.progress-text {
  display: block;
  margin-top: 8px;
  font-size: 0.875rem;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Responsive */
@media (max-width: 600px) {
  .loading-content {
    padding: 1.5rem;
  }
  
  .logo-image {
    width: 80px;
    height: 80px;
  }
  
  .loading-message {
    font-size: 1rem;
  }
}
</style>
