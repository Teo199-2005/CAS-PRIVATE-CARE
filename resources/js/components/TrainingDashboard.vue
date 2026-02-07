<template>
  <!-- Global Loading Overlay -->
  <LoadingOverlay 
    :visible="isPageLoading" 
    context="training"
    tagline="Training Center Portal"
  />

  <notification-toast
    v-model="notification.show"
    :type="notification.type"
    :title="notification.title"
    :message="notification.message"
    :timeout="notification.timeout"
  />
  
  <dashboard-template
    user-role="training"
    :user-name="profile.firstName && profile.lastName ? `${profile.firstName} ${profile.lastName}` : 'Training Center'"
    :user-initials="profile.firstName && profile.lastName ? `${profile.firstName[0]}${profile.lastName[0]}` : 'TC'"
    :user-avatar="userAvatar"
    :welcome-message="profile.firstName ? `Welcome Back, ${profile.firstName}` : 'Welcome Back, Training'"
    subtitle="Manage caregiver training and certifications"
    header-title="Training Center Dashboard"
    header-subtitle="Caregiver education and certification management"
    :nav-items="navItems"
    :current-section="currentSection"
    @section-change="currentSection = $event"
    @logout="logout"
  >
        <!-- Error Modal -->
    <v-dialog v-model="showErrorModal" max-width="500">
      <v-card class="error-modal-card" elevation="8">
        <v-card-title class="error-modal-header pa-6">
          <div class="d-flex align-center">
            <v-icon color="white" size="32" class="mr-3">mdi-alert-circle</v-icon>
            <span class="text-h5 font-weight-bold text-white">Validation Error</span>
          </div>
        </v-card-title>
        <v-card-text class="pa-6">
          <div class="mb-4 text-center">
            <v-icon color="error" size="64">mdi-alert-circle-outline</v-icon>
          </div>
          <p class="text-h6 mb-4 text-center" style="color: #1e293b;">Please fix the following errors:</p>
          <v-alert type="error" variant="tonal" class="mb-0">
            <div v-if="Array.isArray(errorMessages)" class="error-list">
              <div v-for="(error, index) in errorMessages" :key="index" class="error-item mb-2">
                <v-icon size="16" class="mr-2">mdi-alert</v-icon>
                {{ error }}
              </div>
            </div>
            <div v-else class="error-item">
              <v-icon size="16" class="mr-2">mdi-alert</v-icon>
              {{ errorMessages }}
            </div>
          </v-alert>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer></v-spacer>
          <v-btn 
            color="error" 
            variant="flat" 
            size="large"
            prepend-icon="mdi-close"
            @click="showErrorModal = false"
          >
            Close
          </v-btn>
          <v-spacer></v-spacer>
        </v-card-actions>
      </v-card>
    </v-dialog>
<!-- Email Verification Modal (blocks dashboard until verified via OTP) -->
    <email-verification-modal
      v-if="!isEmailVerified && userEmail"
      :user-email="userEmail"
      :is-verified="isEmailVerified"
      @verified="handleEmailVerified"
    />

    <!-- Dashboard Section -->
    <div v-if="currentSection === 'dashboard'">
      <v-row class="mb-2">
        <v-col cols="12" sm="6" md="3">
          <v-card elevation="0" class="mb-3 account-balance-card d-flex flex-column" style="height: 100%;">
            <v-card-title class="account-balance-header pa-4">
              <span class="section-title-compact grey--text text--darken-2">Account Balance</span>
            </v-card-title>
            <v-card-text class="pa-4 flex-grow-1 d-flex flex-column justify-space-between">
              <div>
                <div class="text-center mb-3">
                  <div class="balance-amount grey--text text--darken-2">${{ (Number(accountBalance) || 0).toFixed(2) }}</div>
                  <div class="text-caption text-grey">Available Balance</div>
                </div>
                <div class="d-flex justify-space-between text-caption mb-1">
                  <span>Auto Payout:</span>
                  <span class="grey--text text--darken-2 font-weight-bold">Every Friday</span>
                </div>
                <div class="d-flex justify-space-between text-caption mb-2">
                  <span>Next Payout:</span>
                  <span class="font-weight-bold">{{ nextPayoutDate }}</span>
                </div>
                <div class="d-flex justify-space-between text-caption mb-1">
                  <span>Today:</span>
                  <span class="font-weight-bold">{{ todayFormatted }}</span>
                </div>
                <div class="d-flex justify-space-between text-caption">
                  <span>Covering Date:</span>
                  <span class="font-weight-bold">{{ coveringDateRange }}</span>
                </div>
              </div>
              <div>
                <v-btn block variant="outlined" color="grey-darken-2" size="x-small">Request Payout</v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col v-for="(stat, index) in stats" :key="stat.title" cols="6" sm="6" md="3">
          <stat-card 
            :icon="stat.icon" 
            :value="stat.value" 
            :label="stat.title" 
            :change="stat.change" 
            :change-color="stat.changeColor" 
            :change-icon="stat.changeIcon" 
            icon-class="grey-darken-2"
            :stagger-index="index + 1"
          />
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12" md="6">
          <v-card class="mb-3 enhanced-card d-flex flex-column" elevation="2">
            <v-card-title class="enhanced-card-header pa-6">
              <v-icon color="grey-darken-2" class="mr-3">mdi-chart-line</v-icon>
              <span class="section-title grey--text text--darken-2">Previous Week Summary</span>
            </v-card-title>
            <v-card-text class="pa-4 flex-grow-1 d-flex flex-column justify-space-between">
              <div>
                <div class="mb-3">
                  <div class="d-flex justify-space-between mb-1">
                    <span class="summary-label-compact">Deployed Caregivers</span>
                    <span class="summary-value-compact">{{ weeklySummary.deployed_caregivers }} caregivers</span>
                  </div>
                  <v-progress-linear :model-value="(weeklySummary.deployed_caregivers / weeklySummary.target) * 100" color="info" height="6" rounded />
                  <div class="text-caption text-grey mt-1">Target: {{ weeklySummary.target }} caregivers/week</div>
                </div>
              </div>
              <div>
                <v-divider class="my-2" />
                <div class="summary-item-compact">
                  <span class="summary-label-compact">Previous Payout</span>
                  <span class="summary-value-compact font-weight-bold">${{ (Number(weeklySummary.previous_payout) || 0).toFixed(2) }} - {{ weeklySummary.previous_payout_date || 'N/A' }}</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card elevation="0" class="mb-3 compact-card">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact grey--text text--darken-2">Training Revenue</span>
            </v-card-title>
            <v-card-text class="pa-4">
              <div class="earnings-summary">
                <div class="earning-item mb-3">
                  <div class="d-flex justify-space-between">
                    <span class="earning-label">Total Revenue</span>
                    <span class="earning-value grey--text text--darken-2">${{ totalRevenue }}</span>
                  </div>
                </div>
                <div class="earning-item mb-3">
                  <div class="d-flex justify-space-between">
                    <span class="earning-label">This Month</span>
                    <span class="earning-value">${{ monthlyRevenue }}</span>
                  </div>
                </div>
                <div class="earning-item">
                  <div class="d-flex justify-space-between">
                    <span class="earning-label">Active Trainees</span>
                    <span class="earning-value">{{ stats[0]?.value ?? '0' }}</span>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

