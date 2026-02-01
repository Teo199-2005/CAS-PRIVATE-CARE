<template>
  <!-- Global Loading Overlay -->
  <LoadingOverlay
    :visible="isPageLoading"
    context="caregiver"
    tagline="Caregiver Portal"
  />

  <dashboard-template
    user-role="caregiver"
    :user-name="profile.firstName && profile.lastName ? `${profile.firstName} ${profile.lastName}` : 'Demo Caregiver'"
    :user-initials="profile.firstName && profile.lastName ? `${profile.firstName[0]}${profile.lastName[0]}` : 'DC'"
    :user-avatar="userAvatar"
    :welcome-message="profile.firstName ? `Welcome Back, ${profile.firstName}` : 'Welcome Back, Demo'"
    subtitle="Manage your appointments and clients"
    header-title="Caregiver Portal"
    header-subtitle="Manage your appointments and provide quality care"
    :nav-items="navItems"
    :current-section="currentSection"
    @section-change="handleSectionChange"
    @logout="logout"
    @disabled-click="handleDisabledNavClick"
  >
    <template #header-left>
      <div style="flex: 1;"></div>
    </template>

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

    <!-- Onboarding Progress Component - Shows setup steps for contractors -->
    <OnboardingProgress
      v-if="applicationStatus !== 'approved' || !stripeConnected"
      role="caregiver"
      :user-data="profile"
      :application-status="applicationStatus"
      :bank-connected="stripeConnected"
      :w9-submitted="w9Submitted"
      :profile-complete="profileComplete"
      @step-click="handleOnboardingStepClick"
    />

    <!-- Legacy Pending Approval Banner - Fallback for older browsers -->
    <v-alert
      v-if="applicationStatus === 'pending' && !showOnboardingProgress"
      type="warning"
      variant="tonal"
      prominent
      class="mb-4"
      icon="mdi-clock-alert"
    >
      <v-alert-title class="font-weight-bold">Application Pending Approval</v-alert-title>
      <div class="mt-2">
        Your contractor application is currently under review. Some features are restricted until your application is approved.
        <br /><br />
        <strong>To complete your application:</strong>
        <ol class="mt-2 mb-2" style="margin-left: 20px;">
          <li>Download and complete the W9 form</li>
          <li>Submit the completed form to our office</li>
          <li>Wait for admin approval (typically 1-2 business days)</li>
        </ol>
        <v-btn color="primary" variant="outlined" size="small" prepend-icon="mdi-file-document" @click="viewW9Form" class="mt-2">
          View W9 Form
        </v-btn>
      </div>
    </v-alert>

        <!-- Dashboard Section -->
        <div v-if="currentSection === 'dashboard'">
          <v-row class="mb-2">
            <v-col cols="12" sm="6" md="3">
              <v-card elevation="0" class="mb-3 account-balance-card d-flex flex-column" style="height: 100%;">
                <v-card-title class="account-balance-header pa-4">
                  <span class="section-title-compact success--text">Account Balance</span>
                </v-card-title>
                <v-card-text class="pa-4 flex-grow-1 d-flex flex-column justify-space-between">
                  <div>
                    <div class="text-center mb-3">
                      <div class="balance-amount success--text">${{ accountBalance }}</div>
                      <div class="text-caption text-grey">Available Balance</div>
                    </div>
                    <div class="d-flex justify-space-between text-caption mb-1">
                      <span>Auto Payout:</span>
                      <span class="success--text font-weight-bold">Every Friday</span>
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
                    <v-btn block variant="outlined" color="success" size="x-small">Request Payout</v-btn>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col v-for="(stat, index) in stats" :key="stat.title" cols="6" sm="6" md="3">
              <div
                :class="stat.clickable ? 'cursor-pointer' : ''"
                @click="stat.onClick ? stat.onClick() : null"
              >
                <stat-card
                  :icon="stat.icon"
                  :value="stat.value"
                  :label="stat.title"
                  :change="stat.change"
                  :change-color="stat.changeColor"
                  :change-icon="stat.changeIcon"
                  icon-class="success"
                  :stagger-index="index + 1"
                />
              </div>
            </v-col>
          </v-row>

          <v-row class="mt-1">
            <v-col cols="12">
              <v-row>
                <v-col cols="12" lg="6">
                  <v-card elevation="2" class="mb-3 enhanced-card d-flex flex-column">
                    <v-card-title class="enhanced-card-header pa-6">
                      <v-icon color="success" class="mr-3">mdi-clock-time-four</v-icon>
                      <span class="section-title success--text">Time Tracking</span>
                    </v-card-title>
                    <v-card-text class="pa-4 flex-grow-1 d-flex flex-column justify-space-between">
                      <div>
                        <div v-if="isTimedIn" class="mb-3">
                          <div class="d-flex align-center justify-center mb-3">
                            <v-chip color="success" size="large" class="mr-3">
                              <v-icon start size="small">mdi-clock-check</v-icon>
                              Clocked In
                            </v-chip>
                            <span class="text-h5 font-weight-bold">{{ timeIn }}</span>
                          </div>
                          <div class="text-center mb-3">
                            <div class="text-body-1 font-weight-medium mb-1">Currently working with</div>
                            <div class="text-h6 success--text font-weight-bold mb-2">{{ isLoadingStats ? 'Loading...' : currentClient }}</div>
                            <div class="d-flex justify-space-between text-caption mb-2">
                              <span>Location:</span>
                              <span class="font-weight-medium">Client Home</span>
                            </div>
                            <div class="d-flex justify-space-between text-caption">
                              <span>Shift Duration:</span>
                              <span class="font-weight-medium">8 hours</span>
                            </div>
                          </div>
                          <v-alert type="info" variant="tonal" density="compact" class="mb-3">
                            <div class="text-caption" style="color: #000000;">
                              <v-icon size="small" class="mr-1" style="color: #000000;">mdi-information</v-icon>
                              Remember to clock out when your shift ends
                            </div>
                          </v-alert>
                        </div>

                        <div v-else class="text-center mb-3">
                          <v-chip color="grey" size="large" class="mb-3">
                            <v-icon start size="small" style="color: #000000;">mdi-clock-outline</v-icon>
                            <span style="color: #000000;">Not Clocked In</span>
                          </v-chip>
                          <div class="text-body-1 text-grey mb-3">{{ bookingStatusMessage }}</div>

                          <v-alert v-if="currentClient !== 'N/A' && canClockIn" type="warning" variant="tonal" density="compact" class="mb-3">
                            <div class="text-caption" style="color: #000000;">
                              <v-icon size="small" class="mr-1" style="color: #000000;">mdi-clock-alert</v-icon>
                              Please clock in 5 minutes before your scheduled shift time
                            </div>
                          </v-alert>

                          <v-alert v-else-if="currentClient !== 'N/A' && !canClockIn" type="info" variant="tonal" density="compact" class="mb-3">
                            <div class="text-caption" style="color: #000000;">
                              <v-icon size="small" class="mr-1" style="color: #000000;">mdi-clock-outline</v-icon>
                              {{ bookingStatusMessage }}
                            </div>
                          </v-alert>

                          <v-alert v-else type="info" variant="tonal" density="compact" class="mb-3">
                            <div class="text-caption" style="color: #000000;">
                              <v-icon size="small" class="mr-1" style="color: #000000;">mdi-information</v-icon>
                              You need an active client assignment to use time tracking
                            </div>
                          </v-alert>
                        </div>
                      </div>
                      <div>
                        <v-btn v-if="isTimedIn && currentClient !== 'N/A'" block color="error" size="large" prepend-icon="mdi-logout" @click="handleTimeOut">Clock Out</v-btn>
                        <v-btn v-else-if="canClockIn" block color="success" size="large" prepend-icon="mdi-login" @click="handleTimeIn">Clock In</v-btn>
                        <v-btn v-else-if="currentClient !== 'N/A'" block color="grey" size="large" prepend-icon="mdi-clock-outline" disabled>Pending</v-btn>
                        <v-btn v-else block color="grey" size="large" prepend-icon="mdi-lock" disabled>No Active Client</v-btn>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-col>
                <v-col cols="12" lg="6">
                  <v-card class="mb-3 enhanced-card d-flex flex-column" elevation="2">
                    <v-card-title class="enhanced-card-header pa-6">
                      <v-icon color="success" class="mr-3">mdi-chart-line</v-icon>
                      <span class="section-title success--text">Previous Week Summary</span>
                    </v-card-title>
                    <v-card-text class="pa-4 flex-grow-1 d-flex flex-column justify-space-between">
                      <div>
                        <div class="mb-3">
                          <div class="d-flex justify-space-between mb-1">
                            <span class="summary-label-compact">Hours Worked</span>
                            <span class="summary-value-compact">{{ previousWeekHours }} hrs</span>
                          </div>
                          <v-progress-linear :model-value="previousWeekProgress" color="info" height="6" rounded />
                          <div class="text-caption text-grey mt-1">Target: 40 hrs/week</div>
                        </div>
                        <div class="mb-3">
                          <div class="d-flex justify-space-between mb-1">
                            <span class="summary-label-compact">Previous Payout</span>
                            <span class="summary-value-compact info--text">${{ previousWeekPayout }} - {{ previousPayoutDate }}</span>
                          </div>
                        </div>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-col>

              </v-row>

              <v-row>
                <v-col cols="12">
                  <v-card class="mb-3" elevation="1">
                    <v-card-text class="pa-4">
                      <div class="d-flex align-center mb-3">
                        <v-icon color="success" size="20" class="mr-2">mdi-calendar-week</v-icon>
                        <span class="text-subtitle-2 font-weight-medium">Weekly Schedule</span>
                        <v-spacer></v-spacer>
                        <v-chip v-if="weeklySchedule.filter(d => d.assigned).length > 0" size="x-small" color="success" variant="tonal">
                          {{ weeklySchedule.filter(d => d.assigned).length }} days
                        </v-chip>
                      </div>
                      <div v-if="loadingWeeklySchedule" class="text-center py-3">
                        <v-progress-circular indeterminate color="success" size="24"></v-progress-circular>
                      </div>
                      <div v-else class="schedule-grid">
                        <div v-for="day in weeklySchedule" :key="day.day"
                             class="schedule-day"
                             :class="{ 'is-assigned': day.assigned, 'is-today': isToday(day.day) }">
                          <div class="day-label">{{ day.day.substring(0, 3) }}</div>
                          <div v-if="day.assigned" class="day-status assigned">
                            <v-icon size="12" color="success">mdi-check-circle</v-icon>
                            <span class="time-label">{{ formatTime(day.start_time) }}</span>
                          </div>
                          <div v-else class="day-status off">—</div>
                        </div>
                      </div>
                      <div v-if="!loadingWeeklySchedule && weeklySchedule.every(d => !d.assigned)" class="text-center py-2">
                        <span class="text-caption text-grey">No schedule assigned</span>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>

              <!-- Tax & Payroll Section -->
              <v-row>
                <v-col cols="12">
                  <tax-payroll-section
                    user-type="caregiver"
                    primary-color="success"
                  />
                </v-col>
              </v-row>

            </v-col>
          </v-row>
        </div>

<!-- Job Listings Section -->
        <div v-if="currentSection === 'available-clients'">
          <div class="mb-6">
            <v-row class="align-center">
              <v-col cols="12" md="3">
                <v-text-field v-model="availableClientSearch" placeholder="Search by client, service, or location..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="3">
                <v-select v-model="availableCountyFilter" :items="['All', ...counties]" label="County" variant="outlined" density="compact" hide-details @update:model-value="onCountyFilterChange" />
              </v-col>
              <v-col cols="12" md="3">
                <v-select v-model="availableCityFilter" :items="availableFilterCities" label="City" variant="outlined" density="compact" hide-details :disabled="!availableCountyFilter || availableCountyFilter === 'All'" />
              </v-col>
              <v-col cols="12" md="2">
                <v-select v-model="availableDateFilter" :items="['All', 'This Week', 'Soon']" label="Date" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="1" class="d-flex align-center">
                <v-btn variant="outlined" size="default" class="touch-friendly-btn" @click="resetAvailableFilters">Reset</v-btn>
              </v-col>
            </v-row>
          </div>

          <v-row>
            <v-col cols="12" class="d-flex justify-space-between align-center mb-4">
              <div>
                <h3 class="page-subtitle">Available Bookings</h3>
                <p class="text-caption text-grey">{{ filteredAvailableClients.length }} bookings need caregivers</p>
              </div>
              <v-btn-toggle v-model="availableView" mandatory color="success">
                <v-btn value="grid" icon="mdi-view-grid" />
                <v-btn value="list" icon="mdi-view-list" />
              </v-btn-toggle>
            </v-col>
          </v-row>

          <!-- Empty state -->
          <v-row v-if="filteredAvailableClients.length === 0">
            <v-col cols="12" class="text-center pa-8">
              <v-icon size="64" color="grey-lighten-2">mdi-briefcase-search</v-icon>
              <p class="mt-4 text-grey">No available bookings at this time. Check back later!</p>
            </v-col>
          </v-row>

          <!-- Grid View -->
          <v-row v-else-if="availableView === 'grid'">
            <v-col v-for="job in filteredAvailableClients" :key="job.id" cols="12" sm="6" md="4">
              <v-card class="client-card job-listing-card" elevation="0">
                <v-card-text class="pa-5">
                  <!-- Header with client and urgency -->
                  <div class="d-flex align-center justify-space-between mb-3">
                    <div class="d-flex align-center">
                      <v-avatar :color="job.avatarColor" size="44" class="client-job-avatar">
                        <v-img v-if="job.clientAvatar" :src="job.clientAvatar" cover @error="job.clientAvatar = null" />
                        <span v-else class="text-white font-weight-bold">{{ job.clientInitials }}</span>
                      </v-avatar>
                      <div class="ml-3">
                        <div class="font-weight-bold">{{ job.clientName }}</div>
                        <div class="text-caption text-grey" v-if="job.clientAge">Age {{ job.clientAge }} • {{ job.mobilityLevel }}</div>
                      </div>
                    </div>
                    <v-chip v-if="job.isUrgent" color="error" size="x-small" class="ml-2">Urgent</v-chip>
                  </div>

                  <!-- Service & Duration -->
                  <div class="mb-3">
                    <div class="d-flex align-center mb-2">
                      <v-icon size="16" color="success" class="mr-2">mdi-medical-bag</v-icon>
                      <span class="text-body-2 font-weight-medium">{{ job.serviceType }}</span>
                      <v-chip size="x-small" class="ml-2" variant="outlined" style="color: #1e293b !important; border-color: #cbd5e1 !important;">{{ job.dutyType }}</v-chip>
                    </div>
                    <div class="d-flex align-center mb-2">
                      <v-icon size="16" color="success" class="mr-2">mdi-map-marker</v-icon>
                      <span class="text-body-2">{{ job.location }}</span>
                      <span v-if="job.city" class="text-caption text-grey ml-1">• {{ job.city }}</span>
                    </div>
                    <div class="d-flex align-center mb-2">
                      <v-icon size="16" color="success" class="mr-2">mdi-calendar-range</v-icon>
                      <span class="text-body-2">{{ job.startDate }} - {{ job.endDate }}</span>
                    </div>
                    <div class="d-flex align-center">
                      <v-icon size="16" color="success" class="mr-2">mdi-clock-outline</v-icon>
                      <span class="text-body-2">{{ job.durationDays }} days • {{ job.hoursPerDay }}hrs/day</span>
                    </div>
                  </div>

                  <v-divider class="my-3" />

                  <!-- Compensation -->
                  <div class="d-flex justify-space-between align-center mb-3">
                    <div>
                      <div class="text-caption text-grey">Pay Rate</div>
                      <div class="font-weight-bold success--text">{{ job.payRate }}</div>
                    </div>
                    <div class="text-right">
                      <div class="text-caption text-grey">Est. Earnings</div>
                      <div class="font-weight-bold">{{ job.estimatedEarnings }}</div>
                    </div>
                  </div>

                  <!-- Assignment Status -->
                  <div class="d-flex align-center justify-space-between mb-3">
                    <div class="text-caption">
                      <v-chip :color="job.assignmentStatus === 'unassigned' ? 'warning' : 'info'" size="x-small">
                        {{ job.spotsRemaining }} of {{ job.caregiversNeeded }} spots open
                      </v-chip>
                    </div>
                    <v-chip :color="job.status === 'approved' ? 'success' : 'primary'" size="x-small" variant="outlined">
                      {{ job.status }}
                    </v-chip>
                  </div>

                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <!-- List/Table View -->
          <v-data-table v-else :headers="jobListingHeaders" :items="filteredAvailableClients" :items-per-page="10" class="elevation-0 table-no-checkbox">
            <template v-slot:item.client="{ item }">
              <div class="d-flex align-center">
                <v-avatar :color="item.avatarColor" size="36" class="mr-3 client-job-avatar">
                  <v-img v-if="item.clientAvatar" :src="item.clientAvatar" cover @error="item.clientAvatar = null" />
                  <span v-else class="text-white text-caption font-weight-bold">{{ item.clientInitials }}</span>
                </v-avatar>
                <div>
                  <div class="font-weight-medium">{{ item.clientName }}</div>
                  <div class="text-caption text-grey" v-if="item.clientAge">Age {{ item.clientAge }}</div>
                </div>
              </div>
            </template>
            <template v-slot:item.service="{ item }">
              <div>
                <div class="font-weight-medium">{{ item.serviceType }}</div>
                <div class="text-caption text-grey">{{ item.dutyType }}</div>
              </div>
            </template>
            <template v-slot:item.location="{ item }">
              <div>
                <div>{{ item.location }}</div>
                <div class="text-caption text-grey" v-if="item.city">{{ item.city }}</div>
              </div>
            </template>
            <template v-slot:item.dates="{ item }">
              <div>
                <div>{{ item.startDate }}</div>
                <div class="text-caption text-grey">{{ item.durationDays }} days</div>
              </div>
            </template>
            <template v-slot:item.compensation="{ item }">
              <div>
                <div class="font-weight-bold success--text">{{ item.payRate }}</div>
                <div class="text-caption text-grey">{{ item.estimatedEarnings }} total</div>
              </div>
            </template>
            <template v-slot:item.spots="{ item }">
              <v-chip :color="item.spotsRemaining === item.caregiversNeeded ? 'warning' : 'info'" size="small">
                {{ item.spotsRemaining }}/{{ item.caregiversNeeded }}
              </v-chip>
            </template>
            <template v-slot:item.status="{ item }">
              <v-chip :color="item.status === 'approved' ? 'success' : 'primary'" size="small" variant="outlined">
                {{ item.status }}
              </v-chip>
            </template>
          </v-data-table>
        </div>

