<template>
  <div class="admin-profile">
    <v-row>
      <!-- Personal Information Form -->
      <v-col cols="12" md="8">
        <v-card elevation="0" class="mb-6">
          <v-card-title class="card-header pa-8">
            <span class="section-title error--text">Personal Information</span>
          </v-card-title>
          <v-card-text class="pa-8">
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="localProfileData.firstName" 
                  label="First Name" 
                  variant="outlined" 
                  @update:model-value="localProfileData.firstName = filterLettersOnly(localProfileData.firstName)"
                  :error-messages="errors.firstName"
                  aria-required="true"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="localProfileData.lastName" 
                  label="Last Name" 
                  variant="outlined" 
                  @update:model-value="localProfileData.lastName = filterLettersOnly(localProfileData.lastName)"
                  :error-messages="errors.lastName"
                  aria-required="true"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="localProfileData.email" 
                  label="Email" 
                  variant="outlined" 
                  type="email"
                  :error-messages="errors.email"
                >
                  <template v-slot:append-inner>
                    <v-tooltip :text="emailVerified ? 'Email Verified' : 'Email Not Verified'" location="top">
                      <template v-slot:activator="{ props: tooltipProps }">
                        <v-icon 
                          v-bind="tooltipProps"
                          :color="emailVerified ? 'success' : 'error'"
                          size="20"
                          :aria-label="emailVerified ? 'Email verified' : 'Email not verified'"
                        >
                          {{ emailVerified ? 'mdi-check-circle' : 'mdi-close-circle' }}
                        </v-icon>
                      </template>
                    </v-tooltip>
                  </template>
                </v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="localProfileData.phone" 
                  label="Phone" 
                  variant="outlined"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="localProfileData.department" 
                  label="Department" 
                  variant="outlined"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-select 
                  v-model="localProfileData.role" 
                  :items="['Super Admin', 'Admin Staff']" 
                  label="Admin Role" 
                  variant="outlined"
                  :disabled="!isSuperAdmin"
                />
              </v-col>
            </v-row>
            <v-btn 
              color="error" 
              class="mt-4" 
              size="large" 
              @click="handleSaveProfile"
              :loading="saving"
            >
              Save Changes
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Profile Card & Password Change -->
      <v-col cols="12" md="4">
        <!-- Avatar & Info Card -->
        <v-card elevation="0" class="mb-6">
          <v-card-text class="pa-8 text-center">
            <div class="position-relative d-inline-block">
              <v-avatar 
                size="120" 
                color="error" 
                class="mb-4" 
                style="cursor: pointer;" 
                @click="triggerAvatarUpload"
                role="button"
                :aria-label="'Change profile photo'"
              >
                <img 
                  v-if="avatar" 
                  :src="avatar" 
                  :alt="fullName + ' profile photo'"
                  style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" 
                />
                <span v-else class="text-h3 font-weight-bold text-white">{{ initials }}</span>
              </v-avatar>
              <v-btn 
                icon 
                size="small" 
                color="error" 
                class="position-absolute" 
                style="bottom: 16px; right: -8px;" 
                @click="triggerAvatarUpload"
                :loading="uploadingAvatar"
                aria-label="Upload new avatar"
              >
                <v-icon size="small">mdi-camera</v-icon>
              </v-btn>
              <input 
                ref="avatarInput" 
                type="file" 
                accept="image/*" 
                style="display: none;" 
                @change="handleAvatarUpload"
                aria-label="Select profile image"
              />
            </div>
            <h2 class="mb-2">{{ fullName }}</h2>
            <p class="text-grey mb-4">{{ localProfileData.role || 'System Administrator' }}</p>
            <v-chip color="error" class="mb-4">Active</v-chip>
            <v-divider class="my-4" />
            <div class="profile-stat d-flex align-center justify-center mb-2">
              <v-icon color="error" class="mr-2">mdi-shield-check</v-icon>
              <span>{{ localProfileData.role || 'Super Admin' }} Access</span>
            </div>
            <div class="profile-stat d-flex align-center justify-center">
              <v-icon color="error" class="mr-2">mdi-calendar</v-icon>
              <span>Admin since {{ memberSince }}</span>
            </div>
          </v-card-text>
        </v-card>

        <!-- Change Password Card -->
        <v-card elevation="0">
          <v-card-title class="card-header pa-8">
            <div class="d-flex align-center">
              <v-icon color="error" class="mr-3">mdi-lock-reset</v-icon>
              <span class="section-title error--text">Change Password</span>
            </div>
          </v-card-title>
          <v-card-text class="pa-8">
            <v-text-field 
              v-model="passwordData.currentPassword"
              label="Current Password" 
              variant="outlined" 
              :type="showCurrentPassword ? 'text' : 'password'" 
              :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'" 
              @click:append-inner="showCurrentPassword = !showCurrentPassword" 
              class="mb-4"
              :error-messages="passwordErrors.currentPassword"
              autocomplete="current-password"
            />
            <v-text-field 
              v-model="passwordData.newPassword"
              label="New Password" 
              variant="outlined" 
              :type="showNewPassword ? 'text' : 'password'" 
              :append-inner-icon="showNewPassword ? 'mdi-eye-off' : 'mdi-eye'" 
              @click:append-inner="showNewPassword = !showNewPassword" 
              hint="8 minimum characters" 
              persistent-hint 
              class="mb-4"
              :error-messages="passwordErrors.newPassword"
              autocomplete="new-password"
            />
            <v-text-field 
              v-model="passwordData.confirmPassword"
              label="Confirm New Password" 
              variant="outlined" 
              :type="showConfirmPassword ? 'text' : 'password'" 
              :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'" 
              @click:append-inner="showConfirmPassword = !showConfirmPassword" 
              class="mb-4"
              :error-messages="passwordErrors.confirmPassword"
              autocomplete="new-password"
            />
            <v-btn 
              color="error" 
              block 
              size="large" 
              @click="handleChangePassword"
              :loading="changingPassword"
              :disabled="!isPasswordFormValid"
            >
              Change Password
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';

