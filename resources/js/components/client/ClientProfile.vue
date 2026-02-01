<template>
  <div class="client-profile">
    <v-row>
      <v-col cols="12" md="8">
        <v-card elevation="0" class="mb-6 profile-card">
          <v-card-title class="card-header pa-6 pa-md-8">
            <span class="section-title primary--text">Personal Information</span>
          </v-card-title>
          <v-card-text class="pa-6 pa-md-8">
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="localProfile.firstName" 
                  label="First Name" 
                  variant="outlined"
                  @update:model-value="localProfile.firstName = filterLettersOnly(localProfile.firstName)"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="localProfile.lastName" 
                  label="Last Name" 
                  variant="outlined"
                  @update:model-value="localProfile.lastName = filterLettersOnly(localProfile.lastName)"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="localProfile.email" 
                  label="Email" 
                  variant="outlined" 
                  type="email" 
                  readonly
                >
                  <template v-slot:append-inner>
                    <v-tooltip :text="emailVerified ? 'Email Verified' : 'Email Not Verified'" location="top">
                      <template v-slot:activator="{ props }">
                        <v-icon 
                          v-bind="props"
                          :color="emailVerified ? 'success' : 'error'"
                          size="20"
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
                  v-model="localProfile.phone" 
                  label="Phone" 
                  variant="outlined" 
                  type="tel" 
                  inputmode="tel"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="localProfile.birthdate" 
                  label="Birthdate" 
                  variant="outlined" 
                  type="date"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  :model-value="age" 
                  label="Age" 
                  variant="outlined" 
                  readonly
                />
              </v-col>
              <v-col cols="12">
                <v-text-field 
                  v-model="localProfile.address" 
                  label="Address" 
                  variant="outlined"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-select 
                  v-model="localProfile.county" 
                  :items="counties" 
                  label="County/Borough" 
                  variant="outlined"
                  @update:model-value="onCountyChange"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-select 
                  v-model="localProfile.city" 
                  :items="availableCities" 
                  label="City" 
                  variant="outlined" 
                  :disabled="!localProfile.county"
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field 
                  model-value="New York" 
                  label="State" 
                  variant="outlined" 
                  readonly
                />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field 
                  v-model="localProfile.zip" 
                  label="ZIP Code" 
                  variant="outlined"
                  maxlength="5"
                  :rules="[v => !v || /^\d{5}$/.test(v) || 'Please enter a valid 5-digit ZIP code']"
                  @input="lookupZipCode"
                  @blur="lookupZipCode"
                >
                  <template v-slot:prepend-inner>
                    <v-icon>mdi-map-marker</v-icon>
                  </template>
                </v-text-field>
                <div v-if="zipLocation" class="zip-location-display mt-1">
                  {{ zipLocation }}
                </div>
              </v-col>
            </v-row>
            <v-btn 
              color="primary" 
              class="mt-4" 
              size="large" 
              @click="saveProfile"
              :loading="saving"
            >
              <v-icon start>mdi-content-save</v-icon>
              Save Changes
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <!-- Avatar & Info Card -->
        <v-card elevation="0" class="mb-6 profile-card">
          <v-card-text class="pa-6 pa-md-8 text-center">
            <div class="position-relative d-inline-block mb-4">
              <v-avatar size="120" color="primary">
                <img 
                  v-if="localAvatar && localAvatar.length > 0" 
                  :src="localAvatar" 
                  style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" 
                  @error="localAvatar = ''"
                />
                <span v-else class="text-h3 font-weight-bold text-white">{{ userInitials }}</span>
              </v-avatar>
              <v-btn 
                icon 
                size="small" 
                color="primary" 
                class="avatar-upload-btn"
                style="position: absolute; bottom: 0; right: 0;"
                @click="triggerAvatarUpload"
                :loading="uploadingAvatar"
              >
                <v-icon size="small">mdi-camera</v-icon>
              </v-btn>
              <input 
                ref="avatarInput" 
                type="file" 
                accept="image/jpeg,image/png,image/jpg,image/gif" 
                style="display: none;" 
                @change="uploadAvatar"
              />
            </div>
            <h2 class="mb-2">{{ userName }}</h2>
            <p class="text-grey mb-4">Premium Client</p>
            <v-chip color="primary" class="mb-4">Active</v-chip>
            <v-divider class="my-4" />
            <div class="profile-stat">
              <v-icon color="primary" class="mr-2">mdi-calendar</v-icon>
              <span>Member since {{ memberSince }}</span>
            </div>
          </v-card-text>
        </v-card>

        <!-- Change Password Card -->
        <v-card elevation="0" class="profile-card">
          <v-card-title class="card-header pa-6 pa-md-8">
            <span class="section-title primary--text">Change Password</span>
          </v-card-title>
          <v-card-text class="pa-6 pa-md-8">
            <v-form ref="passwordForm" @submit.prevent="changePassword">
              <v-text-field 
                v-model="passwordData.current"
                label="Current Password" 
                variant="outlined" 
                :type="showCurrentPassword ? 'text' : 'password'" 
                :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'" 
                @click:append-inner="showCurrentPassword = !showCurrentPassword" 
                class="mb-4"
                :rules="[v => !!v || 'Current password is required']"
              />
              <v-text-field 
                v-model="passwordData.new"
                label="New Password" 
                variant="outlined" 
                :type="showNewPassword ? 'text' : 'password'" 
                :append-inner-icon="showNewPassword ? 'mdi-eye-off' : 'mdi-eye'" 
                @click:append-inner="showNewPassword = !showNewPassword" 
                hint="8 minimum characters" 
                persistent-hint 
                class="mb-4"
                :rules="[
                  v => !!v || 'New password is required',
                  v => v.length >= 8 || 'Password must be at least 8 characters'
                ]"
              />
              <v-text-field 
                v-model="passwordData.confirm"
                label="Confirm New Password" 
                variant="outlined" 
                :type="showConfirmPassword ? 'text' : 'password'" 
                :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'" 
                @click:append-inner="showConfirmPassword = !showConfirmPassword" 
                class="mb-4"
                :rules="[
                  v => !!v || 'Please confirm your password',
                  v => v === passwordData.new || 'Passwords do not match'
                ]"
              />
              <v-btn 
                color="primary" 
                block 
                size="large" 
                type="submit"
                :loading="changingPassword"
              >
                <v-icon start>mdi-lock-reset</v-icon>
                Change Password
              </v-btn>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Success/Error Snackbar -->
    <v-snackbar v-model="showSnackbar" :color="snackbarColor" timeout="3000" location="top">
      {{ snackbarMessage }}
      <template v-slot:actions>
        <v-btn variant="text" @click="showSnackbar = false">Close</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
  profileData: {
    type: Object,
    default: () => ({})
  },
  userAvatar: {
    type: String,
    default: ''
  },
  emailVerified: {
    type: Boolean,
    default: false
  },
  memberSince: {
    type: String,
    default: ''
  },
  counties: {
    type: Array,
    default: () => []
  },
  citiesByCounty: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['save', 'change-password', 'upload-avatar']);