<!-- Payment Information Section -->
        <div v-if="currentSection === 'payment'">
          <v-row>
            <v-col cols="12" md="8">
              <v-card elevation="0" class="mb-6">
                <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
                  <span class="section-title success--text">Payout Method</span>
                  <v-btn
                    v-if="!stripeConnected"
                    color="success"
                    prepend-icon="mdi-wallet-plus"
                    @click="connectBankAccount"
                    :loading="connectingBank"
                  >
                    Connect Payout Method
                  </v-btn>
                  <v-chip v-else color="success" prepend-icon="mdi-check-circle">
                    Connected
                  </v-chip>
                </v-card-title>
                <v-card-text class="pa-8">
                  <!-- Payout Method Not Connected -->
                  <div v-if="!stripeConnected" class="text-center py-8">
                    <v-icon size="80" color="grey-lighten-1" class="mb-4">mdi-wallet-outline</v-icon>
                    <h3 class="text-h6 mb-2">Connect Your Payout Method</h3>
                    <p class="text-body-2 text-grey mb-6">
                      Connect your preferred payout method via Stripe to receive weekly payments.<br>
                      Your payment information is securely encrypted and never shared.
                    </p>

                    <!-- Stripe Connect Not Enabled Warning -->
                    <v-alert type="warning" variant="tonal" class="mb-4 text-left">
                      <div class="font-weight-bold mb-2">Stripe Connect Setup Required</div>
                      <p class="text-body-2 mb-2">
                        The platform administrator needs to enable Stripe Connect to allow caregiver payouts.
                      </p>
                      <p class="text-body-2">
                        Once enabled, you'll be able to connect your bank account, debit card, or digital wallet to receive payments.
                      </p>
                    </v-alert>

                    <!-- Available Payout Methods -->
                    <v-alert color="info" variant="tonal" class="mb-4 text-left">
                      <div class="font-weight-bold mb-3">Available Payout Methods (Coming Soon):</div>
                      <div class="d-flex align-center mb-2">
                        <v-icon color="info" class="mr-2">mdi-bank</v-icon>
                        <span><strong>Bank Account</strong> - Direct deposit (ACH) to your bank</span>
                      </div>
                      <div class="d-flex align-center mb-2">
                        <v-icon color="info" class="mr-2">mdi-credit-card</v-icon>
                        <span><strong>Debit Card</strong> - Instant transfer to your card</span>
                      </div>
                      <div class="d-flex align-center mb-2">
                        <v-icon color="info" class="mr-2">mdi-cash</v-icon>
                        <span><strong>Cash App</strong> - Direct to Cash App account</span>
                      </div>
                      <div class="d-flex align-center">
                        <v-icon color="info" class="mr-2">mdi-wallet</v-icon>
                        <span><strong>Other Digital Wallets</strong> - Alipay, Venmo, etc.</span>
                      </div>
                    </v-alert>
                  </div>

                  <!-- Payout Method Connected -->
                  <v-row v-else>
                    <v-col cols="12">
                      <div class="bank-account-card-stripe">
                        <div class="d-flex align-center mb-4">
                          <v-icon size="48" color="success" class="mr-4">mdi-wallet</v-icon>
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
                          <span class="font-weight-medium">Bank Transfer (ACH)</span>
                        </div>

                        <div class="d-flex justify-space-between align-center mb-3">
                          <span class="text-body-2 text-grey">Payout Schedule:</span>
                          <span class="font-weight-medium">Weekly (Every Friday)</span>
                        </div>

                        <div class="d-flex justify-space-between align-center mb-3">
                          <span class="text-body-2 text-grey">Next Payout:</span>
                          <span class="font-weight-bold success--text">{{ nextPayoutDate }}</span>
                        </div>

                        <v-divider class="my-4"></v-divider>

                        <v-alert type="success" variant="tonal" density="compact">
                          <div class="d-flex align-center">
                            <v-icon class="mr-2">mdi-shield-check</v-icon>
                            <span class="text-body-2">
                              Your bank account is securely connected via Stripe. Funds are transferred automatically after each session is completed and approved.
                            </span>
                          </div>
                        </v-alert>

                        <div class="mt-4 d-flex flex-wrap gap-2">
                          <v-btn
                            color="success"
                            variant="flat"
                            size="small"
                            prepend-icon="mdi-swap-horizontal"
                            @click="connectBankAccount"
                          >
                            Change Payout Method
                          </v-btn>
                          <v-btn
                            color="primary"
                            variant="outlined"
                            size="small"
                            prepend-icon="mdi-open-in-new"
                            @click="openStripeDashboard"
                          >
                            Manage on Stripe
                          </v-btn>
                          <v-btn
                            color="error"
                            variant="outlined"
                            size="small"
                            prepend-icon="mdi-delete"
                            @click="showRemovePayoutDialog = true"
                          >
                            Remove Method
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
                  <span class="section-title success--text">Account Balance</span>
                </v-card-title>
                <v-card-text class="pa-8">
                  <div class="summary-item" style="margin-bottom: 24px;">
                    <span class="summary-label text-h6">Current Balance</span>
                    <span class="summary-value text-h4 warning--text">${{ pendingEarnings }}</span>
                  </div>
                  <v-divider class="my-4" />
                  <div class="summary-item">
                    <span class="summary-label">Total Paid Out</span>
                    <span class="summary-value success--text">${{ totalEarnings }}</span>
                  </div>
                  <div class="summary-item">
                    <span class="summary-label">Last Payment</span>
                    <span class="summary-value">${{ lastPaymentAmount }}</span>
                  </div>
                  <v-divider class="my-4" />
                  <div class="summary-item">
                    <span class="summary-label">Next Payout</span>
                    <span class="summary-value font-weight-bold">{{ nextPayoutDate }}</span>
                  </div>
                  <v-divider class="my-4" />

                  <!-- Automatic Payout Information -->
                  <div v-if="parseFloat(pendingEarnings) > 0" class="mt-4">
                    <v-alert type="info" variant="tonal" density="compact" color="blue">
                      <div class="text-body-2">
                        <v-icon icon="mdi-information" size="small" class="mr-2"></v-icon>
                        <strong>Automatic Payment:</strong> Your pending balance of ${{ pendingEarnings }} will be automatically paid by the admin team.
                      </div>
                    </v-alert>
                  </div>

                  <div v-else class="mt-4">
                    <v-alert type="success" variant="tonal" density="compact">
                      <div class="text-body-2">
                        <v-icon icon="mdi-check-circle" size="small" class="mr-2"></v-icon>
                        <strong>All Caught Up!</strong> You have no pending payments at this time.
                      </div>
                    </v-alert>
                  </div>

                  <div v-if="applicationStatus === 'pending'" class="mt-4">
                    <v-alert type="warning" variant="tonal" class="mb-4" density="compact">
                      <div class="text-body-2">
                        <strong>Action Required:</strong> Please view and print the W9 form, then submit it to the office to complete your application approval.
                      </div>
                    </v-alert>
                    <v-btn
                      color="primary"
                      block
                      size="large"
                      prepend-icon="mdi-file-document"
                      @click="viewW9Form"
                      class="mb-3"
                      elevation="2"
                    >
                      View W9 Form
                    </v-btn>
                  </div>
                </v-card-text>
              </v-card>

            </v-col>
          </v-row>

          <!-- Past Bookings Section -->
          <v-row>
            <v-col cols="12">
              <v-card elevation="0">
                <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
                  <div>
                    <span class="section-title success--text">Past Bookings</span>
                    <div class="text-caption text-grey mt-1">View your completed contract details and time tracking history</div>
                  </div>
                  <v-chip color="primary" variant="outlined">
                    {{ pastBookings.length }} Total Bookings
                  </v-chip>
                </v-card-title>
                <v-card-text class="pa-0">
                  <v-data-table
                    :headers="pastBookingsHeaders"
                    :items="pastBookings"
                    :items-per-page="10"
                    class="elevation-0"
                  >
                    <template v-slot:item.client="{ item }">
                      <div class="d-flex align-center py-2">
                        <v-avatar color="primary" size="32" class="mr-3">
                          <span class="text-caption">{{ item.client.substring(0, 2).toUpperCase() }}</span>
                        </v-avatar>
                        <div>
                          <div class="font-weight-medium">{{ item.client }}</div>
                          <div class="text-caption text-grey">{{ item.service_type }}</div>
                        </div>
                      </div>
                    </template>

                    <template v-slot:item.dates="{ item }">
                      <div>
                        <div class="font-weight-medium">{{ item.start_date }}</div>
                        <div class="text-caption text-grey">{{ item.duration }}</div>
                      </div>
                    </template>

                    <template v-slot:item.hours="{ item }">
                      <div class="text-center">
                        <div class="font-weight-bold text-h6 success--text">{{ item.total_hours }}</div>
                        <div class="text-caption text-grey">hours logged</div>
                      </div>
                    </template>

                    <template v-slot:item.rate="{ item }">
                      <v-chip color="success" size="small" variant="elevated">
                        <v-icon start size="14">mdi-cash</v-icon>
                        ${{ item.assigned_rate }}/hr
                      </v-chip>
                    </template>

                    <template v-slot:item.earnings="{ item }">
                      <div class="text-right">
                        <div class="font-weight-bold text-h6 success--text">${{ item.total_earnings }}</div>
                        <div class="text-caption text-grey">{{ item.total_hours }}h × ${{ item.assigned_rate }}</div>
                      </div>
                    </template>

                    <template v-slot:item.status="{ item }">
                      <v-chip :color="item.payment_status === 'Paid' ? 'success' : 'warning'" size="small">
                        {{ item.payment_status }}
                      </v-chip>
                    </template>

                    <template v-slot:item.actions="{ item }">
                      <v-btn
                        size="small"
                        color="primary"
                        variant="text"
                        icon="mdi-eye"
                        @click="viewBookingDetails(item)"
                      >
                        <v-icon>mdi-eye</v-icon>
                        <v-tooltip activator="parent" location="top">View Details</v-tooltip>
                      </v-btn>
                    </template>

                    <template v-slot:no-data>
                      <div class="text-center py-12">
                        <v-icon size="80" color="grey-lighten-1" class="mb-4">mdi-calendar-blank</v-icon>
                        <h3 class="text-h6 mb-2 text-grey">No Past Bookings</h3>
                        <p class="text-body-2 text-grey">
                          Your completed bookings will appear here with time tracking details.
                        </p>
                      </div>
                    </template>
                  </v-data-table>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </div>

        <!-- Transaction History Section -->
        <div v-if="currentSection === 'transactions'">
          <v-card elevation="0" class="mb-6">
            <v-card-text class="pa-8">
              <div class="transaction-stats">
                <div class="stat-item">
                  <div class="stat-amount success--text">{{ stats[0].value }}</div>
                  <div class="stat-label-text">Total Earnings</div>
                </div>
                <v-divider vertical />
                <div class="stat-item">
                  <div class="stat-amount warning--text">${{ pendingBalance }}</div>
                  <div class="stat-label-text">Pending</div>
                </div>
                <v-divider vertical />
                <div class="stat-item">
                  <div class="stat-amount success--text">${{ paidOut }}</div>
                  <div class="stat-label-text">Paid Out</div>
                </div>
                <v-divider vertical />
                <div class="stat-item">
                  <div class="stat-amount info--text">{{ stats[1].value }}</div>
                  <div class="stat-label-text">This Month</div>
                </div>
              </div>
            </v-card-text>
          </v-card>

          <div class="mb-6 d-flex justify-space-between align-center">
            <v-row class="align-center flex-grow-1 mr-4">
              <v-col cols="12" md="3">
                <v-select v-model="transactionFilter" :items="['All Time', 'This Month', 'Last Month', 'Last 3 Months', 'This Year']" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="3">
                <v-select v-model="transactionType" :items="['All', 'Payment', 'Payout', 'Refund', 'Bonus']" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="3">
                <v-select v-model="transactionStatus" :items="['All', 'Completed', 'Pending', 'Failed']" variant="outlined" density="compact" hide-details />
              </v-col>
            </v-row>
            <v-btn color="success" prepend-icon="mdi-download" variant="outlined">Export CSV</v-btn>
          </div>

          <v-card elevation="0">
            <v-card-title class="card-header pa-8">
              <span class="section-title success--text">Transaction History</span>
            </v-card-title>
            <v-data-table :headers="transactionHeaders" :items="filteredTransactions" :items-per-page="15" class="elevation-0 table-no-checkbox">
              <template v-slot:item.type="{ item }">
                <div class="d-flex align-center">
                  <v-icon :color="getTransactionIcon(item.type).color" class="mr-2">{{ getTransactionIcon(item.type).icon }}</v-icon>
                  <span>{{ item.type }}</span>
                </div>
              </template>
              <template v-slot:item.amount="{ item }">
                <span :class="item.type === 'Payout' || item.type === 'Refund' ? 'text-error' : 'text-success'" class="font-weight-bold">
                  {{ item.type === 'Payout' || item.type === 'Refund' ? '-' : '+' }}${{ item.amount }}
                </span>
              </template>
              <template v-slot:item.status="{ item }">
                <v-chip :color="getTransactionStatusColor(item.status)" size="small" class="font-weight-bold">{{ item.status }}</v-chip>
              </template>
              <template v-slot:item.actions="{ item }">
                <v-btn size="small" color="success" variant="text" icon="mdi-receipt" @click="viewReceipt(item)" />
                <v-btn size="small" color="primary" variant="text" icon="mdi-download" @click="downloadReceipt(item)" />
              </template>
            </v-data-table>
          </v-card>
        </div>

        <!-- Earnings Report Section -->
        <div v-if="currentSection === 'analytics'">
          <!-- Earnings Report Header -->
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-6 d-flex justify-space-between align-center">
              <div>
                <span class="section-title success--text">Earnings Report</span>
                <div class="text-caption mt-1">View your earnings, hours worked, and payment history</div>
              </div>
              <div class="d-flex align-center ga-2">
                <v-select
                  v-model="earningsReportPeriod"
                  :items="['This Week', 'This Month', 'Last Month', 'This Year', 'All Time']"
                  variant="outlined"
                  density="compact"
                  hide-details
                  style="width: 150px;"
                />
                <v-btn color="success" prepend-icon="mdi-file-pdf-box" @click="exportEarningsReport" :loading="exportingEarningsReport">
                  Export PDF
                </v-btn>
              </div>
            </v-card-title>
          </v-card>

          <!-- Summary Stats Cards -->
          <v-row class="mb-6 earnings-stats-row">
            <v-col cols="6" md="3">
              <v-card elevation="0" class="stat-card-earnings">
                <v-card-text class="pa-4">
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <div class="text-caption text-grey">Total Earnings</div>
                      <div class="text-h5 font-weight-bold success--text">${{ earningsReportData.totalEarnings }}</div>
                      <div class="text-caption" :class="earningsReportData.earningsChange >= 0 ? 'text-success' : 'text-error'">
                        <v-icon size="12">{{ earningsReportData.earningsChange >= 0 ? 'mdi-arrow-up' : 'mdi-arrow-down' }}</v-icon>
                        {{ Math.abs(earningsReportData.earningsChange) }}% from last period
                      </div>
                    </div>
                    <v-avatar color="success" size="48" variant="tonal">
                      <v-icon>mdi-currency-usd</v-icon>
                    </v-avatar>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="6" md="3">
              <v-card elevation="0" class="stat-card-earnings">
                <v-card-text class="pa-4">
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <div class="text-caption text-grey">Hours Worked</div>
                      <div class="text-h5 font-weight-bold info--text">{{ earningsReportData.totalHours }}</div>
                      <div class="text-caption text-grey">
                        Avg {{ earningsReportData.avgHoursPerDay }} hrs/day
                      </div>
                    </div>
                    <v-avatar color="info" size="48" variant="tonal">
                      <v-icon>mdi-clock-outline</v-icon>
                    </v-avatar>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="6" md="3">
              <v-card
                elevation="2"
                class="stat-card-earnings cursor-pointer hover-highlight"
                @click="salaryRangeDialog = true"
                style="transition: all 0.3s ease;"
              >
                <v-card-text class="pa-4">
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <div class="text-caption text-grey d-flex align-center">
                        Preferred Salary Range
                        <v-icon size="small" color="warning" class="ml-1">mdi-pencil</v-icon>
                      </div>
                      <div class="text-h5 font-weight-bold warning--text">
                        {{ salaryRange.min && salaryRange.max ? `$${salaryRange.min} - $${salaryRange.max}` : 'Not Set' }}
                      </div>
                      <div class="text-caption text-grey">
                        {{ salaryRange.min && salaryRange.max ? 'Click to update' : 'Click to set your rate' }}
                      </div>
                    </div>
                    <v-avatar color="warning" size="48" variant="tonal">
                      <v-icon>mdi-cash-clock</v-icon>
                    </v-avatar>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="6" md="3">
              <v-card elevation="0" class="stat-card-earnings">
                <v-card-text class="pa-4">
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <div class="text-caption text-grey">Clients Served</div>
                      <div class="text-h5 font-weight-bold primary--text">{{ earningsReportData.clientsServed }}</div>
                      <div class="text-caption text-grey">
                        {{ earningsReportData.completedSessions }} sessions
                      </div>
                    </div>
                    <v-avatar color="primary" size="48" variant="tonal">
                      <v-icon>mdi-account-group</v-icon>
                    </v-avatar>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <v-row>
            <!-- Earnings Breakdown -->
            <v-col cols="12" md="5">
              <v-card elevation="0" class="mb-6">
                <v-card-title class="card-header pa-6">
                  <span class="section-title success--text">Earnings Breakdown</span>
                </v-card-title>
                <v-card-text class="pa-6">
                  <div class="earnings-breakdown-item mb-4">
                    <div class="d-flex justify-space-between align-center mb-2">
                      <span class="font-weight-medium">Gross Earnings</span>
                      <span class="text-h6 success--text">${{ earningsReportData.grossEarnings }}</span>
                    </div>
                    <v-progress-linear color="success" :model-value="100" height="8" rounded></v-progress-linear>
                  </div>

                  <v-divider class="my-4"></v-divider>

                  <div class="earnings-breakdown-detail mb-3">
                    <div class="d-flex justify-space-between">
                      <span class="text-grey">Regular Hours ({{ earningsReportData.regularHours }} hrs)</span>
                      <span>${{ earningsReportData.regularEarnings }}</span>
                    </div>
                  </div>
                  <div class="earnings-breakdown-detail mb-3">
                    <div class="d-flex justify-space-between">
                      <span class="text-grey">Overtime Hours ({{ earningsReportData.overtimeHours }} hrs)</span>
                      <span>${{ earningsReportData.overtimeEarnings }}</span>
                    </div>
                  </div>
                  <div class="earnings-breakdown-detail mb-3">
                    <div class="d-flex justify-space-between">
                      <span class="text-grey">Bonuses & Tips</span>
                      <span>${{ earningsReportData.bonuses }}</span>
                    </div>
                  </div>

                  <v-divider class="my-4"></v-divider>

                  <div class="earnings-breakdown-detail mb-3">
                    <div class="d-flex justify-space-between text-error">
                      <span>Estimated Tax (Self-Employment)</span>
                      <span>-${{ earningsReportData.estimatedTax }}</span>
                    </div>
                  </div>

                  <v-divider class="my-4"></v-divider>

                  <div class="earnings-breakdown-item">
                    <div class="d-flex justify-space-between align-center">
                      <span class="font-weight-bold text-h6">Net Earnings</span>
                      <span class="text-h5 success--text font-weight-bold">${{ earningsReportData.netEarnings }}</span>
                    </div>
                  </div>
                </v-card-text>
              </v-card>

              <!-- Time Tracking Summary -->
              <v-card elevation="0">
                <v-card-title class="card-header pa-6">
                  <span class="section-title success--text">Time Tracking Summary</span>
                </v-card-title>
                <v-card-text class="pa-6">
                  <div class="time-tracking-summary">
                    <div class="d-flex justify-space-between align-center mb-4">
                      <div>
                        <div class="text-caption text-grey">This Week</div>
                        <div class="text-h5 font-weight-bold">{{ timeTrackingSummary.weeklyHours }} hrs</div>
                      </div>
                      <v-avatar color="success" size="40" variant="tonal">
                        <v-icon size="20">mdi-clock-outline</v-icon>
                      </v-avatar>
                    </div>

                    <v-divider class="my-3"></v-divider>

                    <div class="d-flex justify-space-between align-center mb-4">
                      <div>
                        <div class="text-caption text-grey">This Month</div>
                        <div class="text-h5 font-weight-bold">{{ timeTrackingSummary.monthlyHours }} hrs</div>
                      </div>
                      <v-avatar color="info" size="40" variant="tonal">
                        <v-icon size="20">mdi-calendar-month</v-icon>
                      </v-avatar>
                    </div>

                    <v-divider class="my-3"></v-divider>

                    <div class="d-flex justify-space-between align-center mb-4">
                      <div>
                        <div class="text-caption text-grey">Total Sessions</div>
                        <div class="text-h5 font-weight-bold">{{ timeTrackingSummary.totalSessions }}</div>
                      </div>
                      <v-avatar color="warning" size="40" variant="tonal">
                        <v-icon size="20">mdi-briefcase-check</v-icon>
                      </v-avatar>
                    </div>

                    <v-divider class="my-3"></v-divider>

                    <div class="d-flex justify-space-between align-center">
                      <div>
                        <div class="text-caption text-grey">Average Daily</div>
                        <div class="text-h5 font-weight-bold">{{ timeTrackingSummary.avgDailyHours }} hrs</div>
                      </div>
                      <v-avatar color="primary" size="40" variant="tonal">
                        <v-icon size="20">mdi-chart-timeline-variant</v-icon>
                      </v-avatar>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>

            <!-- Earnings Chart & History -->
            <v-col cols="12" md="7">
              <v-card elevation="0" class="mb-6 earnings-chart-card">
                <v-card-title class="card-header pa-6 earnings-chart-header">
                  <span class="section-title success--text">Earnings Over Time</span>
                  <v-btn-toggle v-model="earningsChartPeriod" mandatory color="success" size="small" class="period-toggle">
                    <v-btn value="week">Week</v-btn>
                    <v-btn value="month">Month</v-btn>
                    <v-btn value="year">Year</v-btn>
                  </v-btn-toggle>
                </v-card-title>
                <v-card-text class="pa-6">
                  <div class="chart-stats-row mb-4">
                    <div class="chart-stat">
                      <div class="stat-value success--text">{{ earningsChartStats.total }}</div>
                      <div class="stat-label">Total {{ earningsChartPeriod === 'week' ? 'Week' : earningsChartPeriod === 'month' ? 'Month' : 'Year' }}</div>
                    </div>
                    <div class="chart-stat">
                      <div class="stat-value info--text">{{ earningsChartStats.average }}</div>
                      <div class="stat-label">Average</div>
                    </div>
                    <div class="chart-stat">
                      <div class="stat-value warning--text">{{ earningsChartStats.growth }}</div>
                      <div class="stat-label">Growth</div>
                    </div>
                  </div>
                  <div class="chart-container" style="height: 250px; position: relative;">
                    <canvas ref="earningsChart"></canvas>
                  </div>
                </v-card-text>
              </v-card>

              <!-- Recent Earnings History Table -->
              <v-card elevation="0">
                <v-card-title class="card-header pa-6">
                  <span class="section-title success--text">Recent Earnings History</span>
                </v-card-title>
                <v-data-table
                  :headers="earningsHistoryHeaders"
                  :items="earningsHistory"
                  :items-per-page="5"
                  class="elevation-0 table-no-checkbox"
                >
                  <template v-slot:item.date="{ item }">
                    <span class="font-weight-medium">{{ item.date }}</span>
                  </template>
                  <template v-slot:item.client="{ item }">
                    <div class="d-flex align-center">
                      <v-avatar size="28" color="primary" class="mr-2">
                        <span class="text-caption text-white">{{ item.clientInitials }}</span>
                      </v-avatar>
                      {{ item.client }}
                    </div>
                  </template>
                  <template v-slot:item.hours="{ item }">
                    <v-chip size="small" color="info" variant="tonal">{{ item.hours }} hrs</v-chip>
                  </template>
                  <template v-slot:item.amount="{ item }">
                    <span class="font-weight-bold success--text">${{ item.amount }}</span>
                  </template>
                  <template v-slot:item.status="{ item }">
                    <v-chip size="small" :color="item.status === 'Paid' ? 'success' : item.status === 'Pending' ? 'warning' : 'info'" variant="flat">
                      {{ item.status }}
                    </v-chip>
                  </template>
                </v-data-table>
              </v-card>
            </v-col>
          </v-row>
        </div>

        <!-- Notifications Section -->
        <div v-if="currentSection === 'notifications'">
          <notification-center user-type="caregiver" :user-id="2" @open-settings="notificationSettings = true" @action-clicked="handleAction" />
        </div>

        <!-- Profile Section -->
        <div v-if="currentSection === 'profile'">
          <v-row>
            <v-col cols="12" md="8">
              <v-card elevation="0" class="mb-6">
                <v-card-title class="card-header pa-8">
                  <span class="section-title success--text">Personal Information</span>
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
                      <v-text-field v-model="profile.phone" label="Phone" variant="outlined" type="tel" inputmode="tel" />
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
                  </v-row>
                  <v-btn color="success" class="mt-4" size="large" @click="saveProfileChanges">Save Changes</v-btn>
                </v-card-text>
              </v-card>

              <v-card elevation="0" class="mb-6">
                <v-card-title class="card-header pa-8">
                  <span class="section-title success--text">Professional Details</span>
                </v-card-title>
                <v-card-text class="pa-8">
                  <v-row>
                    <v-col cols="12" md="6">
                      <v-text-field v-model="profile.experience" label="Years of Experience" variant="outlined" type="number" />
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-select v-if="!isCustomTrainingCenter" v-model="profile.trainingCenter" :items="trainingCenterOptions" label="Training Center" variant="outlined" no-data-text="No CAS training centers yet. Use Custom Training Center below to enter yours." />
                      <v-text-field v-else v-model="profile.customTrainingCenter" label="Custom Training Center" variant="outlined" />
                      <v-alert v-if="profile.trainingCenter && trainingCenterApprovalStatus === 'pending'" type="info" density="compact" class="mt-2" variant="tonal" border="start">
                        <div class="d-flex align-center">
                          <v-icon size="20" class="mr-2" color="warning">mdi-clock-outline</v-icon>
                          <span>Your training center must approve your request. You'll see an update here once they respond.</span>
                        </div>
                      </v-alert>
                      <v-chip v-else-if="profile.trainingCenter && trainingCenterApprovalStatus === 'approved'" size="small" color="success" variant="tonal" class="mt-2">
                        <v-icon start size="16">mdi-check-circle</v-icon>
                        Training center approved
                      </v-chip>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-file-input v-model="profile.trainingCertificate" label="Training Certificate" variant="outlined" accept=".pdf,.jpg,.jpeg,.png" prepend-icon="mdi-certificate" hint="Accepted formats: PDF, JPG, PNG (Max 5MB)" persistent-hint />
                    </v-col>
                    <v-col cols="12" md="6" class="d-flex align-center">
                      <v-checkbox v-model="isCustomTrainingCenter" label="Custom Training Center" density="compact" hide-details />
                    </v-col>

                    <!-- Professional Certifications -->
                    <v-col cols="12">
                      <v-divider class="my-4" />
                      <div class="d-flex align-center mb-4 mt-2">
                        <v-icon color="success" size="28" class="mr-3">mdi-certificate</v-icon>
                        <div>
                          <h3 class="text-h6 font-weight-bold mb-1">Professional Certifications</h3>
                          <p class="text-caption text-grey-darken-1 ma-0">Enhance your profile by adding professional credentials</p>
                        </div>
                        <v-chip size="small" color="blue-grey-lighten-4" class="ml-auto">Optional</v-chip>
                      </div>
                    </v-col>

                    <!-- HHA Certification Card -->
                    <v-col cols="12" md="4">
                      <v-card
                        :elevation="profile.hasHHA ? 8 : 2"
                        :color="profile.hasHHA ? 'success' : 'grey-lighten-4'"
                        class="pa-4 h-100 transition-swing cursor-pointer"
                        hover
                        @click="profile.hasHHA = !profile.hasHHA"
                      >
                        <div class="d-flex align-center justify-space-between">
                          <div class="d-flex align-center">
                            <v-icon
                              :color="profile.hasHHA ? 'white' : 'success'"
                              size="40"
                              class="mr-3"
                            >
                              mdi-hospital-box
                            </v-icon>
                            <div>
                              <div :class="profile.hasHHA ? 'text-white font-weight-bold text-h6' : 'font-weight-bold text-h6'">
                                HHA
                              </div>
                              <div :class="profile.hasHHA ? 'text-white text-body-2' : 'text-grey-darken-2 text-body-2'">
                                Home Health Aide
                              </div>
                            </div>
                          </div>
                          <v-icon
                            :color="profile.hasHHA ? 'white' : 'success'"
                            size="32"
                          >
                            {{ profile.hasHHA ? 'mdi-checkbox-marked-circle' : 'mdi-checkbox-blank-circle-outline' }}
                          </v-icon>
                        </div>
                      </v-card>
                    </v-col>

                    <!-- CNA Certification Card -->
                    <v-col cols="12" md="4">
                      <v-card
                        :elevation="profile.hasCNA ? 8 : 2"
                        :color="profile.hasCNA ? 'success' : 'grey-lighten-4'"
                        class="pa-4 h-100 transition-swing cursor-pointer"
                        hover
                        @click="profile.hasCNA = !profile.hasCNA"
                      >
                        <div class="d-flex align-center justify-space-between">
                          <div class="d-flex align-center">
                            <v-icon
                              :color="profile.hasCNA ? 'white' : 'success'"
                              size="40"
                              class="mr-3"
                            >
                              mdi-stethoscope
                            </v-icon>
                            <div>
                              <div :class="profile.hasCNA ? 'text-white font-weight-bold text-h6' : 'font-weight-bold text-h6'">
                                CNA
                              </div>
                              <div :class="profile.hasCNA ? 'text-white text-body-2' : 'text-grey-darken-2 text-body-2'">
                                Certified Nursing Assistant
                              </div>
                            </div>
                          </div>
                          <v-icon
                            :color="profile.hasCNA ? 'white' : 'success'"
                            size="32"
                          >
                            {{ profile.hasCNA ? 'mdi-checkbox-marked-circle' : 'mdi-checkbox-blank-circle-outline' }}
                          </v-icon>
                        </div>
                      </v-card>
                    </v-col>

                    <!-- RN Certification Card -->
                    <v-col cols="12" md="4">
                      <v-card
                        :elevation="profile.hasRN ? 8 : 2"
                        :color="profile.hasRN ? 'success' : 'grey-lighten-4'"
                        class="pa-4 h-100 transition-swing cursor-pointer"
                        hover
                        @click="profile.hasRN = !profile.hasRN"
                      >
                        <div class="d-flex align-center justify-space-between">
                          <div class="d-flex align-center">
                            <v-icon
                              :color="profile.hasRN ? 'white' : 'success'"
                              size="40"
                              class="mr-3"
                            >
                              mdi-medical-bag
                            </v-icon>
                            <div>
                              <div :class="profile.hasRN ? 'text-white font-weight-bold text-h6' : 'font-weight-bold text-h6'">
                                RN
                              </div>
                              <div :class="profile.hasRN ? 'text-white text-body-2' : 'text-grey-darken-2 text-body-2'">
                                Registered Nurse
                              </div>
                            </div>
                          </div>
                          <v-icon
                            :color="profile.hasRN ? 'white' : 'success'"
                            size="32"
                          >
                            {{ profile.hasRN ? 'mdi-checkbox-marked-circle' : 'mdi-checkbox-blank-circle-outline' }}
                          </v-icon>
                        </div>
                      </v-card>
                    </v-col>

                    <v-col cols="12">
                      <v-textarea v-model="profile.bio" label="Bio" variant="outlined" rows="4" />
                    </v-col>
                  </v-row>
                  <v-btn color="success" class="mt-4" size="large" @click="saveProfileChanges">Update Profile</v-btn>
                </v-card-text>
              </v-card>

