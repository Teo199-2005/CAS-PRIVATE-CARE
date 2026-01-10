<template>
  <notification-toast
    v-model="notification.show"
    :type="notification.type"
    :title="notification.title"
    :message="notification.message"
    :timeout="notification.timeout"
  />
  
  <dashboard-template
    user-role="marketing"
    :user-name="profile.firstName && profile.lastName ? `${profile.firstName} ${profile.lastName}` : 'Marketing Staff'"
    :user-initials="profile.firstName && profile.lastName ? `${profile.firstName[0]}${profile.lastName[0]}` : 'MS'"
    :user-avatar="userAvatar"
    :welcome-message="profile.firstName ? `Welcome Back, ${profile.firstName}` : 'Welcome Back, Marketing'"
    subtitle="Manage client acquisition and commissions"
    header-title="Marketing Dashboard"
    header-subtitle="Client management and discount tracking"
    :nav-items="navItems"
    :current-section="currentSection"
    @section-change="currentSection = $event"
    @logout="logout"
  >
    <!-- Email Verification Banner -->
    <email-verification-banner />

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
                  <div class="balance-amount grey--text text--darken-2">${{ accountBalance.toFixed(2) }}</div>
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
        <v-col v-for="stat in stats" :key="stat.title" cols="6" sm="6" md="3">
          <stat-card :icon="stat.icon" :value="stat.value" :label="stat.title" :change="stat.change" :change-color="stat.changeColor" :change-icon="stat.changeIcon" :date="stat.date" icon-class="grey-darken-2" />
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
                    <span class="summary-label-compact">Clients Acquired</span>
                    <span class="summary-value-compact">{{ weeklySummary.clients_acquired }} clients</span>
                  </div>
                  <v-progress-linear :model-value="(weeklySummary.clients_acquired / weeklySummary.target) * 100" color="info" height="6" rounded />
                  <div class="text-caption text-grey mt-1">Target: {{ weeklySummary.target }} clients/week</div>
                </div>
                <div class="mb-3">
                  <div class="d-flex justify-space-between mb-1">
                    <span class="summary-label-compact">Previous Payout</span>
                    <span class="summary-value-compact info--text">${{ weeklySummary.previous_payout?.toFixed(2) || '0.00' }} - {{ weeklySummary.previous_payout_date || 'N/A' }}</span>
                  </div>
                </div>
              </div>
              <div>
                <v-divider class="my-2" />
                <div class="summary-item-compact">
                  <span class="summary-label-compact">Previous Payout</span>
                  <span class="summary-value-compact font-weight-bold">${{ weeklySummary.previous_payout?.toFixed(2) || '0.00' }} - {{ weeklySummary.previous_payout_date || 'N/A' }}</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card class="earning-container mb-3" elevation="0">
            <v-card-text class="pa-3">
              <div class="d-flex justify-space-between mb-1">
                <span class="earning-label">Total Commission</span>
                <span class="earning-value grey--text text--darken-2">${{ totalCommission }}</span>
              </div>
              <v-progress-linear :model-value="commissionProgress" color="success" height="6" rounded />
            </v-card-text>
          </v-card>
          
          <v-card class="earning-container mb-3" elevation="0">
            <v-card-text class="pa-3">
              <div class="d-flex justify-space-between mb-1">
                <span class="earning-label">This Month</span>
                <span class="earning-value">${{ monthlyCommission }}</span>
              </div>
              <v-progress-linear :model-value="monthlyProgress" color="info" height="6" rounded />
            </v-card-text>
          </v-card>
          
          <v-card class="earning-container mb-3" elevation="0">
            <v-card-text class="pa-3">
              <div class="d-flex justify-space-between mb-1">
                <span class="earning-label">Active Clients</span>
                <span class="earning-value">{{ activeClients }}</span>
              </div>
              <v-progress-linear :model-value="clientsProgress" color="warning" height="6" rounded />
            </v-card-text>
          </v-card>
          
          <v-card class="earning-container" elevation="0">
            <v-card-text class="pa-3">
              <div class="referral-label mb-2">My Referral Code</div>
              <div class="referral-code-box">
                <div class="referral-code">{{ referralCode }}</div>
                <v-btn icon="mdi-content-copy" size="small" variant="text" @click="copyReferralCode" class="copy-btn"></v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-row class="mt-1">
        <v-col cols="12">
          <v-card elevation="0" class="mb-3 compact-card">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact grey--text text--darken-2">My Clients Performance</span>
            </v-card-title>
            <v-card-text class="pa-4">
              <v-data-table :headers="clientHeaders" :items="myClients" :items-per-page="5" class="elevation-0">
                <template v-slot:item.status="{ item }">
                  <v-chip :color="getStatusColor(item.status)" size="small" class="font-weight-bold">{{ item.status }}</v-chip>
                </template>
                <template v-slot:item.commission="{ item }">
                  <span class="font-weight-bold grey--text text--darken-2">${{ item.commission }}</span>
                </template>
              </v-data-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Clients Section -->
    <div v-if="currentSection === 'clients'">
      <div class="mb-6">
        <v-row class="align-center">
          <v-col cols="12" md="4">
            <v-text-field v-model="clientSearch" placeholder="Search clients..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="3">
            <v-select v-model="statusFilter" :items="statusOptions" label="Status" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="3">
            <v-select v-model="boroughFilter" :items="boroughs" label="Borough" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-btn color="grey-darken-2" prepend-icon="mdi-plus" @click="addClientDialog = true">Add Client</v-btn>
          </v-col>
        </v-row>
      </div>
      <v-card elevation="0">
        <v-card-title class="card-header pa-8">
          <span class="section-title grey--text text--darken-2">My Clients</span>
        </v-card-title>
        <v-data-table :headers="clientManagementHeaders" :items="filteredClients" :items-per-page="10" class="elevation-0">
          <template v-slot:item.status="{ item }">
            <v-chip :color="getStatusColor(item.status)" size="small" class="font-weight-bold">{{ item.status }}</v-chip>
          </template>
          <template v-slot:item.commission="{ item }">
            <span class="font-weight-bold grey--text text--darken-2">${{ item.commission }}</span>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn class="action-btn-view" icon="mdi-eye" @click="viewClient(item)"></v-btn>
              <v-btn class="action-btn-edit" icon="mdi-percent" @click="editDiscount(item)"></v-btn>
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
        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-3">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact grey--text text--darken-2">Monthly Performance</span>
            </v-card-title>
            <v-card-text class="pa-4">
              <div style="height: 200px; position: relative;">
                <canvas ref="performanceChart"></canvas>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-3">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact grey--text text--darken-2">Client Status</span>
            </v-card-title>
            <v-card-text class="pa-4">
              <div style="height: 200px; position: relative;">
                <canvas ref="statusChart"></canvas>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-3">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact grey--text text--darken-2">Top Clients</span>
            </v-card-title>
            <v-card-text class="pa-4">
              <div v-for="client in topClients" :key="client.id" class="d-flex justify-space-between align-center mb-3">
                <div>
                  <div class="font-weight-bold">{{ client.name }}</div>
                  <div class="text-caption text-grey">{{ client.bookings }} bookings</div>
                </div>
                <div class="text-right">
                  <div class="font-weight-bold">${{ client.commission }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Payment Information Section -->
    <div v-if="currentSection === 'payments'">
      <v-card elevation="0">
        <v-card-title class="card-header pa-8">
          <div class="d-flex align-center">
            <v-icon size="40" color="primary" class="mr-4">mdi-credit-card</v-icon>
            <div>
              <div class="section-title primary--text">Commission Payment Information</div>
              <div class="text-caption text-grey">Connect your bank account to receive weekly commission payouts</div>
            </div>
          </div>
        </v-card-title>
        <v-card-text class="pa-8">
          <v-alert
            color="info"
            variant="tonal"
            prominent
            class="mb-6"
          >
            <div class="font-weight-bold mb-2">
              <v-icon start>mdi-bank</v-icon>
              Connect Payout Method
            </div>
            <p class="mb-4">
              Connect your bank account via Stripe to receive weekly commission payments ($1/hour per referral).<br>
              Your payment information is securely encrypted and never shared.
            </p>
            <v-btn
              color="primary"
              size="large"
              prepend-icon="mdi-bank"
              href="/connect-bank-account-marketing"
              elevation="3"
              class="text-none font-weight-bold"
            >
              Connect Bank Account
            </v-btn>
            <div class="text-caption mt-2 text-grey-darken-2">
              Once enabled, you'll receive automatic weekly payouts directly to your bank account.
            </div>
          </v-alert>

          <!-- Commission Summary -->
          <v-card elevation="2" class="mb-6">
            <v-card-title class="pa-6 bg-success">
              <span class="section-title white--text">Commission Summary</span>
            </v-card-title>
            <v-card-text class="pa-6">
              <v-row>
                <v-col cols="12" md="4">
                  <div class="text-center py-4">
                    <span class="summary-label">Total Earned</span>
                    <div class="summary-value success--text">${{ totalCommission }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="text-center py-4">
                    <span class="summary-label">This Month</span>
                    <div class="summary-value primary--text">${{ monthlyCommission }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="text-center py-4">
                    <span class="summary-label">Last Payout</span>
                    <div class="summary-value grey--text">$0.00</div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <!-- Payment Settings -->
          <v-card elevation="2">
            <v-card-title class="pa-6 bg-grey-lighten-4">
              <span class="section-title grey--text text--darken-2">Payment Settings</span>
            </v-card-title>
            <v-card-text class="pa-6">
              <v-row>
                <v-col cols="12" md="6">
                  <div class="text-body-2 mb-2 font-weight-bold">Payout Frequency</div>
                  <v-chip color="success" variant="flat">
                    <v-icon start>mdi-calendar-check</v-icon>
                    Weekly (Every Friday)
                  </v-chip>
                </v-col>
                <v-col cols="12" md="6">
                  <div class="text-body-2 mb-2 font-weight-bold">Commission Rate</div>
                  <v-chip color="primary" variant="flat">
                    <v-icon start>mdi-cash</v-icon>
                    $1.00 per hour referred
                  </v-chip>
                </v-col>
              </v-row>
              <v-divider class="my-4" />
              <div class="text-body-2 text-grey">
                <v-icon start size="small">mdi-information</v-icon>
                <strong>How it works:</strong> You earn $1 for every hour of service from clients you refer. 
                Payments are automatically transferred to your connected bank account every Friday.
              </div>
            </v-card-text>
          </v-card>
        </v-card-text>
      </v-card>
    </div>

    <!-- Notifications Section -->
    <div v-if="currentSection === 'notifications'">
      <notification-center ref="notificationCenter" user-type="marketing" :user-id="4" @action-clicked="handleNotificationAction" />
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
                  <v-text-field v-model="profile.email" label="Email" variant="outlined" type="email">
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
                    :rules="[v => !v || /^\d{5}$/.test(v) || 'Please enter a valid 5-digit ZIP code']"
                    @input="lookupProfileZipCode"
                    @blur="lookupProfileZipCode"
                  >
                    <template v-slot:prepend-inner>
                      <v-icon>mdi-map-marker</v-icon>
                    </template>
                  </v-text-field>
                  <div v-if="profileZipLocation" class="text-caption text-grey mt-1" style="font-weight: 600;">
                    {{ profileZipLocation }}
                  </div>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="profile.department" label="Department" variant="outlined" />
                </v-col>
                <v-col cols="12" md="6">
                  <v-select v-model="profile.role" :items="['Marketing Manager', 'Marketing Specialist', 'Marketing Coordinator']" label="Role" variant="outlined" />
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
                  <img v-if="userAvatar" :src="userAvatar" style="width: 100%; height: 100%; object-fit: cover;" />
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
              <p class="text-grey mb-4">Marketing Department</p>
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

    <!-- Add Client Dialog -->
    <v-dialog v-model="addClientDialog" max-width="600">
      <v-card>
        <v-card-title class="pa-6" style="background: #616161; color: white;">
          <span class="section-title" style="color: white;">Add New Client</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12">
              <v-text-field v-model="clientForm.name" label="Full Name" variant="outlined" />
            </v-col>
            <v-col cols="12">
              <v-text-field v-model="clientForm.email" label="Email" variant="outlined" type="email" />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="clientForm.phone" label="Phone" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-select v-model="clientForm.borough" :items="boroughs" label="Borough" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="clientForm.contractDate" label="Contract Date" variant="outlined" type="date" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="addClientDialog = false">Cancel</v-btn>
          <v-btn color="grey-darken-2" @click="saveClient">Add Client</v-btn>
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
import EmailVerificationBanner from './EmailVerificationBanner.vue';
import { useNotification } from '../composables/useNotification';
import { useNYLocationData } from '../composables/useNYLocationData.js';

const { notification, success, error, info } = useNotification();
const { counties, getCitiesForCounty, loadNYLocationData } = useNYLocationData();

const currentSection = ref(localStorage.getItem('marketingSection') || 'dashboard');
const userEmailVerified = ref(false);
const addClientDialog = ref(false);
const addPaymentDialog = ref(false);
const clientForm = ref({ name: '', email: '', phone: '', borough: 'Manhattan', contractDate: '' });
const paymentInfo = ref({ bankName: '', accountNumber: '', routingNumber: '', accountHolder: 'Marketing Staff' });
const clientSearch = ref('');
const statusFilter = ref('');
const boroughFilter = ref('');

// Payment settings
const marketingPayoutFrequency = ref('Weekly');
const marketingPayoutMethod = ref('Bank Transfer');
const marketingApplicationStatus = ref('pending'); // 'pending' or 'approved'

const marketingPaymentMethods = ref([
  { id: 1, type: 'visa', icon: 'mdi-credit-card', last4: '4242', holder: 'Marketing Staff', expiry: '12/25', isDefault: true, brandName: 'VISA' },
  { id: 2, type: 'mastercard', icon: 'mdi-credit-card', last4: '8888', holder: 'Marketing Staff', expiry: '06/26', isDefault: false, brandName: 'Mastercard' },
]);

const statusOptions = ['Active', 'Inactive', 'Suspended'];

const paymentHeaders = [
  { title: 'Date', key: 'date' },
  { title: 'Description', key: 'description' },
  { title: 'Amount', key: 'amount' },
  { title: 'Status', key: 'status' },
];

const paymentHistory = ref([
  { id: 1, date: '2024-12-15', description: 'Monthly Commission - November', amount: '420.00', status: 'Paid' },
  { id: 2, date: '2024-11-15', description: 'Monthly Commission - October', amount: '380.00', status: 'Paid' },
  { id: 3, date: '2024-10-15', description: 'Monthly Commission - September', amount: '350.00', status: 'Paid' },
  { id: 4, date: '2024-12-31', description: 'Monthly Commission - December', amount: '180.00', status: 'Pending' },
]);

const navItems = ref([
  { icon: 'mdi-view-dashboard', title: 'Dashboard', value: 'dashboard' },
  { icon: 'mdi-bell', title: 'Notifications', value: 'notifications' },
  { icon: 'mdi-account-group', title: 'My Clients', value: 'clients', category: 'MANAGEMENT' },
  { icon: 'mdi-credit-card', title: 'Payments', value: 'payments', category: 'FINANCIAL' },
  { icon: 'mdi-chart-line', title: 'Analytics', value: 'analytics', category: 'REPORTS' },
  { icon: 'mdi-account-circle', title: 'Profile (1099 Contractors)', value: 'profile', category: 'ACCOUNT' },
]);

const stats = ref([
  { title: 'My Clients', value: '0', icon: 'mdi-account-multiple', color: 'grey-darken-2', change: '+0 this month', changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
  { title: 'Active Bookings', value: '0', icon: 'mdi-calendar-check', color: 'grey-darken-2', change: '+0 this week', changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
  { title: 'Total Commission', value: '$0', icon: 'mdi-currency-usd', color: 'grey-darken-2', change: '+$0 this month', changeColor: 'text-success', changeIcon: 'mdi-arrow-up', date: new Date().toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) },
]);

const accountBalance = ref(0);
const weeklySummary = ref({
  clients_acquired: 0,
  target: 10,
  previous_payout: 0,
  previous_payout_date: null
});

// Pricing breakdown (with referral code):
// - Caregiver: $28.00/hr
// - Agency (net): $10.50/hr
// - Marketing Associate (referral commission): $1.00/hr
// - Training Center: $0.50/hr
// Total Client Rate: $40/hr (with referral)
//
// Pricing breakdown (without referral code):
// - Caregiver: $28.00/hr
// - Agency: $16.50/hr
// - Training Center: $0.50/hr
// Total Client Rate: $45/hr (without referral)
const myClients = ref([]);

const loadMarketingStats = async () => {
  try {
    const response = await fetch(`/api/marketing/stats?user_id=${marketingUserId.value}`);
    const data = await response.json();
    
    // Update stats
    stats.value = [
      { title: 'My Clients', value: data.my_clients?.toString() || '0', icon: 'mdi-account-multiple', color: 'grey-darken-2', change: `+${data.weekly_summary?.clients_acquired || 0} this week`, changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
      { title: 'Active Bookings', value: data.active_bookings?.toString() || '0', icon: 'mdi-calendar-check', color: 'grey-darken-2', change: '+' + (data.active_bookings || 0) + ' active', changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
      { title: 'Total Commission', value: '$' + (data.total_commission?.toFixed(2) || '0.00'), icon: 'mdi-currency-usd', color: 'grey-darken-2', change: '+$' + (data.pending_commission?.toFixed(2) || '0.00') + ' pending', changeColor: 'text-success', changeIcon: 'mdi-arrow-up', date: new Date().toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) },
    ];
    
    // Update clients list
    myClients.value = data.clients || [];
    
    // Update account balance
    accountBalance.value = data.account_balance || 0;
    
    // Update weekly summary
    weeklySummary.value = data.weekly_summary || {
      clients_acquired: 0,
      target: 10,
      previous_payout: 0,
      previous_payout_date: null
    };
  } catch (error) {
  }
};

const clientHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Status', key: 'status' },
  { title: 'Total Hours', key: 'totalHours' },
  { title: 'Contract Date Applied', key: 'contractDate' },
  { title: 'My Commission', key: 'commission' },
];

const clientManagementHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Borough', key: 'borough' },
  { title: 'Status', key: 'status' },
  { title: 'Total Hours', key: 'totalHours' },
  { title: 'Contract Date Applied', key: 'contractDate' },
  { title: 'My Commission', key: 'commission' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const analyticsMetrics = ref([
  { title: 'Recruitment Rate', value: '85%', icon: 'mdi-account-plus', color: 'success', change: '+5%' },
  { title: 'Retention Rate', value: '92%', icon: 'mdi-account-check', color: 'info', change: '+3%' },
  { title: 'Avg Hours/Caregiver', value: '105h', icon: 'mdi-clock', color: 'warning', change: '+8h' },
  { title: 'Performance Score', value: '4.7/5', icon: 'mdi-trophy', color: 'primary', change: '+0.1' },
]);

const totalCommission = computed(() => {
  return myClients.value.reduce((sum, client) => sum + parseFloat(client.commission), 0).toFixed(2);
});

const monthlyCommission = ref('420.00');
const activeClients = computed(() => {
  return myClients.value.filter(client => client.status === 'Active').length;
});

const topClients = computed(() => {
  return myClients.value
    .filter(c => c.status === 'Active')
    .sort((a, b) => parseFloat(b.commission) - parseFloat(a.commission))
    .slice(0, 4)
    .map(c => ({ ...c, bookings: c.totalBookings }));
});

const filteredClients = computed(() => {
  let filtered = myClients.value;
  
  if (clientSearch.value) {
    const search = clientSearch.value.toLowerCase();
    filtered = filtered.filter(client => 
      client.name.toLowerCase().includes(search) ||
      client.email.toLowerCase().includes(search) ||
      client.borough.toLowerCase().includes(search)
    );
  }
  
  if (statusFilter.value) {
    filtered = filtered.filter(client => client.status === statusFilter.value);
  }
  
  if (boroughFilter.value) {
    filtered = filtered.filter(client => client.borough === boroughFilter.value);
  }
  
  return filtered;
});

const commissionProgress = computed(() => Math.min((parseFloat(totalCommission.value) / 2000) * 100, 100));
const monthlyProgress = computed(() => Math.min((parseFloat(monthlyCommission.value) / 500) * 100, 100));
const clientsProgress = computed(() => Math.min((activeClients.value / 20) * 100, 100));

const referralCode = ref('Loading...');
const referralCodeStats = ref({
  discount_per_hour: 5.00,
  commission_per_hour: 1.00,
  usage_count: 0,
  total_commission_earned: 0,
});

// Load referral code from API
const loadReferralCode = async () => {
  try {
    const response = await fetch('/api/referral-codes/my-code');
    const data = await response.json();
    if (data.success && data.data) {
      referralCode.value = data.data.code;
      referralCodeStats.value = {
        discount_per_hour: parseFloat(data.data.discount_per_hour) || 5.00,
        commission_per_hour: parseFloat(data.data.commission_per_hour) || 1.00,
        usage_count: data.data.usage_count || 0,
        total_commission_earned: parseFloat(data.data.total_commission_earned) || 0,
      };
    } else {
      // No referral code found - set to "Not Generated"
      referralCode.value = 'Not Generated';
    }
  } catch (error) {
    // Error fetching code - show friendly message
    referralCode.value = 'Contact Admin';
  }
};

const currentTime = ref(new Date());

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

const getStatusColor = (status) => {
  const colors = { 'Active': 'success', 'Inactive': 'grey-darken-1', 'Suspended': 'error', 'No Contract': 'error' };
  return colors[status] || 'grey';
};

const getPaymentStatusColor = (status) => {
  const colors = { 'Paid': 'success', 'Pending': 'warning', 'Failed': 'error' };
  return colors[status] || 'grey';
};

const loadProfile = async () => {
  try {
    const response = await fetch('/api/profile?user_type=marketing');
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
        department: data.user.department || 'Marketing & Client Acquisition',
        role: data.user.role || 'Marketing Specialist'
      };
      marketingUserId.value = data.user.id;
      if (data.user.avatar) {
        userAvatar.value = `/storage/${data.user.avatar}`;
      }
      // Load marketing stats after we have the user ID
      loadMarketingStats();
    }
  } catch (error) {
  }
};

const saveProfile = async () => {
  try {
    // Build payload, ensuring phone is a string if present
    const payload = {
      ...profile.value,
      borough: profile.value.county,
      city: profile.value.city
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

const logout = () => {
  window.location.href = '/login';
};

const loadPaymentMethods = async () => {
  try {
    const response = await fetch('/api/payment-methods');
    const data = await response.json();
    
    if (data.success && data.payment_methods) {
      // Filter cards from payment methods
      const cards = data.payment_methods.filter(pm => pm.type !== 'bank_account');
      if (cards.length > 0) {
        marketingPaymentMethods.value = cards;
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

const editCard = (card) => {
  info('Edit payment method', 'This feature is coming soon');
};

const deleteCard = (card) => {
  info('Delete payment method', 'This feature is coming soon');
};

const checkMarketingApplicationStatus = async () => {
  try {
    const response = await fetch('/api/marketing/application-status');
    const data = await response.json();
    if (data.status) {
      marketingApplicationStatus.value = data.status.toLowerCase();
    } else {
      marketingApplicationStatus.value = 'pending';
    }
  } catch (error) {
    marketingApplicationStatus.value = 'pending';
  }
};

const viewClient = (client) => {
  info(`Viewing details for ${client.name}`, 'Client Details');
};

const editDiscount = (client) => {
  info(`Managing discount for ${client.name}`, 'Edit Discount');
};

const saveClient = () => {
  if (!clientForm.value.name || !clientForm.value.email) {
    error('Please fill in all required fields', 'Missing Information');
    return;
  }
  
  const newId = Math.max(...myClients.value.map(c => c.id)) + 1;
  myClients.value.push({
    id: newId,
    ...clientForm.value,
    status: 'Active',
    totalHours: 0,
    totalSpent: '0.00',
    contractDate: clientForm.value.contractDate,
    commission: '0.00'
  });
  
  success(`${clientForm.value.name} has been added to your clients!`, 'Client Added');
  clientForm.value = { name: '', email: '', phone: '', borough: 'Manhattan', contractDate: '' };
  addClientDialog.value = false;
};

const copyReferralCode = () => {
  navigator.clipboard.writeText(referralCode.value);
  success('Referral code copied to clipboard!', 'Code Copied');
};

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

const lookupProfileZipCode = async () => {
  const zip = profile.value.zip;
  if (zip && zip.length === 5 && /^\d{5}$/.test(zip)) {
    // Try API lookup first (supports all NY ZIP codes)
    try {
      const response = await fetch(`/api/zipcode-lookup/${zip}`);
      if (response.ok) {
        const data = await response.json();
        if (data.success && data.location) {
          profileZipLocation.value = data.location;
          return;
        }
      }
    } catch (error) {
    }
    
  // Fallback to static map (avoid misleading default like "New York, NY")
  profileZipLocation.value = zipCodeMap[zip] || '';
  } else {
    profileZipLocation.value = '';
  }
};

const profile = ref({
  firstName: 'Marketing',
  lastName: 'Staff',
  email: 'marketing@casprivatecare.com',
  phone: '(646) 282-8282',
  address: '',
  county: '',
  city: '',
  zip: '',
  birthdate: '',
  department: 'Marketing & Client Acquisition',
  role: 'Marketing Specialist'
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

const nyCities = computed(() => {
  if (!profile.value.county) {
    return ['Select County First'];
  }
  return getCitiesForCounty(profile.value.county);
});

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Avatar upload
const avatarInput = ref(null);
const userAvatar = ref('');
const uploadingAvatar = ref(false);
const marketingUserId = ref(null);

const userName = computed(() => {
  if (profile.value.firstName && profile.value.lastName) {
    return `${profile.value.firstName} ${profile.value.lastName}`;
  }
  return 'Marketing Staff';
});

const userInitials = computed(() => {
  if (profile.value.firstName && profile.value.lastName) {
    return `${profile.value.firstName[0]}${profile.value.lastName[0]}`.toUpperCase();
  }
  return 'MS';
});

const memberSince = computed(() => {
  return 'Jan 2024';
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
  
  if (!marketingUserId.value) {
    alert('User ID not available. Please refresh the page.');
    return;
  }
  
  uploadingAvatar.value = true;
  
  try {
    const formData = new FormData();
    formData.append('avatar', file);
    
    const response = await fetch(`/api/user/${marketingUserId.value}/avatar`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: formData
    });
    
    const data = await response.json();
    
    if (response.ok && data.success) {
      userAvatar.value = data.avatar;
      alert('Profile picture updated successfully!');
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

const handleNotificationAction = (action) => {
};

const formatSSN = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  if (value.length >= 3) {
    value = value.substring(0, 3) + '-' + value.substring(3, 5) + '-' + value.substring(5, 9);
  }
  profile.value.ssn = value;
  // Clear ITIN if SSN is entered
  if (value.length > 0) {
    profile.value.itin = '';
  }
};

const formatITIN = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  if (value.length >= 3) {
    value = value.substring(0, 3) + '-' + value.substring(3, 5) + '-' + value.substring(5, 9);
  }
  profile.value.itin = value;
};

const performanceChart = ref(null);
const statusChart = ref(null);

const initCharts = () => {
  if (!window.Chart) {
    setTimeout(initCharts, 100);
    return;
  }
  
  if (performanceChart.value) {
    const ctx = performanceChart.value.getContext('2d');
    new window.Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Commission ($)',
          data: [200, 280, 350, 420, 380, 420],
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
  
  if (statusChart.value) {
    const ctx = statusChart.value.getContext('2d');
    const activeCount = myClients.value.filter(c => c.status === 'Active').length;
    const inactiveCount = myClients.value.filter(c => c.status === 'Inactive').length;
    
    new window.Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Active', 'Inactive'],
        datasets: [{
          data: [activeCount, inactiveCount],
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

// Watch for county changes to reset city selection
watch(() => profile.value.county, (newCounty, oldCounty) => {
  // Only reset city if county actually changed and it's not the initial load
  if (newCounty && oldCounty && newCounty !== oldCounty) {
    profile.value.city = '';
  }
});

watch(currentSection, (newVal) => {
  localStorage.setItem('marketingSection', newVal);
  if (newVal === 'payment') {
    loadPaymentMethods();
    checkMarketingApplicationStatus(); // Check approval status when opening payment section
  }
});

onMounted(() => {
  loadNYLocationData();
  loadProfile(); // This will also call loadMarketingStats after getting user ID
  loadReferralCode();
  checkMarketingApplicationStatus();
  setTimeout(initCharts, 500);
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

.action-buttons {
  display: flex;
  gap: 6px;
  justify-content: center;
}

.action-btn-view,
.action-btn-edit {
  width: 36px !important;
  height: 36px !important;
  min-width: 36px !important;
  padding: 0 !important;
  border-radius: 8px !important;
}

.action-btn-view {
  background-color: #757575 !important;
  color: white !important;
}

.action-btn-edit {
  background-color: #9e9e9e !important;
  color: white !important;
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

.payment-schedule {
  padding: 8px 0;
}

.schedule-item {
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
}

.schedule-item:last-child {
  border-bottom: none;
}

.schedule-date {
  font-size: 0.8rem;
  color: #757575;
  font-weight: 500;
}

.schedule-value {
  font-size: 0.95rem;
  font-weight: 600;
  color: #424242;
  margin-top: 4px;
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

.referral-code-section {
  padding: 12px 0;
  border-top: 1px solid #f0f0f0;
  margin-top: 8px;
}

.referral-label {
  font-size: 0.8rem;
  color: #757575;
  font-weight: 500;
  margin-bottom: 8px;
}

.referral-code-box {
  background: #f0f0f0;
  border-radius: 12px;
  padding: 16px 20px;
  border: 2px solid #d0d0d0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.referral-code {
  font-size: 1.4rem;
  font-weight: 800;
  color: #1a1a1a;
  font-family: 'Courier New', monospace;
  letter-spacing: 2px;
  text-transform: uppercase;
}

.copy-btn {
  color: #757575 !important;
  transition: color 0.2s ease;
}

.copy-btn:hover {
  color: #424242 !important;
}

.earning-container {
  border: 1px solid #e0e0e0 !important;
  border-radius: 8px !important;
  background: #fafafa !important;
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

.enhanced-card-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-bottom: 1px solid #e2e8f0;
  border-radius: 20px 20px 0 0 !important;
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

.profile-stat {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 12px 0;
  font-size: 0.95rem;
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
</style>