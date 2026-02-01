<template>
  <div>
    <!-- Email Verification Modal (blocks access if not verified) -->
    <!-- Temporarily disabled to debug - modal will only show when explicitly needed -->
    <email-verification-modal
      v-if="showVerificationModal"
      :user-email="userEmail"
      :is-verified="isVerified"
      @verified="handleVerified"
    />
    
    <!-- Dashboard Content - no longer blocked by verification -->
    <div>
      <slot></slot>
    </div>
  </div>
</template>

<script>
import EmailVerificationModal from './EmailVerificationModal.vue';
import { useEmailVerification } from '../composables/useEmailVerification';
import { computed } from 'vue';

export default {
  name: 'DashboardWrapper',
  components: {
    EmailVerificationModal
  },
  props: {
    isAdmin: {
      type: Boolean,
      default: false
    },
    requireVerification: {
      type: Boolean,
      default: false  // Disabled by default now
    }
  },
  setup(props) {
    const { isVerified, userEmail, loading, checkVerificationStatus } = useEmailVerification();
    
    // Only show modal if verification is explicitly required AND user is not verified
    const showVerificationModal = computed(() => {
      return props.requireVerification && !props.isAdmin && !loading.value && !isVerified.value;
    });
    
    const handleVerified = () => {
      checkVerificationStatus();
    };
    
    return {
      isVerified,
      userEmail,
      loading,
      showVerificationModal,
      handleVerified
    };
  }
};
</script>

<style scoped>
.dashboard-blocked {
  filter: blur(5px);
  pointer-events: none;
  user-select: none;
}
</style>