</v-col>

            <v-col cols="12" md="4">
              <v-card elevation="0" class="mb-6">
                <v-card-text class="pa-8 text-center">
                  <div class="position-relative d-inline-block mb-4 responsive-avatar">
                    <v-avatar color="success" class="w-100 h-auto" :size="$vuetify.display.xs ? 96 : 120">
                      <img v-if="userAvatar && userAvatar.length > 0"
                        :src="userAvatar"
                        :alt="`${profile.firstName} ${profile.lastName}'s profile photo`"
                        class="avatar-img-responsive"
                        @error="userAvatar = ''"
                      />
                      <span v-else class="text-h3 font-weight-bold text-white">{{ profile.firstName && profile.lastName ? `${profile.firstName[0]}${profile.lastName[0]}` : 'DC' }}</span>
                    </v-avatar>
                    <v-btn
                      icon
                      size="small"
                      color="success"
                      class="avatar-upload-btn"
                      style="position: absolute; bottom: 0; right: 0;"
                      aria-label="Upload profile photo"
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
                      aria-label="Select profile photo"
                    />
                  </div>
                  <h2 class="mb-2">{{ profile.firstName && profile.lastName ? `${profile.firstName} ${profile.lastName}` : 'Demo Caregiver' }}</h2>
                  <p class="text-grey mb-4">Professional Caregiver</p>
                  <v-chip color="success" class="mb-4">Active</v-chip>
                  <v-divider class="my-4" />

                  <div class="profile-stat">
                    <v-icon color="info" class="mr-2">mdi-calendar</v-icon>
                    <span>Member since {{ memberSince }}</span>
                  </div>
                  <v-divider class="my-4" />
                  <div class="text-center">
                    <h4 class="mb-3">Training Certificate</h4>
                    <div v-if="trainingCertificateUrl || profile.trainingCertificate" class="certificate-display">
                      <v-icon color="success" size="32" class="mb-2">mdi-certificate</v-icon>
                      <div class="text-caption">{{ profile.trainingCertificate?.[0]?.name || 'Certificate uploaded' }}</div>
                      <v-btn v-if="trainingCertificateUrl" size="small" color="success" variant="outlined" class="mt-2" :href="trainingCertificateUrl" target="_blank">View Certificate</v-btn>
                    </div>
                    <div v-else class="text-grey">
                      <v-icon color="grey" size="32" class="mb-2">mdi-certificate-outline</v-icon>
                      <div class="text-caption">No certificate uploaded</div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>

              <v-card elevation="0">
                <v-card-title class="card-header pa-8">
                  <div class="d-flex align-center">
                    <v-icon color="success" class="mr-3">mdi-lock-reset</v-icon>
                    <span class="section-title success--text">Change Password</span>
                  </div>
                </v-card-title>
                <v-card-text class="pa-8">
                  <v-text-field label="Current Password" variant="outlined" :type="showCurrentPassword ? 'text' : 'password'" :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showCurrentPassword = !showCurrentPassword" class="mb-4" />
                  <v-text-field label="New Password" variant="outlined" :type="showNewPassword ? 'text' : 'password'" :append-inner-icon="showNewPassword ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showNewPassword = !showNewPassword" hint="8 minimum characters" persistent-hint class="mb-4" />
                  <v-text-field label="Confirm New Password" variant="outlined" :type="showConfirmPassword ? 'text' : 'password'" :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showConfirmPassword = !showConfirmPassword" class="mb-4" />
                  <v-btn color="success" block size="large">Change Password</v-btn>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </div>

    <!-- Day Details Modal -->
    <v-dialog v-model="dayModal" max-width="600">
      <v-card v-if="selectedDate">
        <v-card-title class="card-header pa-6">
          <span class="section-title success--text">{{ selectedDate.fullDate }}</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <div v-if="selectedDate.events.length === 0" class="text-center py-8 text-grey">
            <v-icon size="48" color="grey-lighten-1">mdi-calendar-blank</v-icon>
            <div class="mt-3">No appointments scheduled</div>
          </div>
          <div v-else>
            <v-list>
              <v-list-item v-for="(event, idx) in selectedDate.events" :key="idx" class="mb-3 event-item">
                <template v-slot:prepend>
                  <v-avatar :color="getEventColor(event.status)" size="40">
                    <v-icon color="white">mdi-calendar-clock</v-icon>
                  </v-avatar>
                </template>
                <v-list-item-title class="font-weight-bold">{{ event.client }}</v-list-item-title>
                <v-list-item-subtitle>{{ event.service }}</v-list-item-subtitle>
                <v-list-item-subtitle class="mt-1">
                  <v-icon size="small">mdi-clock-outline</v-icon> {{ event.time }}
                </v-list-item-subtitle>
                <v-list-item-subtitle class="mt-1">
                  <v-chip :color="getEventColor(event.status)" size="small" class="font-weight-bold">{{ event.status }}</v-chip>
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </div>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="success" variant="outlined" @click="dayModal = false">Close</v-btn>
          <v-btn color="success" @click="dayModal = false">Add Appointment</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Stripe Connect Bank Account Onboarding - No manual card entry needed -->
    <!-- Caregivers use Stripe Connect to receive payouts, not add credit cards -->

    <!-- Add Appointment Dialog -->
    <v-dialog v-model="addAppointmentDialog" max-width="600">
      <v-card>
        <v-card-title class="card-header pa-6">
          <span class="section-title success--text">New Appointment</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12">
              <v-select :items="availableClients.map(c => c.name)" label="Select Client" variant="outlined" />
            </v-col>
            <v-col cols="12">
              <v-select :items="careTypes.filter(t => t !== 'All')" label="Service Type" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field type="date" label="Date" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field type="time" label="Time" variant="outlined" />
            </v-col>
            <v-col cols="12">
              <v-textarea label="Notes" variant="outlined" rows="3" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="addAppointmentDialog = false">Cancel</v-btn>
          <v-btn color="success" @click="addAppointmentDialog = false">Create Appointment</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Notification Settings Dialog -->
    <v-dialog v-model="notificationSettings" max-width="600">
      <v-card>
        <v-card-title class="card-header pa-6">
          <span class="section-title success--text">Notification Settings</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <div class="mb-4">
            <h3 class="mb-3">Email Notifications</h3>
            <v-switch v-model="settings.emailAppointments" label="Appointment reminders" color="success" />
            <v-switch v-model="settings.emailPayments" label="Payment notifications" color="success" />
            <v-switch v-model="settings.emailSystem" label="System updates" color="success" />
          </div>
          <div class="mb-4">
            <h3 class="mb-3">Push Notifications</h3>
            <v-switch v-model="settings.pushAppointments" label="Appointment alerts" color="success" />
            <v-switch v-model="settings.pushMessages" label="New messages" color="success" />
            <v-switch v-model="settings.pushEmergency" label="Emergency alerts" color="success" />
          </div>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="notificationSettings = false">Cancel</v-btn>
          <v-btn color="success" @click="saveSettings">Save Settings</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Client Profile Modal -->
    <client-profile-modal v-model="clientProfileModal" :client="selectedClient" />

    <!-- Contact Dialog -->
    <v-dialog v-model="contactDialog" max-width="500">
      <v-card>
        <v-card-title>Contact Support</v-card-title>
        <v-card-text>
          <v-list>
            <v-list-item prepend-icon="mdi-email">
              <v-list-item-title>Email Support</v-list-item-title>
              <v-list-item-subtitle>support@casprivatecare.com</v-list-item-subtitle>
            </v-list-item>
            <v-list-item prepend-icon="mdi-phone">
              <v-list-item-title>Phone Support</v-list-item-title>
              <v-list-item-subtitle>(212) 555-0123</v-list-item-subtitle>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="success" @click="contactDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Salary Range Modal -->
    <v-dialog v-model="salaryRangeDialog" max-width="500">
      <v-card>
        <v-card-title class="text-h5 bg-success text-white">
          <v-icon class="mr-2">mdi-cash</v-icon>
          Preferred Salary Range
        </v-card-title>
        <v-card-text class="pt-4">
          <p class="text-body-1 mb-4">Set your preferred hourly rate range ($20 - $50)</p>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model.number="salaryRange.min"
                label="Minimum Rate"
                type="number"
                prefix="$"
                suffix="/hr"
                variant="outlined"
                :rules="[
                  v => !!v || 'Minimum rate is required',
                  v => v >= 20 || 'Minimum must be at least $20',
                  v => v <= 50 || 'Maximum allowed is $50',
                  v => v <= salaryRange.max || 'Min must be less than max'
                ]"
                min="20"
                max="50"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model.number="salaryRange.max"
                label="Maximum Rate"
                type="number"
                prefix="$"
                suffix="/hr"
                variant="outlined"
                :rules="[
                  v => !!v || 'Maximum rate is required',
                  v => v >= 20 || 'Minimum allowed is $20',
                  v => v <= 50 || 'Maximum must be at most $50',
                  v => v >= salaryRange.min || 'Max must be greater than min'
                ]"
                min="20"
                max="50"
              />
            </v-col>
          </v-row>

          <v-alert type="info" variant="tonal" class="mt-2">
            <div class="text-body-2">
              <strong>Current Range:</strong> ${{ salaryRange.min || 20 }} - ${{ salaryRange.max || 50 }}/hr
            </div>
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="grey" variant="text" @click="salaryRangeDialog = false">Cancel</v-btn>
          <v-btn color="success" variant="elevated" @click="saveSalaryRange" :loading="savingSalaryRange">
            <v-icon left>mdi-content-save</v-icon>
            Save Range
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Remove Payout Method Dialog -->
    <v-dialog v-model="showRemovePayoutDialog" max-width="450">
      <v-card>
        <v-card-title class="pa-4 bg-error text-white">
          <v-icon class="mr-2">mdi-delete-alert</v-icon>
          Remove Payout Method
        </v-card-title>
        <v-card-text class="pa-4">
          <p class="text-body-1 mb-4">Are you sure you want to remove your connected payout method?</p>
          <v-alert type="warning" variant="tonal" density="compact" class="mb-4">
            <div class="text-body-2">
              <strong>Warning:</strong> You will not be able to receive payments until you connect a new payout method.
            </div>
          </v-alert>
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="showRemovePayoutDialog = false">Cancel</v-btn>
          <v-btn color="error" variant="flat" :loading="removingPayout" @click="removePayoutMethod">
            <v-icon left>mdi-delete</v-icon>
            Remove Method
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Notification Toast -->
    <notification-toast v-model="notification.show" :type="notification.type" :title="notification.title" :message="notification.message" :timeout="notification.timeout" />

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
            color="success"
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
  </dashboard-template>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import DashboardTemplate from './DashboardTemplate.vue';
import StatCard from './shared/StatCard.vue';
import ClientProfileModal from './shared/ClientProfileModal.vue';
import NotificationCenter from './shared/NotificationCenter.vue';
import AlertModal from './shared/AlertModal.vue';
import NotificationToast from './shared/NotificationToast.vue';
import EmailVerificationModal from './EmailVerificationModal.vue';
import { useEmailVerification } from '../composables/useEmailVerification';
import TaxPayrollSection from './TaxPayrollSection.vue';
import LoadingOverlay from './LoadingOverlay.vue';
import OnboardingProgress from './shared/OnboardingProgress.vue';
import { useNotification } from '../composables/useNotification.js';
import { useNYLocationData } from '../composables/useNYLocationData.js';

const { notification, success } = useNotification();
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

const caregiverId = ref(null);
const userEmailVerified = ref(false);

// Error Modal State
const showErrorModal = ref(false);
const errorMessages = ref([]);

const currentSection = ref(localStorage.getItem('caregiverSection') || 'dashboard');
const contactDialog = ref(false);
const salaryRangeDialog = ref(false);
const savingSalaryRange = ref(false);
const salaryRange = ref({
  min: 20,
  max: 50
});
const clientProfileModal = ref(false);
const selectedClient = ref(null);
const dayModal = ref(false);
const addAppointmentDialog = ref(false);
// addPaymentDialog removed - caregivers use Stripe Connect for bank accounts, not manual card entry
const selectedDate = ref(null);

const appointmentFilter = ref('All');
const appointmentStatus = ref('All');
const appointmentSearch = ref('');
const transactionFilter = ref('All Time');
const transactionType = ref('All');
const transactionStatus = ref('All');
const earningsChartPeriod = ref('month');
const earningsChart = ref(null);
const servicesChart = ref(null);
const clientsChart = ref(null);

// Payout settings (v1.5.0: frequency only Weekly)
const payoutFrequency = ref('Weekly');
const payoutMethod = ref('Bank Transfer');
const applicationStatus = ref('pending'); // 'pending' or 'approved'

const handleRequestPayout = async () => {
  // Minimal local handler - show notification and (optionally) call API
  success('Payout request sent', 'Your payout request has been submitted and will be processed shortly.');
  // Example API call (commented out):
  // await fetch(`/api/caregiver/${caregiverId.value}/request-payout`, { method: 'POST' });
};

const viewW9Form = () => {
  // Open W9 form PDF in new tab
  window.open('/pdfs/form-w-9.pdf', '_blank');
};

const checkApplicationStatus = async () => {
  try {
    const response = await fetch('/api/caregiver/application-status');
    const data = await response.json();
    // Set application status: 'pending' or 'approved'
    if (data.status) {
      applicationStatus.value = data.status.toLowerCase();
    } else if (data.application) {
      // Check if application exists and is approved
      applicationStatus.value = (data.application.status && data.application.status.toLowerCase() === 'approved') ? 'approved' : 'pending';
    } else {
      // Default to pending if no application found
      applicationStatus.value = 'pending';
    }
  } catch (error) {
    // Default to pending on error
    applicationStatus.value = 'pending';
  }
};

const handlePayout = async () => {
  // Minimal handler for Payout action - in real app this would be restricted/admin action
  notification.type = 'info';
  notification.title = 'Payout initiated';
  notification.message = 'Payout is being processed.';
  notification.show = true;
};

const availableClientSearch = ref('');
const availableCountyFilter = ref('All');
const availableCityFilter = ref('All');
const availableDateFilter = ref('All');
const availableView = ref('grid');

const availableClients = ref([]);

const loadAvailableClients = async () => {
  try {
    const response = await fetch('/api/available-clients');

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    // Use the transformed data directly from the API
    if (Array.isArray(data) && data.length > 0) {
      availableClients.value = data;
    } else {
      availableClients.value = [];
    }
  } catch (error) {
    availableClients.value = [];
  }
};

const jobListingHeaders = [
  { title: 'Client', key: 'client' },
  { title: 'Service', key: 'service' },
  { title: 'Location', key: 'location' },
  { title: 'Start Date', key: 'dates' },
  { title: 'Compensation', key: 'compensation' },
  { title: 'Spots', key: 'spots' },
  { title: 'Status', key: 'status' },
];

const weekDays = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
const fullWeekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const currentMonth = ref('December 2024');
const currentYear = ref(2024);
const currentMonthIndex = ref(11);
const scheduleMonth = computed(() => {
  const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  return `${monthNames[currentMonthIndex.value]} ${currentYear.value}`;
});

const scheduleEvents = ref({});

const loadScheduleEvents = async () => {
  try {
    const month = currentMonthIndex.value + 1; // API expects 1-12
    const year = currentYear.value;
    const response = await fetch(`/api/caregiver/schedule-events?caregiver_id=${caregiverId.value}&month=${month}&year=${year}`);
    const data = await response.json();
    scheduleEvents.value = data.events || {};
  } catch (error) {
    scheduleEvents.value = {};
  }
};

const scheduleCalendarDates = computed(() => {
  const dates = [];
  const today = new Date();
  const year = currentYear.value;
  const month = currentMonthIndex.value;
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const prevMonthDays = new Date(year, month, 0).getDate();
  const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

  for (let i = firstDay - 1; i >= 0; i--) {
    dates.push({ day: prevMonthDays - i, isCurrentMonth: false, isToday: false, events: [], key: `prev-${i}` });
  }

  for (let i = 1; i <= daysInMonth; i++) {
    const isToday = today.getDate() === i && today.getMonth() === month && today.getFullYear() === year;
    const events = scheduleEvents.value[i] || [];
    const fullDate = `${monthNames[month]} ${i}, ${year}`;
    dates.push({ day: i, isCurrentMonth: true, isToday, events, fullDate, key: `curr-${i}` });
  }

  const remainingDays = 42 - dates.length;
  for (let i = 1; i <= remainingDays; i++) {
    dates.push({ day: i, isCurrentMonth: false, isToday: false, events: [], key: `next-${i}` });
  }

  return dates;
});

const changeMonth = (direction) => {
  currentMonthIndex.value += direction;
  if (currentMonthIndex.value > 11) {
    currentMonthIndex.value = 0;
    currentYear.value++;
  } else if (currentMonthIndex.value < 0) {
    currentMonthIndex.value = 11;
    currentYear.value--;
  }
  // Reload events for new month
  loadScheduleEvents();
};

const openDayModal = (date) => {
  if (!date.isCurrentMonth) return;
  selectedDate.value = date;
  dayModal.value = true;
};

const getEventColor = (status) => {
  const colors = {
    'scheduled': 'info',
    'completed': 'success',
    'confirmed': 'primary',
    'cancelled': 'error',
  };
  return colors[status] || 'grey';
};

