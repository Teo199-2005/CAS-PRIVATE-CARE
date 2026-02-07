<template>
  <div class="login-page">
    <!-- Background -->
    <div class="auth-bg">
      <div class="auth-bg-slice slice-1"></div>
      <div class="auth-bg-slice slice-2"></div>
      <div class="auth-bg-slice slice-3"></div>
    </div>

    <!-- Back to Home -->
    <div class="back-home">
      <a href="/" @click.prevent="goHome">
        <i class="bi bi-arrow-left"></i>
        <span>Back to Home</span>
      </a>
    </div>

    <!-- Login Container -->
    <main class="auth-container">
      <div class="auth-logo">
        <img :src="logoUrl" alt="CAS Private Care LLC" @error="handleLogoError">
      </div>

      <header class="auth-header">
        <h1>Welcome Back</h1>
        <p>Login to continue to CAS Private Care LLC</p>
      </header>

      <!-- Success Message -->
      <div v-if="successMessage" class="message success">
        <i class="bi bi-check-circle"></i>
        {{ successMessage }}
      </div>

      <!-- Error Message -->
      <div v-if="errorMessage" class="message error">
        <i class="bi bi-exclamation-circle"></i>
        {{ errorMessage }}
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" novalidate>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input
            type="email"
            id="email"
            v-model="email"
            class="form-input"
            :class="{ error: emailError }"
            placeholder="your@email.com"
            required
            autocomplete="email"
            :disabled="isLoading"
          >
          <span v-if="emailError" class="field-error">{{ emailError }}</span>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-wrapper">
            <input
              :type="showPassword ? 'text' : 'password'"
              id="password"
              v-model="password"
              class="form-input"
              :class="{ error: passwordError }"
              placeholder="Enter your password"
              required
              autocomplete="current-password"
              :disabled="isLoading"
            >
            <button
              type="button"
              class="password-toggle"
              @click="showPassword = !showPassword"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
            >
              <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
          </div>
          <span v-if="passwordError" class="field-error">{{ passwordError }}</span>
        </div>

        <div class="form-options">
          <label class="remember-me">
            <input type="checkbox" v-model="rememberMe">
            <span>Remember me</span>
          </label>
          <a href="#" class="forgot-link" @click.prevent="showForgotPassword = true">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-submit" :disabled="isLoading">
          <span v-if="isLoading" class="loading-spinner"></span>
          <span>{{ isLoading ? 'Logging in...' : 'Login' }}</span>
        </button>
      </form>

      <div class="divider">
        <span>or continue with</span>
      </div>

      <nav class="social-login">
        <a href="/auth/google" class="social-btn">
          <i class="bi bi-google"></i>
          <span>Google</span>
        </a>
      </nav>

      <footer class="auth-footer">
        <p>Don't have an account? <a href="/register">Sign up</a></p>
      </footer>
    </main>

    <!-- Forgot Password Modal -->
    <div v-if="showForgotPassword" class="modal-overlay" @click.self="showForgotPassword = false">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Reset Password</h2>
          <button type="button" class="modal-close" @click="showForgotPassword = false">
            <i class="bi bi-x"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>Enter your email address and we'll send you a link to reset your password.</p>
          
          <div v-if="resetMessage" :class="['message', resetMessageType]">
            <i :class="resetMessageType === 'success' ? 'bi bi-check-circle' : 'bi bi-exclamation-circle'"></i>
            {{ resetMessage }}
          </div>

          <form @submit.prevent="handleForgotPassword">
            <div class="form-group">
              <label for="resetEmail">Email Address</label>
              <input
                type="email"
                id="resetEmail"
                v-model="resetEmail"
                class="form-input"
                placeholder="your@email.com"
                required
                :disabled="isResetting"
              >
            </div>
            <button type="submit" class="btn-submit" :disabled="isResetting">
              <span v-if="isResetting" class="loading-spinner"></span>
              <span>{{ isResetting ? 'Sending...' : 'Send Reset Link' }}</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// Form data