</div>

    <!-- Trainees Section -->
    <div v-if="currentSection === 'trainees'">
      <div class="mb-6">
        <v-btn color="grey-darken-2" prepend-icon="mdi-plus" @click="addTraineeDialog = true">Add Trainee</v-btn>
      </div>
      <v-card elevation="0">
        <v-card-title class="card-header pa-8">
          <span class="section-title grey--text text--darken-2">Training Participants</span>
        </v-card-title>
        <v-data-table :headers="traineeHeaders" :items="trainedCaregivers" :items-per-page="10" class="elevation-0">
          <template v-slot:item.certification="{ item }">
            <v-chip :color="getCertificationColor(item.certification)" size="small" class="font-weight-bold">{{ item.certification }}</v-chip>
          </template>
          <template v-slot:item.earnings="{ item }">
            <span class="font-weight-bold grey--text text--darken-2">${{ item.earnings }}</span>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn class="action-btn-view" icon="mdi-eye" @click="viewTrainee(item)" :aria-label="`View ${item.name || 'trainee'} details`"></v-btn>
              <v-btn class="action-btn-edit" icon="mdi-school" @click="manageCertification(item)" :aria-label="`Manage certification for ${item.name || 'trainee'}`"></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- Pending Caregiver Requests Section -->
    <div v-if="currentSection === 'pending-requests'">
      <v-card elevation="0">
        <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
          <span class="section-title grey--text text--darken-2">Pending Caregiver Requests</span>
          <v-chip color="warning" size="small">{{ pendingCaregivers.length }} Pending</v-chip>
        </v-card-title>
        <v-card-text class="pa-0">
          <v-data-table :headers="pendingHeaders" :items="pendingCaregivers" :items-per-page="10" class="elevation-0 pending-requests-table">
            <template v-slot:item.years_experience="{ item }">
              <span>{{ item.years_experience }} years</span>
            </template>
            <template v-slot:item.specializations="{ item }">
              <v-chip v-for="(spec, idx) in (item.specializations || []).slice(0, 2)" :key="idx" size="x-small" class="mr-1">{{ spec }}</v-chip>
              <span v-if="(item.specializations || []).length > 2" class="text-caption">+{{ (item.specializations || []).length - 2 }} more</span>
            </template>
            <template v-slot:item.actions="{ item }">
              <div class="action-buttons action-buttons--pending">
                <v-btn color="success" size="small" variant="flat" class="action-btn-pending" @click="approveCaregiverRequest(item)">
                  <v-icon size="18" class="mr-1">mdi-check</v-icon>
                  Approve
                </v-btn>
                <v-btn color="error" size="small" variant="flat" class="action-btn-pending" @click="rejectCaregiverRequest(item)">
                  <v-icon size="18" class="mr-1">mdi-close</v-icon>
                  Reject
                </v-btn>
              </div>
            </template>
            <template v-slot:no-data>
              <div class="text-center pa-8">
                <v-icon size="64" color="grey-lighten-1">mdi-check-circle-outline</v-icon>
                <div class="text-h6 mt-4 grey--text">No Pending Requests</div>
                <div class="text-body-2 grey--text">All caregiver requests have been processed.</div>
              </div>
            </template>
          </v-data-table>
        </v-card-text>
      </v-card>
    </div>

    <!-- My Contractors Section -->
    <div v-if="currentSection === 'contractors'">
      <div class="mb-6">
        <v-row class="align-center">
          <v-col cols="12" md="4">
            <v-text-field v-model="caregiverSearch" placeholder="Search caregivers..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="4">
            <v-select v-model="certificationFilter" :items="certificationOptions" label="Certification" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="4">
            <v-select v-model="boroughFilter" :items="boroughs" label="Borough" variant="outlined" density="compact" hide-details />
          </v-col>
        </v-row>
      </div>
      <v-card elevation="0">
        <v-card-title class="card-header pa-8">
          <span class="section-title grey--text text--darken-2">List of Caregivers</span>
        </v-card-title>
        <v-data-table :headers="contractorHeaders" :items="filteredCaregivers" :items-per-page="10" class="elevation-0">
          <template v-slot:item.status="{ item }">
            <v-chip :color="getStatusColor(item.status)" size="small" class="font-weight-bold">{{ item.status }}</v-chip>
          </template>
          <template v-slot:item.certification="{ item }">
            <v-chip :color="getCertificationColor(item.certification)" size="small" class="font-weight-bold">{{ item.certification }}</v-chip>
          </template>
          <template v-slot:item.earnings="{ item }">
            <span class="font-weight-bold grey--text text--darken-2">${{ item.earnings }}</span>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <template v-if="item.certification === 'Certified'">
                <v-btn class="action-btn-view" icon="mdi-eye" @click="viewTrainee(item)" :aria-label="`View ${item.name || 'trainee'} details`"></v-btn>
                <v-btn class="action-btn-edit" icon="mdi-pencil" @click="manageCertification(item)" :aria-label="`Edit certification for ${item.name || 'trainee'}`"></v-btn>
              </template>
              <template v-else>
                <v-btn class="action-btn-approve" icon="mdi-check" @click="approveCaregiver(item)" :aria-label="`Approve ${item.name || 'caregiver'}`"></v-btn>
                <v-btn class="action-btn-reject" icon="mdi-close" @click="rejectCaregiver(item)" :aria-label="`Reject ${item.name || 'caregiver'}`"></v-btn>
              </template>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- Analytics Section -->
    <div v-if="currentSection === 'analytics'">
      <v-row class="mb-4">
        <v-col v-for="metric in analyticsMetrics" :key="metric.title" cols="12" sm="6" md="3">
          <v-card elevation="0" class="compact-stat-card">
            <v-card-text class="pa-4">
              <div class="d-flex align-center">
                <v-icon :color="metric.color" size="24" class="mr-3">{{ metric.icon }}</v-icon>
                <div>
                  <div class="stat-value" :class="metric.color + '--text'">{{ metric.value }}</div>
                  <div class="stat-label">{{ metric.title }}</div>
                  <div class="stat-change" :class="metric.changeColor">{{ metric.change }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12" md="6">
          <v-card elevation="0" class="mb-3">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact grey--text text--darken-2">Monthly Revenue</span>
            </v-card-title>
            <v-card-text class="pa-4">
              <div style="height: 200px; position: relative;">
                <canvas ref="revenueChart"></canvas>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card elevation="0" class="mb-3">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact grey--text text--darken-2">Certification Status</span>
            </v-card-title>
            <v-card-text class="pa-4">
              <div style="height: 200px; position: relative;">
                <canvas ref="certificationChart"></canvas>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Courses Section -->
    <div v-if="currentSection === 'courses'">
      <div class="mb-6">
        <v-btn color="grey-darken-2" prepend-icon="mdi-plus" @click="addCourseDialog = true">Add Course</v-btn>
      </div>
      <v-row>
        <v-col v-for="course in courses" :key="course.id" cols="12" md="4">
          <v-card elevation="0" class="course-card">
            <v-card-title class="card-header pa-4">
              <span class="course-title">{{ course.name }}</span>
            </v-card-title>
            <v-card-text class="pa-4">
              <div class="course-info mb-3">
                <div class="course-duration">{{ course.duration }}</div>
                <div class="course-price">${{ course.price }}</div>
              </div>
              <div class="course-stats">
                <div class="stat-item">
                  <span class="stat-label">Enrolled:</span>
                  <span class="stat-value">{{ course.enrolled }}</span>
                </div>
                <div class="stat-item">
                  <span class="stat-label">Completed:</span>
                  <span class="stat-value">{{ course.completed }}</span>
                </div>
              </div>
            </v-card-text>
            <v-card-actions class="pa-4">
              <v-btn color="grey-darken-2" size="small">Manage</v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Notifications Section -->
    <div v-if="currentSection === 'notifications'">
      <notification-center ref="notificationCenter" user-type="training" :user-id="5" @action-clicked="handleNotificationAction" />
    </div>

    <!-- Profile Section -->
    <div v-if="currentSection === 'profile'">
      <v-row>
        <v-col cols="12" md="8">
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-8">
              <span class="section-title grey--text text--darken-2">Personal Information</span>
            </v-card-title>
            <v-card-text class="pa-8">
              <v-row>
                <v-col cols="12" md="6">
                  <div class="readonly-field">
                    <label class="readonly-label">First Name</label>
                    <div class="readonly-value">{{ profile.firstName }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="readonly-field">
                    <label class="readonly-label">Last Name</label>
                    <div class="readonly-value">{{ profile.lastName }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="profile.email" label="Email" variant="outlined" type="email" readonly>
                    <template v-slot:append-inner>
                      <v-tooltip :text="userEmailVerified ? 'Email Verified' : 'Email Not Verified'" location="top">
                        <template v-slot:activator="{ props }">
                          <v-icon 
                            v-bind="props"
                            :color="userEmailVerified ? 'success' : 'error'"
                            size="20"
                          >
                            {{ userEmailVerified ? 'mdi-check-circle' : 'mdi-close-circle' }}
                          </v-icon>
                        </template>
                      </v-tooltip>
                    </template>
                  </v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="profile.phone" label="Phone" variant="outlined" />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="profile.birthdate" label="Birthdate" variant="outlined" type="date" />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field :model-value="age" label="Age" variant="outlined" readonly />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="profile.address" label="Address" variant="outlined" />
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field :model-value="'New York'" label="State" variant="outlined" readonly class="readonly-field" />
                </v-col>
                <v-col cols="12" md="4">
                  <v-select v-model="profile.county" :items="counties" label="County/Borough" variant="outlined" />
                </v-col>
                <v-col cols="12" md="4">
                  <v-select v-model="profile.city" :items="nyCities" label="City" variant="outlined" :disabled="!profile.county" />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field 
                    v-model="profile.zip" 
                    label="ZIP Code" 
                    variant="outlined" 
                    maxlength="5"
                    :rules="[
                      v => !v || /^\d{5}$/.test(v) || 'Enter a 5-digit ZIP code',
                      v => !v || /^(00501|00544|06390|1[0-4]\d{3})$/.test(v) || 'Must be a NY ZIP (10xxx-14xxx)'
                    ]"
                    @input="lookupProfileZipCode"
                    @blur="lookupProfileZipCode"
                  >
                    <template v-slot:prepend-inner>
                      <v-icon>mdi-map-marker</v-icon>
                    </template>
                  </v-text-field>
                  <div v-if="profileZipLocation" :class="['text-caption', 'mt-1', profileZipLocation.includes('Not a NY') ? 'text-error' : 'text-grey']" style="font-weight: 600;">
                    {{ profileZipLocation }}
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="profile.department" label="Department" variant="outlined" />
                </v-col>
                <v-col cols="12" md="6">
                  <v-select v-model="profile.role" :items="['Training Director', 'Training Coordinator', 'Training Specialist']" label="Role" variant="outlined" />
                </v-col>
              </v-row>
              <v-btn color="grey-darken-2" class="mt-4" size="large" @click="saveProfile">Save Changes</v-btn>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-6">
            <v-card-text class="pa-8 text-center">
              <div class="position-relative d-inline-block mb-4">
                <v-avatar size="120" color="grey-darken-2">
                  <img v-if="userAvatar" :src="userAvatar" :alt="`${profile.center_name || 'Training Center'} profile photo`" style="width: 100%; height: 100%; object-fit: cover;" />
                  <span v-else class="text-h3 font-weight-bold">{{ userInitials }}</span>
                </v-avatar>
                <v-btn 
                  icon 
                  size="small" 
                  color="grey-darken-2" 
                  class="avatar-upload-btn"
                  style="position: absolute; bottom: 0; right: 0;"
                  @click="triggerAvatarUpload"
                  :loading="uploadingAvatar"
                  aria-label="Upload profile photo"
                >
                  <v-icon size="small">mdi-camera</v-icon>
                </v-btn>
                <input 
                  ref="avatarInput" 
                  type="file" 
                  accept="image/jpeg,image/png,image/jpg,image/gif" 
                  style="display: none;" 
                  @change="uploadAvatar"
                  aria-label="Select profile photo"
                />
              </div>
              <h2 class="mb-2">{{ userName }}</h2>
              <p class="text-grey mb-4">Training & Certification</p>
              <v-chip color="grey-darken-2" class="mb-4">Active</v-chip>
              <v-divider class="my-4" />
              <div class="profile-stat">
                <v-icon color="grey-darken-2" class="mr-2">mdi-calendar</v-icon>
                <span>Member since {{ memberSince }}</span>
              </div>
            </v-card-text>
          </v-card>

          <v-card elevation="0">
            <v-card-title class="card-header pa-8">
              <div class="d-flex align-center">
                <v-icon color="grey-darken-2" class="mr-3">mdi-lock-reset</v-icon>
                <span class="section-title grey--text text--darken-2">Change Password</span>
              </div>
            </v-card-title>
            <v-card-text class="pa-8">
              <v-text-field label="Current Password" variant="outlined" :type="showCurrentPassword ? 'text' : 'password'" :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showCurrentPassword = !showCurrentPassword" class="mb-4" />
              <v-text-field label="New Password" variant="outlined" :type="showNewPassword ? 'text' : 'password'" :append-inner-icon="showNewPassword ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showNewPassword = !showNewPassword" hint="8 minimum characters" persistent-hint class="mb-4" />
              <v-text-field label="Confirm New Password" variant="outlined" :type="showConfirmPassword ? 'text' : 'password'" :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showConfirmPassword = !showConfirmPassword" class="mb-4" />
              <v-btn color="grey-darken-2" block size="large">Change Password</v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Payments Section (layout aligned with Marketing Partner) -->
    <div v-if="currentSection === 'payments'">
      <v-row>
        <v-col cols="12" md="8">
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
              <span class="section-title primary--text">Payout Method</span>
              <v-btn
                v-if="!bankConnected"
                color="primary"
                prepend-icon="mdi-wallet-plus"
                href="/connect-bank-account-training"
              >
                Connect Payout Method
              </v-btn>
              <v-chip v-else color="success" prepend-icon="mdi-check-circle">
                Connected
              </v-chip>
            </v-card-title>
            <v-card-text class="pa-8">
              <!-- Payout Method Not Connected -->
              <div v-if="!bankConnected" class="text-center py-8">
                <v-icon size="80" color="grey-lighten-1" class="mb-4">mdi-wallet-outline</v-icon>
                <h3 class="text-h6 mb-2">Connect Your Payout Method</h3>
                <p class="text-body-2 text-grey mb-6">
                  Connect your bank account via Stripe to receive weekly commission payments ($2/hour per trained caregiver).<br>
                  Your payment information is securely encrypted and never shared.
                </p>
                <v-btn
                  color="primary"
                  size="large"
                  prepend-icon="mdi-bank"
                  href="/connect-bank-account-training"
                  elevation="2"
                  class="text-none font-weight-bold"
                >
                  Connect Bank Account
                </v-btn>
              </div>

              <!-- Payout Method Connected -->
              <v-row v-else>
                <v-col cols="12">
                  <div class="bank-account-card-stripe">
                    <div class="d-flex align-center mb-4">
                      <v-icon size="48" color="primary" class="mr-4">mdi-wallet</v-icon>
                      <div class="flex-grow-1">
                        <div class="text-h6 font-weight-bold">Payout Method Connected</div>
                        <div class="text-body-2 text-grey">Stripe Connect • Verified</div>
                      </div>
                      <v-chip color="success" prepend-icon="mdi-check-circle" size="small">
                        Active
                      </v-chip>
                    </div>

                    <v-divider class="my-4"></v-divider>

                    <div class="d-flex justify-space-between align-center mb-3">
                      <span class="text-body-2 text-grey">Payout Method:</span>
                      <span class="font-weight-medium">{{ payoutMethodName || 'Bank Transfer (ACH)' }}</span>
                    </div>

                    <div class="d-flex justify-space-between align-center mb-3">
                      <span class="text-body-2 text-grey">Commission Rate:</span>
                      <span class="font-weight-medium">$2.00 per hour trained</span>
                    </div>

                    <div class="d-flex justify-space-between align-center mb-3">
                      <span class="text-body-2 text-grey">Payout Schedule:</span>
                      <span class="font-weight-medium">Weekly (Every Friday)</span>
                    </div>

                    <div class="d-flex justify-space-between align-center mb-3">
                      <span class="text-body-2 text-grey">Next Payout:</span>
                      <span class="font-weight-bold primary--text">{{ nextPayoutDate }}</span>
                    </div>

                    <v-divider class="my-4"></v-divider>

                    <v-alert type="success" variant="tonal" density="compact">
                      <div class="d-flex align-center">
                        <v-icon class="mr-2">mdi-shield-check</v-icon>
                        <span class="text-body-2">
                          Your bank account is securely connected via Stripe. Commission payments are transferred automatically every Friday.
                        </span>
                      </div>
                    </v-alert>

                    <div class="mt-4 d-flex flex-wrap gap-2">
                      <v-btn
                        color="primary"
                        variant="flat"
                        size="small"
                        prepend-icon="mdi-swap-horizontal"
                        href="/connect-bank-account-training"
                      >
                        Change Payout Method
                      </v-btn>
                      <v-btn
                        color="error"
                        variant="outlined"
                        size="small"
                        prepend-icon="mdi-link-off"
                        @click="showRemovePayoutDialog = true"
                      >
                        Remove Payout Method
                      </v-btn>
                    </div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-8">
              <span class="section-title primary--text">Commission Summary</span>
            </v-card-title>
            <v-card-text class="pa-8">
              <div class="summary-item" style="margin-bottom: 24px;">
                <span class="summary-label">Total Earned</span>
                <span class="summary-value success--text">${{ totalRevenue }}</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">This Month</span>
                <span class="summary-value primary--text">${{ monthlyRevenue }}</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">Last Payout</span>
                <span class="summary-value">${{ lastPayoutAmount }}</span>
              </div>
              <v-divider class="my-4" />
              <div class="summary-item">
                <span class="summary-label">Next Payout</span>
                <span class="summary-value font-weight-bold">{{ nextPayoutDate }}</span>
              </div>
            </v-card-text>
          </v-card>

          <!-- How Commission Works Card -->
          <v-card elevation="0">
            <v-card-title class="card-header pa-6">
              <span class="text-subtitle-1 font-weight-bold text-grey-darken-2">How Commission Works</span>
            </v-card-title>
            <v-card-text class="pa-6">
              <div class="d-flex align-center mb-3">
                <v-icon color="primary" class="mr-3">mdi-school</v-icon>
                <span class="text-body-2">Train caregivers who work with CAS Private Care</span>
              </div>
              <div class="d-flex align-center mb-3">
                <v-icon color="primary" class="mr-3">mdi-clock-outline</v-icon>
                <span class="text-body-2">Earn $2 for every hour worked by trained caregivers</span>
              </div>
              <div class="d-flex align-center mb-3">
                <v-icon color="primary" class="mr-3">mdi-calendar-check</v-icon>
                <span class="text-body-2">Weekly payouts every Friday</span>
              </div>
              <div class="d-flex align-center">
                <v-icon color="primary" class="mr-3">mdi-bank-transfer</v-icon>
                <span class="text-body-2">Direct deposit to your bank</span>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Add Trainee Dialog -->
    <v-dialog v-model="addTraineeDialog" max-width="600">
      <v-card>
        <v-card-title class="pa-6" style="background: #616161; color: white;">
          <span class="section-title" style="color: white;">Add New Trainee</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12">
              <v-text-field v-model="traineeForm.name" label="Full Name" variant="outlined" />
            </v-col>
            <v-col cols="12">
              <v-text-field v-model="traineeForm.email" label="Email" variant="outlined" type="email" />
            </v-col>
            <v-col cols="12" md="6">
              <v-select v-model="traineeForm.course" :items="courseNames" label="Course" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-select v-model="traineeForm.status" :items="['Enrolled', 'In Progress', 'Completed']" label="Status" variant="outlined" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="addTraineeDialog = false">Cancel</v-btn>
          <v-btn color="grey-darken-2" @click="saveTrainee">Add Trainee</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Profile Picture Success Modal -->
    <v-dialog 
      v-model="showAvatarSuccessModal" 
      max-width="400"
      persistent
    >
      <v-card class="avatar-success-modal text-center pa-6">
        <div class="success-animation-container">
          <div class="success-checkmark">
            <div class="check-icon">
              <span class="icon-line line-tip"></span>
              <span class="icon-line line-long"></span>
              <div class="icon-circle"></div>
              <div class="icon-fix"></div>
            </div>
          </div>
        </div>
        
        <div class="success-modal-title">Success!</div>
        
        <div class="success-modal-message">
          Your profile picture has been updated successfully.
        </div>
        
        <v-card-actions class="justify-center pt-6 pb-2">
          <v-btn 
            color="grey-darken-2" 
            variant="flat" 
            size="large"
            min-width="150"
            @click="closeAvatarSuccessModal"
          >
            Done
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Remove Payout Method Dialog (same as MarketingDashboard) -->
    <v-dialog v-model="showRemovePayoutDialog" max-width="450" persistent>
      <v-card>
        <v-card-title class="pa-4 bg-error text-white">
          <v-icon class="mr-2">mdi-delete-alert</v-icon>
          Remove Payout Method
        </v-card-title>
        <v-card-text class="pa-4">
          <p class="text-body-1 mb-4">Are you sure you want to remove your connected payout method?</p>
          <v-alert type="warning" variant="tonal" density="compact" class="mb-4">
            <div class="text-body-2">
              <strong>Warning:</strong> You will not be able to receive commission payments until you connect a new payout method.
            </div>
          </v-alert>
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="showRemovePayoutDialog = false">Cancel</v-btn>
          <v-btn color="error" variant="flat" :loading="removingPayoutMethod" @click="removePayoutMethod">
            <v-icon left>mdi-delete</v-icon>
            Remove Method
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </dashboard-template>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import DashboardTemplate from './DashboardTemplate.vue';
import StatCard from './shared/StatCard.vue';
import NotificationToast from './shared/NotificationToast.vue';
import NotificationCenter from './shared/NotificationCenter.vue';
import EmailVerificationModal from './EmailVerificationModal.vue';
import { useEmailVerification } from '../composables/useEmailVerification';
import LoadingOverlay from './LoadingOverlay.vue';
import { useNotification } from '../composables/useNotification';
import { useNYLocationData } from '../composables/useNYLocationData.js';

const { notification, success, error, warning, info } = useNotification();
const { counties, getCitiesForCounty, loadNYLocationData } = useNYLocationData();

// Email verification
const { isVerified: isEmailVerified, userEmail, checkVerificationStatus } = useEmailVerification();
const handleEmailVerified = () => {
  checkVerificationStatus();
  window.location.reload();
};

// Global loading state
const isPageLoading = ref(true);
const loadingContext = ref('dashboard');
const loadingProgress = ref(0);

// Error Modal State
const showErrorModal = ref(false);
const errorMessages = ref([]);

const currentSection = ref('dashboard');
const userEmailVerified = ref(false);
const addPaymentDialog = ref(false);
const currentTime = ref(new Date());

// Payment settings
const trainingPayoutFrequency = ref('Weekly');
const trainingPayoutMethod = ref('Bank Transfer');
const trainingApplicationStatus = ref('pending'); // 'pending' or 'approved'

const trainingPaymentMethods = ref([
  { id: 1, type: 'visa', icon: 'mdi-credit-card', last4: '4242', holder: 'Training Center', expiry: '12/25', isDefault: true, brandName: 'VISA' },
  { id: 2, type: 'mastercard', icon: 'mdi-credit-card', last4: '8888', holder: 'Training Center', expiry: '06/26', isDefault: false, brandName: 'Mastercard' },
]);

const todayFormatted = computed(() => {
  return currentTime.value.toLocaleDateString('en-US', { 
    weekday: 'short', 
    month: 'short', 
    day: 'numeric' 
  });
});

const nextPayoutDate = computed(() => {
  const today = currentTime.value;
  const daysUntilFriday = (5 - today.getDay() + 7) % 7 || 7;
  const nextFriday = new Date(today);
  nextFriday.setDate(today.getDate() + daysUntilFriday);
  return nextFriday.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric' 
  });
});