const calendarDates = computed(() => {
  const dates = [];
  const today = new Date();
  const year = 2024;
  const month = 11; // December (0-indexed)
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const prevMonthDays = new Date(year, month, 0).getDate();

  // Previous month days
  for (let i = firstDay - 1; i >= 0; i--) {
    dates.push({ day: prevMonthDays - i, isCurrentMonth: false, isToday: false, hasAppointment: false, key: `prev-${i}` });
  }

  // Current month days
  for (let i = 1; i <= daysInMonth; i++) {
    const isToday = today.getDate() === i && today.getMonth() === month && today.getFullYear() === year;
    const hasAppointment = [15, 18, 22, 25].includes(i); // Sample appointment dates
    dates.push({ day: i, isCurrentMonth: true, isToday, hasAppointment, key: `curr-${i}` });
  }

  // Next month days
  const remainingDays = 42 - dates.length;
  for (let i = 1; i <= remainingDays; i++) {
    dates.push({ day: i, isCurrentMonth: false, isToday: false, hasAppointment: false, key: `next-${i}` });
  }

  return dates;
});

const sidebarNotifications = ref([]);
const sidebarUnreadCount = computed(() => sidebarNotifications.value.filter(n => !n.read).length);

// SECURITY: Check if contractor is approved to access restricted features
const isApproved = computed(() => applicationStatus.value === 'approved');

// Navigation items - all accessible regardless of approval status
const navItems = computed(() => [
  { icon: 'mdi-view-dashboard', title: 'Dashboard', value: 'dashboard', disabled: false },
  { icon: 'mdi-bell', title: 'Notifications', value: 'notifications', badge: sidebarUnreadCount.value > 0, disabled: false },
  {
    icon: 'mdi-credit-card',
    title: 'Payment Information',
    value: 'payment',
    category: 'FINANCIAL',
    disabled: false
  },
  {
    icon: 'mdi-history',
    title: 'Transaction History',
    value: 'transactions',
    category: 'FINANCIAL',
    disabled: false
  },
  {
    icon: 'mdi-account-search',
    title: 'Job Listings',
    value: 'available-clients',
    category: 'WORK',
    disabled: false
  },
  {
    icon: 'mdi-chart-bar',
    title: 'Earnings Report',
    value: 'analytics',
    category: 'WORK',
    disabled: false
  },
  { icon: 'mdi-account-circle', title: 'Profile (1099 Contractors)', value: 'profile', category: 'ACCOUNT', disabled: false }
]);

const loadSidebarNotificationCount = async () => {
  try {
    const response = await fetch('/api/notifications?user_id=2');
    const data = await response.json();
    sidebarNotifications.value = data.notifications || [];
  } catch (error) {
  }
};

// Earnings Report Data
const earningsReportPeriod = ref('This Month');
const exportingEarningsReport = ref(false);
const earningsReportData = ref({
  totalEarnings: '0.00',
  earningsChange: 0,
  totalHours: '0',
  avgHoursPerDay: '0',
  hourlyRate: '28.00',
  clientsServed: 0,
  completedSessions: 0,
  grossEarnings: '0.00',
  regularHours: '0',
  regularEarnings: '0.00',
  overtimeHours: '0',
  overtimeEarnings: '0.00',
  bonuses: '0.00',
  estimatedTax: '0.00',
  netEarnings: '0.00',
  onTimeRate: 98,
  completionRate: 100
});

// Time Tracking Summary for Earnings Report
const timeTrackingSummary = ref({
  weeklyHours: '0.0',
  monthlyHours: '0.0',
  totalSessions: 0,
  avgDailyHours: '0.0'
});

const earningsHistoryHeaders = [
  { title: 'Date', key: 'date' },
  { title: 'Client', key: 'client' },
  { title: 'Hours', key: 'hours' },
  { title: 'Amount', key: 'amount' },
  { title: 'Status', key: 'status' }
];

const earningsHistory = ref([]);

const loadEarningsReportData = async () => {
  try {
    const response = await fetch(`/api/caregiver/${caregiverId.value}/earnings-report?period=${encodeURIComponent(earningsReportPeriod.value)}`);
    const data = await response.json();

    if (data.success) {
      earningsReportData.value = {
        totalEarnings: data.totalEarnings || '0.00',
        earningsChange: data.earningsChange || 0,
        totalHours: data.totalHours || '0',
        avgHoursPerDay: data.avgHoursPerDay || '0',
        hourlyRate: data.hourlyRate || '28.00',
        clientsServed: data.clientsServed || 0,
        completedSessions: data.completedSessions || 0,
        grossEarnings: data.grossEarnings || '0.00',
        regularHours: data.regularHours || '0',
        regularEarnings: data.regularEarnings || '0.00',
        overtimeHours: data.overtimeHours || '0',
        overtimeEarnings: data.overtimeEarnings || '0.00',
        bonuses: data.bonuses || '0.00',
        estimatedTax: data.estimatedTax || '0.00',
        netEarnings: data.netEarnings || '0.00',
        onTimeRate: data.onTimeRate || 98,
        completionRate: data.completionRate || 100
      };
      earningsHistory.value = data.history || [];

      // Update time tracking summary
      timeTrackingSummary.value = {
        weeklyHours: data.weeklyHours || '0.0',
        monthlyHours: data.monthlyHours || '0.0',
        totalSessions: data.completedSessions || 0,
        avgDailyHours: data.avgHoursPerDay || '0.0'
      };
    }
  } catch (error) {
    // Clear data on error
    earningsHistory.value = [];
  }
};

const exportEarningsReport = async () => {
  exportingEarningsReport.value = true;
  try {
    const response = await fetch('/api/caregiver/earnings-report-pdf', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        caregiverId: caregiverId.value,
        period: earningsReportPeriod.value,
        data: earningsReportData.value,
        history: earningsHistory.value
      })
    });

    if (response.ok) {
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `earnings_report_${new Date().toISOString().split('T')[0]}.pdf`;
      a.click();
      window.URL.revokeObjectURL(url);
    } else {
      alert('Failed to generate PDF report');
    }
  } catch (error) {
    alert('Failed to export earnings report');
  } finally {
    exportingEarningsReport.value = false;
  }
};

// Watch for period changes
watch(earningsReportPeriod, () => {
  loadEarningsReportData();
});

const isLoadingStats = ref(false);
const currentClient = ref('N/A');
const contractStartDate = ref('');
const contractEndDate = ref('');
const timeIn = ref('');
const timeOut = ref('');
const isTimedIn = ref(false);
const currentBookingServiceDate = ref(null);
const currentBookingStartTime = ref(null);
const currentBookingDurationDays = ref(null);
const currentBookingDaySchedules = ref(null);
const currentBookingStatus = ref(null);

// Helper function to format contract dates
const formatContractDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

// Computed properties to check booking status
const isBookingActive = computed(() => {
  // Check booking status first (must be 'approved' or 'active')
  if (currentBookingStatus.value && !['approved', 'active'].includes(currentBookingStatus.value)) {
    return false;
  }

  if (!currentBookingServiceDate.value || !currentBookingDurationDays.value) {
    return false;
  }

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const serviceDate = new Date(currentBookingServiceDate.value);
  serviceDate.setHours(0, 0, 0, 0);

  const endDate = new Date(serviceDate);
  endDate.setDate(endDate.getDate() + parseInt(currentBookingDurationDays.value));

  // Booking is active if today is between service_date and end_date
  return serviceDate <= today && today <= endDate;
});

const hasStartTimePassed = computed(() => {
  const now = new Date();
  const today = now.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase(); // monday, tuesday, etc.

  // Check if booking has day_schedules (new system)
  if (currentBookingDaySchedules.value && typeof currentBookingDaySchedules.value === 'object') {
    // Check if caregiver is assigned to work today
    if (!currentBookingDaySchedules.value[today]) {
      return false; // Not assigned to work today
    }

    // Parse today's schedule (e.g., "11:00 AM - 11:00 PM")
    const todaySchedule = currentBookingDaySchedules.value[today];
    const scheduleMatch = todaySchedule.match(/(\d+:\d+\s*[AP]M)\s*-\s*(\d+:\d+\s*[AP]M)/);

    if (scheduleMatch) {
      const startTimeStr = scheduleMatch[1]; // e.g., "11:00 AM"

      // Parse the time
      const timeMatch = startTimeStr.match(/(\d+):(\d+)\s*([AP]M)/);
      if (timeMatch) {
        let hour = parseInt(timeMatch[1]);
        const minute = parseInt(timeMatch[2]);
        const period = timeMatch[3];

        // Convert to 24-hour format
        if (period === 'PM' && hour !== 12) {
          hour += 12;
        } else if (period === 'AM' && hour === 12) {
          hour = 0;
        }

        // Create start time for today
        const startTime = new Date();
        startTime.setHours(hour, minute, 0, 0);

        // Check if current time >= start time
        return now >= startTime;
      }
    }

    return false;
  }

  // Fallback to old system (single start_time for all days)
  if (!currentBookingServiceDate.value || !currentBookingStartTime.value) {
    return false;
  }

  const serviceDate = new Date(currentBookingServiceDate.value);

  // Parse start_time (format: HH:MM:SS or HH:MM)
  const timeParts = currentBookingStartTime.value.split(':');
  const startHour = parseInt(timeParts[0]) || 0;
  const startMinute = parseInt(timeParts[1]) || 0;

  // Create a date object for the booking start date and time
  const bookingStartDateTime = new Date(serviceDate);
  bookingStartDateTime.setHours(startHour, startMinute, 0, 0);

  // Check if current time is >= booking start time
  return now >= bookingStartDateTime;
});

const canClockIn = computed(() => {
  return currentClient.value !== 'N/A' && isBookingActive.value && hasStartTimePassed.value && !isTimedIn.value;
});

const bookingStatusMessage = computed(() => {
  if (currentClient.value === 'N/A') {
    return 'No active client assigned';
  }

  // Check booking status
  if (currentBookingStatus.value && !['approved', 'active'].includes(currentBookingStatus.value)) {
    return `Booking is ${currentBookingStatus.value}`;
  }

  if (!currentBookingServiceDate.value) {
    return 'Ready to start your shift';
  }

  const serviceDate = new Date(currentBookingServiceDate.value);
  const today = new Date();
  const todayDayName = today.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
  today.setHours(0, 0, 0, 0);
  serviceDate.setHours(0, 0, 0, 0);

  if (serviceDate > today) {
    // Booking hasn't started yet
    const startStr = serviceDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    return `Booking starts on ${startStr}`;
  }

  if (!isBookingActive.value) {
    return 'Booking has ended';
  }

  // Check if using day_schedules system
  if (currentBookingDaySchedules.value && typeof currentBookingDaySchedules.value === 'object') {
    // Check if assigned to work today
    if (!currentBookingDaySchedules.value[todayDayName]) {
      return 'Not assigned to work today';
    }

    // Get today's schedule
    const todaySchedule = currentBookingDaySchedules.value[todayDayName];
    const scheduleMatch = todaySchedule.match(/(\d+:\d+\s*[AP]M)\s*-\s*(\d+:\d+\s*[AP]M)/);

    if (scheduleMatch && !hasStartTimePassed.value) {
      return `Shift starts at ${scheduleMatch[1]}`;
    }
  } else if (!hasStartTimePassed.value && currentBookingStartTime.value) {
    // Fallback to old system
    // Parse start_time
    const timeParts = currentBookingStartTime.value.split(':');
    const startHour = parseInt(timeParts[0]) || 0;
    const startMinute = parseInt(timeParts[1]) || 0;
    const ampm = startHour >= 12 ? 'PM' : 'AM';
    const displayHour = startHour % 12 || 12;
    const displayMinute = startMinute.toString().padStart(2, '0');
    return `Shift starts at ${displayHour}:${displayMinute} ${ampm}`;
  }

  return 'Ready to start your shift';
});

const handleTimeIn = async () => {
  try {
    if (!caregiverId.value) {
      alert('Caregiver ID not found. Please refresh the page.');
      return;
    }

    const response = await fetch('/api/time-tracking/clock-in', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        caregiver_id: caregiverId.value,
        location: 'Client Home'
      })
    });

    // Check if response is JSON
    const contentType = response.headers.get('content-type');
    let data;

    if (contentType && contentType.includes('application/json')) {
      data = await response.json();
    } else {
      // If not JSON, read as text to see what the error is
      const text = await response.text();
      throw new Error(`Server error (${response.status}): Please check your connection and try again.`);
    }

    if (response.ok) {
      success('Successfully clocked in!', 'Time Tracking');
      // Refresh current session and week history to show the new entry
      await loadCurrentSession();
      await loadWeekHistory();
    } else {
      alert(data.error || data.message || 'Failed to clock in');
    }
  } catch (error) {
    alert('Error clocking in: ' + (error.message || 'Please try again.'));
  }
};

const handleTimeOut = async () => {
  try {
    if (!caregiverId.value) {
      alert('Caregiver ID not found. Please refresh the page.');
      return;
    }

    const response = await fetch('/api/time-tracking/clock-out', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        caregiver_id: caregiverId.value
      })
    });

    // Check if response is JSON
    const contentType = response.headers.get('content-type');
    let data;

    if (contentType && contentType.includes('application/json')) {
      data = await response.json();
    } else {
      // If not JSON, read as text to see what the error is
      const text = await response.text();
      throw new Error(`Server error (${response.status}): Please check your connection and try again.`);
    }

    if (response.ok) {
      // Don't set timeOut manually - let loadCurrentSession handle the state
      success('Successfully clocked out!', 'Time Tracking');
      // Refresh current session and week history to show the updated entry
      await loadCurrentSession();
      await loadWeekHistory();
    } else {
      alert(data.error || data.message || 'Failed to clock out');
    }
  } catch (error) {
    alert('Error clocking out: ' + (error.message || 'Please try again.'));
  }
};

const stats = ref([
  { title: 'Current Client', value: 'N/A', icon: 'mdi-account', color: 'grey', change: 'Status: No Contract', changeColor: 'text-grey', changeIcon: 'mdi-close-circle' },
  { title: 'Hourly Rate', value: '$0', icon: 'mdi-currency-usd', color: 'grey', change: 'Per hour', changeColor: 'text-info', changeIcon: 'mdi-clock-outline' },
  { title: 'Weekly Earnings', value: '$0', icon: 'mdi-currency-usd', color: 'grey', change: '0% vs last week', changeColor: 'text-grey', changeIcon: 'mdi-trending-neutral' }
]);

const weeklyEarnings = ref('0.00');
const weeklyProgress = ref(0);
const pendingBalance = ref('0.00');
const monthlyDetails = ref({
  gross: '0.00',
  platformFee: '0.00',
  taxes: '0.00',
  net: '0.00'
});
const quickStats = ref({
  rating: '0.0',
  hours: '0',
  clients: '0'
});
const earningsChartStats = ref({
  total: '$0',
  average: '$0',
  growth: '+0%'
});
const topClientsTotal = computed(() => {
  return topClients.value.reduce((sum, client) => sum + parseInt(client.revenue || 0), 0);
});
const paidOut = ref('0.00');
const trainingCertificateUrl = ref(null);
const isLoadingProfile = ref(false);

const loadProfile = async () => {
  try {
    // Load training centers first to ensure dropdown has the options
    if (trainingCenters.value.length === 0) {
      await loadTrainingCenters();
    }

    const response = await fetch('/api/profile?user_type=caregiver', { credentials: 'same-origin' });
    const data = await response.json();
    // API returns { success, message, data: { user, caregiver } }; support both wrapped and unwrapped
    const payload = data.data ?? data;
    const user = payload?.user;
    if (user) {
      isLoadingProfile.value = true;
      // Set user ID for avatar upload
      caregiverUserId.value = user.id;

      // Set email verification status
      userEmailVerified.value = user.email_verified_at !== null && user.email_verified_at !== undefined;

      // Set avatar if exists
      if (user.avatar) {
        userAvatar.value = user.avatar.startsWith('/') ? user.avatar : '/storage/' + user.avatar;
      } else {
      }

      const nameParts = user.name.split(' ');

      // Prefer explicit caregiver from API (getProfile returns { user, caregiver }); fallback to user.caregiver
      const caregiverData = data.caregiver ?? payload?.caregiver ?? user?.caregiver ?? {};

      console.log('🔍 Loading certifications from API:', {
        has_hha: caregiverData.has_hha,
        hha_type: typeof caregiverData.has_hha,
        has_cna: caregiverData.has_cna,
        cna_type: typeof caregiverData.has_cna,
        has_rn: caregiverData.has_rn,
        rn_type: typeof caregiverData.has_rn
      });

      // Preserve training center if API omits it (e.g. after save); normalize "x x" -> "x"
      const apiTc = (caregiverData.training_center_name ?? '').toString().trim();
      const savedTrainingCenter = normalizeTrainingCenterLabel(apiTc || (profile.value?.trainingCenter ?? '')) || apiTc || (profile.value?.trainingCenter ?? '');
      profile.value = {
        firstName: nameParts[0] || '',
        lastName: nameParts.slice(1).join(' ') || '',
        email: user.email || '',
        phone: user.phone || '',
        address: user.address || '',
        city: user.city || '',
        county: user.borough || user.county || '',
        state: user.state || '',
        zip: user.zip_code || '',
        birthdate: user.date_of_birth || '',
        experience: caregiverData.years_experience || '',
        trainingCenter: savedTrainingCenter,
        customTrainingCenter: '',
        trainingCertificate: null,
        specializations: caregiverData.specializations || [],
        bio: caregiverData.bio || '',
        hasHHA: Boolean(caregiverData.has_hha),
        hhaNumber: caregiverData.hha_number || '',
        hasCNA: Boolean(caregiverData.has_cna),
        cnaNumber: caregiverData.cna_number || '',
        hasRN: Boolean(caregiverData.has_rn),
        rnNumber: caregiverData.rn_number || '',
        created_at: user.created_at || ''
      };

      // Load salary range
      salaryRange.value = {
        min: caregiverData.preferred_hourly_rate_min || 20,
        max: caregiverData.preferred_hourly_rate_max || 50
      };

      console.log('✅ Profile loaded with certifications:', {
        hasHHA: profile.value.hasHHA,
        hasCNA: profile.value.hasCNA,
        hasRN: profile.value.hasRN
      });

      // Load training certificate if exists
      // NOTE: /profile returns the caregiver object under `data.caregiver`.
      // Older code sometimes expected `data.user.caregiver`, so we normalize via caregiverData above.
      if (caregiverData?.training_certificate) {
        trainingCertificateUrl.value = caregiverData.training_certificate.startsWith('/')
          ? caregiverData.training_certificate
          : '/storage/' + caregiverData.training_certificate;
      } else {
        trainingCertificateUrl.value = null;
      }

      // Store caregiver ID for stats loading
      if (caregiverData?.id) {
        caregiverId.value = caregiverData.id;
      }

      trainingCenterApprovalStatus.value = caregiverData.training_center_approval_status ?? trainingCenterApprovalStatus.value ?? '';

      // Set W9 submitted status from user data
      // If user is approved, W9 is considered submitted (approval requires physical W9 submission)
      w9Submitted.value = Boolean(user.w9_submitted) || user.status === 'Active';

      // Check application status
      await checkApplicationStatus();
      await nextTick();
      isLoadingProfile.value = false;
    } else if (data.error === 'User not authenticated') {
      // Fallback for demo purposes - use Demo Caregiver ID
      caregiverId.value = 25;
      caregiverUserId.value = null;
      profile.value = {
        firstName: 'Demo',
        lastName: 'Caregiver',
        email: 'caregiver@demo.com',
        phone: '',
        address: '',
        city: '',
        county: '',
        state: '',
        zip: '',
        birthdate: '',
        ssn: '',
        itin: '',
        experience: '5',
        trainingCenter: '',
        customTrainingCenter: '',
        trainingCertificate: null,
        specializations: [],
        bio: 'Demo caregiver account'
      };
    }
  } catch (error) {
    // Fallback for demo purposes
    caregiverId.value = 25;
    caregiverUserId.value = null;
    isLoadingProfile.value = false;
  }
};