const email = ref('');
const password = ref('');
const rememberMe = ref(false);
const showPassword = ref(false);

// UI state
const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const emailError = ref('');
const passwordError = ref('');

// Forgot password
const showForgotPassword = ref(false);
const resetEmail = ref('');
const isResetting = ref(false);
const resetMessage = ref('');
const resetMessageType = ref('success');

// CSRF token
const csrfToken = ref('');

// Logo URL with fallback
const logoUrl = ref('/logo flower.png');

const handleLogoError = () => {
  // Try alternate paths if logo fails to load
  const altPaths = ['/logo%20flower.png', '/images/logo.png', '/assets/logo.png'];
  const currentIndex = altPaths.indexOf(logoUrl.value);
  if (currentIndex < altPaths.length - 1) {
    logoUrl.value = altPaths[currentIndex + 1] || altPaths[0];
  }
};

// Get CSRF token on mount
onMounted(async () => {
  // If we landed here after logout with ?refresh=, do a full page reload for a clean state (avoids stale form/session issues)
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('refresh')) {
    urlParams.delete('refresh');
    const q = urlParams.toString();
    history.replaceState(null, '', '/login' + (q ? '?' + q : ''));
    window.location.reload();
    return;
  }

  // Get CSRF token from meta tag or fetch it
  const metaToken = document.querySelector('meta[name="csrf-token"]');
  if (metaToken) {
    csrfToken.value = metaToken.getAttribute('content');
  } else {
    // Fetch CSRF cookie
    await fetch('/sanctum/csrf-cookie', {
      credentials: 'include'
    });
  }

  // Load remembered email
  const savedEmail = localStorage.getItem('rememberedEmail');
  if (savedEmail) {
    email.value = savedEmail;
    rememberMe.value = true;
  }

  // Check for success message in URL
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('registered')) {
    successMessage.value = 'Registration successful! Please login.';
  }
  if (urlParams.get('password_reset')) {
    successMessage.value = 'Password reset successful! Please login with your new password.';
  }
  if (urlParams.get('expired')) {
    errorMessage.value = 'Your session has expired. Please log in again.';
  }
});

// Validate form
const validateForm = () => {
  let valid = true;
  emailError.value = '';
  passwordError.value = '';

  if (!email.value) {
    emailError.value = 'Email is required';
    valid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    emailError.value = 'Please enter a valid email';
    valid = false;
  }

  if (!password.value) {
    passwordError.value = 'Password is required';
    valid = false;
  }

  return valid;
};

// Handle login
const handleLogin = async () => {
  if (!validateForm()) return;

  isLoading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    // First, ensure we have fresh CSRF cookie
    await fetch('/sanctum/csrf-cookie', {
      credentials: 'include'
    });

    // Get updated CSRF token
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    const token = metaToken ? metaToken.getAttribute('content') : csrfToken.value;

    // Perform login
    const response = await fetch('/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'include',
      body: JSON.stringify({
        email: email.value,
        password: password.value,
        remember: rememberMe.value
      })
    });

    const data = await response.json();

    if (response.ok && data.success) {
      // Save email if remember me is checked
      if (rememberMe.value) {
        localStorage.setItem('rememberedEmail', email.value);
      } else {
        localStorage.removeItem('rememberedEmail');
      }

      successMessage.value = 'Login successful! Redirecting...';

      // Wait a moment for session to be fully established
      await new Promise(resolve => setTimeout(resolve, 500));

      // Verify session is established by making a test request
      try {
        const verifyResponse = await fetch('/api/profile', {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'include'
        });

        if (verifyResponse.ok) {
          // Session is ready, redirect to dashboard
          const redirectUrl = data.redirect || getDashboardUrl(data.user?.role || data.user?.user_type);
          window.location.href = redirectUrl;
        } else {
          // Session might not be ready, try hard refresh
          window.location.href = data.redirect || '/dashboard';
        }
      } catch (e) {
        // Fallback redirect
        window.location.href = data.redirect || '/dashboard';
      }
    } else {
      errorMessage.value = data.message || data.error || 'Invalid email or password';
    }
  } catch (error) {
    console.error('Login error:', error);
    errorMessage.value = 'An error occurred. Please try again.';
  } finally {
    isLoading.value = false;
  }
};