const coveringDateRange = computed(() => {
  const today = currentTime.value;
  const dayOfWeek = today.getDay();
  const daysSinceFriday = (dayOfWeek + 2) % 7;
  const startFriday = new Date(today);
  startFriday.setDate(today.getDate() - daysSinceFriday);
  const endThursday = new Date(startFriday);
  endThursday.setDate(startFriday.getDate() + 6);
  const formatDate = (date) => date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  return `${formatDate(startFriday)} - ${formatDate(endThursday)}`;
});

const addTraineeDialog = ref(false);
const addCourseDialog = ref(false);
const traineeForm = ref({ name: '', email: '', course: '', status: 'Enrolled' });
const paymentInfo = ref({ bankName: '', accountNumber: '', routingNumber: '', accountHolder: 'Training Center' });
const caregiverSearch = ref('');
const certificationFilter = ref('');
const boroughFilter = ref('');

const boroughs = ['Manhattan', 'Brooklyn', 'Queens', 'Bronx', 'Staten Island'];
const certificationOptions = ['Certified', 'In Progress', 'Expired'];

// ZIP Code to City/State lookup mapping for NY
const zipCodeMap = {
  '10001': 'Manhattan, NY', '10002': 'Manhattan, NY', '10003': 'Manhattan, NY', '10004': 'Manhattan, NY', '10005': 'Manhattan, NY', '10006': 'Manhattan, NY', '10007': 'Manhattan, NY', '10009': 'Manhattan, NY', '10010': 'Manhattan, NY', '10011': 'Manhattan, NY', '10012': 'Manhattan, NY', '10013': 'Manhattan, NY', '10014': 'Manhattan, NY', '10016': 'Manhattan, NY', '10017': 'Manhattan, NY', '10018': 'Manhattan, NY', '10019': 'Manhattan, NY', '10020': 'Manhattan, NY', '10021': 'Manhattan, NY', '10022': 'Manhattan, NY', '10023': 'Manhattan, NY', '10024': 'Manhattan, NY', '10025': 'Manhattan, NY', '10026': 'Manhattan, NY', '10027': 'Manhattan, NY', '10028': 'Manhattan, NY', '10029': 'Manhattan, NY', '10030': 'Manhattan, NY', '10031': 'Manhattan, NY', '10032': 'Manhattan, NY', '10033': 'Manhattan, NY', '10034': 'Manhattan, NY', '10035': 'Manhattan, NY', '10036': 'Manhattan, NY', '10037': 'Manhattan, NY', '10038': 'Manhattan, NY', '10039': 'Manhattan, NY', '10040': 'Manhattan, NY', '10044': 'Manhattan, NY', '10065': 'Manhattan, NY', '10069': 'Manhattan, NY', '10075': 'Manhattan, NY', '10128': 'Manhattan, NY', '10280': 'Manhattan, NY',
  '11201': 'Brooklyn, NY', '11203': 'Brooklyn, NY', '11204': 'Brooklyn, NY', '11205': 'Brooklyn, NY', '11206': 'Brooklyn, NY', '11207': 'Brooklyn, NY', '11208': 'Brooklyn, NY', '11209': 'Brooklyn, NY', '11210': 'Brooklyn, NY', '11211': 'Brooklyn, NY', '11212': 'Brooklyn, NY', '11213': 'Brooklyn, NY', '11214': 'Brooklyn, NY', '11215': 'Brooklyn, NY', '11216': 'Brooklyn, NY', '11217': 'Brooklyn, NY', '11218': 'Brooklyn, NY', '11219': 'Brooklyn, NY', '11220': 'Brooklyn, NY', '11221': 'Brooklyn, NY', '11222': 'Brooklyn, NY', '11223': 'Brooklyn, NY', '11224': 'Brooklyn, NY', '11225': 'Brooklyn, NY', '11226': 'Brooklyn, NY', '11228': 'Brooklyn, NY', '11229': 'Brooklyn, NY', '11230': 'Brooklyn, NY', '11231': 'Brooklyn, NY', '11232': 'Brooklyn, NY', '11233': 'Brooklyn, NY', '11234': 'Brooklyn, NY', '11235': 'Brooklyn, NY', '11236': 'Brooklyn, NY', '11237': 'Brooklyn, NY', '11238': 'Brooklyn, NY', '11239': 'Brooklyn, NY',
  '11354': 'Flushing, NY', '11355': 'Flushing, NY', '11356': 'Flushing, NY', '11357': 'Flushing, NY', '11358': 'Flushing, NY', '11360': 'Bayside, NY', '11361': 'Bayside, NY', '11362': 'Bayside, NY', '11363': 'Bayside, NY', '11364': 'Bayside, NY', '11365': 'Fresh Meadows, NY', '11366': 'Fresh Meadows, NY', '11367': 'Fresh Meadows, NY', '11368': 'Corona, NY', '11369': 'East Elmhurst, NY', '11370': 'Elmhurst, NY', '11371': 'Elmhurst, NY', '11372': 'Jackson Heights, NY', '11373': 'Jackson Heights, NY', '11374': 'Rego Park, NY', '11375': 'Forest Hills, NY', '11377': 'Woodside, NY', '11378': 'Maspeth, NY', '11379': 'Middle Village, NY', '11385': 'Ridgewood, NY',
  '10451': 'Bronx, NY', '10452': 'Bronx, NY', '10453': 'Bronx, NY', '10454': 'Bronx, NY', '10455': 'Bronx, NY', '10456': 'Bronx, NY', '10457': 'Bronx, NY', '10458': 'Bronx, NY', '10459': 'Bronx, NY', '10460': 'Bronx, NY', '10461': 'Bronx, NY', '10462': 'Bronx, NY', '10463': 'Bronx, NY', '10464': 'Bronx, NY', '10465': 'Bronx, NY', '10466': 'Bronx, NY', '10467': 'Bronx, NY', '10468': 'Bronx, NY', '10469': 'Bronx, NY', '10470': 'Bronx, NY', '10471': 'Bronx, NY', '10472': 'Bronx, NY', '10473': 'Bronx, NY', '10474': 'Bronx, NY', '10475': 'Bronx, NY',
  '10301': 'Staten Island, NY', '10302': 'Staten Island, NY', '10303': 'Staten Island, NY', '10304': 'Staten Island, NY', '10305': 'Staten Island, NY', '10306': 'Staten Island, NY', '10307': 'Staten Island, NY', '10308': 'Staten Island, NY', '10309': 'Staten Island, NY', '10310': 'Staten Island, NY', '10311': 'Staten Island, NY', '10312': 'Staten Island, NY', '10314': 'Staten Island, NY',
  '11001': 'Long Island City, NY', '11004': 'Long Island City, NY', '11005': 'Long Island City, NY', '11040': 'Long Island City, NY', '11101': 'Long Island City, NY', '11102': 'Long Island City, NY', '11103': 'Long Island City, NY', '11104': 'Long Island City, NY', '11105': 'Long Island City, NY', '11106': 'Long Island City, NY', '11109': 'Long Island City, NY',
  '11501': 'Hempstead, NY', '11530': 'Hempstead, NY', '11550': 'Hempstead, NY', '11552': 'Hempstead, NY', '11553': 'Hempstead, NY', '11554': 'Hempstead, NY', '11555': 'Hempstead, NY', '11556': 'Hempstead, NY', '11557': 'Hempstead, NY', '11558': 'Hempstead, NY', '11559': 'Hempstead, NY', '11560': 'Hempstead, NY', '11561': 'Hempstead, NY', '11563': 'Hempstead, NY', '11565': 'Hempstead, NY', '11566': 'Hempstead, NY', '11568': 'Hempstead, NY', '11569': 'Hempstead, NY', '11570': 'Hempstead, NY', '11571': 'Hempstead, NY', '11572': 'Hempstead, NY', '11575': 'Hempstead, NY', '11576': 'Hempstead, NY', '11577': 'Hempstead, NY', '11579': 'Hempstead, NY', '11580': 'Hempstead, NY', '11581': 'Hempstead, NY', '11582': 'Hempstead, NY', '11590': 'Hempstead, NY', '11596': 'Hempstead, NY', '11598': 'Hempstead, NY', '11599': 'Hempstead, NY',
  '10501': 'White Plains, NY', '10502': 'White Plains, NY', '10504': 'White Plains, NY', '10505': 'White Plains, NY', '10506': 'White Plains, NY', '10507': 'White Plains, NY', '10510': 'White Plains, NY', '10514': 'White Plains, NY', '10520': 'White Plains, NY', '10522': 'White Plains, NY', '10523': 'White Plains, NY', '10524': 'White Plains, NY', '10526': 'White Plains, NY', '10527': 'White Plains, NY', '10528': 'White Plains, NY', '10530': 'White Plains, NY', '10532': 'White Plains, NY', '10533': 'White Plains, NY', '10538': 'White Plains, NY', '10543': 'White Plains, NY', '10546': 'White Plains, NY', '10547': 'White Plains, NY', '10548': 'White Plains, NY', '10549': 'White Plains, NY', '10550': 'White Plains, NY', '10552': 'White Plains, NY', '10553': 'White Plains, NY', '10560': 'White Plains, NY', '10562': 'White Plains, NY', '10566': 'White Plains, NY', '10567': 'White Plains, NY', '10570': 'White Plains, NY', '10573': 'White Plains, NY', '10576': 'White Plains, NY', '10577': 'White Plains, NY', '10578': 'White Plains, NY', '10579': 'White Plains, NY', '10580': 'White Plains, NY', '10583': 'White Plains, NY', '10587': 'White Plains, NY', '10588': 'White Plains, NY', '10589': 'White Plains, NY', '10590': 'White Plains, NY', '10591': 'White Plains, NY', '10594': 'White Plains, NY', '10595': 'White Plains, NY', '10596': 'White Plains, NY', '10597': 'White Plains, NY', '10598': 'White Plains, NY', '10601': 'White Plains, NY', '10602': 'White Plains, NY', '10603': 'White Plains, NY', '10604': 'White Plains, NY', '10605': 'White Plains, NY', '10606': 'White Plains, NY', '10607': 'White Plains, NY',
  '10701': 'Yonkers, NY', '10703': 'Yonkers, NY', '10704': 'Yonkers, NY', '10705': 'Yonkers, NY', '10706': 'Yonkers, NY', '10707': 'Yonkers, NY', '10708': 'Yonkers, NY', '10709': 'Yonkers, NY', '10710': 'Yonkers, NY'
};