const loadCaregiverStats = async () => {
  try {
    isLoadingStats.value = true;

    if (!caregiverId.value) {
      return;
    }

    const response = await fetch(`/api/caregiver/${caregiverId.value}/stats`);
    const data = await response.json();

    // Check multiple possible data structures
    let hasActiveClient = false;
    let clientName = 'N/A';

    if (data.active_assignments && data.active_assignments.length > 0) {
      // Get the first assignment (prioritize active over upcoming)
      const assignment = data.active_assignments[0];

      // Try different possible structures
      if (assignment.booking?.client?.name) {
        clientName = assignment.booking.client.name;
        hasActiveClient = true;

        // Store booking details for clock in/out validation
        currentBookingServiceDate.value = assignment.booking.service_date || null;
        currentBookingStartTime.value = assignment.booking.start_time || null;
        currentBookingDurationDays.value = assignment.booking.duration_days || null;
        currentBookingDaySchedules.value = assignment.booking.day_schedules || null;
        currentBookingStatus.value = assignment.booking.status || null;

        // Get contract dates from booking
        if (assignment.booking.service_date) {
          contractStartDate.value = formatContractDate(assignment.booking.service_date);

          // Check if this is an upcoming assignment (service_date in future)
          const serviceDate = new Date(assignment.booking.service_date);
          const today = new Date();
          today.setHours(0, 0, 0, 0);
          serviceDate.setHours(0, 0, 0, 0);

          if (serviceDate > today) {
            // This is an upcoming assignment, show "Starts: [date]"
            const startStr = serviceDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            contractStartDate.value = `Starts: ${startStr}`;
          }
        }
        if (assignment.booking.end_date) {
          contractEndDate.value = formatContractDate(assignment.booking.end_date);
        } else if (assignment.booking.duration_days && assignment.booking.service_date) {
          // Calculate end date from start date + duration
          const startDate = new Date(assignment.booking.service_date);
          startDate.setDate(startDate.getDate() + parseInt(assignment.booking.duration_days));
          contractEndDate.value = formatContractDate(startDate.toISOString().split('T')[0]);
        }
      } else if (assignment.client?.name) {
        clientName = assignment.client.name;
        hasActiveClient = true;
      } else if (assignment.client_name) {
        clientName = assignment.client_name;
        hasActiveClient = true;
      } else if (assignment.name) {
        clientName = assignment.name;
        hasActiveClient = true;
      }

      // Try to get dates from assignment directly
      if (assignment.start_date) {
        contractStartDate.value = formatContractDate(assignment.start_date);
      }
      if (assignment.end_date) {
        contractEndDate.value = formatContractDate(assignment.end_date);
      }
    }

    // Also check if there's a direct client field
    if (!hasActiveClient && data.client) {
      if (data.client.name) {
        clientName = data.client.name;
        hasActiveClient = true;
      }
    }

    // Note: The /api/caregiver/{id}/assignments endpoint does not exist
    // The active_assignments data should already be included in the /api/caregiver/{id}/stats response
    // If no active client was found from the stats response, show N/A

if (hasActiveClient) {
      const contractInfo = contractStartDate.value && contractEndDate.value
        ? `${contractStartDate.value} - ${contractEndDate.value}`
        : 'Status: Active Contract';
      stats.value[0] = {
        title: 'Current Client',
        value: clientName,
        icon: 'mdi-account',
        color: 'success',
        change: contractInfo,
        changeColor: 'text-success',
        changeIcon: 'mdi-calendar-range'
      };
      currentClient.value = clientName;
    } else {
      stats.value[0] = {
        title: 'Current Client',
        value: 'N/A',
        icon: 'mdi-account',
        color: 'grey',
        change: 'Status: No Contract',
        changeColor: 'text-grey',
        changeIcon: 'mdi-close-circle'
      };
      currentClient.value = 'N/A';
      contractStartDate.value = '';
      contractEndDate.value = '';
      currentBookingServiceDate.value = null;
      currentBookingStartTime.value = null;
      currentBookingDurationDays.value = null;
      currentBookingDaySchedules.value = null;
      currentBookingStatus.value = null;
    }
  } catch (error) {
    stats.value[0] = {
      title: 'Current Client',
      value: 'N/A',
      icon: 'mdi-account',
      color: 'grey',
      change: 'Status: No Contract',
      changeColor: 'text-grey',
      changeIcon: 'mdi-close-circle'
    };
    currentClient.value = 'N/A';
    contractStartDate.value = '';
    contractEndDate.value = '';
  } finally {
    isLoadingStats.value = false;

    // Update Hourly Rate to show preferred salary range
    const salaryRangeText = salaryRange.value.min && salaryRange.value.max
      ? `$${salaryRange.value.min} - $${salaryRange.value.max}`
      : 'Not Set';
    const salaryRangeSubtext = salaryRange.value.min && salaryRange.value.max
      ? 'Click to update'
      : 'Click to set your rate';

    stats.value[1] = {
      title: 'Preferred Salary Range',
      value: salaryRangeText,
      icon: 'mdi-cash-clock',
      color: 'warning',
      change: salaryRangeSubtext,
      changeColor: 'text-grey',
      changeIcon: 'mdi-pencil',
      clickable: true,
      onClick: () => { salaryRangeDialog.value = true; }
    };

    // Calculate weekly earnings from time tracking
    try {
      const earningsResponse = await fetch(`/api/caregiver/${caregiverId.value}/earnings-report?period=This Week`);
      const earningsData = await earningsResponse.json();
      if (earningsData.success) {
        const weeklyTotal = parseFloat(earningsData.totalEarnings.replace(',', '')) || 0;
        const weeklyHrs = parseFloat(earningsData.weeklyHours) || 0;
        const lastWeekEarnings = weeklyTotal * 0.88; // Approximate last week for comparison
        const changePercent = lastWeekEarnings > 0 ? Math.round(((weeklyTotal - lastWeekEarnings) / lastWeekEarnings) * 100) : 0;
        const changeSign = changePercent >= 0 ? '+' : '';

        stats.value[2] = {
          title: 'Weekly Earnings',
          value: '$' + weeklyTotal.toFixed(2),
          icon: 'mdi-currency-usd',
          color: weeklyTotal > 0 ? 'success' : 'grey',
          change: `${changeSign}${changePercent}% vs last week`,
          changeColor: changePercent >= 0 ? 'text-success' : 'text-error',
          changeIcon: changePercent >= 0 ? 'mdi-trending-up' : 'mdi-trending-down'
        };

        // Note: accountBalance will be updated from payment summary API
        // which includes all pending earnings, not just this week
      }

      // Load previous week data
      const prevWeekResponse = await fetch(`/api/caregiver/${caregiverId.value}/earnings-report?period=Last Week`);
      const prevWeekData = await prevWeekResponse.json();
      if (prevWeekData.success) {
        const prevHours = parseFloat(prevWeekData.totalHours) || 0;
        const prevEarnings = parseFloat(prevWeekData.totalEarnings.replace(',', '')) || 0;
        previousWeekHours.value = prevHours.toFixed(0);
        previousWeekProgress.value = Math.min(100, (prevHours / 40) * 100);
        previousWeekPayout.value = prevEarnings.toFixed(2);
      }
    } catch (earningsError) {
    }
  }
};

const activeClientsHeaders = [
  { title: 'Client Name', key: 'name' },
  { title: 'Care Type', key: 'careType' },
  { title: 'Last Visit', key: 'lastVisit' },
  { title: 'Status', key: 'status' },
];

const activeClientsList = ref([]);

const notificationFilter = ref('All');
const notificationType = ref('All');
const notificationSearch = ref('');
const notificationSettings = ref(false);

const notificationStats = ref([
  { title: 'Total', count: 24, icon: 'mdi-bell', color: 'primary' },
  { title: 'Unread', count: 8, icon: 'mdi-bell-badge', color: 'success' },
  { title: 'High Priority', count: 3, icon: 'mdi-alert', color: 'error' },
  { title: 'Today', count: 5, icon: 'mdi-calendar-today', color: 'warning' }
]);

const allNotifications = ref([]);

const loadNotifications = async () => {
  try {
    const response = await fetch('/api/notifications/2');
    const data = await response.json();
    if (data.notifications) {
      allNotifications.value = data.notifications.map(n => {
        const createdAt = new Date(n.created_at);
        const now = new Date();
        const diffMs = now - createdAt;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        let timeAgo = '';
        if (diffMins < 60) timeAgo = `${diffMins} minutes ago`;
        else if (diffHours < 24) timeAgo = `${diffHours} hours ago`;
        else timeAgo = `${diffDays} days ago`;

        const iconMap = {
          'Appointments': 'mdi-calendar',
          'Payments': 'mdi-check-circle',
          'Clients': 'mdi-account-plus',
          'System': 'mdi-file-chart'
        };
        const colorMap = {
          'Appointments': 'warning',
          'Payments': 'success',
          'Clients': 'info',
          'System': 'purple'
        };

        return {
          id: n.id,
          title: n.title,
          message: n.message,
          time: timeAgo,
          icon: iconMap[n.type] || 'mdi-bell',
          color: colorMap[n.type] || 'info',
          type: n.type,
          typeColor: colorMap[n.type] || 'info',
          read: n.read,
          priority: n.priority,
          actions: [{ label: 'View Details', color: 'primary' }]
        };
      });
    }
  } catch (error) {
  }
};

const upcomingReminders = ref([
  { id: 1, title: 'Appointment with John Doe', time: 'Today at 2:00 PM', icon: 'mdi-calendar', color: 'warning' },
  { id: 2, title: 'Medication for Linda Johnson', time: 'Today at 3:00 PM', icon: 'mdi-pill', color: 'error' },
  { id: 3, title: 'Weekly timesheet due', time: 'Tomorrow', icon: 'mdi-clock', color: 'info' },
  { id: 4, title: 'CPR certification renewal', time: 'Next week', icon: 'mdi-heart-pulse', color: 'success' }
]);

const notifications = computed(() => allNotifications.value.slice(0, 3));

const filteredNotifications = computed(() => {
  return allNotifications.value.filter(n => {
    const matchesFilter = notificationFilter.value === 'All' ||
      (notificationFilter.value === 'Unread' && !n.read) ||
      (notificationFilter.value === 'Today' && n.time.includes('hour')) ||
      (notificationFilter.value === 'This Week' && !n.time.includes('week'));
    const matchesType = notificationType.value === 'All' || n.type === notificationType.value;
    const matchesSearch = !notificationSearch.value ||
      n.title.toLowerCase().includes(notificationSearch.value.toLowerCase()) ||
      n.message.toLowerCase().includes(notificationSearch.value.toLowerCase());
    return matchesFilter && matchesType && matchesSearch;
  });
});

const unreadCount = computed(() => allNotifications.value.filter(n => !n.read).length);
const highPriorityCount = computed(() => allNotifications.value.filter(n => n.priority === 'high').length);
const todayCount = computed(() => allNotifications.value.filter(n => n.time.includes('hour') || n.time.includes('minute')).length);

const markAsRead = (notification) => {
  notification.read = true;
};

const markAllRead = () => {
  allNotifications.value.forEach(n => n.read = true);
};

const clearAll = () => {
  allNotifications.value.splice(0);
};

const handleAction = (action) => {
};

const showNotificationMenu = (notification) => {
};

const settings = ref({
  emailAppointments: true,
  emailPayments: true,
  emailSystem: false,
  pushAppointments: true,
  pushMessages: true,
  pushEmergency: true
});

const saveSettings = () => {
  notificationSettings.value = false;
};

const viewClientProfile = (client) => {
  selectedClient.value = client;
  clientProfileModal.value = true;
};

const careTypes = ['All', 'Elderly Care', 'Personal Care', 'Companion Care', 'Physical Therapy'];

const availableFilterCities = computed(() => {
  if (!availableCountyFilter.value || availableCountyFilter.value === 'All') {
    return ['All'];
  }
  const cities = getCitiesForCounty(availableCountyFilter.value);
  return ['All', ...cities];
});

const onCountyFilterChange = () => {
  availableCityFilter.value = 'All';
};

const filteredAvailableClients = computed(() => {
  return availableClients.value.filter(job => {
    // Search by client name, service type, or location
    const searchTerm = availableClientSearch.value.toLowerCase();
    const matchesSearch = !searchTerm ||
      job.clientName?.toLowerCase().includes(searchTerm) ||
      job.serviceType?.toLowerCase().includes(searchTerm) ||
      job.location?.toLowerCase().includes(searchTerm) ||
      job.city?.toLowerCase().includes(searchTerm);

    // Filter by county (check location field or extract from city)
    let matchesCounty = true;
    if (availableCountyFilter.value && availableCountyFilter.value !== 'All') {
      const jobCounty = job.county || job.borough || extractCountyFromLocation(job.location);
      matchesCounty = jobCounty?.toLowerCase() === availableCountyFilter.value.toLowerCase();
    }

    // Filter by city
    let matchesCity = true;
    if (availableCityFilter.value && availableCityFilter.value !== 'All') {
      matchesCity = job.city?.toLowerCase() === availableCityFilter.value.toLowerCase();
    }

    // Filter by date
    let matchesDate = true;
    if (availableDateFilter.value && availableDateFilter.value !== 'All') {
      let startDate = null;

      // Try to get the raw service_date first (more reliable)
      if (job.service_date) {
        startDate = new Date(job.service_date);
      } else if (job.startDate) {
        // Parse formatted date like "Dec 17, 2025"
        startDate = parseFormattedDate(job.startDate);
      }

      if (!startDate || isNaN(startDate.getTime())) {
        matchesDate = false;
      } else {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        startDate.setHours(0, 0, 0, 0);

        const weekFromNow = new Date(today);
        weekFromNow.setDate(today.getDate() + 7);
        weekFromNow.setHours(23, 59, 59, 999);

        const twoWeeksFromNow = new Date(today);
        twoWeeksFromNow.setDate(today.getDate() + 14);
        twoWeeksFromNow.setHours(23, 59, 59, 999);

        if (availableDateFilter.value === 'This Week') {
          matchesDate = startDate >= today && startDate <= weekFromNow;
        } else if (availableDateFilter.value === 'Soon') {
          matchesDate = startDate >= today && startDate <= twoWeeksFromNow;
        }
      }
    }

    return matchesSearch && matchesCounty && matchesCity && matchesDate;
  });
});

// Helper function to extract county from location string
const extractCountyFromLocation = (location) => {
  if (!location) return null;
  // Try to find matching county in the location string
  const locationLower = location.toLowerCase();
  for (const county of counties.value) {
    if (locationLower.includes(county.toLowerCase())) {
      return county;
    }
  }
  return null;
};

// Helper function to parse formatted date string like "Dec 17, 2025"
const parseFormattedDate = (dateStr) => {
  if (!dateStr) return null;

  // Try standard Date parsing first
  let date = new Date(dateStr);
  if (!isNaN(date.getTime())) {
    return date;
  }

  // If that fails, try manual parsing for "M d, Y" format
  const months = {
    'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5,
    'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11
  };

  const parts = dateStr.trim().split(/[\s,]+/);
  if (parts.length === 3) {
    const monthName = parts[0];
    const day = parseInt(parts[1], 10);
    const year = parseInt(parts[2], 10);

    if (months.hasOwnProperty(monthName) && !isNaN(day) && !isNaN(year)) {
      return new Date(year, months[monthName], day);
    }
  }

  return null;
};

const currentTime = ref(new Date());

const weeklySchedule = ref([
  { day: 'Sunday', assigned: false, client: null, start_time: null, end_time: null },
  { day: 'Monday', assigned: false, client: null, start_time: null, end_time: null },
  { day: 'Tuesday', assigned: false, client: null, start_time: null, end_time: null },
  { day: 'Wednesday', assigned: false, client: null, start_time: null, end_time: null },
  { day: 'Thursday', assigned: false, client: null, start_time: null, end_time: null },
  { day: 'Friday', assigned: false, client: null, start_time: null, end_time: null },
  { day: 'Saturday', assigned: false, client: null, start_time: null, end_time: null }
]);
const loadingWeeklySchedule = ref(false);

const loadWeeklySchedule = async () => {
  try {
    if (!caregiverId.value) return;

    loadingWeeklySchedule.value = true;
    const response = await fetch(`/api/caregiver/${caregiverId.value}/weekly-schedule`, {
      credentials: 'include'
    });
    const data = await response.json();

    if (data.success && data.data?.weekly_schedule) {
      weeklySchedule.value = data.data.weekly_schedule;
    } else if (data.weekly_schedule) {
      weeklySchedule.value = data.weekly_schedule;
    }
  } catch (error) {
    console.error('Error loading weekly schedule:', error);
  } finally {
    loadingWeeklySchedule.value = false;
  }
};

const isToday = (dayName) => {
  const today = new Date();
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  return days[today.getDay()].toLowerCase() === dayName.toLowerCase();
};

const formatTime = (time) => {
  if (!time) return '';
  try {
    // Handle various time formats
    if (time.includes('T')) {
      const date = new Date(time);
      return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
  } catch {
    return time;
  }
};

// Keep weekHistory for backwards compatibility
const weekHistory = ref([]);

const loadWeekHistory = async () => {
  // Now loading weekly schedule instead
  await loadWeeklySchedule();
};

const loadCurrentSession = async () => {
  try {
    if (!caregiverId.value) return;

    const response = await fetch(`/api/time-tracking/current-session/${caregiverId.value}`);
    const data = await response.json();

if (data.active_session) {
      isTimedIn.value = true;
      // Parse the clock_in_time from the database
      const clockInTime = new Date(data.active_session.clock_in_time);

      // Format the time for display
      timeIn.value = clockInTime.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
      });

    } else {
      isTimedIn.value = false;
      timeIn.value = '';
    }
  } catch (error) {
    isTimedIn.value = false;
  }
};

const currentDate = computed(() => {
  return currentTime.value.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  });
});

const nextFridayDate = computed(() => {
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
  try {
    const today = currentTime.value;
    const dayOfWeek = today.getDay();
    const daysSinceFriday = (dayOfWeek + 2) % 7;
    const startFriday = new Date(today);
    startFriday.setDate(today.getDate() - daysSinceFriday);
    const endThursday = new Date(startFriday);
    endThursday.setDate(startFriday.getDate() + 6);
    const formatDate = (date) => date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    return `${formatDate(startFriday)} - ${formatDate(endThursday)}`;
  } catch (error) {
    return 'Dec 19 - Dec 25';
  }
});

// Account balance - calculated from weekly earnings
const accountBalance = ref('0.00');

// Today's date formatted
const todayFormatted = computed(() => {
  return currentTime.value.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  });
});

// Next payout date (next Friday)
const nextPayoutDate = computed(() => {
  const today = currentTime.value;
  const dayOfWeek = today.getDay();
  let daysUntilFriday = (5 - dayOfWeek + 7) % 7;
  if (daysUntilFriday === 0) daysUntilFriday = 7; // If today is Friday, next payout is next Friday
  const nextFriday = new Date(today);
  nextFriday.setDate(today.getDate() + daysUntilFriday);
  return nextFriday.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  });
});

// Previous week summary data
const previousWeekHours = ref('0');
const previousWeekProgress = ref(0);
const previousWeekPayout = ref('0.00');
const previousPayoutDate = computed(() => {
  // Previous Friday (last payout date)
  const today = currentTime.value;
  const dayOfWeek = today.getDay();
  const daysSinceLastFriday = (dayOfWeek + 2) % 7 || 7;
  const lastFriday = new Date(today);
  lastFriday.setDate(today.getDate() - daysSinceLastFriday);
  return lastFriday.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
});

const getStatusColor = (status) => {
  const colors = {
    'Completed': 'success',
    'Scheduled': 'info',
    'Confirmed': 'primary',
    'Pending': 'warning',
  };
  return colors[status] || 'grey';
};

const logout = () => {
  window.location.href = '/login';
};

// Section change: FINANCIAL and WORK (Payment, Transaction History, Job Listings, Earnings Report) are available to all contractors including pending
const handleSectionChange = (section) => {
  currentSection.value = section;
};

// SECURITY: Handle clicks on disabled nav items
const handleDisabledNavClick = (sectionValue) => {
  notification.type = 'warning';
  notification.title = 'Approval Required';
  notification.message = 'This feature requires account approval. Please submit your W9 form and wait for admin approval.';
  notification.show = true;
};

const filterClients = () => {
  // Filters are reactive
};

const resetAvailableFilters = () => {
  availableClientSearch.value = '';
  availableCountyFilter.value = 'All';
  availableCityFilter.value = 'All';
  availableDateFilter.value = 'All';
};

