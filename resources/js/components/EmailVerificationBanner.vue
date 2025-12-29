<template>
  <v-alert
    v-if="shouldShow"
    type="warning"
    variant="tonal"
    prominent
    border="start"
    class="mb-4"
    closable
    @click:close="dismissBanner"
  >
    <template v-slot:prepend>
      <v-icon size="24">mdi-email-alert</v-icon>
    </template>
    <div class="d-flex align-center justify-space-between flex-wrap">
      <div class="flex-grow-1 mr-4">
        <div class="text-h6 mb-1" style="color: black;">Verify Your Email Address</div>
        <div class="text-body-2" style="color: black;">
          Please verify your email address to access all features. A verification link has been sent to <strong>{{ userEmail }}</strong>
        </div>
      </div>
      <div class="d-flex align-center gap-2 mt-2 mt-sm-0">
        <v-btn
          color="warning"
          variant="flat"
          size="small"
          :loading="sending"
          @click="sendVerificationEmail"
        >
          <v-icon start>mdi-email-send</v-icon>
          Resend Verification Email
        </v-btn>
      </div>
    </div>
  </v-alert>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useNotification } from '../composables/useNotification.js';

const props = defineProps({
  userData: {
    type: Object,
    default: () => ({})
  }
});

const { notification, success } = useNotification();
const sending = ref(false);
const dismissed = ref(false);
const userData = ref(props.userData || {});

const isEmailVerified = computed(() => {
  return userData.value?.email_verified_at !== null && userData.value?.email_verified_at !== undefined;
});

const userEmail = computed(() => {
  return userData.value?.email || 'your email';
});

// Load user data if not provided via props
const loadUserData = async () => {
  if (props.userData && Object.keys(props.userData).length > 0) {
    userData.value = props.userData;
    return;
  }
  
  try {
    const response = await fetch('/api/profile');
    const data = await response.json();
    if (data.user) {
      userData.value = data.user;
    }
  } catch (error) {
    console.error('Failed to load user data:', error);
  }
};

const sendVerificationEmail = async () => {
  sending.value = true;
  try {
    const response = await fetch('/email/verification-notification', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json'
      }
    });

    const data = await response.json();

    if (data.success) {
      success('Verification email sent! Please check your inbox.', 'Email Sent');
    } else {
      notification(data.message || 'Failed to send verification email', 'error');
    }
  } catch (error) {
    console.error('Error sending verification email:', error);
    notification('Failed to send verification email. Please try again later.', 'error');
  } finally {
    sending.value = false;
  }
};

const dismissBanner = () => {
  dismissed.value = true;
  // Store dismissal in localStorage for this session
  localStorage.setItem(`email_verification_dismissed_${userData.value?.id}`, 'true');
};

// Check if banner was dismissed
onMounted(async () => {
  await loadUserData();
  const dismissedKey = `email_verification_dismissed_${userData.value?.id}`;
  if (localStorage.getItem(dismissedKey) === 'true') {
    dismissed.value = true;
  }
});

// Only show if not verified and not dismissed
const shouldShow = computed(() => {
  return !isEmailVerified.value && !dismissed.value;
});
</script>

<style scoped>
.gap-2 {
  gap: 8px;
}
</style>