const profileZipLocation = ref('');

/**
 * NY ZIP Code Validation Helper
 * Valid NY ZIPs: 10xxx-14xxx range OR special cases (00501, 00544, 06390)
 */
const isValidNYZip = (zip) => {
  if (!zip) return false;
  const nyZipRegex = /^(00501|00544|06390|1[0-4]\d{3})(-\d{4})?$/;
  return nyZipRegex.test(zip);
};

/**
 * Get NY region based on ZIP prefix for fallback
 */
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

const lookupProfileZipCode = async () => {
  const zip = profile.value.zip;
  
  if (!zip || zip.length !== 5 || !/^\d{5}$/.test(zip)) {
    profileZipLocation.value = '';
    return;
  }

  // Client-side NY ZIP validation FIRST
  if (!isValidNYZip(zip)) {
    profileZipLocation.value = 'Not a NY ZIP (must be 10xxx-14xxx)';
    return;
  }

  // Try API lookup (supports all NY ZIP codes)
  try {
    profileZipLocation.value = 'Looking up location…';
    const response = await fetch(`/api/zipcode-lookup/${zip}`);
    
    if (response.ok) {
      const data = await response.json();
      if (data.success && data.location) {
        profileZipLocation.value = data.location;
        return;
      }
    }
    
    // Fallback to region for valid NY ZIPs
    profileZipLocation.value = zipCodeMap[zip] || getNYRegionFromZip(zip) || 'New York, NY';
  } catch (error) {
    console.error('Profile ZIP code lookup error:', error);
    // Fallback to static map or region
    profileZipLocation.value = zipCodeMap[zip] || getNYRegionFromZip(zip) || 'New York, NY';
  }
};