// Get dashboard URL based on user role
const getDashboardUrl = (role) => {
  const dashboards = {
    'admin': '/admin/dashboard',
    'admin_staff': '/admin-staff/dashboard',
    'client': '/client/dashboard',
    'caregiver': '/caregiver/dashboard',
    'housekeeper': '/housekeeper/dashboard',
    'marketing': '/marketing/dashboard',
    'training': '/training/dashboard'
  };
  return dashboards[role] || '/dashboard';
};

// Handle forgot password
const handleForgotPassword = async () => {
  if (!resetEmail.value) {
    resetMessage.value = 'Please enter your email address';
    resetMessageType.value = 'error';
    return;
  }

  isResetting.value = true;
  resetMessage.value = '';

  try {
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    const token = metaToken ? metaToken.getAttribute('content') : csrfToken.value;

    const response = await fetch('/password/email', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'include',
      body: JSON.stringify({ email: resetEmail.value })
    });

    const data = await response.json();

    if (response.ok) {
      resetMessage.value = 'Password reset link sent! Please check your email.';
      resetMessageType.value = 'success';
      setTimeout(() => {
        showForgotPassword.value = false;
        resetEmail.value = '';
        resetMessage.value = '';
      }, 3000);
    } else {
      resetMessage.value = data.message || 'Email not found in our system.';
      resetMessageType.value = 'error';
    }
  } catch (error) {
    console.error('Reset error:', error);
    resetMessage.value = 'An error occurred. Please try again.';
    resetMessageType.value = 'error';
  } finally {
    isResetting.value = false;
  }
};

// Navigate to home
const goHome = () => {
  window.location.href = '/';
};
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.login-page {
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

/* Background with image slices */
.auth-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  z-index: 0;
}

.auth-bg-slice {
  flex: 1;
  background-size: cover;
  background-position: center;
  transform: skewX(-5deg);
  margin: 0 -2%;
}

.auth-bg-slice.slice-1 {
  background-image: url('https://images.unsplash.com/photo-1609220136736-443140cffec6?w=800&q=80');
}

.auth-bg-slice.slice-2 {
  background-image: url('https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=800&q=80');
}

.auth-bg-slice.slice-3 {
  background-image: url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&q=80');
}

.auth-bg::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(30, 64, 175, 0.9) 0%, rgba(59, 130, 246, 0.85) 50%, rgba(96, 165, 250, 0.8) 100%);
  z-index: 1;
}

/* Main container */
.auth-container {
  position: relative;
  z-index: 2;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(20px);
  border-radius: 30px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  padding: 2.5rem;
  width: 90%;
  max-width: 420px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Logo */
.auth-logo {
  text-align: center;
  margin-bottom: 0.5rem;
}

.auth-logo img {
  height: 110px;
  width: auto;
  object-fit: contain;
}

/* Header */
.auth-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.auth-header h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e40af;
  margin-bottom: 0.5rem;
  font-style: normal;
}

.auth-header p {
  color: #64748b;
  font-size: 0.9rem;
}

/* Form styling */
.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  color: #374151;
  font-weight: 600;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  font-family: inherit;
  background: #fff;
  color: #1f2937;
}

.form-input::placeholder {
  color: #9ca3af;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.form-input.error {
  border-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-input:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
  opacity: 0.7;
}

.field-error {
  display: block;
  color: #dc2626;
  font-size: 0.75rem;
  margin-top: 0.35rem;
  font-weight: 500;
}

/* Password field */
.password-wrapper {
  position: relative;
}

.password-wrapper .form-input {
  padding-right: 3rem;
}

.password-toggle {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  font-size: 1.1rem;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.password-toggle:hover {
  color: #3b82f6;
}

/* Form options row */
.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
  font-size: 0.85rem;
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6b7280;
  cursor: pointer;
  user-select: none;
}