// Local state
const localProfile = ref({ ...props.profileData });
const localAvatar = ref(props.userAvatar);
const avatarInput = ref(null);
const passwordForm = ref(null);
const passwordData = ref({
  current: '',
  new: '',
  confirm: ''
});
const zipLocation = ref('');
const saving = ref(false);
const changingPassword = ref(false);
const uploadingAvatar = ref(false);
const showSnackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');

// Password visibility toggles
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Computed
const userName = computed(() => {
  return `${localProfile.value.firstName || ''} ${localProfile.value.lastName || ''}`.trim() || 'User';
});

const userInitials = computed(() => {
  const first = (localProfile.value.firstName || '')[0] || '';
  const last = (localProfile.value.lastName || '')[0] || '';
  return (first + last).toUpperCase() || 'U';
});

const age = computed(() => {
  if (!localProfile.value.birthdate) return '';
  const birthDate = new Date(localProfile.value.birthdate);
  const today = new Date();
  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }
  return age;
});

const availableCities = computed(() => {
  if (!localProfile.value.county || !props.citiesByCounty) return [];
  return props.citiesByCounty[localProfile.value.county] || [];
});

// Methods
const filterLettersOnly = (value) => {
  if (!value) return '';
  return value.replace(/[^a-zA-Z\s'-]/g, '');
};

const onCountyChange = () => {
  localProfile.value.city = '';
};

/**
 * NY ZIP Code Validation Helper
 * Valid NY ZIPs: 10xxx-14xxx range OR special cases (00501, 00544, 06390)
 */
const isValidNYZip = (zip) => {
  if (!zip) return false;
  const nyZipRegex = /^(00501|00544|06390|1[0-4]\d{3})(-\d{4})?$/;
  return nyZipRegex.test(zip);
};

const getNYRegionFromZip = (zip) => {
  if (!zip || !isValidNYZip(zip)) return null;
  const prefix = parseInt(zip.substring(0, 3), 10);
  if (prefix >= 100 && prefix <= 102) return 'Manhattan, NY';
  if (prefix === 103) return 'Staten Island, NY';
  if (prefix === 104) return 'Bronx, NY';
  if (prefix >= 105 && prefix <= 109) return 'Westchester, NY';
  if (prefix >= 110 && prefix <= 111) return 'Long Island, NY';
  if (prefix === 112) return 'Brooklyn, NY';
  if (prefix >= 113 && prefix <= 119) return 'Long Island, NY';
  if (prefix >= 120 && prefix <= 129) return 'Capital Region, NY';
  if (prefix >= 130 && prefix <= 139) return 'Central NY';
  if (prefix >= 140 && prefix <= 149) return 'Western NY';
  return 'New York, NY';
};

const lookupZipCode = async () => {
  const zip = localProfile.value.zip;
  if (zip && zip.length === 5) {
    // Client-side NY validation first
    if (!isValidNYZip(zip)) {
      zipLocation.value = 'Not a NY ZIP (10xxx-14xxx)';
      return;
    }
    
    try {
      const response = await fetch(`/api/zipcode-lookup/${zip}`);
      if (response.ok) {
        const data = await response.json();
        if (data.success && data.location) {
          zipLocation.value = data.location;
          return;
        }
        zipLocation.value = `${data.city}, ${data.state}`;
      } else {
        // Fallback to region for valid NY ZIPs
        zipLocation.value = getNYRegionFromZip(zip) || 'New York, NY';
      }
    } catch (error) {
      console.error('ZIP code lookup failed:', error);
      // Fallback to region
      zipLocation.value = getNYRegionFromZip(zip) || 'New York, NY';
    }
  } else {
    zipLocation.value = '';
  }
};

const saveProfile = async () => {
  saving.value = true;
  try {
    emit('save', localProfile.value);
    showNotification('Profile updated successfully', 'success');
  } catch (error) {
    showNotification('Failed to save profile', 'error');
  } finally {
    saving.value = false;
  }
};

const changePassword = async () => {
  const { valid } = await passwordForm.value.validate();
  if (!valid) return;

  changingPassword.value = true;
  try {
    emit('change-password', passwordData.value);
    passwordData.value = { current: '', new: '', confirm: '' };
    showNotification('Password changed successfully', 'success');
  } catch (error) {
    showNotification('Failed to change password', 'error');
  } finally {
    changingPassword.value = false;
  }
};

const triggerAvatarUpload = () => {
  avatarInput.value?.click();
};

const uploadAvatar = async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  // Validate file size (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    showNotification('Image size must be less than 2MB', 'error');
    return;
  }

  uploadingAvatar.value = true;
  try {
    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
      localAvatar.value = e.target.result;
    };
    reader.readAsDataURL(file);

    emit('upload-avatar', file);
    showNotification('Avatar updated successfully', 'success');
  } catch (error) {
    showNotification('Failed to upload avatar', 'error');
  } finally {
    uploadingAvatar.value = false;
  }
};

const showNotification = (message, color = 'success') => {
  snackbarMessage.value = message;
  snackbarColor.value = color;
  showSnackbar.value = true;
};

// Watch for prop changes
watch(() => props.profileData, (newVal) => {
  localProfile.value = { ...newVal };
}, { deep: true });

watch(() => props.userAvatar, (newVal) => {
  localAvatar.value = newVal;
});
</script>

<style scoped>
.client-profile {
  max-width: 1200px;
  margin: 0 auto;
}

.profile-card {
  border-radius: 16px;
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-bottom: 1px solid #e2e8f0;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
}

.avatar-upload-btn {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.profile-stat {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  font-size: 0.9375rem;
}

.zip-location-display {
  font-size: 0.875rem;
  color: #10b981;
  padding-left: 0.5rem;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .profile-card {
    margin-bottom: 1rem;
  }

  .section-title {
    font-size: 1.125rem;
  }
}

@media (max-width: 480px) {
  .client-profile .v-card-text {
    padding: 1rem !important;
  }

  .client-profile .card-header {
    padding: 1rem !important;
  }
}
</style>