const profile = ref({
  firstName: 'Training',
  lastName: 'Center',
  email: 'training@casprivatecare.com',
  phone: '(212) 555-0160',
  address: '',
  county: '',
  city: '',
  zip: '',
  birthdate: '',
  department: 'Training & Certification',
  role: 'Training Director',
  created_at: ''
});

const age = computed(() => {
  if (!profile.value.birthdate) return '';
  const today = new Date();
  const birthDate = new Date(profile.value.birthdate);
  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }
  return age;
});

// Include current profile city so saved/API values always display (permanent fix for city not showing after load).
const nyCities = computed(() => {
  if (!profile.value.county) {
    return ['Select County First'];
  }
  const list = getCitiesForCounty(profile.value.county) || [];
  const current = profile.value.city?.trim();
  if (!current) return list;
  const inList = list.some((c) => String(c).trim().toLowerCase() === current.toLowerCase());
  if (!inList) return [current, ...list];
  return list;
});

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Avatar upload
const avatarInput = ref(null);
const userAvatar = ref('');
const uploadingAvatar = ref(false);
const trainingUserId = ref(null);
const showAvatarSuccessModal = ref(false);

const closeAvatarSuccessModal = () => {
  showAvatarSuccessModal.value = false;
};

const userName = computed(() => {
  if (profile.value.firstName && profile.value.lastName) {
    return `${profile.value.firstName} ${profile.value.lastName}`;
  }
  return 'Training Center';
});

const userInitials = computed(() => {
  if (profile.value.firstName && profile.value.lastName) {
    return `${profile.value.firstName[0]}${profile.value.lastName[0]}`.toUpperCase();
  }
  return 'TC';
});

const memberSince = computed(() => {
  if (profile.value.created_at) {
    try {
      const date = new Date(profile.value.created_at);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    } catch {
      return 'N/A';
    }
  }
  return 'N/A';
});

const triggerAvatarUpload = () => {
  avatarInput.value?.click();
};

const uploadAvatar = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
  if (!validTypes.includes(file.type)) {
    alert('Please select a valid image file (JPEG, PNG, or GIF)');
    return;
  }
  
  if (file.size > 2 * 1024 * 1024) {
    alert('Image size must be less than 2MB');
    return;
  }
  
  if (!trainingUserId.value) {
    alert('User ID not available. Please refresh the page.');
    return;
  }
  
  uploadingAvatar.value = true;
  
  try {
    const formData = new FormData();
    formData.append('avatar', file);
    
    const response = await fetch(`/api/user/${trainingUserId.value}/avatar`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: formData
    });
    
    const data = await response.json();
    
    if (response.ok && data.success) {
      userAvatar.value = data.avatar;
      showAvatarSuccessModal.value = true;
    } else {
      alert('Error: ' + (data.error || 'Failed to upload avatar'));
    }
  } catch (error) {
    alert('Error uploading avatar. Please try again.');
  } finally {
    uploadingAvatar.value = false;
    if (avatarInput.value) {
      avatarInput.value.value = '';
    }
  }
};

const navItems = ref([
  { icon: 'mdi-view-dashboard', title: 'Dashboard', value: 'dashboard' },
  { icon: 'mdi-bell', title: 'Notifications', value: 'notifications' },
  { icon: 'mdi-account-clock', title: 'Pending Requests', value: 'pending-requests', category: 'MANAGEMENT', badge: true },
  { icon: 'mdi-account-group', title: 'Caregivers', value: 'contractors', category: 'MANAGEMENT' },
  { icon: 'mdi-chart-line', title: 'Analytics', value: 'analytics', category: 'REPORTS' },
  { icon: 'mdi-credit-card', title: 'Payments', value: 'payments', category: 'FINANCIAL' },
  { icon: 'mdi-account-circle', title: 'Profile (1099 Contractors)', value: 'profile', category: 'ACCOUNT' },
]);