.remember-me input[type="checkbox"] {
  width: 16px;
  height: 16px;
  cursor: pointer;
  accent-color: #3b82f6;
  border-radius: 4px;
}

.forgot-link {
  color: #3b82f6;
  text-decoration: underline;
  font-weight: 500;
  transition: color 0.2s;
}

.forgot-link:hover {
  color: #1d4ed8;
}

/* Submit button */
.btn-submit {
  width: 100%;
  padding: 0.875rem 1rem;
  background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.btn-submit:active:not(:disabled) {
  transform: translateY(0);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.loading-spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Divider */
.divider {
  display: flex;
  align-items: center;
  margin: 1.25rem 0;
  color: #9ca3af;
  font-size: 0.8rem;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #e5e7eb;
}

.divider span {
  padding: 0 0.75rem;
}

/* Social login */
.social-login {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.social-btn {
  padding: 0.625rem 1.5rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #374151;
  text-decoration: none;
  font-size: 0.9rem;
}

.social-btn:hover {
  border-color: #3b82f6;
  background: #eff6ff;
  color: #1d4ed8;
}

.social-btn i {
  font-size: 1.1rem;
}

/* Footer */
.auth-footer {
  text-align: center;
  margin-top: 1.25rem;
  color: #6b7280;
  font-size: 0.875rem;
}

.auth-footer a {
  color: #3b82f6;
  text-decoration: underline;
  font-weight: 600;
}

.auth-footer a:hover {
  color: #1d4ed8;
}

/* Messages */
.message {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.message i {
  font-size: 1rem;
}

.message.success {
  background: #dcfce7;
  border: 1px solid #86efac;
  color: #166534;
}

.message.error {
  background: #fee2e2;
  border: 1px solid #fca5a5;
  color: #991b1b;
}

/* Back to home button */
.back-home {
  position: absolute;
  top: 1.5rem;
  left: 1.5rem;
  z-index: 3;
}

.back-home a {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: white;
  text-decoration: none;
  font-weight: 600;
  padding: 0.625rem 1.25rem;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border-radius: 50px;
  transition: all 0.2s ease;
  font-size: 0.9rem;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.back-home a:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateX(-3px);
}

.back-home a i {
  font-size: 1rem;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 16px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  width: 100%;
  max-width: 380px;
  overflow: hidden;
  animation: modalSlideIn 0.2s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
  color: white;
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 700;
}

.modal-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background 0.2s;
}

.modal-close:hover {
  background: rgba(255, 255, 255, 0.2);
}

.modal-body {
  padding: 1.5rem;
}

.modal-body > p {
  color: #6b7280;
  margin-bottom: 1.25rem;
  line-height: 1.5;
  font-size: 0.9rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .auth-container {
    padding: 2rem;
    margin: 1rem;
    border-radius: 24px;
  }

  .back-home {
    top: 1rem;
    left: 1rem;
  }
}

@media (max-width: 480px) {
  .auth-container {
    padding: 1.5rem;
    margin: 0.5rem;
    border-radius: 20px;
  }

  .auth-logo img {
    height: 90px;
  }

  .auth-header h1 {
    font-size: 1.5rem;
  }

  .auth-header p {
    font-size: 0.85rem;
  }

  .form-input {
    padding: 0.7rem 0.875rem;
    font-size: 16px; /* Prevents iOS zoom */
  }

  .btn-submit {
    padding: 0.8rem;
    font-size: 0.95rem;
  }

  .back-home {
    top: 0.75rem;
    left: 0.75rem;
  }

  .back-home a {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
  }

  .form-options {
    flex-direction: column;
    gap: 0.75rem;
    align-items: flex-start;
  }

  .social-btn {
    padding: 0.625rem 1.25rem;
    font-size: 0.85rem;
  }
}

@media (max-width: 360px) {
  .auth-container {
    padding: 1.25rem;
  }

  .auth-logo img {
    height: 80px;
  }

  .auth-header h1 {
    font-size: 1.35rem;
  }
}
</style>