/**
 * AdminProfile - Admin profile management section
 * Personal information, avatar, and password change
 */

const props = defineProps({
  /** Profile data object */
  profileData: {
    type: Object,
    default: () => ({
      firstName: '',
      lastName: '',
      email: '',
      phone: '',
      department: '',
      role: 'Super Admin'
    })
  },
  /** User's avatar URL */
  userAvatar: {
    type: String,
    default: ''
  },
  /** Whether email is verified */
  emailVerified: {
    type: Boolean,
    default: false
  },
  /** Whether current user is super admin */
  isSuperAdmin: {
    type: Boolean,
    default: true
  },
  /** Member since date */
  memberSinceDate: {
    type: String,
    default: ''
  }
});

const emit = defineEmits([
  'save-profile',
  'change-password',
  'upload-avatar'
]);

// Local state
const avatarInput = ref(null);
const saving = ref(false);
const changingPassword = ref(false);
const uploadingAvatar = ref(false);

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Local copy of profile data
const localProfileData = reactive({ ...props.profileData });

// Watch for prop changes
watch(() => props.profileData, (newVal) => {
  Object.assign(localProfileData, newVal);
}, { deep: true });

// Password data
const passwordData = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
});

// Validation errors
const errors = reactive({
  firstName: '',
  lastName: '',
  email: ''
});

const passwordErrors = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
});

/**
 * Computed: User avatar
 */
const avatar = computed(() => props.userAvatar);

/**
 * Computed: User's full name
 */
const fullName = computed(() => {
  const first = localProfileData.firstName || '';
  const last = localProfileData.lastName || '';
  return first || last ? `${first} ${last}`.trim() : 'Admin User';
});

/**
 * Computed: User initials
 */