const stats = ref([
  { title: 'Total Caregivers', value: '0', icon: 'mdi-school', color: 'grey-darken-2', change: '+0 this month', changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
  { title: 'Total Revenue', value: '$0', icon: 'mdi-currency-usd', color: 'grey-darken-2', change: '+$0 this month', changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
]);

const accountBalance = ref(0);
const bankConnected = ref(false);
const payoutMethodName = ref(''); // e.g. "Bank Transfer (ACH)" or bank name from profile
const showRemovePayoutDialog = ref(false);
const removingPayoutMethod = ref(false);
const weeklySummary = ref({
  deployed_caregivers: 0,
  target: 10,
  previous_payout: 0,
  previous_payout_date: null
});

const trainedCaregivers = ref([]);
const pendingCaregivers = ref([]);

const courses = ref([
  { id: 1, name: 'Basic Caregiver Training', duration: '40 hours', price: '299', enrolled: 0, completed: 0 },
  { id: 2, name: 'Advanced Care Techniques', duration: '60 hours', price: '499', enrolled: 0, completed: 0 },
  { id: 3, name: 'Specialized Medical Care', duration: '80 hours', price: '699', enrolled: 0, completed: 0 },
]);

const loadPendingCaregivers = async () => {
  try {
    const response = await fetch('/api/training/pending-caregivers', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });
    
    if (response.ok) {
      const data = await response.json();
      pendingCaregivers.value = data.pendingCaregivers || [];
      
      // Update badge count in navigation
      const pendingItem = navItems.value.find(item => item.value === 'pending-requests');
      if (pendingItem) {
        pendingItem.badge = pendingCaregivers.value.length > 0;
      }
    }
  } catch (error) {
  }
};

/** Fetch fresh CSRF token and optionally update meta tag (avoids mismatch after session refresh). */
const getCsrfToken = async () => {
  const meta = document.querySelector('meta[name="csrf-token"]');
  const fromMeta = meta?.getAttribute('content') || '';
  try {
    const r = await fetch('/csrf-token', { credentials: 'same-origin' });
    if (!r.ok) return fromMeta;
    const data = await r.json();
    const token = data?.token || '';
    if (token && meta) meta.setAttribute('content', token);
    return token || fromMeta;
  } catch (_) {
    return fromMeta;
  }
};

const approveCaregiverRequest = async (caregiver) => {
  const id = caregiver?.id;
  if (id == null || id === '') {
    error('Invalid request. Please refresh and try again.', 'Error');
    return;
  }
  try {
    const csrfToken = await getCsrfToken();
    const response = await fetch(`/api/training/caregivers/${id}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      credentials: 'same-origin'
    });

    let errorMessage = 'Failed to approve caregiver';
    if (!response.ok) {
      try {
        const errorData = await response.json();
        errorMessage = errorData.message || errorData.error || errorMessage;
      } catch (_) {
        errorMessage = response.status === 419 ? 'Session expired. Please refresh and log in again.' : errorMessage;
      }
      error(errorMessage, 'Error');
      return;
    }
    const data = await response.json();
    success('Caregiver approved successfully!', 'Approved');
    await loadPendingCaregivers();
    await loadTrainingStats();
  } catch (err) {
    error(err?.message || 'Failed to approve caregiver', 'Error');
  }
};

const rejectCaregiverRequest = async (caregiver) => {
  if (!confirm(`Are you sure you want to reject ${caregiver.name}'s request?`)) {
    return;
  }

  try {
    const csrfToken = await getCsrfToken();
    const response = await fetch(`/api/training/caregivers/${caregiver.id}/reject`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      credentials: 'same-origin'
    });
    
    if (response.ok) {
      const data = await response.json();
      warning('Caregiver request rejected', 'Rejected');
      await loadPendingCaregivers();
    } else {
      const errorData = await response.json();
      error('Failed to reject caregiver', 'Error');
    }
  } catch (err) {
    error('Failed to reject caregiver', 'Error');
  }
};

const loadTrainingStats = async () => {
  try {
    const response = await fetch(`/api/training/stats?user_id=${trainingUserId.value}`);
    const data = await response.json();
    const totalRev = typeof data.total_revenue === 'string' ? data.total_revenue : (data.total_revenue ?? 0).toFixed(2);
    const monthlyRev = typeof data.monthly_revenue === 'string' ? data.monthly_revenue : (data.monthly_revenue ?? 0).toFixed(2);

    // Update stats
    stats.value = [
      { title: 'Total Caregivers', value: data.total_caregivers?.toString() || '0', icon: 'mdi-school', color: 'grey-darken-2', change: `+${data.weekly_summary?.deployed_caregivers || 0} this week`, changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
      { title: 'Total Revenue', value: '$' + totalRev, icon: 'mdi-currency-usd', color: 'grey-darken-2', change: '+$' + monthlyRev + ' this month', changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
    ];

    // Commission summary: use real API data so new accounts show $0.00, not placeholder amounts
    monthlyRevenue.value = monthlyRev;
    lastPayoutAmount.value = (data.weekly_summary?.previous_payout_date && data.weekly_summary?.previous_payout) ? data.weekly_summary.previous_payout : '0.00';

    // Update caregivers list
    trainedCaregivers.value = data.caregivers || [];

    // Update account balance (ensure number for .toFixed in template)
    accountBalance.value = Number(data.account_balance) || 0;

    // Update weekly summary (ensure previous_payout is number when present)
    const ws = data.weekly_summary || { deployed_caregivers: 0, target: 10, previous_payout: 0, previous_payout_date: null };
    if (typeof ws.previous_payout === 'string') ws.previous_payout = Number(ws.previous_payout) || 0;
    weeklySummary.value = ws;
    
    // Update courses with enrolled counts
    if (trainedCaregivers.value.length > 0) {
      courses.value = courses.value.map(course => ({
        ...course,
        enrolled: trainedCaregivers.value.filter(c => c.course === course.name).length,
        completed: trainedCaregivers.value.filter(c => c.course === course.name && c.certification === 'Certified').length
      }));
    }
  } catch (error) {
  }
};

const caregiverHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Course', key: 'course' },
  { title: 'Certification', key: 'certification' },
  { title: 'Revenue Generated', key: 'earnings' },
  { title: 'Status', key: 'status' },
];

const traineeHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Course', key: 'course' },
  { title: 'Certification', key: 'certification' },
  { title: 'Revenue Generated', key: 'earnings' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const contractorHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Contact Number', key: 'phone' },
  { title: 'Borough', key: 'borough' },
  { title: 'Certification', key: 'certification' },
  { title: 'Status', key: 'status' },
  { title: 'Revenue Generated', key: 'earnings' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const pendingHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Experience', key: 'years_experience' },
  { title: 'Specializations', key: 'specializations' },
  { title: 'Requested', key: 'requested_at' },
  { title: 'Actions', key: 'actions', sortable: false, width: '220px', minWidth: '220px' },
];

const analyticsMetrics = ref([
  { title: 'Training Success Rate', value: '89%', icon: 'mdi-school', color: 'grey-darken-2', change: '+3%', changeColor: 'success--text' },
  { title: 'Avg Course Duration', value: '55h', icon: 'mdi-clock', color: 'grey', change: '-2h', changeColor: 'success--text' },
  { title: 'Revenue per Trainee', value: '$63', icon: 'mdi-currency-usd', color: 'grey-lighten-1', change: '+$8', changeColor: 'success--text' },
  { title: 'Certification Rate', value: '71%', icon: 'mdi-certificate', color: 'grey-darken-1', change: '+5%', changeColor: 'success--text' },
]);

const revenueHeaders = [
  { title: 'Date', key: 'date' },
  { title: 'Source', key: 'source' },
  { title: 'Amount', key: 'amount' },
  { title: 'Status', key: 'status' },
];

const revenueHistory = ref([
  { id: 1, date: '2024-12-15', source: 'Maria Santos - Commission', amount: '320.00', status: 'Paid' },
  { id: 2, date: '2024-12-10', source: 'John Smith - Commission', amount: '280.00', status: 'Paid' },
  { id: 3, date: '2024-12-05', source: 'Robert Chen - Commission', amount: '450.00', status: 'Paid' },
  { id: 4, date: '2024-12-31', source: 'Monthly Commission', amount: '850.00', status: 'Pending' },
]);

const courseNames = computed(() => courses.value.map(c => c.name));

const totalRevenue = computed(() => {
  return trainedCaregivers.value.reduce((sum, caregiver) => sum + parseFloat(caregiver.earnings), 0).toFixed(2);
});

const monthlyRevenue = ref('0.00');
const lastPayoutAmount = ref('0.00');
const activeTrainees = computed(() => trainedCaregivers.value.filter(c => c.status === 'Active').length);

const filteredCaregivers = computed(() => {
  let filtered = trainedCaregivers.value;
  
  if (caregiverSearch.value) {
    const search = caregiverSearch.value.toLowerCase();
    filtered = filtered.filter(caregiver => 
      caregiver.name.toLowerCase().includes(search) ||
      caregiver.email.toLowerCase().includes(search) ||
      caregiver.borough.toLowerCase().includes(search)
    );
  }
  
  if (certificationFilter.value) {
    filtered = filtered.filter(caregiver => caregiver.certification === certificationFilter.value);
  }
  
  if (boroughFilter.value) {
    filtered = filtered.filter(caregiver => caregiver.borough === boroughFilter.value);
  }
  
  return filtered;
});

const revenueProgress = computed(() => Math.min((parseFloat(totalRevenue.value) / 5000) * 100, 100));
const monthlyProgress = computed(() => Math.min((parseFloat(monthlyRevenue.value) / 1000) * 100, 100));
const traineesProgress = computed(() => Math.min((activeTrainees.value / 50) * 100, 100));

const getStatusColor = (status) => {
  const colors = { 'Ongoing Contract': 'success', 'No Contract': 'error' };
  return colors[status] || 'grey';
};

const getCertificationColor = (certification) => {
  const colors = { 'Certified': 'success', 'In Progress': 'warning', 'Expired': 'error' };
  return colors[certification] || 'grey';
};

const getPaymentStatusColor = (status) => {
  const colors = { 'Paid': 'success', 'Pending': 'warning', 'Failed': 'error' };
  return colors[status] || 'grey';
};

const loadProfile = async () => {
  try {
    const response = await fetch('/api/profile?user_type=training');
    const data = await response.json();
    if (data.user) {
      const nameParts = (data.user.name || '').split(' ');
      
      // Set email verification status
      userEmailVerified.value = data.user.email_verified_at !== null && data.user.email_verified_at !== undefined;
      
      profile.value = {
        firstName: nameParts[0] || '',
        lastName: nameParts.slice(1).join(' ') || '',
        email: data.user.email || '',
        phone: data.user.phone || '',
        address: data.user.address || '',
        county: data.user.borough || '',
        city: data.user.city || '',
        zip: data.user.zip_code || '',
        birthdate: data.user.date_of_birth || '',
        department: data.user.department || 'Training & Certification',
        role: data.user.role || 'Training Director',
        created_at: data.user.created_at || '',
        stripe_connect_id: data.user.stripe_connect_id || null
      };
      bankConnected.value = !!(data.user.stripe_connect_id);
      payoutMethodName.value = (data.user.bank_name && data.user.bank_name.trim()) ? data.user.bank_name.trim() : 'Bank Transfer (ACH)';
      trainingUserId.value = data.user.id;
      if (data.user.avatar) {
        userAvatar.value = `/storage/${data.user.avatar}`;
      }
      // Load training stats after we have the user ID
      loadTrainingStats();
    }
  } catch (error) {
  }
};

const saveProfile = async () => {
  try {
    // Permanent: explicitly send city/county/borough/state/address/zip so they always persist.
    const payload = {
      ...profile.value,
      borough: profile.value.county,
      county: profile.value.county,
      city: profile.value.city ?? '',
      state: profile.value.state ?? '',
      address: profile.value.address ?? '',
      zip: profile.value.zip ?? '',
      zip_code: profile.value.zip ?? profile.value.zip_code ?? ''
    };
    
    // Ensure phone is a string if present, or remove it if empty
    if (payload.phone) {
      payload.phone = String(payload.phone);
    } else {
      delete payload.phone; // Remove if empty to avoid null issues
    }
    
    const response = await fetch('/api/profile/update', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify(payload)
    });
    
    if (response.ok) {
      success('Profile changes saved successfully!');
      // Reload profile data to ensure UI is updated
      await loadProfile();
    } else {
      let errorMessage = 'Failed to save profile';
      try {
        const data = await response.json();
        // Handle Laravel validation errors (data.error can be an object)
        if (data.error && typeof data.error === 'object') {
          // Format validation errors
          const errors = [];
          for (const [field, messages] of Object.entries(data.error)) {
            if (Array.isArray(messages)) {
              errors.push(...messages);
            } else {
              errors.push(messages);
            }
          }
          errorMessage = errors.join(', ') || errorMessage;
        } else {
          errorMessage = data.error || data.message || errorMessage;
        }
      } catch (e) {
        // If response is not JSON, use status text
        errorMessage = response.statusText || errorMessage;
      }
      error('Error: ' + errorMessage);
    }
  } catch (err) {
    const errorMessage = err?.message || err?.toString() || 'Unknown error occurred';
    error('Error saving profile: ' + errorMessage);
  }
};

const logout = async () => {
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

const removePayoutMethod = async () => {
  removingPayoutMethod.value = true;
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  try {
    const response = await fetch('/api/training/payout-method', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-XSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ _token: csrfToken }),
      credentials: 'include'
    });
    const data = await response.json();
    if (response.ok && data.success) {
      showRemovePayoutDialog.value = false;
      success('Payout method removed successfully', 'Payout Method');
      await loadProfile();
    } else {
      error(data.message || 'Failed to remove payout method');
    }
  } catch (err) {
    error('Failed to remove payout method. Please try again.');
  } finally {
    removingPayoutMethod.value = false;
    showRemovePayoutDialog.value = false;
  }
};

