<template>
  <div>
    <!-- Email Verification Modal (blocks access if not verified) -->
    <email-verification-modal
      v-if="!isAdmin && !loading"
      :user-email="userEmail"
      :is-verified="isVerified"
      @verified="handleVerified"
    />
    
    <!-- Actual Dashboard Content (blurred if not verified) -->
    <div :class="{ 'dashboard-blocked': !isAdmin && !isVerified && !loading }">
      <slot></slot>
    </div>
  </div>
</template>

<script>
import EmailVerificationModal from './EmailVerificationModal.vue';
import { useEmailVerification } from '../composables/useEmailVerification';

export default {
  name: 'DashboardWrapper',
  components: {
    EmailVerificationModal
  },
  props: {
    isAdmin: {
      type: Boolean,
      default: false
    }
  },
  setup() {
    const { isVerified, userEmail, loading, checkVerificationStatus } = useEmailVerification();
    
    const handleVerified = () => {
      checkVerificationStatus();
    };
    
    return {
      isVerified,
      userEmail,
      loading,
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