const initials = computed(() => {
  const first = localProfileData.firstName?.[0] || 'A';
  const last = localProfileData.lastName?.[0] || 'U';
  return `${first}${last}`.toUpperCase();
});

/**
 * Computed: Member since formatted
 */
const memberSince = computed(() => {
  if (props.memberSinceDate) {
    try {
      return new Date(props.memberSinceDate).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    } catch {
      return props.memberSinceDate;
    }
  }
  return 'Jan 2024';
});

/**
 * Computed: Password form validity
 */
const isPasswordFormValid = computed(() => {
  return (
    passwordData.currentPassword &&
    passwordData.newPassword &&
    passwordData.newPassword.length >= 8 &&
    passwordData.confirmPassword &&
    passwordData.newPassword === passwordData.confirmPassword
  );
});

/**
 * Filter letters only
 */
const filterLettersOnly = (value) => {
  if (!value) return '';
  return value.replace(/[^a-zA-Z\s'-]/g, '');
};

/**
 * Validate profile form
 */
const validateProfile = () => {
  errors.firstName = '';
  errors.lastName = '';
  errors.email = '';
  let valid = true;
  
  if (!localProfileData.firstName?.trim()) {
    errors.firstName = 'First name is required';
    valid = false;
  }
  
  if (!localProfileData.lastName?.trim()) {
    errors.lastName = 'Last name is required';
    valid = false;
  }
  
  if (!localProfileData.email?.trim()) {
    errors.email = 'Email is required';
    valid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(localProfileData.email)) {
    errors.email = 'Invalid email format';
    valid = false;
  }
  
  return valid;
};

/**
 * Validate password form
 */
const validatePassword = () => {
  passwordErrors.currentPassword = '';
  passwordErrors.newPassword = '';
  passwordErrors.confirmPassword = '';
  let valid = true;
  
  if (!passwordData.currentPassword) {
    passwordErrors.currentPassword = 'Current password is required';
    valid = false;
  }
  
  if (!passwordData.newPassword) {
    passwordErrors.newPassword = 'New password is required';
    valid = false;
  } else if (passwordData.newPassword.length < 8) {
    passwordErrors.newPassword = 'Password must be at least 8 characters';
    valid = false;
  }
  
  if (!passwordData.confirmPassword) {
    passwordErrors.confirmPassword = 'Please confirm your password';
    valid = false;
  } else if (passwordData.newPassword !== passwordData.confirmPassword) {
    passwordErrors.confirmPassword = 'Passwords do not match';
    valid = false;
  }
  
  return valid;
};

/**
 * Handle save profile
 */
const handleSaveProfile = () => {
  if (!validateProfile()) return;
  
  saving.value = true;
  emit('save-profile', { ...localProfileData });
  
  setTimeout(() => {
    saving.value = false;
  }, 500);
};

/**
 * Handle change password
 */
const handleChangePassword = () => {
  if (!validatePassword()) return;
  
  changingPassword.value = true;
  emit('change-password', {
    currentPassword: passwordData.currentPassword,
    newPassword: passwordData.newPassword
  });
  
  // Reset password fields
  setTimeout(() => {
    passwordData.currentPassword = '';
    passwordData.newPassword = '';
    passwordData.confirmPassword = '';
    changingPassword.value = false;
  }, 500);
};

/**
 * Trigger avatar file input
 */
const triggerAvatarUpload = () => {
  avatarInput.value?.click();
};

/**
 * Handle avatar upload
 */
const handleAvatarUpload = (event) => {
  const file = event.target.files?.[0];
  if (!file) return;
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    return;
  }
  
  // Validate file size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    return;
  }
  
  uploadingAvatar.value = true;
  emit('upload-avatar', file);
  
  setTimeout(() => {
    uploadingAvatar.value = false;
  }, 1000);
};
</script>

<style scoped>
.profile-stat {
  padding: 8px 0;
  color: #6b7280;
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
  }
}
</style>
