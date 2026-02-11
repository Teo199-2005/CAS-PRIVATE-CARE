<template>
  <!-- Global Loading Overlay -->
  <LoadingOverlay 
    :visible="isPageLoading" 
    context="marketing"
    tagline="Marketing Portal"
  />

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
    :tier-label="marketingTierLabel"
    :tier="marketingTier"
    :tier-commission-per-hour="marketingTierCommissionPerHour"
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
            :date="stat.date" 
            icon-class="grey-darken-2"
            :stagger-index="index + 1"
          />
        </v-col>
      </v-row>

      <!-- Summary + Referral: two equal-width cards, compact grid inside -->
      <v-row align="stretch">
        <v-col cols="12" md="6">
          <v-card class="dashboard-summary-card h-100" elevation="1">
            <v-card-title class="dashboard-summary-header pa-4">
              <v-icon color="grey-darken-2" size="22" class="mr-2">mdi-chart-line</v-icon>
              <span class="section-title-compact grey--text text--darken-2">Previous Week Summary</span>
            </v-card-title>
            <v-card-text class="pa-4 pt-0">
              <div class="dashboard-summary-grid">
                <div class="summary-block summary-block--full">
                  <div class="summary-row summary-row--inline">
                    <span class="summary-label-compact">Clients Acquired</span>
                    <span class="summary-value-compact font-weight-medium">{{ weeklySummary.clients_acquired }} clients</span>
                  </div>
                  <v-progress-linear :model-value="Math.min((weeklySummary.clients_acquired / (weeklySummary.target || 1)) * 100, 100)" color="info" height="6" rounded class="mb-1 mt-1" />
                  <div class="text-caption text-grey">Target: {{ weeklySummary.target }} clients/week</div>
                </div>
                <div class="summary-row">
                  <span class="summary-label-compact">Previous Payout</span>
                  <span class="summary-value-compact summary-value--right info--text">${{ (Number(weeklySummary.previous_payout) || 0).toFixed(2) }} <span class="text-caption text-disabled">· {{ weeklySummary.previous_payout_date || 'N/A' }}</span></span>
                </div>
                <div class="summary-row">
                  <span class="summary-label-compact">Total Commission</span>
                  <span class="summary-value-compact summary-value--right success--text font-weight-bold">${{ totalCommission }}</span>
                </div>
                <div class="summary-row">
                  <span class="summary-label-compact">This Month</span>
                  <span class="summary-value-compact summary-value--right primary--text font-weight-bold">${{ monthlyCommission }}</span>
                </div>
                <div class="summary-row">
                  <span class="summary-label-compact">Active Clients</span>
                  <span class="summary-value-compact summary-value--right font-weight-bold">{{ activeClients }}</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card class="referral-card h-100" elevation="1">
            <v-card-title class="dashboard-summary-header pa-4">
              <v-icon color="grey-darken-2" size="22" class="mr-2">mdi-link-variant</v-icon>
              <span class="section-title-compact grey--text text--darken-2">My Referral Code</span>
            </v-card-title>
            <v-card-text class="pa-4 pt-0">
              <div class="referral-code-box mb-3">
                <div class="referral-code">{{ referralCode }}</div>
                <v-btn icon="mdi-content-copy" size="small" variant="text" @click="copyReferralCode" class="copy-btn" title="Copy Code" aria-label="Copy code"></v-btn>
              </div>
              <div class="share-buttons mb-3">
                <v-btn size="x-small" color="success" variant="tonal" @click="shareViaWhatsApp" class="mr-1">
                  <v-icon size="14">mdi-whatsapp</v-icon>
                </v-btn>
                <v-btn size="x-small" color="info" variant="tonal" @click="shareViaEmail" class="mr-1">
                  <v-icon size="14">mdi-email</v-icon>
                </v-btn>
                <v-btn size="x-small" color="primary" variant="tonal" @click="shareViaSMS">
                  <v-icon size="14">mdi-message-text</v-icon>
                </v-btn>
              </div>
              <v-divider class="my-2" />
              <div class="d-flex justify-space-between text-body-2">
                <span class="text-grey">Times Used</span>
                <span class="font-weight-bold">{{ referralCodeStats.usageCount }}</span>
              </div>
              <div class="d-flex justify-space-between text-body-2 mt-1">
                <span class="text-grey">Total Earned</span>
                <span class="font-weight-bold grey--text text--darken-2">${{ referralCodeStats.totalEarned }}</span>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- My Clients Performance: full width -->
      <v-row class="mt-4">
        <v-col cols="12">
          <v-card elevation="0" class="compact-card">
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
    <div v-if="currentSection === 'analytics'" class="marketing-analytics">
      <!-- Summary Header (like Client Analytics) -->
      <v-card elevation="0" class="mb-6 analytics-summary-card">
        <v-card-text class="pa-6 pa-md-8">
          <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center gap-4">
            <div class="analytics-summary-stats">
              <div class="summary-stat-item">
                <div class="summary-stat-amount primary--text">${{ analyticsSummary.totalCommission }}</div>
                <div class="summary-stat-label">Total Commission</div>
              </div>
              <v-divider vertical class="d-none d-md-block mx-4" />
              <div class="summary-stat-item">
                <div class="summary-stat-amount info--text">${{ analyticsSummary.thisMonth }}</div>
                <div class="summary-stat-label">This Month</div>
              </div>
              <v-divider vertical class="d-none d-md-block mx-4" />
              <div class="summary-stat-item">
                <div class="summary-stat-amount success--text">{{ analyticsSummary.clientsReferred }}</div>
                <div class="summary-stat-label">Clients Referred</div>
              </div>
              <v-divider vertical class="d-none d-md-block mx-4" />
              <div class="summary-stat-item tier-summary-cell" :class="'tier-summary-cell--' + tierKey.toLowerCase()">
                <div class="tier-summary-content">
                  <v-icon class="tier-summary-medal" size="22">{{ tierKey === 'Platinum' ? 'mdi-medal' : tierKey === 'Gold' ? 'mdi-medal' : 'mdi-medal' }}</v-icon>
                  <div>
                    <div class="summary-stat-amount tier-summary-amount">{{ marketingTierLabel || 'Silver Partner' }} · ${{ displayCommissionRate }}/hr</div>
                    <div class="summary-stat-label">Current Tier</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </v-card-text>
      </v-card>

      <!-- Top metric cards -->
      <v-row class="mb-4">
        <v-col v-for="metric in analyticsMetrics" :key="metric.title" cols="12" sm="6" md="3">
          <v-card
            elevation="0"
            class="compact-stat-card analytics-stat-card"
            :class="metric.tierKey ? 'tier-stat-card tier-stat-card--' + metric.tierKey.toLowerCase() : ''"
          >
            <v-card-text class="pa-4">
              <div class="d-flex align-center">
                <v-icon
                  :color="metric.tierKey ? undefined : metric.color"
                  :class="metric.tierKey ? 'tier-stat-icon tier-stat-icon--' + metric.tierKey.toLowerCase() : ''"
                  size="28"
                  class="mr-3"
                >{{ metric.icon }}</v-icon>
                <div class="flex-grow-1">
                  <div class="stat-value" :class="metric.tierKey ? 'tier-stat-value' : metric.color + '--text'">{{ metric.value }}</div>
                  <div class="stat-label">{{ metric.title }}</div>
                  <div v-if="metric.change" class="stat-change" :class="metric.changeColor">{{ metric.change }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-row>
        <!-- Commission Over Time -->
        <v-col cols="12" md="8">
          <v-card elevation="0" class="mb-3 compact-chart-card">
            <v-card-title class="compact-header pa-4">
              <div class="d-flex justify-space-between align-center flex-wrap gap-2">
                <span class="compact-title primary--text">Commission Over Time</span>
                <span class="text-caption text-grey">Last 6 months</span>
              </div>
            </v-card-title>
            <v-card-text class="pa-4">
              <div class="chart-container" style="height: 220px; position: relative;">
                <canvas ref="performanceChart"></canvas>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Quick Stats -->
        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-3 h-100">
            <v-card-title class="compact-header pa-4">
              <span class="compact-title primary--text">Quick Stats</span>
            </v-card-title>
            <v-card-text class="pa-4">
              <div class="quick-stat-item mb-4">
                <v-icon color="primary" class="mr-3" size="28">mdi-account-multiple</v-icon>
                <div>
                  <div class="quick-stat-value">{{ myClients.length }}</div>
                  <div class="quick-stat-label">Total Clients</div>
                </div>
              </div>
              <v-divider class="my-3" />
              <div class="quick-stat-item mb-4">
                <v-icon color="info" class="mr-3" size="28">mdi-clock-outline</v-icon>
                <div>
                  <div class="quick-stat-value">{{ analyticsQuickStats.totalHours }}h</div>
                  <div class="quick-stat-label">Total Hours</div>
                </div>
              </div>
              <v-divider class="my-3" />
              <div class="quick-stat-item mb-4">
                <v-icon color="success" class="mr-3" size="28">mdi-account-check</v-icon>
                <div>
                  <div class="quick-stat-value">{{ activeClients }}</div>
                  <div class="quick-stat-label">Active Clients</div>
                </div>
              </div>
              <v-divider class="my-3" />
              <div class="quick-stat-item">
                <v-icon color="warning" class="mr-3" size="28">mdi-tag</v-icon>
                <div>
                  <div class="quick-stat-value">{{ referralCodeStats.usageCount }}</div>
                  <div class="quick-stat-label">Referral Code Uses</div>
                </div>
              </div>
              <v-divider class="my-3" />
              <div class="quick-stat-item">
                <v-icon color="indigo" class="mr-3" size="28">mdi-currency-usd</v-icon>
                <div>
                  <div class="quick-stat-value">${{ displayCommissionRate }}/hr</div>
                  <div class="quick-stat-label">Your Commission Rate ({{ marketingTierLabel || 'Silver' }})</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

    </div>

    <!-- Payment Information Section -->
    <div v-if="currentSection === 'payments'">
      <v-row>
        <v-col cols="12" md="8">
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
              <span class="section-title primary--text">Payout Method</span>
              <v-btn 
                v-if="!stripeConnected" 
                color="primary" 
                prepend-icon="mdi-wallet-plus" 
                href="/connect-bank-account-marketing"
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
                  Connect your preferred payout method via Stripe to receive weekly commission payments.<br>
                  Your payment information is securely encrypted and never shared.
                </p>
                
                <!-- Available Payout Methods -->
                <v-alert color="info" variant="tonal" class="mb-4 text-left">
                  <div class="font-weight-bold mb-3">Available Payout Methods:</div>
                  <div class="d-flex align-center mb-2">
                    <v-icon color="info" class="mr-2">mdi-bank</v-icon>
                    <span><strong>Bank Account</strong> - Direct deposit (ACH) to your bank</span>
                  </div>
                  <div class="d-flex align-center mb-2">
                    <v-icon color="info" class="mr-2">mdi-credit-card</v-icon>
                    <span><strong>Debit Card</strong> - Instant transfer to your card</span>
                  </div>
                </v-alert>
                
                <v-btn
                  color="primary"
                  size="large"
                  prepend-icon="mdi-bank"
                  href="/connect-bank-account-marketing"
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
                      <span class="font-weight-medium">{{ marketingTierLabel }} · ${{ displayCommissionRate }}/hr</span>
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
                        href="/connect-bank-account-marketing"
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
                <span class="summary-label text-h6">Pending Commission</span>
                <span class="summary-value text-h4 warning--text">${{ pendingCommission || '0.00' }}</span>
              </div>
              <v-divider class="my-4" />
              <div class="summary-item">
                <span class="summary-label">Total Earned</span>
                <span class="summary-value success--text">${{ totalCommission }}</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">This Month</span>
                <span class="summary-value primary--text">${{ monthlyCommission }}</span>
              </div>
              <div class="summary-item">
                <span class="summary-label">Last Payout</span>
                <span class="summary-value">${{ lastPayoutAmount || '0.00' }}</span>
              </div>
              <v-divider class="my-4" />
              <div class="summary-item">
                <span class="summary-label">Next Payout</span>
                <span class="summary-value font-weight-bold">{{ nextPayoutDate }}</span>
              </div>
              <v-divider class="my-4" />
              
              <!-- Pending Commission Notice -->
              <div v-if="parseFloat(pendingCommission || 0) > 0" class="mt-4">
                <v-alert type="info" variant="tonal" density="compact" class="mb-0">
                  <div class="text-body-2">
                    <v-icon size="small" class="mr-1">mdi-information</v-icon>
                    Your pending commission of <strong>${{ pendingCommission }}</strong> will be automatically transferred on {{ nextPayoutDate }}.
                  </div>
                </v-alert>
              </div>
              
              <div v-else class="mt-4">
                <v-alert type="success" variant="tonal" density="compact" class="mb-0">
                  <div class="text-body-2">
                    <v-icon size="small" class="mr-1">mdi-check-circle</v-icon>
                    All Caught Up! You have no pending commission payments.
                  </div>
                </v-alert>
              </div>
            </v-card-text>
          </v-card>

          <!-- Commission Info Card -->
          <v-card elevation="0">
            <v-card-title class="card-header pa-6">
              <span class="text-subtitle-1 font-weight-bold text-grey-darken-2">How Commission Works</span>
            </v-card-title>
            <v-card-text class="pa-6">
              <div class="d-flex align-center mb-3">
                <v-icon color="primary" class="mr-3">mdi-account-plus</v-icon>
                <span class="text-body-2">Refer clients to CAS Private Care</span>
              </div>
              <div class="d-flex align-center mb-3">
                <v-icon color="primary" class="mr-3">mdi-clock-outline</v-icon>
                <span class="text-body-2">Earn ${{ displayCommissionRate }}/hr (Silver $1.00, Gold $1.25, Platinum $1.50)</span>
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
                  <img v-if="userAvatar" :src="userAvatar" :alt="`${profile.company_name || 'Marketing Agent'} profile photo`" style="width: 100%; height: 100%; object-fit: cover;" />
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

    <!-- Remove Payout Method Dialog (matches CaregiverDashboard style) -->
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

const { notification, success, error, info } = useNotification();
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
// Total Client Rate: $42/hr (with $3 referral discount)
//
// Pricing breakdown (without referral code):
// - Caregiver: $28.00/hr
// - Agency: $16.50/hr
// - Training Center: $0.50/hr
// Total Client Rate: $45/hr (without referral)
const myClients = ref([]);

const loadMarketingStats = async () => {
  try {
    const url = `${window.location.origin}/api/marketing/stats?user_id=${marketingUserId.value || ''}`;
    const response = await fetch(url, {
      credentials: 'include',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok || (data && typeof data.my_clients === 'undefined' && !Array.isArray(data.clients))) {
      return;
    }
    const clientsList = Array.isArray(data.clients) ? data.clients : [];
    const myClientsCount = data.my_clients != null ? Number(data.my_clients) : clientsList.length;
    stats.value = [
      { title: 'My Clients', value: String(myClientsCount), icon: 'mdi-account-multiple', color: 'grey-darken-2', change: `+${data.weekly_summary?.clients_acquired ?? 0} this week`, changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
      { title: 'Active Bookings', value: String(data.active_bookings ?? 0), icon: 'mdi-calendar-check', color: 'grey-darken-2', change: '+' + (data.active_bookings ?? 0) + ' active', changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
      { title: 'Total Commission', value: '$' + (typeof data.total_commission === 'number' ? data.total_commission.toFixed(2) : (data.total_commission ?? '0.00')), icon: 'mdi-currency-usd', color: 'grey-darken-2', change: '+$' + (data.pending_commission ?? '0.00') + ' pending', changeColor: 'text-success', changeIcon: 'mdi-arrow-up', date: new Date().toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) },
    ];
    myClients.value = clientsList;
    accountBalance.value = parseFloat(data.account_balance) || 0;
    weeklySummary.value = data.weekly_summary ?? {
      clients_acquired: 0,
      target: 10,
      previous_payout: 0,
      previous_payout_date: null
    };
    if (data.monthly_commission != null) monthlyCommission.value = parseFloat(data.monthly_commission).toFixed(2);
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

const analyticsSummary = computed(() => ({
  totalCommission: totalCommission.value,
  thisMonth: monthlyCommission.value,
  clientsReferred: myClients.value.length,
  tierLabel: marketingTierLabel.value || 'Silver Partner'
}));

const analyticsMetrics = computed(() => [
  { title: 'Total Commission', value: '$' + totalCommission.value, icon: 'mdi-currency-usd', color: 'success', change: '', changeColor: '' },
  { title: 'Clients Referred', value: String(myClients.value.length), icon: 'mdi-account-multiple', color: 'info', change: '', changeColor: '' },
  { title: 'Code Uses', value: String(referralCodeStats.value.usageCount || 0), icon: 'mdi-tag-multiple', color: 'primary', change: '', changeColor: '' },
  { title: 'Tier', value: marketingTierLabel.value || 'Silver', icon: 'mdi-medal', color: 'warning', change: marketingTierCommissionPerHour.value != null ? '$' + Number(marketingTierCommissionPerHour.value).toFixed(2) + '/hr' : '', changeColor: 'text-grey', tierKey: tierKey.value }
]);

const analyticsQuickStats = computed(() => {
  const hours = myClients.value.reduce((sum, c) => sum + parseFloat(c.totalHours) || 0, 0);
  return { totalHours: hours.toFixed(1) };
});

const statusChartActive = computed(() => myClients.value.filter(c => c.status === 'Active').length);
const statusChartInactive = computed(() => myClients.value.filter(c => c.status !== 'Active').length);

function getTopClientColor(index) {
  const colors = ['primary', 'info', 'success', 'warning'];
  return colors[index % colors.length];
}

const totalCommission = computed(() => {
  return myClients.value.reduce((sum, client) => sum + parseFloat(client.commission), 0).toFixed(2);
});

const monthlyCommission = ref('420.00');
const pendingCommission = ref('0.00');
const lastPayoutAmount = ref('0.00');
const stripeConnected = ref(false);
const payoutMethodName = ref(''); // e.g. "Debit Card (**** 4242)" or "Test Bank (Routing: ...)"
const showRemovePayoutDialog = ref(false);
const removingPayoutMethod = ref(false);

const activeClients = computed(() => {
  return myClients.value.filter(client => client.status === 'Active').length;
});

const topClients = computed(() => {
  return myClients.value
    .filter(c => c.status === 'Active')
    .sort((a, b) => parseFloat(b.commission) - parseFloat(a.commission))
    .slice(0, 5)
    .map(c => ({ ...c, totalBookings: c.totalBookings ?? 0, totalHours: c.totalHours ?? 0 }));
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
const marketingTierLabel = ref('');
const marketingTier = ref('');
const marketingTierCommissionPerHour = ref(null);
const referralCodeStats = ref({
  discount_per_hour: 3.00,
  commission_per_hour: 1.00,
  usageCount: 0,
  totalEarned: '0.00',
});

// Tier key for styling (Silver | Gold | Platinum)
const tierKey = computed(() => {
  const t = (marketingTier.value || marketingTierLabel.value || '').toLowerCase();
  if (t.includes('platinum')) return 'Platinum';
  if (t.includes('gold')) return 'Gold';
  return 'Silver';
});

// Display rate: when backend returns 0 (no paid clients yet), show tier's potential rate
const displayCommissionRate = computed(() => {
  const rate = marketingTierCommissionPerHour.value;
  const tierRates = { Silver: '1.00', Gold: '1.25', Platinum: '1.50' };
  if (rate != null && rate > 0) return Number(rate).toFixed(2);
  return tierRates[marketingTier.value] || '1.00';
});

// Computed property for the full referral link
const referralLink = computed(() => {
  if (referralCode.value && referralCode.value !== 'Loading...' && referralCode.value !== 'Not Generated' && referralCode.value !== 'Contact Admin') {
    return `${window.location.origin}/book?ref=${referralCode.value}`;
  }
  return 'Generating...';
});

// Load referral code from API
const loadReferralCode = async () => {
  try {
    const response = await fetch('/api/referral-codes/my-code');
    const data = await response.json();
    if (data.success && data.data) {
      referralCode.value = data.data.code;
      marketingTierLabel.value = data.data.tier_label || data.data.tierLabel || '';
      marketingTier.value = data.data.tier || '';
      marketingTierCommissionPerHour.value = data.data.commission_per_hour != null ? parseFloat(data.data.commission_per_hour) : 1.00;
      referralCodeStats.value = {
        discount_per_hour: parseFloat(data.data.discount_per_hour) || 3.00,
        commission_per_hour: parseFloat(data.data.commission_per_hour) || 1.00,
        usageCount: data.data.usage_count || 0,
        totalEarned: parseFloat(data.data.total_commission_earned || 0).toFixed(2),
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
        role: data.user.role || 'Marketing Specialist',
        created_at: data.user.created_at || ''
      };
      marketingUserId.value = data.user.id;
      if (data.user.avatar) {
        userAvatar.value = `/storage/${data.user.avatar}`;
      }
      // Payout method: show connected when user has stripe_connect_id or bank_name (from connect page)
      const hasPayout = !!(data.user.stripe_connect_id || (data.user.bank_name && data.user.bank_name.trim()));
      stripeConnected.value = hasPayout;
      payoutMethodName.value = (data.user.bank_name && data.user.bank_name.trim()) ? data.user.bank_name.trim() : 'Bank Transfer (ACH)';
      // Load marketing stats after we have the user ID (await so initial load includes stats)
      await loadMarketingStats();
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

const getCsrfToken = () => {
  const meta = document.querySelector('meta[name="csrf-token"]');
  if (meta?.getAttribute('content')) return meta.getAttribute('content');
  const match = document.cookie?.match(/XSRF-TOKEN=([^;]+)/);
  if (match?.[1]) {
    try { return decodeURIComponent(match[1]); } catch { return ''; }
  }
  return '';
};

const removePayoutMethod = async () => {
  removingPayoutMethod.value = true;
  const csrfToken = getCsrfToken();
  try {
    const response = await fetch('/api/marketing/payout-method', {
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

// Copy the full referral link
const copyReferralLink = () => {
  navigator.clipboard.writeText(referralLink.value);
  success('Referral link copied to clipboard!', 'Link Copied');
};

// Share via WhatsApp
const shareViaWhatsApp = () => {
  const message = encodeURIComponent(`Book professional caregiving services with CAS Private Care! Use my referral link to get $3/hour discount: ${referralLink.value}\n\nI earn commission when you book.`);
  window.open(`https://wa.me/?text=${message}`, '_blank');
};

// Share via Email
const shareViaEmail = () => {
  const subject = encodeURIComponent('Professional Caregiving Services - CAS Private Care');
  const body = encodeURIComponent(`Hi,\n\nI'd like to recommend CAS Private Care for professional caregiving services in New York.\n\nUse my referral link to get $3/hour discount on your first booking:\n${referralLink.value}\n\nI earn commission when you book.\n\nThey offer verified caregivers, housekeeping services, and more!\n\nBest regards`);
  window.open(`mailto:?subject=${subject}&body=${body}`, '_blank');
};

// Share via SMS
const shareViaSMS = () => {
  const message = encodeURIComponent(`Get $3/hour off caregiving services with CAS Private Care! Use my link: ${referralLink.value} I earn commission when you book.`);
  window.open(`sms:?body=${message}`, '_blank');
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
  role: 'Marketing Specialist',
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
const marketingUserId = ref(null);
const showAvatarSuccessModal = ref(false);

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

const closeAvatarSuccessModal = () => {
  showAvatarSuccessModal.value = false;
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
let performanceChartInstance = null;
let statusChartInstance = null;

const initCharts = () => {
  if (!window.Chart) {
    setTimeout(initCharts, 100);
    return;
  }
  if (currentSection.value !== 'analytics') return;

  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
  const now = new Date();
  const thisMonthIndex = now.getMonth();
  const monthLabels = [];
  const monthData = [];
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    monthLabels.push(d.toLocaleString('default', { month: 'short' }));
    if (i === 0) {
      monthData.push(parseFloat(monthlyCommission.value) || 0);
    } else {
      monthData.push(0);
    }
  }

  if (performanceChart.value) {
    if (performanceChartInstance) performanceChartInstance.destroy();
    const ctx = performanceChart.value.getContext('2d');
    performanceChartInstance = new window.Chart(ctx, {
      type: 'line',
      data: {
        labels: monthLabels,
        datasets: [{
          label: 'Commission ($)',
          data: monthData,
          borderColor: '#6366f1',
          backgroundColor: 'rgba(99, 102, 241, 0.1)',
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
          y: { display: true, grid: { color: '#f5f5f5' }, beginAtZero: true }
        }
      }
    });
  }

  if (statusChart.value) {
    if (statusChartInstance) statusChartInstance.destroy();
    const ctx = statusChart.value.getContext('2d');
    const activeCount = statusChartActive.value;
    const inactiveCount = statusChartInactive.value;
    statusChartInstance = new window.Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Active', 'Inactive'],
        datasets: [{
          data: [activeCount || 0, inactiveCount || 0].map(v => v || 0.1),
          backgroundColor: ['#10b981', '#e5e7eb'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        cutout: '65%'
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
  localStorage.setItem('marketingSection', newVal);
  if (newVal === 'payment') {
    loadPaymentMethods();
    checkMarketingApplicationStatus();
  }
  if (newVal === 'dashboard' || newVal === 'clients') {
    loadMarketingStats();
  }
  if (newVal === 'analytics') {
    loadMarketingStats().then(() => setTimeout(initCharts, 200));
  }
});

onMounted(async () => {
  // Show loading overlay
  isPageLoading.value = true;
  loadingContext.value = 'dashboard';
  loadingProgress.value = 0;
  
  const loadingTasks = [
    { fn: loadNYLocationData, weight: 10 },
    { fn: loadProfile, weight: 30 },
    { fn: loadReferralCode, weight: 20 },
    { fn: checkMarketingApplicationStatus, weight: 20 }
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

/* Marketing Analytics */
.marketing-analytics .analytics-summary-card {
  border-radius: 16px;
  border: 1px solid #e5e7eb;
  background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
}

.analytics-summary-stats {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
}

.summary-stat-item {
  text-align: center;
}

.summary-stat-amount {
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.summary-stat-label {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

/* Current Tier summary cell – tier-colored */
.tier-summary-cell {
  padding: 12px 16px !important;
  border-radius: 12px;
  margin: 0 -4px;
}
.tier-summary-cell--silver {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border: 1px solid #cbd5e1;
  box-shadow: 0 1px 3px rgba(100, 116, 139, 0.15);
}
.tier-summary-cell--silver .tier-summary-amount,
.tier-summary-cell--silver .summary-stat-label { color: #475569 !important; }
.tier-summary-cell--silver .tier-summary-medal { color: #64748b !important; }
.tier-summary-cell--gold {
  background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
  border: 1px solid #fcd34d;
  box-shadow: 0 1px 3px rgba(217, 119, 6, 0.15);
}
.tier-summary-cell--gold .tier-summary-amount,
.tier-summary-cell--gold .summary-stat-label { color: #92400e !important; }
.tier-summary-cell--gold .tier-summary-medal { color: #d97706 !important; }
.tier-summary-cell--platinum {
  background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
  border: 1px solid #818cf8;
  box-shadow: 0 1px 3px rgba(67, 56, 202, 0.15);
}
.tier-summary-cell--platinum .tier-summary-amount,
.tier-summary-cell--platinum .summary-stat-label { color: #3730a3 !important; }
.tier-summary-cell--platinum .tier-summary-medal { color: #4338ca !important; }
.tier-summary-content {
  display: flex;
  align-items: center;
  gap: 10px;
  justify-content: center;
}
.tier-summary-content .summary-stat-amount { font-size: 1.1rem; }
.tier-summary-medal { flex-shrink: 0; }

/* Tier stat card (metric card) – tier-colored */
.tier-stat-card.tier-stat-card--silver {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
}
.tier-stat-card--silver .tier-stat-icon--silver { color: #64748b !important; }
.tier-stat-card--silver .tier-stat-value { color: #475569 !important; }
.tier-stat-card.tier-stat-card--gold {
  background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%) !important;
  border: 1px solid #fcd34d;
  border-radius: 12px;
}
.tier-stat-card--gold .tier-stat-icon--gold { color: #d97706 !important; }
.tier-stat-card--gold .tier-stat-value { color: #92400e !important; }
.tier-stat-card.tier-stat-card--platinum {
  background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%) !important;
  border: 1px solid #818cf8;
  border-radius: 12px;
}
.tier-stat-card--platinum .tier-stat-icon--platinum { color: #4338ca !important; }
.tier-stat-card--platinum .tier-stat-value { color: #3730a3 !important; }

.marketing-analytics .analytics-stat-card {
  transition: box-shadow 0.2s ease;
}

.marketing-analytics .analytics-stat-card:hover {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1) !important;
}

.compact-header .compact-title {
  font-size: 1rem;
  font-weight: 600;
}

.quick-stat-item {
  display: flex;
  align-items: center;
}

.quick-stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

.quick-stat-label {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
}

.chart-legend .legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-right: 8px;
}

.chart-legend .active-dot { background: #10b981; }
.chart-legend .inactive-dot { background: #e5e7eb; }

.top-client-row {
  border-bottom: 1px solid #f0f0f0;
}

.top-client-row:last-child {
  border-bottom: none;
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

/* Referral Link Styles */
.referral-card {
  background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%) !important;
}

.share-buttons {
  display: flex;
  justify-content: center;
  gap: 8px;
}

.referral-stats {
  font-size: 0.75rem;
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

/* Dashboard summary + referral: equal-height cards */
.dashboard-summary-card,
.referral-card.h-100 {
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.dashboard-summary-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-bottom: 1px solid #e2e8f0;
  border-radius: 12px 12px 0 0;
  font-weight: 600;
}

.dashboard-summary-grid {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.dashboard-summary-grid .summary-block--full {
  padding-bottom: 12px;
  margin-bottom: 4px;
  border-bottom: 1px solid #f1f5f9;
}

.dashboard-summary-grid .summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.875rem;
}

.dashboard-summary-grid .summary-row:last-child {
  border-bottom: none;
}

.dashboard-summary-grid .summary-value--right {
  min-width: 5rem;
  text-align: right;
  white-space: nowrap;
}

.h-100 {
  height: 100%;
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

/* Bank Account Card Stripe Style */
.bank-account-card-stripe {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #e2e8f0;
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

/* Success Checkmark Animation */
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
  0% {
    transform: rotate(-45deg);
  }
  5% {
    transform: rotate(-45deg);
  }
  12% {
    transform: rotate(-405deg);
  }
  100% {
    transform: rotate(-405deg);
  }
}

@keyframes icon-line-tip {
  0% {
    width: 0;
    left: 1px;
    top: 19px;
  }
  54% {
    width: 0;
    left: 1px;
    top: 19px;
  }
  70% {
    width: 50px;
    left: -8px;
    top: 37px;
  }
  84% {
    width: 17px;
    left: 21px;
    top: 48px;
  }
  100% {
    width: 25px;
    left: 14px;
    top: 46px;
  }
}

@keyframes icon-line-long {
  0% {
    width: 0;
    right: 46px;
    top: 54px;
  }
  65% {
    width: 0;
    right: 46px;
    top: 54px;
  }
  84% {
    width: 55px;
    right: 0px;
    top: 35px;
  }
  100% {
    width: 47px;
    right: 8px;
    top: 38px;
  }
}

/* Modal entrance animation */
.avatar-success-modal {
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  0% {
    opacity: 0;
    transform: scale(0.8) translateY(-20px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
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

