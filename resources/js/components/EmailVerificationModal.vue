<template>
  <v-dialog v-model="showModal" persistent max-width="500" :scrim="true">
    <v-card>
      <v-card-title class="text-h5 pa-6 bg-primary text-white">
        <v-icon start color="white">mdi-email-check</v-icon>
        Verify Your Email
      </v-card-title>
      
      <v-card-text class="pa-6">
        <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
          {{ error }}
        </v-alert>
        
        <v-alert v-if="success" type="success" variant="tonal" class="mb-4">
          {{ success }}
        </v-alert>
        
        <div v-if="!otpSent">
          <p class="text-body-1 mb-4">
            Please verify your email address to access your dashboard.
          </p>
          <p class="text-body-2 text-grey mb-4">
            We'll send a 6-digit verification code to <strong>{{ userEmail }}</strong>
          </p>
          
          <v-btn 
            color="primary" 
            block 
            size="large"
            @click="sendOTP"
            :loading="loading"
          >
            <v-icon start>mdi-email-send</v-icon>
            Send Verification Code
          </v-btn>
        </div>
        
        <div v-else>
          <p class="text-body-1 mb-4">
            Enter the 6-digit code sent to <strong>{{ userEmail }}</strong>
          </p>
          
          <v-otp-input
            v-model="otp"
            :length="6"
            type="number"
            variant="outlined"
            class="mb-4"
            @finish="verifyOTP"
          ></v-otp-input>
          
          <v-btn 
            color="primary" 
            block 
            size="large"
            @click="verifyOTP"
            :loading="loading"
            :disabled="otp.length !== 6"
          >
            <v-icon start>mdi-check-circle</v-icon>
            Verify Email
          </v-btn>
          
          <v-btn 
            variant="text" 
            block 
            class="mt-2"
            @click="resendOTP"
            :disabled="loading || countdown > 0"
          >
            {{ countdown > 0 ? `Resend code in ${countdown}s` : 'Resend Code' }}
          </v-btn>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'EmailVerificationModal',
  props: {
    userEmail: {
      type: String,
      required: true
    },
    isVerified: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      showModal: !this.isVerified,
      otpSent: false,
      otp: '',
      loading: false,
      error: null,
      success: null,
      countdown: 0,
      countdownInterval: null
    };
  },
  watch: {
    isVerified(newVal) {
      this.showModal = !newVal;
    }
  },
  methods: {
    async sendOTP() {
      this.loading = true;
      this.error = null;
      this.success = null;
      
      try {
        const response = await fetch('/api/auth/send-otp', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.otpSent = true;
          this.success = data.message;
          this.startCountdown();
        } else {
          this.error = data.message || 'Failed to send OTP';
        }
      } catch (err) {
        this.error = 'Network error. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
    async verifyOTP() {
      if (this.otp.length !== 6) return;
      
      this.loading = true;
      this.error = null;
      this.success = null;
      
      try {
        const response = await fetch('/api/auth/verify-otp', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({ otp: this.otp })
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.success = data.message;
          setTimeout(() => {
            this.showModal = false;
            this.$emit('verified');
            window.location.reload();
          }, 1500);
        } else {
          this.error = data.message || 'Invalid OTP';
          this.otp = '';
        }
      } catch (err) {
        this.error = 'Network error. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
    resendOTP() {
      this.otp = '';
      this.sendOTP();
    },
    
    startCountdown() {
      this.countdown = 60;
      this.countdownInterval = setInterval(() => {
        this.countdown--;
        if (this.countdown <= 0) {
          clearInterval(this.countdownInterval);
        }
      }, 1000);
    }
  },
  beforeUnmount() {
    if (this.countdownInterval) {
      clearInterval(this.countdownInterval);
    }
  }
};
</script>

<style scoped>
.v-dialog {
  z-index: 9999 !important;
}
</style>
