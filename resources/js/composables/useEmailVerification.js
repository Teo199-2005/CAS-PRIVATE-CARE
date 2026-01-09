import { ref, onMounted } from 'vue';

export function useEmailVerification() {
  const isVerified = ref(true);
  const userEmail = ref('');
  const loading = ref(true);

  const checkVerificationStatus = async () => {
    try {
      const response = await fetch('/api/auth/verification-status', {
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
      });
      
      const data = await response.json();
      isVerified.value = data.verified;
      userEmail.value = data.email || '';
    } catch (error) {
      console.error('Failed to check verification status:', error);
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
