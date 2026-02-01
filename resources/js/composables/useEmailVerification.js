import { ref, onMounted } from 'vue';

export function useEmailVerification() {
  // Default to true so dashboard is accessible if API fails
  const isVerified = ref(true);
  const userEmail = ref('');
  const loading = ref(true);

  const checkVerificationStatus = async () => {
    try {
      const response = await fetch('/api/auth/verification-status', {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      
      if (!response.ok) {
        // If API fails, default to verified to not block users
        console.warn('Verification status check failed, defaulting to verified');
        isVerified.value = true;
        loading.value = false;
        return;
      }
      
      const data = await response.json();
      
      // Handle both response formats: { verified: true } or { data: { verified: true } }
      if (data.data && typeof data.data.verified !== 'undefined') {
        isVerified.value = data.data.verified;
        userEmail.value = data.data.email || '';
      } else if (typeof data.verified !== 'undefined') {
        isVerified.value = data.verified;
        userEmail.value = data.email || '';
      } else {
        // Unknown response format, default to verified
        console.warn('Unknown verification response format, defaulting to verified');
        isVerified.value = true;
      }
    } catch (error) {
      console.error('Failed to check verification status:', error);
      // On error, default to verified so users aren't locked out
      isVerified.value = true;
    } finally {
      loading.value = false;
    }
  };

  onMounted(() => {
    checkVerificationStatus();
  });

  return {
    isVerified,
    userEmail,
    loading,
    checkVerificationStatus
  };
}