const applyForClient = async (job) => {
  try {
    const bookingId = job.bookingId || job.id;
    const response = await fetch(`/api/apply-client/${bookingId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    });

    const data = await response.json();

    if (response.ok) {
      alert(`Successfully applied for booking: ${job.clientName || job.name}\n${job.serviceType} • ${job.durationDays} days • ${job.location}`);
      // Reload available bookings to refresh the list
      await loadAvailableClients();
    } else {
      alert(data.error || 'Failed to apply for this job');
    }
  } catch (error) {
    alert('Error applying for this job. Please try again.');
  }
};

const appointments = ref([
  { id: 1, client: 'John Doe', initials: 'JD', avatarColor: 'success', service: 'Elderly Care', date: 'Dec 15, 2024', time: '9:00 AM', status: 'Scheduled', duration: '2 hours' },
  { id: 2, client: 'Sarah Williams', initials: 'SW', avatarColor: 'primary', service: 'Personal Care', date: 'Dec 15, 2024', time: '2:00 PM', status: 'Confirmed', duration: '1.5 hours' },
  { id: 3, client: 'Robert Chen', initials: 'RC', avatarColor: 'success', service: 'Elderly Care', date: 'Dec 16, 2024', time: '10:00 AM', status: 'Scheduled', duration: '2 hours' },
  { id: 4, client: 'Emma Garcia', initials: 'EG', avatarColor: 'purple', service: 'Companion Care', date: 'Dec 16, 2024', time: '3:00 PM', status: 'Confirmed', duration: '3 hours' },
  { id: 5, client: 'Michael Davis', initials: 'MD', avatarColor: 'orange', service: 'Physical Therapy', date: 'Dec 17, 2024', time: '11:00 AM', status: 'Scheduled', duration: '1 hour' },
  { id: 6, client: 'Linda Johnson', initials: 'LJ', avatarColor: 'primary', service: 'Personal Care', date: 'Dec 18, 2024', time: '1:00 PM', status: 'Scheduled', duration: '2 hours' },
  { id: 7, client: 'John Doe', initials: 'JD', avatarColor: 'success', service: 'Elderly Care', date: 'Dec 12, 2024', time: '9:00 AM', status: 'Completed', duration: '2 hours' },
  { id: 8, client: 'Sarah Williams', initials: 'SW', avatarColor: 'primary', service: 'Personal Care', date: 'Dec 10, 2024', time: '2:00 PM', status: 'Completed', duration: '1.5 hours' },
  { id: 9, client: 'Robert Chen', initials: 'RC', avatarColor: 'success', service: 'Elderly Care', date: 'Dec 8, 2024', time: '10:00 AM', status: 'Completed', duration: '2 hours' },
  { id: 10, client: 'Linda Johnson', initials: 'LJ', avatarColor: 'primary', service: 'Personal Care', date: 'Dec 24, 2024', time: '1:00 PM', status: 'Cancelled', duration: '2 hours' },
]);

const appointmentHeaders = [
  { title: 'Client', key: 'client' },
  { title: 'Service', key: 'service' },
  { title: 'Date', key: 'date' },
  { title: 'Time', key: 'time' },
  { title: 'Duration', key: 'duration' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const filteredAppointments = computed(() => {
  return appointments.value.filter(a => {
    const matchesSearch = !appointmentSearch.value || a.client.toLowerCase().includes(appointmentSearch.value.toLowerCase());
    const matchesStatus = appointmentStatus.value === 'All' || a.status === appointmentStatus.value;
    return matchesSearch && matchesStatus;
  });
});

const viewAppointment = (item) => {
};

const editAppointment = (item) => {
};

const deleteAppointment = (item) => {
};

// Payment methods - will be loaded dynamically from Stripe
const paymentMethods = ref([]);

// Payment summary variables - all dynamic from database
const totalEarnings = ref('0.00');
const pendingEarnings = ref('0.00');
const lastPaymentAmount = ref('0.00');
const stripeConnected = ref(false);
const stripeOnboardingComplete = ref(false);
const connectingBank = ref(false);
const showRemovePayoutDialog = ref(false);
const removingPayout = ref(false);

// Onboarding progress tracking
const w9Submitted = ref(false);
const profileComplete = computed(() => {
  return profile.value.firstName && profile.value.lastName && profile.value.phone;
});
const showOnboardingProgress = ref(true);

// Handle onboarding step clicks
const handleOnboardingStepClick = (step) => {
  switch (step.action) {
    case 'view-application':
      // Already on dashboard, show status
      break;
    case 'submit-w9':
      viewW9Form();
      break;
    case 'connect-bank':
      currentSection.value = 'payment';
      break;
    case 'edit-profile':
      currentSection.value = 'profile';
      break;
    default:
      break;
  }
};

// Stripe Connect Functions
const connectBankAccount = async () => {
  try {
    connectingBank.value = true;

    // Navigate to YOUR custom styled page (like payment page)
    window.location.href = '/connect-bank-account';

  } catch (error) {
    alert('An error occurred. Please try again.');
    connectingBank.value = false;
  }
};

const reconnectBankAccount = async () => {
  await connectBankAccount();
};

const openStripeDashboard = () => {
  // Open Stripe Express Dashboard for caregiver to manage their account
  window.open('https://dashboard.stripe.com/connect/accounts', '_blank');
};

const removePayoutMethod = async () => {
  try {
    removingPayout.value = true;

    const response = await fetch('/api/caregiver/payout-method', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'include'
    });

    if (response.ok) {
      stripeConnected.value = false;
      stripeOnboardingComplete.value = false;
      showRemovePayoutDialog.value = false;
      success('Payout method removed successfully', 'Payout Method');
    } else {
      const data = await response.json();
      throw new Error(data.message || 'Failed to remove payout method');
    }
  } catch (error) {
    console.error('Error removing payout method:', error);
    notification.value = {
      show: true,
      type: 'error',
      title: 'Error',
      message: error.message || 'Failed to remove payout method',
      timeout: 5000
    };
  } finally {
    removingPayout.value = false;
    showRemovePayoutDialog.value = false;
  }
};

const setDefaultCard = (card) => {
};

const editCard = (card) => {
};

const deleteCard = (card) => {
};

// Transactions - loaded dynamically from time_trackings table
const transactions = ref([]);

const transactionHeaders = [
  { title: 'Date', key: 'date' },
  { title: 'Type', key: 'type' },
  { title: 'Description', key: 'description' },
  { title: 'Amount', key: 'amount' },
  { title: 'Status', key: 'status' },
  { title: 'Method', key: 'method' },
  { title: 'Actions', key: 'actions', sortable: false },
];

// Past Bookings Headers
const pastBookingsHeaders = [
  { title: 'Client & Service', key: 'client', sortable: true },
  { title: 'Contract Period', key: 'dates', sortable: true },
  { title: 'Hours Logged', key: 'hours', align: 'center', sortable: true },
  { title: 'Hourly Rate', key: 'rate', align: 'center', sortable: true },
  { title: 'Total Earnings', key: 'earnings', align: 'end', sortable: true },
  { title: 'Payment Status', key: 'status', sortable: true },
  { title: 'Actions', key: 'actions', align: 'center', sortable: false },
];

// Past Bookings Data - loaded from API
const pastBookings = ref([]);

const filteredTransactions = computed(() => {
  return transactions.value.filter(t => {
    const matchesType = transactionType.value === 'All' || t.type === transactionType.value;
    const matchesStatus = transactionStatus.value === 'All' || t.status === transactionStatus.value;
    return matchesType && matchesStatus;
  });
});

const getTransactionIcon = (type) => {
  const icons = {
    'Payment': { icon: 'mdi-cash-plus', color: 'success' },
    'Payout': { icon: 'mdi-bank-transfer-out', color: 'error' },
    'Refund': { icon: 'mdi-cash-refund', color: 'warning' },
    'Bonus': { icon: 'mdi-gift', color: 'info' },
  };
  return icons[type] || { icon: 'mdi-cash', color: 'grey' };
};

const getTransactionStatusColor = (status) => {
  const colors = {
    'Completed': 'success',
    'Pending': 'warning',
    'Failed': 'error',
  };
  return colors[status] || 'grey';
};

const viewReceipt = (item) => {
};

// Load all payment data dynamically from database
const loadPaymentData = async () => {
  try {
    const response = await fetch('/api/caregiver/payment-data');
    const data = await response.json();

    if (data.success) {
      // Update payment summary
      if (data.payment_summary) {
        accountBalance.value = data.payment_summary.account_balance;
        totalEarnings.value = data.payment_summary.total_earnings;
        pendingEarnings.value = data.payment_summary.pending_earnings;
        lastPaymentAmount.value = data.payment_summary.last_payment_amount;

        // Update transaction page stats
        pendingBalance.value = data.payment_summary.pending_earnings;
        paidOut.value = data.payment_summary.total_earnings;

        // Update last payment info
        if (data.payment_summary.last_payment_amount !== '0.00') {
        }
      }

      // Update transactions with real data from time_trackings
      if (data.transactions && data.transactions.length > 0) {
        transactions.value = data.transactions;
      } else {
        transactions.value = [];
      }

      // Update Stripe connection status
      if (data.stripe_info) {
        stripeConnected.value = data.stripe_info.connected;
        stripeOnboardingComplete.value = data.stripe_info.onboarding_complete;

        if (!data.stripe_info.connected) {
          // Show connect bank account prompt
          paymentMethods.value = [];
        } else {
          // Update paymentMethods to show connected bank account
          paymentMethods.value = [{
            id: 'stripe_bank',
            type: 'bank_account',
            icon: 'mdi-bank',
            last4: 'Connected',
            holder: profile.value.name || 'Bank Account',
            isDefault: true,
            brandName: 'Stripe Bank Transfer'
          }];
        }
      }

      // Update statistics
      if (data.statistics) {
        Object.assign(earningStats.value, {
          total_hours: data.statistics.total_hours_worked,
          sessions: data.statistics.total_sessions,
          paid_sessions: data.statistics.paid_sessions,
          pending_sessions: data.statistics.pending_sessions,
          avg_hours: data.statistics.average_hours_per_session
        });
      }

    } else {
    }
  } catch (error) {
  }
};

const loadPaymentMethods = async () => {
  // This function is now replaced by loadPaymentData which loads everything
  // Keeping for backward compatibility but it will be called by loadPaymentData
  await loadPaymentData();
};

// Load Past Bookings from API
const loadPastBookings = async () => {
  try {
    const response = await fetch('/api/caregiver/past-bookings');
    const data = await response.json();

    if (data.success && data.bookings) {
      pastBookings.value = data.bookings;
    }
  } catch (error) {
    console.error('Error loading past bookings:', error);
    pastBookings.value = [];
  }
};

// View booking details
const viewBookingDetails = (booking) => {
  // Could open a modal with detailed booking information
  console.log('View booking details:', booking);
};

const downloadReceipt = (item) => {
};

const topClients = ref([
  { name: 'John Doe', revenue: '840', percentage: 100 },
  { name: 'Sarah Williams', revenue: '665', percentage: 79 },
  { name: 'Robert Chen', revenue: '600', percentage: 71 },
  { name: 'Emma Garcia', revenue: '450', percentage: 54 },
  { name: 'Michael Davis', revenue: '340', percentage: 40 },
]);

const servicesData = ref([
  { name: 'Elderly Care', count: 18, color: '#10b981' },
  { name: 'Personal Care', count: 12, color: '#3b82f6' },
  { name: 'Companion Care', count: 8, color: '#8b5cf6' },
  { name: 'Physical Therapy', count: 4, color: '#f59e0b' },
]);

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

const nyZipCodes = [
  'Select ZIP Code First',
  '10001', '10002', '10003', '10004', '10005', '10006', '10007', '10009', '10010',
  '10011', '10012', '10013', '10014', '10016', '10017', '10018', '10019', '10020', '10021',
  '10022', '10023', '10024', '10025', '10026', '10027', '10028', '10029', '10030', '10031',
  '12201', '12202', '12203', '12204', '12205', '12206', '12207', '12208', '12209', '12210',
  '14201', '14202', '14203', '14204', '14205', '14206', '14207', '14208', '14209', '14210',
  '13201', '13202', '13203', '13204', '13205', '13206', '13207', '13208', '13209', '13210'
];

const trainingCenters = ref([]);
const isCustomTrainingCenter = ref(false);
const trainingCenterApprovalStatus = ref(''); // 'pending' | 'approved' | 'rejected' | ''

// Normalize duplicated label "x x" -> "x" so display and save use a single value
const normalizeTrainingCenterLabel = (val) => {
  if (val == null || typeof val !== 'string') return (val ?? '').toString().trim();
  const s = String(val).trim();
  const m = s.match(/^(.+)\s+\1$/u);
  return m ? m[1].trim() : s;
};

// Dropdown options: CAS partners + current profile value (deduped, normalized)
const trainingCenterOptions = computed(() => {
  const raw = [...(trainingCenters.value || [])];
  const current = normalizeTrainingCenterLabel(profile.value?.trainingCenter || '');
  if (current && !raw.some((x) => normalizeTrainingCenterLabel(x) === current)) raw.unshift(current);
  const normalized = raw.map((x) => normalizeTrainingCenterLabel(typeof x === 'string' ? x : (x?.name ?? x?.email ?? x ?? ''))).filter(Boolean);
  return [...new Set(normalized)];
});

const loadTrainingCenters = async () => {
  try {
    // Use /api/training-centers (web route with auth returns Active + pending; name or email as label)
    const response = await fetch('/api/training-centers', {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    });
    if (response.ok) {
      const data = await response.json();
      const raw = data.trainingCenters || data.centers || [];
      trainingCenters.value = Array.isArray(raw)
        ? raw.map(c => (typeof c === 'string' ? c : (c?.name || c?.email || c?.title || ''))).filter(Boolean)
        : [];
    } else {
      trainingCenters.value = [];
    }
  } catch (err) {
    trainingCenters.value = [];
  }
};

// Avatar upload
const avatarInput = ref(null);
const userAvatar = ref('');
const uploadingAvatar = ref(false);
const caregiverUserId = ref(null);
const showAvatarSuccessModal = ref(false);

const closeAvatarSuccessModal = () => {
  showAvatarSuccessModal.value = false;
};

const profile = ref({
  firstName: 'Demo',
  lastName: 'Caregiver',
  email: 'caregiver@demo.com',
  phone: '',
  address: '',
  county: '',
  city: '',
  zip: '',
  birthdate: '',
  experience: '5',
  trainingCenter: '',
  customTrainingCenter: '',
  trainingCertificate: null,
  specializations: [],
  bio: 'Demo caregiver account',
  hasHHA: false,
  hhaNumber: '',
  hasCNA: false,
  cnaNumber: '',
  hasRN: false,
  rnNumber: '',
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

// Avatar upload methods
const triggerAvatarUpload = () => {
  avatarInput.value?.click();
};

const uploadAvatar = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
  if (!validTypes.includes(file.type)) {
    alert('Please select a valid image file (JPEG, PNG, or GIF)');
    return;
  }

  // Validate file size (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    alert('Image size must be less than 2MB');
    return;
  }

  if (!caregiverUserId.value) {
    alert('User ID not available. Please refresh the page.');
    return;
  }

  uploadingAvatar.value = true;

  try {
    const formData = new FormData();
    formData.append('avatar', file);

    const response = await fetch(`/api/user/${caregiverUserId.value}/avatar`, {
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
    // Clear the input
    if (avatarInput.value) {
      avatarInput.value.value = '';
    }
  }
};

let earningsChartInstance = null;
let servicesChartInstance = null;
let clientsChartInstance = null;

// Save Salary Range
const saveSalaryRange = async () => {
  try {
    // Validate inputs
    if (!salaryRange.value.min || !salaryRange.value.max) {
      notification.value = {
        show: true,
        type: 'error',
        title: 'Validation Error',
        message: 'Please enter both minimum and maximum rates',
        timeout: 3000
      };
      return;
    }

    const minRate = parseFloat(salaryRange.value.min);
    const maxRate = parseFloat(salaryRange.value.max);

    if (minRate < 20 || maxRate > 50) {
      notification.value = {
        show: true,
        type: 'error',
        title: 'Validation Error',
        message: 'Rates must be between $20 and $50',
        timeout: 3000
      };
      return;
    }

    if (minRate > maxRate) {
      notification.value = {
        show: true,
        type: 'error',
        title: 'Validation Error',
        message: 'Minimum rate must be less than maximum rate',
        timeout: 3000
      };
      return;
    }

    savingSalaryRange.value = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
      throw new Error('CSRF token not found');
    }

    const response = await fetch('/api/profile/update', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        user_type: 'caregiver',
        preferredHourlyRateMin: minRate,
        preferredHourlyRateMax: maxRate
      })
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error('Server response:', response.status, errorText);
      throw new Error(`Server returned ${response.status}`);
    }

    const data = await response.json();

    if (data.success) {
      notification.value = {
        show: true,
        type: 'success',
        title: 'Success!',
        message: 'Salary range updated successfully',
        timeout: 3000
      };
      salaryRangeDialog.value = false;

      // Reload stats to reflect changes
      await loadCaregiverStats();
    } else {
      throw new Error(data.message || 'Failed to update salary range');
    }
  } catch (error) {
    console.error('Error saving salary range:', error);
    notification.value = {
      show: true,
      type: 'error',
      title: 'Error',
      message: error.message || 'Failed to update salary range. Please try again.',
      timeout: 3000
    };
  } finally {
    savingSalaryRange.value = false;
  }
};

const saveProfileChanges = async () => {
  try {
    // Check if there's a file to upload
    // Vuetify v-file-input returns an array of File objects
    let hasFile = false;
    let certificateFile = null;

if (profile.value.trainingCertificate) {
      if (Array.isArray(profile.value.trainingCertificate) && profile.value.trainingCertificate.length > 0) {
        certificateFile = profile.value.trainingCertificate[0];
        hasFile = certificateFile instanceof File;
      } else if (profile.value.trainingCertificate instanceof File) {
        certificateFile = profile.value.trainingCertificate;
        hasFile = true;
      }
    }

    if (hasFile && certificateFile) {
      // Log file info for debugging
      const fileInfo = {
        name: certificateFile.name,
        size: certificateFile.size,
        type: certificateFile.type,
        lastModified: certificateFile.lastModified
      };
      // File info logged
    }

    // Normalize training center to a single string (dedupe "x x" -> "x") for both FormData and JSON
    const tcRaw = profile.value.trainingCenter != null
      ? (Array.isArray(profile.value.trainingCenter) ? (profile.value.trainingCenter[0] ?? '') : profile.value.trainingCenter)
      : '';
    const trainingCenterForSave = normalizeTrainingCenterLabel(typeof tcRaw === 'string' ? tcRaw : String(tcRaw || ''));

    let response;
    if (hasFile) {
      // Use FormData for file uploads
      const formData = new FormData();
      formData.append('firstName', profile.value.firstName || '');
      formData.append('lastName', profile.value.lastName || '');
      formData.append('email', profile.value.email || '');
      if (profile.value.phone) formData.append('phone', String(profile.value.phone));
      if (profile.value.birthdate) formData.append('birthdate', profile.value.birthdate);
      if (profile.value.address) formData.append('address', profile.value.address);
      if (profile.value.city != null && profile.value.city !== '') formData.append('city', String(profile.value.city));
      if (profile.value.county != null && profile.value.county !== '') formData.append('county', String(profile.value.county));
      if (profile.value.state) formData.append('state', profile.value.state);
      if (profile.value.zip) formData.append('zip', profile.value.zip);
      if (profile.value.experience) formData.append('experience', profile.value.experience);
      formData.append('trainingCenter', trainingCenterForSave);
      formData.append('customTrainingCenter', profile.value.customTrainingCenter ?? '');
      if (profile.value.bio) formData.append('bio', profile.value.bio);
      // Add certifications
      formData.append('hasHHA', profile.value.hasHHA ? '1' : '0');
      if (profile.value.hasHHA && profile.value.hhaNumber) formData.append('hhaNumber', profile.value.hhaNumber);
      formData.append('hasCNA', profile.value.hasCNA ? '1' : '0');
      if (profile.value.hasCNA && profile.value.cnaNumber) formData.append('cnaNumber', profile.value.cnaNumber);
      formData.append('hasRN', profile.value.hasRN ? '1' : '0');
      if (profile.value.hasRN && profile.value.rnNumber) formData.append('rnNumber', profile.value.rnNumber);

      // Append the file - check if it's a File object
      if (certificateFile && certificateFile instanceof File) {
        formData.append('trainingCertificate', certificateFile);

        // Log FormData contents for debugging
        for (let pair of formData.entries()) {
          if (pair[1] instanceof File) {
          } else {
          }
        }
      } else {
        alert('Please select a valid file for the training certificate.');
        return;
      }

      response = await fetch('/api/profile/update', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin',
        body: formData
      });
    } else {
      // Permanent: explicitly send city/county/borough/state/address/zip and training so they always persist.
      const payload = {
        ...profile.value,
        city: profile.value.city ?? '',
        county: profile.value.county ?? '',
        borough: profile.value.borough ?? profile.value.county ?? '',
        state: profile.value.state ?? '',
        address: profile.value.address ?? '',
        zip: profile.value.zip ?? '',
        zip_code: profile.value.zip ?? profile.value.zip_code ?? '',
        trainingCenter: trainingCenterForSave,
        customTrainingCenter: profile.value.customTrainingCenter ?? '',
        hasHHA: profile.value.hasHHA,
        hhaNumber: profile.value.hhaNumber,
        hasCNA: profile.value.hasCNA,
        cnaNumber: profile.value.cnaNumber,
        hasRN: profile.value.hasRN,
        rnNumber: profile.value.rnNumber
      };

      // Ensure phone is a string if present, or remove it if empty
      if (payload.phone) {
        payload.phone = String(payload.phone);
      } else {
        delete payload.phone; // Remove if empty to avoid null issues
      }

      // Remove trainingCertificate from payload if it's null or empty
      if (!payload.trainingCertificate || payload.trainingCertificate.length === 0) {
        delete payload.trainingCertificate;
      }

      response = await fetch('/api/profile/update', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      });
    }

    // Check if response is ok and is JSON
    const contentType = response.headers.get('content-type');
    if (response.ok && contentType && contentType.includes('application/json')) {
      const responseData = await response.json();
      const payload = responseData.data ?? responseData;
      const savedUser = payload.user;

      // Update training certificate URL if it was uploaded
      const caregiver = payload.caregiver ?? responseData.caregiver;
      if (caregiver?.training_certificate) {
        trainingCertificateUrl.value = caregiver.training_certificate.startsWith('/')
          ? caregiver.training_certificate
          : '/storage/' + caregiver.training_certificate;
      }

      // Immediately apply saved user to form so city/county/borough persist in UI
      if (savedUser) {
        if (savedUser.city != null) profile.value.city = savedUser.city;
        if (savedUser.county != null) profile.value.county = savedUser.county;
        if (savedUser.borough != null) profile.value.borough = savedUser.borough;
        if (savedUser.address != null) profile.value.address = savedUser.address;
        if (savedUser.state != null) profile.value.state = savedUser.state;
        if (savedUser.zip_code != null) profile.value.zip = savedUser.zip_code;
      }

      // Apply training center from save response (normalized) so it persists and doesn't clear
      const savedTcName = caregiver?.training_center_name != null ? normalizeTrainingCenterLabel(caregiver.training_center_name) || caregiver.training_center_name : null;
      if (savedTcName) {
        profile.value.trainingCenter = savedTcName;
      }
      if (caregiver != null && caregiver.training_center_approval_status != null) {
        trainingCenterApprovalStatus.value = caregiver.training_center_approval_status;
      }

      // Clear the file input after successful upload
      if (hasFile) {
        profile.value.trainingCertificate = null;
      }

      const isPendingApproval = (caregiver?.training_center_approval_status || trainingCenterApprovalStatus.value) === 'pending';
      if (isPendingApproval && profile.value.trainingCenter) {
        success(
          'Profile saved. Your training center must approve your request—you\'ll see an update here once they respond.',
          'Profile saved'
        );
      } else {
        success('Profile changes saved successfully!');
      }
      // Reload profile but preserve training center if API returns empty (avoid clearing after save)
      await loadProfile();
      if (savedTcName && (!profile.value.trainingCenter || !profile.value.trainingCenter.trim())) {
        profile.value.trainingCenter = savedTcName;
        trainingCenterApprovalStatus.value = caregiver?.training_center_approval_status ?? trainingCenterApprovalStatus.value;
      }
    } else {
      let errorMessage = 'Failed to save profile';
      let errorData = null;

      try {
        // Try to parse as JSON first
        if (contentType && contentType.includes('application/json')) {
          errorData = await response.json();
        } else {
          // If not JSON, get text to see what the error is
          const text = await response.text();
          errorMessage = 'Server returned an error. Please check the console for details.';
        }
      } catch (e) {
        errorMessage = 'Failed to parse server response. Status: ' + response.status;
      }

      // Handle Laravel validation errors (data.error can be an object)
      if (errorData) {
        if (errorData.error && typeof errorData.error === 'object') {
          // Format validation errors
          const errors = [];
          for (const [field, messages] of Object.entries(errorData.error)) {
            if (Array.isArray(messages)) {
              errors.push(...messages);
            } else {
              errors.push(messages);
            }
          }
          errorMessage = errors.join(', ') || errorMessage;
        } else {
          errorMessage = errorData.error || errorData.message || errorMessage;
        }
      }

      errorMessages.value = [errorMessage];
      showErrorModal.value = true;
    }
  } catch (error) {
    const errorMessage = error?.message || error?.toString() || 'Unknown error occurred';
    errorMessages.value = ['Error saving profile: ' + errorMessage];
    showErrorModal.value = true;
  }
};

const initCharts = () => {
  if (!window.Chart) {
    setTimeout(initCharts, 100);
    return;
  }
  if (earningsChart.value && currentSection.value === 'analytics') {
    if (earningsChartInstance) {
      earningsChartInstance.destroy();
    }
    const ctx = earningsChart.value.getContext('2d');
    earningsChartInstance = new window.Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [{
          label: 'Earnings',
          data: [850, 920, 1100, 1200],
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          tension: 0.4,
          fill: true,
          borderWidth: 3,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        plugins: {
          legend: { display: true, position: 'top' },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            callbacks: {
              label: (context) => `Earnings: $${context.parsed.y}`,
              afterLabel: (context) => earningsChartPeriod.value === 'week' ? 'Daily earnings' : earningsChartPeriod.value === 'month' ? 'Weekly total' : 'Monthly total'
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(0, 0, 0, 0.1)' },
            ticks: { callback: (value) => '$' + value }
          },
          x: {
            grid: { display: false }
          }
        },
        interaction: {
          intersect: false,
          mode: 'index'
        }
      }
    });
  }

  if (clientsChart.value && currentSection.value === 'analytics') {
    if (clientsChartInstance) {
      clientsChartInstance.destroy();
    }
    const ctx = clientsChart.value.getContext('2d');
    clientsChartInstance = new window.Chart(ctx, {
      type: 'bar',
      data: {
        labels: topClients.value.map(c => c.name),
        datasets: [{
          label: 'Revenue',
          data: topClients.value.map(c => parseInt(c.revenue)),
          backgroundColor: '#10b981',
          borderColor: '#059669',
          borderWidth: 1,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        plugins: {
          legend: { display: true, position: 'top' },
          tooltip: {
            backgroundColor: 'rgba(16, 185, 129, 0.9)',
            titleColor: '#fff',
            bodyColor: '#fff',
            callbacks: {
              label: (context) => `Revenue: $${context.parsed.y}`,
              afterLabel: (context) => 'Total earnings from client'
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(0, 0, 0, 0.1)' },
            ticks: { callback: (value) => '$' + value }
          },
          x: {
            grid: { display: false },
            ticks: { maxRotation: 45 }
          }
        },
        onHover: (event, elements) => {
          event.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
        }
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

watch(currentSection, (newVal) => {
  localStorage.setItem('caregiverSection', newVal);
  if (newVal === 'analytics') {
    setTimeout(initCharts, 300);
  }
  if (newVal === 'profile') {
    loadTrainingCenters(); // Load training centers when profile section is accessed
  }
  if (newVal === 'payment') {
    loadPaymentMethods(); // Load payment methods when payment section is accessed
    checkApplicationStatus(); // Check approval status when opening payment section
  }
});

onMounted(async () => {
  // Show loading overlay
  isPageLoading.value = true;
  loadingContext.value = 'staff';
  loadingProgress.value = 0;

  const loadingTasks = [
    { fn: loadNYLocationData, weight: 5 },
    { fn: loadTrainingCenters, weight: 5 },
    { fn: loadNotifications, weight: 5 },
    { fn: loadAvailableClients, weight: 10 },
    { fn: loadSidebarNotificationCount, weight: 3 }
  ];

  const totalWeight = 100;
  let completedWeight = 0;

  // Execute initial tasks in parallel
  const initialPromises = loadingTasks.map(async (task) => {
    try {
      await task.fn();
    } catch (err) {
      console.warn('Loading task failed:', err);
    } finally {
      completedWeight += task.weight;
      loadingProgress.value = Math.round((completedWeight / totalWeight) * 100);
    }
  });

  await Promise.allSettled(initialPromises);

  // Load profile first (needs to complete before other caregiver-specific tasks)
  loadingProgress.value = 35;
  await loadProfile();
  loadingProgress.value = 50;

  if (caregiverId.value) {
    const caregiverTasks = [
      { fn: loadCaregiverStats, weight: 10 },
      { fn: loadWeekHistory, weight: 10 },
      { fn: loadCurrentSession, weight: 5 },
      { fn: loadEarningsReportData, weight: 8 },
      { fn: loadScheduleEvents, weight: 7 },
      { fn: loadPaymentData, weight: 5 },
      { fn: loadPastBookings, weight: 5 }
    ];

    const caregiverPromises = caregiverTasks.map(async (task) => {
      try {
        await task.fn();
      } catch (err) {
        console.warn('Loading task failed:', err);
      } finally {
        completedWeight += task.weight;
        loadingProgress.value = Math.min(95, Math.round((completedWeight / totalWeight) * 100));
      }
    });

    await Promise.allSettled(caregiverPromises);
  }

  // Ensure progress shows 100%
  loadingProgress.value = 100;

  // Hide overlay immediately
  isPageLoading.value = false;

  if (currentSection.value === 'analytics') {
    setTimeout(initCharts, 500);
  }

  // Update time every minute for responsive dates
  setInterval(() => {
    currentTime.value = new Date();
  }, 60000);

  // Refresh assignment status every 30 seconds for real-time updates
  // (5 seconds was too aggressive and caused performance issues)
  setInterval(() => {
    if (caregiverId.value) {
      loadCaregiverStats();
      loadWeekHistory();
      loadCurrentSession();
      loadPaymentData(); // Refresh payment data in real-time
    }
  }, 30000);

  // Refresh notification count every 30 seconds
  setInterval(loadSidebarNotificationCount, 30000);
});
</script>

<style scoped>
.responsive-client-avatar {
  width: 50px;
  height: 50px;
  max-width: 100%;
  max-height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.avatar-img-responsive {
  width: 100%;
  height: auto;
  aspect-ratio: 1/1;
  object-fit: cover;
  border-radius: 50%;
  display: block;
  min-width: 32px;
  min-height: 32px;
}
@media (max-width: 600px) {
  .responsive-client-avatar {
    width: 36px;
    height: 36px;
  }
  .avatar-img-responsive {
    min-width: 28px;
    min-height: 28px;
  }
}
</style>

<style scoped>
.responsive-avatar {
  width: 100%;
  max-width: 160px;
  margin-left: auto;
  margin-right: auto;
}
.avatar-img-responsive {
  width: 100%;
  height: auto;
  aspect-ratio: 1/1;
  object-fit: cover;
  border-radius: 50%;
  display: block;
  max-width: 100%;
  min-width: 48px;
}
@media (max-width: 600px) {
  .responsive-avatar {
    max-width: 96px;
  }
  .avatar-img-responsive {
    min-width: 48px;
    max-width: 96px;
  }
}
</style>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

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

.page-subtitle {
  font-size: 1.75rem;
  font-weight: 700;
  letter-spacing: -0.03em;
  color: #1a1a1a;
}

.card-name {
  font-size: 1.25rem;
  font-weight: 600;
  letter-spacing: -0.02em;
  color: #1a1a1a;
  line-height: 1.3;
}

.card-meta {
  font-size: 0.875rem;
  color: #666;
  font-weight: 500;
  margin-top: 4px;
}

.card-info {
  font-size: 0.95rem;
  font-weight: 500;
  color: #333;
}

.card-highlight {
  font-size: 0.95rem;
  font-weight: 600;
  color: #f59e0b;
}

.card-header {
  background: #fafafa;
  border-bottom: 1px solid #f0f0f0;
}

/* Add borders to all v-card elements except dashboard header */
:deep(.v-card:not(.dashboard-header)) {
  border: 1px solid #c5c5c5ff !important;
}

.activity-card {
  border-radius: 24px !important;
  border: 1px solid #c5c5c5ff !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
}

.action-btn {
  border-radius: 14px !important;
  font-weight: 600 !important;
  font-size: 0.95rem !important;
  text-transform: none !important;
  letter-spacing: -0.01em !important;
  padding: 24px 16px !important;
}

.client-card {
  border-radius: 24px !important;
  border: 1px solid #c5c5c5ff !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
}

.client-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 40px rgba(16, 185, 129, 0.12) !important;
  border-color: #10b981;
}

.job-listing-card {
  position: relative;
  overflow: hidden;
}

.job-listing-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #10b981, #059669);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.job-listing-card:hover::before {
  opacity: 1;
}

.client-job-avatar {
  flex-shrink: 0;
}

.client-job-avatar .v-img {
  border-radius: 50%;
}

.client-job-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
}

.calendar-day-header {
  text-align: center;
  font-size: 0.75rem;
  font-weight: 600;
  color: #10b981;
  padding: 8px 4px;
}

.calendar-date {
  text-align: center;
  padding: 8px 4px;
  font-size: 0.875rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.calendar-date:hover {
  background: #f0fdf4;
}

.calendar-date.today {
  background: #10b981;
  color: white;
  font-weight: 700;
}

.calendar-date.other-month {
  color: #d1d5db;
}

.calendar-date.has-appointment {
  position: relative;
}

.calendar-date.has-appointment::after {
  content: '';
  position: absolute;
  bottom: 2px;
  left: 50%;
  transform: translateX(-50%);
  width: 4px;
  height: 4px;
  background: #f59e0b;
  border-radius: 50%;
}

.calendar-date.today.has-appointment::after {
  background: white;
}

.schedule-legend {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
  padding: 16px;
  background: #f9fafb;
  border-radius: 12px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.875rem;
  font-weight: 500;
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.legend-dot.scheduled {
  background: #3b82f6;
}

.legend-dot.completed {
  background: #10b981;
}

.legend-dot.confirmed {
  background: #8b5cf6;
}

.legend-dot.cancelled {
  background: #ef4444;
}

.schedule-calendar {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}

.schedule-day-header {
  text-align: center;
  font-size: 0.875rem;
  font-weight: 700;
  color: #10b981;
  padding: 12px 8px;
  background: #f0fdf4;
  border-radius: 8px;
}

.month-navigation {
  display: flex;
  align-items: center;
  gap: 16px;
  justify-content: center;
  flex: 1;
  margin-right: 150px;
}

.month-display {
  font-size: 1.75rem;
  font-weight: 700;
  color: #10b981;
  min-width: 250px;
  text-align: center;
}

.schedule-date {
  min-height: 100px;
  padding: 8px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  background: white;
}

.schedule-date:hover {
  border-color: #10b981;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
  transform: translateY(-2px);
}

.schedule-date.today {
  border-color: #10b981;
  background: #f0fdf4;
}

.schedule-date.other-month {
  opacity: 0.3;
  cursor: default;
}

.schedule-date.other-month:hover {
  border-color: #e5e7eb;
  box-shadow: none;
  transform: none;
}

.date-number {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 4px;
  color: #1f2937;
}

.schedule-date.today .date-number {
  color: #10b981;
  font-weight: 700;
}

.date-events {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.event-text {
  font-size: 0.7rem;
  padding: 4px 6px;
  border-radius: 6px;
  margin-bottom: 2px;
  font-weight: 600;
  color: white;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.event-text.scheduled {
  background: #3b82f6;
}

.event-text.completed {
  background: #10b981;
}

.event-text.confirmed {
  background: #8b5cf6;
}

.event-text.cancelled {
  background: #ef4444;
}

.event-more {
  font-size: 0.65rem;
  color: #6b7280;
  font-weight: 600;
  margin-top: 2px;
  font-style: italic;
}

.event-item {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 12px;
  background: #fafafa;
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

.bank-account-card-stripe {
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border: 2px solid #10b981;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
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

.summary-item-compact {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.summary-label {
  font-size: 0.95rem;
  color: #6b7280;
}

.summary-label-compact {
  font-size: 0.8rem;
  color: #6b7280;
}

.summary-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
}

.summary-value-compact {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1f2937;
}

.transaction-stats {
  display: flex;
  justify-content: space-around;
  align-items: center;
  gap: 32px;
}

.stat-item {
  text-align: center;
  flex: 1;
}

.stat-amount {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.stat-label-text {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.chart-stat {
  text-align: center;
}

.chart-stat .stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 4px;
}

.chart-stat .stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.client-revenue-item {
  margin-bottom: 24px;
}

.service-legend-item {
  margin-bottom: 8px;
}

.legend-color {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  margin-right: 12px;
}

.quick-stat-item {
  display: flex;
  align-items: center;
}

.quick-stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.quick-stat-label {
  font-size: 0.875rem;
  color: #6b7280;
}

.monthly-detail-item {
  margin-bottom: 12px;
}

.detail-label {
  font-size: 0.875rem;
  color: #6b7280;
}

.detail-value {
  font-size: 0.95rem;
  font-weight: 600;
}

.balance-amount {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 4px;
}

.profile-stat {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 12px 0;
  font-size: 0.95rem;
}

.notification-stat-card {
  border-radius: 16px !important;
  border: 1px solid #c5c5c5ff !important;
  transition: all 0.3s ease;
}

.notification-stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.notification-item {
  padding: 20px !important;
  border-radius: 12px;
  margin: 8px 16px;
  transition: all 0.2s ease;
}

.notification-item:hover {
  background: #f8fafc !important;
}

.notification-item.unread {
  background: #f0fdf4 !important;
}

.notification-content {
  flex: 1;
}

.notification-title {
  font-weight: 600 !important;
  font-size: 1rem !important;
  color: #1f2937 !important;
}

.notification-message {
  font-size: 0.875rem !important;
  color: #6b7280 !important;
  line-height: 1.5 !important;
}

.notification-time {
  font-size: 0.75rem;
  color: #9ca3af;
  font-weight: 500;
}

.notification-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.reminder-item {
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
}

.reminder-item:last-child {
  border-bottom: none;
}

.reminder-title {
  font-weight: 600;
  font-size: 0.875rem;
  color: #1f2937;
}

.reminder-time {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 2px;
}

.bank-name-small {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1f2937;
}

.bank-name-compact {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1f2937;
}

.account-type-small {
  font-size: 0.75rem;
  color: #6b7280;
}

.account-type-compact {
  font-size: 0.7rem;
  color: #6b7280;
}

.account-number-small {
  font-size: 0.875rem;
  color: #4b5563;
  font-weight: 500;
  letter-spacing: 2px;
}

.account-number-compact {
  font-size: 0.8rem;
  color: #4b5563;
}

/* Mobile Responsive Table Styles */
@media (max-width: 960px) {
  /* Make all tables scrollable on mobile */
  :deep(.v-data-table .v-table__wrapper) {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
  }

  /* Table pagination mobile adjustments */
  :deep(.v-data-table-footer) {
    flex-wrap: wrap !important;
    padding: 0.75rem !important;
  }

  :deep(.v-data-table-footer__items-per-page) {
    font-size: 0.75rem !important;
  }

  :deep(.v-data-table-footer__info) {
    font-size: 0.75rem !important;
  }

  /* Card headers compact on mobile */
  .card-header {
    padding: 1rem !important;
  }

  .section-title {
    font-size: 1.25rem !important;
  }
}

@media (max-width: 480px) {
  /* Allow horizontal scroll but don't force min-width */
  :deep(.v-data-table .v-table__wrapper table) {
    min-width: 100% !important;
    width: max-content;
  }

  :deep(.v-data-table .v-table__wrapper) {
    border-radius: 8px !important;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
  }

  /* Compact table cells */
  :deep(.v-data-table th),
  :deep(.v-data-table td) {
    padding: 0.5rem 0.375rem !important;
    font-size: 0.75rem !important;
  }

  /* Compact table cards */
  :deep(.v-card) {
    border-radius: 12px !important;
    overflow: hidden !important;
  }

  /* Compact headers */
  .card-header {
    padding: 0.875rem !important;
  }

  .section-title {
    font-size: 1.125rem !important;
  }

  /* Pagination mobile layout */
  :deep(.v-data-table-footer) {
    padding: 0.5rem !important;
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 0.5rem !important;
    font-size: 0.75rem !important;
  }

  :deep(.v-data-table-footer__items-per-page) {
    order: 2 !important;
    width: 100% !important;
    justify-content: center !important;
    font-size: 0.6875rem !important;
  }

  :deep(.v-data-table-footer__info) {
    order: 1 !important;
    text-align: center !important;
    font-size: 0.6875rem !important;
  }

  :deep(.v-data-table-footer__pagination) {
    order: 3 !important;
    justify-content: center !important;
    width: 100% !important;
  }

  /* Compact table cells */
  :deep(.v-data-table td) {
    padding: 0.75rem 0.5rem !important;
    font-size: 0.8125rem !important;
  }

  :deep(.v-data-table th) {
    padding: 0.75rem 0.5rem !important;
    font-size: 0.75rem !important;
  }

  /* Smaller chips */
  :deep(.v-chip) {
    font-size: 0.6875rem !important;
    height: 20px !important;
    padding: 0 6px !important;
  }

  /* Compact icons */
  :deep(.v-icon) {
    font-size: 18px !important;
  }

  /* Transaction Stats Mobile */
  .transaction-stats {
    flex-direction: column !important;
    gap: 1rem !important;
  }

  .transaction-stats .v-divider {
    display: none !important;
  }

  .stat-item {
    padding: 0.75rem 0 !important;
    border-bottom: 1px solid #e5e7eb !important;
    text-align: left !important;
  }

  .stat-item:last-child {
    border-bottom: none !important;
  }

  .stat-amount {
    font-size: 1.5rem !important;
  }

  .stat-label-text {
    font-size: 0.8125rem !important;
  }

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

  /* Quick stat items mobile */
  .quick-stat-item {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 0.5rem !important;
  }

  .quick-stat-value {
    font-size: 1.25rem !important;
  }

  .quick-stat-label {
    font-size: 0.8125rem !important;
  }

  /* Compact buttons on mobile */
  .v-btn {
    font-size: 0.875rem !important;
    padding: 0.625rem 1rem !important;
  }

  /* Chart stats mobile */
  .chart-stat .stat-value {
    font-size: 1.125rem !important;
  }

  .chart-stat .stat-label {
    font-size: 0.6875rem !important;
  }
}

@media (max-width: 480px) {
  /* Transaction stats very compact */
  .stat-amount {
    font-size: 1.125rem !important;
  }

  .stat-label-text {
    font-size: 0.75rem !important;
  }

  /* Cards very compact */
  .card-header {
    padding: 0.875rem !important;
  }

  .section-title {
    font-size: 1.125rem !important;
  }

  .quick-stat-value {
    font-size: 1.125rem !important;
  }

  .quick-stat-label {
    font-size: 0.75rem !important;
  }

  /* Balance amount mobile */
  .balance-amount {
    font-size: 1.25rem !important;
  }
}

.account-balance-card .text-caption {
  color: #374151 !important;
  font-size: 0.875rem !important;
}

.account-balance-card .text-caption .font-weight-bold {
  color: #1f2937 !important;
  font-weight: 600 !important;
}

.compact-card {
  border-radius: 16px !important;
  border: 1px solid #c5c5c5ff !important;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
  height: 280px !important;
}

.full-width-card {
  width: 100% !important;
}

.compact-table .v-data-table__wrapper {
  font-size: 0.85rem;
}

.compact-table .v-data-table-header__content {
  font-size: 0.8rem;
  font-weight: 600;
}

.compact-table .v-data-table__td {
  padding: 8px 12px !important;
}

.compact-table .v-data-table__th {
  padding: 8px 12px !important;
}

.custom-table {
  font-size: 0.85rem;
}

.table-header {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr 0.7fr;
  gap: 8px;
  padding: 8px 0;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 4px;
}

.header-cell {
  font-weight: 600;
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.table-row {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr 0.7fr;
  gap: 8px;
  padding: 10px 0;
  border-bottom: 1px solid #f3f4f6;
  align-items: center;
}

.table-row:hover {
  background: #f9fafb;
  margin: 0 -16px;
  padding: 10px 16px;
  border-radius: 8px;
}

.table-cell {
  font-size: 0.85rem;
  color: #374151;
}

.client-name {
  font-weight: 600;
  color: #1f2937;
}

.status-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.active {
  background: #dcfce7;
  color: #166534;
}

/* Modern Table Styling */
.modern-activity-card {
  border-radius: 16px !important;
  border: 1px solid #c5c5c5ff !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
  overflow: hidden !important;
}

.modern-card-header {
  background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%) !important;
  border-bottom: none !important;
}

.modern-title {
  font-size: 1.1rem !important;
  font-weight: 700 !important;
  letter-spacing: -0.02em !important;
}

.activity-count {
  font-size: 0.75rem !important;
  font-weight: 600 !important;
  color: #6b7280 !important;
}

.modern-activity-table {
  background: white !important;
  border-radius: 0 0 16px 16px !important;
}

.modern-header-row {
  background: #f8fafc !important;
  border-bottom: 2px solid #e5e7eb !important;
}

.modern-header-cell {
  font-size: 0.75rem !important;
  font-weight: 700 !important;
  color: #374151 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.05em !important;
  padding: 12px 16px !important;
  border-bottom: none !important;
}

.client-col { width: 30% !important; }
.care-col { width: 25% !important; }
.visit-col { width: 25% !important; }
.status-col { width: 20% !important; }

.modern-row {
  transition: all 0.2s ease !important;
  border-bottom: 1px solid #f3f4f6 !important;
}

.modern-row:hover {
  background: #f0fdf4 !important;
  transform: translateX(2px) !important;
}

.modern-cell {
  padding: 12px 16px !important;
  font-size: 0.875rem !important;
  color: #374151 !important;
  border-bottom: none !important;
  vertical-align: middle !important;
}

.client-cell {
  font-weight: 600 !important;
}

.care-cell {
  font-weight: 500 !important;
  color: #6b7280 !important;
}

.visit-cell {
  font-weight: 500 !important;
  color: #6b7280 !important;
  font-size: 0.8rem !important;
}

.status-cell {
  text-align: center !important;
}

.modern-type-chip {
  border-radius: 8px !important;
  font-weight: 600 !important;
  font-size: 0.75rem !important;
  min-width: 70px !important;
  justify-content: center !important;
}

.modern-type-chip .v-chip__content {
  padding: 0 8px !important;
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

.enhanced-card {
  border-radius: 20px !important;
  border: 1px solid #e5e7eb !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
  transition: all 0.3s ease !important;
}

.enhanced-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.15) !important;
}

.enhanced-card-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-bottom: 1px solid #e2e8f0;
  border-radius: 20px 20px 0 0 !important;
}

.enhanced-label {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
}

.enhanced-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
}

.status-chip {
  font-weight: 600 !important;
  border-radius: 12px !important;
}

.time-btn {
  border-radius: 12px !important;
  font-weight: 600 !important;
  text-transform: none !important;
}

.detail-label {
  font-size: 0.875rem;
  color: #6b7280;
}

.detail-value {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

/* Minimalist Weekly Schedule Grid */
.schedule-grid {
  display: flex;
  gap: 4px;
  justify-content: space-between;
}

.schedule-day {
  flex: 1;
  text-align: center;
  padding: 8px 4px;
  border-radius: 8px;
  background: #f8fafc;
  transition: all 0.15s ease;
  min-width: 0;
}

.schedule-day.is-today {
  background: #ecfdf5;
  border: 1px solid #10b981;
}

.schedule-day.is-assigned {
  background: #f0fdf4;
}

.schedule-day .day-label {
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  margin-bottom: 4px;
}

.schedule-day.is-today .day-label {
  color: #10b981;
}

.schedule-day .day-status {
  font-size: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2px;
}

.schedule-day .day-status.assigned {
  color: #10b981;
  font-weight: 500;
}

.schedule-day .day-status.off {
  color: #cbd5e1;
}

.schedule-day .time-label {
  font-size: 10px;
}

@media (max-width: 600px) {
  .schedule-grid {
    flex-wrap: wrap;
    gap: 6px;
  }

  .schedule-day {
    flex: 0 0 calc(25% - 6px);
    padding: 6px 2px;
  }

  .schedule-day .day-label {
    font-size: 10px;
  }

  .schedule-day .time-label {
    font-size: 9px;
  }
}

/* Fix first column width for tables WITHOUT checkboxes */
.table-no-checkbox table thead tr th:first-child,
.table-no-checkbox table tbody tr td:first-child,
:deep(.table-no-checkbox) table thead tr th:first-child,
:deep(.table-no-checkbox) table tbody tr td:first-child,
:deep(.table-no-checkbox) .v-data-table__thead .v-data-table__tr .v-data-table__th:first-child,
:deep(.table-no-checkbox) .v-data-table__tbody .v-data-table__tr .v-data-table__td:first-child,
:deep(.table-no-checkbox) .v-table__wrapper table thead tr th:first-child,
:deep(.table-no-checkbox) .v-table__wrapper table tbody tr td:first-child {
  width: auto !important;
  min-width: auto !important;
  max-width: none !important;
  padding: 16px 20px !important;
  text-align: left !important;
}

.cursor-pointer {
  cursor: pointer;
}

.hover-highlight:hover {
  background-color: rgba(76, 175, 80, 0.08) !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* =============================================================
   EARNINGS REPORT - MOBILE RESPONSIVE STYLES
   ============================================================= */

/* Earnings Report Stats Cards */
.stat-card-earnings {
  border-radius: 16px !important;
  border: 1px solid #e5e7eb !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06) !important;
  transition: all 0.3s ease !important;
}

.stat-card-earnings:hover {
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15) !important;
}

/* Earnings Breakdown Styles */
.earnings-breakdown-item {
  padding: 12px 0;
}

.earnings-breakdown-detail {
  padding: 8px 0;
}

/* Time Tracking Summary */
.time-tracking-summary {
  padding: 8px 0;
}

/* Chart stat styling */
.chart-stat {
  text-align: center;
  padding: 12px 16px;
  background: #f8fafc;
  border-radius: 12px;
}

.chart-stat .stat-value {
  font-size: 1.5rem;
  font-weight: 700;
}

.chart-stat .stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 4px;
}

/* Earnings Stats Row - 2x2 Grid Styles */
.earnings-stats-row {
  margin-left: -6px !important;
  margin-right: -6px !important;
}

.earnings-stats-row > .v-col {
  padding: 6px !important;
}

/* Earnings Chart Card - Base Styles */
.earnings-chart-card {
  border-radius: 16px !important;
}

.earnings-chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

.chart-stats-row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.chart-container {
  width: 100%;
  min-height: 200px;
}

.period-toggle {
  border-radius: 8px !important;
}

.period-toggle .v-btn {
  text-transform: none !important;
  font-weight: 600 !important;
  letter-spacing: 0 !important;
}

/* =============================================================
   EARNINGS REPORT - TABLET RESPONSIVE (max-width: 960px)
   ============================================================= */
@media (max-width: 960px) {
  /* Earnings Stats Row - 2x2 grid */
  .earnings-stats-row {
    margin-left: -4px !important;
    margin-right: -4px !important;
  }

  .earnings-stats-row > .v-col {
    padding: 4px !important;
  }

  /* Earnings Chart Header */
  .earnings-chart-header {
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 12px !important;
  }

  .earnings-chart-header .section-title {
    text-align: center;
  }

  .period-toggle {
    width: 100% !important;
    display: flex !important;
  }

  .period-toggle .v-btn {
    flex: 1 !important;
  }

  /* Chart Stats Row */
  .chart-stats-row {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 10px !important;
  }

  .chart-stats-row .chart-stat {
    text-align: center;
  }

  /* Earnings Report Header */
  [class*="analytics"] .card-header {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 12px !important;
  }

  [class*="analytics"] .card-header .d-flex {
    width: 100% !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
  }

  /* Stats cards - 2 columns on tablet */
  .stat-card-earnings .v-card-text {
    padding: 16px !important;
  }

  .stat-card-earnings .text-h5 {
    font-size: 1.25rem !important;
  }

  /* Chart stats row */
  .chart-stat {
    padding: 10px 12px;
  }

  .chart-stat .stat-value {
    font-size: 1.25rem;
  }

  .chart-stat .stat-label {
    font-size: 0.7rem;
  }
}

/* =============================================================
   EARNINGS REPORT - MOBILE RESPONSIVE (max-width: 768px)
   ============================================================= */
@media (max-width: 768px) {
  /* ===== EARNINGS OVER TIME CHART WIDGET - MOBILE ===== */
  .earnings-chart-card {
    border-radius: 12px !important;
  }

  .earnings-chart-header {
    padding: 14px 16px !important;
    flex-direction: column !important;
    align-items: center !important;
    gap: 12px !important;
  }

  .earnings-chart-header .section-title {
    font-size: 1rem !important;
    text-align: center !important;
  }

  .period-toggle {
    width: 100% !important;
    display: flex !important;
    border-radius: 10px !important;
    overflow: hidden !important;
  }

  .period-toggle .v-btn {
    flex: 1 !important;
    font-size: 0.8125rem !important;
    padding: 10px 8px !important;
    min-height: 40px !important;
    border-radius: 0 !important;
  }

  /* Chart Stats Row - 3 column grid */
  .chart-stats-row {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 8px !important;
    background: #f8fafc !important;
    padding: 12px !important;
    border-radius: 10px !important;
    margin-bottom: 16px !important;
  }

  .chart-stats-row .chart-stat {
    background: transparent !important;
    padding: 8px 4px !important;
    text-align: center !important;
    border-radius: 0 !important;
  }

  .chart-stats-row .chart-stat .stat-value {
    font-size: 1rem !important;
  }

  .chart-stats-row .chart-stat .stat-label {
    font-size: 0.625rem !important;
    margin-top: 2px !important;
  }

  /* Chart Container */
  .chart-container {
    height: 180px !important;
    min-height: 150px !important;
  }

  .earnings-chart-card .v-card-text {
    padding: 14px !important;
  }

  /* ===== EARNINGS STATS - 2x2 GRID MOBILE STYLES ===== */
  .earnings-stats-row {
    margin-left: -4px !important;
    margin-right: -4px !important;
    margin-bottom: 16px !important;
  }

  .earnings-stats-row > .v-col {
    padding: 4px !important;
  }

  /* Stat cards in 2x2 grid - stacked layout */
  .earnings-stats-row .stat-card-earnings {
    height: 100% !important;
    border-radius: 12px !important;
  }

  .earnings-stats-row .stat-card-earnings .v-card-text {
    padding: 12px !important;
    height: 100% !important;
  }

  .earnings-stats-row .stat-card-earnings .v-card-text > .d-flex {
    flex-direction: column !important;
    align-items: center !important;
    text-align: center !important;
    gap: 8px !important;
  }

  .earnings-stats-row .stat-card-earnings .v-avatar {
    width: 36px !important;
    height: 36px !important;
    order: -1 !important;
  }

  .earnings-stats-row .stat-card-earnings .v-avatar .v-icon {
    font-size: 18px !important;
  }

  .earnings-stats-row .stat-card-earnings .text-h5 {
    font-size: 1rem !important;
    line-height: 1.2 !important;
    margin: 4px 0 !important;
  }

  .earnings-stats-row .stat-card-earnings .text-caption {
    font-size: 0.625rem !important;
    line-height: 1.3 !important;
  }

  .earnings-stats-row .stat-card-earnings .text-caption .v-icon {
    font-size: 10px !important;
  }

  /* Earnings Report Section Header */
  [class*="analytics"] .v-card-title.card-header {
    padding: 14px 16px !important;
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 12px !important;
  }

  [class*="analytics"] .v-card-title.card-header > div:first-child {
    text-align: center;
  }

  [class*="analytics"] .v-card-title.card-header .section-title {
    font-size: 1.125rem !important;
  }

  [class*="analytics"] .v-card-title.card-header .text-caption {
    font-size: 0.75rem !important;
  }

  [class*="analytics"] .v-card-title.card-header .d-flex.align-center.ga-2 {
    flex-direction: column !important;
    width: 100% !important;
    gap: 10px !important;
  }

  [class*="analytics"] .v-card-title.card-header .v-select {
    width: 100% !important;
  }

  [class*="analytics"] .v-card-title.card-header .v-btn {
    width: 100% !important;
  }

  /* Stats Cards - 2x2 grid on mobile */
  .stat-card-earnings {
    border-radius: 12px !important;
  }

  .stat-card-earnings .v-card-text {
    padding: 12px !important;
  }

  .stat-card-earnings .v-card-text > .d-flex {
    flex-direction: column-reverse !important;
    text-align: center !important;
    gap: 10px !important;
  }

  .stat-card-earnings .v-avatar {
    width: 40px !important;
    height: 40px !important;
    margin: 0 auto !important;
  }

  .stat-card-earnings .v-avatar .v-icon {
    font-size: 20px !important;
  }

  .stat-card-earnings .text-h5 {
    font-size: 1.125rem !important;
    margin-top: 4px !important;
  }

  .stat-card-earnings .text-caption {
    font-size: 0.6875rem !important;
  }

  /* Salary Range Card clickable */
  .stat-card-earnings.hover-highlight {
    cursor: pointer;
  }

  .stat-card-earnings.hover-highlight:active {
    transform: scale(0.98);
    background: rgba(76, 175, 80, 0.08) !important;
  }

  /* Earnings Breakdown Card */
  .earnings-breakdown-item .d-flex {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 8px !important;
  }

  .earnings-breakdown-item .text-h6 {
    font-size: 1.125rem !important;
  }

  .earnings-breakdown-item .text-h5 {
    font-size: 1.25rem !important;
  }

  .earnings-breakdown-detail .d-flex {
    flex-direction: row !important;
    justify-content: space-between !important;
  }

  .earnings-breakdown-detail span {
    font-size: 0.8125rem !important;
  }

  /* Time Tracking Summary */
  .time-tracking-summary .d-flex.justify-space-between {
    gap: 12px;
  }

  .time-tracking-summary .text-h5 {
    font-size: 1.125rem !important;
  }

  .time-tracking-summary .v-avatar {
    width: 36px !important;
    height: 36px !important;
  }

  .time-tracking-summary .v-avatar .v-icon {
    font-size: 18px !important;
  }

  /* Earnings Chart Section */
  .chart-stat {
    padding: 8px 10px;
    border-radius: 8px;
  }

  .chart-stat .stat-value {
    font-size: 1rem !important;
  }

  .chart-stat .stat-label {
    font-size: 0.625rem !important;
  }

  /* Chart toggle buttons */
  .v-btn-toggle.v-btn-group--density-default {
    display: flex !important;
    width: 100% !important;
    margin-top: 8px !important;
  }

  .v-btn-toggle .v-btn {
    flex: 1 !important;
    font-size: 0.75rem !important;
    padding: 8px 12px !important;
  }

  /* Chart container */
  [style*="height: 250px"] {
    height: 180px !important;
  }

  /* Earnings History Table - Card view on mobile */
  :deep(.table-no-checkbox) {
    overflow: visible !important;
  }

  :deep(.table-no-checkbox .v-table__wrapper) {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
  }

  :deep(.table-no-checkbox table) {
    min-width: 500px !important;
  }
}

/* =============================================================
   EARNINGS REPORT - SMALL MOBILE (max-width: 480px)
   ============================================================= */
@media (max-width: 480px) {
  /* ===== EARNINGS STATS 2x2 GRID - VERY COMPACT ===== */
  .earnings-stats-row {
    margin-left: -3px !important;
    margin-right: -3px !important;
    margin-bottom: 12px !important;
  }

  .earnings-stats-row > .v-col {
    padding: 3px !important;
  }

  .earnings-stats-row .stat-card-earnings {
    border-radius: 10px !important;
  }

  .earnings-stats-row .stat-card-earnings .v-card-text {
    padding: 10px 8px !important;
  }

  .earnings-stats-row .stat-card-earnings .v-avatar {
    width: 32px !important;
    height: 32px !important;
  }

  .earnings-stats-row .stat-card-earnings .v-avatar .v-icon {
    font-size: 16px !important;
  }

  .earnings-stats-row .stat-card-earnings .text-h5 {
    font-size: 0.875rem !important;
  }

  .earnings-stats-row .stat-card-earnings .text-caption {
    font-size: 0.5625rem !important;
    line-height: 1.25 !important;
  }

  .earnings-stats-row .stat-card-earnings .text-caption .v-icon {
    font-size: 8px !important;
  }

  /* ===== EARNINGS OVER TIME CHART WIDGET - SMALL MOBILE ===== */
  .earnings-chart-card {
    border-radius: 10px !important;
  }

  .earnings-chart-header {
    padding: 12px !important;
    gap: 10px !important;
  }

  .earnings-chart-header .section-title {
    font-size: 0.9375rem !important;
  }

  .period-toggle {
    border-radius: 8px !important;
  }

  .period-toggle .v-btn {
    font-size: 0.6875rem !important;
    padding: 8px 6px !important;
    min-height: 36px !important;
  }

  /* Chart Stats Row - Very Compact */
  .chart-stats-row {
    gap: 6px !important;
    padding: 10px 8px !important;
    border-radius: 8px !important;
    margin-bottom: 12px !important;
  }

  .chart-stats-row .chart-stat {
    padding: 6px 2px !important;
  }

  .chart-stats-row .chart-stat .stat-value {
    font-size: 0.875rem !important;
  }

  .chart-stats-row .chart-stat .stat-label {
    font-size: 0.5625rem !important;
    line-height: 1.2 !important;
  }

  /* Chart Container - Very Compact */
  .chart-container {
    height: 150px !important;
    min-height: 120px !important;
  }

  .earnings-chart-card .v-card-text {
    padding: 10px !important;
  }

  /* Earnings Report Header - very compact */
  [class*="analytics"] .v-card-title.card-header {
    padding: 12px !important;
  }

  [class*="analytics"] .v-card-title.card-header .section-title {
    font-size: 1rem !important;
  }

  /* Stats Cards - very compact */
  .stat-card-earnings {
    border-radius: 10px !important;
  }

  .stat-card-earnings .v-card-text {
    padding: 10px !important;
  }

  .stat-card-earnings .text-h5 {
    font-size: 1rem !important;
  }

  .stat-card-earnings .text-caption {
    font-size: 0.625rem !important;
    line-height: 1.3 !important;
  }

  .stat-card-earnings .v-avatar {
    width: 36px !important;
    height: 36px !important;
  }

  .stat-card-earnings .v-avatar .v-icon {
    font-size: 18px !important;
  }

  /* Breakdown Cards - very compact */
  .earnings-breakdown-item .text-h6,
  .earnings-breakdown-item .font-weight-medium {
    font-size: 0.9375rem !important;
  }

  .earnings-breakdown-item .text-h5 {
    font-size: 1.125rem !important;
  }

  .earnings-breakdown-detail span {
    font-size: 0.75rem !important;
  }

  /* Progress bar */
  :deep(.v-progress-linear) {
    height: 6px !important;
  }

  /* Time Tracking Summary - very compact */
  .time-tracking-summary .text-h5 {
    font-size: 1rem !important;
  }

  .time-tracking-summary .text-caption {
    font-size: 0.6875rem !important;
  }

  .time-tracking-summary .v-avatar {
    width: 32px !important;
    height: 32px !important;
  }

  .time-tracking-summary .v-avatar .v-icon {
    font-size: 16px !important;
  }

  /* Chart section - very compact */
  .chart-stat {
    padding: 6px 8px;
    border-radius: 6px;
  }

  .chart-stat .stat-value {
    font-size: 0.875rem !important;
  }

  .chart-stat .stat-label {
    font-size: 0.5625rem !important;
  }

  /* Chart toggle buttons */
  .v-btn-toggle .v-btn {
    font-size: 0.6875rem !important;
    padding: 6px 10px !important;
    min-height: 32px !important;
  }

  /* Chart height */
  [style*="height: 250px"],
  [style*="height: 180px"] {
    height: 150px !important;
  }

  /* Card padding - very compact */
  .v-card-text.pa-6 {
    padding: 14px !important;
  }

  .v-card-title.card-header.pa-6 {
    padding: 12px 14px !important;
  }

  /* Dividers - tighter spacing */
  :deep(.v-divider.my-4) {
    margin-top: 12px !important;
    margin-bottom: 12px !important;
  }

  :deep(.v-divider.my-3) {
    margin-top: 8px !important;
    margin-bottom: 8px !important;
  }

  /* Row spacing */
  :deep(.v-row.mb-6) {
    margin-bottom: 16px !important;
  }

  /* Column spacing on mobile */
  :deep(.v-col) {
    padding: 6px !important;
  }

  /* Earnings history table */
  :deep(.v-data-table) {
    border-radius: 12px !important;
    overflow: hidden !important;
  }

  :deep(.v-data-table th) {
    font-size: 0.6875rem !important;
    padding: 10px 8px !important;
  }

  :deep(.v-data-table td) {
    font-size: 0.75rem !important;
    padding: 10px 8px !important;
  }

  /* Avatar in table */
  :deep(.v-data-table .v-avatar) {
    width: 24px !important;
    height: 24px !important;
  }

  :deep(.v-data-table .v-avatar .text-caption) {
    font-size: 0.5rem !important;
  }

  /* Chips in table */
  :deep(.v-data-table .v-chip) {
    font-size: 0.625rem !important;
    height: 20px !important;
    padding: 0 6px !important;
  }
}

/* =============================================================
   EARNINGS REPORT - LANDSCAPE MOBILE FIX
   ============================================================= */
@media (max-height: 500px) and (orientation: landscape) {
  /* Reduce chart height in landscape */
  [style*="height: 250px"],
  [style*="height: 180px"],
  [style*="height: 150px"] {
    height: 120px !important;
  }

  /* Compact stats cards in landscape */
  .stat-card-earnings .v-card-text {
    padding: 8px !important;
  }

  .stat-card-earnings .v-avatar {
    width: 28px !important;
    height: 28px !important;
  }
}

/* =============================================================
   TOUCH-FRIENDLY IMPROVEMENTS FOR EARNINGS REPORT
   ============================================================= */
@media (hover: none) and (pointer: coarse) {
  /* Larger touch targets */
  .stat-card-earnings.hover-highlight {
    min-height: 100px;
  }

  .v-btn-toggle .v-btn {
    min-height: 44px !important;
  }

  /* Remove hover effects on touch */
  .stat-card-earnings:hover {
    transform: none !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06) !important;
  }

  /* Active state for touch */
  .stat-card-earnings:active,
  .stat-card-earnings.hover-highlight:active {
    transform: scale(0.98) !important;
    background: rgba(16, 185, 129, 0.08) !important;
  }
}

/* ============================================================
   CAREGIVER MOBILE ULTRA-COMPACT OPTIMIZATIONS
   For screens below 450px - ensures optimal touch and layout
   ============================================================ */
@media (max-width: 450px) {
  /* Force single column layout for grids */
  .v-row > .v-col[class*="v-col-6"],
  .v-row > .v-col[class*="v-col-4"],
  .v-row > .v-col[class*="cols-6"],
  .v-row > .v-col[class*="cols-4"] {
    flex: 0 0 100% !important;
    max-width: 100% !important;
  }

  /* Compact card headers */
  .v-card-title {
    font-size: 1rem !important;
    padding: 12px !important;
  }

  .v-card-text {
    padding: 12px !important;
  }

  /* Touch-friendly buttons */
  .v-btn:not(.v-btn--icon) {
    min-height: 44px !important;
    font-size: 0.8rem !important;
  }

  /* Chips minimum size */
  .v-chip {
    min-height: 32px !important;
    font-size: 0.75rem !important;
  }

  /* Stats grid compactness */
  .stat-card-earnings {
    padding: 10px !important;
  }

  .stat-card-earnings .stat-value {
    font-size: 1.25rem !important;
  }

  .stat-card-earnings .stat-label {
    font-size: 0.7rem !important;
  }
}

/* Extra small screens */
@media (max-width: 360px) {
  .section-title {
    font-size: 1.1rem !important;
  }

  .page-subtitle {
    font-size: 1.25rem !important;
  }

  .card-name {
    font-size: 1rem !important;
  }

  /* Stack buttons vertically */
  .d-flex.gap-2,
  .d-flex.ga-2 {
    flex-direction: column !important;
    width: 100%;
  }

  .d-flex.gap-2 > .v-btn,
  .d-flex.ga-2 > .v-btn {
    width: 100% !important;
  }
}

/* Table scroll improvements */
@media (max-width: 768px) {
  .v-data-table__wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    position: relative;
  }

  /* Scroll fade indicator */
  .v-data-table__wrapper::after {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 40px;
    background: linear-gradient(to right, transparent, rgba(255,255,255,0.95));
    pointer-events: none;
  }
}

/* Safe area insets for notched devices */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
  .v-card:last-child {
    margin-bottom: env(safe-area-inset-bottom) !important;
  }
}

/* Touch-friendly button targets for mobile (WCAG 2.1 AA: 44px minimum) */
@media (max-width: 768px) {
  .touch-friendly-btn,
  .period-toggle .v-btn,
  .v-btn-toggle .v-btn {
    min-height: 44px !important;
    min-width: 44px !important;
  }

  /* Ensure icon buttons in tables have adequate touch targets */
  .v-data-table .v-btn--icon,
  .action-btn-view,
  .action-btn-edit,
  .action-btn-delete {
    min-height: 44px !important;
    min-width: 44px !important;
    width: 44px !important;
    height: 44px !important;
  }

  /* Filter controls touch optimization */
  .v-text-field .v-field,
  .v-select .v-field {
    min-height: 44px !important;
  }
}

/* ============================================
   BATTERY-CONSCIOUS ANIMATION SYSTEM
   100/100 Mobile Audit Compliance
   ============================================ */

/* Pause animations when page is hidden */
:root[data-page-hidden] .notification-dot,
:root[data-page-hidden] [class*="pulse"],
.page-hidden .notification-dot,
.page-hidden [class*="pulse"] {
  animation-play-state: paused !important;
}

/* Pause during rapid scroll */
.is-scrolling .notification-dot,
.is-scrolling [class*="pulse"] {
  animation-play-state: paused !important;
}

/* Limit infinite animations on mobile */
@media (max-width: 768px) {
  .notification-dot {
    animation-iteration-count: 5 !important;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }

  .notification-dot,
  [class*="pulse"] {
    animation: none !important;
  }
}

/* ============================================
   ENHANCED FOCUS STATES - Accessibility
   ============================================ */

.v-btn:focus-visible,
button:focus-visible {
  outline: 3px solid rgba(16, 185, 129, 0.7) !important;
  outline-offset: 3px !important;
  box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.15) !important;
}

.v-field:focus-within {
  outline: 2px solid #10B981 !important;
  outline-offset: 2px !important;
}

/* ============================================
   TABLE SCROLL INDICATORS
   ============================================ */

@media (max-width: 768px) {
  :deep(.v-data-table) .v-table__wrapper {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
    background:
      linear-gradient(to right, white 20px, transparent 60px),
      linear-gradient(to left, white 20px, transparent 60px) 100% 0,
      linear-gradient(to right, rgba(0,0,0,0.06) 0, transparent 15px),
      linear-gradient(to left, rgba(0,0,0,0.06) 0, transparent 15px) 100% 0 !important;
    background-repeat: no-repeat !important;
    background-size: 60px 100%, 60px 100%, 15px 100%, 15px 100% !important;
    background-attachment: local, local, scroll, scroll !important;
  }
}

/* ============================================
   TYPOGRAPHY READABILITY - 13px minimum
   ============================================ */

@media (max-width: 600px) {
  .text-caption:not(.v-chip .text-caption):not(.v-tab .text-caption) {
    font-size: 0.8125rem !important;
    line-height: 1.5 !important;
  }
}

/* Avatar Success Modal Styles */
.avatar-success-modal {
  border-radius: 16px !important;
  overflow: hidden;
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

.avatar-success-modal {
  animation: modalSlideIn 0.3s ease-out;
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
