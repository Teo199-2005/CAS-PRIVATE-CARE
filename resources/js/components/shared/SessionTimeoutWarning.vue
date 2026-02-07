<template>
  <!-- Session Timeout Warning Modal -->
  <v-dialog 
    v-model="showWarning" 
    max-width="450" 
    persistent
    :scrim="true"
    scrim-color="rgba(0,0,0,0.7)"
  >
    <v-card class="session-warning-card" elevation="24">
      <v-card-title class="session-warning-header pa-6">
        <div class="d-flex align-center">
          <v-icon color="warning" size="32" class="mr-3">mdi-clock-alert</v-icon>
          <span class="text-h5 font-weight-bold">Session Expiring Soon</span>
        </div>
      </v-card-title>
      
      <v-card-text class="pa-6 text-center">
        <div class="countdown-circle mb-4">
          <div class="countdown-number">{{ formattedTime }}</div>
          <div class="countdown-label">remaining</div>
        </div>
        
        <p class="text-body-1 mb-4">
          Your session will expire in <strong>{{ formattedTime }}</strong> due to inactivity.
        </p>
        
        <v-alert type="warning" variant="tonal" density="compact" class="mb-4">
          <template v-slot:prepend>
            <v-icon>mdi-information</v-icon>
          </template>
          Any unsaved changes will be lost when your session expires.
        </v-alert>
      </v-card-text>
      
      <v-card-actions class="pa-6 pt-0 d-flex justify-center gap-3">
        <v-btn 
          color="grey" 
          variant="outlined" 
          size="large"
          @click="logout"
        >
          <v-icon start>mdi-logout</v-icon>
          Log Out Now
        </v-btn>
        <v-btn 
          color="primary" 
          variant="flat" 
          size="large"
          @click="extendSession"
          :loading="extending"
        >
          <v-icon start>mdi-refresh</v-icon>
          Stay Logged In
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
/**
 * Session Timeout Warning Component
 * 
 * Shows a warning modal when user's session is about to expire.
 * Provides options to extend session or log out.
 * 
 * WCAG 2.1 Compliant:
 * - 2.2.1: Timing Adjustable - Users can extend session
 * - 2.2.6: Timeouts - Users are warned before session expires
 */
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  // Session timeout in minutes (default 120 = 2 hours)
  sessionTimeout: {
    type: Number,
    default: 120
  },
  // Warning time before session expires (in minutes)
  warningTime: {
    type: Number,
    default: 5
  },
  // Enable/disable the component
  enabled: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['session-extended', 'session-expired', 'logout']);

const showWarning = ref(false);
const extending = ref(false);
const secondsRemaining = ref(props.warningTime * 60);
const lastActivity = ref(Date.now());

let warningTimer = null;
let countdownTimer = null;
let activityTimer = null;

const formattedTime = computed(() => {
  const minutes = Math.floor(secondsRemaining.value / 60);
  const seconds = secondsRemaining.value % 60;
  if (minutes > 0) {
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
  }
  return `${seconds}s`;
});

/**
 * Start tracking user activity
 */
const startActivityTracking = () => {
  const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
  
  events.forEach(event => {
    document.addEventListener(event, recordActivity, { passive: true });
  });
};

/**
 * Stop tracking user activity
 */
const stopActivityTracking = () => {
  const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
  
  events.forEach(event => {
    document.removeEventListener(event, recordActivity);
  });
};

/**
 * Record user activity
 */
const recordActivity = () => {
  lastActivity.value = Date.now();
  
  // If warning is not showing, reset the warning timer
  if (!showWarning.value) {
    resetWarningTimer();
  }
};

/**
 * Reset the warning timer based on last activity
 */
const resetWarningTimer = () => {
  if (warningTimer) {
    clearTimeout(warningTimer);
  }
  
  // Calculate when to show warning (session timeout - warning time)
  const warningDelay = (props.sessionTimeout - props.warningTime) * 60 * 1000;
  
  warningTimer = setTimeout(() => {
    showSessionWarning();
  }, warningDelay);
};

/**
 * Show the session warning modal
 */
const showSessionWarning = () => {
  showWarning.value = true;
  secondsRemaining.value = props.warningTime * 60;
  
  // Start countdown
  countdownTimer = setInterval(() => {
    secondsRemaining.value--;
    
    if (secondsRemaining.value <= 0) {
      // Session expired
      clearInterval(countdownTimer);
      handleSessionExpired();
    }
  }, 1000);
  
  // Announce to screen readers
  announceToScreenReader('Your session will expire in ' + props.warningTime + ' minutes. Click Stay Logged In to continue.');
};

/**
 * Extend the session
 */
const extendSession = async () => {
  extending.value = true;
  
  try {
    // Call heartbeat endpoint to extend session
    await axios.get('/api/session/heartbeat');
    
    // Reset timers
    showWarning.value = false;
    if (countdownTimer) {
      clearInterval(countdownTimer);
    }
    resetWarningTimer();
    
    emit('session-extended');
    announceToScreenReader('Session extended successfully. You can continue working.');
  } catch (error) {
    console.error('Failed to extend session:', error);
    // If extension fails, redirect to login
    handleSessionExpired();
  } finally {
    extending.value = false;
  }
};

/**
 * Handle session expiration
 */
const handleSessionExpired = () => {
  showWarning.value = false;
  emit('session-expired');
  
  // Redirect to login with message and refresh so login page loads clean
  window.location.href = '/login?expired=1&refresh=' + Date.now();
};

/**
 * Log out immediately
 */
const logout = async () => {
  showWarning.value = false;
  emit('logout');
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    await fetch('/logout', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({})
    });
  } catch (_) {}
  window.location.href = '/login?refresh=' + Date.now();
};

/**
 * Announce message to screen readers
 */
const announceToScreenReader = (message) => {
  const announcement = document.createElement('div');
  announcement.setAttribute('role', 'alert');
  announcement.setAttribute('aria-live', 'assertive');
  announcement.className = 'sr-only';
  announcement.textContent = message;
  document.body.appendChild(announcement);
  
  setTimeout(() => {
    document.body.removeChild(announcement);
  }, 1000);
};

onMounted(() => {
  if (props.enabled) {
    startActivityTracking();
    resetWarningTimer();
  }
});

onUnmounted(() => {
  stopActivityTracking();
  
  if (warningTimer) {
    clearTimeout(warningTimer);
  }
  if (countdownTimer) {
    clearInterval(countdownTimer);
  }
});

watch(() => props.enabled, (newVal) => {
  if (newVal) {
    startActivityTracking();
    resetWarningTimer();
  } else {
    stopActivityTracking();
    if (warningTimer) {
      clearTimeout(warningTimer);
    }
  }
});
</script>

<style scoped>
.session-warning-card {
  border-radius: 16px !important;
  overflow: hidden;
}

.session-warning-header {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.countdown-circle {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border: 4px solid #f59e0b;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}

.countdown-number {
  font-size: 2rem;
  font-weight: 700;
  color: #d97706;
  line-height: 1;
}

.countdown-label {
  font-size: 0.75rem;
  color: #92400e;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/* Responsive adjustments */
@media (max-width: 480px) {
  .session-warning-header {
    padding: 1rem !important;
  }
  
  .session-warning-header .text-h5 {
    font-size: 1.1rem !important;
  }
  
  .countdown-circle {
    width: 100px;
    height: 100px;
  }
  
  .countdown-number {
    font-size: 1.5rem;
  }
}
</style>