const loadPaymentMethods = async () => {
  try {
    const response = await fetch('/api/payment-methods');
    const data = await response.json();
    
    if (data.success && data.payment_methods) {
      // Filter cards from payment methods
      const cards = data.payment_methods.filter(pm => pm.type !== 'bank_account');
      if (cards.length > 0) {
        trainingPaymentMethods.value = cards;
      }
      
      // Find bank account if exists
      const bankAccount = data.payment_methods.find(pm => pm.type === 'bank_account');
      if (bankAccount) {
      }
    }
  } catch (error) {
  }
};

const viewW9Form = () => {
  // Open W9 form PDF in new tab
  window.open('/pdfs/form-w-9.pdf', '_blank');
};

const handleRequestPayout = () => {
  success('Payout request sent', 'Your payout request has been submitted and will be processed shortly.');
};

const checkTrainingApplicationStatus = async () => {
  try {
    const response = await fetch('/api/training/application-status');
    const data = await response.json();
    if (data.status) {
      trainingApplicationStatus.value = data.status.toLowerCase();
    } else {
      trainingApplicationStatus.value = 'pending';
    }
  } catch (error) {
    trainingApplicationStatus.value = 'pending';
  }
};

const viewTrainee = (trainee) => {
  info(`Viewing details for ${trainee.name}`, 'Trainee Details');
};

const manageCertification = (trainee) => {
  info(`Managing certification for ${trainee.name}`, 'Certification Management');
};

const approveCaregiver = (caregiver) => {
  success(`${caregiver.name} has been approved!`, 'Caregiver Approved');
};

const rejectCaregiver = (caregiver) => {
  error(`${caregiver.name} has been rejected`, 'Caregiver Rejected');
};

const saveTrainee = () => {
  if (!traineeForm.value.name || !traineeForm.value.email) {
    error('Please fill in all required fields', 'Missing Information');
    return;
  }
  
  const newId = Math.max(...trainedCaregivers.value.map(c => c.id)) + 1;
  trainedCaregivers.value.push({
    id: newId,
    ...traineeForm.value,
    certification: 'In Progress',
    earnings: '0.00',
    status: 'Training'
  });
  
  success(`${traineeForm.value.name} has been added to training!`, 'Trainee Added');
  traineeForm.value = { name: '', email: '', course: '', status: 'Enrolled' };
  addTraineeDialog.value = false;
};

const handleNotificationAction = (action) => {
};

const revenueChart = ref(null);
const certificationChart = ref(null);

const initCharts = () => {
  if (!window.Chart) {
    setTimeout(initCharts, 100);
    return;
  }
  
  if (revenueChart.value) {
    const ctx = revenueChart.value.getContext('2d');
    new window.Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Revenue ($)',
          data: [1200, 1800, 2200, 2850, 2400, 2850],
          borderColor: '#616161',
          backgroundColor: 'rgba(97, 97, 97, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { display: true, grid: { display: false } },
          y: { display: true, grid: { color: '#f5f5f5' } }
        }
      }
    });
  }
  
  if (certificationChart.value) {
    const ctx = certificationChart.value.getContext('2d');
    const certifiedCount = trainedCaregivers.value.filter(c => c.certification === 'Certified').length;
    const inProgressCount = trainedCaregivers.value.filter(c => c.certification === 'In Progress').length;
    
    new window.Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Certified', 'In Progress'],
        datasets: [{
          data: [certifiedCount, inProgressCount],
          backgroundColor: ['#616161', '#bdbdbd'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        cutout: '60%'
      }
    });
  }
};

// Permanent: only reset city when user changes county and current city is not valid for the new county (do not clear on profile load).
watch(() => profile.value.county, (newCounty) => {
  if (!newCounty) return;
  const citiesForCounty = getCitiesForCounty(newCounty) || [];
  const currentCity = profile.value.city?.trim();
  if (!currentCity) return;
  const cityValidForCounty = citiesForCounty.some((c) => String(c).trim().toLowerCase() === currentCity.toLowerCase());
  if (!cityValidForCounty) {
    profile.value.city = '';
  }
});

// Watch for section changes
watch(currentSection, (newVal) => {
  if (newVal === 'analytics') {
    setTimeout(initCharts, 300);
  }
  if (newVal === 'pending-requests') {
    loadPendingCaregivers();
  }
  if (newVal === 'payments') {
    loadProfile(); // Refresh so bank connection status is up to date after connect redirect
    loadPaymentMethods();
    checkTrainingApplicationStatus();
  }
});

onMounted(async () => {
  // Open section from URL (e.g. after bank connect redirect: ?section=payments&success=true)
  const params = typeof window !== 'undefined' && window.location?.search ? new URLSearchParams(window.location.search) : null;
  if (params?.get('section')) {
    const section = params.get('section');
    if (['dashboard', 'trainees', 'pending-requests', 'contractors', 'analytics', 'courses', 'notifications', 'profile', 'payments'].includes(section)) {
      currentSection.value = section;
    }
  }

  // Show loading overlay
  isPageLoading.value = true;
  loadingContext.value = 'dashboard';
  loadingProgress.value = 0;
  
  const loadingTasks = [
    { fn: loadNYLocationData, weight: 10 },
    { fn: loadProfile, weight: 30 },
    { fn: loadPendingCaregivers, weight: 30 },
    { fn: checkTrainingApplicationStatus, weight: 20 }
  ];
  
  const totalWeight = loadingTasks.reduce((sum, task) => sum + task.weight, 0);
  let completedWeight = 0;
  
  const promises = loadingTasks.map(async (task) => {
    try {
      await task.fn();
    } catch (err) {
      console.warn('Loading task failed:', err);
    } finally {
      completedWeight += task.weight;
      loadingProgress.value = Math.round((completedWeight / totalWeight) * 100);
    }
  });
  
  await Promise.allSettled(promises);
  loadingProgress.value = 100;

  // Hide overlay immediately
  isPageLoading.value = false;
  
  if (currentSection.value === 'analytics') {
    setTimeout(initCharts, 500);
  }
});
</script>

<style scoped>
.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.section-title-compact {
  font-size: 1.1rem;
  font-weight: 600;
  letter-spacing: -0.01em;
}

.card-header {
  background: #fafafa;
  border-bottom: 1px solid #f0f0f0;
}

.compact-card {
  border-radius: 16px !important;
  border: 1px solid #e0e0e0 !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}

.course-card {
  border-radius: 16px !important;
  border: 1px solid #e0e0e0 !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
  transition: transform 0.2s ease;
}

.course-card:hover {
  transform: translateY(-2px);
}

.compact-stat-card {
  border-radius: 12px !important;
  border: 1px solid #e0e0e0 !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
}

.stat-label {
  font-size: 0.75rem;
  color: #757575;
  font-weight: 500;
  text-transform: uppercase;
}

.stat-change {
  font-size: 0.7rem;
  font-weight: 600;
}

.course-title {
  font-size: 1rem;
  font-weight: 600;
  color: #424242;
}

.course-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.course-duration {
  font-size: 0.85rem;
  color: #757575;
}

.course-price {
  font-size: 1.25rem;
  font-weight: 700;
  color: #616161;
}

.course-stats {
  padding: 8px 0;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 0.8rem;
  color: #757575;
}

.stat-value {
  font-size: 0.8rem;
  font-weight: 600;
  color: #424242;
}

.earnings-summary {
  padding: 8px 0;
}

.earning-item {
  padding: 8px 0;
}

.earning-label {
  font-size: 0.85rem;
  color: #757575;
  font-weight: 500;
}

.earning-value {
  font-size: 1rem;
  font-weight: 700;
  color: #424242;
}

.earning-overview {
  text-align: center;
}

.earning-stat {
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
}

.earning-stat:last-child {
  border-bottom: none;
}

.earning-amount {
  font-size: 2rem;
  font-weight: 700;
  color: #616161;
}

.earning-desc {
  font-size: 0.85rem;
  color: #757575;
  margin-top: 4px;
}

.action-buttons {
  display: flex;
  gap: 6px;
  justify-content: center;
}

/* Pending requests: prevent Actions buttons from overlapping */
.pending-requests-table :deep(th:last-child),
.pending-requests-table :deep(td:last-child) {
  min-width: 220px;
  width: 220px;
  box-sizing: border-box;
}
.action-buttons--pending {
  flex-wrap: wrap;
  gap: 6px;
  justify-content: flex-start;
  min-width: 0;
}
.action-btn-pending {
  flex-shrink: 0;
  white-space: nowrap;
}

.action-btn-view,
.action-btn-edit,
.action-btn-approve,
.action-btn-reject {
  width: 44px !important;
  height: 44px !important;
  min-width: 44px !important;
  min-height: 44px !important;
  padding: 0 !important;
  border-radius: 10px !important;
  touch-action: manipulation;
  -webkit-tap-highlight-color: transparent;
}

.action-btn-view {
  background-color: #2196f3 !important;
  color: white !important;
}

.action-btn-edit {
  background-color: #ff9800 !important;
  color: white !important;
}

.action-btn-approve {
  background-color: #4caf50 !important;
  color: white !important;
}

.action-btn-reject {
  background-color: #f44336 !important;
  color: white !important;
}

.payment-card {
  background: linear-gradient(135deg, #1a1f36 0%, #2d3561 100%);
  border-radius: 20px;
  padding: 28px;
  color: white;
  min-height: 220px;
  position: relative;
  overflow: hidden;
  border: 1px solid #c5c5c5ff;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  transition: all 0.3s ease;
}

.payment-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
}

.payment-card::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -20%;
  width: 200px;
  height: 200px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
}

.payment-card.visa {
  background: linear-gradient(135deg, #1434CB 0%, #2952E8 100%);
}

.payment-card.mastercard {
  background: linear-gradient(135deg, #EB001B 0%, #F79E1B 100%);
}

.payment-card.amex {
  background: linear-gradient(135deg, #006FCF 0%, #00A3E0 100%);
}

.card-number {
  font-size: 1.5rem;
  font-weight: 500;
  letter-spacing: 4px;
  margin: 20px 0;
  font-family: 'Courier New', monospace;
}

.card-label {
  font-size: 0.65rem;
  opacity: 0.7;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 4px;
}

.card-value {
  font-size: 1rem;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.card-actions {
  display: flex;
  gap: 4px;
  align-items: center;
}

.card-chip {
  width: 50px;
  height: 40px;
  background: linear-gradient(135deg, #d4af37 0%, #f4e5a1 50%, #d4af37 100%);
  border-radius: 8px;
  position: relative;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
}

.card-chip::before {
  content: '';
  position: absolute;
  top: 8px;
  left: 8px;
  right: 8px;
  bottom: 8px;
  border: 1px solid rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}

.card-brand-logo {
  font-size: 1.25rem;
  font-weight: 700;
  letter-spacing: 1px;
  opacity: 0.9;
}

.bank-account-card {
  background: #f9fafb;
  border: 1px solid #c5c5c5ff;
  border-radius: 12px;
  padding: 20px;
}

.bank-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
}

.account-type {
  font-size: 0.875rem;
  color: #6b7280;
}

.account-number, .routing-number {
  font-size: 0.95rem;
  color: #4b5563;
  margin-top: 8px;
  font-weight: 500;
}

/* Payout Method card when connected (matches Marketing dashboard) */
.bank-account-card-stripe {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #e2e8f0;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.summary-label {
  font-size: 0.95rem;
  color: #6b7280;
}

.summary-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
}

.profile-stat {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 12px 0;
  font-size: 0.95rem;
}

.account-balance-card {
  border: 1px solid #a3a3a3ff !important;
}

.account-balance-card .text-caption {
  color: #374151 !important;
  font-size: 0.875rem !important;
}

.account-balance-card .text-caption .font-weight-bold {
  color: #1f2937 !important;
  font-weight: 600 !important;
}

.balance-amount {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 4px;
}

.summary-label-compact {
  font-size: 0.8rem;
  color: #6b7280;
}

.summary-value-compact {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1f2937;
}

.summary-item-compact {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.enhanced-card {
  border-radius: 20px !important;
  border: 1px solid #e5e7eb !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
  transition: all 0.3s ease !important;
}

.enhanced-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(97, 97, 97, 0.15) !important;
}

.readonly-field {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 16px 12px 8px 12px;
  background: #fafafa;
  position: relative;
  min-height: 56px;
}

.readonly-label {
  position: absolute;
  top: 4px;
  left: 12px;
  font-size: 0.75rem;
  color: #666;
  font-weight: 500;
}

.readonly-value {
  font-size: 1rem;
  color: #333;
  font-weight: 500;
  margin-top: 8px;
}

.enhanced-card-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-bottom: 1px solid #e2e8f0;
  border-radius: 20px 20px 0 0 !important;
}

/* Mobile Responsive Styles */
@media (max-width: 960px) {
  /* Cards mobile adjustments */
  .card-header {
    padding: 1rem !important;
  }

  .section-title {
    font-size: 1.25rem !important;
  }

  .section-title-compact {
    font-size: 1rem !important;
  }

  /* Summary items mobile */
  .summary-item,
  .summary-item-compact {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 0.25rem !important;
  }

  .summary-label,
  .summary-label-compact {
    margin-bottom: 0.25rem !important;
  }

  /* Compact buttons on mobile */
  .v-btn {
    font-size: 0.875rem !important;
    padding: 0.625rem 1rem !important;
  }
}

@media (max-width: 480px) {
  /* Cards very compact */
  .card-header {
    padding: 0.875rem !important;
  }

  .section-title {
    font-size: 1.125rem !important;
  }

  .summary-value,
  .summary-value-compact {
    font-size: 0.875rem !important;
  }

  .summary-label,
  .summary-label-compact {
    font-size: 0.75rem !important;
  }
}

/* Avatar Success Modal Styles */
.avatar-success-modal {
  border-radius: 16px !important;
  overflow: hidden;
  animation: modalSlideIn 0.3s ease-out;
}

.success-modal-title {
  font-size: 1.5rem !important;
  font-weight: 700 !important;
  color: #2e7d32 !important;
  text-align: center;
  margin-top: 16px;
}

.success-modal-message {
  font-size: 1rem !important;
  color: #616161 !important;
  text-align: center;
  padding: 8px 24px 0 24px;
}

.success-animation-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding-top: 20px;
}

.success-checkmark {
  width: 80px;
  height: 80px;
  position: relative;
}

.success-checkmark .check-icon {
  width: 80px;
  height: 80px;
  position: relative;
  border-radius: 50%;
  box-sizing: content-box;
  border: 4px solid #4CAF50;
}

.success-checkmark .check-icon::before {
  top: 3px;
  left: -2px;
  width: 30px;
  transform-origin: 100% 50%;
  border-radius: 100px 0 0 100px;
}

.success-checkmark .check-icon::after {
  top: 0;
  left: 30px;
  width: 60px;
  transform-origin: 0 50%;
  border-radius: 0 100px 100px 0;
  animation: rotate-circle 4.25s ease-in;
}

.success-checkmark .check-icon::before,
.success-checkmark .check-icon::after {
  content: '';
  height: 100px;
  position: absolute;
  background: #FFFFFF;
  transform: rotate(-45deg);
}

.success-checkmark .check-icon .icon-line {
  height: 5px;
  background-color: #4CAF50;
  display: block;
  border-radius: 2px;
  position: absolute;
  z-index: 10;
}

.success-checkmark .check-icon .icon-line.line-tip {
  top: 46px;
  left: 14px;
  width: 25px;
  transform: rotate(45deg);
  animation: icon-line-tip 0.75s;
}

.success-checkmark .check-icon .icon-line.line-long {
  top: 38px;
  right: 8px;
  width: 47px;
  transform: rotate(-45deg);
  animation: icon-line-long 0.75s;
}

.success-checkmark .check-icon .icon-circle {
  top: -4px;
  left: -4px;
  z-index: 10;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  position: absolute;
  box-sizing: content-box;
  border: 4px solid rgba(76, 175, 80, 0.5);
}

.success-checkmark .check-icon .icon-fix {
  top: 8px;
  width: 5px;
  left: 26px;
  z-index: 1;
  height: 85px;
  position: absolute;
  transform: rotate(-45deg);
  background-color: #FFFFFF;
}

@keyframes rotate-circle {
  0% { transform: rotate(-45deg); }
  5% { transform: rotate(-45deg); }
  12% { transform: rotate(-405deg); }
  100% { transform: rotate(-405deg); }
}

@keyframes icon-line-tip {
  0% { width: 0; left: 1px; top: 19px; }
  54% { width: 0; left: 1px; top: 19px; }
  70% { width: 50px; left: -8px; top: 37px; }
  84% { width: 17px; left: 21px; top: 48px; }
  100% { width: 25px; left: 14px; top: 46px; }
}

@keyframes icon-line-long {
  0% { width: 0; right: 46px; top: 54px; }
  65% { width: 0; right: 46px; top: 54px; }
  84% { width: 55px; right: 0px; top: 35px; }
  100% { width: 47px; right: 8px; top: 38px; }
}

@keyframes modalSlideIn {
  0% { opacity: 0; transform: scale(0.8) translateY(-20px); }
  100% { opacity: 1; transform: scale(1) translateY(0); }
}

/* Error Modal Styles */
.error-modal-card {
  border-radius: 16px !important;
  overflow: hidden;
}

.error-modal-header {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.error-list {
  max-height: 300px;
  overflow-y: auto;
}

.error-item {
  display: flex;
  align-items: flex-start;
  font-size: 0.95rem;
  line-height: 1.5;
}
</style>

