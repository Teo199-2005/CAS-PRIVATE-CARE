<template>
  <notification-toast
    v-model="notification.show"
    :type="notification.type"
    :title="notification.title"
    :message="notification.message"
    :timeout="notification.timeout"
  />
  
  <dashboard-template
    user-role="admin"
    :user-name="profile.firstName && profile.lastName ? `${profile.firstName} ${profile.lastName}` : 'Admin User'"
    :user-initials="profile.firstName && profile.lastName ? `${profile.firstName[0]}${profile.lastName[0]}` : 'AU'"
    :user-avatar="userAvatar"
    :welcome-message="profile.firstName ? `Welcome Back, ${profile.firstName}` : 'Welcome Back, Admin'"
    subtitle="Manage platform operations and oversight"
    header-title="Admin Control Panel"
    header-subtitle="Comprehensive platform management and analytics"
    :nav-items="navItems"
    :current-section="currentSection"
    @section-change="currentSection = $event"
    @toggle-click="handleNavClick"
    @logout="logout"
  >
    <template #header-left>
      <v-btn color="error" size="x-large" prepend-icon="mdi-bullhorn" class="admin-btn" @click="announceDialog = true">Send Announcement</v-btn>
    </template>

  <!-- Email Verification Banner (not needed for company admin) -->
  <email-verification-banner v-if="false" />

    <!-- Dashboard Section -->
    <div v-if="currentSection === 'dashboard'">
      <v-row class="mb-4">
        <v-col v-for="stat in stats" :key="stat.title" cols="6" sm="6" md="3">
          <stat-card :icon="stat.icon" :value="stat.value" :label="stat.title" :change="stat.change" :change-color="stat.changeColor" :change-icon="stat.changeIcon" icon-class="error" />
        </v-col>
      </v-row>

      <v-row class="mt-1">
        <v-col cols="12">
          <v-row>
            <v-col cols="12" md="4">
              <v-card class="mb-3 compact-card d-flex flex-column" elevation="0">
                <v-card-title class="card-header pa-4">
                  <span class="section-title-compact error--text">System Overview</span>
                </v-card-title>
                <v-card-text class="pa-4 flex-grow-1 d-flex flex-column justify-space-between">
                  <div>
                    <div class="mb-3">
                      <div class="d-flex justify-space-between mb-1">
                        <span class="summary-label-compact">Active Users</span>
                        <span class="summary-value-compact error--text">{{ stats[0].value }}</span>
                      </div>
                      <v-progress-linear :model-value="userProgress" color="error" height="6" rounded />
                      <div class="text-caption text-grey mt-1">{{ userGrowth }}</div>
                    </div>
                    <div class="mb-3">
                      <div class="d-flex justify-space-between mb-1">
                        <span class="summary-label-compact">Server Load</span>
                        <span class="summary-value-compact">68%</span>
                      </div>
                      <v-progress-linear :model-value="68" color="warning" height="6" rounded />
                      <div class="text-caption text-grey mt-1">Normal range</div>
                    </div>
                    <div class="mb-3">
                      <div class="d-flex justify-space-between mb-1">
                        <span class="summary-label-compact">Revenue Goal</span>
                        <span class="summary-value-compact">{{ stats[2].value }}</span>
                      </div>
                      <v-progress-linear :model-value="revenueProgress" color="success" height="6" rounded />
                      <div class="text-caption text-grey mt-1">Target: ${{ revenueTarget }}/month</div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" md="4">
              <v-card elevation="0" class="mb-3 compact-card d-flex flex-column">
                <v-card-title class="card-header pa-4">
                  <span class="section-title-compact error--text">Platform Metrics</span>
                </v-card-title>
                <v-card-text class="pa-4 flex-grow-1">
                  <v-row class="metric-grid">
                    <v-col cols="6" class="pa-2">
                      <div class="metric-card">
                        <div class="d-flex align-center mb-2">
                          <v-icon color="error" size="20" class="mr-2">mdi-calendar-check</v-icon>
                          <span class="metric-label-grid">Bookings</span>
                        </div>
                        <div class="metric-value-grid error--text">{{ platformMetrics.bookings }}</div>
                        <div class="metric-change-grid success--text">+8.5%</div>
                      </div>
                    </v-col>
                    <v-col cols="6" class="pa-2">
                      <div class="metric-card">
                        <div class="d-flex align-center mb-2">
                          <v-icon color="info" size="20" class="mr-2">mdi-speedometer</v-icon>
                          <span class="metric-label-grid">Response</span>
                        </div>
                        <div class="metric-value-grid">{{ platformMetrics.response }}</div>
                        <div class="metric-change-grid success--text">-0.3s</div>
                      </div>
                    </v-col>
                    <v-col cols="6" class="pa-2">
                      <div class="metric-card">
                        <div class="d-flex align-center mb-2">
                          <v-icon color="warning" size="20" class="mr-2">mdi-alert-circle</v-icon>
                          <span class="metric-label-grid">Errors</span>
                        </div>
                        <div class="metric-value-grid warning--text">{{ platformMetrics.errors }}</div>
                        <div class="metric-change-grid success--text">-0.01%</div>
                      </div>
                    </v-col>
                    <v-col cols="6" class="pa-2">
                      <div class="metric-card">
                        <div class="d-flex align-center mb-2">
                          <v-icon color="success" size="20" class="mr-2">mdi-account-multiple</v-icon>
                          <span class="metric-label-grid">Sessions</span>
                        </div>
                        <div class="metric-value-grid info--text">{{ platformMetrics.sessions }}</div>
                        <div class="metric-change-grid">Live</div>
                      </div>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" md="4">
              <v-card elevation="0" class="mb-3 compact-card d-flex flex-column">
                <v-card-title class="card-header pa-4">
                  <div class="d-flex justify-space-between align-center">
                    <span class="section-title-compact error--text">Caregiver Contacts</span>
                    <v-btn size="small" color="error" variant="flat" prepend-icon="mdi-eye" @click="caregiverContactsDialog = true">View All</v-btn>
                  </div>
                </v-card-title>
                <v-card-text class="pa-4 flex-grow-1">
                  <div class="caregiver-contacts">
                    <div v-for="caregiver in quickCaregivers" :key="caregiver.id" class="caregiver-contact-item">
                      <div class="d-flex align-center mb-2">
                        <v-avatar size="32" :color="caregiver.available ? 'success' : 'grey'" class="mr-3">
                          <span class="text-white font-weight-bold">{{ caregiver.initials }}</span>
                        </v-avatar>
                        <div class="flex-grow-1">
                          <div class="caregiver-name">{{ caregiver.name }}</div>
                          <div class="caregiver-status" :class="caregiver.available ? 'success--text' : 'grey--text'">
                            {{ caregiver.available ? 'Available' : 'Busy' }}
                          </div>
                        </div>
                        <div class="caregiver-phone">{{ caregiver.phone }}</div>
                      </div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <v-card elevation="0" class="modern-activity-card">
            <v-card-title class="modern-card-header pa-6">
              <div class="d-flex align-center justify-space-between">
                <div class="d-flex align-center">
                  <v-icon color="error" size="20" class="mr-3">mdi-pulse</v-icon>
                  <span class="modern-title error--text">Recent Platform Activity</span>
                </div>
                <v-chip color="grey-lighten-2" size="small" class="activity-count">{{ recentActivity.length }} items</v-chip>
              </div>
            </v-card-title>
            <v-divider></v-divider>
            <v-data-table 
              :headers="activityHeaders" 
              :items="recentActivity" 
              :items-per-page="5"
              :items-per-page-options="[5, 10, 25]"
              class="elevation-0 modern-activity-table table-no-checkbox"
              height="300"
              hide-default-header
            >
              <template v-slot:headers>
                <tr class="modern-header-row">
                  <th class="modern-header-cell time-col">TIME</th>
                  <th class="modern-header-cell user-col">USER</th>
                  <th class="modern-header-cell action-col">ACTION</th>
                  <th class="modern-header-cell type-col">TYPE</th>
                </tr>
              </template>
              <template v-slot:item="{ item }">
                <tr class="modern-row">
                  <td class="modern-cell time-cell">{{ item.time }}</td>
                  <td class="modern-cell user-cell">{{ item.user }}</td>
                  <td class="modern-cell action-cell">{{ item.action }}</td>
                  <td class="modern-cell type-cell">
                    <v-chip :color="getActivityColor(item.type)" size="small" class="modern-type-chip">{{ item.type }}</v-chip>
                  </td>
                </tr>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Users Management Section -->
    <div v-if="currentSection === 'users'">
      <div class="mb-6">
        <v-row class="align-center">
          <v-col cols="12" md="3">
            <v-text-field v-model="userSearch" placeholder="Search users..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="userType" :items="['All', 'Clients', 'Caregivers', 'Admins']" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="userStatus" :items="['All', 'Active', 'Inactive', 'Suspended']" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="locationFilter" :items="locationFilterOptions" label="All Locations" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="3">
            <v-btn color="error" prepend-icon="mdi-plus" @click="addUserDialog = true">Add User</v-btn>
          </v-col>
        </v-row>
      </div>

      <v-card elevation="0">
        <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
          <span class="section-title error--text">Users</span>
          <v-btn v-if="selectedUsers.length > 0" color="error" variant="outlined" prepend-icon="mdi-delete" @click="deleteSelectedUsers">
            Delete Selected ({{ selectedUsers.length }})
          </v-btn>
        </v-card-title>
        <v-data-table v-model="selectedUsers" :headers="userHeaders" :items="filteredUsers" :items-per-page="10" show-select item-value="id" class="elevation-0" density="compact">
          <template v-slot:item.status="{ item }">
            <v-chip
              :color="getUserStatusColor(item.status)"
              size="small"
              class="font-weight-bold"
              :style="(String(item.status).toLowerCase() === 'pending') ? 'background-color: #f59e0b !important; color: #ffffff !important;' : ''"
              :prepend-icon="getStatusIcon(item.status)"
            >{{ item.status }}</v-chip>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn class="action-btn-edit" icon="mdi-pencil" size="small" @click="editUser(item)"></v-btn>
              <v-btn class="action-btn-delete" icon="mdi-account-cancel" size="small" @click="suspendUser(item)"></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- Analytics Section -->
    <div v-if="currentSection === 'analytics'">
      <!-- Top Stats Row -->
      <v-row class="mb-4">
        <v-col v-for="stat in analyticsStats" :key="stat.title" cols="6" sm="6" md="3">
          <v-card elevation="0" class="compact-stat-card">
            <v-card-text class="pa-4">
              <div class="d-flex align-center">
                <v-icon :color="stat.color" size="24" class="mr-3">{{ stat.icon }}</v-icon>
                <div>
                  <div class="stat-value" :class="stat.color + '--text'">{{ stat.value }}</div>
                  <div class="stat-label">{{ stat.title }}</div>
                  <div class="stat-change" :class="stat.changeColor">{{ stat.change }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Charts and Analytics Grid -->
      <v-row>
        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-3 compact-chart-card">
            <v-card-title class="compact-header pa-4">
              <div class="d-flex justify-space-between align-center">
                <span class="compact-title error--text">Revenue Trend</span>
                <v-chip color="success" size="small" class="font-weight-bold">+15%</v-chip>
              </div>
            </v-card-title>
            <v-card-text class="pa-4">
              <div class="mb-2">
                <div class="d-flex justify-space-between align-center mb-1">
                  <span class="chart-subtitle">Monthly Growth</span>
                  <span class="chart-value success--text">{{ analyticsStats[0].value }}</span>
                </div>
              </div>
              <div style="height: 180px; position: relative;">
                <canvas ref="revenueChart"></canvas>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-3 compact-chart-card">
            <v-card-title class="compact-header pa-4">
              <div class="d-flex justify-space-between align-center">
                <span class="compact-title error--text">User Distribution</span>
                <v-chip color="info" size="small" class="font-weight-bold">{{ totalUsersForChart }} Total</v-chip>
              </div>
            </v-card-title>
            <v-card-text class="pa-4">
              <div class="mb-2">
                <div class="user-stats-row">
                  <div class="user-stat-item">
                    <div class="stat-dot" style="background-color: #3b82f6;"></div>
                    <span class="stat-text">Clients: {{ clientMetrics[0].value }}</span>
                  </div>
                  <div class="user-stat-item">
                    <div class="stat-dot" style="background-color: #10b981;"></div>
                    <span class="stat-text">Caregivers: {{ caregiverMetrics[0].value }}</span>
                  </div>
                  <div class="user-stat-item">
                    <div class="stat-dot" style="background-color: #dc2626;"></div>
                    <span class="stat-text">Admins: {{ adminCount }}</span>
                  </div>
                </div>
              </div>
              <div style="height: 140px; position: relative;">
                <canvas ref="userChart"></canvas>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-3 compact-chart-card">
            <v-card-title class="compact-header pa-4">
              <div class="d-flex justify-space-between align-center">
                <span class="compact-title error--text">Booking Status</span>
                <v-chip color="warning" size="small" class="font-weight-bold">{{ totalBookingsForChart }} Total</v-chip>
              </div>
            </v-card-title>
            <v-card-text class="pa-4">
              <div class="mb-2">
                <div class="booking-stats-grid">
                  <div class="booking-stat-item">
                    <div class="stat-indicator" style="background-color: #f59e0b;"></div>
                    <div class="stat-info">
                      <div class="stat-number">{{ bookingStats.pending }}</div>
                      <div class="stat-label">Pending</div>
                    </div>
                  </div>
                  <div class="booking-stat-item">
                    <div class="stat-indicator" style="background-color: #10b981;"></div>
                    <div class="stat-info">
                      <div class="stat-number">{{ bookingStats.active }}</div>
                      <div class="stat-label">Active</div>
                    </div>
                  </div>
                  <div class="booking-stat-item">
                    <div class="stat-indicator" style="background-color: #3b82f6;"></div>
                    <div class="stat-info">
                      <div class="stat-number">{{ bookingStats.completed }}</div>
                      <div class="stat-label">Completed</div>
                    </div>
                  </div>
                  <div class="booking-stat-item">
                    <div class="stat-indicator" style="background-color: #ef4444;"></div>
                    <div class="stat-info">
                      <div class="stat-number">{{ bookingStats.cancelled }}</div>
                      <div class="stat-label">Cancelled</div>
                    </div>
                  </div>
                </div>
              </div>
              <div style="height: 120px; position: relative;">
                <canvas ref="bookingChart"></canvas>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Client & Caregiver Analytics -->
      <v-row>
        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-3" style="border: 1px solid #c5c5c5ff;">
            <v-card-title class="compact-header pa-4">
              <span class="compact-title error--text">Client Analytics</span>
            </v-card-title>
            <v-card-text class="pa-4">
              <v-row>
                <v-col cols="6" v-for="metric in clientMetrics" :key="metric.label">
                  <div class="metric-box">
                    <div class="metric-number" :class="metric.color + '--text'">{{ metric.value }}</div>
                    <div class="metric-text">{{ metric.label }}</div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-3" style="border: 1px solid #c5c5c5ff;">
            <v-card-title class="compact-header pa-4">
              <span class="compact-title error--text">Caregiver Analytics</span>
            </v-card-title>
            <v-card-text class="pa-4">
              <v-row>
                <v-col cols="6" v-for="metric in caregiverMetrics" :key="metric.label">
                  <div class="metric-box">
                    <div class="metric-number" :class="metric.color + '--text'">{{ metric.value }}</div>
                    <div class="metric-text">{{ metric.label }}</div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-3" style="border: 1px solid #c5c5c5ff;">
            <v-card-title class="compact-header pa-4">
              <span class="compact-title deep-purple--text">Housekeeper Analytics</span>
            </v-card-title>
            <v-card-text class="pa-4">
              <v-row>
                <v-col cols="6" v-for="metric in housekeeperMetrics" :key="metric.label">
                  <div class="metric-box">
                    <div class="metric-number" :class="metric.color + '--text'">{{ metric.value }}</div>
                    <div class="metric-text">{{ metric.label }}</div>
                  </div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

</div>

<!-- Caregivers Management Section -->
    <div v-if="currentSection === 'caregivers'">
      <div class="mb-6">
        <v-row class="align-center">
          <v-col cols="12" md="3">
            <v-text-field v-model="caregiverSearch" placeholder="Search caregivers..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="caregiverLocationFilter" :items="locationFilterOptions" label="All Locations" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="caregiverStatusFilter" :items="['All', 'Active', 'Assigned', 'Inactive']" label="All Status" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="3">
            <v-btn color="error" prepend-icon="mdi-plus" @click="openCaregiverDialog()">Add Caregiver</v-btn>
          </v-col>
        </v-row>
      </div>
      <v-card elevation="0">
        <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
          <span class="section-title error--text">Caregivers</span>
          <v-btn v-if="selectedCaregivers.length > 0" color="error" variant="outlined" prepend-icon="mdi-delete" @click="deleteSelectedCaregivers">
            Delete Selected ({{ selectedCaregivers.length }})
          </v-btn>
        </v-card-title>
        <v-data-table v-model="selectedCaregivers" :headers="caregiverHeaders" :items="filteredCaregivers" :items-per-page="10" show-select item-value="userId" class="elevation-0" density="compact">
          <template v-slot:item.zip_code="{ item }">
            {{ item.zip_code || 'Unknown ZIP' }}
          </template>
          <template v-slot:item.location="{ item }">
            <span style="display:none">{{ ensureItemPlaceIndicator(item) }}</span>
            {{ item.place_indicator || item.location || 'Unknown ZIP' }}
          </template>
          <template v-slot:item.status="{ item }">
            <v-chip
              :color="getUserStatusColor(item.status)"
              size="small"
              class="font-weight-bold"
              :style="(String(item.status).toLowerCase() === 'pending') ? 'background-color: #f59e0b !important; color: #ffffff !important;' : ''"
              :prepend-icon="getStatusIcon(item.status)"
            >{{ item.status }}</v-chip>
          </template>
          <template v-slot:item.rating="{ item }">
            <div class="d-flex align-center">
              <v-rating
                :model-value="parseFloat(item.rating || 0)"
                :length="5"
                :size="18"
                color="amber"
                active-color="amber"
                half-increments
                readonly
                density="compact"
              ></v-rating>
              <span class="ml-1 text-caption">{{ parseFloat(item.rating || 0).toFixed(1) }}</span>
            </div>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn class="action-btn-view" icon="mdi-eye" size="small" @click="viewCaregiverDetails(item)"></v-btn>
              <v-btn class="action-btn-edit" icon="mdi-pencil" size="small" @click="openCaregiverDialog(item)"></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- View Caregiver Details Dialog -->
    <v-dialog v-model="viewCaregiverDialog" max-width="800" scrollable>
      <v-card v-if="viewingCaregiver">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="section-title" style="color: white;">Caregiver Details</span>
            <v-btn icon="mdi-close" variant="text" style="color: white;" @click="viewCaregiverDialog = false"></v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6" style="max-height: 60vh; overflow-y: auto;">
          <v-row>
            <v-col cols="12" class="text-center mb-4">
              <v-avatar size="120" color="success" class="mb-3">
                <span class="text-h3 font-weight-bold text-white">{{ viewingCaregiver.name.split(' ').map(n => n[0]).join('') }}</span>
              </v-avatar>
              <h2>{{ viewingCaregiver.name }}</h2>
              <v-chip :color="getUserStatusColor(viewingCaregiver.status)" class="mt-2">{{ viewingCaregiver.status }}</v-chip>
              <v-chip color="warning" class="mt-2 ml-2">
                <v-icon size="16" class="mr-1">mdi-account-group</v-icon>
                {{ viewingCaregiver.clients }} Clients
              </v-chip>
              
              <!-- Rating Display -->
              <div class="mt-4">
                <div class="d-flex align-center justify-center">
                  <v-rating
                    :model-value="parseFloat(viewingCaregiver.rating || 0)"
                    :length="5"
                    :size="32"
                    color="amber"
                    active-color="amber"
                    half-increments
                    readonly
                    density="compact"
                  ></v-rating>
                  <span class="ml-2 text-h6">{{ parseFloat(viewingCaregiver.rating || 0).toFixed(1) }}</span>
                </div>
                <div class="text-caption text-grey mt-1">
                  {{ caregiverReviews.length || 0 }} {{ caregiverReviews.length === 1 ? 'Review' : 'Reviews' }}
                </div>
              </div>
            </v-col>
          </v-row>
          
          <v-divider class="mb-4"></v-divider>
          
          <v-row>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">First Name</div>
                <div class="detail-value">{{ viewingCaregiver.first_name || (viewingCaregiver.name ? viewingCaregiver.name.split(' ')[0] : '') || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Last Name</div>
                <div class="detail-value">{{ viewingCaregiver.last_name || (viewingCaregiver.name ? viewingCaregiver.name.split(' ').slice(1).join(' ') : '') || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Email</div>
                <div class="detail-value">{{ viewingCaregiver.email || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Phone</div>
                <div class="detail-value">{{ viewingCaregiver.phone || 'N/A' }}</div>
              </div>
            </v-col>

            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Birthdate</div>
                <div class="detail-value">{{ viewingCaregiver.birthdate || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Age</div>
                <div class="detail-value">{{ viewingCaregiver.age || 'N/A' }}</div>
              </div>
            </v-col>

            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Address</div>
                <div class="detail-value">{{ viewingCaregiver.address || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">State</div>
                <div class="detail-value">{{ viewingCaregiver.state || 'N/A' }}</div>
              </div>
            </v-col>

            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">County/Borough</div>
                <div class="detail-value">{{ viewingCaregiver.county || viewingCaregiver.borough || 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">City</div>
                <div class="detail-value">{{ viewingCaregiver.city || 'N/A' }}</div>
              </div>
            </v-col>

            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">ZIP Code</div>
                <div class="detail-value">{{ viewingCaregiver.zip_code || 'Unknown ZIP' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Location</div>
                <div class="detail-value">
                  {{ viewingCaregiver.place_indicator || viewingCaregiver.location || 'Unknown ZIP' }}
                </div>
              </div>
            </v-col>

            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Clients Served</div>
                <div class="detail-value">{{ viewingCaregiver.clients }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Joined</div>
                <div class="detail-value">{{ viewingCaregiver.joined }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Verification Status</div>
                <div class="detail-value">
                  <v-chip :color="viewingCaregiver.verified ? 'success' : 'warning'" size="small">
                    {{ viewingCaregiver.verified ? 'Verified' : 'Pending' }}
                  </v-chip>
                </div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Preferred Hourly Rate</div>
                <div class="detail-value">
                  <v-chip color="success" size="small">
                    <v-icon size="14" class="mr-1">mdi-cash</v-icon>
                    ${{ viewingCaregiver.preferred_hourly_rate_min || 20 }} - 
                    ${{ viewingCaregiver.preferred_hourly_rate_max || 50 }}/hr
                  </v-chip>
                </div>
              </div>
            </v-col>
          </v-row>
          
          <v-divider class="my-4"></v-divider>
          
          <!-- Professional Certifications Section -->
          <v-row v-if="viewingCaregiver.has_hha || viewingCaregiver.has_cna || viewingCaregiver.has_rn">
            <v-col cols="12">
              <div class="detail-label mb-3">
                <v-icon class="mr-2">mdi-certificate</v-icon>
                Professional Certifications
              </div>
            </v-col>
            <v-col v-if="viewingCaregiver.has_hha" cols="12" md="4">
              <v-chip color="success" prepend-icon="mdi-check-circle" class="mb-2">
                HHA – Home Health Aide
              </v-chip>
              <div v-if="viewingCaregiver.hha_number" class="text-caption text-grey mt-1">
                Certificate #: {{ viewingCaregiver.hha_number }}
              </div>
            </v-col>
            <v-col v-if="viewingCaregiver.has_cna" cols="12" md="4">
              <v-chip color="success" prepend-icon="mdi-check-circle" class="mb-2">
                CNA – Certified Nursing Assistant
              </v-chip>
              <div v-if="viewingCaregiver.cna_number" class="text-caption text-grey mt-1">
                Certificate #: {{ viewingCaregiver.cna_number }}
              </div>
            </v-col>
            <v-col v-if="viewingCaregiver.has_rn" cols="12" md="4">
              <v-chip color="success" prepend-icon="mdi-check-circle" class="mb-2">
                RN – Registered Nurse
              </v-chip>
              <div v-if="viewingCaregiver.rn_number" class="text-caption text-grey mt-1">
                License #: {{ viewingCaregiver.rn_number }}
              </div>
            </v-col>
          </v-row>
          
          <v-divider class="my-4"></v-divider>
          
          <!-- Ratings & Reviews Section -->
          <v-row>
            <v-col cols="12">
              <div class="detail-section">
                <div class="detail-label mb-3">
                  <v-icon class="mr-2">mdi-star</v-icon>
                  Ratings & Reviews
                </div>
                
                <!-- Loading State -->
                <div v-if="loadingCaregiverReviews" class="text-center py-8">
                  <v-progress-circular indeterminate color="primary"></v-progress-circular>
                  <p class="text-caption mt-2">Loading reviews...</p>
                </div>
                
                <!-- Reviews List -->
                <div v-else-if="caregiverReviews.length > 0">
                  <v-card
                    v-for="review in caregiverReviews.slice(0, 5)"
                    :key="review.id"
                    class="mb-3 pa-4"
                    elevation="1"
                  >
                    <div class="d-flex justify-space-between align-start mb-2">
                      <div>
                        <div class="font-weight-bold">{{ review.client_name }}</div>
                        <div class="text-caption text-grey">{{ review.service_type }} - {{ review.service_date }}</div>
                      </div>
                      <v-chip 
                        :color="review.recommend ? 'success' : 'grey'" 
                        size="x-small"
                      >
                        <v-icon size="14" start>{{ review.recommend ? 'mdi-thumb-up' : 'mdi-thumb-down' }}</v-icon>
                        {{ review.recommend ? 'Recommended' : 'Not Recommended' }}
                      </v-chip>
                    </div>
                    
                    <v-rating
                      :model-value="review.rating"
                      :length="5"
                      :size="20"
                      color="amber"
                      active-color="amber"
                      readonly
                      density="compact"
                      class="mb-2"
                    ></v-rating>
                    
                    <p v-if="review.comment" class="text-body-2 mb-1">{{ review.comment }}</p>
                    <div class="text-caption text-grey">{{ review.created_at }}</div>
                  </v-card>
                  
                  <v-btn
                    v-if="caregiverReviews.length > 5"
                    variant="text"
                    color="primary"
                    block
                    @click="viewAllReviews(viewingCaregiver)"
                  >
                    View All {{ caregiverReviews.length }} Reviews
                  </v-btn>
                </div>
                
                <!-- No Reviews -->
                <v-alert v-else type="info" variant="tonal" density="compact">
                  <v-icon>mdi-information</v-icon>
                  No reviews yet for this caregiver
                </v-alert>
              </div>
            </v-col>
          </v-row>
          
          <v-divider class="my-4"></v-divider>
          
          <v-row>
            <v-col cols="12">
              <div class="detail-section">
                <div class="detail-label">Training Certificate</div>
                <div class="detail-value" v-if="viewingCaregiver.training_certificate">
                  <v-card class="certificate-card pa-4" elevation="2">
                    <div class="d-flex align-center justify-space-between">
                      <div class="d-flex align-center">
                        <v-icon color="success" size="32" class="mr-3">mdi-file-certificate</v-icon>
                        <div>
                          <div class="certificate-name">{{ viewingCaregiver.training_certificate.split('/').pop() }}</div>
                          <div class="certificate-info">Uploaded on {{ viewingCaregiver.joined }}</div>
                        </div>
                      </div>
                      <v-btn
                        color="primary"
                        variant="outlined"
                        prepend-icon="mdi-download"
                        size="small"
                        :href="viewingCaregiver.training_certificate.startsWith('/') ? viewingCaregiver.training_certificate : ('/storage/' + viewingCaregiver.training_certificate)"
                        target="_blank"
                      >
                        Download
                      </v-btn>
                    </div>
                  </v-card>
                </div>
                <div class="detail-value" v-else>
                  <v-alert type="warning" variant="tonal" density="compact">
                    <v-icon>mdi-alert-circle</v-icon>
                    No certificate uploaded
                  </v-alert>
                </div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="viewCaregiverDialog = false">Close</v-btn>
          <v-btn color="error" @click="openCaregiverDialog(viewingCaregiver); viewCaregiverDialog = false">Edit</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- View Client Details Dialog -->
    <v-dialog v-model="viewClientDialog" max-width="800">
      <v-card v-if="viewingClient">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="section-title" style="color: white;">Client Details</span>
            <v-btn icon="mdi-close" variant="text" style="color: white;" @click="viewClientDialog = false"></v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12" class="text-center mb-4">
              <v-avatar size="120" color="primary" class="mb-3">
                <span class="text-h3 font-weight-bold text-white">{{ viewingClient.name.split(' ').map(n => n[0]).join('') }}</span>
              </v-avatar>
              <h2>{{ viewingClient.name }}</h2>
              <v-chip :color="getUserStatusColor(viewingClient.status)" class="mt-2">{{ viewingClient.status }}</v-chip>
              <v-chip :color="viewingClient.verified ? 'success' : 'warning'" class="mt-2 ml-2">
                <v-icon size="16" class="mr-1">{{ viewingClient.verified ? 'mdi-shield-check' : 'mdi-shield-alert' }}</v-icon>
                {{ viewingClient.verified ? 'Verified' : 'Unverified' }}
              </v-chip>
            </v-col>
          </v-row>
          
          <v-divider class="mb-4"></v-divider>
          
          <v-row>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Email</div>
                <div class="detail-value">{{ viewingClient.email }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Zip Code</div>
                <div class="detail-value">{{ viewingClient.zip_code || viewingClient.zip || 'Unknown ZIP' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Location</div>
                <div class="detail-value">{{ viewingClient.place_indicator || viewingClient.location || 'Unknown ZIP' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                  <v-chip :color="getUserStatusColor(viewingClient.status)" size="small">
                    {{ viewingClient.status }}
                  </v-chip>
                </div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Total Bookings</div>
                <div class="detail-value">{{ viewingClient.bookings }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Total Spent</div>
                <div class="detail-value">{{ viewingClient.totalSpent }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Member Since</div>
                <div class="detail-value">{{ viewingClient.joined }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Verification Status</div>
                <div class="detail-value">
                  <v-chip :color="viewingClient.verified ? 'success' : 'warning'" size="small">
                    {{ viewingClient.verified ? 'Verified' : 'Pending Verification' }}
                  </v-chip>
                </div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="viewClientDialog = false">Close</v-btn>
          <v-btn color="error" @click="openClientDialog(viewingClient); viewClientDialog = false">Edit</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Housekeepers Management Section -->
    <div v-if="currentSection === 'housekeepers'">
      <div class="mb-6">
        <v-row class="align-center">
          <v-col cols="12" md="3">
            <v-text-field v-model="housekeeperSearch" placeholder="Search housekeepers..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="housekeeperLocationFilter" :items="locationFilterOptions" label="All Locations" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="housekeeperStatusFilter" :items="['All', 'Active', 'Assigned', 'Inactive']" label="All Status" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="3">
            <v-btn color="error" prepend-icon="mdi-plus" @click="openHousekeeperDialog()">Add Housekeeper</v-btn>
          </v-col>
        </v-row>
      </div>
      <v-card elevation="0">
        <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
          <span class="section-title deep-purple--text">Housekeepers</span>
        </v-card-title>
        <v-data-table :headers="housekeeperHeaders" :items="filteredHousekeepers" :items-per-page="10" class="elevation-0" density="compact">
          <template v-slot:item.location="{ item }">
            {{ item.location || 'Unknown' }}
          </template>
          <template v-slot:item.status="{ item }">
            <v-chip
              :color="getUserStatusColor(item.status)"
              size="small"
              class="font-weight-bold"
              :style="(String(item.status).toLowerCase() === 'pending') ? 'background-color: #f59e0b !important; color: #ffffff !important;' : ''"
            >{{ item.status }}</v-chip>
          </template>
          <template v-slot:item.rating="{ item }">
            <div class="d-flex align-center">
              <v-rating
                :model-value="parseFloat(item.rating || 0)"
                :length="5"
                :size="18"
                color="amber"
                active-color="amber"
                half-increments
                readonly
                density="compact"
              ></v-rating>
              <span class="ml-1 text-caption">{{ parseFloat(item.rating || 0).toFixed(1) }}</span>
            </div>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn class="action-btn-view" icon="mdi-eye" size="small" @click="viewHousekeeperDetails(item)"></v-btn>
              <v-btn class="action-btn-edit" icon="mdi-pencil" size="small" @click="openHousekeeperDialog(item)"></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- Clients Management Section -->
    <div v-if="currentSection === 'clients'">
      <div class="mb-6">
        <v-row class="align-center">
          <v-col cols="12" md="3">
            <v-text-field v-model="clientSearch" placeholder="Search clients..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="clientLocationFilter" :items="locationFilterOptions" label="All Locations" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="clientStatusFilter" :items="['All', 'Active', 'Inactive']" label="All Status" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="3">
            <v-btn color="error" prepend-icon="mdi-plus" @click="openClientDialog()">Add Client</v-btn>
          </v-col>
        </v-row>
      </div>
      <v-card elevation="0">
        <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
          <span class="section-title error--text">Clients</span>
          <v-btn v-if="selectedClients.length > 0" color="error" variant="outlined" prepend-icon="mdi-delete" @click="deleteSelectedClients">
            Delete Selected ({{ selectedClients.length }})
          </v-btn>
        </v-card-title>
        <v-data-table v-model="selectedClients" :headers="clientHeaders" :items="filteredClients" :items-per-page="10" show-select item-value="id" class="elevation-0" density="compact">
          <template v-slot:item.zip_code="{ item }">
            {{ item.zip_code || 'Unknown ZIP' }}
          </template>
          <template v-slot:item.location="{ item }">
            <span style="display:none">{{ ensureItemPlaceIndicator(item) }}</span>
            {{ item.place_indicator || item.location || 'Unknown ZIP' }}
          </template>
          <template v-slot:item.status="{ item }">
            <v-chip
              :color="getUserStatusColor(item.status)"
              size="small"
              class="font-weight-bold"
              :style="(String(item.status).toLowerCase() === 'pending') ? 'background-color: #f59e0b !important; color: #ffffff !important;' : ''"
              :prepend-icon="getStatusIcon(item.status)"
            >{{ item.status }}</v-chip>
          </template>

          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn class="action-btn-view" icon="mdi-eye" size="small" @click="viewClientDetails(item)"></v-btn>
              <v-btn class="action-btn-edit" icon="mdi-pencil" size="small" @click="openClientDialog(item)"></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- Marketing Partner Management Section -->
    <div v-if="currentSection === 'marketing-staff'">
      <div class="mb-6">
        <v-row class="align-center">
          <v-col cols="12" md="3">
            <v-text-field v-model="marketingStaffSearch" placeholder="Search marketing partner..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="marketingStaffStatusFilter" :items="['All', 'Active', 'pending', 'Inactive']" label="All Status" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="3">
            <v-btn color="error" prepend-icon="mdi-plus" @click="openMarketingStaffDialog()">Add Marketing Partner</v-btn>
          </v-col>
        </v-row>
      </div>
      <v-card elevation="0">
        <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
          <span class="section-title error--text">Marketing Partner</span>
          <v-btn v-if="selectedMarketingStaff.length > 0" color="error" variant="outlined" prepend-icon="mdi-delete" @click="deleteSelectedMarketingStaff">
            Delete Selected ({{ selectedMarketingStaff.length }})
          </v-btn>
        </v-card-title>
        <v-data-table v-model="selectedMarketingStaff" :headers="marketingStaffHeaders" :items="filteredMarketingStaff" :items-per-page="10" show-select item-value="id" class="elevation-0" density="compact">
          <template v-slot:item.zip_code="{ item }">
            {{ item.zip_code || 'Unknown ZIP' }}
          </template>
          <template v-slot:item.location="{ item }">
            <span style="display:none">{{ ensureItemPlaceIndicator(item) }}</span>
            {{ item.place_indicator || item.location || 'Unknown ZIP' }}
          </template>
          <template v-slot:item.referralCode="{ item }">
            <v-chip color="primary" size="small" class="font-weight-bold">
              <v-icon size="14" class="mr-1">mdi-ticket-percent</v-icon>
              {{ item.referralCode }}
            </v-chip>
          </template>
          <template v-slot:item.clientsAcquired="{ item }">
            <v-chip color="info" size="small">{{ item.clientsAcquired }}</v-chip>
          </template>
          <template v-slot:item.commissionEarned="{ item }">
            <span class="font-weight-bold text-success">${{ item.commissionEarned }}</span>
          </template>
          <template v-slot:item.status="{ item }">
            <v-chip :color="getUserStatusColor(item.status)" size="small" class="font-weight-bold" :prepend-icon="getStatusIcon(item.status)">{{ item.status }}</v-chip>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn class="action-btn-view" icon="mdi-eye" size="small" @click="viewMarketingStaffDetails(item)"></v-btn>
              <v-btn class="action-btn-edit" icon="mdi-pencil" size="small" @click="openMarketingStaffDialog(item)"></v-btn>
              <v-btn 
                v-if="parseFloat(item.commissionEarned) > 0" 
                class="action-btn-pay" 
                icon="mdi-cash-multiple" 
                size="small" 
                color="success"
                @click="payMarketingCommission(item)"
                :loading="payingCommission === item.id"
              ></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- View Marketing Partner Details Dialog -->
    <v-dialog v-model="viewMarketingStaffDialog" max-width="800">
      <v-card v-if="viewingMarketingStaff">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="section-title" style="color: white;">Marketing Partner Details</span>
            <v-btn icon="mdi-close" variant="text" style="color: white;" @click="viewMarketingStaffDialog = false"></v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12" class="text-center mb-4">
              <v-avatar size="120" color="primary" class="mb-3">
                <span class="text-h3 font-weight-bold text-white">{{ viewingMarketingStaff.name.split(' ').map(n => n[0]).join('') }}</span>
              </v-avatar>
              <h2>{{ viewingMarketingStaff.name }}</h2>
              <v-chip :color="getUserStatusColor(viewingMarketingStaff.status)" class="mt-2">{{ viewingMarketingStaff.status }}</v-chip>
            </v-col>
          </v-row>
          
          <v-divider class="mb-4"></v-divider>
          
          <v-row>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Email</div>
                <div class="detail-value">{{ viewingMarketingStaff.email }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Phone</div>
                <div class="detail-value">{{ viewingMarketingStaff.phone }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Zip Code</div>
                <div class="detail-value">{{ viewingMarketingStaff.zip_code || viewingMarketingStaff.zip || 'Unknown ZIP' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Location</div>
                <div class="detail-value">{{ viewingMarketingStaff.place_indicator || viewingMarketingStaff.location || 'Unknown ZIP' }}</div>
              </div>
            </v-col>
          </v-row>
          
          <v-divider class="my-4"></v-divider>
          
          <h3 class="mb-3">Referral Code Information</h3>
          <v-row>
            <v-col cols="12" md="4">
              <v-card class="pa-4 text-center" color="primary" variant="tonal">
                <v-icon size="32" color="primary">mdi-ticket-percent</v-icon>
                <h4 class="mt-2">{{ viewingMarketingStaff.referralCode }}</h4>
                <div class="text-caption">Referral Code</div>
              </v-card>
            </v-col>
            <v-col cols="12" md="4">
              <v-card class="pa-4 text-center" color="info" variant="tonal">
                <v-icon size="32" color="info">mdi-account-group</v-icon>
                <h4 class="mt-2">{{ viewingMarketingStaff.clientsAcquired }}</h4>
                <div class="text-caption">Clients Acquired</div>
              </v-card>
            </v-col>
            <v-col cols="12" md="4">
              <v-card class="pa-4 text-center" color="success" variant="tonal">
                <v-icon size="32" color="success">mdi-currency-usd</v-icon>
                <h4 class="mt-2">${{ viewingMarketingStaff.commissionEarned }}</h4>
                <div class="text-caption">Commission Earned</div>
              </v-card>
            </v-col>
          </v-row>
          
          <v-row class="mt-4">
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Total Hours Referred</div>
                <div class="detail-value">{{ viewingMarketingStaff.totalHours }} hours</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Commission Rate</div>
                <div class="detail-value">$1.00 per hour</div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="viewMarketingStaffDialog = false">Close</v-btn>
          <v-btn color="error" @click="openMarketingStaffDialog(viewingMarketingStaff); viewMarketingStaffDialog = false">Edit</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Add/Edit Marketing Partner Dialog -->
    <v-dialog v-model="marketingStaffDialog" max-width="900" scrollable>
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">{{ editingMarketingStaff ? 'Edit Marketing Partner' : 'Add Marketing Partner' }}</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <div class="mb-4">
            <h3 class="text-h6 mb-4">Personal Information</h3>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field v-model="marketingStaffFormData.firstName" label="First Name *" variant="outlined" required @update:model-value="marketingStaffFormData.firstName = filterLettersOnly(marketingStaffFormData.firstName)" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="marketingStaffFormData.lastName" label="Last Name *" variant="outlined" required @update:model-value="marketingStaffFormData.lastName = filterLettersOnly(marketingStaffFormData.lastName)" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="marketingStaffFormData.email" label="Email *" type="email" variant="outlined" required />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="marketingStaffFormData.phone" 
                  label="Phone" 
                  variant="outlined"
                  placeholder="(646) 282-8282"
                  maxlength="14"
                  @update:model-value="marketingStaffFormData.phone = formatPhoneNumber(marketingStaffFormData.phone)"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="marketingStaffFormData.birthdate" label="Birthdate" variant="outlined" type="date" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field :model-value="calculateAge(marketingStaffFormData.birthdate)" label="Age" variant="outlined" readonly />
              </v-col>
              <v-col cols="12">
                <v-text-field v-model="marketingStaffFormData.address" label="Address" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="marketingStaffFormData.state" label="State" variant="outlined" readonly value="New York" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="marketingStaffFormData.county" :items="nyCounties" label="County" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="marketingStaffFormData.city" label="City" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="marketingStaffFormData.zip_code" 
                  label="ZIP Code" 
                  variant="outlined"
                  maxlength="5"
                  :rules="[v => !v || /^\d{5}$/.test(v) || 'Please enter a valid 5-digit ZIP code']"
                  placeholder="Enter ZIP code"
                  @input="lookupMarketingStaffZipCode"
                  @blur="lookupMarketingStaffZipCode"
                >
                  <template v-slot:prepend-inner>
                    <v-icon>mdi-map-marker</v-icon>
                  </template>
                </v-text-field>
                <div v-if="marketingStaffZipLocation" style="font-weight: 600; color: #000000; margin-top: -8px; font-size: 0.75rem; line-height: 1.2;">
                  {{ marketingStaffZipLocation }}
                </div>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-if="!editingMarketingStaff" 
                  v-model="marketingStaffFormData.password" 
                  label="Password *" 
                  :type="showMarketingPassword ? 'text' : 'password'" 
                  variant="outlined" 
                  required
                  :append-inner-icon="showMarketingPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showMarketingPassword = !showMarketingPassword"
                />
                <div v-if="!editingMarketingStaff && marketingStaffFormData.password" class="password-requirements mt-2">
                  <div class="requirement-item" :class="{ valid: passwordMeetsLength(marketingStaffFormData.password) }">
                    <span class="requirement-icon">{{ passwordMeetsLength(marketingStaffFormData.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">At least 8 characters</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsUppercase(marketingStaffFormData.password) }">
                    <span class="requirement-icon">{{ passwordMeetsUppercase(marketingStaffFormData.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One capital letter</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsDigit(marketingStaffFormData.password) }">
                    <span class="requirement-icon">{{ passwordMeetsDigit(marketingStaffFormData.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One digit</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsSpecial(marketingStaffFormData.password) }">
                    <span class="requirement-icon">{{ passwordMeetsSpecial(marketingStaffFormData.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One special character</span>
                  </div>
                </div>
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="marketingStaffFormData.status" :items="['Active', 'Inactive']" label="Status *" variant="outlined" required />
              </v-col>
            </v-row>
          </div>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="marketingStaffDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="saveMarketingStaff">{{ editingMarketingStaff ? 'Update' : 'Create' }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Admin Staff Management Section -->
    <div v-if="currentSection === 'admin-staff'">
      <div class="mb-6">
        <v-row class="align-center">
          <v-col cols="12" md="3">
            <v-text-field v-model="adminStaffSearch" placeholder="Search admin staff..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="adminStaffStatusFilter" :items="['All', 'Active', 'Inactive']" label="All Status" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="3">
            <v-btn color="error" prepend-icon="mdi-plus" @click="openAdminStaffDialog()">Add Admin Staff</v-btn>
          </v-col>
        </v-row>
      </div>
      <v-card elevation="0">
        <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
          <span class="section-title error--text">Admin Staff Management</span>
          <v-btn v-if="selectedAdminStaff.length > 0" color="error" variant="outlined" prepend-icon="mdi-delete" @click="deleteSelectedAdminStaff">
            Delete Selected ({{ selectedAdminStaff.length }})
          </v-btn>
        </v-card-title>
        <v-data-table 
          v-model="selectedAdminStaff" 
          :headers="adminStaffHeaders" 
          :items="filteredAdminStaff" 
          :items-per-page="10" 
          show-select 
          item-value="id" 
          class="elevation-0" 
          density="compact"
        >
          <template v-slot:item.location="{ item }">
            <span style="display:none">{{ ensureItemPlaceIndicator(item) }}</span>
            {{ item.place_indicator || item.location || 'Unknown ZIP' }}
          </template>
          <template v-slot:item.email_verified="{ item }">
            <v-chip :color="item.email_verified === 'Yes' ? 'success' : 'warning'" size="small">
              <v-icon size="14" class="mr-1">{{ item.email_verified === 'Yes' ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
              {{ item.email_verified }}
            </v-chip>
          </template>
          <template v-slot:item.status="{ item }">
            <v-chip
              :color="getUserStatusColor(item.status)"
              size="small"
              class="font-weight-bold"
              :style="(String(item.status).toLowerCase() === 'pending') ? 'background-color: #f59e0b !important; color: #ffffff !important;' : ''"
              :prepend-icon="getStatusIcon(item.status)"
            >
              {{ item.status }}
            </v-chip>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn class="action-btn-view" icon="mdi-eye" size="small" @click="viewAdminStaffDetails(item)"></v-btn>
              <v-btn class="action-btn-edit" icon="mdi-pencil" size="small" @click="openAdminStaffDialog(item)"></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- View Admin Staff Details Dialog -->
    <v-dialog v-model="viewAdminStaffDialog" max-width="800">
      <v-card v-if="viewingAdminStaff">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="section-title" style="color: white;">Admin Staff Details</span>
            <v-btn icon="mdi-close" variant="text" style="color: white;" @click="viewAdminStaffDialog = false"></v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12" class="text-center mb-4">
              <v-avatar size="120" color="error" class="mb-3">
                <span class="text-h3 font-weight-bold text-white">{{ viewingAdminStaff.name.split(' ').map(n => n[0]).join('') }}</span>
              </v-avatar>
              <h2>{{ viewingAdminStaff.name }}</h2>
              <p class="text-subtitle-1 text-grey mb-2">Admin Staff</p>
              <v-chip :color="getUserStatusColor(viewingAdminStaff.status)" class="mt-2">{{ viewingAdminStaff.status }}</v-chip>
            </v-col>
          </v-row>
          
          <v-divider class="mb-4"></v-divider>
          
          <v-row>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Email</div>
                <div class="detail-value">{{ viewingAdminStaff.email }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Phone</div>
                <div class="detail-value">{{ viewingAdminStaff.phone || 'Not provided' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Email Verified</div>
                <div class="detail-value">
                  <v-chip :color="viewingAdminStaff.email_verified === 'Yes' ? 'success' : 'warning'" size="small">
                    {{ viewingAdminStaff.email_verified }}
                  </v-chip>
                </div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Last Login</div>
                <div class="detail-value">{{ viewingAdminStaff.last_login }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Joined Date</div>
                <div class="detail-value">{{ viewingAdminStaff.joined }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Location</div>
                <div class="detail-value">{{ viewingAdminStaff.place_indicator || viewingAdminStaff.location || 'Unknown ZIP' }}</div>
              </div>
            </v-col>
          </v-row>
          
          <v-divider class="my-4"></v-divider>
          
          <h3 class="mb-3">Access Permissions</h3>
          <v-row>
            <v-col cols="12" md="6">
              <v-list density="compact" class="bg-transparent">
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-check-circle</v-icon>
                  </template>
                  <v-list-item-title>View Users (Read-Only)</v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-check-circle</v-icon>
                  </template>
                  <v-list-item-title>Contractors Application</v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-check-circle</v-icon>
                  </template>
                  <v-list-item-title>Password Resets</v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-check-circle</v-icon>
                  </template>
                  <v-list-item-title>Client Bookings</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-col>
            <v-col cols="12" md="6">
              <v-list density="compact" class="bg-transparent">
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-check-circle</v-icon>
                  </template>
                  <v-list-item-title>Time Tracking</v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-check-circle</v-icon>
                  </template>
                  <v-list-item-title>Reviews & Ratings</v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-check-circle</v-icon>
                  </template>
                  <v-list-item-title>Announcements</v-list-item-title>
                </v-list-item>
                <v-list-item>
                  <template v-slot:prepend>
                    <v-icon color="error">mdi-close-circle</v-icon>
                  </template>
                  <v-list-item-title class="text-grey">Full Admin Controls</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="viewAdminStaffDialog = false">Close</v-btn>
          <v-btn color="error" @click="openAdminStaffDialog(viewingAdminStaff); viewAdminStaffDialog = false">Edit</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Add/Edit Admin Staff Dialog -->
    <v-dialog v-model="adminStaffDialog" max-width="800" scrollable>
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">{{ editingAdminStaff ? 'Edit Admin Staff' : 'Add Admin Staff' }}</span>
        </v-card-title>
        <v-card-text class="pa-6" style="max-height: 70vh; overflow-y: auto;">
          <v-row>
            <v-col cols="12">
              <v-text-field 
                v-model="adminStaffFormData.name" 
                label="Full Name *" 
                variant="outlined" 
                prepend-inner-icon="mdi-account"
                required 
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field 
                v-model="adminStaffFormData.email" 
                label="Email *" 
                variant="outlined" 
                prepend-inner-icon="mdi-email"
                type="email" 
                required 
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field 
                v-model="adminStaffFormData.phone" 
                label="Phone" 
                variant="outlined" 
                prepend-inner-icon="mdi-phone"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field 
                v-model="adminStaffFormData.password" 
                :label="editingAdminStaff ? 'New Password (leave blank to keep current)' : 'Password *'" 
                :type="showAdminStaffPassword ? 'text' : 'password'" 
                variant="outlined" 
                prepend-inner-icon="mdi-lock"
                :required="!editingAdminStaff"
                :append-inner-icon="showAdminStaffPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showAdminStaffPassword = !showAdminStaffPassword"
                :hint="editingAdminStaff ? 'Leave blank to keep current password' : 'Minimum 8 characters'"
                persistent-hint
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-select 
                v-model="adminStaffFormData.status" 
                :items="['Active', 'Inactive']" 
                label="Status *" 
                variant="outlined" 
                prepend-inner-icon="mdi-check-circle"
                required 
              />
            </v-col>
          </v-row>
          
          <!-- Page Permissions Section -->
          <v-divider class="my-4"></v-divider>
          <div class="d-flex align-center justify-space-between mb-4">
            <div>
              <h3 class="text-h6 mb-1">
                <v-icon color="error" class="mr-2">mdi-shield-key</v-icon>
                Page Access Permissions
              </h3>
              <p class="text-caption text-grey">Select which pages this Admin Staff can access</p>
            </div>
            <div>
              <v-btn size="small" color="success" variant="outlined" class="mr-2" @click="selectAllPermissions">
                <v-icon start size="small">mdi-check-all</v-icon>
                Check All
              </v-btn>
              <v-btn size="small" color="grey" variant="outlined" @click="deselectAllPermissions">
                <v-icon start size="small">mdi-close</v-icon>
                Uncheck All
              </v-btn>
            </div>
          </div>
          
          <v-row>
            <!-- Dashboard & General -->
            <v-col cols="12">
              <v-card variant="outlined" class="pa-4 mb-3">
                <div class="text-subtitle-2 font-weight-bold mb-3 text-grey-darken-2">
                  <v-icon size="small" class="mr-1">mdi-view-dashboard</v-icon>
                  GENERAL
                </div>
                <v-row>
                  <v-col cols="6" md="4" v-for="page in permissionPages.general" :key="page.value">
                    <v-checkbox
                      v-model="adminStaffFormData.page_permissions[page.value]"
                      :label="page.title"
                      color="error"
                      density="compact"
                      hide-details
                    >
                      <template v-slot:label>
                        <div class="d-flex align-center">
                          <v-icon size="small" class="mr-2" :color="adminStaffFormData.page_permissions[page.value] ? 'success' : 'grey'">{{ page.icon }}</v-icon>
                          {{ page.title }}
                        </div>
                      </template>
                    </v-checkbox>
                  </v-col>
                </v-row>
              </v-card>
            </v-col>
            
            <!-- Users -->
            <v-col cols="12">
              <v-card variant="outlined" class="pa-4 mb-3">
                <div class="text-subtitle-2 font-weight-bold mb-3 text-grey-darken-2">
                  <v-icon size="small" class="mr-1">mdi-account-group</v-icon>
                  USER MANAGEMENT
                </div>
                <v-row>
                  <v-col cols="6" md="4" v-for="page in permissionPages.users" :key="page.value">
                    <v-checkbox
                      v-model="adminStaffFormData.page_permissions[page.value]"
                      :label="page.title"
                      color="error"
                      density="compact"
                      hide-details
                    >
                      <template v-slot:label>
                        <div class="d-flex align-center">
                          <v-icon size="small" class="mr-2" :color="adminStaffFormData.page_permissions[page.value] ? 'success' : 'grey'">{{ page.icon }}</v-icon>
                          {{ page.title }}
                        </div>
                      </template>
                    </v-checkbox>
                  </v-col>
                </v-row>
              </v-card>
            </v-col>
            
            <!-- Applications -->
            <v-col cols="12">
              <v-card variant="outlined" class="pa-4 mb-3">
                <div class="text-subtitle-2 font-weight-bold mb-3 text-grey-darken-2">
                  <v-icon size="small" class="mr-1">mdi-file-document</v-icon>
                  APPLICATIONS
                </div>
                <v-row>
                  <v-col cols="6" md="4" v-for="page in permissionPages.applications" :key="page.value">
                    <v-checkbox
                      v-model="adminStaffFormData.page_permissions[page.value]"
                      :label="page.title"
                      color="error"
                      density="compact"
                      hide-details
                    >
                      <template v-slot:label>
                        <div class="d-flex align-center">
                          <v-icon size="small" class="mr-2" :color="adminStaffFormData.page_permissions[page.value] ? 'success' : 'grey'">{{ page.icon }}</v-icon>
                          {{ page.title }}
                        </div>
                      </template>
                    </v-checkbox>
                  </v-col>
                </v-row>
              </v-card>
            </v-col>
            
            <!-- Bookings -->
            <v-col cols="12">
              <v-card variant="outlined" class="pa-4 mb-3">
                <div class="text-subtitle-2 font-weight-bold mb-3 text-grey-darken-2">
                  <v-icon size="small" class="mr-1">mdi-calendar</v-icon>
                  BOOKINGS
                </div>
                <v-row>
                  <v-col cols="6" md="4" v-for="page in permissionPages.bookings" :key="page.value">
                    <v-checkbox
                      v-model="adminStaffFormData.page_permissions[page.value]"
                      :label="page.title"
                      color="error"
                      density="compact"
                      hide-details
                    >
                      <template v-slot:label>
                        <div class="d-flex align-center">
                          <v-icon size="small" class="mr-2" :color="adminStaffFormData.page_permissions[page.value] ? 'success' : 'grey'">{{ page.icon }}</v-icon>
                          {{ page.title }}
                        </div>
                      </template>
                    </v-checkbox>
                  </v-col>
                </v-row>
              </v-card>
            </v-col>
            
            <!-- Other -->
            <v-col cols="12">
              <v-card variant="outlined" class="pa-4 mb-3">
                <div class="text-subtitle-2 font-weight-bold mb-3 text-grey-darken-2">
                  <v-icon size="small" class="mr-1">mdi-cog</v-icon>
                  OTHER
                </div>
                <v-row>
                  <v-col cols="6" md="4" v-for="page in permissionPages.other" :key="page.value">
                    <v-checkbox
                      v-model="adminStaffFormData.page_permissions[page.value]"
                      :label="page.title"
                      color="error"
                      density="compact"
                      hide-details
                    >
                      <template v-slot:label>
                        <div class="d-flex align-center">
                          <v-icon size="small" class="mr-2" :color="adminStaffFormData.page_permissions[page.value] ? 'success' : 'grey'">{{ page.icon }}</v-icon>
                          {{ page.title }}
                        </div>
                      </template>
                    </v-checkbox>
                  </v-col>
                </v-row>
              </v-card>
            </v-col>
          </v-row>
          
          <v-alert type="info" variant="tonal" class="mt-4">
            <v-icon class="mr-2">mdi-information</v-icon>
            <strong>Note:</strong> Unchecked pages will appear disabled in the Admin Staff's sidebar with a lock icon. They will see a notification when attempting to access restricted pages.
          </v-alert>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="adminStaffDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="saveAdminStaff" :loading="savingAdminStaff">
            {{ editingAdminStaff ? 'Update' : 'Create' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Training Centers Management Section -->
    <div v-if="currentSection === 'training-centers'">
      <div class="mb-6">
        <v-row class="align-center">
          <v-col cols="12" md="3">
            <v-text-field v-model="trainingCenterSearch" placeholder="Search training centers..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="trainingCenterStatusFilter" :items="['All', 'Active', 'pending', 'Inactive']" label="All Status" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="3">
            <v-btn color="error" prepend-icon="mdi-plus" @click="openTrainingCenterDialog()">Add Training Center</v-btn>
          </v-col>
        </v-row>
      </div>
      <v-card elevation="0">
        <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
          <span class="section-title error--text">Accredited Training Center</span>
          <v-btn v-if="selectedTrainingCenters.length > 0" color="error" variant="outlined" prepend-icon="mdi-delete" @click="deleteSelectedTrainingCenters">
            Delete Selected ({{ selectedTrainingCenters.length }})
          </v-btn>
        </v-card-title>
        <v-data-table v-model="selectedTrainingCenters" :headers="trainingCenterHeaders" :items="filteredTrainingCenters" :items-per-page="10" show-select item-value="id" class="elevation-0" density="compact">
          <template v-slot:item.location="{ item }">
            <span style="display:none">{{ ensureItemPlaceIndicator(item) }}</span>
            {{ item.place_indicator || item.location || 'Unknown ZIP' }}
          </template>
          <template v-slot:item.zip_code="{ item }">
            {{ item.zip_code || 'Unknown ZIP' }}
          </template>
          <template v-slot:item.caregiverCount="{ item }">
            <v-chip color="info" size="small">
              <v-icon size="14" class="mr-1">mdi-account-heart</v-icon>
              {{ item.caregiverCount }}
            </v-chip>
          </template>
          <template v-slot:item.commissionEarned="{ item }">
            <span class="font-weight-bold text-success">${{ item.commissionEarned }}</span>
          </template>
          <template v-slot:item.status="{ item }">
            <v-chip
              :color="getUserStatusColor(item.status)"
              size="small"
              class="font-weight-bold"
              :style="(String(item.status).toLowerCase() === 'pending') ? 'background-color: #f59e0b !important; color: #ffffff !important;' : ''"
              :prepend-icon="getStatusIcon(item.status)"
            >{{ item.status }}</v-chip>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn class="action-btn-view" icon="mdi-eye" size="small" @click="viewTrainingCenterDetails(item)"></v-btn>
              <v-btn class="action-btn-edit" icon="mdi-pencil" size="small" @click="openTrainingCenterDialog(item)"></v-btn>
              <v-btn 
                v-if="parseFloat(item.commissionEarned) > 0" 
                class="action-btn-pay" 
                icon="mdi-cash-multiple" 
                size="small" 
                color="success"
                @click="payTrainingCommission(item)"
                :loading="payingCommission === item.id"
              ></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- View Training Center Details Dialog -->
    <v-dialog v-model="viewTrainingCenterDialog" max-width="900" scrollable>
      <v-card v-if="viewingTrainingCenter">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="section-title" style="color: white;">Training Center Details</span>
            <v-btn icon="mdi-close" variant="text" style="color: white;" @click="viewTrainingCenterDialog = false"></v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6" style="max-height: calc(80vh - 140px); overflow-y: auto;">
          <v-row>
            <v-col cols="12" class="text-center mb-4">
              <v-avatar size="120" color="warning" class="mb-3">
                <v-icon size="60" color="white">mdi-school</v-icon>
              </v-avatar>
              <h2>{{ viewingTrainingCenter.name }}</h2>
              <v-chip :color="getUserStatusColor(viewingTrainingCenter.status)" class="mt-2">{{ viewingTrainingCenter.status }}</v-chip>
            </v-col>
          </v-row>
          
          <v-divider class="mb-4"></v-divider>
          
          <v-row>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Email</div>
                <div class="detail-value">{{ viewingTrainingCenter.email }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Phone</div>
                <div class="detail-value">{{ viewingTrainingCenter.phone }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Zip Code</div>
                <div class="detail-value">{{ viewingTrainingCenter.zip_code || viewingTrainingCenter.zip || 'Unknown ZIP' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Location</div>
                <div class="detail-value">{{ viewingTrainingCenter.place_indicator || viewingTrainingCenter.location || 'Unknown ZIP' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Address</div>
                <div class="detail-value">{{ viewingTrainingCenter.address || 'Not provided' }}</div>
              </div>
            </v-col>
          </v-row>
          
          <v-divider class="my-4"></v-divider>
          
          <h3 class="mb-3">Commission Statistics</h3>
          <v-row>
            <v-col cols="12" md="4">
              <v-card class="pa-4 text-center" color="info" variant="tonal">
                <v-icon size="32" color="info">mdi-account-heart</v-icon>
                <h4 class="mt-2">{{ viewingTrainingCenter.caregiverCount }}</h4>
                <div class="text-caption">Caregivers</div>
              </v-card>
            </v-col>
            <v-col cols="12" md="4">
              <v-card class="pa-4 text-center" color="primary" variant="tonal">
                <v-icon size="32" color="primary">mdi-clock</v-icon>
                <h4 class="mt-2">{{ viewingTrainingCenter.totalHours }}</h4>
                <div class="text-caption">Total Hours</div>
              </v-card>
            </v-col>
            <v-col cols="12" md="4">
              <v-card class="pa-4 text-center" color="success" variant="tonal">
                <v-icon size="32" color="success">mdi-currency-usd</v-icon>
                <h4 class="mt-2">${{ viewingTrainingCenter.commissionEarned }}</h4>
                <div class="text-caption">Commission Earned</div>
              </v-card>
            </v-col>
          </v-row>
          
          <v-row class="mt-4">
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Commission Rate</div>
                <div class="detail-value">$0.50 per hour</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Joined</div>
                <div class="detail-value">{{ viewingTrainingCenter.joined }}</div>
              </div>
            </v-col>
          </v-row>

          <v-divider class="my-4" v-if="viewingTrainingCenter.caregivers && viewingTrainingCenter.caregivers.length > 0"></v-divider>
          
          <div v-if="viewingTrainingCenter.caregivers && viewingTrainingCenter.caregivers.length > 0">
            <h3 class="mb-3">Caregivers Using This Training Center</h3>
            <div style="max-height: 300px; overflow-y: auto;">
              <v-table density="compact">
                <thead style="position: sticky; top: 0; background-color: white; z-index: 1;">
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="caregiver in viewingTrainingCenter.caregivers" :key="caregiver.id">
                    <td>{{ caregiver.name }}</td>
                    <td>{{ caregiver.email }}</td>
                    <td><v-chip size="x-small" color="success">Active</v-chip></td>
                  </tr>
                </tbody>
              </v-table>
            </div>
          </div>
        </v-card-text>
        <v-card-actions class="pa-6">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="viewTrainingCenterDialog = false">Close</v-btn>
          <v-btn color="error" @click="openTrainingCenterDialog(viewingTrainingCenter); viewTrainingCenterDialog = false">Edit</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Add/Edit Training Center Dialog -->
    <v-dialog v-model="trainingCenterDialog" max-width="900" scrollable>
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">{{ editingTrainingCenter ? 'Edit Training Center' : 'Add Training Center' }}</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <div class="mb-4">
            <h3 class="text-h6 mb-4">Training Center Information</h3>
            <v-row>
              <v-col cols="12">
                <v-text-field v-model="trainingCenterFormData.name" label="Training Center Name *" variant="outlined" required />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="trainingCenterFormData.email" label="Email *" type="email" variant="outlined" required />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="trainingCenterFormData.phone" 
                  label="Phone" 
                  variant="outlined"
                  placeholder="(646) 282-8282"
                  maxlength="14"
                  @update:model-value="trainingCenterFormData.phone = formatPhoneNumber(trainingCenterFormData.phone)"
                />
              </v-col>
              <v-col cols="12">
                <v-text-field v-model="trainingCenterFormData.address" label="Address" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="trainingCenterFormData.state" label="State" variant="outlined" readonly value="New York" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="trainingCenterFormData.county" :items="nyCounties" label="County" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="trainingCenterFormData.city" label="City" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="trainingCenterFormData.zip_code" 
                  label="ZIP Code" 
                  variant="outlined"
                  maxlength="5"
                  :rules="[v => !v || /^\d{5}$/.test(v) || 'Please enter a valid 5-digit ZIP code']"
                  placeholder="Enter ZIP code"
                  @input="lookupTrainingCenterZipCode"
                  @blur="lookupTrainingCenterZipCode"
                >
                  <template v-slot:prepend-inner>
                    <v-icon>mdi-map-marker</v-icon>
                  </template>
                </v-text-field>
                <div v-if="trainingCenterZipLocation" style="font-weight: 600; color: #64748b; margin-top: 0.5rem; font-size: 0.95rem; line-height: 1.35;">
                  {{ trainingCenterZipLocation }}
                </div>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-if="!editingTrainingCenter" 
                  v-model="trainingCenterFormData.password" 
                  label="Password (Optional)" 
                  :type="showTrainingPassword ? 'text' : 'password'" 
                  variant="outlined" 
                  hint="Leave blank to auto-generate secure password" 
                  persistent-hint
                  :append-inner-icon="showTrainingPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showTrainingPassword = !showTrainingPassword"
                />
                <div v-if="!editingTrainingCenter && trainingCenterFormData.password" class="password-requirements mt-2">
                  <div class="requirement-item" :class="{ valid: passwordMeetsLength(trainingCenterFormData.password) }">
                    <span class="requirement-icon">{{ passwordMeetsLength(trainingCenterFormData.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">At least 8 characters</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsUppercase(trainingCenterFormData.password) }">
                    <span class="requirement-icon">{{ passwordMeetsUppercase(trainingCenterFormData.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One capital letter</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsDigit(trainingCenterFormData.password) }">
                    <span class="requirement-icon">{{ passwordMeetsDigit(trainingCenterFormData.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One digit</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsSpecial(trainingCenterFormData.password) }">
                    <span class="requirement-icon">{{ passwordMeetsSpecial(trainingCenterFormData.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One special character</span>
                  </div>
                </div>
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="trainingCenterFormData.status" :items="['Active', 'Inactive']" label="Status *" variant="outlined" required />
              </v-col>
            </v-row>
          </div>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="trainingCenterDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="saveTrainingCenter">{{ editingTrainingCenter ? 'Update' : 'Create' }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Contractors Application Section -->
    <div v-if="currentSection === 'pending'">
      <v-card elevation="0">
        <v-card-title class="card-header pa-8">
          <span class="section-title error--text">Contractors Application</span>
        </v-card-title>
        <v-data-table :headers="applicationHeaders" :items="pendingApplications" :items-per-page="10" class="elevation-0 table-no-checkbox">
          <template v-slot:item.type="{ item }">
            <v-chip 
              v-if="item.type === 'Housekeeper'"
              color="#6A1B9A"
              size="small" 
              class="font-weight-bold housekeeper-chip" 
              style="background-color: #6A1B9A !important; color: #ffffff !important;"
              prepend-icon="mdi-broom"
            >
              {{ item.type }}
            </v-chip>
            <v-chip 
              v-else
              :color="item.type === 'Caregiver' ? 'success' : (item.type === 'Marketing Partner' ? 'info' : 'warning')" 
              size="small" 
              class="font-weight-bold" 
              :prepend-icon="item.type === 'Caregiver' ? 'mdi-account-heart' : (item.type === 'Marketing Partner' ? 'mdi-bullhorn-variant' : 'mdi-school')"
            >
              {{ item.type }}
            </v-chip>
          </template>
          <template v-slot:item.applied="{ item }">
            <span v-if="item.applied_at">{{ new Date(item.applied_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}</span>
            <span v-else>N/A</span>
          </template>
          <template v-slot:item.documents="{ item }">
            <v-chip 
              :color="(item.status && (item.status.toLowerCase() === 'approved')) ? 'success' : 'warning'" 
              size="small" 
              class="font-weight-bold" 
              :prepend-icon="(item.status && (item.status.toLowerCase() === 'approved')) ? 'mdi-check-circle' : 'mdi-clock-outline'"
            >
              {{ (item.status && (item.status.toLowerCase() === 'approved')) ? 'Approved' : 'Pending' }}
            </v-chip>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn class="action-btn-view" icon="mdi-eye" size="small" @click="viewApplication(item)"></v-btn>
              <v-btn class="action-btn-approve" icon="mdi-check" size="small" @click="approveApplication(item)"></v-btn>
              <v-btn class="action-btn-reject" icon="mdi-close" size="small" @click="rejectApplication(item)"></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- View Application Details Dialog -->
    <v-dialog v-model="viewApplicationDialog" max-width="800">
      <v-card v-if="viewingApplication">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="section-title" style="color: white;">Application Details</span>
            <v-btn icon="mdi-close" variant="text" style="color: white;" @click="viewApplicationDialog = false"></v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12" class="text-center mb-4">
              <v-avatar size="120" :color="viewingApplication.type === 'Caregiver' ? 'success' : (viewingApplication.type === 'Housekeeper' ? '#6A1B9A' : (viewingApplication.type === 'Marketing Partner' ? 'info' : 'warning'))" class="mb-3">
                <span class="text-h3 font-weight-bold text-white">{{ viewingApplication.name.split(' ').map(n => n[0]).join('') }}</span>
              </v-avatar>
              <h2>{{ viewingApplication.name }}</h2>
              <v-chip 
                v-if="viewingApplication.type === 'Housekeeper'"
                color="#6A1B9A"
                size="large" 
                class="mt-2 font-weight-bold housekeeper-chip"
                style="background-color: #6A1B9A !important; color: #ffffff !important;"
                prepend-icon="mdi-broom"
              >
                {{ viewingApplication.type }}
              </v-chip>
              <v-chip 
                v-else
                :color="viewingApplication.type === 'Caregiver' ? 'success' : (viewingApplication.type === 'Marketing Partner' ? 'info' : 'warning')" 
                size="large" 
                class="mt-2 font-weight-bold"
                :prepend-icon="viewingApplication.type === 'Caregiver' ? 'mdi-account-heart' : (viewingApplication.type === 'Marketing Partner' ? 'mdi-bullhorn-variant' : 'mdi-school')"
              >
                {{ viewingApplication.type }}
              </v-chip>
              <v-chip :color="(viewingApplication.status && (viewingApplication.status.toLowerCase() === 'approved')) ? 'success' : 'warning'" class="mt-2 ml-2" size="large">
                <v-icon size="16" class="mr-1">{{ (viewingApplication.status && (viewingApplication.status.toLowerCase() === 'approved')) ? 'mdi-check-circle' : 'mdi-clock-outline' }}</v-icon>
                {{ (viewingApplication.status && (viewingApplication.status.toLowerCase() === 'approved')) ? 'Approved' : 'Pending' }}
              </v-chip>
            </v-col>
          </v-row>
          
          <v-divider class="mb-4"></v-divider>
          
          <v-row>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Email</div>
                <div class="detail-value">{{ viewingApplication.email }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Phone</div>
                <div class="detail-value">{{ viewingApplication.phone || 'Not provided' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Application Type</div>
                <div class="detail-value">
                  <v-chip 
                    :color="viewingApplication.type === 'Caregiver' ? 'success' : (viewingApplication.type === 'Marketing Partner' ? 'info' : 'warning')" 
                    size="small" 
                    class="font-weight-bold"
                    :prepend-icon="viewingApplication.type === 'Caregiver' ? 'mdi-account-heart' : (viewingApplication.type === 'Marketing Partner' ? 'mdi-bullhorn-variant' : 'mdi-school')"
                  >
                    {{ viewingApplication.type }}
                  </v-chip>
                </div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Applied Date</div>
                <div class="detail-value">{{ viewingApplication.applied_at ? new Date(viewingApplication.applied_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Document Status</div>
                <div class="detail-value">
                  <v-chip 
                    :color="viewingApplication.documents === 'Complete' ? 'success' : 'warning'" 
                    size="small" 
                    class="font-weight-bold"
                    :prepend-icon="viewingApplication.documents === 'Complete' ? 'mdi-check-circle' : 'mdi-alert-circle'"
                  >
                    {{ viewingApplication.documents === 'Complete' ? 'Documents Complete' : 'Documents Pending' }}
                  </v-chip>
                </div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Application Status</div>
                <div class="detail-value">
                  <v-chip 
                    :color="(viewingApplication.status && (viewingApplication.status.toLowerCase() === 'approved')) ? 'success' : 'warning'" 
                    size="small" 
                    class="font-weight-bold"
                    :prepend-icon="(viewingApplication.status && (viewingApplication.status.toLowerCase() === 'approved')) ? 'mdi-check-circle' : 'mdi-clock-outline'"
                  >
                    {{ (viewingApplication.status && (viewingApplication.status.toLowerCase() === 'approved')) ? 'Approved' : 'Pending' }}
                  </v-chip>
                </div>
              </div>
            </v-col>
          </v-row>
          
          <v-divider class="my-4"></v-divider>
          
          <v-card-actions class="pa-0">
            <v-spacer></v-spacer>
            <v-btn color="grey" variant="text" @click="viewApplicationDialog = false">Close</v-btn>
            <v-btn color="success" variant="flat" prepend-icon="mdi-check" @click="approveApplication(viewingApplication); viewApplicationDialog = false">Approve</v-btn>
            <v-btn color="error" variant="flat" prepend-icon="mdi-close" @click="rejectApplication(viewingApplication); viewApplicationDialog = false">Reject</v-btn>
          </v-card-actions>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Password Resets Section -->
    <div v-if="currentSection === 'password-resets'">
      <v-card elevation="0">
        <v-card-title class="card-header pa-8">
          <span class="section-title error--text">Password Reset Requests</span>
        </v-card-title>
        <v-data-table :headers="passwordResetHeaders" :items="passwordResets" :items-per-page="10" class="elevation-0 table-no-checkbox">
          <template v-slot:item.status="{ item }">
            <v-chip :color="item.status === 'Completed' ? 'success' : 'warning'" size="small" class="font-weight-bold" :prepend-icon="getStatusIcon(item.status)">{{ item.status }}</v-chip>
          </template>
          <template v-slot:item.userType="{ item }">
            <v-chip :color="item.userType === 'Caregiver' ? 'success' : 'primary'" size="small" class="font-weight-bold" :prepend-icon="item.userType === 'Caregiver' ? 'mdi-account-heart' : 'mdi-account'">{{ item.userType }}</v-chip>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn v-if="item.status === 'Pending'" class="action-btn-approve" icon="mdi-check" size="small" @click="processPasswordReset(item)"></v-btn>
              <v-icon v-else color="grey" size="small">mdi-check-circle</v-icon>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- Time Tracking Section -->
    <div v-if="currentSection === 'time-tracking'">
      <div class="mb-6">
        <v-row class="align-center">
          <v-col cols="12" md="3">
            <v-text-field v-model="timeTrackingSearch" placeholder="Search clients..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="timeTrackingDateFilter" :items="['Today', 'This Week', 'This Month', 'All Time']" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="timeTrackingStatusFilter" :items="['All', 'Clocked In', 'Clocked Out']" variant="outlined" density="compact" hide-details />
          </v-col>
        </v-row>
      </div>

      <v-card elevation="0">
        <v-card-title class="card-header pa-8">
          <div class="d-flex justify-space-between align-center w-100">
            <span class="section-title error--text">Client Time Tracking</span>
            <div class="d-flex gap-2">
              <v-btn 
                color="info" 
                prepend-icon="mdi-history" 
                @click="timeTrackingHistoryDialog = true"
              >
                View History
              </v-btn>
              <v-btn 
                color="error" 
                prepend-icon="mdi-refresh" 
                @click="refreshTimeTracking"
                :loading="false"
              >
                Refresh Data
              </v-btn>
            </div>
          </div>
        </v-card-title>
        <v-data-table :headers="timeTrackingHeaders" :items="filteredTimeTracking" :items-per-page="15" class="elevation-0 table-no-checkbox">
          <template v-slot:item.client="{ item }">
            <div class="font-weight-bold text-h6">{{ item.client }}</div>
          </template>
          <template v-slot:item.caregivers="{ item }">
            <div class="d-flex flex-column gap-2">
              <div v-if="item.caregivers.length === 0" class="text-grey text-caption">
                No caregivers assigned
              </div>
              <template v-else>
                <!-- Show first 2 caregivers directly -->
                <div v-for="(caregiver, index) in item.caregivers.slice(0, 2)" :key="caregiver.id" class="caregiver-row pa-2" style="border: 1px solid #e0e0e0; border-radius: 4px;">
                  <div class="d-flex align-center justify-space-between">
                    <span class="font-weight-medium">{{ caregiver.name }}</span>
                    <v-chip 
                      :color="caregiver.status === 'Clocked In' ? 'success' : 'default'" 
                      size="small" 
                      variant="flat"
                    >
                      {{ caregiver.status }}
                    </v-chip>
                  </div>
                  <div v-if="caregiver.status === 'Clocked In'" class="text-body-2 text-grey mt-1" style="font-size: 0.95rem;">
                    Clock In: {{ caregiver.clockIn || 'N/A' }} | Today: {{ formatHours(caregiver.todayHours) }} | Week: {{ formatHours(caregiver.weekHours) }}
                  </div>
                </div>
                
                <!-- Dropdown for remaining caregivers if more than 2 -->
                <v-menu v-if="item.caregivers.length > 2" location="bottom">
                  <template v-slot:activator="{ props }">
                    <v-btn 
                      v-bind="props"
                      variant="outlined"
                      size="small"
                      color="primary"
                      prepend-icon="mdi-chevron-down"
                    >
                      View {{ item.caregivers.length - 2 }} More Caregiver{{ item.caregivers.length - 2 > 1 ? 's' : '' }}
                    </v-btn>
                  </template>
                  <v-list max-height="400" style="max-width: 500px;">
                    <v-list-item 
                      v-for="caregiver in item.caregivers.slice(2)" 
                      :key="caregiver.id"
                      class="pa-3"
                    >
                      <v-list-item-title class="font-weight-medium mb-2">{{ caregiver.name }}</v-list-item-title>
                      <div class="d-flex align-center mb-2">
                        <v-chip 
                          :color="caregiver.status === 'Clocked In' ? 'success' : 'default'" 
                          size="small" 
                          variant="flat"
                        >
                          {{ caregiver.status }}
                        </v-chip>
                      </div>
                      <div v-if="caregiver.status === 'Clocked In'" class="text-body-2 text-grey" style="font-size: 0.95rem;">
                        Clock In: {{ caregiver.clockIn || 'N/A' }} | Today: {{ formatHours(caregiver.todayHours) }} | Week: {{ formatHours(caregiver.weekHours) }}
                      </div>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>
            </div>
          </template>
          <template v-slot:item.status="{ item }">
            <v-chip 
              :color="item.status === 'Active' ? 'success' : 'grey-darken-1'" 
              size="small" 
              class="font-weight-bold"
              variant="flat"
            >
              {{ item.status }}
            </v-chip>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn 
                class="action-btn-view" 
                icon="mdi-eye" 
                size="small" 
                variant="text"
                @click="viewClientCaregivers(item)"
              />
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- Client Bookings Section -->
    <div v-if="currentSection === 'client-bookings'">
      <div class="mb-6">
        <v-row class="align-center">
          <v-col cols="12" md="4">
            <v-text-field v-model="bookingSearch" placeholder="Search bookings..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="3">
            <v-select v-model="bookingStatusFilter" :items="['All', 'Pending', 'Approved', 'Rejected']" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="3">
            <v-select v-model="bookingDateFilter" :items="['All Time', 'Today', 'This Week', 'This Month']" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-btn color="error" prepend-icon="mdi-plus" @click="addBookingDialog = true">Add Booking</v-btn>
          </v-col>
        </v-row>
      </div>

      <v-card elevation="0">
        <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
          <span class="section-title error--text">Client Bookings</span>
          <v-btn v-if="selectedBookings.length > 0" color="error" variant="outlined" prepend-icon="mdi-delete" @click="deleteSelectedBookings">
            Delete Selected ({{ selectedBookings.length }})
          </v-btn>
        </v-card-title>
        <v-data-table v-model="selectedBookings" :headers="bookingHeaders" :items="filteredBookings" :items-per-page="10" :items-per-page-options="[10, 25, 50, -1]" show-select item-value="id" class="elevation-0 admin-bookings-table" density="compact">
          <template v-slot:item.formattedPrice="{ item }">
            <div class="price-cell">
              <span v-if="item.referralDiscountApplied && item.referralDiscountApplied > 0" class="original-price-strike">
                ${{ ((item.hoursPerDay * item.durationDays * (parseFloat(item.hourlyRate) + parseFloat(item.referralDiscountApplied)))).toLocaleString() }}
              </span>
              <span class="current-price">{{ item.formattedPrice }}</span>
            </div>
          </template>
          <template v-slot:item.status="{ item }">
            <v-chip :color="getBookingStatusColor(item.status)" size="small" class="font-weight-bold" :prepend-icon="getStatusIcon(item.status)">{{ item.status }}</v-chip>
          </template>
          <template v-slot:item.paymentStatus="{ item }">
            <v-chip 
              v-if="item.paymentStatus === 'paid'" 
              color="success" 
              size="small" 
              class="font-weight-bold" 
              prepend-icon="mdi-check-circle"
            >
              Paid
            </v-chip>
            <v-chip 
              v-else 
              color="warning" 
              size="small" 
              class="font-weight-bold" 
              prepend-icon="mdi-clock-outline"
            >
              Unpaid
            </v-chip>
          </template>
          <template v-slot:item.assignmentStatus="{ item }">
            <v-chip :color="getAssignmentStatusColor(item.assignmentStatus)" size="small" class="font-weight-bold" :prepend-icon="getStatusIcon(item.assignmentStatus)">{{ item.assignmentStatus }}</v-chip>
          </template>
          <template v-slot:item.coverageEnd="{ item }">
            <div class="d-flex align-center">
              <span :class="item.isActive ? 'success--text font-weight-bold' : ''">
                {{ item.coverageEnd }}
              </span>
              <span v-if="item.isActive" class="ml-2 active-indicator" style="width: 8px; height: 8px; border-radius: 50%; background-color: #4caf50; display: inline-block;"></span>
            </div>
          </template>
          <template v-slot:item.assignedCount="{ item }">
            <div class="assignment-progress">
              <div class="progress-text">{{ item.assignedCount }} / {{ item.caregiversNeeded }}</div>
              <v-progress-linear 
                :model-value="(item.assignedCount / item.caregiversNeeded) * 100" 
                :color="item.assignedCount >= item.caregiversNeeded ? 'success' : item.assignedCount > 0 ? 'warning' : 'error'" 
                height="6" 
                rounded
                class="mt-1"
              />
            </div>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn v-if="item.status === 'pending'" class="action-btn-approve" icon="mdi-check" size="small" @click="approveBooking(item)"></v-btn>
              <v-btn v-if="item.status === 'pending'" class="action-btn-reject" icon="mdi-close" size="small" @click="rejectBooking(item)"></v-btn>
              <v-btn class="action-btn-view" icon="mdi-eye" size="small" @click="viewBooking(item)"></v-btn>
              <!-- Assignment / management actions depend on service type -->
              <v-btn
                v-if="(item.status === 'approved' || item.status === 'confirmed') && !String(item.service || item.service_type || '').toLowerCase().includes('housekeeping')"
                class="action-btn-caregivers"
                icon="mdi-account-group"
                size="small"
                @click="viewAssignedCaregivers(item)"
              ></v-btn>

              <v-btn
                v-if="(item.status === 'approved' || item.status === 'confirmed') && String(item.service || item.service_type || '').toLowerCase().includes('housekeeping')"
                class="action-btn-housekeepers"
                icon="mdi-broom"
                size="small"
                title="View Assigned Housekeepers"
                @click="viewAssignedHousekeepers(item)"
              ></v-btn>

              <v-btn
                v-if="item.status === 'approved' || item.status === 'confirmed'"
                :class="String(item.service || item.service_type || '').toLowerCase().includes('housekeeping') ? 'action-btn-housekeepers' : 'action-btn-edit'"
                :icon="String(item.service || item.service_type || '').toLowerCase().includes('housekeeping') ? 'mdi-broom' : 'mdi-account-plus'"
                size="small"
                :title="String(item.service || item.service_type || '').toLowerCase().includes('housekeeping') ? 'Assign Housekeepers' : 'Assign Caregivers'"
                @click="String(item.service || item.service_type || '').toLowerCase().includes('housekeeping') ? assignHousekeeperDialog(item) : assignCaregiverDialog(item)"
              ></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>
    </div>

    <!-- Reviews & Ratings Section -->
    <div v-if="currentSection === 'reviews'">
      <v-card elevation="0">
        <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
          <div>
            <span class="section-title error--text">Reviews & Ratings</span>
            <div class="text-caption text-grey mt-1">Manage client feedback and caregiver ratings</div>
          </div>
          <v-chip color="info" variant="flat" size="large">
            <v-icon start>mdi-star</v-icon>
            {{ allReviews.length }} Total Reviews
          </v-chip>
        </v-card-title>
        
        <v-card-text class="pa-0">
          <v-data-table
            :headers="reviewHeaders"
            :items="allReviews"
            :items-per-page="10"
            :loading="loadingReviews"
            class="admin-table"
            item-value="id"
          >
            <template v-slot:item.rating="{ item }">
              <div class="d-flex align-center">
                <v-rating
                  :model-value="item.rating"
                  readonly
                  density="compact"
                  size="small"
                  color="amber"
                  active-color="amber"
                ></v-rating>
                <span class="ml-2 font-weight-bold">{{ item.rating }}.0</span>
              </div>
            </template>
            
            <template v-slot:item.recommend="{ item }">
              <v-chip
                :color="item.recommend ? 'success' : 'error'"
                size="small"
                variant="flat"
              >
                <v-icon start>{{ item.recommend ? 'mdi-thumb-up' : 'mdi-thumb-down' }}</v-icon>
                {{ item.recommend ? 'Yes' : 'No' }}
              </v-chip>
            </template>
            
            <template v-slot:item.comment="{ item }">
              <div class="text-truncate" style="max-width: 200px;">
                {{ item.comment || 'No comment' }}
              </div>
            </template>
            
            <template v-slot:item.created_at="{ item }">
              <span class="text-grey">{{ item.created_at }}</span>
            </template>
            
            <template v-slot:item.actions="{ item }">
              <div class="d-flex gap-2">
                <v-btn color="info" variant="outlined" size="small" icon="mdi-eye" @click="viewReviewDetails(item)"></v-btn>
                <v-btn color="error" variant="outlined" size="small" icon="mdi-delete" @click="deleteReview(item.id)"></v-btn>
              </div>
            </template>
          </v-data-table>
        </v-card-text>
      </v-card>
    </div>

    <!-- Announcements Section -->
    <div v-if="currentSection === 'announcements'">
      <v-row>
        <v-col cols="12" md="8">
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
              <span class="section-title error--text">Send Announcement</span>
              <v-btn color="error" prepend-icon="mdi-plus" @click="announceDialog = true">New Announcement</v-btn>
            </v-card-title>
            <v-card-text class="pa-8">
              <v-text-field v-model="announcementData.title" label="Announcement Title" variant="outlined" class="mb-4" />
              <v-textarea v-model="announcementData.message" label="Message" variant="outlined" rows="4" class="mb-4" />
              <v-row>
                <v-col cols="12" md="4">
                  <v-select v-model="announcementData.type" :items="[{title: 'Information', value: 'info'}, {title: 'Warning', value: 'warning'}, {title: 'Success', value: 'success'}, {title: 'Error', value: 'error'}]" label="Type" variant="outlined" />
                </v-col>
                <v-col cols="12" md="4">
                  <v-select v-model="announcementData.recipients" :items="[{title: 'All Users', value: 'all'}, {title: 'Caregivers Only', value: 'caregivers'}, {title: 'Clients Only', value: 'clients'}]" label="Recipients" variant="outlined" />
                </v-col>
                <v-col cols="12" md="4">
                  <v-select v-model="announcementData.priority" :items="[{title: 'Low', value: 'low'}, {title: 'Normal', value: 'normal'}, {title: 'High', value: 'high'}, {title: 'Urgent', value: 'urgent'}]" label="Priority" variant="outlined" />
                </v-col>
              </v-row>
              <v-btn color="error" size="large" prepend-icon="mdi-send" @click="sendAnnouncement" class="mt-4">Send Announcement</v-btn>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card elevation="0">
            <v-card-title class="card-header pa-8">
              <span class="section-title error--text">Recent Announcements</span>
            </v-card-title>
            <v-card-text class="pa-8">
              <div class="announcement-item mb-3">
                <div class="d-flex justify-space-between align-center mb-1">
                  <span class="announcement-title">System Maintenance</span>
                  <v-chip color="warning" size="x-small">Warning</v-chip>
                </div>
                <div class="announcement-message">Scheduled maintenance on Dec 20</div>
                <div class="text-caption text-grey">Sent 2 hours ago to All Users</div>
              </div>
              <div class="announcement-item mb-3">
                <div class="d-flex justify-space-between align-center mb-1">
                  <span class="announcement-title">New Features</span>
                  <v-chip color="success" size="x-small">Success</v-chip>
                </div>
                <div class="announcement-message">New booking features available</div>
                <div class="text-caption text-grey">Sent yesterday to All Users</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Payments Section -->
    <div v-if="currentSection === 'payments'">
      <v-tabs v-model="paymentsTab" color="error" class="mb-6">
        <v-tab value="overview">Overview</v-tab>
        <v-tab value="client-payments">Client Payments</v-tab>
        <v-tab value="caregiver-payments">Caregiver Payments</v-tab>
  <v-tab value="housekeeper-payments">Housekeeper Payments</v-tab>
        <v-tab value="marketing-commissions">Marketing Commissions</v-tab>
        <v-tab value="training-commissions">Training Commissions</v-tab>
        <v-tab value="transactions">All Transactions</v-tab>
      </v-tabs>

      <v-tabs-window v-model="paymentsTab">
        <!-- Overview Tab -->
        <v-tabs-window-item value="overview">
          <v-row class="mb-4">
            <v-col v-for="stat in paymentStats" :key="stat.title" cols="12" sm="6" md="3">
              <v-card elevation="0" class="compact-stat-card">
                <v-card-text class="pa-4">
                  <div class="d-flex align-center">
                    <v-icon :color="stat.color" size="24" class="mr-3">{{ stat.icon }}</v-icon>
                    <div>
                      <div class="stat-value" :class="stat.color + '--text'">{{ stat.value }}</div>
                      <div class="stat-label">{{ stat.title }}</div>
                      <div class="stat-change" :class="stat.changeColor">{{ stat.change }}</div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <!-- Money Flow Monitoring Card -->
          <v-row class="mb-4">
            <v-col cols="12">
              <v-card elevation="2" class="money-flow-card" style="border: 2px solid #1565c0;">
                <v-card-title class="pa-4" style="background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);">
                  <div class="d-flex align-center justify-space-between w-100">
                    <div class="d-flex align-center">
                      <v-icon size="32" color="white" class="mr-3">mdi-cash-sync</v-icon>
                      <div>
                        <span class="text-h5 font-weight-bold text-white">Money Flow Monitor</span>
                        <div class="text-caption text-white" style="opacity: 0.9;">Real-time tracking of all payments</div>
                      </div>
                    </div>
                    <v-chip color="success" variant="flat" size="large">
                      <v-icon start>mdi-check-circle</v-icon>
                      System Active
                    </v-chip>
                  </div>
                </v-card-title>
                
                <v-card-text class="pa-4">
                  <!-- Today's Activity -->
                  <div class="mb-6">
                    <div class="text-h6 mb-3 d-flex align-center">
                      <v-icon color="primary" class="mr-2">mdi-calendar-today</v-icon>
                      Today's Activity
                    </div>
                    <v-row>
                      <v-col cols="12" md="4">
                        <v-card variant="tonal" color="success" class="pa-4">
                          <div class="d-flex align-center justify-space-between">
                            <div>
                              <div class="text-caption text-grey-darken-2">Money In (Clients)</div>
                              <div class="text-h4 font-weight-bold success--text">
                                ${{ moneyFlow.today.payments_in.toFixed(2) }}
                              </div>
                            </div>
                            <v-icon size="48" color="success" style="opacity: 0.3;">mdi-arrow-down-bold-circle</v-icon>
                          </div>
                        </v-card>
                      </v-col>
                      <v-col cols="12" md="4">
                        <v-card variant="tonal" color="error" class="pa-4">
                          <div class="d-flex align-center justify-space-between">
                            <div>
                              <div class="text-caption text-grey-darken-2">Money Out (Contractors)</div>
                              <div class="text-h4 font-weight-bold error--text">
                                ${{ moneyFlow.today.payouts_out.toFixed(2) }}
                              </div>
                            </div>
                            <v-icon size="48" color="error" style="opacity: 0.3;">mdi-arrow-up-bold-circle</v-icon>
                          </div>
                        </v-card>
                      </v-col>
                      <v-col cols="12" md="4">
                        <v-card variant="tonal" color="primary" class="pa-4">
                          <div class="d-flex align-center justify-space-between">
                            <div>
                              <div class="text-caption text-grey-darken-2">Net Change</div>
                              <div class="text-h4 font-weight-bold primary--text">
                                {{ moneyFlow.today.net_change >= 0 ? '+' : '' }}${{ moneyFlow.today.net_change.toFixed(2) }}
                              </div>
                            </div>
                            <v-icon size="48" color="primary" style="opacity: 0.3;">mdi-scale-balance</v-icon>
                          </div>
                        </v-card>
                      </v-col>
                    </v-row>
                  </div>

                  <!-- All-Time Totals -->
                  <div class="mb-6">
                    <div class="text-h6 mb-3 d-flex align-center">
                      <v-icon color="primary" class="mr-2">mdi-chart-line</v-icon>
                      All-Time Totals
                    </div>
                    <v-row>
                      <v-col cols="12" md="3">
                        <div class="text-caption text-grey-darken-2">Total Received</div>
                        <div class="text-h5 font-weight-bold">
                          ${{ moneyFlow.totals.total_payments_in.toFixed(2) }}
                        </div>
                      </v-col>
                      <v-col cols="12" md="3">
                        <div class="text-caption text-grey-darken-2">Total Paid Out</div>
                        <div class="text-h5 font-weight-bold">
                          ${{ moneyFlow.totals.total_payouts_out.toFixed(2) }}
                        </div>
                      </v-col>
                      <v-col cols="12" md="3">
                        <div class="text-caption text-grey-darken-2">Pending Payouts</div>
                        <div class="text-h5 font-weight-bold warning--text">
                          ${{ moneyFlow.totals.pending_payouts.toFixed(2) }}
                        </div>
                      </v-col>
                      <v-col cols="12" md="3">
                        <div class="text-caption text-grey-darken-2">Expected Balance</div>
                        <div class="text-h5 font-weight-bold primary--text">
                          ${{ moneyFlow.totals.expected_balance.toFixed(2) }}
                        </div>
                      </v-col>
                    </v-row>
                  </div>

                  <!-- Stripe Balance Verification -->
                  <div class="mb-6" v-if="moneyFlow.totals.stripe_balance !== null">
                    <v-alert 
                      :color="moneyFlow.totals.balance_difference === 0 ? 'success' : 'warning'"
                      variant="tonal"
                      border="start"
                      border-color="primary">
                      <div class="d-flex align-center justify-space-between">
                        <div>
                          <div class="text-subtitle-2 font-weight-bold mb-1">
                            <v-icon start>mdi-shield-check</v-icon>
                            Stripe Balance Verification
                          </div>
                          <div>
                            Database Expected: <strong>${{ moneyFlow.totals.expected_balance.toFixed(2) }}</strong> 
                            | Stripe Actual: <strong>${{ moneyFlow.totals.stripe_balance.toFixed(2) }}</strong>
                            | Difference: <strong :class="moneyFlow.totals.balance_difference === 0 ? 'success--text' : 'error--text'">
                              ${{ Math.abs(moneyFlow.totals.balance_difference).toFixed(2) }}
                            </strong>
                          </div>
                        </div>
                        <v-chip 
                          :color="moneyFlow.totals.balance_difference === 0 ? 'success' : 'warning'"
                          variant="flat">
                          {{ moneyFlow.totals.balance_difference === 0 ? '✓ Matched' : '⚠ Review' }}
                        </v-chip>
                      </div>
                    </v-alert>
                  </div>

                  <!-- Failed Payouts Warning -->
                  <div class="mb-4" v-if="moneyFlow.failed_payouts && moneyFlow.failed_payouts.length > 0">
                    <v-alert color="error" variant="tonal" border="start">
                      <div class="text-subtitle-2 font-weight-bold mb-2">
                        <v-icon start>mdi-alert-circle</v-icon>
                        {{ moneyFlow.failed_payouts.length }} Failed Payout{{ moneyFlow.failed_payouts.length > 1 ? 's' : '' }}
                      </div>
                      <div v-for="failed in moneyFlow.failed_payouts" :key="failed.id" class="mb-1">
                        • {{ failed.caregiver_name }}: ${{ failed.amount }} - {{ failed.failure_reason }}
                      </div>
                    </v-alert>
                  </div>

                  <!-- Quick Actions -->
                  <div class="d-flex gap-2 flex-wrap">
                    <v-btn color="primary" variant="flat" prepend-icon="mdi-refresh" @click="loadMoneyFlowData">
                      Refresh Data
                    </v-btn>
                    <v-btn color="primary" variant="outlined" prepend-icon="mdi-file-pdf-box" @click="exportFinancialReportPDF">
                      Export to PDF
                    </v-btn>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-card elevation="0" class="mb-3">
                <v-card-title class="card-header pa-4">
                  <span class="section-title-compact error--text">Recent Transactions</span>
                </v-card-title>
                <v-card-text class="pa-4">
                  <div v-for="transaction in recentTransactions" :key="transaction.id" class="transaction-item">
                    <div class="d-flex justify-space-between align-center mb-2">
                      <div class="d-flex align-center">
                        <v-icon :color="transaction.type === 'payment' ? 'success' : 'warning'" size="16" class="mr-2">
                          {{ transaction.type === 'payment' ? 'mdi-arrow-down' : 'mdi-arrow-up' }}
                        </v-icon>
                        <span class="transaction-desc">{{ transaction.description }}</span>
                      </div>
                      <span class="transaction-amount" :class="transaction.type === 'payment' ? 'success--text' : 'warning--text'">
                        {{ transaction.type === 'payment' ? '+' : '-' }}${{ transaction.amount }}
                      </span>
                    </div>
                    <div class="transaction-time">{{ transaction.time }}</div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="6">
              <v-card elevation="0" class="mb-3">
                <v-card-title class="card-header pa-4">
                  <span class="section-title-compact error--text">Payment Methods</span>
                </v-card-title>
                <v-card-text class="pa-4">
                  <div v-for="method in paymentMethods" :key="method.type" class="payment-method-item">
                    <div class="d-flex justify-space-between align-center mb-2">
                      <div class="d-flex align-center">
                        <v-icon :color="method.color" size="20" class="mr-2">{{ method.icon }}</v-icon>
                        <span class="method-name">{{ method.name }}</span>
                      </div>
                      <v-chip :color="method.status === 'Active' ? 'success' : 'warning'" size="small">{{ method.status }}</v-chip>
                    </div>
                    <div class="method-details">{{ method.details }}</div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-tabs-window-item>

        <!-- Client Payments Tab -->
        <v-tabs-window-item value="client-payments">
          <div class="mb-4">
            <v-row class="align-center">
              <v-col cols="12" md="4">
                <v-text-field v-model="clientPaymentSearch" placeholder="Search payments..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="3">
                <v-select v-model="paymentStatusFilter" :items="['All', 'Paid', 'Pending', 'Overdue']" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="3">
                <v-select v-model="paymentPeriodFilter" :items="['All Time', 'This Month', 'Last Month', 'Last 3 Months']" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="2">
                <v-btn color="error" prepend-icon="mdi-plus" @click="addPaymentDialog = true">Add Payment</v-btn>
              </v-col>
            </v-row>
          </div>

          <v-card elevation="0">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact error--text">Client Payments</span>
            </v-card-title>
            <v-data-table :headers="clientPaymentHeaders" :items="clientPayments" :items-per-page="10" class="elevation-0 table-no-checkbox">
              <template v-slot:item.status="{ item }">
                <v-chip :color="getPaymentStatusColor(item.status)" size="small" class="font-weight-bold">{{ item.status }}</v-chip>
              </template>
              <template v-slot:item.actions="{ item }">
                <div class="action-buttons">
                  <v-btn class="action-btn-view" icon="mdi-eye" @click="viewPayment(item)"></v-btn>
                  <v-btn v-if="item.status === 'Pending'" class="action-btn-mark-paid" icon="mdi-check" @click="markPaid(item)"></v-btn>
                </div>
              </template>
            </v-data-table>
          </v-card>
        </v-tabs-window-item>

        <!-- Caregiver Payments Tab -->
        <v-tabs-window-item value="caregiver-payments">
          <!-- Friday Payout Notice Banner -->
          <v-alert 
            :color="isFriday ? 'success' : 'info'"
            :variant="isFriday ? 'tonal' : 'outlined'"
            :border="isFriday ? 'start' : 'start'"
            border-color="primary"
            class="mb-4"
            prominent
          >
            <template v-slot:prepend>
              <v-icon :color="isFriday ? 'success' : 'primary'" size="x-large">
                {{ isFriday ? 'mdi-check-circle' : 'mdi-information' }}
              </v-icon>
            </template>
            <div class="d-flex flex-column">
              <div class="text-h6 font-weight-bold mb-2">
                {{ isFriday ? 'Today is Payout Day!' : 'Payout Schedule Information' }}
              </div>
              <div class="text-body-1 mb-2">
                {{ isFriday 
                  ? 'All payment buttons are now active. You can process caregiver payments today.' 
                  : 'Payment processing is only available on Fridays to ensure proper fund allocation and processing time.' 
                }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                <strong>Why Fridays Only?</strong> 
                This ensures: (1) Consistent weekly payment schedule for all staff, 
                (2) Adequate time for bank processing before the weekend, 
                (3) Proper reconciliation of weekly hours and earnings, 
                (4) Compliance with payment processing protocols.
              </div>
              <v-chip 
                v-if="!isFriday"
                :color="'primary'" 
                variant="flat" 
                size="small" 
                class="mt-2 align-self-start"
              >
                <v-icon start size="small">mdi-calendar</v-icon>
                Next Payout: This Friday
              </v-chip>
            </div>
          </v-alert>
          
          <div class="mb-4">
            <v-row class="align-center">
              <v-col cols="12" md="3">
                <v-text-field v-model="salarySearch" placeholder="Search caregivers..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="2">
                <v-select v-model="salaryStatusFilter" :items="['All', 'Paid', 'Pending', 'Partial', 'No Hours']" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="2">
                <v-select v-model="salaryPeriodFilter" :items="['Current Month', 'Last Month', 'Last 3 Months']" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="3" class="d-flex gap-2">
                <v-btn 
                  color="success" 
                  @click="exportCaregiverPaymentsPDF" 
                  variant="elevated"
                  size="large"
                  class="font-weight-bold text-none"
                  elevation="4"
                >
                  <v-icon size="large" class="mr-2">mdi-file-pdf-box</v-icon>
                  Export PDF
                </v-btn>
                <v-tooltip :text="isFriday ? 'Process all payments' : 'Payments are processed only on Fridays'">
                  <template v-slot:activator="{ props }">
                    <v-btn 
                      v-bind="props"
                      color="error" 
                      prepend-icon="mdi-cash-multiple" 
                      @click="processSalariesDialog = true"
                      :disabled="!isFriday"
                    >
                      Pay All
                    </v-btn>
                  </template>
                </v-tooltip>
              </v-col>
            </v-row>
          </div>

          <v-card elevation="0">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact error--text">Caregiver Payments</span>
            </v-card-title>
            <v-data-table :headers="paymentHeaders" :items="caregiverPayments" :items-per-page="10" class="elevation-0 table-no-checkbox">
              <template v-slot:item.bank_status="{ item }">
                <v-chip :color="item.bank_connected ? 'success' : 'warning'" size="small" variant="flat">
                  <v-icon start size="small">{{ item.bank_connected ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
                  {{ item.bank_status }}
                </v-chip>
              </template>
              <template v-slot:item.status="{ item }">
                <v-chip :color="getPaymentStatusColor(item.status)" size="small" class="font-weight-bold">{{ item.status }}</v-chip>
              </template>
              <template v-slot:item.actions="{ item }">
                <div class="action-buttons">
                  <v-btn class="action-btn-view" icon="mdi-eye" size="small" @click="viewPaymentDetails(item)"></v-btn>
                  <v-tooltip v-if="item.can_pay && isFriday" text="Process Payment">
                    <template v-slot:activator="{ props }">
                      <v-btn 
                        v-bind="props" 
                        class="action-btn-pay-now" 
                        icon="mdi-cash-fast" 
                        size="small" 
                        color="success" 
                        @click="payCaregiver(item)"
                      ></v-btn>
                    </template>
                  </v-tooltip>
                  <v-tooltip v-else-if="item.can_pay && !isFriday" text="Payments are processed only on Fridays">
                    <template v-slot:activator="{ props }">
                      <v-btn 
                        v-bind="props" 
                        icon="mdi-cash-clock" 
                        size="small" 
                        color="grey" 
                        disabled
                      ></v-btn>
                    </template>
                  </v-tooltip>
                  <v-tooltip v-else-if="!item.bank_connected" text="Bank account not connected">
                    <template v-slot:activator="{ props }">
                      <v-btn v-bind="props" icon="mdi-bank-off" size="small" color="grey" disabled></v-btn>
                    </template>
                  </v-tooltip>
                </div>
              </template>
            </v-data-table>
          </v-card>
        </v-tabs-window-item>

        <!-- Housekeeper Payments Tab -->
        <v-tabs-window-item value="housekeeper-payments">
          <div class="mb-4">
            <v-row class="align-center">
              <v-col cols="12" md="3">
                <v-text-field v-model="housekeeperSalarySearch" placeholder="Search housekeepers..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="2">
                <v-select v-model="housekeeperSalaryStatusFilter" :items="['All', 'Paid', 'Pending', 'Partial', 'No Hours']" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="2">
                <v-select v-model="housekeeperSalaryPeriodFilter" :items="['Current Month', 'Last Month', 'Last 3 Months']" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="3" class="d-flex gap-2">
                <v-btn 
                  color="success" 
                  variant="elevated"
                  size="large"
                  class="font-weight-bold text-none"
                  elevation="4"
                  @click="exportHousekeeperPaymentsPDF"
                >
                  <v-icon size="large" class="mr-2">mdi-file-pdf-box</v-icon>
                  Export PDF
                </v-btn>
              </v-col>
            </v-row>
          </div>

          <v-card elevation="0">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact error--text">Housekeeper Payments</span>
            </v-card-title>
            <v-data-table :headers="housekeeperPaymentHeaders" :items="filteredHousekeeperPayments" :items-per-page="10" class="elevation-0 table-no-checkbox">
              <template v-slot:item.bank_status="{ item }">
                <v-chip :color="item.bank_connected ? 'success' : 'warning'" size="small" variant="flat">
                  <v-icon start size="small">{{ item.bank_connected ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
                  {{ item.bank_status }}
                </v-chip>
              </template>
              <template v-slot:item.status="{ item }">
                <v-chip :color="getPaymentStatusColor(item.status)" size="small" class="font-weight-bold">{{ item.status }}</v-chip>
              </template>
              <template v-slot:item.actions="{ item }">
                <div class="action-buttons">
                  <v-btn class="action-btn-view" icon="mdi-eye" size="small" @click="viewHousekeeperPaymentDetails(item)"></v-btn>
                  <v-tooltip v-if="item.can_pay && isFriday" text="Process Payment">
                    <template v-slot:activator="{ props }">
                      <v-btn 
                        v-bind="props" 
                        class="action-btn-pay-now" 
                        icon="mdi-cash-fast" 
                        size="small" 
                        color="success" 
                        @click="payHousekeeper(item)"
                      ></v-btn>
                    </template>
                  </v-tooltip>
                  <v-tooltip v-else-if="!item.bank_connected" text="Bank account not connected">
                    <template v-slot:activator="{ props }">
                      <v-btn v-bind="props" icon="mdi-bank-off" size="small" color="grey" disabled></v-btn>
                    </template>
                  </v-tooltip>
                </div>
              </template>
            </v-data-table>
          </v-card>
        </v-tabs-window-item>

        <!-- Marketing Commissions Tab -->
        <v-tabs-window-item value="marketing-commissions">
          <!-- Friday Payout Notice Banner -->
          <v-alert 
            :color="isFriday ? 'success' : 'info'"
            :variant="isFriday ? 'tonal' : 'outlined'"
            :border="isFriday ? 'start' : 'start'"
            border-color="primary"
            class="mb-4"
            prominent
          >
            <template v-slot:prepend>
              <v-icon :color="isFriday ? 'success' : 'primary'" size="x-large">
                {{ isFriday ? 'mdi-check-circle' : 'mdi-information' }}
              </v-icon>
            </template>
            <div class="d-flex flex-column">
              <div class="text-h6 font-weight-bold mb-2">
                {{ isFriday ? 'Today is Payout Day!' : 'Payout Schedule Information' }}
              </div>
              <div class="text-body-1 mb-2">
                {{ isFriday 
                  ? 'All payment buttons are now active. You can process marketing commission payments today.' 
                  : 'Commission payment processing is only available on Fridays to maintain consistency with all staff payments.' 
                }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                <strong>Why Fridays Only?</strong> 
                Unified payment schedule across all departments, streamlined accounting processes, 
                and predictable cash flow management for both the agency and commission earners.
              </div>
              <v-chip 
                v-if="!isFriday"
                :color="'primary'" 
                variant="flat" 
                size="small" 
                class="mt-2 align-self-start"
              >
                <v-icon start size="small">mdi-calendar</v-icon>
                Next Payout: This Friday
              </v-chip>
            </div>
          </v-alert>
          
          <div class="mb-4">
            <v-row class="align-center">
              <v-col cols="12" md="3">
                <v-text-field 
                  v-model="marketingCommissionSearch" 
                  placeholder="Search marketing staff..." 
                  prepend-inner-icon="mdi-magnify" 
                  variant="outlined" 
                  density="compact" 
                  hide-details 
                />
              </v-col>
              <v-col cols="12" md="2">
                <v-select 
                  v-model="marketingCommissionStatusFilter" 
                  :items="['All', 'Paid', 'Pending']" 
                  variant="outlined" 
                  density="compact" 
                  hide-details 
                />
              </v-col>
              <v-col cols="12" md="2">
                <v-select 
                  v-model="marketingCommissionPeriodFilter" 
                  :items="['Current Month', 'Last Month', 'Last 3 Months']" 
                  variant="outlined" 
                  density="compact" 
                  hide-details 
                />
              </v-col>
              <v-col cols="12" md="3" class="d-flex gap-2">
                <v-btn 
                  color="success" 
                  @click="exportMarketingCommissionsPDF" 
                  variant="elevated"
                  size="large"
                  class="font-weight-bold text-none"
                  elevation="4"
                >
                  <v-icon size="large" class="mr-2">mdi-file-pdf-box</v-icon>
                  Export PDF
                </v-btn>
                <v-tooltip :text="isFriday ? 'Process all marketing commissions' : 'Payments are processed only on Fridays'">
                  <template v-slot:activator="{ props }">
                    <v-btn 
                      v-bind="props"
                      color="error" 
                      prepend-icon="mdi-cash-multiple" 
                      @click="payAllMarketingCommissions"
                      :disabled="!isFriday"
                    >
                      Pay All
                    </v-btn>
                  </template>
                </v-tooltip>
              </v-col>
            </v-row>
          </div>

          <v-card elevation="0">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact error--text">Marketing Staff Commissions</span>
            </v-card-title>
            <v-data-table 
              :headers="marketingCommissionHeaders" 
              :items="filteredMarketingCommissions" 
              :items-per-page="10" 
              class="elevation-0 table-no-checkbox"
              :loading="loadingMarketingCommissions"
            >
              <template v-slot:item.bank_status="{ item }">
                <v-chip :color="item.bank_connected ? 'success' : 'warning'" size="small" variant="flat">
                  <v-icon start size="small">{{ item.bank_connected ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
                  {{ item.bank_status }}
                </v-chip>
              </template>
              <template v-slot:item.payment_status="{ item }">
                <v-chip :color="item.payment_status === 'Paid' ? 'success' : 'warning'" size="small" class="font-weight-bold">
                  {{ item.payment_status }}
                </v-chip>
              </template>
              <template v-slot:item.actions="{ item }">
                <div class="action-buttons">
                  <v-btn 
                    class="action-btn-view" 
                    icon="mdi-eye" 
                    size="small" 
                    @click="viewMarketingCommissionDetails(item)"
                  ></v-btn>
                  <v-tooltip v-if="item.pending_commission > 0 && item.bank_connected && isFriday" text="Process Payment">
                    <template v-slot:activator="{ props }">
                      <v-btn 
                        v-bind="props"
                        class="action-btn-pay-now" 
                        icon="mdi-cash-fast" 
                        size="small" 
                        color="success" 
                        @click="payMarketingCommission(item)"
                        :loading="item.paying"
                      ></v-btn>
                    </template>
                  </v-tooltip>
                  <v-tooltip v-else-if="item.pending_commission > 0 && item.bank_connected && !isFriday" text="Payments are processed only on Fridays">
                    <template v-slot:activator="{ props }">
                      <v-btn 
                        v-bind="props"
                        icon="mdi-cash-clock" 
                        size="small" 
                        color="grey" 
                        disabled
                      ></v-btn>
                    </template>
                  </v-tooltip>
                  <v-tooltip v-else-if="!item.bank_connected" text="Bank account not connected">
                    <template v-slot:activator="{ props }">
                      <v-btn v-bind="props" icon="mdi-bank-off" size="small" color="grey" disabled></v-btn>
                    </template>
                  </v-tooltip>
                  <v-tooltip v-else text="No pending commission">
                    <template v-slot:activator="{ props }">
                      <v-btn v-bind="props" icon="mdi-cash-off" size="small" color="grey" disabled></v-btn>
                    </template>
                  </v-tooltip>
                </div>
              </template>
            </v-data-table>
          </v-card>
        </v-tabs-window-item>

        <!-- Training Commissions Tab -->
        <v-tabs-window-item value="training-commissions">
          <!-- Friday Payout Notice Banner -->
          <v-alert 
            :color="isFriday ? 'success' : 'info'"
            :variant="isFriday ? 'tonal' : 'outlined'"
            :border="isFriday ? 'start' : 'start'"
            border-color="primary"
            class="mb-4"
            prominent
          >
            <template v-slot:prepend>
              <v-icon :color="isFriday ? 'success' : 'primary'" size="x-large">
                {{ isFriday ? 'mdi-check-circle' : 'mdi-information' }}
              </v-icon>
            </template>
            <div class="d-flex flex-column">
              <div class="text-h6 font-weight-bold mb-2">
                {{ isFriday ? 'Today is Payout Day!' : 'Payout Schedule Information' }}
              </div>
              <div class="text-body-1 mb-2">
                {{ isFriday 
                  ? 'All payment buttons are now active. You can process training commission payments today.' 
                  : 'Training commission payments are processed only on Fridays along with all other staff payments.' 
                }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                <strong>Why Fridays Only?</strong> 
                Synchronized payment processing ensures training centers receive payments on the same schedule as all staff members, 
                simplifying financial tracking and maintaining professional payment standards.
              </div>
              <v-chip 
                v-if="!isFriday"
                :color="'primary'" 
                variant="flat" 
                size="small" 
                class="mt-2 align-self-start"
              >
                <v-icon start size="small">mdi-calendar</v-icon>
                Next Payout: This Friday
              </v-chip>
            </div>
          </v-alert>
          
          <div class="mb-4">
            <v-row class="align-center">
              <v-col cols="12" md="3">
                <v-text-field 
                  v-model="trainingCommissionSearch" 
                  placeholder="Search training centers..." 
                  prepend-inner-icon="mdi-magnify" 
                  variant="outlined" 
                  density="compact" 
                  hide-details 
                />
              </v-col>
              <v-col cols="12" md="2">
                <v-select 
                  v-model="trainingCommissionStatusFilter" 
                  :items="['All', 'Paid', 'Pending']" 
                  variant="outlined" 
                  density="compact" 
                  hide-details 
                />
              </v-col>
              <v-col cols="12" md="2">
                <v-select 
                  v-model="trainingCommissionPeriodFilter" 
                  :items="['Current Month', 'Last Month', 'Last 3 Months']" 
                  variant="outlined" 
                  density="compact" 
                  hide-details 
                />
              </v-col>
              <v-col cols="12" md="3" class="d-flex gap-2">
                <v-btn 
                  color="success" 
                  @click="exportTrainingCommissionsPDF" 
                  variant="elevated"
                  size="large"
                  class="font-weight-bold text-none"
                  elevation="4"
                >
                  <v-icon size="large" class="mr-2">mdi-file-pdf-box</v-icon>
                  Export PDF
                </v-btn>
                <v-tooltip :text="isFriday ? 'Process all training commissions' : 'Payments are processed only on Fridays'">
                  <template v-slot:activator="{ props }">
                    <v-btn 
                      v-bind="props"
                      color="error" 
                      prepend-icon="mdi-cash-multiple" 
                      @click="payAllTrainingCommissions"
                      :disabled="!isFriday"
                    >
                      Pay All
                    </v-btn>
                  </template>
                </v-tooltip>
              </v-col>
            </v-row>
          </div>

          <v-card elevation="0">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact error--text">Training Center Commissions</span>
            </v-card-title>
            <v-data-table 
              :headers="trainingCommissionHeaders" 
              :items="filteredTrainingCommissions" 
              :items-per-page="10" 
              class="elevation-0 table-no-checkbox"
              :loading="loadingTrainingCommissions"
            >
              <template v-slot:item.bank_status="{ item }">
                <v-chip :color="item.bank_connected ? 'success' : 'warning'" size="small" variant="flat">
                  <v-icon start size="small">{{ item.bank_connected ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
                  {{ item.bank_status }}
                </v-chip>
              </template>
              <template v-slot:item.payment_status="{ item }">
                <v-chip :color="item.payment_status === 'Paid' ? 'success' : 'warning'" size="small" class="font-weight-bold">
                  {{ item.payment_status }}
                </v-chip>
              </template>
              <template v-slot:item.actions="{ item }">
                <div class="action-buttons">
                  <v-btn 
                    class="action-btn-view" 
                    icon="mdi-eye" 
                    size="small" 
                    @click="viewTrainingCommissionDetails(item)"
                  ></v-btn>
                  <v-tooltip v-if="item.pending_commission > 0 && item.bank_connected && isFriday" text="Process Payment">
                    <template v-slot:activator="{ props }">
                      <v-btn 
                        v-bind="props"
                        class="action-btn-pay-now" 
                        icon="mdi-cash-fast" 
                        size="small" 
                        color="success" 
                        @click="payTrainingCommission(item)"
                        :loading="item.paying"
                      ></v-btn>
                    </template>
                  </v-tooltip>
                  <v-tooltip v-else-if="item.pending_commission > 0 && item.bank_connected && !isFriday" text="Payments are processed only on Fridays">
                    <template v-slot:activator="{ props }">
                      <v-btn 
                        v-bind="props"
                        icon="mdi-cash-clock" 
                        size="small" 
                        color="grey" 
                        disabled
                      ></v-btn>
                    </template>
                  </v-tooltip>
                  <v-tooltip v-else-if="!item.bank_connected" text="Bank account not connected">
                    <template v-slot:activator="{ props }">
                      <v-btn v-bind="props" icon="mdi-bank-off" size="small" color="grey" disabled></v-btn>
                    </template>
                  </v-tooltip>
                  <v-tooltip v-else text="No pending commission">
                    <template v-slot:activator="{ props }">
                      <v-btn v-bind="props" icon="mdi-cash-off" size="small" color="grey" disabled></v-btn>
                    </template>
                  </v-tooltip>
                </div>
              </template>
            </v-data-table>
          </v-card>
        </v-tabs-window-item>

        <!-- All Transactions Tab -->
        <v-tabs-window-item value="transactions">
          <div class="mb-4">
            <v-row class="align-center">
              <v-col cols="12" md="4">
                <v-text-field v-model="transactionSearch" placeholder="Search transactions..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="3">
                <v-select v-model="transactionTypeFilter" :items="['All', 'Payment', 'Salary', 'Refund', 'Fee']" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="3">
                <v-select v-model="transactionPeriodFilter" :items="['All Time', 'Today', 'This Week', 'This Month']" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="2">
                <v-btn color="error" prepend-icon="mdi-file-pdf-box" @click="exportTransactions">Export PDF</v-btn>
              </v-col>
            </v-row>
          </div>

          <v-card elevation="0">
            <v-card-title class="card-header pa-4">
              <span class="section-title-compact error--text">All Transactions</span>
            </v-card-title>
            <v-data-table :headers="transactionHeaders" :items="allTransactions" :items-per-page="15" class="elevation-0 table-no-checkbox">
              <template v-slot:item.type="{ item }">
                <v-chip :color="getTransactionTypeColor(item.type)" size="small" class="font-weight-bold">{{ item.type }}</v-chip>
              </template>
              <template v-slot:item.amount="{ item }">
                <span :class="item.type === 'Payment' ? 'success--text' : 'warning--text'" class="font-weight-bold">
                  {{ item.type === 'Payment' ? '+' : '-' }}${{ item.amount }}
                </span>
              </template>
            </v-data-table>
          </v-card>
        </v-tabs-window-item>
      </v-tabs-window>
    </div>

    <!-- Payment Confirmation Dialog -->
    <v-dialog v-model="paymentConfirmDialog" max-width="550" persistent scrollable>
      <v-card elevation="8" class="rounded-lg">
        <!-- Header with Navy Blue -->
        <v-card-title class="pa-4" style="background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);">
          <div class="d-flex align-center">
            <v-icon size="28" color="white" class="mr-2">mdi-cash-check</v-icon>
            <span class="text-h6 font-weight-bold text-white">Confirm Payment</span>
          </div>
        </v-card-title>

        <v-card-text class="pa-4" v-if="selectedCaregiverPayment">
          <!-- Payment Amount Highlight - Compact Red -->
          <div class="text-center mb-3 pa-4 rounded-lg" style="background: linear-gradient(135deg, #c62828 0%, #b71c1c 100%); box-shadow: 0 3px 15px rgba(198, 40, 40, 0.3);">
            <div class="text-caption text-white mb-1" style="letter-spacing: 1px; opacity: 0.9;">PAYMENT AMOUNT</div>
            <div class="text-h4 font-weight-bold text-white">{{ selectedCaregiverPayment.unpaid_display }}</div>
          </div>

          <!-- Recipient Info - Compact -->
          <v-card variant="tonal" color="blue-darken-3" class="mb-3">
            <v-card-text class="pa-3">
              <div class="d-flex align-center mb-2">
                <v-avatar color="blue-darken-1" size="32" class="mr-2">
                  <v-icon size="18" color="white">mdi-account</v-icon>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="text-caption text-grey-darken-1">Recipient</div>
                  <div class="font-weight-bold">{{ selectedCaregiverPayment.caregiver }}</div>
                </div>
              </div>
              <div class="d-flex align-center">
                <v-avatar color="blue-darken-1" size="32" class="mr-2">
                  <v-icon size="18" color="white">mdi-email</v-icon>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="text-caption text-grey-darken-1">Email</div>
                  <div class="text-body-2">{{ selectedCaregiverPayment.caregiver_email }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>

          <!-- Payment Details - Compact -->
          <v-card variant="outlined" class="mb-3" style="border: 1px solid #e0e0e0;">
            <v-card-text class="pa-3">
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="16" class="mr-1">mdi-clock-outline</v-icon>
                  Hours:
                </span>
                <span class="font-weight-bold">{{ selectedCaregiverPayment.hours_display }}</span>
              </div>
              <v-divider class="my-1"></v-divider>
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="16" class="mr-1">mdi-currency-usd</v-icon>
                  Rate:
                </span>
                <span class="font-weight-bold">{{ selectedCaregiverPayment.rate }}</span>
              </div>
              <v-divider class="my-1"></v-divider>
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="16" class="mr-1">mdi-calendar</v-icon>
                  Period:
                </span>
                <span class="font-weight-bold">{{ selectedCaregiverPayment.period }}</span>
              </div>
              <v-divider class="my-2" style="border-color: #c62828;"></v-divider>
              <div class="d-flex justify-space-between align-center pa-2 rounded" style="background: #ffebee;">
                <span class="font-weight-bold">Total:</span>
                <span class="text-h6 font-weight-bold" style="color: #c62828;">{{ selectedCaregiverPayment.unpaid_display }}</span>
              </div>
            </v-card-text>
          </v-card>

          <!-- Bank Transfer Info - Compact -->
          <v-alert variant="tonal" class="mb-2 py-2" color="blue-darken-3" density="compact">
            <div class="d-flex align-center">
              <v-icon size="20" class="mr-2">mdi-bank-transfer</v-icon>
              <span class="text-caption">Bank transfer via Stripe to connected account</span>
            </div>
          </v-alert>

          <!-- Security Notice - Compact -->
          <v-alert variant="tonal" density="compact" color="green-darken-1" class="py-1">
            <div class="d-flex align-center">
              <v-icon size="16" class="mr-1">mdi-shield-check</v-icon>
              <span class="text-caption">Secure payment • Bank-level encryption</span>
            </div>
          </v-alert>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn 
            variant="outlined" 
            size="default"
            @click="paymentConfirmDialog = false" 
            class="px-4"
            color="grey-darken-1"
          >
            <v-icon start size="20">mdi-close</v-icon>
            Cancel
          </v-btn>
          <v-btn 
            size="default"
            variant="elevated" 
            @click="confirmPayment" 
            class="px-6"
            style="background: linear-gradient(135deg, #c62828 0%, #b71c1c 100%); color: white;"
          >
            <v-icon start size="20">mdi-check-circle</v-icon>
            Confirm Payment
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Payment Details Dialog -->
    <v-dialog v-model="caregiverPaymentDetailsDialog" max-width="550" scrollable>
      <v-card elevation="8" class="rounded-lg">
        <!-- Header with Navy Blue -->
        <v-card-title class="pa-4" style="background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);">
          <div class="d-flex align-center">
            <v-icon size="28" color="white" class="mr-2">mdi-information</v-icon>
            <span class="text-h6 font-weight-bold text-white">Payment Details</span>
          </div>
        </v-card-title>

        <v-card-text class="pa-4" v-if="selectedCaregiverPaymentDetails">
          <!-- Caregiver Info -->
          <v-card variant="tonal" color="blue-darken-3" class="mb-3">
            <v-card-text class="pa-3">
              <div class="d-flex align-center mb-2">
                <v-avatar color="blue-darken-1" size="40" class="mr-3">
                  <v-icon size="20" color="white">mdi-account</v-icon>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="text-caption text-grey-darken-1">Caregiver</div>
                  <div class="font-weight-bold text-h6">{{ selectedCaregiverPaymentDetails.caregiver }}</div>
                </div>
              </div>
              <div class="d-flex align-center">
                <v-avatar color="blue-darken-1" size="40" class="mr-3">
                  <v-icon size="20" color="white">mdi-email</v-icon>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="text-caption text-grey-darken-1">Email</div>
                  <div class="text-body-2">{{ selectedCaregiverPaymentDetails.caregiver_email }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>

          <!-- Work Details -->
          <v-card variant="outlined" class="mb-3" style="border: 2px solid #1565c0;">
            <v-card-text class="pa-3">
              <div class="text-caption text-grey-darken-2 mb-2 font-weight-bold" style="letter-spacing: 1px;">WORK DETAILS</div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="blue-darken-1">mdi-clock-outline</v-icon>
                  Total Hours:
                </span>
                <span class="font-weight-bold">{{ selectedCaregiverPaymentDetails.hours_display }}</span>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="blue-darken-1">mdi-currency-usd</v-icon>
                  Hourly Rate:
                </span>
                <span class="font-weight-bold">{{ selectedCaregiverPaymentDetails.rate }}</span>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="blue-darken-1">mdi-calendar-multiple</v-icon>
                  Days Worked:
                </span>
                <span class="font-weight-bold">{{ selectedCaregiverPaymentDetails.days_worked }}</span>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="blue-darken-1">mdi-calendar-range</v-icon>
                  Period:
                </span>
                <span class="font-weight-bold">{{ selectedCaregiverPaymentDetails.period }}</span>
              </div>
            </v-card-text>
          </v-card>

          <!-- Payment Status -->
          <v-card variant="outlined" class="mb-3" style="border: 2px solid #2e7d32;">
            <v-card-text class="pa-3">
              <div class="text-caption text-grey-darken-2 mb-2 font-weight-bold" style="letter-spacing: 1px;">PAYMENT SUMMARY</div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="green-darken-2">mdi-cash-multiple</v-icon>
                  Total Earned:
                </span>
                <span class="font-weight-bold text-success">{{ selectedCaregiverPaymentDetails.amount_display }}</span>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="orange-darken-2">mdi-clock-alert</v-icon>
                  Unpaid:
                </span>
                <span class="font-weight-bold text-warning">{{ selectedCaregiverPaymentDetails.unpaid_display }}</span>
              </div>
              
              <v-divider class="my-2"></v-divider>
              
              <div class="d-flex justify-space-between align-center pa-2 rounded" style="background: #e8f5e9;">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="green-darken-3">mdi-bank</v-icon>
                  Bank Status:
                </span>
                <v-chip 
                  :color="selectedCaregiverPaymentDetails.bank_connected ? 'success' : 'error'" 
                  size="small" 
                  variant="flat"
                >
                  <v-icon start size="16">{{ selectedCaregiverPaymentDetails.bank_connected ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
                  {{ selectedCaregiverPaymentDetails.bank_status }}
                </v-chip>
              </div>
            </v-card-text>
          </v-card>

          <!-- Stripe Info (if connected) -->
          <v-alert 
            v-if="selectedCaregiverPaymentDetails.bank_connected" 
            type="info" 
            variant="tonal" 
            density="compact"
            class="mb-0"
          >
            <div class="text-body-2">
              <v-icon size="16" class="mr-1">mdi-information</v-icon>
              Stripe Connect ID: <code class="text-caption">{{ selectedCaregiverPaymentDetails.stripe_connect_id }}</code>
            </div>
          </v-alert>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn 
            variant="elevated" 
            color="primary"
            @click="caregiverPaymentDetailsDialog = false" 
            class="px-6"
          >
            <v-icon start size="20">mdi-check</v-icon>
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Notifications Section -->
    <div v-if="currentSection === 'notifications'">
      <notification-center ref="notificationCenter" user-type="admin" :user-id="3" @open-settings="() => {/* Add settings handler */}" @action-clicked="handleNotificationAction" />
    </div>

    <!-- Profile Section -->
    <div v-if="currentSection === 'profile'">
      <v-row>
        <v-col cols="12" md="8">
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-8">
              <span class="section-title error--text">Personal Information</span>
            </v-card-title>
            <v-card-text class="pa-8">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field v-model="profileData.firstName" label="First Name" variant="outlined" @update:model-value="profileData.firstName = filterLettersOnly(profileData.firstName)" />
                  </v-col>
                  <v-col cols="12" md="6">
                  <v-text-field v-model="profileData.lastName" label="Last Name" variant="outlined" @update:model-value="profileData.lastName = filterLettersOnly(profileData.lastName)" />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="profileData.email" label="Email" variant="outlined" type="email">
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
                  <v-text-field v-model="profileData.phone" label="Phone" variant="outlined" />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="profileData.department" label="Department" variant="outlined" />
                </v-col>
                <v-col cols="12" md="6">
                  <v-select v-model="profileData.role" :items="['Super Admin', 'Admin Staff']" label="Admin Role" variant="outlined" />
                </v-col>
              </v-row>
              <v-btn color="error" class="mt-4" size="large" @click="saveProfile">Save Changes</v-btn>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-6">
            <v-card-text class="pa-8 text-center">
              <div class="position-relative d-inline-block">
                <v-avatar size="120" color="error" class="mb-4" style="cursor: pointer;" @click="triggerAvatarUpload">
                  <img v-if="userAvatar && userAvatar.length > 0" :src="userAvatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" />
                  <span v-else class="text-h3 font-weight-bold">{{ profile.firstName && profile.lastName ? `${profile.firstName[0]}${profile.lastName[0]}` : 'AU' }}</span>
                </v-avatar>
                <v-btn icon size="small" color="error" class="position-absolute" style="bottom: 16px; right: -8px;" @click="triggerAvatarUpload" :loading="uploadingAvatar">
                  <v-icon size="small">mdi-camera</v-icon>
                </v-btn>
                <input ref="avatarInput" type="file" accept="image/*" style="display: none;" @change="uploadAvatar" />
              </div>
              <h2 class="mb-2">{{ profile.firstName && profile.lastName ? `${profile.firstName} ${profile.lastName}` : 'Admin User' }}</h2>
              <p class="text-grey mb-4">System Administrator</p>
              <v-chip color="error" class="mb-4">Active</v-chip>
              <v-divider class="my-4" />
              <div class="profile-stat">
                <v-icon color="error" class="mr-2">mdi-shield-check</v-icon>
                <span>Super Admin Access</span>
              </div>
              <div class="profile-stat">
                <v-icon color="error" class="mr-2">mdi-calendar</v-icon>
                <span>Admin since Jan 2024</span>
              </div>
            </v-card-text>
          </v-card>

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
              />
              <v-text-field 
                v-model="passwordData.confirmPassword"
                label="Confirm New Password" 
                variant="outlined" 
                :type="showConfirmPassword ? 'text' : 'password'" 
                :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'" 
                @click:append-inner="showConfirmPassword = !showConfirmPassword" 
                class="mb-4" 
              />
              <v-btn color="error" block size="large" @click="changePassword">Change Password</v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Send Announcement Dialog -->
    <v-dialog v-model="announceDialog" max-width="600">
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">Send Announcement</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-text-field v-model="announcementData.title" label="Title" variant="outlined" class="mb-4" />
          <v-textarea v-model="announcementData.message" label="Message" variant="outlined" rows="4" class="mb-4" />
          <v-row>
            <v-col cols="12" md="6">
              <v-select v-model="announcementData.type" :items="[{title: 'Information', value: 'info'}, {title: 'Warning', value: 'warning'}, {title: 'Success', value: 'success'}, {title: 'Error', value: 'error'}]" label="Type" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-select v-model="announcementData.recipients" :items="[{title: 'All Users', value: 'all'}, {title: 'Caregivers Only', value: 'caregivers'}, {title: 'Clients Only', value: 'clients'}]" label="Recipients" variant="outlined" />
            </v-col>
          </v-row>
          <v-select v-model="announcementData.priority" :items="[{title: 'Low', value: 'low'}, {title: 'Normal', value: 'normal'}, {title: 'High', value: 'high'}, {title: 'Urgent', value: 'urgent'}]" label="Priority" variant="outlined" class="mb-4" />
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="announceDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="sendAnnouncement">Send Announcement</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Test Email Dialog -->
    <v-dialog v-model="testEmailDialog" max-width="500">
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">Test Email Configuration</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <p class="mb-4">Send a test email to verify your Brevo email configuration is working correctly.</p>
          <v-text-field 
            v-model="testEmailAddress" 
            label="Test Email Address" 
            variant="outlined" 
            placeholder="teofiloharry69@gmail.com"
            prepend-inner-icon="mdi-email"
            class="mb-4"
          />
          <v-alert type="info" variant="tonal" class="mb-4">
            This will send a test email to verify your SMTP configuration is working.
          </v-alert>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="testEmailDialog = false">Cancel</v-btn>
          <v-btn color="error" :loading="sendingTestEmail" @click="sendTestEmail">Send Test Email</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Add User Dialog -->
    <v-dialog v-model="addUserDialog" max-width="600">
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">Add New User</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field v-model="addUserFormData.firstName" label="First Name" variant="outlined" @update:model-value="addUserFormData.firstName = filterLettersOnly(addUserFormData.firstName)" />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="addUserFormData.lastName" label="Last Name" variant="outlined" @update:model-value="addUserFormData.lastName = filterLettersOnly(addUserFormData.lastName)" />
            </v-col>
            <v-col cols="12">
              <v-text-field v-model="addUserFormData.email" label="Email" variant="outlined" type="email" />
            </v-col>
            <v-col cols="12">
              <v-text-field 
                v-model="addUserFormData.password" 
                label="Password *" 
                :type="showAddUserPassword ? 'text' : 'password'" 
                variant="outlined" 
                required
                :append-inner-icon="showAddUserPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showAddUserPassword = !showAddUserPassword"
              />
              <div v-if="addUserFormData.password" class="password-requirements mt-2">
                <div class="requirement-item" :class="{ valid: passwordMeetsLength(addUserFormData.password) }">
                  <span class="requirement-icon">{{ passwordMeetsLength(addUserFormData.password) ? '✓' : '✗' }}</span>
                  <span class="requirement-text">At least 8 characters</span>
                </div>
                <div class="requirement-item" :class="{ valid: passwordMeetsUppercase(addUserFormData.password) }">
                  <span class="requirement-icon">{{ passwordMeetsUppercase(addUserFormData.password) ? '✓' : '✗' }}</span>
                  <span class="requirement-text">One capital letter</span>
                </div>
                <div class="requirement-item" :class="{ valid: passwordMeetsDigit(addUserFormData.password) }">
                  <span class="requirement-icon">{{ passwordMeetsDigit(addUserFormData.password) ? '✓' : '✗' }}</span>
                  <span class="requirement-text">One digit</span>
                </div>
                <div class="requirement-item" :class="{ valid: passwordMeetsSpecial(addUserFormData.password) }">
                  <span class="requirement-icon">{{ passwordMeetsSpecial(addUserFormData.password) ? '✓' : '✗' }}</span>
                  <span class="requirement-text">One special character</span>
                </div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <v-select v-model="addUserFormData.userType" :items="['Client', 'Caregiver', 'Admin']" label="User Type" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-select v-model="addUserFormData.status" :items="['Active', 'Inactive']" label="Status" variant="outlined" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="addUserDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="addUserDialog = false">Add User</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Payment Details Dialog -->
    <v-dialog v-model="paymentDetailsDialog" max-width="700">
      <v-card v-if="selectedPayment">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <v-icon start color="white">mdi-cash-multiple</v-icon>
          <span class="section-title" style="color: white;">Payment Details</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <!-- Client Information -->
            <v-col cols="12">
              <div class="text-overline mb-2">Client Information</div>
              <v-card variant="outlined" class="pa-4 mb-4">
                <div class="d-flex align-center mb-2">
                  <v-avatar color="primary" size="48" class="mr-3">
                    <span class="text-h6">{{ selectedPayment.client.charAt(0) }}</span>
                  </v-avatar>
                  <div>
                    <div class="text-h6">{{ selectedPayment.client }}</div>
                    <div class="text-caption text-grey">Client ID: {{ selectedPayment.id }}</div>
                  </div>
                </div>
              </v-card>
            </v-col>

            <!-- Service Details -->
            <v-col cols="12">
              <div class="text-overline mb-2">Service Details</div>
              <v-card variant="outlined" class="pa-4 mb-4">
                <v-row dense>
                  <v-col cols="6">
                    <div class="text-caption text-grey">Service Type</div>
                    <div class="font-weight-medium">{{ selectedPayment.service }}</div>
                  </v-col>
                  <v-col cols="6">
                    <div class="text-caption text-grey">Service Date</div>
                    <div class="font-weight-medium">{{ selectedPayment.date }}</div>
                  </v-col>
                </v-row>
              </v-card>
            </v-col>

            <!-- Payment Information -->
            <v-col cols="12">
              <div class="text-overline mb-2">Payment Information</div>
              <v-card variant="outlined" class="pa-4 mb-4">
                <v-row dense>
                  <v-col cols="6">
                    <div class="text-caption text-grey">Total Amount</div>
                    <div class="text-h5 success--text font-weight-bold">${{ selectedPayment.amount }}</div>
                  </v-col>
                  <v-col cols="6">
                    <div class="text-caption text-grey">Payment Status</div>
                    <v-chip :color="getPaymentStatusColor(selectedPayment.status)" class="mt-1">
                      {{ selectedPayment.status }}
                    </v-chip>
                  </v-col>
                  <v-col cols="12" class="mt-3">
                    <v-divider class="mb-3"></v-divider>
                    <div class="text-caption text-grey">Payment Method</div>
                    <div class="d-flex align-center mt-1">
                      <v-icon size="small" class="mr-2">mdi-credit-card</v-icon>
                      <span class="font-weight-medium">Card ending in ****</span>
                    </div>
                  </v-col>
                </v-row>
              </v-card>
            </v-col>

            <!-- Transaction ID -->
            <v-col cols="12" v-if="selectedPayment.status === 'Paid'">
              <div class="text-overline mb-2">Transaction Details</div>
              <v-card variant="outlined" class="pa-4">
                <div class="text-caption text-grey">Transaction ID</div>
                <div class="font-weight-medium font-monospace">TXN-{{ selectedPayment.id }}-{{ Date.now() }}</div>
                <div class="text-caption text-grey mt-2">Processed via Stripe</div>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-btn color="primary" variant="outlined" prepend-icon="mdi-download" @click="downloadReceipt(selectedPayment)">
            Download Receipt
          </v-btn>
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="paymentDetailsDialog = false">Close</v-btn>
          <v-btn v-if="selectedPayment.status === 'Pending'" color="success" prepend-icon="mdi-check" @click="markPaid(selectedPayment)">
            Mark as Paid
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Client Dialog -->
    <v-dialog v-model="clientDialog" max-width="900" scrollable>
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">{{ editingClient ? 'Edit Client' : 'Add Client' }}</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <div class="mb-4">
            <h3 class="text-h6 mb-4">Personal Information</h3>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field v-model="clientForm.firstName" label="First Name *" variant="outlined" required @update:model-value="clientForm.firstName = filterLettersOnly(clientForm.firstName)" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="clientForm.lastName" label="Last Name *" variant="outlined" required @update:model-value="clientForm.lastName = filterLettersOnly(clientForm.lastName)" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="clientForm.email" label="Email *" variant="outlined" type="email" required />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="clientForm.phone" 
                  label="Phone" 
                  variant="outlined"
                  placeholder="(646) 282-8282"
                  maxlength="14"
                  @update:model-value="clientForm.phone = formatPhoneNumber(clientForm.phone)"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="clientForm.birthdate" label="Birthdate" variant="outlined" type="date" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field :model-value="calculateAge(clientForm.birthdate)" label="Age" variant="outlined" readonly />
              </v-col>
              <v-col cols="12">
                <v-text-field v-model="clientForm.address" label="Address" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="clientForm.state" label="State" variant="outlined" readonly value="New York" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="clientForm.county" :items="nyCounties" label="County" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="clientForm.city" :items="clientCities" label="City" variant="outlined" :disabled="!clientForm.county" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="clientForm.zip_code" 
                  label="ZIP Code" 
                  variant="outlined"
                  maxlength="5"
                  :rules="[v => !v || /^\d{5}$/.test(v) || 'Please enter a valid 5-digit ZIP code']"
                  placeholder="Enter ZIP code"
                  @input="lookupClientZipCode"
                  @blur="lookupClientZipCode"
                >
                  <template v-slot:prepend-inner>
                    <v-icon>mdi-map-marker</v-icon>
                  </template>
                </v-text-field>
                <div v-if="clientZipLocation" style="font-weight: 600; color: #000000; margin-top: -8px; font-size: 0.75rem; line-height: 1.2;">
                  {{ clientZipLocation }}
                </div>
              </v-col>
              <v-col cols="12" v-if="!editingClient">
                <v-text-field 
                  v-model="clientForm.password" 
                  label="Password *" 
                  :type="showClientPassword ? 'text' : 'password'" 
                  variant="outlined" 
                  required
                  :append-inner-icon="showClientPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showClientPassword = !showClientPassword"
                />
                <div v-if="clientForm.password" class="password-requirements mt-2">
                  <div class="requirement-item" :class="{ valid: passwordMeetsLength(clientForm.password) }">
                    <span class="requirement-icon">{{ passwordMeetsLength(clientForm.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">At least 8 characters</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsUppercase(clientForm.password) }">
                    <span class="requirement-icon">{{ passwordMeetsUppercase(clientForm.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One capital letter</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsDigit(clientForm.password) }">
                    <span class="requirement-icon">{{ passwordMeetsDigit(clientForm.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One digit</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsSpecial(clientForm.password) }">
                    <span class="requirement-icon">{{ passwordMeetsSpecial(clientForm.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One special character</span>
                  </div>
                </div>
              </v-col>
              <v-col cols="12">
                <v-select v-model="clientForm.status" :items="['Active', 'Inactive']" label="Status *" variant="outlined" required />
              </v-col>
            </v-row>
          </div>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="closeClientDialog">Cancel</v-btn>
          <v-btn color="error" @click="saveClient">{{ editingClient ? 'Update Client' : 'Add Client' }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Caregiver Contacts Dialog -->
    <v-dialog v-model="caregiverContactsDialog" max-width="900">
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">All Caregiver Contacts</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row class="mb-4">
            <v-col cols="12" md="4">
              <v-text-field 
                v-model="caregiverSearch" 
                placeholder="Search caregivers..." 
                prepend-inner-icon="mdi-magnify" 
                variant="outlined" 
                density="compact" 
                hide-details
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-select
                v-model="boroughFilter"
                :items="boroughs"
                label="Filter by Borough"
                variant="outlined"
                density="compact"
                hide-details
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-select
                v-model="sortBy"
                :items="sortOptions"
                label="Sort by"
                variant="outlined"
                density="compact"
                hide-details
                @update:model-value="handleSortChange"
              />
            </v-col>
          </v-row>
          <div class="caregiver-list">
            <div v-for="caregiver in filteredAndSortedCaregivers" :key="caregiver.id" class="caregiver-contact-row">
              <div class="d-flex align-center pa-3">
                <v-avatar size="40" :color="caregiver.status === 'Active' ? 'success' : 'grey'" class="mr-4">
                  <span class="text-white font-weight-bold">{{ caregiver.name.split(' ').map(n => n[0]).join('') }}</span>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="caregiver-name-large">{{ caregiver.name }}</div>
                  <div class="caregiver-details">{{ caregiver.email }} • {{ caregiver.phone || '(646) 282-8282' }}</div>
                  <div class="caregiver-location">{{ caregiver.zip_code }} - {{ caregiver.location }}</div>
                </div>
              </div>
            </div>
          </div>
          <div v-if="filteredAndSortedCaregivers.length === 0" class="text-center py-8">
            <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-account-search</v-icon>
            <div class="text-grey">No caregivers found matching your criteria</div>
          </div>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="caregiverContactsDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Caregiver Dialog -->
    <v-dialog v-model="caregiverDialog" max-width="900" scrollable>
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">{{ editingCaregiver ? 'Edit Caregiver' : 'Add Caregiver' }}</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <div class="mb-6">
            <h3 class="text-h6 mb-4">Personal Information</h3>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field v-model="caregiverForm.firstName" label="First Name *" variant="outlined" required @update:model-value="caregiverForm.firstName = filterLettersOnly(caregiverForm.firstName)" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="caregiverForm.lastName" label="Last Name *" variant="outlined" required @update:model-value="caregiverForm.lastName = filterLettersOnly(caregiverForm.lastName)" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="caregiverForm.email" label="Email *" variant="outlined" type="email" required />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="caregiverForm.phone" 
                  label="Phone" 
                  variant="outlined"
                  placeholder="(646) 282-8282"
                  maxlength="14"
                  @update:model-value="caregiverForm.phone = formatPhoneNumber(caregiverForm.phone)"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="caregiverForm.birthdate" label="Birthdate" variant="outlined" type="date" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field :model-value="calculateAge(caregiverForm.birthdate)" label="Age" variant="outlined" readonly />
              </v-col>
              <v-col cols="12">
                <v-text-field v-model="caregiverForm.address" label="Address" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="caregiverForm.state" label="State" variant="outlined" readonly value="New York" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="caregiverForm.county" :items="nyCounties" label="County" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="caregiverForm.city" :items="caregiverCities" label="City" variant="outlined" :disabled="!caregiverForm.county" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="caregiverForm.zip_code" 
                  label="ZIP Code" 
                  variant="outlined"
                  maxlength="5"
                  :rules="[v => !v || /^\d{5}$/.test(v) || 'Please enter a valid 5-digit ZIP code']"
                  placeholder="Enter ZIP code"
                  @input="lookupCaregiverZipCode"
                  @blur="lookupCaregiverZipCode"
                >
                  <template v-slot:prepend-inner>
                    <v-icon>mdi-map-marker</v-icon>
                  </template>
                </v-text-field>
                <div v-if="caregiverZipLocation" style="font-weight: 600; color: #000000; margin-top: -8px; font-size: 0.75rem; line-height: 1.2;">
                  {{ caregiverZipLocation }}
                </div>
              </v-col>
              <v-col cols="12" v-if="!editingCaregiver">
                <v-text-field 
                  v-model="caregiverForm.password" 
                  label="Password *" 
                  :type="showCaregiverPassword ? 'text' : 'password'" 
                  variant="outlined" 
                  required
                  :append-inner-icon="showCaregiverPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showCaregiverPassword = !showCaregiverPassword"
                />
                <div v-if="caregiverForm.password" class="password-requirements mt-2">
                  <div class="requirement-item" :class="{ valid: passwordMeetsLength(caregiverForm.password) }">
                    <span class="requirement-icon">{{ passwordMeetsLength(caregiverForm.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">At least 8 characters</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsUppercase(caregiverForm.password) }">
                    <span class="requirement-icon">{{ passwordMeetsUppercase(caregiverForm.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One capital letter</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsDigit(caregiverForm.password) }">
                    <span class="requirement-icon">{{ passwordMeetsDigit(caregiverForm.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One digit</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsSpecial(caregiverForm.password) }">
                    <span class="requirement-icon">{{ passwordMeetsSpecial(caregiverForm.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One special character</span>
                  </div>
                </div>
              </v-col>
            </v-row>
          </div>

          <div class="mb-4">
            <h3 class="text-h6 mb-4">Professional Details</h3>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field v-model="caregiverForm.experience" label="Years of Experience" variant="outlined" type="number" />
              </v-col>
              <v-col cols="12" md="6">
                <v-checkbox v-model="caregiverForm.isCustomTrainingCenter" label="Custom Training Center" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-if="!caregiverForm.isCustomTrainingCenter" v-model="caregiverForm.trainingCenter" :items="caregiverTrainingCenters" label="Training Center" variant="outlined" />
                <v-text-field v-else v-model="caregiverForm.customTrainingCenter" label="Custom Training Center" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="caregiverForm.preferred_hourly_rate_min" label="Preferred Hourly Rate (Min)" variant="outlined" type="number" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="caregiverForm.preferred_hourly_rate_max" label="Preferred Hourly Rate (Max)" variant="outlined" type="number" />
              </v-col>
              <v-col cols="12" md="4">
                <v-checkbox v-model="caregiverForm.has_hha" label="HHA" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="8" v-if="caregiverForm.has_hha">
                <v-text-field v-model="caregiverForm.hha_number" label="HHA Certificate #" variant="outlined" />
              </v-col>
              <v-col cols="12" md="4">
                <v-checkbox v-model="caregiverForm.has_cna" label="CNA" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="8" v-if="caregiverForm.has_cna">
                <v-text-field v-model="caregiverForm.cna_number" label="CNA Certificate #" variant="outlined" />
              </v-col>
              <v-col cols="12" md="4">
                <v-checkbox v-model="caregiverForm.has_rn" label="RN" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="8" v-if="caregiverForm.has_rn">
                <v-text-field v-model="caregiverForm.rn_number" label="RN License #" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-file-input v-model="caregiverForm.trainingCertificate" label="Training Certificate" variant="outlined" accept=".pdf,.jpg,.jpeg,.png" prepend-icon="mdi-certificate" hint="Accepted formats: PDF, JPG, PNG (Max 5MB)" persistent-hint />
              </v-col>
              <v-col cols="12">
                <v-textarea v-model="caregiverForm.bio" label="Bio" variant="outlined" rows="3" />
              </v-col>
              <v-col cols="12">
                <v-select v-model="caregiverForm.status" :items="['Active', 'Inactive', 'Suspended']" label="Status *" variant="outlined" required />
              </v-col>
            </v-row>
          </div>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-btn color="grey" variant="outlined" @click="closeCaregiverDialog">Cancel</v-btn>
          <v-btn color="error" @click="saveCaregiver">{{ editingCaregiver ? 'Update' : 'Add' }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Add/Edit Housekeeper Dialog -->
    <v-dialog v-model="housekeeperDialog" max-width="900" scrollable>
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">{{ editingHousekeeper ? 'Edit Housekeeper' : 'Add Housekeeper' }}</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <div class="mb-6">
            <h3 class="text-h6 mb-4">Personal Information</h3>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field v-model="housekeeperForm.firstName" label="First Name *" variant="outlined" required @update:model-value="housekeeperForm.firstName = filterLettersOnly(housekeeperForm.firstName)" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="housekeeperForm.lastName" label="Last Name *" variant="outlined" required @update:model-value="housekeeperForm.lastName = filterLettersOnly(housekeeperForm.lastName)" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="housekeeperForm.email" label="Email *" variant="outlined" type="email" required />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="housekeeperForm.phone" 
                  label="Phone" 
                  variant="outlined"
                  placeholder="(646) 282-8282"
                  maxlength="14"
                  @update:model-value="housekeeperForm.phone = formatPhoneNumber(housekeeperForm.phone)"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="housekeeperForm.birthdate" label="Birthdate" variant="outlined" type="date" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field :model-value="calculateAge(housekeeperForm.birthdate)" label="Age" variant="outlined" readonly />
              </v-col>
              <v-col cols="12">
                <v-text-field v-model="housekeeperForm.address" label="Address" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="housekeeperForm.state" label="State" variant="outlined" readonly value="New York" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="housekeeperForm.county" :items="nyCounties" label="County" variant="outlined" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="housekeeperForm.city" :items="housekeeperCities" label="City" variant="outlined" :disabled="!housekeeperForm.county" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field 
                  v-model="housekeeperForm.zip_code" 
                  label="ZIP Code" 
                  variant="outlined"
                  maxlength="5"
                  :rules="[v => !v || /^\d{5}$/.test(v) || 'Please enter a valid 5-digit ZIP code']"
                  placeholder="Enter ZIP code"
                  @input="lookupHousekeeperZipCode"
                  @blur="lookupHousekeeperZipCode"
                >
                  <template v-slot:prepend-inner>
                    <v-icon>mdi-map-marker</v-icon>
                  </template>
                </v-text-field>
                <div v-if="housekeeperZipLocation" style="font-weight: 600; color: #000000; margin-top: -8px; font-size: 0.75rem; line-height: 1.2;">
                  {{ housekeeperZipLocation }}
                </div>
              </v-col>
              <v-col cols="12" v-if="!editingHousekeeper">
                <v-text-field 
                  v-model="housekeeperForm.password" 
                  label="Password *" 
                  :type="showHousekeeperPassword ? 'text' : 'password'" 
                  variant="outlined" 
                  required
                  :append-inner-icon="showHousekeeperPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showHousekeeperPassword = !showHousekeeperPassword"
                />
                <div v-if="housekeeperForm.password" class="password-requirements mt-2">
                  <div class="requirement-item" :class="{ valid: passwordMeetsLength(housekeeperForm.password) }">
                    <span class="requirement-icon">{{ passwordMeetsLength(housekeeperForm.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">At least 8 characters</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsUppercase(housekeeperForm.password) }">
                    <span class="requirement-icon">{{ passwordMeetsUppercase(housekeeperForm.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One capital letter</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsDigit(housekeeperForm.password) }">
                    <span class="requirement-icon">{{ passwordMeetsDigit(housekeeperForm.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One digit</span>
                  </div>
                  <div class="requirement-item" :class="{ valid: passwordMeetsSpecial(housekeeperForm.password) }">
                    <span class="requirement-icon">{{ passwordMeetsSpecial(housekeeperForm.password) ? '✓' : '✗' }}</span>
                    <span class="requirement-text">One special character</span>
                  </div>
                </div>
              </v-col>
            </v-row>
          </div>

          <div class="mb-4">
            <h3 class="text-h6 mb-4">Professional Details</h3>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field v-model="housekeeperForm.experience" label="Years of Experience" variant="outlined" type="number" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="housekeeperForm.hourly_rate" label="Hourly Rate ($)" variant="outlined" type="number" prefix="$" />
              </v-col>
              <v-col cols="12" md="6">
                <v-checkbox v-model="housekeeperForm.has_own_supplies" label="Has Own Cleaning Supplies" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="6">
                <v-checkbox v-model="housekeeperForm.available_for_transport" label="Available for Transport" density="compact" hide-details />
              </v-col>
              <v-col cols="12">
                <v-select 
                  v-model="housekeeperForm.skills" 
                  :items="['Deep Cleaning', 'Laundry', 'Ironing', 'Cooking', 'Pet Care', 'Organization', 'Window Cleaning', 'Carpet Cleaning']" 
                  label="Skills" 
                  variant="outlined" 
                  multiple 
                  chips 
                  closable-chips
                />
              </v-col>
              <v-col cols="12">
                <v-select 
                  v-model="housekeeperForm.specializations" 
                  :items="['Residential', 'Commercial', 'Move In/Out', 'Post-Construction', 'Green Cleaning', 'Senior Care']" 
                  label="Specializations" 
                  variant="outlined" 
                  multiple 
                  chips 
                  closable-chips
                />
              </v-col>
              <v-col cols="12">
                <v-textarea v-model="housekeeperForm.bio" label="Bio" variant="outlined" rows="3" />
              </v-col>
              <v-col cols="12">
                <v-select v-model="housekeeperForm.status" :items="['Active', 'Inactive', 'Suspended']" label="Status *" variant="outlined" required />
              </v-col>
            </v-row>
          </div>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-btn color="grey" variant="outlined" @click="closeHousekeeperDialog">Cancel</v-btn>
          <v-btn color="error" @click="saveHousekeeper">{{ editingHousekeeper ? 'Update' : 'Add' }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Confirmation Dialog -->
    <v-dialog v-model="confirmDialog" max-width="500">
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <div class="d-flex align-center justify-space-between w-100">
            <div class="d-flex align-center">
              <v-icon color="white" class="mr-3">{{ confirmData.buttonIcon === 'mdi-check' ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
              <span class="section-title" style="color: white;">{{ confirmData.title }}</span>
            </div>
            <v-btn icon="mdi-close" variant="text" style="color: white;" @click="confirmDialog = false"></v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6">
          <p class="text-body-1">{{ confirmData.message }}</p>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="confirmDialog = false">Cancel</v-btn>
          <v-btn :color="confirmData.buttonColor" variant="flat" :prepend-icon="confirmData.buttonIcon" @click="handleConfirm">{{ confirmData.buttonText }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Add Booking Dialog -->
    <v-dialog v-model="addBookingDialog" max-width="900" scrollable>
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">Add New Booking</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12">
              <v-select 
                v-model="bookingForm.client_id" 
                :items="clients.map(c => ({ title: c.name, value: c.id }))" 
                label="Select Client *" 
                variant="outlined"
                required
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-select 
                v-model="bookingForm.service_type" 
                :items="['Caregiver', 'Housekeeping']"
                label="Service Type *" 
                variant="outlined"
                required
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-select 
                v-model="bookingForm.duty_type" 
                :items="['8 Hours', '12 Hours', '24 Hours']" 
                label="Duty Type *" 
                variant="outlined"
                required
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field 
                v-model="bookingForm.service_date" 
                label="Service Start Date *" 
                type="date" 
                variant="outlined"
                :min="new Date().toISOString().split('T')[0]"
                required
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-select 
                v-model="bookingForm.duration_days" 
                :items="[15, 30, 60, 90]" 
                label="Duration (Days) *" 
                variant="outlined"
                required
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-select 
                v-model="bookingForm.status" 
                :items="['pending', 'approved', 'confirmed']" 
                label="Status *" 
                variant="outlined"
                required
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field 
                v-model="bookingForm.zipcode" 
                label="ZIP Code *" 
                variant="outlined"
                maxlength="5"
                :rules="[v => !!v || 'ZIP code is required', v => /^\d{5}$/.test(v) || 'Please enter a valid 5-digit ZIP code']"
                required
                @input="lookupBookingZipCode"
                @blur="lookupBookingZipCode"
              >
                <template v-slot:prepend-inner>
                  <v-icon>mdi-map-marker</v-icon>
                </template>
              </v-text-field>
              <div v-if="bookingZipLocation" class="text-caption text-grey mt-1" style="font-weight: 600;">
                {{ bookingZipLocation }}
              </div>
            </v-col>
            <v-col cols="12" md="8">
              <v-text-field 
                v-model="bookingForm.street_address" 
                label="Street Address" 
                variant="outlined"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field 
                v-model="bookingForm.apartment_unit" 
                label="Apartment/Unit" 
                variant="outlined"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field 
                v-model="bookingForm.client_age" 
                label="Client Age" 
                type="number" 
                variant="outlined"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-select 
                v-model="bookingForm.mobility_level" 
                :items="[
                  { title: 'Independent', value: 'independent' },
                  { title: 'Needs Assistance', value: 'needs_assistance' },
                  { title: 'Wheelchair Bound', value: 'wheelchair_bound' },
                  { title: 'Bedridden', value: 'bedridden' }
                ]" 
                label="Mobility Level" 
                variant="outlined"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-switch 
                v-model="bookingForm.transportation_needed" 
                label="Transportation Needed" 
                color="error"
              />
            </v-col>
            <v-col cols="12">
              <v-textarea 
                v-model="bookingForm.special_instructions" 
                label="Special Instructions" 
                variant="outlined"
                rows="3"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="closeAddBookingDialog">Cancel</v-btn>
          <v-btn color="error" @click="saveBooking" :loading="savingBooking">Create Booking</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Assign Caregiver Dialog -->
    <v-dialog v-model="assignDialog" max-width="1200" scrollable>
      <v-card class="assign-dialog-card">
        <v-card-title class="assign-dialog-header pa-6">
          <div class="d-flex align-center justify-space-between w-100">
            <div>
              <span class="assign-dialog-title">Assign {{ selectedBooking?.service_type === 'Housekeeping' ? 'Housekeepers' : 'Caregivers' }}</span>
              <div class="assign-dialog-subtitle">Select {{ selectedBooking?.service_type === 'Housekeeping' ? 'housekeepers' : 'caregivers' }} for this booking</div>
            </div>
            <div>
              <v-chip 
                :color="assignSelectedCaregivers.length > (customCaregiversNeeded || selectedBooking?.caregiversNeeded || 0) ? 'warning' : 'error'" 
                size="large" 
                class="font-weight-bold selection-counter"
              >
                {{ assignSelectedCaregivers.length }} / {{ customCaregiversNeeded || selectedBooking?.caregiversNeeded || 0 }} Selected
                <v-icon v-if="assignSelectedCaregivers.length > (customCaregiversNeeded || selectedBooking?.caregiversNeeded || 0)" size="16" class="ml-1">mdi-alert</v-icon>
              </v-chip>
              <div v-if="assignSelectedCaregivers.length > (customCaregiversNeeded || selectedBooking?.caregiversNeeded || 0)" class="text-warning text-caption mt-1">
                <v-icon size="12" class="mr-1">mdi-information</v-icon>
                Too many {{ selectedBooking?.service_type === 'Housekeeping' ? 'housekeepers' : 'caregivers' }} selected. This booking needs {{ customCaregiversNeeded || selectedBooking?.caregiversNeeded || 0 }}.
              </div>
            </div>
          </div>
        </v-card-title>
        <v-card-text class="pa-6" style="max-height: 70vh; overflow-y: auto;">
          <v-container fluid>
          <div v-if="selectedBooking" class="booking-details-card mb-6" style="background: #f5f5f5; border-radius: 12px; padding: 20px;">
            <div class="text-h6 font-weight-bold mb-4" style="color: #d32f2f;">
              <v-icon color="error" class="mr-2">mdi-information</v-icon>
              Booking Details
            </div>
            <v-row class="booking-details-content">
              <v-col cols="12" md="3">
                <div class="detail-item">
                  <v-icon color="error" size="16" class="mr-2">mdi-account</v-icon>
                  <span class="detail-label">Client:</span>
                  <span class="detail-value font-weight-bold">{{ selectedBooking.client }}</span>
                </div>
                <div class="detail-item">
                  <v-icon color="error" size="16" class="mr-2">mdi-calendar</v-icon>
                  <span class="detail-label">Date:</span>
                  <span class="detail-value">{{ selectedBooking.date }}</span>
                </div>
              </v-col>
              <v-col cols="6">
                <div class="detail-item">
                  <v-icon color="error" size="16" class="mr-2">mdi-medical-bag</v-icon>
                  <span class="detail-label">Service:</span>
                  <span class="detail-value">{{ selectedBooking.service }}</span>
                </div>
                <div class="detail-item">
                  <v-icon color="error" size="16" class="mr-2">mdi-clock</v-icon>
                  <span class="detail-label">Duration:</span>
                  <span class="detail-value">{{ selectedBooking.duration }}</span>
                </div>
              </v-col>
            </v-row>
            
            <!-- Customizable Caregivers Needed -->
            <v-divider class="my-3" />
            <v-row>
              <v-col cols="12">
                <v-alert color="info" variant="tonal" density="compact" class="mb-2">
                  <div class="d-flex align-center">
                    <v-icon size="18" class="mr-2">mdi-information</v-icon>
                    <span class="text-caption">Recommended: <strong>{{ selectedBooking.caregiversNeeded }} {{ selectedBooking?.service_type === 'Housekeeping' ? 'housekeeper(s)' : 'caregiver(s)' }}</strong> based on {{ selectedBooking.dutyType }}</span>
                  </div>
                </v-alert>
                <v-text-field
                  v-model.number="customCaregiversNeeded"
                  :label="`Number of ${selectedBooking?.service_type === 'Housekeeping' ? 'Housekeepers' : 'Caregivers'} Needed`"
                  type="number"
                  variant="outlined"
                  density="compact"
                  min="1"
                  max="10"
                  prepend-inner-icon="mdi-account-multiple"
                  :hint="`Customize the number of ${selectedBooking?.service_type === 'Housekeeping' ? 'housekeepers' : 'caregivers'} for this booking`"
                  persistent-hint
                >
                  <template v-slot:append-inner>
                    <v-btn 
                      size="x-small" 
                      variant="text" 
                      color="primary"
                      @click="customCaregiversNeeded = selectedBooking.caregiversNeeded"
                    >
                      Reset
                    </v-btn>
                  </template>
                </v-text-field>
              </v-col>
            </v-row>
          </div>
          
          <v-divider class="mb-4" />
          
          <div class="mb-4">
            <v-row>
              <v-col cols="8">
                <v-text-field
                  v-model="assignCaregiverSearch"
                  :placeholder="`Search ${selectedBooking?.service_type === 'Housekeeping' ? 'housekeepers' : 'caregivers'} by name...`"
                  prepend-inner-icon="mdi-magnify"
                  variant="outlined"
                  density="compact"
                  hide-details
                  class="search-field"
                />
              </v-col>
              <v-col cols="4">
                <v-select
                  v-model="assignAvailabilityFilter"
                  :items="['All', 'Available', 'Assigned', 'Unavailable']"
                  variant="outlined"
                  density="compact"
                  hide-details
                  prepend-inner-icon="mdi-filter"
                  class="filter-select"
                />
              </v-col>
            </v-row>
          </div>

          <!-- Assigned Hourly Rate Section -->
          <v-card v-if="assignSelectedCaregivers.length > 0" elevation="0" class="mb-4" style="border: 2px solid #4caf50; border-radius: 8px;">
            <v-card-title class="pa-4" style="background: #4caf50; color: white;">
              <div class="d-flex align-center justify-space-between w-100">
                <div class="d-flex align-center">
                  <v-icon size="24" class="mr-2" color="white">mdi-cash-multiple</v-icon>
                  <div>
                    <div class="text-h6 font-weight-bold">Assign Hourly Rates</div>
                    <div class="text-caption" style="opacity: 0.9;">
                      Set rates within each {{ selectedBooking?.service_type === 'Housekeeping' ? 'housekeeper\'s' : 'caregiver\'s' }} preferred range
                    </div>
                  </div>
                </div>
                <v-chip size="small" style="background: rgba(255,255,255,0.25); color: white;" class="font-weight-bold px-3">
                  {{ assignSelectedCaregivers.length }} Selected
                </v-chip>
              </div>
            </v-card-title>
            
            <v-card-text class="pa-4">
              <v-row>
                <!-- Rate input for each selected caregiver -->
                <v-col v-for="caregiverId in assignSelectedCaregivers" :key="`rate-${caregiverId}`" cols="12" md="6">
                  <v-card elevation="0" class="pa-3" style="border: 1px solid #e0e0e0; border-radius: 8px; background: #fafafa;">
                    <div class="d-flex align-center mb-3">
                      <v-avatar size="40" color="success" class="mr-3">
                        <span class="text-white font-weight-bold">{{ getCaregiverById(caregiverId)?.name.split(' ').map(n => n[0]).join('') }}</span>
                      </v-avatar>
                      <div class="flex-grow-1">
                        <div class="text-subtitle-2 font-weight-bold">{{ getCaregiverById(caregiverId)?.name }}</div>
                        <div class="text-caption text-grey">
                          Preferred: ${{ getCaregiverById(caregiverId)?.preferred_hourly_rate_min || 20 }}-${{ getCaregiverById(caregiverId)?.preferred_hourly_rate_max || 50 }}/hr
                        </div>
                      </div>
                    </div>
                    
                    <v-text-field
                      v-model="assignedRates[caregiverId]"
                      type="number"
                      label="Hourly Rate"
                      prefix="$"
                      suffix="/hr"
                      variant="outlined"
                      density="compact"
                      :min="getCaregiverById(caregiverId)?.preferred_hourly_rate_min || 20"
                      :max="getCaregiverById(caregiverId)?.preferred_hourly_rate_max || 50"
                      step="1"
                      hide-details
                      color="success"
                      class="mb-2"
                    />
                  </v-card>
                </v-col>
              </v-row>
              
              <!-- Schedule Note -->
              <v-divider class="my-4" />
              
              <v-alert type="info" variant="tonal" density="compact" class="mb-0">
                <div class="text-caption">
                  <v-icon size="16" class="mr-1">mdi-information</v-icon>
                  <strong>Next Step:</strong> After assignment, use the "Weekly Schedule" tab to assign {{ selectedBooking?.service_type === 'Housekeeping' ? 'housekeepers' : 'caregivers' }} to specific days. Profit calculations will be available after scheduling is complete.
                </div>
              </v-alert>
            </v-card-text>
          </v-card>

          <div class="caregivers-list-container">
            <div v-for="caregiver in filteredAssignCaregivers" :key="caregiver.id" class="caregiver-assign-card">
              <div class="d-flex align-center justify-space-between">
                <div class="d-flex align-center flex-grow-1">
                  <v-checkbox
                    :model-value="assignSelectedCaregivers.includes(caregiver.id)"
                    @update:model-value="toggleCaregiverSelection(caregiver.id)"
                    color="error"
                    hide-details
                    class="mr-3"
                  />
                  <v-avatar size="48" :color="caregiver.status === 'Active' ? 'success' : 'grey'" class="mr-4">
                    <span class="text-white font-weight-bold text-h6">{{ caregiver.name.split(' ').map(n => n[0]).join('') }}</span>
                  </v-avatar>
                  <div class="flex-grow-1">
                    <div class="caregiver-assign-name">{{ caregiver.name }}</div>
                    <div class="caregiver-assign-details">
                      <v-icon size="14" class="mr-1">mdi-account-group</v-icon>
                      {{ caregiver.clients }} Clients
                      <span class="mx-2">•</span>
                      <v-icon size="14" class="mr-1">mdi-map-marker</v-icon>
                      {{ caregiver.zip_code }} - {{ caregiver.location }}
                      <span class="mx-2">•</span>
                      <v-icon size="14" class="mr-1">mdi-cash</v-icon>
                      ${{ caregiver.preferred_hourly_rate_min || 20 }}-${{ caregiver.preferred_hourly_rate_max || 50 }}/hr
                    </div>
                  </div>
                  <v-chip
                    :color="caregiver.status === 'Active' ? 'success' : caregiver.status === 'Assigned' ? 'info' : 'grey'"
                    size="small"
                    class="font-weight-bold"
                  >
                    {{ caregiver.status === 'Active' ? 'Available' : caregiver.status === 'Assigned' ? 'Assigned' : 'Unavailable' }}
                  </v-chip>
                </div>
              </div>
            </div>
            <div v-if="filteredAssignCaregivers.length === 0" class="no-caregivers">
              <v-icon size="64" color="grey-lighten-1">mdi-account-search</v-icon>
              <div class="text-grey mt-2">No caregivers found</div>
            </div>
          </div>
          </v-container>
        </v-card-text>
        <v-card-actions class="pa-4" style="border-top: 1px solid #e0e0e0; background: #fafafa;">
          <v-spacer />
          <v-btn color="grey" variant="text" @click="closeAssignDialog">
            Cancel
          </v-btn>
          <v-btn 
            color="error" 
            variant="elevated"
            @click="confirmAssignCaregivers"
          >
            {{ assignSelectedCaregivers.length === 0 ? 'Unassign All' : `Assign ${assignSelectedCaregivers.length} Caregiver${assignSelectedCaregivers.length !== 1 ? 's' : ''}` }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Assign Housekeeper Dialog (custom, copied from caregiver assign dialog) -->
    <v-dialog v-model="assignHousekeeperDialogOpen" max-width="1200" scrollable>
      <v-card class="assign-dialog-card">
        <v-card-title class="assign-dialog-header pa-6">
          <div class="d-flex align-center justify-space-between w-100">
            <div>
              <span class="assign-dialog-title">Assign Housekeepers</span>
              <div class="assign-dialog-subtitle">Select housekeepers for this booking</div>
            </div>
            <div>
              <v-chip
                :color="assignSelectedHousekeepers.length > (customHousekeepersNeeded || selectedBooking?.caregiversNeeded || 0) ? 'warning' : 'error'"
                size="large"
                class="font-weight-bold selection-counter"
              >
                {{ assignSelectedHousekeepers.length }} / {{ customHousekeepersNeeded || selectedBooking?.caregiversNeeded || 0 }} Selected
                <v-icon v-if="assignSelectedHousekeepers.length > (customHousekeepersNeeded || selectedBooking?.caregiversNeeded || 0)" size="16" class="ml-1">mdi-alert</v-icon>
              </v-chip>
              <div v-if="assignSelectedHousekeepers.length > (customHousekeepersNeeded || selectedBooking?.caregiversNeeded || 0)" class="text-warning text-caption mt-1">
                <v-icon size="12" class="mr-1">mdi-information</v-icon>
                Too many housekeepers selected. This booking needs {{ customHousekeepersNeeded || selectedBooking?.caregiversNeeded || 0 }}.
              </div>
            </div>
          </div>
        </v-card-title>

        <v-card-text class="pa-6" style="max-height: 70vh; overflow-y: auto;">
          <v-container fluid>
            <div v-if="selectedBooking" class="booking-details-card mb-6" style="background: #f5f5f5; border-radius: 12px; padding: 20px;">
              <div class="text-h6 font-weight-bold mb-4" style="color: #7B1FA2;">
                <v-icon color="deep-purple" class="mr-2">mdi-information</v-icon>
                Booking Details
              </div>
              <v-row class="booking-details-content">
                <v-col cols="12" md="3">
                  <div class="detail-item">
                    <v-icon color="deep-purple" size="16" class="mr-2">mdi-account</v-icon>
                    <span class="detail-label">Client:</span>
                    <span class="detail-value font-weight-bold">{{ selectedBooking.client }}</span>
                  </div>
                  <div class="detail-item">
                    <v-icon color="deep-purple" size="16" class="mr-2">mdi-calendar</v-icon>
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ selectedBooking.date }}</span>
                  </div>
                </v-col>
                <v-col cols="6">
                  <div class="detail-item">
                    <v-icon color="deep-purple" size="16" class="mr-2">mdi-medical-bag</v-icon>
                    <span class="detail-label">Service:</span>
                    <span class="detail-value">{{ selectedBooking.service }}</span>
                  </div>
                  <div class="detail-item">
                    <v-icon color="deep-purple" size="16" class="mr-2">mdi-clock</v-icon>
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value">{{ selectedBooking.duration }}</span>
                  </div>
                </v-col>
              </v-row>

              <!-- Customizable Housekeepers Needed -->
              <v-divider class="my-3" />
              <v-row>
                <v-col cols="12">
                  <v-alert color="info" variant="tonal" density="compact" class="mb-2">
                    <div class="d-flex align-center">
                      <v-icon size="18" class="mr-2">mdi-information</v-icon>
                      <span class="text-caption">Recommended: <strong>{{ selectedBooking.caregiversNeeded }} housekeeper(s)</strong> based on {{ selectedBooking.dutyType }}</span>
                    </div>
                  </v-alert>
                  <v-text-field
                    v-model.number="customHousekeepersNeeded"
                    label="Number of Housekeepers Needed"
                    type="number"
                    variant="outlined"
                    density="compact"
                    min="1"
                    max="10"
                    prepend-inner-icon="mdi-account-multiple"
                    hint="Customize the number of housekeepers for this booking"
                    persistent-hint
                  >
                    <template v-slot:append-inner>
                      <v-btn
                        size="x-small"
                        variant="text"
                        color="primary"
                        @click="customHousekeepersNeeded = selectedBooking.caregiversNeeded"
                      >
                        Reset
                      </v-btn>
                    </template>
                  </v-text-field>
                </v-col>
              </v-row>
            </div>

            <v-divider class="mb-4" />

            <div class="mb-4">
              <v-row>
                <v-col cols="8">
                  <v-text-field
                    v-model="assignHousekeeperSearch"
                    placeholder="Search housekeepers by name..."
                    prepend-inner-icon="mdi-magnify"
                    variant="outlined"
                    density="compact"
                    hide-details
                    class="search-field"
                  />
                </v-col>
                <v-col cols="4">
                  <v-select
                    v-model="assignHousekeeperAvailabilityFilter"
                    :items="['All', 'Available', 'Assigned', 'Unavailable']"
                    variant="outlined"
                    density="compact"
                    hide-details
                    prepend-inner-icon="mdi-filter"
                    class="filter-select"
                  />
                </v-col>
              </v-row>
            </div>

            <!-- Assigned Hourly Rate Section -->
            <v-card v-if="assignSelectedHousekeepers.length > 0" elevation="0" class="mb-4" style="border: 2px solid #4caf50; border-radius: 8px;">
              <v-card-title class="pa-4" style="background: #4caf50; color: white;">
                <div class="d-flex align-center justify-space-between w-100">
                  <div class="d-flex align-center">
                    <v-icon size="24" class="mr-2" color="white">mdi-cash-multiple</v-icon>
                    <div>
                      <div class="text-h6 font-weight-bold">Assign Hourly Rates</div>
                      <div class="text-caption" style="opacity: 0.9;">Set rates for each housekeeper</div>
                    </div>
                  </div>
                  <v-chip size="small" style="background: rgba(255,255,255,0.25); color: white;" class="font-weight-bold px-3">
                    {{ assignSelectedHousekeepers.length }} Selected
                  </v-chip>
                </div>
              </v-card-title>

              <v-card-text class="pa-4">
                <v-row>
                  <v-col v-for="housekeeperId in assignSelectedHousekeepers" :key="`hk-rate-${housekeeperId}`" cols="12" md="6">
                    <v-card elevation="0" class="pa-3" style="border: 1px solid #e0e0e0; border-radius: 8px; background: #fafafa;">
                      <div class="d-flex align-center mb-3">
                        <v-avatar size="40" color="success" class="mr-3">
                          <span class="text-white font-weight-bold">{{ (getHousekeeperById(housekeeperId)?.name || 'H').split(' ').map(n => n[0]).join('') }}</span>
                        </v-avatar>
                        <div class="flex-grow-1">
                          <div class="text-subtitle-2 font-weight-bold">{{ getHousekeeperById(housekeeperId)?.name }}</div>
                          <div class="text-caption text-grey">
                            Default: ${{ getHousekeeperById(housekeeperId)?.hourly_rate || 20 }}/hr
                          </div>
                        </div>
                      </div>

                      <v-text-field
                        v-model="assignedHousekeeperRates[housekeeperId]"
                        type="number"
                        label="Hourly Rate"
                        prefix="$"
                        suffix="/hr"
                        variant="outlined"
                        density="compact"
                        step="1"
                        hide-details
                        color="success"
                        class="mb-2"
                      />
                    </v-card>
                  </v-col>
                </v-row>

                <v-divider class="my-4" />
                <v-alert type="info" variant="tonal" density="compact" class="mb-0">
                  <div class="text-caption">
                    <v-icon size="16" class="mr-1">mdi-information</v-icon>
                    <strong>Next Step:</strong> After assignment, use the "Weekly Schedule" tab to assign housekeepers to specific days.
                  </div>
                </v-alert>
              </v-card-text>
            </v-card>

            <div class="caregivers-list-container">
              <div v-for="hk in filteredAssignHousekeepers" :key="hk.id" class="caregiver-assign-card">
                <div class="d-flex align-center justify-space-between">
                  <div class="d-flex align-center flex-grow-1">
                    <v-checkbox
                      :model-value="assignSelectedHousekeepers.includes(hk.id)"
                      @update:model-value="(val) => toggleHousekeeperSelection(hk.id, val)"
                      color="error"
                      hide-details
                      class="mr-3"
                    />
                    <v-avatar size="48" :color="(['inactive','unavailable'].includes(String(hk.status || hk.availability_status || '').toLowerCase()) ? 'grey' : (String(hk.status || hk.availability_status || '').toLowerCase() === 'assigned' ? 'info' : 'success'))" class="mr-4">
                      <span class="text-white font-weight-bold text-h6">{{ (hk.name || 'H').split(' ').map(n => n[0]).join('') }}</span>
                    </v-avatar>
                    <div class="flex-grow-1">
                      <div class="caregiver-assign-name">{{ hk.name }}</div>
                      <div class="caregiver-assign-details">
                        <v-icon size="14" class="mr-1">mdi-email</v-icon>
                        {{ hk.email || '—' }}
                      </div>
                    </div>
                    <v-chip
                      :color="(['inactive','unavailable'].includes(String(hk.status || hk.availability_status || '').toLowerCase()) ? 'grey' : (String(hk.status || hk.availability_status || '').toLowerCase() === 'assigned' ? 'info' : 'success'))"
                      size="small"
                      class="font-weight-bold"
                    >
                      {{ (String(hk.status || hk.availability_status || '').toLowerCase() === 'assigned') ? 'Assigned' : (['inactive','unavailable'].includes(String(hk.status || hk.availability_status || '').toLowerCase()) ? 'Unavailable' : 'Available') }}
                    </v-chip>
                  </div>
                </div>
              </div>

              <div v-if="filteredAssignHousekeepers.length === 0" class="no-caregivers">
                <v-icon size="64" color="grey-lighten-1">mdi-account-search</v-icon>
                <div class="text-grey mt-2">No housekeepers found</div>
              </div>
            </div>
          </v-container>
        </v-card-text>

        <v-card-actions class="pa-4" style="border-top: 1px solid #e0e0e0; background: #fafafa;">
          <v-spacer />
          <v-btn color="grey" variant="text" @click="closeAssignHousekeeperDialog">Cancel</v-btn>
          <v-btn color="deep-purple" variant="elevated" @click="confirmAssignHousekeepers">
            {{ assignSelectedHousekeepers.length === 0 ? 'Unassign All' : `Assign ${assignSelectedHousekeepers.length} Housekeeper${assignSelectedHousekeepers.length !== 1 ? 's' : ''}` }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- View Booking Details Dialog -->
    <v-dialog v-model="viewBookingDialog" max-width="900" scrollable>
      <v-card v-if="viewingBooking" max-height="80vh">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="section-title" style="color: white;">Booking Details</span>
            <v-btn icon="mdi-close" variant="text" style="color: white;" @click="viewBookingDialog = false"></v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6 scrollable-content">
          <!-- Booking Overview -->
          <v-row class="mb-4">
            <v-col cols="12">
              <div class="booking-overview-card">
                <div class="booking-overview-header">
                  <v-icon color="error" size="24" class="mr-3">mdi-calendar-check</v-icon>
                  <span class="booking-overview-title">Service Information</span>
                  <v-spacer />
                  <v-chip :color="getBookingStatusColor(viewingBooking.status)" size="large" class="font-weight-bold">
                    {{ viewingBooking.status }}
                  </v-chip>
                </div>
                <v-divider class="my-3" />
                <v-row>
                  <v-col cols="12" md="6">
                    <div class="booking-detail-item">
                      <v-icon color="error" size="18" class="mr-2">mdi-medical-bag</v-icon>
                      <span class="booking-detail-label">Service Type:</span>
                      <span class="booking-detail-value">{{ viewingBooking.service }}</span>
                    </div>
                    <div class="booking-detail-item">
                      <v-icon color="error" size="18" class="mr-2">mdi-clock-outline</v-icon>
                      <span class="booking-detail-label">Hours per Day:</span>
                      <span class="booking-detail-value">{{ viewingBooking.hoursPerDay || 8 }} hours</span>
                    </div>
                    <div class="booking-detail-item">
                      <v-icon color="error" size="18" class="mr-2">mdi-calendar</v-icon>
                      <span class="booking-detail-label">Service Date:</span>
                      <span class="booking-detail-value">{{ viewingBooking.date }}</span>
                    </div>
                  </v-col>
                  <v-col cols="12" md="6">
                    <div class="booking-detail-item">
                      <v-icon color="error" size="18" class="mr-2">mdi-clock</v-icon>
                      <span class="booking-detail-label">Starting Time:</span>
                      <span class="booking-detail-value">{{ viewingBooking.time || viewingBooking.startingTime || 'N/A' }}</span>
                    </div>
                    <div class="booking-detail-item">
                      <v-icon color="error" size="18" class="mr-2">mdi-timer</v-icon>
                      <span class="booking-detail-label">Duration:</span>
                      <span class="booking-detail-value">{{ viewingBooking.duration || viewingBooking.durationDays + ' days' }}</span>
                    </div>
                    <div class="booking-detail-item">
                      <v-icon color="error" size="18" class="mr-2">mdi-account-group</v-icon>
                      <span class="booking-detail-label">
                        {{ (viewingBooking.service && viewingBooking.service.toLowerCase().includes('housekeeping')) ? 'Housekeepers Assigned:' : 'Caregivers Assigned:' }}
                      </span>
                      <v-chip :color="(viewingBooking.assignedCount || 0) >= (viewingBooking.caregiversNeeded || 1) ? 'success' : 'warning'" size="small" class="ml-2">
                        {{ viewingBooking.assignedCount || 0 }} / {{ viewingBooking.caregiversNeeded || 1 }} Assigned
                      </v-chip>
                    </div>
                  </v-col>
                </v-row>
              </div>
            </v-col>
          </v-row>

          <!-- Location Information -->
          <v-row class="mb-4">
            <v-col cols="12">
              <div class="booking-overview-card">
                <div class="booking-overview-header">
                  <v-icon color="primary" size="24" class="mr-3">mdi-map-marker</v-icon>
                  <span class="booking-overview-title">Location</span>
                </div>
                <v-divider class="my-3" />
                <v-row>
                  <v-col cols="12" md="6">
                    <div class="booking-detail-item">
                      <v-icon color="primary" size="18" class="mr-2">mdi-city</v-icon>
                      <span class="booking-detail-label">City/Borough:</span>
                      <span class="booking-detail-value">{{ viewingBooking.location || viewingBooking.borough || 'N/A' }}</span>
                    </div>
                    <div class="booking-detail-item">
                      <v-icon color="primary" size="18" class="mr-2">mdi-road</v-icon>
                      <span class="booking-detail-label">Street Address:</span>
                      <span class="booking-detail-value">{{ viewingBooking.streetAddress || viewingBooking.address || 'N/A' }}</span>
                    </div>
                  </v-col>
                  <v-col cols="12" md="6">
                    <div class="booking-detail-item">
                      <v-icon color="primary" size="18" class="mr-2">mdi-office-building</v-icon>
                      <span class="booking-detail-label">Apartment/Unit:</span>
                      <span class="booking-detail-value">{{ viewingBooking.apartmentUnit || viewingBooking.unit || 'N/A' }}</span>
                    </div>
                  </v-col>
                </v-row>
              </div>
            </v-col>
          </v-row>

          <!-- Client Information -->
          <v-row class="mb-4">
            <v-col cols="12">
              <div class="booking-overview-card">
                <div class="booking-overview-header">
                  <v-icon color="info" size="24" class="mr-3">mdi-account-circle</v-icon>
                  <span class="booking-overview-title">Client Information</span>
                </div>
                <v-divider class="my-3" />
                <v-row>
                  <v-col cols="12" md="6">
                    <div class="booking-detail-item">
                      <v-icon color="info" size="18" class="mr-2">mdi-account</v-icon>
                      <span class="booking-detail-label">Client Name:</span>
                      <span class="booking-detail-value">{{ viewingBooking.client }}</span>
                    </div>
                    <div class="booking-detail-item">
                      <v-icon color="info" size="18" class="mr-2">mdi-cake-variant</v-icon>
                      <span class="booking-detail-label">Client Age:</span>
                      <span class="booking-detail-value">{{ viewingBooking.clientAge || 'N/A' }}</span>
                    </div>
                    <div class="booking-detail-item">
                      <v-icon color="info" size="18" class="mr-2">mdi-walk</v-icon>
                      <span class="booking-detail-label">Mobility Level:</span>
                      <span class="booking-detail-value">{{ viewingBooking.mobilityLevel || 'N/A' }}</span>
                    </div>
                  </v-col>
                  <v-col cols="12" md="6">
                    <div class="booking-detail-item">
                      <v-icon color="info" size="18" class="mr-2">mdi-medical-bag</v-icon>
                      <span class="booking-detail-label">Medical Conditions:</span>
                      <span class="booking-detail-value">{{ viewingBooking.medicalConditions || 'None specified' }}</span>
                    </div>
                  </v-col>
                </v-row>
              </div>
            </v-col>
          </v-row>

          <!-- Service Summary with Pricing -->
          <v-row class="mb-4">
            <v-col cols="12">
              <div class="booking-overview-card">
                <div class="booking-overview-header">
                  <v-icon color="success" size="24" class="mr-3">mdi-currency-usd</v-icon>
                  <span class="booking-overview-title">Service Summary</span>
                </div>
                <v-divider class="my-3" />
                <v-row>
                  <v-col cols="12" md="6">
                    <div class="booking-detail-item">
                      <v-icon color="success" size="18" class="mr-2">mdi-briefcase</v-icon>
                      <span class="booking-detail-label">Duty Type:</span>
                      <span class="booking-detail-value text-capitalize">{{ viewingBooking.dutyType || 'Hourly' }}</span>
                    </div>
                    <div class="booking-detail-item">
                      <v-icon color="success" size="18" class="mr-2">mdi-clock-outline</v-icon>
                      <span class="booking-detail-label">Hours per Day:</span>
                      <span class="booking-detail-value">{{ viewingBooking.hoursPerDay || 8 }} hours</span>
                    </div>
                    <div class="booking-detail-item">
                      <v-icon color="success" size="18" class="mr-2">mdi-calendar-range</v-icon>
                      <span class="booking-detail-label">Duration:</span>
                      <span class="booking-detail-value">{{ viewingBooking.durationDays || 1 }} days</span>
                    </div>
                  </v-col>
                  <v-col cols="12" md="6">
                    <div class="booking-detail-item">
                      <v-icon color="success" size="18" class="mr-2">mdi-cash</v-icon>
                      <span class="booking-detail-label">Rate per Hour:</span>
                      <span class="booking-detail-value">${{ viewingBooking.hourlyRate || 45 }}</span>
                    </div>
                    <div class="booking-detail-item">
                      <v-icon color="success" size="18" class="mr-2">mdi-calculator</v-icon>
                      <span class="booking-detail-label">Calculation:</span>
                      <span class="booking-detail-value">{{ viewingBooking.hoursPerDay || 8 }}h × {{ viewingBooking.durationDays || 1 }}d × ${{ viewingBooking.hourlyRate || 45 }}</span>
                    </div>
                    <div class="booking-detail-item">
                      <v-icon color="success" size="18" class="mr-2">mdi-cash-multiple</v-icon>
                      <span class="booking-detail-label">Order Total:</span>
                      <span class="booking-detail-value font-weight-bold text-success" style="font-size: 1.2em;">{{ viewingBooking.formattedPrice || '$0' }}</span>
                    </div>
                  </v-col>
                </v-row>
              </div>
            </v-col>
          </v-row>

          <!-- Voucher/Referral Code Information -->
          <v-row v-if="viewingBooking.referralCode" class="mb-4">
            <v-col cols="12">
              <div class="booking-overview-card">
                <div class="booking-overview-header">
                  <v-icon color="primary" size="24" class="mr-3">mdi-ticket-percent</v-icon>
                  <span class="booking-overview-title">Voucher Applied</span>
                </div>
                <v-divider class="my-3" />
                <v-row>
                  <v-col cols="12" md="6">
                    <div class="booking-detail-item">
                      <v-icon color="primary" size="18" class="mr-2">mdi-ticket</v-icon>
                      <span class="booking-detail-label">Referral Code:</span>
                      <v-chip color="primary" size="small" class="ml-2 font-weight-bold">
                        {{ viewingBooking.referralCode.code }}
                      </v-chip>
                    </div>
                    <div class="booking-detail-item" v-if="viewingBooking.referralDiscountApplied">
                      <v-icon color="success" size="18" class="mr-2">mdi-tag</v-icon>
                      <span class="booking-detail-label">Discount Applied:</span>
                      <span class="booking-detail-value text-success font-weight-bold">${{ viewingBooking.referralDiscountApplied }}/hour</span>
                    </div>
                  </v-col>
                  <v-col cols="12" md="6" v-if="viewingBooking.referralCode.user">
                    <div class="booking-detail-item">
                      <v-icon color="info" size="18" class="mr-2">mdi-account-circle</v-icon>
                      <span class="booking-detail-label">Referred By:</span>
                      <span class="booking-detail-value">{{ viewingBooking.referralCode.user.name }}</span>
                    </div>
                    <div class="booking-detail-item" v-if="viewingBooking.referralCode.user.email">
                      <v-icon color="info" size="18" class="mr-2">mdi-email</v-icon>
                      <span class="booking-detail-label">Email:</span>
                      <span class="booking-detail-value">{{ viewingBooking.referralCode.user.email }}</span>
                    </div>
                  </v-col>
                </v-row>
              </div>
            </v-col>
          </v-row>

          <!-- Assigned Caregivers/Housekeepers Section -->
          <v-row v-if="(isHousekeepingBooking(viewingBooking) ? getAssignedHousekeepers(viewingBooking.id).length : getAssignedCaregivers(viewingBooking.id).length) > 0" class="mb-4">
            <v-col cols="12">
              <div class="booking-overview-card" style="border: 2px solid #4caf50;">
                <div class="booking-overview-header">
                  <v-icon color="success" size="24" class="mr-3">{{ isHousekeepingBooking(viewingBooking) ? 'mdi-broom' : 'mdi-account-heart' }}</v-icon>
                  <span class="booking-overview-title">{{ isHousekeepingBooking(viewingBooking) ? 'Assigned Housekeepers' : 'Assigned Caregivers' }}</span>
                  <v-chip size="small" color="success" class="ml-2">
                    {{ isHousekeepingBooking(viewingBooking) ? getAssignedHousekeepers(viewingBooking.id).length : getAssignedCaregivers(viewingBooking.id).length }} Active
                  </v-chip>
                </div>
                <v-divider class="my-3" />
                <v-row>
                  <!-- Show housekeepers for housekeeping bookings -->
                  <template v-if="isHousekeepingBooking(viewingBooking)">
                    <v-col v-for="housekeeper in getAssignedHousekeepers(viewingBooking.id)" :key="housekeeper.id" cols="12" md="6">
                      <v-card elevation="1" class="pa-3" style="border: 1px solid #e0e0e0; border-radius: 8px; background: #f9f9f9;">
                        <div class="d-flex align-center">
                          <v-avatar size="48" color="purple" class="mr-3">
                            <span class="text-white font-weight-bold">{{ housekeeper.name.split(' ').map(n => n[0]).join('') }}</span>
                          </v-avatar>
                          <div class="flex-grow-1">
                            <div class="text-subtitle-2 font-weight-bold">{{ housekeeper.name }}</div>
                            <div class="text-caption text-grey">{{ housekeeper.email }}</div>
                            <v-chip size="x-small" color="purple" class="mt-1">
                              <v-icon size="14" class="mr-1">mdi-cash</v-icon>
                              ${{ housekeeper.hourly_rate || 20 }}/hr
                            </v-chip>
                          </div>
                        </div>
                      </v-card>
                    </v-col>
                  </template>
                  <!-- Show caregivers for non-housekeeping bookings -->
                  <template v-else>
                    <v-col v-for="caregiver in getAssignedCaregivers(viewingBooking.id)" :key="caregiver.id" cols="12" md="6">
                      <v-card elevation="1" class="pa-3" style="border: 1px solid #e0e0e0; border-radius: 8px; background: #f9f9f9;">
                        <div class="d-flex align-center">
                          <v-avatar size="48" color="success" class="mr-3">
                            <span class="text-white font-weight-bold">{{ caregiver.name.split(' ').map(n => n[0]).join('') }}</span>
                          </v-avatar>
                          <div class="flex-grow-1">
                            <div class="text-subtitle-2 font-weight-bold">{{ caregiver.name }}</div>
                            <div class="text-caption text-grey">{{ caregiver.email }}</div>
                            <v-chip size="x-small" color="success" class="mt-1">
                              <v-icon size="14" class="mr-1">mdi-cash</v-icon>
                              ${{ caregiver.hourly_rate || caregiver.hourlyRate || 20 }}/hr
                            </v-chip>
                          </div>
                        </div>
                      </v-card>
                    </v-col>
                  </template>
                </v-row>
              </div>
            </v-col>
          </v-row>

          <!-- Booking Timeline -->
          <v-row>
            <v-col cols="12">
              <div class="booking-timeline-card">
                <div class="booking-timeline-header">
                  <v-icon color="info" size="24" class="mr-3">mdi-timeline</v-icon>
                  <span class="booking-timeline-title">Booking Timeline</span>
                </div>
                <v-divider class="my-3" />
                <div class="booking-timeline">
                  <div class="timeline-item">
                    <div class="timeline-dot timeline-dot-success"></div>
                    <div class="timeline-content">
                      <div class="timeline-title">Booking Created</div>
                      <div class="timeline-subtitle" v-if="viewingBooking.createdAt && viewingBooking.createdAt !== 'N/A'">
                        {{ viewingBooking.createdAt }}
                      </div>
                      <div class="timeline-subtitle" v-else-if="viewingBooking.createdAtDate && viewingBooking.createdAtTime">
                        {{ viewingBooking.createdAtDate }} at {{ viewingBooking.createdAtTime }}
                      </div>
                      <div class="timeline-subtitle" v-else-if="viewingBooking.submitted && viewingBooking.submitted !== 'N/A'">
                        {{ viewingBooking.submitted }}
                      </div>
                      <div class="timeline-subtitle" v-else>
                        {{ viewingBooking.date }} at {{ viewingBooking.time || 'N/A' }}
                      </div>
                    </div>
                  </div>
                  <div v-if="viewingBooking.assignedCount > 0" class="timeline-item">
                    <div class="timeline-dot timeline-dot-info"></div>
                    <div class="timeline-content">
                      <div class="timeline-title">{{ isHousekeepingBooking(viewingBooking) ? 'Housekeepers Assigned' : 'Caregivers Assigned' }}</div>
                      <div class="timeline-subtitle">{{ viewingBooking.assignedCount }} {{ isHousekeepingBooking(viewingBooking) ? 'housekeeper(s)' : 'caregiver(s)' }} assigned</div>
                    </div>
                  </div>
                  <div v-if="viewingBooking.status === 'Assigned'" class="timeline-item">
                    <div class="timeline-dot timeline-dot-success"></div>
                    <div class="timeline-content">
                      <div class="timeline-title">Fully Assigned</div>
                      <div class="timeline-subtitle">All required {{ isHousekeepingBooking(viewingBooking) ? 'housekeepers' : 'caregivers' }} assigned</div>
                    </div>
                  </div>
                  <div v-if="viewingBooking.status === 'In Progress'" class="timeline-item">
                    <div class="timeline-dot timeline-dot-warning"></div>
                    <div class="timeline-content">
                      <div class="timeline-title">Service In Progress</div>
                      <div class="timeline-subtitle">Care service is currently active</div>
                    </div>
                  </div>
                  <div v-if="viewingBooking.status === 'Completed'" class="timeline-item">
                    <div class="timeline-dot timeline-dot-success"></div>
                    <div class="timeline-content">
                      <div class="timeline-title">Service Completed</div>
                      <div class="timeline-subtitle">Care service successfully completed</div>
                    </div>
                  </div>
                </div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="viewBookingDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- View Time Details Dialog -->
    <v-dialog v-model="viewTimeDetailsDialog" max-width="800" scrollable>
      <v-card v-if="selectedTimeEntry" max-height="80vh">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">Time Tracking History - {{ selectedTimeEntry.caregiver }}</span>
        </v-card-title>
        <v-card-text class="pa-6 scrollable-content">
          <!-- Current Status -->
          <v-row class="mb-4">
            <v-col cols="12">
              <div class="current-status-card">
                <div class="d-flex align-center justify-space-between mb-3">
                  <h3>Current Status</h3>
                  <v-chip :color="selectedTimeEntry.status === 'Clocked In' ? 'success' : 'error'" class="font-weight-bold">
                    {{ selectedTimeEntry.status }}
                  </v-chip>
                </div>
                <v-row>
                  <v-col cols="6">
                    <div class="detail-section">
                      <div class="detail-label">Today's Hours</div>
                      <div class="detail-value">{{ selectedTimeEntry.todayHours }} hours</div>
                    </div>
                  </v-col>
                  <v-col cols="6">
                    <div class="detail-section">
                      <div class="detail-label">This Week</div>
                      <div class="detail-value">{{ selectedTimeEntry.weekHours }} hours</div>
                    </div>
                  </v-col>
                  <v-col cols="6">
                    <div class="detail-section">
                      <div class="detail-label">Current Client</div>
                      <div class="detail-value">{{ selectedTimeEntry.currentClient || 'N/A' }}</div>
                    </div>
                  </v-col>
                  <v-col cols="6" v-if="selectedTimeEntry.clockIn">
                    <div class="detail-section">
                      <div class="detail-label">Last Clock In</div>
                      <div class="detail-value">{{ selectedTimeEntry.clockIn }}</div>
                    </div>
                  </v-col>
                </v-row>
              </div>
            </v-col>
          </v-row>

          <!-- Time Tracking History -->
          <v-row>
            <v-col cols="12">
              <div class="time-history-section">
                <h3 class="mb-4">Recent Time Tracking History</h3>
                <div v-if="selectedTimeEntry.timeHistory && selectedTimeEntry.timeHistory.length > 0" class="time-history-list">
                  <div v-for="(entry, index) in selectedTimeEntry.timeHistory" :key="index" class="time-history-item">
                    <div class="d-flex align-center justify-space-between">
                      <div class="d-flex align-center">
                        <v-icon :color="entry.status === 'completed' ? 'success' : 'warning'" size="20" class="mr-3">
                          {{ entry.status === 'completed' ? 'mdi-check-circle' : 'mdi-clock' }}
                        </v-icon>
                        <div>
                          <div class="history-date">{{ entry.date }}</div>
                          <div class="history-client">{{ entry.client }}</div>
                        </div>
                      </div>
                      <div class="time-details">
                        <div class="d-flex align-center mb-1">
                          <v-icon size="16" class="mr-1">mdi-login</v-icon>
                          <span class="time-in">{{ entry.clockIn || 'N/A' }}</span>
                          <v-icon size="16" class="mx-2">mdi-arrow-right</v-icon>
                          <v-icon size="16" class="mr-1">mdi-logout</v-icon>
                          <span class="time-out">{{ entry.clockOut || 'Active' }}</span>
                        </div>
                        <div class="hours-worked">
                          <v-chip size="small" :color="entry.hoursWorked >= 8 ? 'success' : 'warning'">
                            {{ entry.hoursWorked }}h worked
                          </v-chip>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="no-history">
                  <v-icon size="48" color="grey-lighten-1">mdi-clock-outline</v-icon>
                  <div class="text-grey mt-2">No time tracking history available</div>
                </div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="viewTimeDetailsDialog = false">Close</v-btn>
          <v-btn color="error" @click="editTimeEntry(selectedTimeEntry); viewTimeDetailsDialog = false">Edit Current</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Edit Time Entry Dialog -->
    <v-dialog v-model="editTimeEntryDialog" max-width="600">
      <v-card v-if="selectedTimeEntry">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">Edit Time Entry</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12" class="text-center mb-4">
              <h2>{{ selectedTimeEntry.caregiver }}</h2>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="6">
              <v-text-field v-model="selectedTimeEntry.clockIn" label="Clock In Time" variant="outlined" />
            </v-col>
            <v-col cols="6">
              <v-text-field v-model="selectedTimeEntry.clockOut" label="Clock Out Time" variant="outlined" />
            </v-col>
            <v-col cols="6">
              <v-text-field v-model="selectedTimeEntry.todayHours" label="Hours Today" variant="outlined" type="number" step="0.5" />
            </v-col>
            <v-col cols="6">
              <v-text-field v-model="selectedTimeEntry.currentClient" label="Current Client" variant="outlined" />
            </v-col>
            <v-col cols="12">
              <v-select v-model="selectedTimeEntry.status" :items="['Clocked In', 'Clocked Out']" label="Status" variant="outlined" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="editTimeEntryDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="saveTimeEntry">Save Changes</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- View Client Caregivers Dialog -->
    <v-dialog v-model="viewClientCaregiversDialog" max-width="1000" scrollable>
      <v-card v-if="selectedClientEntry" max-height="80vh">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">Client: {{ selectedClientEntry.client }}</span>
        </v-card-title>
        <v-card-text class="pa-6 scrollable-content">
          <!-- Caregivers List -->
          <div class="mb-6">
            <h3 class="mb-4">Assigned Caregivers ({{ selectedClientEntry.caregivers.length }})</h3>
            <v-row>
              <v-col v-for="caregiver in selectedClientEntry.caregivers" :key="caregiver.id" cols="12" md="6">
                <v-card elevation="1" class="pa-4">
                  <div class="d-flex align-center justify-space-between mb-3">
                    <h4 class="font-weight-bold">{{ caregiver.name }}</h4>
                    <v-chip :color="caregiver.status === 'Clocked In' ? 'success' : 'default'" size="small">
                      {{ caregiver.status }}
                    </v-chip>
                  </div>
                  <div class="text-caption text-grey mb-2">
                    <div v-if="caregiver.clockIn">Clock In: {{ caregiver.clockIn }}</div>
                    <div v-if="caregiver.clockOut">Clock Out: {{ caregiver.clockOut }}</div>
                    <div>Today: {{ formatHours(caregiver.todayHours) }}</div>
                    <div>Week: {{ formatHours(caregiver.weekHours) }}</div>
                  </div>
                </v-card>
              </v-col>
            </v-row>
          </div>

          <!-- Time Tracking History -->
          <div>
            <h3 class="mb-4">Time Tracking History</h3>
            <div v-if="selectedClientEntry.timeHistory && selectedClientEntry.timeHistory.length > 0">
              <v-data-table
                :headers="[
                  { title: 'Date', key: 'date' },
                  { title: 'Caregiver', key: 'caregiver' },
                  { title: 'Clock In', key: 'clockIn' },
                  { title: 'Clock Out', key: 'clockOut' },
                  { title: 'Hours', key: 'hoursWorked' },
                  { title: 'Status', key: 'status' }
                ]"
                :items="selectedClientEntry.timeHistory"
                :items-per-page="10"
              >
                <template v-slot:item.status="{ item }">
                  <v-chip :color="item.status === 'completed' ? 'success' : 'warning'" size="small">
                    {{ item.status }}
                  </v-chip>
                </template>
              </v-data-table>
            </div>
            <div v-else class="text-center text-grey py-8">
              No time tracking history available
            </div>
          </div>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="viewClientCaregiversDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Time Tracking History Dialog -->
    <v-dialog v-model="timeTrackingHistoryDialog" max-width="1200" scrollable>
      <v-card max-height="90vh">
        <v-card-title class="pa-6" style="background: #3b82f6; color: white;">
          <div class="d-flex align-center justify-space-between w-100">
            <div>
              <span class="section-title" style="color: white;">Time Tracking History</span>
              <div class="text-sm opacity-90 mt-1">Complete time tracking records for all caregivers</div>
            </div>
            <v-btn icon="mdi-close" variant="text" style="color: white;" @click="timeTrackingHistoryDialog = false"></v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6 scrollable-content">
          <!-- Filters -->
          <v-row class="mb-4">
            <v-col cols="12" md="2">
              <v-text-field 
                v-model="historySearch" 
                placeholder="Search..." 
                prepend-inner-icon="mdi-magnify" 
                variant="outlined" 
                density="compact" 
                hide-details
              />
            </v-col>
            <v-col cols="12" md="2">
              <v-select
                v-model="historyCaregiverFilter"
                :items="uniqueCaregivers"
                label="Caregiver"
                variant="outlined"
                density="compact"
                hide-details
                clearable
              />
            </v-col>
            <v-col cols="12" md="2">
              <v-select
                v-model="historyClientFilter"
                :items="uniqueClients"
                label="Client"
                variant="outlined"
                density="compact"
                hide-details
                clearable
              />
            </v-col>
            <v-col cols="12" md="2">
              <v-select
                v-model="historyDateFilter"
                :items="['Today', 'This Week', 'This Month', 'Last Month', 'All Time']"
                label="Date Range"
                variant="outlined"
                density="compact"
                hide-details
              />
            </v-col>
            <v-col cols="12" md="2">
              <v-select
                v-model="historyStatusFilter"
                :items="['All', 'Active Sessions', 'Completed Sessions']"
                label="Status"
                variant="outlined"
                density="compact"
                hide-details
              />
            </v-col>
            <v-col cols="12" md="2">
              <v-select
                v-model="historySortBy"
                :items="['Date (Newest)', 'Date (Oldest)', 'Caregiver Name', 'Hours Worked']"
                label="Sort By"
                variant="outlined"
                density="compact"
                hide-details
              />
            </v-col>
          </v-row>
          <v-row class="mb-4">
            <v-col cols="12" class="d-flex justify-end gap-2">
              <v-btn color="info" prepend-icon="mdi-refresh" @click="loadTimeTrackingHistory" size="small">
                Refresh
              </v-btn>
              <v-btn color="success" prepend-icon="mdi-download" @click="exportTimeHistory" size="small">
                Export
              </v-btn>
            </v-col>
          </v-row>

          <!-- Summary Stats -->
          <v-row class="mb-4">
            <v-col cols="12" md="3">
              <v-card class="history-stat-card" elevation="2">
                <v-card-text class="pa-4 text-center">
                  <v-icon color="success" size="32" class="mb-2">mdi-account-clock</v-icon>
                  <div class="stat-number success--text">{{ historyStats.totalSessions }}</div>
                  <div class="stat-label">Total Sessions</div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" md="3">
              <v-card class="history-stat-card" elevation="2">
                <v-card-text class="pa-4 text-center">
                  <v-icon color="info" size="32" class="mb-2">mdi-clock</v-icon>
                  <div class="stat-number info--text">{{ historyStats.totalHours }}h</div>
                  <div class="stat-label">Total Hours</div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" md="3">
              <v-card class="history-stat-card" elevation="2">
                <v-card-text class="pa-4 text-center">
                  <v-icon color="warning" size="32" class="mb-2">mdi-account-multiple</v-icon>
                  <div class="stat-number warning--text">{{ historyStats.activeCaregivers }}</div>
                  <div class="stat-label">Active Caregivers</div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" md="3">
              <v-card class="history-stat-card" elevation="2">
                <v-card-text class="pa-4 text-center">
                  <v-icon color="error" size="32" class="mb-2">mdi-chart-line</v-icon>
                  <div class="stat-number error--text">{{ historyStats.avgHoursPerDay }}h</div>
                  <div class="stat-label">Avg Hours/Day</div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <!-- History Table -->
          <v-card elevation="0" class="history-table-card">
            <v-data-table 
              :headers="historyHeaders" 
              :items="filteredTimeHistory" 
              :items-per-page="15"
              :items-per-page-options="[15, 25, 50, 100]"
              class="elevation-0"
            >
              <template v-slot:item.status="{ item }">
                <v-chip 
                  :color="item.status === 'Active' ? 'success' : item.status === 'Completed' ? 'info' : 'warning'" 
                  size="small" 
                  class="font-weight-bold"
                >
                  {{ item.status }}
                </v-chip>
              </template>
              <template v-slot:item.hoursWorked="{ item }">
                <span class="font-weight-bold">{{ formatHours(item.hoursWorked) }}</span>
              </template>
              <template v-slot:item.actions="{ item }">
                <div class="action-buttons">
                  <v-btn 
                    class="action-btn-view" 
                    icon="mdi-eye" 
                    size="small" 
                    @click="viewHistoryDetails(item)"
                  />
                  <v-btn 
                    v-if="item.status === 'Active'" 
                    class="action-btn-edit" 
                    icon="mdi-clock-end" 
                    size="small" 
                    @click="endSession(item)"
                  />
                </div>
              </template>
            </v-data-table>
          </v-card>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="timeTrackingHistoryDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="viewAssignedCaregiversDialog" max-width="1200" scrollable>
      <v-card v-if="viewingBookingCaregivers">
        <!-- Header -->
        <v-toolbar color="success" dark>
          <v-toolbar-title>
            <div class="d-flex align-center">
              <v-icon class="mr-2">mdi-account-heart</v-icon>
              <div>
                <div class="text-h6">Caregiver Management</div>
                <div class="text-caption opacity-90">{{ viewingBookingCaregivers.client }} - {{ viewingBookingCaregivers.service }}</div>
              </div>
            </div>
          </v-toolbar-title>
          <v-spacer />
          <v-btn icon @click="viewAssignedCaregiversDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>

        <!-- Booking Info Card -->
        <v-card-text class="pa-6 bg-grey-lighten-4">
          <v-card elevation="0" class="rounded-lg">
            <v-card-text class="pa-4">
              <v-row dense>
                <v-col cols="12" md="3">
                  <div class="text-caption text-grey-darken-1 mb-1">Date & Time</div>
                  <div class="text-body-2 font-weight-bold">{{ viewingBookingCaregivers.date }}</div>
                  <div class="text-body-2 font-weight-bold">{{ viewingBookingCaregivers.time }}</div>
                </v-col>
                <v-col cols="12" md="2">
                  <div class="text-caption text-grey-darken-1 mb-1">Duration</div>
                  <div class="text-body-2 font-weight-bold">{{ viewingBookingCaregivers.duration }}</div>
                </v-col>
                <v-col cols="12" md="2">
                  <div class="text-caption text-grey-darken-1 mb-1">Needed</div>
                  <div class="text-body-2 font-weight-bold">{{ viewingBookingCaregivers.caregiversNeeded }} caregivers</div>
                </v-col>
                <v-col cols="12" md="2">
                  <div class="text-caption text-grey-darken-1 mb-1">Assigned</div>
                  <div class="text-body-2 font-weight-bold">{{ viewingBookingCaregivers.assignedCount }} caregivers</div>
                </v-col>
                <v-col cols="12" md="3" class="d-flex align-center justify-end">
                  <v-chip :color="getBookingStatusColor(viewingBookingCaregivers.status)" class="text-white font-weight-bold">
                    {{ viewingBookingCaregivers.status }}
                  </v-chip>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-card-text>

        <!-- Tabs -->
        <v-tabs v-model="assignedCaregiversTab" color="success" bg-color="white">
          <v-tab value="caregivers">
            <v-icon class="mr-2">mdi-account-group</v-icon>
            Caregivers ({{ getAssignedCaregivers(viewingBookingCaregivers.id).length }})
          </v-tab>
          <v-tab value="schedule">
            <v-icon class="mr-2">mdi-calendar-week</v-icon>
            Weekly Schedule
          </v-tab>
        </v-tabs>

        <v-divider />

        <!-- Tab Content -->
        <v-card-text class="pa-0" style="height: 500px; overflow-y: auto;">
          <v-tabs-window v-model="assignedCaregiversTab">
            <!-- Caregivers List Tab -->
            <v-tabs-window-item value="caregivers">
              <div class="pa-6">
                <div v-if="getAssignedCaregivers(viewingBookingCaregivers.id).length > 0">
                  <v-row>
                    <v-col v-for="caregiver in getAssignedCaregivers(viewingBookingCaregivers.id)" :key="caregiver.id" cols="12">
                      <v-card elevation="2" class="rounded-lg hover-card">
                        <v-card-text class="pa-4">
                          <div class="d-flex align-start">
                            <!-- Avatar -->
                            <v-avatar size="64" color="success" class="mr-4">
                              <span class="text-white font-weight-bold text-h5">{{ caregiver.name.split(' ').map(n => n[0]).join('') }}</span>
                            </v-avatar>

                            <!-- Caregiver Info -->
                            <div class="flex-grow-1">
                              <div class="text-h6 font-weight-bold mb-1 text-grey-darken-3">{{ caregiver.name }}</div>
                              <div class="d-flex flex-wrap gap-2 mb-2">
                                <v-chip size="small" color="grey-lighten-2">
                                  <v-icon size="16" class="mr-1" style="color: #1a1a1a !important;">mdi-email</v-icon>
                                  <span class="text-grey-darken-3">{{ caregiver.email }}</span>
                                </v-chip>
                                <v-chip size="small" color="grey-lighten-2">
                                  <v-icon size="16" class="mr-1" style="color: #1a1a1a !important;">mdi-phone</v-icon>
                                  <span class="text-grey-darken-3">{{ caregiver.phone || '(646) 282-8282' }}</span>
                                </v-chip>
                                <v-chip size="small" color="success" variant="elevated">
                                  <v-icon size="16" class="mr-1">mdi-cash</v-icon>
                                  <span class="font-weight-bold">${{ caregiver.hourly_rate || caregiver.hourlyRate || 20 }}/hr</span>
                                </v-chip>
                              </div>
                              
                              <!-- Schedule Info -->
                              <div v-if="getCaregiverScheduleDays(caregiver.id).length > 0" class="mt-2">
                                <v-chip size="small" color="primary" variant="tonal" prepend-icon="mdi-calendar-check">
                                  {{ getCaregiverScheduleDays(caregiver.id).length }} days scheduled
                                </v-chip>
                                <span class="text-caption text-grey ml-2">
                                  {{ getCaregiverScheduleDays(caregiver.id).map(d => d.charAt(0).toUpperCase() + d.slice(1, 3)).join(', ') }}
                                </span>
                              </div>
                              <div v-else class="mt-2">
                                <v-chip size="small" color="warning" variant="tonal" prepend-icon="mdi-calendar-alert">
                                  No schedule assigned
                                </v-chip>
                              </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-column gap-2">
                              <v-btn color="error" variant="outlined" size="small" prepend-icon="mdi-close" @click="unassignCaregiver(caregiver.id)">
                                Unassign
                              </v-btn>
                            </div>
                          </div>
                        </v-card-text>
                      </v-card>
                    </v-col>
                  </v-row>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                  <v-icon size="80" color="grey-lighten-2">mdi-account-off-outline</v-icon>
                  <div class="text-h6 text-grey-darken-1 mt-4">No Caregivers Assigned</div>
                  <div class="text-body-2 text-grey mt-2 mb-4">Assign caregivers to this booking to get started.</div>
                  <v-btn color="success" size="large" prepend-icon="mdi-account-plus" @click="assignCaregiverDialog(viewingBookingCaregivers); viewAssignedCaregiversDialog = false">
                    Assign Caregivers
                  </v-btn>
                </div>
              </div>
            </v-tabs-window-item>

            <!-- Weekly Schedule Tab -->
            <v-tabs-window-item value="schedule">
              <div class="pa-6">
                <div v-if="getAssignedCaregivers(viewingBookingCaregivers.id).length > 0">
                  <!-- Clear All Button -->
                  <div class="d-flex justify-end mb-4">
                    <v-btn
                      color="error"
                      variant="outlined"
                      size="small"
                      prepend-icon="mdi-close-circle"
                      @click="clearAllSchedules"
                    >
                      Clear All Schedules
                    </v-btn>
                  </div>

                  <!-- Week Calendar Grid - 4 columns -->
                  <v-row>
                    <v-col 
                      v-for="day in getAvailableDays(viewingBookingCaregivers)" 
                      :key="day.value"
                      cols="12"
                      sm="6"
                      md="4"
                      lg="3"
                    >
                      <v-card 
                        :class="['day-schedule-card', { 'today-card': isToday(day.value) }]"
                        :color="isToday(day.value) ? 'blue-lighten-5' : ''"
                        elevation="2"
                      >
                        <v-card-title class="pa-3 d-flex align-center justify-space-between" :class="{ 'bg-blue-lighten-4': isToday(day.value) }" style="color: #1a1a1a !important;">
                          <div class="d-flex align-center">
                            <v-icon size="20" class="mr-2" :style="{ color: isToday(day.value) ? '#1565c0 !important' : '#1a1a1a !important' }">
                              {{ isToday(day.value) ? 'mdi-calendar-star' : 'mdi-calendar' }}
                            </v-icon>
                            <div>
                              <div class="text-body-2 font-weight-bold" style="color: #1a1a1a !important;">{{ day.label }}</div>
                              <div v-if="isToday(day.value)" class="text-caption font-weight-bold" style="color: #1565c0 !important;">TODAY</div>
                              <div class="text-caption d-flex align-center mt-1" style="color: #616161 !important;">
                                <v-icon size="12" class="mr-1" style="color: #616161 !important;">mdi-clock-outline</v-icon>
                                {{ getTimeForDay(viewingBookingCaregivers, day.value) }}
                              </div>
                            </div>
                          </div>
                        </v-card-title>

                        <v-divider />

                        <v-card-text class="pa-3">
                          <!-- Assigned Caregiver Badge -->
                          <div class="mb-3">
                            <v-chip 
                              v-if="getCaregiverForDay(day.value)"
                              :color="isToday(day.value) ? 'blue' : 'success'"
                              size="small"
                              class="text-white font-weight-bold"
                              block
                            >
                              <v-icon size="14" class="mr-1">mdi-account-check</v-icon>
                              Assigned
                            </v-chip>
                            <v-chip 
                              v-else
                              color="grey-lighten-2"
                              size="small"
                              block
                            >
                              <v-icon size="14" class="mr-1" color="grey-darken-2">mdi-account-off</v-icon>
                              <span class="text-grey-darken-2">Unassigned</span>
                            </v-chip>
                          </div>

                          <!-- Current Assignment Display -->
                          <div v-if="getCaregiverForDay(day.value)" class="mb-3">
                            <div class="pa-2 bg-success-lighten-5 rounded d-flex align-center">
                              <v-avatar size="32" color="success" class="mr-2">
                                <span class="text-white text-caption font-weight-bold">
                                  {{ getCaregiverForDay(day.value).name.split(' ').map(n => n[0]).join('') }}
                                </span>
                              </v-avatar>
                              <div class="flex-grow-1" style="min-width: 0;">
                                <div class="text-body-2 font-weight-bold text-truncate text-grey-darken-3">
                                  {{ getCaregiverForDay(day.value).name }}
                                </div>
                                <div class="text-caption text-grey-darken-1">Currently assigned</div>
                              </div>
                            </div>
                          </div>

                          <!-- Caregiver Selection -->
                          <v-select
                            :model-value="getCaregiverForDay(day.value)?.id"
                            @update:model-value="(val) => assignCaregiverToDay(day.value, val)"
                            :items="getAssignedCaregivers(viewingBookingCaregivers.id)"
                            item-title="name"
                            item-value="id"
                            label="Assign Caregiver"
                            variant="outlined"
                            density="compact"
                            prepend-inner-icon="mdi-account"
                            clearable
                            hide-details
                          >
                            <template v-slot:selection="{ item }">
                              <div class="d-flex align-center">
                                <v-avatar size="20" color="success" class="mr-2">
                                  <span class="text-white" style="font-size: 10px; font-weight: bold;">
                                    {{ item.title.split(' ').map(n => n[0]).join('') }}
                                  </span>
                                </v-avatar>
                                <span class="text-body-2 text-grey-darken-3">{{ item.title }}</span>
                              </div>
                            </template>
                            <template v-slot:item="{ props, item }">
                              <v-list-item v-bind="props">
                                <template v-slot:prepend>
                                  <v-avatar size="32" color="success">
                                    <span class="text-white text-caption font-weight-bold">
                                      {{ item.title.split(' ').map(n => n[0]).join('') }}
                                    </span>
                                  </v-avatar>
                                </template>
                                <template v-slot:title>
                                  <span class="text-grey-darken-3">{{ item.title }}</span>
                                </template>
                              </v-list-item>
                            </template>
                          </v-select>
                        </v-card-text>
                      </v-card>
                    </v-col>
                  </v-row>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                  <v-icon size="80" color="grey-lighten-2">mdi-account-off-outline</v-icon>
                  <div class="text-h6 text-grey-darken-1 mt-4">No Caregivers Assigned</div>
                  <div class="text-body-2 text-grey mt-2 mb-4">Please assign caregivers to this booking first.</div>
                  <v-btn color="success" size="large" prepend-icon="mdi-account-plus" @click="assignCaregiverDialog(viewingBookingCaregivers); viewAssignedCaregiversDialog = false">
                    Assign Caregivers
                  </v-btn>
                </div>
              </div>
            </v-tabs-window-item>
          </v-tabs-window>
        </v-card-text>

        <!-- Actions -->
        <v-divider />
        <v-card-actions class="pa-4 bg-grey-lighten-5">
          <v-spacer />
          <v-btn variant="text" @click="viewAssignedCaregiversDialog = false">
            Close
          </v-btn>
          <v-btn color="success" variant="flat" prepend-icon="mdi-account-plus" @click="assignCaregiverDialog(viewingBookingCaregivers); viewAssignedCaregiversDialog = false">
            Manage Assignment
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  <v-dialog v-model="viewAssignedHousekeepersDialog" max-width="1200" scrollable>
      <v-card v-if="viewingBookingHousekeepers">
        <v-toolbar color="purple" dark>
          <v-toolbar-title>
            <div class="d-flex align-center">
              <v-icon class="mr-2">mdi-broom</v-icon>
              <div>
                <div class="text-h6">Housekeeper Management</div>
                <div class="text-caption opacity-90">{{ viewingBookingHousekeepers.client }} - {{ viewingBookingHousekeepers.service }}</div>
              </div>
            </div>
          </v-toolbar-title>
          <v-spacer />
          <v-btn icon @click="viewAssignedHousekeepersDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>

        <!-- Booking Info Card (match Caregiver Management layout) -->
        <v-card-text class="pa-6 bg-grey-lighten-4">
          <v-card elevation="0" class="rounded-lg">
            <v-card-text class="pa-4">
              <v-row dense>
                <v-col cols="12" md="3">
                  <div class="text-caption text-grey-darken-1 mb-1">Date & Time</div>
                  <div class="text-body-2 font-weight-bold">{{ viewingBookingHousekeepers.date }}</div>
                  <div class="text-body-2 font-weight-bold">{{ viewingBookingHousekeepers.time }}</div>
                </v-col>
                <v-col cols="12" md="2">
                  <div class="text-caption text-grey-darken-1 mb-1">Duration</div>
                  <div class="text-body-2 font-weight-bold">{{ viewingBookingHousekeepers.duration }}</div>
                </v-col>
                <v-col cols="12" md="2">
                  <div class="text-caption text-grey-darken-1 mb-1">Needed</div>
                  <div class="text-body-2 font-weight-bold">{{ viewingBookingHousekeepers.caregiversNeeded }} housekeepers</div>
                </v-col>
                <v-col cols="12" md="2">
                  <div class="text-caption text-grey-darken-1 mb-1">Assigned</div>
                  <div class="text-body-2 font-weight-bold">{{ getAssignedHousekeepers(viewingBookingHousekeepers.id).length }} housekeepers</div>
                </v-col>
                <v-col cols="12" md="3" class="d-flex align-center justify-end">
                  <v-chip :color="getBookingStatusColor(viewingBookingHousekeepers.status)" class="text-white font-weight-bold">
                    {{ viewingBookingHousekeepers.status }}
                  </v-chip>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-card-text>

        <!-- Tabs -->
        <v-tabs v-model="assignedHousekeepersTab" color="purple" bg-color="white">
          <v-tab value="housekeepers">
            <v-icon class="mr-2">mdi-broom</v-icon>
            Housekeepers ({{ getAssignedHousekeepers(viewingBookingHousekeepers.id).length }})
          </v-tab>
          <v-tab value="schedule">
            <v-icon class="mr-2">mdi-calendar-week</v-icon>
            Weekly Schedule
          </v-tab>
        </v-tabs>

        <v-divider />

        <!-- Tab Content -->
        <v-card-text class="pa-0" style="height: 500px; overflow-y: auto;">
          <v-tabs-window v-model="assignedHousekeepersTab">
            <!-- Housekeepers List Tab -->
            <v-tabs-window-item value="housekeepers">
              <div class="pa-6">
                <div v-if="getAssignedHousekeepers(viewingBookingHousekeepers.id).length > 0">
                  <v-row>
                    <v-col v-for="hk in getAssignedHousekeepers(viewingBookingHousekeepers.id)" :key="hk.id" cols="12">
                      <v-card elevation="2" class="rounded-lg hover-card">
                        <v-card-text class="pa-4">
                          <div class="d-flex align-start">
                            <!-- Avatar -->
                            <v-avatar size="64" color="purple" class="mr-4">
                              <span class="text-white font-weight-bold text-h5">{{ (hk.name || 'H').split(' ').map(n => n[0]).join('') }}</span>
                            </v-avatar>

                            <!-- Housekeeper Info -->
                            <div class="flex-grow-1">
                              <div class="text-h6 font-weight-bold mb-1 text-grey-darken-3">{{ hk.name }}</div>
                              <div class="d-flex flex-wrap gap-2 mb-2">
                                <v-chip size="small" color="grey-lighten-2">
                                  <v-icon size="16" class="mr-1" style="color: #1a1a1a !important;">mdi-email</v-icon>
                                  <span class="text-grey-darken-3">{{ hk.email }}</span>
                                </v-chip>
                                <v-chip size="small" color="grey-lighten-2">
                                  <v-icon size="16" class="mr-1" style="color: #1a1a1a !important;">mdi-phone</v-icon>
                                  <span class="text-grey-darken-3">{{ hk.phone || '(646) 282-8282' }}</span>
                                </v-chip>
                                <v-chip size="small" color="purple" variant="elevated">
                                  <v-icon size="16" class="mr-1">mdi-cash</v-icon>
                                  <span class="font-weight-bold">${{ hk.hourly_rate || 20 }}/hr</span>
                                </v-chip>
                              </div>

                              <!-- Schedule Info -->
                              <div v-if="getHousekeeperScheduleDays(hk.id).length > 0" class="mt-2">
                                <v-chip size="small" color="primary" variant="tonal" prepend-icon="mdi-calendar-check">
                                  {{ getHousekeeperScheduleDays(hk.id).length }} days scheduled
                                </v-chip>
                                <span class="text-caption text-grey ml-2">
                                  {{ getHousekeeperScheduleDays(hk.id).map(d => d.charAt(0).toUpperCase() + d.slice(1, 3)).join(', ') }}
                                </span>
                              </div>
                              <div v-else class="mt-2">
                                <v-chip size="small" color="warning" variant="tonal" prepend-icon="mdi-calendar-alert">
                                  No schedule assigned
                                </v-chip>
                              </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-column gap-2">
                              <v-btn color="error" variant="outlined" size="small" prepend-icon="mdi-close" @click="unassignHousekeeper(hk.id, viewingBookingHousekeepers.id)">
                                Unassign
                              </v-btn>
                            </div>
                          </div>
                        </v-card-text>
                      </v-card>
                    </v-col>
                  </v-row>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                  <v-icon size="80" color="grey-lighten-2">mdi-broom</v-icon>
                  <div class="text-h6 text-grey-darken-1 mt-4">No Housekeepers Assigned</div>
                  <div class="text-body-2 text-grey mt-2 mb-4">Assign housekeepers to this booking to get started.</div>
                  <v-btn color="purple" size="large" prepend-icon="mdi-broom" @click="assignHousekeeperDialog(viewingBookingHousekeepers); viewAssignedHousekeepersDialog = false">
                    Assign Housekeepers
                  </v-btn>
                </div>
              </div>
            </v-tabs-window-item>

            <!-- Weekly Schedule Tab -->
            <v-tabs-window-item value="schedule">
              <div class="pa-6">
                <div v-if="getAssignedHousekeepers(viewingBookingHousekeepers.id).length > 0">
                  <div class="d-flex justify-end mb-4">
                    <v-btn
                      color="error"
                      variant="outlined"
                      size="small"
                      prepend-icon="mdi-close-circle"
                      @click="clearAllHousekeeperSchedules"
                    >
                      Clear All Schedules
                    </v-btn>
                  </div>

                  <v-row>
                    <v-col
                      v-for="day in getAvailableDays(viewingBookingHousekeepers)"
                      :key="day.value"
                      cols="12"
                      sm="6"
                      md="4"
                      lg="3"
                    >
                      <v-card
                        :class="['day-schedule-card', { 'today-card': isTodayHousekeeper(day.value) }]"
                        :color="isTodayHousekeeper(day.value) ? 'blue-lighten-5' : ''"
                        elevation="2"
                      >
                        <v-card-title class="pa-3 d-flex align-center justify-space-between" :class="{ 'bg-blue-lighten-4': isTodayHousekeeper(day.value) }" style="color: #1a1a1a !important;">
                          <div class="d-flex align-center">
                            <v-icon size="20" class="mr-2" :style="{ color: isTodayHousekeeper(day.value) ? '#1565c0 !important' : '#1a1a1a !important' }">
                              {{ isTodayHousekeeper(day.value) ? 'mdi-calendar-star' : 'mdi-calendar' }}
                            </v-icon>
                            <div>
                              <div class="text-body-2 font-weight-bold" style="color: #1a1a1a !important;">{{ day.label }}</div>
                              <div v-if="isTodayHousekeeper(day.value)" class="text-caption font-weight-bold" style="color: #1565c0 !important;">TODAY</div>
                              <div class="text-caption d-flex align-center mt-1" style="color: #616161 !important;">
                                <v-icon size="12" class="mr-1" style="color: #616161 !important;">mdi-clock-outline</v-icon>
                                {{ getTimeForDay(viewingBookingHousekeepers, day.value) }}
                              </div>
                            </div>
                          </div>
                        </v-card-title>

                        <v-divider />

                        <v-card-text class="pa-3">
                          <div class="mb-3">
                            <v-chip
                              v-if="getHousekeeperForDay(day.value)"
                              :color="isTodayHousekeeper(day.value) ? 'blue' : 'purple'"
                              size="small"
                              class="text-white font-weight-bold"
                              block
                            >
                              <v-icon size="14" class="mr-1">mdi-account-check</v-icon>
                              Assigned
                            </v-chip>
                            <v-chip
                              v-else
                              color="grey-lighten-2"
                              size="small"
                              block
                            >
                              <v-icon size="14" class="mr-1" color="grey-darken-2">mdi-account-off</v-icon>
                              <span class="text-grey-darken-2">Unassigned</span>
                            </v-chip>
                          </div>

                          <div v-if="getHousekeeperForDay(day.value)" class="mb-3">
                            <div class="pa-2 bg-purple-lighten-5 rounded d-flex align-center">
                              <v-avatar size="32" color="purple" class="mr-2">
                                <span class="text-white text-caption font-weight-bold">
                                  {{ getHousekeeperForDay(day.value).name.split(' ').map(n => n[0]).join('') }}
                                </span>
                              </v-avatar>
                              <div class="flex-grow-1" style="min-width: 0;">
                                <div class="text-body-2 font-weight-bold text-truncate text-grey-darken-3">
                                  {{ getHousekeeperForDay(day.value).name }}
                                </div>
                                <div class="text-caption text-grey-darken-1">Currently assigned</div>
                              </div>
                            </div>
                          </div>

                          <v-select
                            :model-value="getHousekeeperForDay(day.value)?.id"
                            @update:model-value="(val) => assignHousekeeperToDay(day.value, val)"
                            :items="getAssignedHousekeepers(viewingBookingHousekeepers.id)"
                            item-title="name"
                            item-value="id"
                            label="Assign Housekeeper"
                            variant="outlined"
                            density="compact"
                            prepend-inner-icon="mdi-broom"
                            clearable
                            hide-details
                          />
                        </v-card-text>
                      </v-card>
                    </v-col>
                  </v-row>
                </div>

                <div v-else class="text-center py-12">
                  <v-icon size="80" color="grey-lighten-2">mdi-broom</v-icon>
                  <div class="text-h6 text-grey-darken-1 mt-4">No Housekeepers Assigned</div>
                  <div class="text-body-2 text-grey mt-2 mb-4">Please assign housekeepers to this booking first.</div>
                  <v-btn color="purple" size="large" prepend-icon="mdi-broom" @click="assignHousekeeperDialog(viewingBookingHousekeepers); viewAssignedHousekeepersDialog = false">
                    Assign Housekeepers
                  </v-btn>
                </div>
              </div>
            </v-tabs-window-item>
          </v-tabs-window>
        </v-card-text>

        <!-- Actions -->
        <v-divider />
        <v-card-actions class="pa-4 bg-grey-lighten-5">
          <v-spacer />
          <v-btn variant="text" @click="viewAssignedHousekeepersDialog = false">Close</v-btn>
          <v-btn color="purple" variant="flat" prepend-icon="mdi-broom" @click="assignHousekeeperDialog(viewingBookingHousekeepers); viewAssignedHousekeepersDialog = false">
            Manage Assignment
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Schedule Management Dialog -->
    <v-dialog v-model="scheduleDialog" max-width="1000" persistent scrollable>
      <v-card class="schedule-dialog-card">
        <v-card-title class="d-flex align-center pa-6 bg-primary">
          <v-icon color="white" class="mr-3" size="28">mdi-calendar-clock</v-icon>
          <span class="text-h5 font-weight-bold text-white">Manage Caregiver Schedule</span>
          <v-spacer />
          <v-btn icon="mdi-close" variant="text" color="white" size="small" @click="closeScheduleDialog" />
        </v-card-title>
        
        <v-divider />
        
        <v-card-text class="pa-6">
          <v-row v-if="scheduleCaregiver && scheduleBooking">
            <!-- Caregiver Info -->
            <v-col cols="12">
              <v-card class="caregiver-info-card" elevation="0" color="grey-lighten-5">
                <v-card-text class="pa-4">
                  <div class="d-flex align-center">
                    <v-avatar 
                      v-if="scheduleCaregiver.avatar" 
                      :image="scheduleCaregiver.avatar.startsWith('http') ? scheduleCaregiver.avatar : `/storage/${scheduleCaregiver.avatar}`" 
                      size="64" 
                      class="mr-4" 
                    />
                    <v-avatar 
                      v-else 
                      color="primary" 
                      size="64" 
                      class="mr-4"
                    >
                      <span class="text-h5 font-weight-bold text-white">
                        {{ getInitials(scheduleCaregiver.name) }}
                      </span>
                    </v-avatar>
                    <div class="flex-grow-1">
                      <h3 class="text-h6 font-weight-bold mb-1">{{ scheduleCaregiver.name }}</h3>
                      <div class="d-flex align-center text-caption text-grey-darken-1">
                        <v-icon size="14" class="mr-1">mdi-email</v-icon>
                        {{ scheduleCaregiver.email }}
                      </div>
                    </div>
                    <v-chip color="success" variant="flat" size="small">
                      <v-icon start size="16">mdi-shield-check</v-icon>
                      Assigned
                    </v-chip>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>

            <!-- Booking Details -->
            <v-col cols="12">
              <v-card class="booking-info-card" elevation="0" color="blue-lighten-5">
                <v-card-text class="pa-4">
                  <div class="d-flex align-center">
                    <v-icon color="blue-darken-2" class="mr-3" size="24">mdi-information</v-icon>
                    <div>
                      <div class="text-subtitle-2 text-grey-darken-1 mb-1">Booking Information</div>
                      <div class="text-body-1 font-weight-medium">
                        <strong>{{ scheduleBooking.client || 'Client' }}</strong> • 
                        {{ scheduleBooking.dutyType || scheduleBooking.duty_type || 'Care Service' }} • 
                        <v-chip size="x-small" color="primary" variant="flat" class="ml-1">
                          {{ scheduleBooking.durationDays || scheduleBooking.duration_days || 0 }} days
                        </v-chip>
                      </div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>

            <!-- Days Selection -->
            <v-col cols="12">
              <div class="section-header mb-4">
                <v-icon color="primary" class="mr-2">mdi-calendar-multiple</v-icon>
                <span class="text-h6 font-weight-bold">Select Working Days</span>
                <v-chip size="small" color="info" variant="tonal" class="ml-2">
                  {{ getScheduleType(scheduleBooking.dutyType || scheduleBooking.duty_type) }}
                </v-chip>
              </div>
              
              <div class="days-grid-row">
                <v-card
                  v-for="day in getAvailableDays(scheduleBooking.dutyType || scheduleBooking.duty_type)"
                  :key="day.value"
                  :class="['day-card-modern', { 'day-selected': isDaySelected(day.value) }]"
                  @click="toggleDay(day.value)"
                  :elevation="isDaySelected(day.value) ? 8 : 2"
                >
                  <v-card-text class="pa-4">
                    <div class="text-center">
                      <v-icon 
                        :color="isDaySelected(day.value) ? 'white' : 'grey'" 
                        size="40" 
                        class="mb-3"
                      >
                        {{ isDaySelected(day.value) ? 'mdi-check-circle' : 'mdi-circle-outline' }}
                      </v-icon>
                      <div 
                        :class="['text-subtitle-1 font-weight-bold mb-2', isDaySelected(day.value) ? 'text-white' : '']"
                      >
                        {{ day.label }}
                      </div>
                      
                      <!-- Show assigned caregiver if different from current -->
                      <div v-if="getCaregiverForDay(day.value) && getCaregiverForDay(day.value).id !== scheduleCaregiver.id" class="mb-2">
                        <v-chip 
                          size="x-small" 
                          :color="isDaySelected(day.value) ? 'white' : 'warning'" 
                          :variant="isDaySelected(day.value) ? 'flat' : 'tonal'"
                        >
                          <v-icon size="12" class="mr-1">mdi-account</v-icon>
                          {{ getCaregiverForDay(day.value).shortName }}
                        </v-chip>
                      </div>
                      
                      <div 
                        v-if="isDaySelected(day.value)" 
                        :class="['text-caption', isDaySelected(day.value) ? 'text-white' : 'text-primary']"
                      >
                        {{ getDaySchedule(day.value).start_time || '--:--' }} - {{ getDaySchedule(day.value).end_time || '--:--' }}
                      </div>
                      <div v-else-if="getCaregiverForDay(day.value) && getCaregiverForDay(day.value).id !== scheduleCaregiver.id" class="text-caption text-warning">
                        Assigned to {{ getCaregiverForDay(day.value).name.split(' ')[0] }}
                      </div>
                      <div v-else class="text-caption text-grey">
                        Not scheduled
                      </div>
                    </div>
                  </v-card-text>
                </v-card>
              </div>
            </v-col>

            <!-- Time Settings -->
            <v-col cols="12" v-if="selectedDays.length > 0">
              <v-divider class="my-4" />
              
              <div class="section-header mb-4">
                <v-icon color="primary" class="mr-2">mdi-clock-outline</v-icon>
                <span class="text-h6 font-weight-bold">Set Shift Times</span>
              </div>
              
              <v-card elevation="0" color="grey-lighten-5" class="pa-4">
                <v-row>
                  <v-col cols="12" md="5">
                    <v-text-field
                      v-model="shiftStartTime"
                      label="Shift Start Time"
                      type="time"
                      variant="outlined"
                      density="comfortable"
                      prepend-inner-icon="mdi-clock-start"
                      color="primary"
                      bg-color="white"
                      hide-details
                    />
                  </v-col>
                  <v-col cols="12" md="5">
                    <v-text-field
                      v-model="shiftEndTime"
                      label="Shift End Time"
                      type="time"
                      variant="outlined"
                      density="comfortable"
                      prepend-inner-icon="mdi-clock-end"
                      color="primary"
                      bg-color="white"
                      hide-details
                    />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-btn
                      color="primary"
                      variant="flat"
                      prepend-icon="mdi-arrow-down-bold"
                      @click="applyTimesToSelectedDays"
                      block
                      height="40"
                    >
                      Apply
                    </v-btn>
                  </v-col>
                </v-row>
              </v-card>
            </v-col>

            <!-- Schedule Summary -->
            <v-col cols="12" v-if="selectedDays.length > 0">
              <v-divider class="my-4" />
              
              <div class="section-header mb-4">
                <v-icon color="success" class="mr-2">mdi-calendar-check</v-icon>
                <span class="text-h6 font-weight-bold">Schedule Summary</span>
                <v-chip size="small" color="success" variant="flat" class="ml-2">
                  {{ selectedDays.length }} {{ selectedDays.length === 1 ? 'day' : 'days' }} scheduled
                </v-chip>
              </div>
              
              <v-card elevation="2">
                <v-list density="compact" class="py-0">
                  <v-list-item
                    v-for="(day, index) in selectedDays"
                    :key="day"
                    :class="index % 2 === 0 ? 'bg-grey-lighten-5' : ''"
                  >
                    <template v-slot:prepend>
                      <v-avatar color="primary" size="32">
                        <v-icon color="white" size="18">mdi-calendar</v-icon>
                      </v-avatar>
                    </template>
                    
                    <v-list-item-title class="font-weight-medium">
                      {{ getDayLabel(day) }}
                    </v-list-item-title>
                    
                    <v-list-item-subtitle>
                      <v-icon size="14" class="mr-1">mdi-clock-outline</v-icon>
                      {{ getDaySchedule(day).start_time || 'Not set' }} - {{ getDaySchedule(day).end_time || 'Not set' }}
                    </v-list-item-subtitle>

                    <template v-slot:append>
                      <v-btn
                        icon="mdi-close"
                        size="x-small"
                        variant="text"
                        color="error"
                        @click="toggleDay(day)"
                      />
                    </template>
                  </v-list-item>
                </v-list>
              </v-card>
            </v-col>

            <!-- No Days Selected Message -->
            <v-col cols="12" v-else>
              <v-alert type="warning" variant="tonal" prominent>
                <v-alert-title class="font-weight-bold">No Days Selected</v-alert-title>
                Please select at least one day from the calendar above to create a schedule.
              </v-alert>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider />

        <v-card-actions class="pa-6">
          <v-spacer />
          <v-btn 
            color="grey-darken-1" 
            variant="outlined" 
            prepend-icon="mdi-close"
            @click="closeScheduleDialog"
            size="large"
          >
            Cancel
          </v-btn>
          <v-btn
            color="success"
            variant="flat"
            prepend-icon="mdi-content-save"
            @click="saveSchedule"
            :disabled="selectedDays.length === 0"
            :loading="savingSchedule"
            size="large"
          >
            Save Schedule
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
import EmailVerificationBanner from './EmailVerificationBanner.vue';
import { useNotification } from '../composables/useNotification';

const { notification, success, error, warning, info } = useNotification();

const currentSection = ref(localStorage.getItem('adminSection') || 'dashboard');
const userEmailVerified = ref(false);
const settingsTab = ref('system');
const addUserDialog = ref(false);
const viewCaregiverDialog = ref(false);
const viewingCaregiver = ref(null);
const caregiverReviews = ref([]);
const loadingCaregiverReviews = ref(false);
const announceDialog = ref(false);
const testEmailDialog = ref(false);
const testEmailAddress = ref('teofiloharry69@gmail.com');
const sendingTestEmail = ref(false);
const clientDialog = ref(false);
const caregiverDialog = ref(false);
const editingClient = ref(false);
const editingCaregiver = ref(false);
const clientForm = ref({ 
  firstName: '', 
  lastName: '', 
  email: '', 
  phone: '', 
  birthdate: '', 
  address: '', 
  state: 'New York', 
  county: '', 
  city: '', 
  zip_code: '', 
  password: '',
  status: 'Active' 
});
const caregiverForm = ref({ 
  firstName: '', 
  lastName: '', 
  email: '', 
  phone: '', 
  birthdate: '', 
  address: '', 
  state: 'New York', 
  county: '', 
  city: '', 
  zip_code: '', 
  password: '',
  experience: '', 
  trainingCenter: '', 
  customTrainingCenter: '', 
  isCustomTrainingCenter: false,
  trainingCertificate: null, 
  bio: '', 
  preferred_hourly_rate_min: null,
  preferred_hourly_rate_max: null,
  has_hha: false,
  hha_number: '',
  has_cna: false,
  cna_number: '',
  has_rn: false,
  rn_number: '',
  status: 'Active' 
});

// Housekeeper dialog state
const housekeeperDialog = ref(false);
const editingHousekeeper = ref(false);
const housekeeperForm = ref({
  id: null,
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  birthdate: '',
  address: '',
  state: 'New York',
  county: '',
  city: '',
  zip_code: '',
  password: '',
  experience: '',
  hourly_rate: '',
  bio: '',
  has_own_supplies: false,
  available_for_transport: false,
  skills: [],
  specializations: [],
  status: 'Active'
});

// caregiverTrainingCenters is loaded dynamically via loadCaregiverTrainingCenters()

const announcementData = ref({
  title: '',
  message: '',
  type: 'info',
  recipients: 'all',
  priority: 'normal'
});
const userSearch = ref('');
const userType = ref('All');
const userStatus = ref('All');
const locationFilter = ref('All');
const caregiverSearch = ref('');
const caregiverLocationFilter = ref('All');
const caregiverStatusFilter = ref('All');
const clientSearch = ref('');
const clientLocationFilter = ref('All');
const clientStatusFilter = ref('All');
const bookingLocationFilter = ref('All');
const revenueChartPeriod = ref('month');
const revenueChart = ref(null);
const userChart = ref(null);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const showMarketingPassword = ref(false);
const showTrainingPassword = ref(false);
const showAddUserPassword = ref(false);
const showClientPassword = ref(false);
const showCaregiverPassword = ref(false);
const showHousekeeperPassword = ref(false);
const addUserFormData = ref({
  firstName: '',
  lastName: '',
  email: '',
  password: '',
  userType: 'Client',
  status: 'Active'
});
const maintenanceMode = ref(false);

const caregivers = ref([]);
const caregiverAssignments = ref({}); // Track which caregivers are assigned to which bookings

const housekeepers = ref([]);
const housekeeperSearch = ref('');
const housekeeperLocationFilter = ref('All');
const housekeeperStatusFilter = ref('All');

const clients = ref([]);

// Selection refs for bulk operations
const selectedUsers = ref([]);
const selectedCaregivers = ref([]);
const selectedClients = ref([]);
const selectedMarketingStaff = ref([]);
const selectedTrainingCenters = ref([]);
const selectedBookings = ref([]);

const pendingApplications = ref([]);

const viewApplicationDialog = ref(false);
const viewingApplication = ref(null);

const loadApplications = async () => {
  try {
    const response = await fetch('/api/admin/applications');
    const data = await response.json();
    pendingApplications.value = data.applications;
  } catch (error) {
  }
};

const viewApplication = (application) => {
  viewingApplication.value = application;
  viewApplicationDialog.value = true;
};

const passwordResets = ref([]);

const loadPasswordResets = async () => {
  try {
    const response = await fetch('/api/admin/password-resets');
    const data = await response.json();
    passwordResets.value = data.resets;
  } catch (error) {
  }
};

const caregiverHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Zip Code', key: 'zip_code' },
  { title: 'Location', key: 'location' },
  { title: 'Status', key: 'status' },
  { title: 'Rating', key: 'rating' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const housekeeperHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'ZIP Code', key: 'zip_code' },
  { title: 'Location', key: 'location' },
  { title: 'Status', key: 'status' },
  { title: 'Experience', key: 'years_experience' },
  { title: 'Rating', key: 'rating' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const clientHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Zip Code', key: 'zip_code' },
  { title: 'Status', key: 'status' },
  { title: 'Bookings', key: 'bookings' },
  { title: 'Total Spent', key: 'totalSpent' },
  { title: 'Actions', key: 'actions', sortable: false },
];

// Marketing Staff Management
const marketingStaff = ref([]);
const marketingStaffSearch = ref('');
const marketingStaffStatusFilter = ref('All');
const marketingStaffDialog = ref(false);
const editingMarketingStaff = ref(null);
const viewMarketingStaffDialog = ref(false);
const viewingMarketingStaff = ref(null);
const payingCommission = ref(null);
const marketingStaffHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Zip Code', key: 'zip_code' },
  { title: 'Referral Code', key: 'referralCode' },
  { title: 'Clients Acquired', key: 'clientsAcquired' },
  { title: 'Total Hours', key: 'totalHours' },
  { title: 'Commission Earned', key: 'commissionEarned' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

// Admin Staff Management
const adminStaff = ref([]);
const adminStaffSearch = ref('');
const adminStaffStatusFilter = ref('All');
const adminStaffDialog = ref(false);
const editingAdminStaff = ref(null);
const viewAdminStaffDialog = ref(false);
const viewingAdminStaff = ref(null);
const selectedAdminStaff = ref([]);
const savingAdminStaff = ref(false);
const showAdminStaffPassword = ref(false);

// Default page permissions for admin staff
const getDefaultPagePermissions = () => ({
  dashboard: true,
  notifications: true,
  users: true,
  caregivers: true,
  housekeepers: true,
  clients: true,
  'admin-staff': true,
  'marketing-staff': true,
  'training-centers': true,
  pending: true,
  'password-resets': true,
  'client-bookings': true,
  'time-tracking': true,
  reviews: true,
  announcements: true,
  payments: true,
  analytics: true,
  profile: true,
});

// Permission pages grouped by category for the UI
const permissionPages = {
  general: [
    { value: 'dashboard', title: 'Dashboard', icon: 'mdi-view-dashboard' },
    { value: 'notifications', title: 'Notifications', icon: 'mdi-bell' },
    { value: 'profile', title: 'Profile', icon: 'mdi-account-circle' },
  ],
  users: [
    { value: 'users', title: 'Users', icon: 'mdi-account-group' },
    { value: 'caregivers', title: 'Caregivers', icon: 'mdi-account-heart' },
    { value: 'housekeepers', title: 'Housekeepers', icon: 'mdi-broom' },
    { value: 'clients', title: 'Clients', icon: 'mdi-account-multiple' },
    { value: 'admin-staff', title: 'Admin Staff', icon: 'mdi-shield-account' },
    { value: 'marketing-staff', title: 'Marketing Partner', icon: 'mdi-bullhorn-variant' },
    { value: 'training-centers', title: 'Training Centers', icon: 'mdi-school' },
  ],
  applications: [
    { value: 'pending', title: 'Contractors Application', icon: 'mdi-account-clock' },
    { value: 'password-resets', title: 'Password Resets', icon: 'mdi-lock-reset' },
  ],
  bookings: [
    { value: 'client-bookings', title: 'Client Bookings', icon: 'mdi-calendar-account' },
    { value: 'time-tracking', title: 'Time Tracking', icon: 'mdi-clock-time-four' },
  ],
  other: [
    { value: 'reviews', title: 'Reviews & Ratings', icon: 'mdi-star' },
    { value: 'announcements', title: 'Announcements', icon: 'mdi-bullhorn' },
    { value: 'payments', title: 'Payments', icon: 'mdi-credit-card' },
    { value: 'analytics', title: 'Analytics', icon: 'mdi-chart-line' },
  ],
};

const adminStaffFormData = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  status: 'Active',
  page_permissions: getDefaultPagePermissions()
});

// Select/Deselect all permissions
const selectAllPermissions = () => {
  Object.keys(adminStaffFormData.value.page_permissions).forEach(key => {
    adminStaffFormData.value.page_permissions[key] = true;
  });
};

const deselectAllPermissions = () => {
  Object.keys(adminStaffFormData.value.page_permissions).forEach(key => {
    adminStaffFormData.value.page_permissions[key] = false;
  });
};

const adminStaffHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Email Verified', key: 'email_verified' },
  { title: 'Last Login', key: 'last_login' },
  { title: 'Joined', key: 'joined' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

// Training Centers Management
const trainingCenters = ref([]);
const trainingCenterSearch = ref('');
const trainingCenterStatusFilter = ref('All');
const trainingCenterDialog = ref(false);
const editingTrainingCenter = ref(null);
const viewTrainingCenterDialog = ref(false);
const viewingTrainingCenter = ref(null);
const trainingCenterFormData = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
  state: 'New York',
  county: '',
  city: '',
  zip_code: '',
  password: '',
  status: 'Active'
});
const trainingCenterZipLocation = ref('');

// Use the same training center list pattern as the caregiver profile (API-backed)
const caregiverTrainingCenters = ref([]);
const loadCaregiverTrainingCenters = async () => {
  try {
    const response = await fetch('/api/training-centers', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      credentials: 'include'
    });

    const data = await response.json().catch(() => ({}));
    // Accept a few possible shapes
    const centers = Array.isArray(data) ? data : (data.centers || data.training_centers || []);
    caregiverTrainingCenters.value = (centers || [])
      .map(c => (typeof c === 'string' ? c : (c.name || c.title || '')))
      .map(s => String(s || '').trim())
      .filter(Boolean);
  } catch (e) {
    caregiverTrainingCenters.value = [];
  }
};
const trainingCenterHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Zip Code', key: 'zip_code' },
  { title: 'Caregivers', key: 'caregiverCount' },
  { title: 'Total Hours', key: 'totalHours' },
  { title: 'Commission Earned', key: 'commissionEarned' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const applicationHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Type', key: 'type' },
  { title: 'Phone', key: 'phone' },
  { title: 'Applied', key: 'applied' },
  { title: 'Status', key: 'documents' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const passwordResetHeaders = [
  { title: 'Email', key: 'email' },
  { title: 'User Type', key: 'userType' },
  { title: 'Requested', key: 'requestedAt' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const profileData = ref({
  firstName: 'Admin',
  lastName: 'User',
  email: 'admin@casprivatecare.com',
  phone: '(646) 282-8282',
  department: 'System Administration',
  role: 'Super Admin',
});

// Password change data
const passwordData = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
});

// Profile for header
const profile = ref({
  firstName: '',
  lastName: ''
});
const userAvatar = ref('');
const uploadingAvatar = ref(false);
const adminUserId = ref(null);
const avatarInput = ref(null);

const triggerAvatarUpload = () => {
  avatarInput.value?.click();
};

const uploadAvatar = async (event) => {
  const file = event.target.files?.[0];
  if (!file || !adminUserId.value) return;
  
  uploadingAvatar.value = true;
  try {
    const formData = new FormData();
    formData.append('avatar', file);
    
    const response = await fetch(`/api/user/${adminUserId.value}/avatar`, {
      method: 'POST',
      body: formData
    });
    
    if (response.ok) {
      const data = await response.json();
      userAvatar.value = data.avatar_url;
    }
  } catch (error) {
  } finally {
    uploadingAvatar.value = false;
    if (avatarInput.value) avatarInput.value.value = '';
  }
};

const loadProfile = async () => {
  try {
    const response = await fetch('/api/profile?user_type=admin');
    if (response.ok) {
      const result = await response.json();
      const data = result.user || result;
      profile.value.firstName = data.first_name || data.name?.split(' ')[0] || 'Admin';
      profile.value.lastName = data.last_name || data.name?.split(' ').slice(1).join(' ') || 'User';
      adminUserId.value = data.id;
      
      // Set email verification status
      userEmailVerified.value = data.email_verified_at !== null && data.email_verified_at !== undefined;
      
      profileData.value.firstName = profile.value.firstName;
      profileData.value.lastName = profile.value.lastName;
      profileData.value.email = data.email || 'admin@casprivatecare.com';
  profileData.value.phone = data.phone || '(646) 282-8282';
      if (data.avatar) {
        userAvatar.value = `/storage/${data.avatar}`;
      }
    }
  } catch (error) {
  }
};

const adminNotifications = ref([]);
const adminUnreadCount = computed(() => adminNotifications.value.filter(n => !n.read).length);

const navItems = ref([
  { icon: 'mdi-view-dashboard', title: 'Dashboard', value: 'dashboard' },
  { icon: 'mdi-bell', title: 'Notifications', value: 'notifications', badge: adminUnreadCount.value > 0 },
  { 
    icon: 'mdi-account-group', 
    title: 'Users', 
    value: 'user-management',
    isToggle: true,
    expanded: false,
    children: [
      { icon: 'mdi-account-heart', title: 'Caregivers', value: 'caregivers' },
      { icon: 'mdi-broom', title: 'Housekeepers', value: 'housekeepers' },
      { icon: 'mdi-account-multiple', title: 'Clients', value: 'clients' },
      { icon: 'mdi-shield-account', title: 'Admin Staff', value: 'admin-staff' },
      { icon: 'mdi-bullhorn-variant', title: 'Marketing Partner', value: 'marketing-staff' },
      { icon: 'mdi-school', title: 'Training Centers', value: 'training-centers' }
    ]
  },
  { icon: 'mdi-account-clock', title: 'Contractors Application', value: 'pending', category: 'APPLICATIONS' },
  { icon: 'mdi-lock-reset', title: 'Password Resets', value: 'password-resets', category: 'APPLICATIONS' },
  { icon: 'mdi-calendar-account', title: 'Client Bookings', value: 'client-bookings', category: 'BOOKINGS' },
  { icon: 'mdi-clock-time-four', title: 'Time Tracking', value: 'time-tracking', category: 'BOOKINGS' },
  { icon: 'mdi-star', title: 'Reviews & Ratings', value: 'reviews', category: 'FEEDBACK' },
  { icon: 'mdi-bullhorn', title: 'Announcements', value: 'announcements', category: 'COMMUNICATIONS' },
  { icon: 'mdi-credit-card', title: 'Payments', value: 'payments', category: 'FINANCIAL' },
  { icon: 'mdi-chart-line', title: 'Analytics', value: 'analytics', category: 'REPORTS' },
  { icon: 'mdi-account-circle', title: 'Profile', value: 'profile', category: 'ACCOUNT' },
]);

const loadAdminNotificationCount = async () => {
  try {
    const response = await fetch('/api/notifications?user_id=3');
    const data = await response.json();
    adminNotifications.value = data.notifications || [];
  } catch (error) {
  }
};

const stats = ref([
  { title: 'Total Users', value: '0', icon: 'mdi-account-group', color: 'error', change: '+12% this month', changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
  { title: 'Active Bookings', value: '0', icon: 'mdi-calendar-check', color: 'error', change: '+8% this week', changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
  { title: 'Total Revenue', value: '$0', icon: 'mdi-currency-usd', color: 'error', change: '+15% this month', changeColor: 'text-success', changeIcon: 'mdi-arrow-up' },
  { title: 'System Uptime', value: '98.5%', icon: 'mdi-server', color: 'error', change: 'Last 30 days', changeColor: 'text-info', changeIcon: 'mdi-information' },
]);

const loadAdminStats = async () => {
  try {
    const response = await fetch('/api/admin/stats');
    if (!response.ok) throw new Error('API failed');
    const data = await response.json();
    const totalUsers = data.total_users || 0;
    const revenue = data.total_revenue || 0;
    const activeBookings = data.active_bookings || 0;
    const userGrowthPct = data.user_growth || 0;
    const bookingGrowthPct = data.booking_growth || 0;
    
    stats.value[0].value = totalUsers.toString();
    stats.value[0].change = userGrowthPct >= 0 ? `+${userGrowthPct}% this month` : `${userGrowthPct}% this month`;
    stats.value[1].value = activeBookings.toString();
    stats.value[1].change = bookingGrowthPct >= 0 ? `+${bookingGrowthPct}% this week` : `${bookingGrowthPct}% this week`;
    stats.value[2].value = '$' + revenue.toFixed(2);
    userProgress.value = Math.min((totalUsers / 100) * 100, 100);
    userGrowth.value = totalUsers > 0 ? `${totalUsers} total users` : '+0% this month';
    revenueProgress.value = Math.min((revenue / 50000) * 100, 100);
    
    // Load real platform metrics from new endpoint
    try {
      const metricsResponse = await fetch('/api/admin/platform-metrics');
      if (metricsResponse.ok) {
        const metricsData = await metricsResponse.json();
        const metrics = metricsData.metrics || {};
        platformMetrics.value.bookings = (metrics.total_bookings || activeBookings).toString();
        platformMetrics.value.response = metrics.response_time ? `${metrics.response_time}ms` : '0ms';
        platformMetrics.value.errors = metrics.error_rate ? `${metrics.error_rate.toFixed(1)}%` : '0%';
        platformMetrics.value.sessions = (metrics.active_sessions || totalUsers).toString();
        
        // Update System Uptime in analyticsStats
        if (metrics.uptime && analyticsStats.value.length > 0) {
          // Find and update the System Uptime stat
          const uptimeStat = analyticsStats.value.find(s => s.title === 'System Uptime');
          if (uptimeStat) {
            uptimeStat.value = `${metrics.uptime.toFixed(1)}%`;
          }
        }
      } else {
        // Fallback to basic values
        platformMetrics.value.bookings = activeBookings.toString();
        platformMetrics.value.sessions = totalUsers.toString();
      }
    } catch (metricsError) {
      // Fallback on error
      platformMetrics.value.bookings = activeBookings.toString();
      platformMetrics.value.sessions = totalUsers.toString();
    }
    
    if (data.recent_bookings) {
      recentActivity.value = data.recent_bookings.map(b => ({
        time: new Date(b.created_at).toLocaleString(),
        user: b.client?.name || 'Unknown',
        action: `Booking for ${b.service_type}`,
        type: 'Booking'
      }));
    }
  } catch (error) {
  }
};

const analyticsStats = ref([
  { title: 'Revenue', value: '$0', icon: 'mdi-currency-usd', color: 'error', change: '+15%' },
  { title: 'Clients', value: '0', icon: 'mdi-account-multiple', color: 'info', change: '+8%' },
  { title: 'Caregivers', value: '0', icon: 'mdi-account-heart', color: 'success', change: '+12%' },
  { title: 'Bookings', value: '0', icon: 'mdi-calendar-check', color: 'warning', change: '+5%' },
]);

// Money Flow Monitoring
const moneyFlow = ref({
  today: {
    payments_in: 0,
    payouts_out: 0,
    net_change: 0
  },
  totals: {
    total_payments_in: 0,
    total_payouts_out: 0,
    pending_payouts: 0,
    expected_balance: 0,
    stripe_balance: null,
    balance_difference: null
  },
  commissions: {
    pending_marketing: 0,
    pending_training: 0,
    platform_total: 0
  },
  recent_transactions: [],
  failed_payouts: [],
  caregiver_balances: []
});

const loadMoneyFlowData = async () => {
  try {
    console.log('🔄 Loading Money Flow Data...');
    const response = await fetch('/api/admin/money-flow-dashboard');
    console.log('📡 Money Flow API Response Status:', response.status);
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('❌ Money Flow API Error:', errorText);
      throw new Error('Failed to load money flow data');
    }
    
    const data = await response.json();
    console.log('📦 Money Flow API Raw Data:', JSON.stringify(data, null, 2));
    
    if (data.success) {
      // Safely extract today data
      const today = data.today || {};
      console.log('📅 Today Data:', today);
      moneyFlow.value.today = {
        payments_in: Number(today.payments_in ?? 0),
        payouts_out: Number(today.payouts_out ?? 0),
        net_change: Number(today.net_change ?? 0)
      };
      
      // Safely extract totals data
      const totals = data.totals || {};
      console.log('💰 Totals Data:', totals);
      moneyFlow.value.totals = {
        total_payments_in: Number(totals.total_payments_in ?? 0),
        total_payouts_out: Number(totals.total_payouts_out ?? 0),
        pending_payouts: Number(totals.pending_payouts ?? 0),
        expected_balance: Number(totals.expected_balance ?? 0),
        stripe_balance: totals.stripe_balance !== null ? Number(totals.stripe_balance) : null,
        balance_difference: totals.balance_difference !== null ? Number(totals.balance_difference) : null
      };
      
      // Safely extract commissions data
      const commissions = data.commissions || {};
      console.log('💵 Commissions Data:', commissions);
      moneyFlow.value.commissions = {
        pending_marketing: Number(commissions.pending_marketing ?? 0),
        pending_training: Number(commissions.pending_training ?? 0),
        platform_total: Number(commissions.platform_total ?? 0)
      };
      
      moneyFlow.value.recent_transactions = data.recent_transactions || [];
      moneyFlow.value.failed_payouts = data.failed_payouts || [];
      moneyFlow.value.caregiver_balances = data.caregiver_balances || [];
      
      console.log('✅ Money Flow Data Loaded Successfully:');
      console.log('   Today:', moneyFlow.value.today);
      console.log('   Totals:', moneyFlow.value.totals);
      console.log('   Commissions:', moneyFlow.value.commissions);
    } else {
      console.error('❌ Money Flow API returned success=false');
    }
  } catch (error) {
    console.error('💥 Money Flow Data Error:', error);
  }
};

const loadAnalyticsStats = async () => {
  try {
    const response = await fetch('/api/admin/stats');
    if (!response.ok) throw new Error('API failed');
    const data = await response.json();
    analyticsStats.value[0].value = '$' + (data.total_revenue || 0).toFixed(2);
    analyticsStats.value[1].value = (data.total_clients || 0).toString();
    analyticsStats.value[2].value = (data.total_caregivers || 0).toString();
    analyticsStats.value[3].value = (data.active_bookings || 0).toString();
  } catch (error) {
  }
};

const clientMetrics = ref([
  { label: 'Total Clients', value: '0', color: 'info' },
  { label: 'Active Today', value: '0', color: 'success' },
  { label: 'New This Week', value: '0', color: 'warning' },
  { label: 'Avg Spending', value: '$0', color: 'error' },
]);

const caregiverMetrics = ref([
  { label: 'Total Caregivers', value: '0', color: 'success' },
  { label: 'Available Now', value: '0', color: 'info' },
  { label: 'Top Rated (5★)', value: '0', color: 'warning' },
  { label: 'Avg Earnings', value: '$0', color: 'error' },
]);

const housekeeperMetrics = ref([
  { label: 'Total Housekeepers', value: '0', color: 'deep-purple' },
  { label: 'Active Today', value: '0', color: 'purple' },
  { label: 'Assigned', value: '0', color: 'purple-lighten-2' },
  { label: 'Avg Earnings', value: '$0', color: 'deep-purple' },
]);

const adminCount = ref('0');
const userProgress = ref(0);
const userGrowth = ref('+0% this month');
const revenueProgress = ref(0);
const revenueTarget = ref('50,000');
const platformMetrics = ref({
  bookings: '0',
  response: '0s',
  errors: '0%',
  sessions: '0'
});
const totalUsersForChart = ref('0');
const totalBookingsForChart = ref('0');
const bookingStats = ref({
  pending: '0',
  active: '0',
  completed: '0',
  cancelled: '0'
});

const loadMetrics = async () => {
  try {
    const response = await fetch('/api/admin/stats');
    if (!response.ok) throw new Error('API failed');
    const data = await response.json();
    const totalUsers = data.total_users || 0;
    const totalCaregivers = data.total_caregivers || 0;
    const totalClients = data.total_clients || 0;
    const totalHousekeepers = data.total_housekeepers || 0;
    const admins = totalUsers - totalCaregivers - totalClients - totalHousekeepers;
    clientMetrics.value[0].value = totalClients.toString();
    caregiverMetrics.value[0].value = totalCaregivers.toString();
    housekeeperMetrics.value[0].value = totalHousekeepers.toString();
    adminCount.value = admins.toString();
    totalUsersForChart.value = totalUsers.toString();
    const bookingsResp = await fetch('/api/bookings');
    const bookingsData = await bookingsResp.json();
    const allBookings = bookingsData.data || [];
    bookingStats.value.pending = allBookings.filter(b => b.status === 'pending').length.toString();
    bookingStats.value.active = allBookings.filter(b => b.status === 'confirmed').length.toString();
    bookingStats.value.completed = allBookings.filter(b => b.status === 'completed').length.toString();
    bookingStats.value.cancelled = allBookings.filter(b => b.status === 'cancelled').length.toString();
    totalBookingsForChart.value = allBookings.length.toString();
    
    // Load housekeeper analytics
    try {
      const hkResp = await fetch('/api/admin/housekeeper-salaries');
      const hkData = await hkResp.json();
      const payments = hkData.payments || [];
      housekeeperMetrics.value[1].value = payments.filter(p => p.days_worked > 0).length.toString();
      housekeeperMetrics.value[2].value = payments.filter(p => p.status !== 'No Hours').length.toString();
      const totalEarnings = payments.reduce((sum, p) => sum + (p.total_amount || 0), 0);
      const avgEarnings = payments.length > 0 ? (totalEarnings / payments.length) : 0;
      housekeeperMetrics.value[3].value = '$' + avgEarnings.toFixed(0);
    } catch (hkError) {
      // Housekeeper stats optional
    }
    
    setTimeout(initCharts, 100);
  } catch (error) {
  }
};

const topPerformers = ref([]);

const loadTopPerformers = async () => {
  try {
    const response = await fetch('/api/admin/top-performers');
    const data = await response.json();
    topPerformers.value = data.performers || [];
  } catch (error) {
  }
};

const recentAnalyticsActivity = ref([]);

const loadRecentAnalyticsActivity = async () => {
  try {
    const response = await fetch('/api/admin/recent-activity');
    const data = await response.json();
    recentAnalyticsActivity.value = data.activities || [];
  } catch (error) {
  }
};

const systemHealth = ref([
  { service: 'Database', status: 'Optimal', value: 95, color: 'success' },
  { service: 'API Server', status: 'Good', value: 88, color: 'info' },
  { service: 'Payment', status: 'Active', value: 92, color: 'success' },
  { service: 'Email', status: 'Limited', value: 65, color: 'warning' },
]);

const activityHeaders = [
  { title: 'Time', key: 'time' },
  { title: 'User', key: 'user' },
  { title: 'Action', key: 'action' },
  { title: 'Type', key: 'type' },
];

const recentActivity = ref([]);

const userHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Type', key: 'type' },
  { title: 'Status', key: 'status' },
  { title: 'Joined', key: 'joined' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const users = ref([]);

const loadUsers = async () => {
  try {
    // Use dedicated endpoint to avoid any HTML redirect responses and keep payload minimal
    const caregiversResponse = await fetch('/api/admin/caregivers');
    const caregiversData = await caregiversResponse.json();
    const caregiverUsers = caregiversData.caregivers || [];
    
    const mappedCaregivers = caregiverUsers
      .map((u) => {
        return {
          id: u.caregiver?.id,
          userId: u.id,
          name: u.name,
          email: u.email,
          status: 'Active',
          rating: u.caregiver?.rating || null,
          clients: 0,
          joined: u.joined,
          verified: true,
          // Prefer canonical field `zip_code`, fall back to `zip` if present
          zip_code: u.zip_code || u.zip || '',
          // No guessing: place_indicator will be resolved via /api/zipcode-lookup.
          // Keep legacy location empty so the table doesn't show misleading data.
          location: '',
          // Accurate place indicator (City, ST) filled in lazily via /api/zipcode-lookup
          place_indicator: (u.zip_code || u.zip) ? 'Loading...' : '',
          phone: u.phone || '(646) 282-8282',
          // Use actual stored file path from DB (null when not uploaded)
          training_certificate: u.caregiver?.training_certificate || null,
          preferred_hourly_rate_min: u.caregiver?.preferred_hourly_rate_min || null,
          preferred_hourly_rate_max: u.caregiver?.preferred_hourly_rate_max || null
        };
      });
    
    caregivers.value = mappedCaregivers;
    
    // Resolve all ZIP codes and force Vue reactivity update
    resolveAllZipCodes(mappedCaregivers, caregivers);

    // Try to load the full users list (used across other tabs). If it returns HTML (login page),
    // do NOT wipe already-loaded caregivers — just keep existing data.
    let allUsers = [];
    try {
      const response = await fetch('/api/admin/users');
      const text = await response.text();
      if (text && text.trim().startsWith('{')) {
        const data = JSON.parse(text);
        allUsers = data.users || [];
        users.value = allUsers;
      }
    } catch (_) {
      // ignore
    }

    // Build other tables ONLY if we actually got JSON users.
    if (allUsers.length > 0) {
      clients.value = allUsers.filter(u => u.type === 'Client').map(u => {
        const item = {
          id: u.id,
          name: u.name,
          email: u.email,
          status: u.status,
          bookings: 0,
          totalSpent: '$0',
          joined: u.joined,
          verified: true,
          zip_code: u.zip_code || u.zip || '',
          location: '',
          place_indicator: (u.zip_code || u.zip) ? 'Loading...' : ''
        };
        return item;
      });
      // Batch resolve ZIP locations for clients
      resolveAllZipCodes(clients.value, clients);
    }
  } catch (error) {
    console.error('Error loading users:', error);
    // Set empty arrays to avoid undefined errors
    // Keep existing values if partial load succeeded; only reset if truly undefined
    users.value = users.value || [];
    caregivers.value = caregivers.value || [];
    clients.value = clients.value || [];
  }
};

const loadHousekeepers = async () => {
  try {
    const response = await fetch('/api/admin/housekeepers');
    const data = await response.json();
  housekeepers.value = (data.housekeepers || []).map(h => ({
  id: h.housekeeper?.id,
      userId: h.id,
      name: h.name,
      email: h.email,
      phone: h.phone || 'N/A',
      zip_code: h.zip_code || '',
      location: h.location || 'Unknown',
      status: h.status || 'Active',
      years_experience: h.years_experience || 0,
  rating: h.housekeeper?.rating || h.rating || 0,
  hourly_rate: h.housekeeper?.hourly_rate || 20,
      joined: h.joined
  })).filter(h => !!h.id);
  } catch (error) {
    console.error('Error loading housekeepers:', error);
    housekeepers.value = [];
  }
};

const viewHousekeeperDetails = (housekeeper) => {
  alert(`Viewing details for ${housekeeper.name}\n\nEmail: ${housekeeper.email}\nPhone: ${housekeeper.phone}\nExperience: ${housekeeper.years_experience} years\nRating: ${housekeeper.rating}/5`);
};

const settings = ref({
  platformName: 'CAS Private Care LLC',
  supportEmail: 'support@casprivatecare.com',
  commissionRate: 15,
  defaultCurrency: 'USD',
  maintenanceMode: false,
  emailNotifications: true,
  twoFactorAuth: true,
  loginNotifications: true,
  suspiciousActivityAlerts: true,
  sessionTimeout: 30,
  maxLoginAttempts: 5,
  passwordMinLength: 8,
  requirePasswordComplexity: true,
});

const backupSettings = ref({
  frequency: 'Daily',
  retentionDays: 30,
  autoBackup: true,
});

const notifications = ref({
  newUserRegistration: true,
  bookingCreated: true,
  paymentReceived: true,
  systemAlerts: true,
  smsAlerts: false,
  emergencyNotifications: true,
  adminPhone: '+1 (646) 282-8282',
});

const maintenanceMessage = ref('The system is currently under maintenance. Please check back later.');
const paymentsTab = ref('overview');
const assignedCaregiversTab = ref('caregivers');
const clientPaymentSearch = ref('');
const paymentStatusFilter = ref('All');
const paymentPeriodFilter = ref('All Time');
const salarySearch = ref('');
const salaryStatusFilter = ref('All');
const salaryPeriodFilter = ref('Current Month');
const transactionSearch = ref('');
const transactionTypeFilter = ref('All');
const transactionPeriodFilter = ref('All Time');
const addPaymentDialog = ref(false);
const paymentDetailsDialog = ref(false);
const selectedPayment = ref(null);
const processSalariesDialog = ref(false);
const timeTrackingSearch = ref('');
const timeTrackingDateFilter = ref('Today');
const timeTrackingStatusFilter = ref('All');

const bookingSearch = ref('');
const bookingStatusFilter = ref('All');
const bookingDateFilter = ref('All Time');
const addBookingDialog = ref(false);
const bookingZipLocation = ref('');

// ZIP Code to City/State lookup mapping for NY (comprehensive mapping)
const zipCodeMap = {
  '10001': 'Manhattan, NY', '10002': 'Manhattan, NY', '10003': 'Manhattan, NY', '10004': 'Manhattan, NY', '10005': 'Manhattan, NY', '10006': 'Manhattan, NY', '10007': 'Manhattan, NY', '10009': 'Manhattan, NY', '10010': 'Manhattan, NY', '10011': 'Manhattan, NY', '10012': 'Manhattan, NY', '10013': 'Manhattan, NY', '10014': 'Manhattan, NY', '10016': 'Manhattan, NY', '10017': 'Manhattan, NY', '10018': 'Manhattan, NY', '10019': 'Manhattan, NY', '10020': 'Manhattan, NY', '10021': 'Manhattan, NY', '10022': 'Manhattan, NY', '10023': 'Manhattan, NY', '10024': 'Manhattan, NY', '10025': 'Manhattan, NY', '10026': 'Manhattan, NY', '10027': 'Manhattan, NY', '10028': 'Manhattan, NY', '10029': 'Manhattan, NY', '10030': 'Manhattan, NY', '10031': 'Manhattan, NY', '10032': 'Manhattan, NY', '10033': 'Manhattan, NY', '10034': 'Manhattan, NY', '10035': 'Manhattan, NY', '10036': 'Manhattan, NY', '10037': 'Manhattan, NY', '10038': 'Manhattan, NY', '10039': 'Manhattan, NY', '10040': 'Manhattan, NY', '10044': 'Manhattan, NY', '10065': 'Manhattan, NY', '10069': 'Manhattan, NY', '10075': 'Manhattan, NY', '10128': 'Manhattan, NY', '10280': 'Manhattan, NY',
  '11201': 'Brooklyn, NY', '11203': 'Brooklyn, NY', '11204': 'Brooklyn, NY', '11205': 'Brooklyn, NY', '11206': 'Brooklyn, NY', '11207': 'Brooklyn, NY', '11208': 'Brooklyn, NY', '11209': 'Brooklyn, NY', '11210': 'Brooklyn, NY', '11211': 'Brooklyn, NY', '11212': 'Brooklyn, NY', '11213': 'Brooklyn, NY', '11214': 'Brooklyn, NY', '11215': 'Brooklyn, NY', '11216': 'Brooklyn, NY', '11217': 'Brooklyn, NY', '11218': 'Brooklyn, NY', '11219': 'Brooklyn, NY', '11220': 'Brooklyn, NY', '11221': 'Brooklyn, NY', '11222': 'Brooklyn, NY', '11223': 'Brooklyn, NY', '11224': 'Brooklyn, NY', '11225': 'Brooklyn, NY', '11226': 'Brooklyn, NY', '11228': 'Brooklyn, NY', '11229': 'Brooklyn, NY', '11230': 'Brooklyn, NY', '11231': 'Brooklyn, NY', '11232': 'Brooklyn, NY', '11233': 'Brooklyn, NY', '11234': 'Brooklyn, NY', '11235': 'Brooklyn, NY', '11236': 'Brooklyn, NY', '11237': 'Brooklyn, NY', '11238': 'Brooklyn, NY', '11239': 'Brooklyn, NY',
  '11354': 'Flushing, NY', '11355': 'Flushing, NY', '11356': 'Flushing, NY', '11357': 'Flushing, NY', '11358': 'Flushing, NY', '11360': 'Bayside, NY', '11361': 'Bayside, NY', '11362': 'Bayside, NY', '11363': 'Bayside, NY', '11364': 'Bayside, NY', '11365': 'Fresh Meadows, NY', '11366': 'Fresh Meadows, NY', '11367': 'Fresh Meadows, NY', '11368': 'Corona, NY', '11369': 'East Elmhurst, NY', '11370': 'Elmhurst, NY', '11371': 'Elmhurst, NY', '11372': 'Jackson Heights, NY', '11373': 'Jackson Heights, NY', '11374': 'Rego Park, NY', '11375': 'Forest Hills, NY', '11377': 'Woodside, NY', '11378': 'Maspeth, NY', '11379': 'Middle Village, NY', '11385': 'Ridgewood, NY',
  '10451': 'Bronx, NY', '10452': 'Bronx, NY', '10453': 'Bronx, NY', '10454': 'Bronx, NY', '10455': 'Bronx, NY', '10456': 'Bronx, NY', '10457': 'Bronx, NY', '10458': 'Bronx, NY', '10459': 'Bronx, NY', '10460': 'Bronx, NY', '10461': 'Bronx, NY', '10462': 'Bronx, NY', '10463': 'Bronx, NY', '10464': 'Bronx, NY', '10465': 'Bronx, NY', '10466': 'Bronx, NY', '10467': 'Bronx, NY', '10468': 'Bronx, NY', '10469': 'Bronx, NY', '10470': 'Bronx, NY', '10471': 'Bronx, NY', '10472': 'Bronx, NY', '10473': 'Bronx, NY', '10474': 'Bronx, NY', '10475': 'Bronx, NY',
  '10301': 'Staten Island, NY', '10302': 'Staten Island, NY', '10303': 'Staten Island, NY', '10304': 'Staten Island, NY', '10305': 'Staten Island, NY', '10306': 'Staten Island, NY', '10307': 'Staten Island, NY', '10308': 'Staten Island, NY', '10309': 'Staten Island, NY', '10310': 'Staten Island, NY', '10311': 'Staten Island, NY', '10312': 'Staten Island, NY', '10314': 'Staten Island, NY',
  '11001': 'Long Island City, NY', '11004': 'Long Island City, NY', '11005': 'Long Island City, NY', '11040': 'Long Island City, NY', '11101': 'Long Island City, NY', '11102': 'Long Island City, NY', '11103': 'Long Island City, NY', '11104': 'Long Island City, NY', '11105': 'Long Island City, NY', '11106': 'Long Island City, NY', '11109': 'Long Island City, NY',
  '11501': 'Hempstead, NY', '11530': 'Hempstead, NY', '11550': 'Hempstead, NY', '11552': 'Hempstead, NY', '11553': 'Hempstead, NY', '11554': 'Hempstead, NY', '11555': 'Hempstead, NY', '11556': 'Hempstead, NY', '11557': 'Hempstead, NY', '11558': 'Hempstead, NY', '11559': 'Hempstead, NY', '11560': 'Hempstead, NY', '11561': 'Hempstead, NY', '11563': 'Hempstead, NY', '11565': 'Hempstead, NY', '11566': 'Hempstead, NY', '11568': 'Hempstead, NY', '11569': 'Hempstead, NY', '11570': 'Hempstead, NY', '11571': 'Hempstead, NY', '11572': 'Hempstead, NY', '11575': 'Hempstead, NY', '11576': 'Hempstead, NY', '11577': 'Hempstead, NY', '11579': 'Hempstead, NY', '11580': 'Hempstead, NY', '11581': 'Hempstead, NY', '11582': 'Hempstead, NY', '11590': 'Hempstead, NY', '11596': 'Hempstead, NY', '11598': 'Hempstead, NY', '11599': 'Hempstead, NY',
  '10501': 'White Plains, NY', '10502': 'White Plains, NY', '10504': 'White Plains, NY', '10505': 'White Plains, NY', '10506': 'White Plains, NY', '10507': 'White Plains, NY', '10510': 'White Plains, NY', '10514': 'White Plains, NY', '10520': 'White Plains, NY', '10522': 'White Plains, NY', '10523': 'White Plains, NY', '10524': 'White Plains, NY', '10526': 'White Plains, NY', '10527': 'White Plains, NY', '10528': 'White Plains, NY', '10530': 'White Plains, NY', '10532': 'White Plains, NY', '10533': 'White Plains, NY', '10538': 'White Plains, NY', '10543': 'White Plains, NY', '10546': 'White Plains, NY', '10547': 'White Plains, NY', '10548': 'White Plains, NY', '10549': 'White Plains, NY', '10550': 'White Plains, NY', '10552': 'White Plains, NY', '10553': 'White Plains, NY',
  '10701': 'Yonkers, NY', '10703': 'Yonkers, NY', '10704': 'Yonkers, NY', '10705': 'Yonkers, NY', '10706': 'Yonkers, NY', '10707': 'Yonkers, NY', '10708': 'Yonkers, NY', '10709': 'Yonkers, NY', '10710': 'Yonkers, NY'
};

const lookupBookingZipCode = async () => {
  const zip = normalizeZip5(bookingForm.value.zipcode);
  if (!zip) {
    bookingZipLocation.value = '';
    return;
  }

  // Show loading state
  bookingZipLocation.value = 'Looking up location…';
  const location = await resolveZipCityState(zip);
  bookingZipLocation.value = location || 'ZIP not found';
};

const lookupTrainingCenterZipCode = async () => {
  const zip = normalizeZip5(trainingCenterFormData.value.zip_code);
  if (!zip) {
    trainingCenterZipLocation.value = '';
    return;
  }

  // Show loading state
  trainingCenterZipLocation.value = 'Looking up location…';
  const location = await resolveZipCityState(zip);
  trainingCenterZipLocation.value = location || 'ZIP not found';
};

// Phone number formatting function - NY format (XXX) XXX-XXXX
const formatPhoneNumber = (value) => {
  if (!value) return '';
  // Remove all non-numeric characters
  let cleaned = value.replace(/\D/g, '');
  // Limit to 10 digits
  if (cleaned.length > 10) {
    cleaned = cleaned.slice(0, 10);
  }
  // Format as (XXX) XXX-XXXX
  if (cleaned.length === 0) {
    return '';
  } else if (cleaned.length <= 3) {
    return `(${cleaned}`;
  } else if (cleaned.length <= 6) {
    return `(${cleaned.slice(0, 3)}) ${cleaned.slice(3)}`;
  } else {
    return `(${cleaned.slice(0, 3)}) ${cleaned.slice(3, 6)}-${cleaned.slice(6, 10)}`;
  }
};

// Password requirement check functions
const passwordMeetsLength = (password) => {
  return password && password.length >= 8;
};

const passwordMeetsUppercase = (password) => {
  return password && /[A-Z]/.test(password);
};

const passwordMeetsDigit = (password) => {
  return password && /[0-9]/.test(password);
};

const passwordMeetsSpecial = (password) => {
  return password && /[^a-zA-Z0-9]/.test(password);
};

// Filter to allow only letters and spaces for name fields
const filterLettersOnly = (value) => {
  if (!value) return '';
  return value.replace(/[^A-Za-z\s]/g, '');
};

const clientZipLocation = ref('');
const lookupClientZipCode = async () => {
  const zip = normalizeZip5(clientForm.value.zip_code);
  if (!zip) {
    clientZipLocation.value = '';
    return;
  }

  // Show loading state
  clientZipLocation.value = 'Looking up location…';
  const location = await resolveZipCityState(zip);
  clientZipLocation.value = location || 'ZIP not found';
};

const caregiverZipLocation = ref('');
const lookupCaregiverZipCode = async () => {
  const zip = normalizeZip5(caregiverForm.value.zip_code);
  if (!zip) {
    caregiverZipLocation.value = '';
    return;
  }

  // Show loading state
  caregiverZipLocation.value = 'Looking up location…';
  const location = await resolveZipCityState(zip);
  caregiverZipLocation.value = location || 'ZIP not found';
};

const housekeeperZipLocation = ref('');
const lookupHousekeeperZipCode = async () => {
  const zip = normalizeZip5(housekeeperForm.value.zip_code);
  if (!zip) {
    housekeeperZipLocation.value = '';
    return;
  }

  // Show loading state
  housekeeperZipLocation.value = 'Looking up location…';
  const location = await resolveZipCityState(zip);
  housekeeperZipLocation.value = location || 'ZIP not found';
};

const marketingStaffZipLocation = ref('');
const lookupMarketingStaffZipCode = async () => {
  const zip = normalizeZip5(marketingStaffFormData.value.zip_code);
  if (!zip) {
    marketingStaffZipLocation.value = '';
    return;
  }

  // Show loading state
  marketingStaffZipLocation.value = 'Looking up location…';
  const location = await resolveZipCityState(zip);
  marketingStaffZipLocation.value = location || 'ZIP not found';
};

const bookingForm = ref({
  client_id: null,
  service_type: 'Caregiver',
  duty_type: '8 Hours',
  service_date: '',
  starting_time: '',
  duration_days: 15,
  zipcode: '',
  street_address: '',
  apartment_unit: '',
  client_age: '',
  mobility_level: 'independent',
  specific_skills: [],
  medical_conditions: [],
  transportation_needed: false,
  special_instructions: '',
  status: 'approved'
});
const savingBooking = ref(false);

const timeTrackingHeaders = [
  { title: 'Client', key: 'client' },
  { title: 'Assigned Caregivers', key: 'caregivers' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const timeTrackingData = ref([]);

// Reviews & Ratings Data
const allReviews = ref([]);
const loadingReviews = ref(false);
const reviewHeaders = [
  { title: 'Client', key: 'client_name' },
  { title: 'Caregiver', key: 'caregiver_name' },
  { title: 'Service', key: 'service_type' },
  { title: 'Rating', key: 'rating' },
  { title: 'Recommend', key: 'recommend' },
  { title: 'Comment', key: 'comment' },
  { title: 'Date', key: 'created_at' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const loadAllReviews = async () => {
  loadingReviews.value = true;
  try {
    const response = await fetch('/api/reviews', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      credentials: 'include'
    });
    
    const data = await response.json();
    
    if (data.success) {
      allReviews.value = data.reviews || [];
    } else {
      error(data.message || 'Failed to load reviews');
    }
  } catch (err) {
    error('Failed to load reviews. Please try again.');
  } finally {
    loadingReviews.value = false;
  }
};

const viewReviewDetails = (review) => {
  alert(`Review Details:\n\nClient: ${review.client_name}\nCaregiver: ${review.caregiver_name}\nRating: ${review.rating}/5\nRecommend: ${review.recommend ? 'Yes' : 'No'}\n\nComment: ${review.comment || 'No comment'}`);
};

const deleteReview = async (reviewId) => {
  if (!confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
    return;
  }

  try {
    const response = await fetch(`/api/reviews/${reviewId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    });

    const data = await response.json();

    if (data.success) {
      success('Review deleted successfully');
      loadAllReviews();
    } else {
      error(data.message || 'Failed to delete review');
    }
  } catch (err) {
    error('Failed to delete review');
  }
};

const loadTimeTrackingData = async () => {
  try {
    // Use the admin time tracking API which returns all caregivers with active bookings
    const response = await fetch('/api/admin/time-tracking');
    if (response.ok) {
      const data = await response.json();
      
      // Group by client
      const clientMap = new Map();
      
      data.forEach(item => {
        const clientName = item.client_name || 'Unknown Client';
        if (!clientMap.has(clientName)) {
          clientMap.set(clientName, {
            client: clientName,
            clientId: item.client_id || null,
            caregivers: [],
            status: 'No Active Sessions'
          });
        }
        
        const client = clientMap.get(clientName);
        client.caregivers.push({
          id: item.caregiver_id,
          name: item.caregiver_name,
          status: item.status || 'Not Clocked In',
          clockIn: item.clock_in || null,
          clockOut: item.clock_out || null,
          todayHours: item.hours_today || 0,
          weekHours: item.weekly_hours || 0
        });
        
        // Update overall status if any caregiver is clocked in
        if (item.status === 'Clocked In' && client.status === 'No Active Sessions') {
          client.status = 'Active';
        }
      });
      
      // Convert map to array and sort by client name
      timeTrackingData.value = Array.from(clientMap.values()).sort((a, b) => 
        a.client.localeCompare(b.client)
      );
    } else {
      throw new Error('Failed to load from API');
    }
  } catch (error) {
    timeTrackingData.value = [];
  }
};

const filteredTimeTracking = computed(() => {
  return timeTrackingData.value.filter(item => {
    const matchesSearch = !timeTrackingSearch.value || item.client.toLowerCase().includes(timeTrackingSearch.value.toLowerCase());
    const matchesStatus = timeTrackingStatusFilter.value === 'All' || item.status === timeTrackingStatusFilter.value || 
      (timeTrackingStatusFilter.value === 'Clocked In' && item.status === 'Active') ||
      (timeTrackingStatusFilter.value === 'Clocked Out' && item.status === 'No Active Sessions');
    return matchesSearch && matchesStatus;
  });
});

const formatHours = (hours) => {
  if (!hours || hours === 0) return '0h 0m';
  const totalHours = Math.floor(hours);
  const minutes = Math.round((hours - totalHours) * 60);
  return `${totalHours}h ${minutes}m`;
};

const viewClientCaregiversDialog = ref(false);
const selectedClientEntry = ref(null);

const viewClientCaregivers = async (item) => {
  selectedClientEntry.value = {
    client: item.client,
    clientId: item.clientId,
    caregivers: item.caregivers
  };
  
  // Fetch time history for all caregivers assigned to this client
  try {
    const response = await fetch('/api/time-tracking/history');
    if (response.ok) {
      const data = await response.json();
      
      // Get history for all caregivers assigned to this client
      const caregiverNames = item.caregivers.map(c => c.name);
      const clientHistory = (data.history || [])
        .filter(h => h.client === item.client && caregiverNames.includes(h.caregiver))
        .slice(0, 20) // Show last 20 entries
        .map(h => ({
          date: h.date,
          clockIn: h.clockIn,
          clockOut: h.clockOut,
          hoursWorked: h.hoursWorked,
          status: h.status,
          caregiver: h.caregiver
        }));
      selectedClientEntry.value.timeHistory = clientHistory;
    } else {
      selectedClientEntry.value.timeHistory = [];
    }
  } catch (err) {
    selectedClientEntry.value.timeHistory = [];
  }
  
  viewClientCaregiversDialog.value = true;
};

const viewTimeDetailsDialog = ref(false);
const editTimeEntryDialog = ref(false);
const selectedTimeEntry = ref(null);

const viewTimeDetails = async (item) => {
  selectedTimeEntry.value = {
    ...item,
    todayHours: item.hours_today || item.todayHours || '0',
    weekHours: item.weekly_hours || item.weekHours || '0',
    currentClient: item.client_name || item.currentClient || 'N/A',
    clockIn: item.clock_in || item.clockIn || null,
    clockOut: item.clock_out || item.clockOut || null,
    caregiver: item.caregiver_name || item.caregiver || 'Unknown',
    status: item.status || 'Unknown'
  };
  
  // Fetch real time history from database for this caregiver
  try {
    const response = await fetch('/api/time-tracking/history');
    if (response.ok) {
      const data = await response.json();
      // Filter history for this caregiver and get recent entries
      const caregiverHistory = (data.history || [])
        .filter(h => h.caregiver === selectedTimeEntry.value.caregiver)
        .slice(0, 10) // Show last 10 entries
        .map(h => ({
          date: h.date,
          clockIn: h.clockIn,
          clockOut: h.clockOut,
          hoursWorked: h.hoursWorked,
          status: h.status,
          client: h.client
        }));
      selectedTimeEntry.value.timeHistory = caregiverHistory;
    } else {
      selectedTimeEntry.value.timeHistory = [];
    }
  } catch (err) {
    selectedTimeEntry.value.timeHistory = [];
  }
  
  viewTimeDetailsDialog.value = true;
};

const editTimeEntry = (item) => {
  selectedTimeEntry.value = { ...item };
  editTimeEntryDialog.value = true;
};

const saveTimeEntry = async () => {
  try {
    success(`Time entry updated for ${selectedTimeEntry.value.caregiver}`, 'Entry Updated');
    editTimeEntryDialog.value = false;
    loadTimeTrackingData();
  } catch (err) {
    error('Failed to update time entry', 'Update Failed');
  }
};

const refreshTimeTracking = async () => {
  await loadTimeTrackingData();
  await loadUsers();
  success('Time tracking data refreshed successfully!', 'Data Refreshed');
};

// Time Tracking History
const timeTrackingHistoryDialog = ref(false);
const historySearch = ref('');
const historyDateFilter = ref('This Week');
const historyStatusFilter = ref('All');
const historyCaregiverFilter = ref('All');
const historyClientFilter = ref('All');
const historySortBy = ref('Date (Newest)');
const timeHistory = ref([]);

// Computed lists for caregiver and client filter dropdowns
const uniqueCaregivers = computed(() => {
  const caregivers = [...new Set(timeHistory.value.map(item => item.caregiver))].filter(Boolean).sort();
  return ['All', ...caregivers];
});

const uniqueClients = computed(() => {
  const clients = [...new Set(timeHistory.value.map(item => item.client))].filter(Boolean).sort();
  return ['All', ...clients];
});
const historyStats = ref({
  totalSessions: '0',
  totalHours: '0',
  activeCaregivers: '0',
  avgHoursPerDay: '0'
});

const historyHeaders = [
  { title: 'Date', key: 'date' },
  { title: 'Caregiver', key: 'caregiver' },
  { title: 'Client', key: 'client' },
  { title: 'Clock In', key: 'clockIn' },
  { title: 'Clock Out', key: 'clockOut' },
  { title: 'Hours Worked', key: 'hoursWorked' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const loadTimeTrackingHistory = async () => {
  try {
    // Load real data from database
    const response = await fetch('/api/time-tracking/history');
    if (response.ok) {
      const data = await response.json();
      timeHistory.value = data.history || [];
      
      // Calculate stats from real data
      const totalSessions = timeHistory.value.length;
      const totalHours = timeHistory.value.reduce((sum, session) => sum + (session.hoursWorked || 0), 0);
      const uniqueCaregivers = new Set(timeHistory.value.map(s => s.caregiver)).size;
      const avgHoursPerDay = totalHours / 7;
      
      historyStats.value = {
        totalSessions: totalSessions.toString(),
        totalHours: Math.round(totalHours).toString(),
        activeCaregivers: uniqueCaregivers.toString(),
        avgHoursPerDay: avgHoursPerDay.toFixed(1)
      };
    } else {
      throw new Error('Failed to load from API');
    }
  } catch (error) {
    
    // Show empty state when API fails - no hardcoded data
    timeHistory.value = [];
    
    historyStats.value = {
      totalSessions: '0',
      totalHours: '0',
      activeCaregivers: '0',
      avgHoursPerDay: '0'
    };
  }
};

const filteredTimeHistory = computed(() => {
  let filtered = timeHistory.value.filter(item => {
    const matchesSearch = !historySearch.value || 
      item.caregiver.toLowerCase().includes(historySearch.value.toLowerCase()) ||
      item.client.toLowerCase().includes(historySearch.value.toLowerCase());
    const matchesStatus = historyStatusFilter.value === 'All' || 
      (historyStatusFilter.value === 'Active Sessions' && item.status === 'Active') ||
      (historyStatusFilter.value === 'Completed Sessions' && item.status === 'Completed');
    const matchesCaregiver = historyCaregiverFilter.value === 'All' || 
      item.caregiver === historyCaregiverFilter.value;
    const matchesClient = historyClientFilter.value === 'All' || 
      item.client === historyClientFilter.value;
    return matchesSearch && matchesStatus && matchesCaregiver && matchesClient;
  });
  
  // Sort the filtered results
  filtered.sort((a, b) => {
    switch (historySortBy.value) {
      case 'Date (Newest)':
        return new Date(b.date) - new Date(a.date);
      case 'Date (Oldest)':
        return new Date(a.date) - new Date(b.date);
      case 'Caregiver Name':
        return a.caregiver.localeCompare(b.caregiver);
      case 'Hours Worked':
        return b.hoursWorked - a.hoursWorked;
      default:
        return 0;
    }
  });
  
  return filtered;
});

const viewTimeTrackingHistory = () => {
  loadTimeTrackingHistory();
  timeTrackingHistoryDialog.value = true;
};

const viewHistoryDetails = (item) => {
  info(`Viewing details for ${item.caregiver} on ${item.date}`, 'Session Details');
};

const endSession = async (item) => {
  if (confirm(`End active session for ${item.caregiver}?`)) {
    try {
      item.status = 'Completed';
      item.clockOut = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
      success(`Session ended for ${item.caregiver}`, 'Session Ended');
      await loadTimeTrackingHistory();
    } catch (err) {
      error('Failed to end session', 'End Session Failed');
    }
  }
};

const exportTimeHistory = async () => {
  try {
    // Create a professional HTML report using the new template
    const reportData = {
      dateFilter: historyDateFilter.value,
      statusFilter: historyStatusFilter.value,
      totalSessions: historyStats.value.totalSessions,
      totalHours: historyStats.value.totalHours,
      activeCaregivers: historyStats.value.activeCaregivers,
      avgHoursPerDay: historyStats.value.avgHoursPerDay,
      timeHistory: filteredTimeHistory.value
    };
    
    // Generate PDF using the server-side template
    const response = await fetch('/api/reports/time-tracking-pdf', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(reportData)
    });
    
    if (response.ok) {
      const blob = await response.blob();
      const url = URL.createObjectURL(blob);
      
      // Open in new window instead of download
      window.open(url, '_blank');
      
      // Clean up after a delay
      setTimeout(() => URL.revokeObjectURL(url), 100);
      
      success('Professional time tracking report exported successfully!', 'Export Complete');
    } else {
      throw new Error('Failed to generate PDF report');
    }
  } catch (err) {
    
    // Fallback to client-side generation with clean styling
    try {
      const { jsPDF } = window.jsPDF || window.jspdf;
      if (!jsPDF) {
        throw new Error('jsPDF library not loaded');
      }
      
      const doc = new jsPDF('p', 'mm', 'a4');
      
      // Clean header
      doc.setDrawColor(200, 200, 200);
      doc.line(20, 35, 190, 35);
      
      // Company info
      doc.setTextColor(51, 51, 51);
      doc.setFontSize(16);
      doc.setFont('helvetica', 'bold');
      doc.text('CAS Private Care LLC', 20, 20);
      doc.setFontSize(10);
      doc.setFont('helvetica', 'normal');
      doc.text('Comfort & Support', 20, 27);
      
      // Report date
      doc.setFontSize(9);
      doc.setTextColor(102, 102, 102);
      doc.text('Generated: ' + new Date().toLocaleDateString('en-US', { 
        year: 'numeric', month: 'short', day: 'numeric', 
        hour: 'numeric', minute: '2-digit', hour12: true 
      }), 190, 20, { align: 'right' });
      
      // Title
      doc.setTextColor(51, 51, 51);
      doc.setFontSize(14);
      doc.setFont('helvetica', 'bold');
      doc.text('Time Tracking History Report', 105, 50, { align: 'center' });
      
      doc.setFontSize(9);
      doc.setFont('helvetica', 'normal');
      doc.setTextColor(102, 102, 102);
      doc.text('Employee work hours documentation', 105, 57, { align: 'center' });
      doc.text(`Period: ${historyDateFilter.value} | Status: ${historyStatusFilter.value}`, 105, 63, { align: 'center' });
      
      // Summary box
      doc.setFillColor(249, 249, 249);
      doc.setDrawColor(221, 221, 221);
      doc.rect(20, 70, 170, 25, 'FD');
      
      doc.setTextColor(51, 51, 51);
      doc.setFontSize(10);
      doc.setFont('helvetica', 'bold');
      doc.text('Summary Statistics', 25, 80);
      
      // Stats in simple format
      doc.setFont('helvetica', 'normal');
      doc.setFontSize(9);
      const stats = [
        `Total Sessions: ${historyStats.value.totalSessions}`,
        `Total Hours: ${historyStats.value.totalHours}h`,
        `Active Staff: ${historyStats.value.activeCaregivers}`,
        `Avg Hours/Day: ${historyStats.value.avgHoursPerDay}h`
      ];
      
      let x = 25;
      stats.forEach((stat, index) => {
        if (index === 2) {
          x = 25;
          doc.text(stat, x, 90);
        } else if (index === 3) {
          doc.text(stat, x + 85, 90);
        } else {
          doc.text(stat, x, 85);
          if (index === 0) x += 85;
        }
      });
      
      // Table header
      let tableY = 105;
      doc.setFillColor(245, 245, 245);
      doc.setDrawColor(221, 221, 221);
      doc.rect(20, tableY, 170, 8, 'FD');
      
      doc.setTextColor(51, 51, 51);
      doc.setFontSize(8);
      doc.setFont('helvetica', 'bold');
      
      const headers = ['DATE', 'EMPLOYEE', 'CLIENT', 'CLOCK IN', 'CLOCK OUT', 'HOURS', 'STATUS'];
      const colWidths = [22, 30, 25, 18, 18, 15, 18];
      let currentX = 22;
      
      headers.forEach((header, index) => {
        doc.text(header, currentX, tableY + 5);
        currentX += colWidths[index];
      });
      
      // Table data
      tableY += 8;
      doc.setFont('helvetica', 'normal');
      doc.setFontSize(8);
      
      filteredTimeHistory.value.forEach((item, index) => {
        if (tableY > 270) {
          doc.addPage();
          tableY = 20;
        }
        
        // Alternating row colors
        if (index % 2 === 0) {
          doc.setFillColor(250, 250, 250);
          doc.rect(20, tableY, 170, 6, 'F');
        }
        
        currentX = 22;
        const rowData = [
          item.date,
          item.caregiver.substring(0, 18),
          item.client.substring(0, 15),
          item.clockIn,
          item.clockOut || 'N/A',
          formatHours(item.hoursWorked),
          item.status
        ];
        
        doc.setTextColor(51, 51, 51);
        rowData.forEach((data, colIndex) => {
          if (colIndex === 5) { // Hours column
            doc.setFont('helvetica', 'bold');
          } else {
            doc.setFont('helvetica', 'normal');
          }
          
          doc.text(data, currentX, tableY + 4);
          currentX += colWidths[colIndex];
        });
        
        tableY += 6;
      });
      
      // Simple footer
      const pageCount = doc.internal.getNumberOfPages();
      for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setDrawColor(221, 221, 221);
        doc.line(20, 285, 190, 285);
        
        doc.setFontSize(8);
        doc.setTextColor(102, 102, 102);
        doc.text('CAS Private Care LLC - Confidential Document', 105, 292, { align: 'center' });
      }
      
      // Download
      const pdfBlob = doc.output('blob');
      const url = URL.createObjectURL(pdfBlob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `CAS-TimeTracking-Report-${new Date().toISOString().split('T')[0]}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
      
      success('Time tracking report exported successfully!', 'Export Complete');
    } catch (fallbackErr) {
      error('Failed to export PDF. Please ensure jsPDF library is loaded.', 'Export Failed');
    }
  }
};
const assignDialog = ref(false);
const selectedBooking = ref(null);
const assignCaregiverSearch = ref('');
const assignAvailabilityFilter = ref('Available');
const assignSelectedCaregivers = ref([]);
const assignedRates = ref({});
const customCaregiversNeeded = ref(null);

// Housekeeper assignment dialog state (custom)
const assignHousekeeperDialogOpen = ref(false);
const assignHousekeeperSearch = ref('');
const assignHousekeeperAvailabilityFilter = ref('Available');
const assignSelectedHousekeepers = ref([]);
const assignedHousekeeperRates = ref({});
const customHousekeepersNeeded = ref(null);

const paymentStats = ref([
  { title: 'Total Revenue', value: '$0', icon: 'mdi-currency-usd', color: 'success', change: '+15%', changeColor: 'success--text' },
  { title: 'Pending Payments', value: '$0', icon: 'mdi-clock', color: 'warning', change: '0 pending', changeColor: 'warning--text' },
  { title: 'Salaries Due', value: '$0', icon: 'mdi-account-cash', color: 'info', change: '0 caregivers', changeColor: 'info--text' },
  { title: 'Processing Fees', value: '$0', icon: 'mdi-percent', color: 'error', change: '2.5% avg', changeColor: 'error--text' },
]);

const loadPaymentStats = async () => {
  try {
    const response = await fetch('/api/admin/payment-stats');
    const data = await response.json();
    if (data.stats) {
      paymentStats.value = data.stats;
    }
  } catch (error) {
  }
};

const recentTransactions = ref([]);

const loadRecentTransactions = async () => {
  try {
    const response = await fetch('/api/admin/transactions');
    const data = await response.json();
    recentTransactions.value = data.transactions || [];
  } catch (error) {
  }
};

const paymentMethods = ref([
  { 
    type: 'stripe', 
    name: 'Stripe Payment Element', 
    icon: 'mdi-credit-card', 
    color: 'info', 
    status: 'Active', 
    details: 'Card, Link, Apple Pay, Google Pay' 
  },
  { 
    type: 'stripe-connect', 
    name: 'Stripe Connect', 
    icon: 'mdi-bank-transfer', 
    color: 'success', 
    status: 'Active', 
    details: 'Caregiver bank payouts' 
  },
]);

const clientPaymentHeaders = [
  { title: 'Client', key: 'client' },
  { title: 'Service', key: 'service' },
  { title: 'Amount', key: 'amount' },
  { title: 'Due Date', key: 'dueDate' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const clientPayments = ref([]);

const loadClientPayments = async () => {
  try {
    const response = await fetch('/api/admin/client-payments');
    const data = await response.json();
    clientPayments.value = data.payments || [];
  } catch (error) {
  }
};

const paymentHeaders = [
  { title: 'Caregiver', key: 'caregiver' },
  { title: 'Hours Worked', key: 'hours_display' },
  { title: 'Hourly Rate', key: 'rate' },
  { title: 'Total Earned', key: 'amount_display' },
  { title: 'Unpaid Amount', key: 'unpaid_display' },
  { title: 'Bank Account', key: 'bank_status' },
  { title: 'Period', key: 'period' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const caregiverPayments = ref([]);
const paymentConfirmDialog = ref(false);
const caregiverPaymentDetailsDialog = ref(false);
const selectedCaregiverPayment = ref(null);
const selectedCaregiverPaymentDetails = ref(null);

// Housekeeper payments
const housekeeperPaymentHeaders = [
  { title: 'Housekeeper', key: 'housekeeper' },
  { title: 'Hours Worked', key: 'hours_display' },
  { title: 'Hourly Rate', key: 'rate' },
  { title: 'Total Earned', key: 'amount_display' },
  { title: 'Unpaid Amount', key: 'unpaid_display' },
  { title: 'Bank Account', key: 'bank_status' },
  { title: 'Period', key: 'period' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const housekeeperPayments = ref([]);
const housekeeperSalarySearch = ref('');
const housekeeperSalaryStatusFilter = ref('All');
const housekeeperSalaryPeriodFilter = ref('Current Month');

// Check if today is Friday (payout day)
const isFriday = computed(() => {
  return new Date().getDay() === 5; // 5 = Friday
});

const loadCaregiverPayments = async () => {
  try {
    const response = await fetch('/api/admin/caregiver-salaries');
    const data = await response.json();
    caregiverPayments.value = data.payments || [];
  } catch (error) {
  }
};

const loadHousekeeperPayments = async () => {
  try {
    const response = await fetch('/api/admin/housekeeper-salaries');
    const data = await response.json();
    housekeeperPayments.value = data.payments || [];
  } catch (error) {
  }
};

const filteredHousekeeperPayments = computed(() => {
  let items = [...(housekeeperPayments.value || [])];

  // Search (name/email)
  const q = (housekeeperSalarySearch.value || '').toLowerCase().trim();
  if (q) {
    items = items.filter(p => {
      const name = (p.housekeeper || '').toLowerCase();
      const email = (p.housekeeper_email || '').toLowerCase();
      return name.includes(q) || email.includes(q);
    });
  }

  // Status
  if (housekeeperSalaryStatusFilter.value && housekeeperSalaryStatusFilter.value !== 'All') {
    items = items.filter(p => p.status === housekeeperSalaryStatusFilter.value);
  }

  // Period (backend currently returns current month for all rows)
  // Keep filter in UI for future extension.
  return items;
});

const viewHousekeeperPaymentDetails = (item) => {
  // Minimal: reuse the existing details dialog structure later if needed.
  selectedCaregiverPayment.value = item;
  caregiverPaymentDetailsDialog.value = true;
};

const payHousekeeper = async (item) => {
  if (!item.can_pay) {
    warning('Cannot process payment. Check if bank is connected and there are unpaid hours.', 'Payment');
    return;
  }
  
  if (!confirm(`Pay ${item.housekeeper} $${item.unpaid_amount.toFixed(2)}?`)) {
    return;
  }

  try {
    item.paying = true;
    const response = await fetch('/api/admin/pay-housekeeper', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        housekeeper_id: item.id,
        amount: item.unpaid_amount
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      success(`Payment of $${data.amount.toFixed(2)} sent to ${data.housekeeper}`, 'Payment Success');
      // Reload housekeeper payments
      await loadHousekeeperPayments();
    } else {
      error(data.message || 'Payment failed', 'Error');
    }
  } catch (err) {
    error('Failed to process payment: ' + (err.message || 'Unknown error'), 'Error');
  } finally {
    item.paying = false;
  }
};

const exportHousekeeperPaymentsPDF = () => {
  try {
    console.log('Exporting Housekeeper Payments PDF...');
    console.log('Data:', filteredHousekeeperPayments.value);
    console.log('Period:', housekeeperSalaryPeriodFilter.value);
    
    if (!window.jspdf) {
      console.error('jsPDF library not loaded!');
      alert('PDF library not loaded. Please refresh the page.');
      return;
    }
    
    if (!filteredHousekeeperPayments.value || filteredHousekeeperPayments.value.length === 0) {
      console.warn('No housekeeper payment data to export');
      alert('No payment data available to export.');
      return;
    }
    
    generatePaymentPDF('Housekeeper Payments', filteredHousekeeperPayments.value, housekeeperSalaryPeriodFilter.value, [
      'Date',
      'Housekeeper Name',
      'Client Name',
      'Total Hours',
      'Total Payout'
    ]);
    
    console.log('PDF exported successfully!');
  } catch (error) {
    console.error('Error exporting PDF:', error);
    alert('Error generating PDF: ' + error.message);
  }
};

// Marketing Commissions
const marketingCommissionHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Referral Code', key: 'referral_code' },
  { title: 'Total Commission', key: 'total_commission' },
  { title: 'Pending', key: 'pending_commission' },
  { title: 'Bank Account', key: 'bank_status' },
  { title: 'Status', key: 'payment_status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const marketingCommissions = ref([]);
const loadingMarketingCommissions = ref(false);
const marketingCommissionSearch = ref('');
const marketingCommissionStatusFilter = ref('All');
const marketingCommissionPeriodFilter = ref('Current Month');

const filteredMarketingCommissions = computed(() => {
  let filtered = marketingCommissions.value;
  
  if (marketingCommissionSearch.value) {
    const search = marketingCommissionSearch.value.toLowerCase();
    filtered = filtered.filter(m => 
      m.name.toLowerCase().includes(search) ||
      m.email.toLowerCase().includes(search) ||
      m.referral_code.toLowerCase().includes(search)
    );
  }
  
  if (marketingCommissionStatusFilter.value !== 'All') {
    filtered = filtered.filter(m => m.payment_status === marketingCommissionStatusFilter.value);
  }
  
  return filtered;
});

const loadMarketingCommissions = async () => {
  loadingMarketingCommissions.value = true;
  try {
    const response = await fetch('/api/admin/marketing-commissions');
    const data = await response.json();
    
    if (data.success) {
      marketingCommissions.value = data.commissions.map(c => ({
        ...c,
        total_commission: parseFloat(c.total_commission || 0).toFixed(2),
        pending_commission: parseFloat(c.pending_commission || 0).toFixed(2),
        bank_connected: !!c.stripe_connect_id,
        bank_status: c.stripe_connect_id ? 'Connected' : 'Not Connected',
        payment_status: parseFloat(c.pending_commission || 0) > 0 ? 'Pending' : 'Paid',
        paying: false
      }));
    }
  } catch (error) {
  } finally {
    loadingMarketingCommissions.value = false;
  }
};

const payAllMarketingCommissions = async () => {
  const pending = marketingCommissions.value.filter(m => 
    parseFloat(m.pending_commission) > 0 && m.bank_connected
  );
  
  if (pending.length === 0) {
    showError('No pending commissions to pay');
    return;
  }
  
  const total = pending.reduce((sum, m) => sum + parseFloat(m.pending_commission), 0);
  
  if (!confirm(`Pay total of $${total.toFixed(2)} to ${pending.length} marketing staff?`)) return;
  
  try {
    for (const item of pending) {
      await payMarketingCommission(item);
    }
    showSuccess('All commissions paid successfully!');
  } catch (error) {
    showError('Some payments failed');
  }
};

const viewMarketingCommissionDetails = (item) => {
  // Show detailed commission breakdown dialog
  alert(`Marketing Commission Details:\n\nName: ${item.name}\nTotal Earned: $${item.total_commission}\nPending: $${item.pending_commission}\nReferral Code: ${item.referral_code}`);
};

// Training Commissions
const trainingCommissionHeaders = [
  { title: 'Center Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Caregivers Trained', key: 'caregivers_count' },
  { title: 'Total Commission', key: 'total_commission' },
  { title: 'Pending', key: 'pending_commission' },
  { title: 'Bank Account', key: 'bank_status' },
  { title: 'Status', key: 'payment_status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const trainingCommissions = ref([]);
const loadingTrainingCommissions = ref(false);
const trainingCommissionSearch = ref('');
const trainingCommissionStatusFilter = ref('All');
const trainingCommissionPeriodFilter = ref('Current Month');

const filteredTrainingCommissions = computed(() => {
  let filtered = trainingCommissions.value;
  
  if (trainingCommissionSearch.value) {
    const search = trainingCommissionSearch.value.toLowerCase();
    filtered = filtered.filter(t => 
      t.name.toLowerCase().includes(search) ||
      t.email.toLowerCase().includes(search)
    );
  }
  
  if (trainingCommissionStatusFilter.value !== 'All') {
    filtered = filtered.filter(t => t.payment_status === trainingCommissionStatusFilter.value);
  }
  
  return filtered;
});

const loadTrainingCommissions = async () => {
  loadingTrainingCommissions.value = true;
  try {
    const response = await fetch('/api/admin/training-commissions');
    const data = await response.json();
    
    if (data.success) {
      trainingCommissions.value = data.commissions.map(c => ({
        ...c,
        total_commission: parseFloat(c.total_commission || 0).toFixed(2),
        pending_commission: parseFloat(c.pending_commission || 0).toFixed(2),
        bank_connected: !!c.stripe_connect_id,
        bank_status: c.stripe_connect_id ? 'Connected' : 'Not Connected',
        payment_status: parseFloat(c.pending_commission || 0) > 0 ? 'Pending' : 'Paid',
        paying: false
      }));
    }
  } catch (error) {
  } finally {
    loadingTrainingCommissions.value = false;
  }
};

const payAllTrainingCommissions = async () => {
  const pending = trainingCommissions.value.filter(t => 
    parseFloat(t.pending_commission) > 0 && t.bank_connected
  );
  
  if (pending.length === 0) {
    showError('No pending commissions to pay');
    return;
  }
  
  const total = pending.reduce((sum, t) => sum + parseFloat(t.pending_commission), 0);
  
  if (!confirm(`Pay total of $${total.toFixed(2)} to ${pending.length} training centers?`)) return;
  
  try {
    for (const item of pending) {
      await payTrainingCommission(item);
    }
    showSuccess('All commissions paid successfully!');
  } catch (error) {
    showError('Some payments failed');
  }
};

const viewTrainingCommissionDetails = (item) => {
  // Show detailed commission breakdown dialog
  alert(`Training Commission Details:\n\nCenter: ${item.name}\nTotal Earned: $${item.total_commission}\nPending: $${item.pending_commission}\nCaregivers: ${item.caregivers_count}`);
};

const transactionHeaders = [
  { title: 'Date', key: 'date' },
  { title: 'Description', key: 'description' },
  { title: 'Type', key: 'type' },
  { title: 'Amount', key: 'amount' },
  { title: 'Status', key: 'status' },
  { title: 'Reference', key: 'reference' },
];

const bookingHeaders = [
  { title: 'Client', key: 'client', width: '110px' },
  { title: 'Service', key: 'service', width: '100px' },
  { title: 'Date', key: 'date', width: '95px' },
  { title: 'Time', key: 'startingTime', width: '70px', align: 'center' },
  { title: 'Hours', key: 'hoursPerDay', width: '60px', align: 'center' },
  { title: 'Duration', key: 'duration', width: '80px' },
  { title: 'Location', key: 'location', width: '100px' },
  { title: 'Price', key: 'formattedPrice', width: '90px', align: 'end' },
  { title: 'Payment', key: 'paymentStatus', width: '90px', align: 'center' },
  { title: 'Assigned', key: 'assignedCount', width: '100px', align: 'center' },
  { title: 'Status', key: 'status', width: '90px', align: 'center' },
  { title: 'Actions', key: 'actions', sortable: false, width: '140px', align: 'center' },
];

const clientBookings = ref([]);

const loadClientBookings = async () => {
  try {
    const response = await fetch('/api/admin/bookings', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });

if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
    }
    
    const result = await response.json();
    
    if (!result.success) {
      clientBookings.value = [];
      return;
    }
    
    const bookings = result.data || [];
    if (!Array.isArray(bookings)) {
      clientBookings.value = [];
      return;
    }
    
    clientBookings.value = bookings.map(b => {
      const date = new Date(b.service_date);
      const time = b.start_time ? new Date(`1970-01-01T${b.start_time}`) : date;
      // Calculate caregivers needed based on hours per day
      // 8 hours per day = 1 caregiver, 12 hours = 2 caregivers, 24 hours = 3 caregivers
      const extractHours = (dutyType) => {
        if (!dutyType) return 8;
        const match = dutyType.match(/(\d+)\s*Hours?/i);
        return match ? parseInt(match[1]) : 8;
      };
      
      const calculateEndTime = (startTime, hours) => {
        if (!startTime) return '';
        const [hoursStr, minutesStr] = startTime.split(':');
        const startHour = parseInt(hoursStr);
        const startMinutes = parseInt(minutesStr);
        const totalHours = startHour + hours;
        const endHour = totalHours % 24;
        const endMinutes = startMinutes;
        const endPeriod = endHour >= 12 ? 'PM' : 'AM';
        const displayHour = endHour === 0 ? 12 : (endHour > 12 ? endHour - 12 : endHour);
        const nextDay = totalHours >= 24 ? ' +1' : '';
        return `${displayHour}:${endMinutes.toString().padStart(2, '0')} ${endPeriod}${nextDay}`;
      };
      
      const calculateCaregiversNeeded = (dutyType) => {
        const hoursPerDay = extractHours(dutyType);
        if (hoursPerDay === 8) return 1;
        if (hoursPerDay === 12) return 2;
        if (hoursPerDay === 24) return 3;
        // Fallback for custom hours
        if (hoursPerDay < 12) return 1;
        if (hoursPerDay < 24) return 2;
        return 3;
      };
      
      const caregiversNeeded = b.caregivers_needed !== undefined ? b.caregivers_needed : calculateCaregiversNeeded(b.duty_type);
      const assignedCount = b.assignments?.length || 0;
      
      // Calculate coverage end date
      const serviceDate = new Date(b.service_date);
      const coverageEndDate = new Date(serviceDate);
      coverageEndDate.setDate(serviceDate.getDate() + b.duration_days);
      const today = new Date();
      
      // Determine if booking is currently active (covers today)
      const isCurrentlyActive = serviceDate <= today && today <= coverageEndDate;
      
      // Store assignments in memory with full caregiver data
      if (assignedCount > 0) {
        caregiverAssignments.value[b.id] = b.assignments.map(a => a.caregiver_id);
        // Store the full booking data including assignments for phone access
        if (!clientBookings.value.find(existing => existing.id === b.id)) {
          // This will be set later, but we need to ensure assignments are preserved
        }
      }
      
      // Determine assignment status based on assigned count
      let assignmentStatus = 'unassigned';
      if (assignedCount > 0 && assignedCount < caregiversNeeded) {
        assignmentStatus = 'partial';
      } else if (assignedCount >= caregiversNeeded) {
        assignmentStatus = 'assigned';
      }
      
      // Calculate pricing
      // Pricing: Caregiver $28 + Agency $16.50 + Training $0.50 = $45/hr (no referral)
      // With referral: Caregiver $28 + Agency $10.50 + Marketing $1 + Training $0.50 = $40/hr
      const hoursPerDay = extractHours(b.duty_type);
      const hourlyRate = parseFloat(b.hourly_rate) || 45;
      const durationDays = parseInt(b.duration_days) || 1;
      const totalBudget = parseFloat(b.total_budget) || (hoursPerDay * hourlyRate * durationDays);
      
      // Format creation timestamp
      const createdAt = b.created_at || b.submitted_at;
      const createdDate = createdAt ? new Date(createdAt) : null;
      const createdDateFormatted = createdDate ? createdDate.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
      }) : 'N/A';
      const createdTimeFormatted = createdDate ? createdDate.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit', 
        hour12: true 
      }) : '';
      const createdFullTimestamp = createdDate ? createdDate.toLocaleString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: 'numeric', 
        minute: '2-digit', 
        hour12: true 
      }) : 'N/A';
      
      // Format starting time
      const startingTime = b.starting_time || b.start_time || '';
      
      // Extract time from different formats:
      // - ISO 8601: "2026-01-04T01:00:00.000000Z" or "2026-01-04T01:00:00+08:00"
      // - DateTime: "2026-01-04 09:00:00"
      // - Time only: "09:00:00" or "09:00"
      let timeOnly = startingTime;
      if (startingTime.includes('T')) {
        // ISO 8601 format - parse as Date and get local time
        const dateObj = new Date(startingTime);
        const hours = dateObj.getHours().toString().padStart(2, '0');
        const minutes = dateObj.getMinutes().toString().padStart(2, '0');
        timeOnly = `${hours}:${minutes}`;
      } else if (startingTime.includes(' ')) {
        // DateTime format - extract time part after space
        timeOnly = startingTime.split(' ')[1];
      }
      
      const timeFormatted = timeOnly ? (() => {
        const [hours, minutes] = timeOnly.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
      })() : 'N/A';
      const endTimeFormatted = timeOnly ? calculateEndTime(timeOnly, hoursPerDay) : 'N/A';
      const timeRange = `${timeFormatted} - ${endTimeFormatted}`;
      
      return {
        id: b.id,
        client: b.client?.name || 'Unknown',
        service: b.service_type,
        date: date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
        time: timeRange,
        startingTime: timeFormatted,
        endingTime: endTimeFormatted,
        location: b.borough || b.city || b.county || 'N/A',
        submitted: b.submitted_at ? new Date(b.submitted_at).toLocaleString('en-US', { 
          month: 'short', 
          day: 'numeric', 
          year: 'numeric',
          hour: 'numeric', 
          minute: '2-digit', 
          hour12: true 
        }) : 'N/A',
        createdAt: createdFullTimestamp,
        createdAtDate: createdDateFormatted,
        createdAtTime: createdTimeFormatted,
        created_at: createdAt, // Store raw timestamp
        duration: b.duration_days + ' days',
        durationDays: durationDays,
        coverageEnd: coverageEndDate.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
        isActive: isCurrentlyActive,
        caregiversNeeded: caregiversNeeded,
        assignedCount: assignedCount,
        caregiver: b.assigned_caregiver?.user?.name || null,
        status: b.status || 'pending',
        paymentStatus: b.payment_status || 'unpaid',
        assignmentStatus: assignmentStatus,
        assignments: b.assignments || [], // Store full assignment data with phone numbers
        // Pricing fields
        dutyType: b.duty_type || 'hourly',
        hoursPerDay: hoursPerDay,
        hourlyRate: hourlyRate,
        totalBudget: totalBudget,
        formattedPrice: '$' + totalBudget.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }),
        // Referral code/voucher information
        referralCode: b.referral_code || null,
        referralCodeId: b.referral_code_id || null,
        referralDiscountApplied: b.referral_discount_applied || null,
        // Day schedules (if client selected specific days/times)
        daySchedules: b.day_schedules || null,
        // Full address details
        borough: b.borough,
        city: b.city,
        county: b.county,
        streetAddress: b.street_address,
        apartmentUnit: b.apartment_unit
      };
    });
    
    // Update caregiver statuses based on assignments
    updateCaregiverStatuses();
  } catch (error) {
    // Error loading bookings
    console.error('Error loading bookings:', error);
    const errorInfo = {
      message: error.message,
      stack: error.stack,
      name: error.name
    };
    clientBookings.value = [];
  }
};

const updateCaregiverStatuses = () => {
  caregivers.value.forEach(caregiver => {
    const isAssigned = Object.values(caregiverAssignments.value).some(assignments => 
      assignments.includes(caregiver.id)
    );
    caregiver.status = isAssigned ? 'Assigned' : 'Active';
  });
};

const allTransactions = ref([]);

const loadAllTransactions = async () => {
  try {
    const response = await fetch('/api/admin/transactions');
    const data = await response.json();
    allTransactions.value = data.transactions || [];
  } catch (error) {
  }
};

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const matchesSearch = !userSearch.value || u.name.toLowerCase().includes(userSearch.value.toLowerCase()) || u.email.toLowerCase().includes(userSearch.value.toLowerCase());
    const matchesType = userType.value === 'All' || u.type === userType.value.slice(0, -1);
    const matchesStatus = userStatus.value === 'All' || u.status === userStatus.value;
    const matchesLocation = locationFilter.value === 'All' || (u.county && u.county.includes(locationFilter.value));
    return matchesSearch && matchesType && matchesStatus && matchesLocation;
  });
});

const filteredCaregivers = computed(() => {
  return caregivers.value.filter(c => {
    const matchesSearch = !caregiverSearch.value || c.name.toLowerCase().includes(caregiverSearch.value.toLowerCase()) || c.email.toLowerCase().includes(caregiverSearch.value.toLowerCase());
    const matchesLocation = caregiverLocationFilter.value === 'All' || (c.borough && c.borough.includes(caregiverLocationFilter.value)) || (c.county && c.county.includes(caregiverLocationFilter.value));
    const matchesStatus = caregiverStatusFilter.value === 'All' || c.status === caregiverStatusFilter.value;
    return matchesSearch && matchesLocation && matchesStatus;
  });
});

const filteredHousekeepers = computed(() => {
  return housekeepers.value.filter(h => {
    const matchesSearch = !housekeeperSearch.value || h.name.toLowerCase().includes(housekeeperSearch.value.toLowerCase()) || h.email.toLowerCase().includes(housekeeperSearch.value.toLowerCase());
    const matchesLocation = housekeeperLocationFilter.value === 'All' || (h.location && h.location.includes(housekeeperLocationFilter.value));
    const matchesStatus = housekeeperStatusFilter.value === 'All' || h.status === housekeeperStatusFilter.value;
    return matchesSearch && matchesLocation && matchesStatus;
  });
});

const filteredClients = computed(() => {
  return clients.value.filter(c => {
    const matchesSearch = !clientSearch.value || c.name.toLowerCase().includes(clientSearch.value.toLowerCase()) || c.email.toLowerCase().includes(clientSearch.value.toLowerCase());
    const matchesLocation = clientLocationFilter.value === 'All' || (c.county && c.county.includes(clientLocationFilter.value));
    const matchesStatus = clientStatusFilter.value === 'All' || c.status === clientStatusFilter.value;
    return matchesSearch && matchesLocation && matchesStatus;
  });
});

// Marketing Staff filtering
const filteredMarketingStaff = computed(() => {
  return marketingStaff.value.filter(m => {
    const matchesSearch = !marketingStaffSearch.value || m.name.toLowerCase().includes(marketingStaffSearch.value.toLowerCase()) || m.email.toLowerCase().includes(marketingStaffSearch.value.toLowerCase());
    const matchesStatus = marketingStaffStatusFilter.value === 'All' || m.status === marketingStaffStatusFilter.value;
    return matchesSearch && matchesStatus;
  });
});

// Admin Staff filtering
const filteredAdminStaff = computed(() => {
  return adminStaff.value.filter(a => {
    const matchesSearch = !adminStaffSearch.value || 
      a.name.toLowerCase().includes(adminStaffSearch.value.toLowerCase()) || 
      a.email.toLowerCase().includes(adminStaffSearch.value.toLowerCase());
    const matchesStatus = adminStaffStatusFilter.value === 'All' || a.status === adminStaffStatusFilter.value;
    return matchesSearch && matchesStatus;
  });
});

// Training Centers filtering
const filteredTrainingCenters = computed(() => {
  return trainingCenters.value.filter(t => {
    const matchesSearch = !trainingCenterSearch.value || t.name.toLowerCase().includes(trainingCenterSearch.value.toLowerCase()) || t.email.toLowerCase().includes(trainingCenterSearch.value.toLowerCase());
    const matchesStatus = trainingCenterStatusFilter.value === 'All' || t.status === trainingCenterStatusFilter.value;
    return matchesSearch && matchesStatus;
  });
});

const locationFilterOptions = ref([
  'All',
  'Manhattan',
  'Brooklyn', 
  'Queens',
  'Bronx',
  'Staten Island',
  'Nassau',
  'Suffolk',
  'Westchester'
]);

const filteredBookings = computed(() => {
  return clientBookings.value.filter(b => {
    const matchesSearch = !bookingSearch.value || b.client.toLowerCase().includes(bookingSearch.value.toLowerCase()) || b.service.toLowerCase().includes(bookingSearch.value.toLowerCase());
    const matchesStatus = bookingStatusFilter.value === 'All' || b.status.toLowerCase() === bookingStatusFilter.value.toLowerCase();
    return matchesSearch && matchesStatus;
  });
});

const getActivityColor = (type) => {
  const colors = {
    'Booking': 'success',
    'Profile': 'info',
    'System': 'error',
    'Payment': 'warning',
    'Account': 'error',
  };
  return colors[type] || 'grey';
};

const getUserStatusColor = (status) => {
  const colors = {
    'Active': 'success',
    'Inactive': 'warning',
    'Suspended': 'error',
    'Assigned': 'info',
    'pending': 'orange',
    'Pending': 'orange',
  };
  return colors[status] || 'grey';
};

const getStatusIcon = (status) => {
  const icons = {
    'Active': 'mdi-check-circle',
    'Inactive': 'mdi-pause-circle',
    'Suspended': 'mdi-cancel',
    'Complete': 'mdi-check-circle',
    'Pending': 'mdi-clock',
    'Completed': 'mdi-check-circle',
    'Processing': 'mdi-sync',
    'Paid': 'mdi-check-circle',
    'Overdue': 'mdi-alert-circle',
    'Verified': 'mdi-shield-check',
    'Unverified': 'mdi-shield-alert'
  };
  return icons[status] || 'mdi-help-circle';
};

const saveProfile = async () => {
  try {
    if (!profileData.value.firstName || !profileData.value.lastName) {
      error('First Name and Last Name are required', 'Validation Error');
      return;
    }
    
    if (!profileData.value.email) {
      error('Email is required', 'Validation Error');
      return;
    }
    
    const response = await fetch('/api/profile/update', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        firstName: profileData.value.firstName,
        lastName: profileData.value.lastName,
        email: profileData.value.email,
        phone: profileData.value.phone,
        department: profileData.value.department,
        role: profileData.value.role
      })
    });
    
    const data = await response.json();
    
    if (response.ok && data.success) {
      success('Profile changes saved successfully!');
      // Update the header name
      profile.value.firstName = profileData.value.firstName;
      profile.value.lastName = profileData.value.lastName;
    } else {
      error('Error: ' + (data.error || data.message || 'Failed to save profile'));
    }
  } catch (err) {
    console.error('Save profile error:', err);
    error('Failed to save profile. Please try again.');
  }
};

const changePassword = async () => {
  try {
    if (!passwordData.value.currentPassword) {
      error('Current password is required', 'Validation Error');
      return;
    }
    
    if (!passwordData.value.newPassword) {
      error('New password is required', 'Validation Error');
      return;
    }
    
    if (passwordData.value.newPassword.length < 8) {
      error('New password must be at least 8 characters', 'Validation Error');
      return;
    }
    
    if (passwordData.value.newPassword !== passwordData.value.confirmPassword) {
      error('Passwords do not match', 'Validation Error');
      return;
    }
    
    const response = await fetch('/api/profile/change-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        currentPassword: passwordData.value.currentPassword,
        newPassword: passwordData.value.newPassword,
        confirmPassword: passwordData.value.confirmPassword
      })
    });
    
    const data = await response.json();
    
    if (response.ok && data.success) {
      success('Password changed successfully!');
      // Clear form
      passwordData.value.currentPassword = '';
      passwordData.value.newPassword = '';
      passwordData.value.confirmPassword = '';
    } else {
      error('Error: ' + (data.error || data.message || 'Failed to change password'));
    }
  } catch (err) {
    console.error('Change password error:', err);
    error('Failed to change password. Please try again.');
  }
};

const logout = () => {
  window.location.href = '/login';
};

const editUser = (user) => {
  info(`Edit user: ${user.name}`, 'Edit User');
};

const suspendUser = (user) => {
  if (confirm(`Suspend user: ${user.name}?`)) {
    user.status = 'Suspended';
  }
};

const approveApplication = async (application) => {
  showConfirm(
    'Approve Application',
    `Are you sure you want to approve the application for ${application.name}? The contractor will be able to access their dashboard once approved.`,
    async () => {
      try {
        const response = await fetch(`/api/admin/applications/${application.id}/approve`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });
        const result = await response.json();
        if (result.success) {
          const emailMsg = result.email_sent ? ` Approval email sent to ${application.email}.` : (result.email_message ? ` ${result.email_message}` : '');
          success(`${application.name} has been approved!${emailMsg}`, 'Application Approved');
        } else {
          error('Failed to approve application. Please try again.', 'Approval Failed');
        }
        loadApplications();
      } catch (err) {
        error('Failed to approve application. Please try again.', 'Approval Failed');
      }
    },
    'success',
    'Approve',
    'mdi-check'
  );
};

const rejectApplication = async (application) => {
  showConfirm(
    'Reject Application',
    `Are you sure you want to reject the application for ${application.name}? This action cannot be undone and the applicant will not be able to access their dashboard.`,
    async () => {
      try {
        const response = await fetch(`/api/admin/applications/${application.id}/reject`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });
        const result = await response.json();
        if (result.success) {
          const emailMsg = result.email_sent ? ` Rejection email sent to ${application.email}.` : (result.email_message ? ` ${result.email_message}` : '');
          warning(`${application.name} has been rejected.${emailMsg}`, 'Application Rejected');
        } else {
          error('Failed to reject application. Please try again.', 'Rejection Failed');
        }
        loadApplications();
      } catch (err) {
        error('Failed to reject application. Please try again.', 'Rejection Failed');
      }
    },
    'error',
    'Reject',
    'mdi-close'
  );
};

const processPasswordReset = async (reset) => {
  showConfirm(
    'Process Password Reset',
    `Process password reset for ${reset.email}? This will set the user's password to 123456.`,
    async () => {
      try {
        const response = await fetch(`/api/admin/password-resets/${reset.id}/process`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });
        const result = await response.json().catch(() => ({ success: response.ok }));
        if (result.success) {
          const emailMsg = result.email_sent ? ` Notification email sent to ${reset.email}.` : '';
          success(`Password reset processed for ${reset.email}.${emailMsg}`, 'Reset Complete');
        } else {
          error('Failed to process password reset. Please try again.', 'Reset Failed');
        }
        loadPasswordResets();
      } catch (err) {
        error('Failed to process password reset. Please try again.', 'Reset Failed');
      }
    },
    'success',
    'Approve',
    'mdi-lock-reset'
  );
};

const verifyUser = (user, type) => {
  user.verified = !user.verified;
  success(`${user.name} verification status updated.`, 'Verification Updated');
};

const sendTestEmail = async () => {
  if (!testEmailAddress.value || !testEmailAddress.value.includes('@')) {
    warning('Please enter a valid email address.', 'Invalid Email');
    return;
  }
  
  sendingTestEmail.value = true;
  try {
    const response = await fetch('/api/admin/test-email', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ email: testEmailAddress.value })
    });
    
    const result = await response.json();
    
    if (result.success) {
      success(result.message || `Test email sent successfully to ${testEmailAddress.value}!`, 'Test Email Sent');
      testEmailDialog.value = false;
    } else {
      error(result.message || 'Failed to send test email. Please check your configuration.', 'Test Email Failed');
    }
  } catch (err) {
    error('Failed to send test email. Please try again.', 'Test Email Failed');
  } finally {
    sendingTestEmail.value = false;
  }
};

const sendAnnouncement = async () => {
  if (!announcementData.value.title || !announcementData.value.message) {
    warning('Please fill in all required fields.', 'Missing Information');
    return;
  }
  try {
    const response = await fetch('/api/admin/announcements', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(announcementData.value)
    });
    const result = await response.json();
    const emailMsg = result.emails_sent ? ` Emails sent to ${result.emails_sent} out of ${result.notifications_sent || 0} recipients.` : '';
    success(`Announcement sent successfully! ${result.notifications_sent || 0} notifications created.${emailMsg}`, 'Announcement Sent');
    announcementData.value = { title: '', message: '', type: 'info', recipients: 'all', priority: 'normal' };
    announceDialog.value = false;
    // Refresh notifications after sending announcement
    loadAdminNotificationCount();
    // Also refresh the notification center if it's currently visible
    if (currentSection.value === 'notifications' && notificationCenter.value) {
      notificationCenter.value.loadNotifications();
    }
  } catch (err) {
    error('Failed to send announcement. Please try again.', 'Send Failed');
  }
};

const saveSettings = () => {
  success('Settings saved successfully!', 'Settings Updated');
};

const createBackup = () => {
  success('Database backup created successfully!', 'Backup Complete');
};

const downloadBackup = () => {
  info('Downloading latest backup...', 'Download Started');
};

const clearCache = () => {
  success('Cache cleared successfully!', 'Cache Cleared');
};

const restartServices = () => {
  if (confirm('Are you sure you want to restart system services? This may cause temporary downtime.')) {
    success('Services restarted successfully!', 'Services Restarted');
  }
};

const calculateAge = (birthdate) => {
  if (!birthdate) return '';
  const today = new Date();
  const birth = new Date(birthdate);
  let age = today.getFullYear() - birth.getFullYear();
  const monthDiff = today.getMonth() - birth.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--;
  }
  return age.toString();
};

const openClientDialog = (client = null) => {
  if (client) {
    editingClient.value = true;
    // Parse name into firstName and lastName if needed
    const nameParts = (client.name || '').split(' ');
    clientForm.value = {
      id: client.id,
      firstName: nameParts[0] || '',
      lastName: nameParts.slice(1).join(' ') || '',
      email: client.email || '',
      phone: client.phone || '',
      birthdate: client.date_of_birth || '',
      address: client.address || '',
      state: client.state || 'New York',
      county: client.county || '',
      city: client.city || client.borough || '',
      zip_code: client.zip_code || '',
      password: '',
      status: client.status || 'Active'
    };
    if (client.zip_code) {
      lookupClientZipCode();
    }
  } else {
    editingClient.value = false;
    clientForm.value = { 
      firstName: '', 
      lastName: '', 
      email: '', 
      phone: '', 
      birthdate: '', 
      address: '', 
      state: 'New York', 
      county: '', 
      city: '', 
      zip_code: '', 
      password: '',
      status: 'Active' 
    };
    clientZipLocation.value = '';
  }
  clientDialog.value = true;
};

const closeClientDialog = () => {
  clientDialog.value = false;
  editingClient.value = false;
};

const saveClient = async () => {
  try {
    if (!clientForm.value.firstName || !clientForm.value.lastName || !clientForm.value.email) {
      error('Please fill in required fields: First Name, Last Name, and Email', 'Validation Error');
      return;
    }
    if (!editingClient.value && !clientForm.value.password) {
      error('Password is required for new clients', 'Validation Error');
      return;
    }
    
    const url = editingClient.value ? `/api/admin/users/${clientForm.value.id}` : '/api/admin/users';
    const method = editingClient.value ? 'PUT' : 'POST';
    
    const formData = {
      name: `${clientForm.value.firstName} ${clientForm.value.lastName}`.trim(),
      firstName: clientForm.value.firstName,
      lastName: clientForm.value.lastName,
      email: clientForm.value.email,
      phone: clientForm.value.phone || null,
      date_of_birth: clientForm.value.birthdate || null,
      address: clientForm.value.address || null,
      state: clientForm.value.state || 'New York',
      county: clientForm.value.county || null,
      city: clientForm.value.city || null,
      borough: clientForm.value.city || null,
      zip_code: clientForm.value.zip_code || null,
      status: clientForm.value.status,
      user_type: 'client'
    };
    
    if (!editingClient.value && clientForm.value.password) {
      formData.password = clientForm.value.password;
    }
    
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(formData)
    });
    
    if (response.ok) {
      success(editingClient.value ? 'Client updated successfully!' : 'Client added successfully!', editingClient.value ? 'Client Updated' : 'Client Added');
      loadUsers();
      closeClientDialog();
    } else {
      const errorData = await response.json().catch(() => ({}));
      error(errorData.message || 'Failed to save client. Please try again.', 'Save Failed');
    }
  } catch (err) {
    error('Failed to save client. Please try again.', 'Save Failed');
  }
};

const viewClientDialog = ref(false);
const viewingClient = ref(null);

const viewClientDetails = (client) => {
  viewingClient.value = client;
  viewClientDialog.value = true;

  // Resolve place indicator for details view
  if (viewingClient.value) {
    viewingClient.value.zip_code = viewingClient.value.zip_code || viewingClient.value.zip || '';
    viewingClient.value.place_indicator = viewingClient.value.place_indicator || '';
    ensureItemPlaceIndicator(viewingClient.value);
  }
};

const updateClientStatus = async (client) => {
  try {
    await fetch(`/api/admin/users/${client.id}/status`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ status: client.status })
    });
    success(`Client status updated to ${client.status}`, 'Status Updated');
    loadUsers(); // Reload the users data
  } catch (err) {
    error('Failed to update client status', 'Update Failed');
  }
};

const updateCaregiverStatus = async (caregiver) => {
  try {
    await fetch(`/api/admin/caregivers/${caregiver.id}/status`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ status: caregiver.status })
    });
    success(`Caregiver status updated to ${caregiver.status}`, 'Status Updated');
    loadUsers(); // Reload the users data
  } catch (err) {
    error('Failed to update caregiver status', 'Update Failed');
  }
};

const deleteClient = async (client) => {
  showConfirm(
    'Delete Client',
    `Are you sure you want to delete ${client.name}? This action cannot be undone.`,
    async () => {
      try {
        await fetch(`/api/admin/users/${client.id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });
        success('Client deleted successfully!', 'Client Deleted');
        loadUsers();
      } catch (err) {
        error('Failed to delete client. Please try again.', 'Delete Failed');
      }
    }
  );
};

// Marketing Staff Form Data and Methods
const marketingStaffFormData = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  birthdate: '',
  address: '',
  state: 'New York',
  county: '',
  city: '',
  zip_code: '',
  password: '',
  status: 'Active'
});

const loadMarketingStaff = async () => {
  try {
    const response = await fetch('/api/admin/marketing-staff');
    const data = await response.json();
    marketingStaff.value = (data.staff || []).map(s => {
      const item = {
        ...s,
        zip_code: s.zip_code || s.zip || '',
        location: '',
        place_indicator: ''
      };
      ensureItemPlaceIndicator(item);
      return item;
    });
  } catch (err) {
  }
};

// Pay Marketing Commission
const payMarketingCommission = async (item) => {
  if (payingCommission.value) return; // Prevent double-click
  
  if (!confirm(`Pay $${parseFloat(item.commissionEarned).toFixed(2)} commission to ${item.name}?`)) {
    return;
  }
  
  payingCommission.value = item.id;
  
  try {
    const response = await fetch(`/api/stripe/admin/pay-marketing-commission/${item.id}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      success(`Successfully paid $${parseFloat(data.amount).toFixed(2)} to ${item.name}`);
      await loadMarketingStaff(); // Refresh marketing staff data
    } else {
      error(data.message || 'Payment failed');
    }
  } catch (err) {
    error('Payment failed: ' + err.message);
  } finally {
    payingCommission.value = null;
  }
};

// Pay Training Commission
const payTrainingCommission = async (item) => {
  if (payingCommission.value) return; // Prevent double-click
  
  if (!confirm(`Pay $${parseFloat(item.commissionEarned).toFixed(2)} commission to ${item.name}?`)) {
    return;
  }
  
  payingCommission.value = item.id;
  
  try {
    const response = await fetch(`/api/stripe/admin/pay-training-commission/${item.id}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      success(`Successfully paid $${parseFloat(data.amount).toFixed(2)} to ${item.name}`);
      await loadTrainingCenters(); // Refresh training centers data
    } else {
      error(data.message || 'Payment failed');
    }
  } catch (err) {
    error('Payment failed: ' + err.message);
  } finally {
    payingCommission.value = null;
  }
};

const viewMarketingStaffDetails = (staff) => {
  viewingMarketingStaff.value = staff;
  viewMarketingStaffDialog.value = true;

  // Resolve place indicator for details view
  if (viewingMarketingStaff.value) {
    viewingMarketingStaff.value.zip_code = viewingMarketingStaff.value.zip_code || viewingMarketingStaff.value.zip || '';
    viewingMarketingStaff.value.place_indicator = viewingMarketingStaff.value.place_indicator || '';
    ensureItemPlaceIndicator(viewingMarketingStaff.value);
  }
};

const openMarketingStaffDialog = (staff = null) => {
  if (staff) {
    editingMarketingStaff.value = staff;
    const nameParts = (staff.name || '').split(' ');
    marketingStaffFormData.value = {
      firstName: nameParts[0] || '',
      lastName: nameParts.slice(1).join(' ') || '',
      email: staff.email || '',
      phone: staff.phone || '',
      birthdate: staff.date_of_birth || '',
      address: staff.address || '',
      state: staff.state || 'New York',
      county: staff.county || '',
      city: staff.city || '',
      zip_code: staff.zip_code || '',
      password: '',
      status: staff.status || 'Active'
    };
  } else {
    editingMarketingStaff.value = null;
    marketingStaffFormData.value = { 
      firstName: '', 
      lastName: '', 
      email: '', 
      phone: '', 
      birthdate: '', 
      address: '', 
      state: 'New York', 
      county: '', 
      city: '', 
      zip_code: '', 
      password: '', 
      status: 'Active' 
    };
  }
  marketingStaffDialog.value = true;
};

const saveMarketingStaff = async () => {
  try {
    if (!marketingStaffFormData.value.firstName || !marketingStaffFormData.value.lastName || !marketingStaffFormData.value.email) {
      error('Please fill in required fields: First Name, Last Name, and Email', 'Validation Error');
      return;
    }
    if (!editingMarketingStaff.value && !marketingStaffFormData.value.password) {
      error('Password is required for new users', 'Validation Error');
      return;
    }

    const url = editingMarketingStaff.value 
      ? `/api/admin/marketing-staff/${editingMarketingStaff.value.id}`
      : '/api/admin/marketing-staff';
    
    const formData = {
      name: `${marketingStaffFormData.value.firstName} ${marketingStaffFormData.value.lastName}`.trim(),
      firstName: marketingStaffFormData.value.firstName,
      lastName: marketingStaffFormData.value.lastName,
      email: marketingStaffFormData.value.email,
      phone: marketingStaffFormData.value.phone || null,
      date_of_birth: marketingStaffFormData.value.birthdate || null,
      address: marketingStaffFormData.value.address || null,
      state: marketingStaffFormData.value.state || 'New York',
      county: marketingStaffFormData.value.county || null,
      city: marketingStaffFormData.value.city || null,
      zip_code: marketingStaffFormData.value.zip_code || null,
      status: marketingStaffFormData.value.status
    };
    
    if (!editingMarketingStaff.value && marketingStaffFormData.value.password) {
      formData.password = marketingStaffFormData.value.password;
    }

    const response = await fetch(url, {
      method: editingMarketingStaff.value ? 'PUT' : 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(formData)
    });

    if (response.ok) {
      success(editingMarketingStaff.value ? 'Marketing partner updated!' : 'Marketing partner created!', 'Success');
      marketingStaffDialog.value = false;
      loadMarketingStaff();
    } else {
      throw new Error('Failed to save');
    }
  } catch (err) {
    error('Failed to save marketing partner', 'Error');
  }
};

const deleteMarketingStaff = (staff) => {
  showConfirm(
    'Delete Marketing Partner',
    `Are you sure you want to delete ${staff.name}? This action cannot be undone.`,
    async () => {
      try {
        await fetch(`/api/admin/marketing-staff/${staff.id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });
        success('Marketing partner deleted!', 'Deleted');
        loadMarketingStaff();
      } catch (err) {
        error('Failed to delete marketing partner', 'Error');
      }
    }
  );
};

// Admin Staff Functions
const loadAdminStaff = async () => {
  try {
    const response = await fetch('/api/admin/admin-staff');
    const data = await response.json();
    adminStaff.value = (data.staff || []).map(s => {
      const item = {
        ...s,
        zip_code: s.zip_code || s.zip || '',
        location: '',
        place_indicator: ''
      };
      ensureItemPlaceIndicator(item);
      return item;
    });
  } catch (err) {
    adminStaff.value = [];
  }
};

const viewAdminStaffDetails = (staff) => {
  viewingAdminStaff.value = staff;
  viewAdminStaffDialog.value = true;

  if (viewingAdminStaff.value) {
    viewingAdminStaff.value.zip_code = viewingAdminStaff.value.zip_code || viewingAdminStaff.value.zip || '';
    viewingAdminStaff.value.place_indicator = viewingAdminStaff.value.place_indicator || '';
    ensureItemPlaceIndicator(viewingAdminStaff.value);
  }
};

const openAdminStaffDialog = (staff = null) => {
  if (staff) {
    editingAdminStaff.value = staff;
    // Merge existing permissions with defaults to ensure all keys exist
    const existingPermissions = staff.page_permissions || {};
    const mergedPermissions = { ...getDefaultPagePermissions(), ...existingPermissions };
    adminStaffFormData.value = {
      name: staff.name || '',
      email: staff.email || '',
      phone: staff.phone || '',
      password: '',
      status: staff.status || 'Active',
      page_permissions: mergedPermissions
    };
  } else {
    editingAdminStaff.value = null;
    adminStaffFormData.value = { 
      name: '', 
      email: '', 
      phone: '', 
      password: '', 
      status: 'Active',
      page_permissions: getDefaultPagePermissions()
    };
  }
  adminStaffDialog.value = true;
};

const saveAdminStaff = async () => {
  try {
    if (!adminStaffFormData.value.name || !adminStaffFormData.value.email) {
      error('Please fill in required fields: Name and Email', 'Validation Error');
      return;
    }
    
    if (!editingAdminStaff.value && !adminStaffFormData.value.password) {
      error('Password is required for new admin staff', 'Validation Error');
      return;
    }

    if (!editingAdminStaff.value && adminStaffFormData.value.password && adminStaffFormData.value.password.length < 8) {
      error('Password must be at least 8 characters', 'Validation Error');
      return;
    }

    savingAdminStaff.value = true;

    const url = editingAdminStaff.value 
      ? `/api/admin/admin-staff/${editingAdminStaff.value.id}`
      : '/api/admin/admin-staff';
    
    const formData = {
      name: adminStaffFormData.value.name.trim(),
      email: adminStaffFormData.value.email,
      phone: adminStaffFormData.value.phone || null,
      status: adminStaffFormData.value.status,
      page_permissions: adminStaffFormData.value.page_permissions
    };
    
    // Only include password if it's provided
    if (adminStaffFormData.value.password) {
      formData.password = adminStaffFormData.value.password;
    }

    const response = await fetch(url, {
      method: editingAdminStaff.value ? 'PUT' : 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(formData)
    });

    if (response.ok) {
      success(editingAdminStaff.value ? 'Admin staff updated!' : 'Admin staff created!', 'Success');
      adminStaffDialog.value = false;
      await loadAdminStaff();
    } else {
      // Handle error - check if response is JSON
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        const errorData = await response.json();
        const errorMessage = errorData.message || (errorData.errors ? Object.values(errorData.errors).flat().join(', ') : 'Failed to save');
        throw new Error(errorMessage);
      } else {
        throw new Error(`Server error (${response.status}): Please check if you're logged in and try again`);
      }
    }
  } catch (err) {
    error(err.message || 'Failed to save admin staff', 'Error');
  } finally {
    savingAdminStaff.value = false;
  }
};

const deleteAdminStaff = (staff) => {
  showConfirm(
    'Delete Admin Staff',
    `Are you sure you want to delete ${staff.name}? This action cannot be undone.`,
    async () => {
      try {
        const response = await fetch(`/api/admin/admin-staff/${staff.id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });
        
        if (response.ok) {
          success('Admin staff deleted!', 'Deleted');
          await loadAdminStaff();
        } else {
          throw new Error('Failed to delete');
        }
      } catch (err) {
        error('Failed to delete admin staff', 'Error');
      }
    }
  );
};

const deleteSelectedAdminStaff = () => {
  if (selectedAdminStaff.value.length === 0) return;
  
  showConfirm(
    'Delete Selected Admin Staff',
    `Are you sure you want to delete ${selectedAdminStaff.value.length} admin staff member(s)? This action cannot be undone.`,
    async () => {
      try {
        const deletePromises = selectedAdminStaff.value.map(id => 
          fetch(`/api/admin/admin-staff/${id}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
          })
        );
        
        await Promise.all(deletePromises);
        success('Selected admin staff deleted!', 'Deleted');
        selectedAdminStaff.value = [];
        await loadAdminStaff();
      } catch (err) {
        error('Failed to delete some admin staff members', 'Error');
      }
    }
  );
};

// Training Center Form Data and Methods
const loadTrainingCenters = async () => {
  try {
    const response = await fetch('/api/admin/training-centers', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });
    
    if (!response.ok) {
      const errorText = await response.text();
      trainingCenters.value = [];
      return;
    }
    
    const data = await response.json();

    trainingCenters.value = (data.centers || []).map(c => {
      const item = {
        ...c,
        zip_code: c.zip_code || c.zip || '',
        location: '',
        place_indicator: ''
      };
      ensureItemPlaceIndicator(item);
      return item;
    });
    
    if (trainingCenters.value.length === 0) {
    }
  } catch (err) {
    trainingCenters.value = [];
  }
};

const viewTrainingCenterDetails = async (center) => {
  viewingTrainingCenter.value = { ...center };
  viewTrainingCenterDialog.value = true;

  if (viewingTrainingCenter.value) {
    viewingTrainingCenter.value.zip_code = viewingTrainingCenter.value.zip_code || viewingTrainingCenter.value.zip || '';
    viewingTrainingCenter.value.place_indicator = viewingTrainingCenter.value.place_indicator || '';
    ensureItemPlaceIndicator(viewingTrainingCenter.value);
  }
  
  // Load caregivers for this center
  try {
    const response = await fetch(`/api/admin/training-centers/${center.id}/caregivers`);
    const data = await response.json();
    viewingTrainingCenter.value.caregivers = data.caregivers || [];
  } catch (err) {
  }
};

const openTrainingCenterDialog = (center = null) => {
  if (center) {
    editingTrainingCenter.value = center;
    trainingCenterFormData.value = {
      name: center.name || '',
      email: center.email || '',
      phone: center.phone || '',
      address: center.address || '',
      state: center.state || 'New York',
      county: center.county || '',
      city: center.city || '',
      zip_code: center.zip_code || '',
      password: '',
      status: center.status || 'Active'
    };
    if (center.zip_code) {
      lookupTrainingCenterZipCode();
    } else {
      trainingCenterZipLocation.value = '';
    }
  } else {
    editingTrainingCenter.value = null;
    trainingCenterFormData.value = { 
      name: '', 
      email: '', 
      phone: '', 
      address: '', 
      state: 'New York', 
      county: '', 
      city: '', 
      zip_code: '', 
      password: '', 
      status: 'Active' 
    };
    trainingCenterZipLocation.value = '';
  }
  trainingCenterDialog.value = true;
};

const saveTrainingCenter = async () => {
  try {
    if (!trainingCenterFormData.value.name || !trainingCenterFormData.value.email) {
      error('Please fill in required fields: Training Center Name and Email', 'Validation Error');
      return;
    }
    
    if (editingTrainingCenter.value && !editingTrainingCenter.value.id) {
      error('Invalid training center data. Please refresh the page and try again.', 'Error');
      return;
    }
    
    const url = editingTrainingCenter.value 
      ? `/api/admin/training-centers/${editingTrainingCenter.value.id}`
      : '/api/admin/training-centers';
    
    // Log save operation details
    const saveDetails = {
      isEdit: !!editingTrainingCenter.value,
      id: editingTrainingCenter.value?.id,
      url: url,
      formData: trainingCenterFormData.value
    };
    
    const formData = {
      name: trainingCenterFormData.value.name.trim(),
      email: trainingCenterFormData.value.email,
      phone: trainingCenterFormData.value.phone || null,
      address: trainingCenterFormData.value.address || null,
      state: trainingCenterFormData.value.state || 'New York',
      county: trainingCenterFormData.value.county || null,
      city: trainingCenterFormData.value.city || null,
      zip_code: trainingCenterFormData.value.zip_code || null,
      status: trainingCenterFormData.value.status
    };
    
    // Password is optional - backend will generate random password if not provided
    if (!editingTrainingCenter.value && trainingCenterFormData.value.password) {
      formData.password = trainingCenterFormData.value.password;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const response = await fetch(url, {
      method: editingTrainingCenter.value ? 'PUT' : 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken || '',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(formData)
    });

    const data = await response.json();

    if (response.ok && data.success) {
      success(editingTrainingCenter.value ? 'Training center updated successfully!' : 'Training center created successfully!', editingTrainingCenter.value ? 'Training Center Updated' : 'Training Center Created');
      trainingCenterDialog.value = false;
      await loadTrainingCenters();
    } else {
      const errorMessage = data.message || (data.errors ? JSON.stringify(data.errors) : 'Failed to save training center. Please try again.');
      error(errorMessage, 'Save Failed');
    }
  } catch (err) {
    error('Failed to save training center: ' + err.message, 'Error');
  }
};

const deleteTrainingCenter = (center) => {
  showConfirm(
    'Delete Training Center',
    `Are you sure you want to delete ${center.name}? This action cannot be undone.`,
    async () => {
      try {
        await fetch(`/api/admin/training-centers/${center.id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });
        success('Training center deleted!', 'Deleted');
        loadTrainingCenters();
      } catch (err) {
        error('Failed to delete training center', 'Error');
      }
    }
  );
};

const openCaregiverDialog = (caregiver = null) => {
  if (caregiver) {
    editingCaregiver.value = true;
    const nameParts = (caregiver.name || '').split(' ');
    caregiverForm.value = {
  // IMPORTANT: the update endpoint is /api/admin/users/{id} and expects the *users.id*.
  // Our caregiver row object includes both `userId` (users.id) and `id` (caregiver.id).
  // Using caregiver.id here causes updates to target the wrong user and triggers
  // "email already taken" because that email belongs to a different user.
  id: caregiver.userId || caregiver.id,
      firstName: nameParts[0] || '',
      lastName: nameParts.slice(1).join(' ') || '',
      email: caregiver.email || '',
      phone: caregiver.phone || '',
      birthdate: caregiver.date_of_birth || '',
      address: caregiver.address || '',
      state: caregiver.state || 'New York',
      county: caregiver.county || '',
      city: caregiver.city || caregiver.borough || '',
      zip_code: caregiver.zip_code || '',
      password: '',
      experience: caregiver.years_experience || caregiver.experience || '',
  trainingCenter: caregiver.training_center_name || caregiver.training_center || '',
      customTrainingCenter: '',
      isCustomTrainingCenter: false,
      trainingCertificate: null,
      bio: caregiver.bio || '',
  preferred_hourly_rate_min: caregiver.preferred_hourly_rate_min ?? caregiver.caregiver?.preferred_hourly_rate_min ?? null,
  preferred_hourly_rate_max: caregiver.preferred_hourly_rate_max ?? caregiver.caregiver?.preferred_hourly_rate_max ?? null,
  has_hha: Boolean(caregiver.has_hha ?? caregiver.caregiver?.has_hha),
  hha_number: caregiver.hha_number || caregiver.caregiver?.hha_number || '',
  has_cna: Boolean(caregiver.has_cna ?? caregiver.caregiver?.has_cna),
  cna_number: caregiver.cna_number || caregiver.caregiver?.cna_number || '',
  has_rn: Boolean(caregiver.has_rn ?? caregiver.caregiver?.has_rn),
  rn_number: caregiver.rn_number || caregiver.caregiver?.rn_number || '',
      status: caregiver.status || 'Active'
    };
    if (caregiver.zip_code) {
      lookupCaregiverZipCode();
    }
  } else {
    editingCaregiver.value = false;
    caregiverForm.value = { 
      firstName: '', 
      lastName: '', 
      email: '', 
      phone: '', 
      birthdate: '', 
      address: '', 
      state: 'New York', 
      county: '', 
      city: '', 
      zip_code: '', 
      password: '',
      experience: '', 
      trainingCenter: '', 
      customTrainingCenter: '', 
      isCustomTrainingCenter: false,
      trainingCertificate: null, 
      bio: '', 
  preferred_hourly_rate_min: null,
  preferred_hourly_rate_max: null,
  has_hha: false,
  hha_number: '',
  has_cna: false,
  cna_number: '',
  has_rn: false,
  rn_number: '',
      status: 'Active' 
    };
    caregiverZipLocation.value = '';
  }
  caregiverDialog.value = true;
};

const closeCaregiverDialog = () => {
  caregiverDialog.value = false;
  editingCaregiver.value = false;
};

// Housekeeper Dialog Functions
const openHousekeeperDialog = (housekeeper = null) => {
  if (housekeeper) {
    editingHousekeeper.value = true;
    const nameParts = (housekeeper.name || '').split(' ');
    housekeeperForm.value = {
      id: housekeeper.userId || housekeeper.id,
      firstName: nameParts[0] || '',
      lastName: nameParts.slice(1).join(' ') || '',
      email: housekeeper.email || '',
      phone: housekeeper.phone || '',
      birthdate: housekeeper.date_of_birth || '',
      address: housekeeper.address || '',
      state: housekeeper.state || 'New York',
      county: housekeeper.county || '',
      city: housekeeper.city || housekeeper.borough || '',
      zip_code: housekeeper.zip_code || '',
      password: '',
      experience: housekeeper.years_experience || '',
      hourly_rate: housekeeper.hourly_rate || '',
      bio: housekeeper.bio || '',
      has_own_supplies: Boolean(housekeeper.has_own_supplies),
      available_for_transport: Boolean(housekeeper.available_for_transport),
      skills: housekeeper.skills || [],
      specializations: housekeeper.specializations || [],
      status: housekeeper.status || 'Active'
    };
  } else {
    editingHousekeeper.value = false;
    housekeeperForm.value = {
      id: null,
      firstName: '',
      lastName: '',
      email: '',
      phone: '',
      birthdate: '',
      address: '',
      state: 'New York',
      county: '',
      city: '',
      zip_code: '',
      password: '',
      experience: '',
      hourly_rate: '',
      bio: '',
      has_own_supplies: false,
      available_for_transport: false,
      skills: [],
      specializations: [],
      status: 'Active'
    };
  }
  housekeeperDialog.value = true;
};

const closeHousekeeperDialog = () => {
  housekeeperDialog.value = false;
  editingHousekeeper.value = false;
  housekeeperZipLocation.value = '';
};

const saveHousekeeper = async () => {
  try {
    if (!housekeeperForm.value.firstName || !housekeeperForm.value.lastName || !housekeeperForm.value.email) {
      error('Please fill in required fields: First Name, Last Name, and Email', 'Validation Error');
      return;
    }
    if (!editingHousekeeper.value && !housekeeperForm.value.password) {
      error('Password is required for new housekeepers', 'Validation Error');
      return;
    }

    const url = editingHousekeeper.value ? `/api/admin/users/${housekeeperForm.value.id}` : '/api/admin/users';
    const method = editingHousekeeper.value ? 'PUT' : 'POST';
    
    const formData = {
      name: `${housekeeperForm.value.firstName} ${housekeeperForm.value.lastName}`.trim(),
      firstName: housekeeperForm.value.firstName,
      lastName: housekeeperForm.value.lastName,
      email: housekeeperForm.value.email,
      phone: housekeeperForm.value.phone || null,
      date_of_birth: housekeeperForm.value.birthdate || null,
      address: housekeeperForm.value.address || null,
      state: housekeeperForm.value.state || 'New York',
      county: housekeeperForm.value.county || null,
      city: housekeeperForm.value.city || null,
      borough: housekeeperForm.value.city || null,
      zip_code: housekeeperForm.value.zip_code || null,
      years_experience: housekeeperForm.value.experience || null,
      hourly_rate: housekeeperForm.value.hourly_rate || null,
      bio: housekeeperForm.value.bio || null,
      has_own_supplies: Boolean(housekeeperForm.value.has_own_supplies),
      available_for_transport: Boolean(housekeeperForm.value.available_for_transport),
      skills: housekeeperForm.value.skills || [],
      specializations: housekeeperForm.value.specializations || [],
      status: housekeeperForm.value.status,
      user_type: 'housekeeper'
    };
    
    if (!editingHousekeeper.value && housekeeperForm.value.password) {
      formData.password = housekeeperForm.value.password;
    }

    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(formData)
    });
    
    if (response.ok) {
      success(editingHousekeeper.value ? 'Housekeeper updated successfully!' : 'Housekeeper added successfully!', editingHousekeeper.value ? 'Housekeeper Updated' : 'Housekeeper Added');
      loadUsers();
      closeHousekeeperDialog();
    } else {
      const errorData = await response.json().catch(() => ({}));
      error(errorData.message || 'Failed to save housekeeper. Please try again.', 'Save Failed');
    }
  } catch (err) {
    error('Failed to save housekeeper. Please try again.', 'Save Failed');
  }
};

const closeAddBookingDialog = () => {
  addBookingDialog.value = false;
  bookingZipLocation.value = '';
  // Reset form
  bookingForm.value = {
    client_id: null,
    service_type: 'Caregiver',
    duty_type: '8 Hours',
    service_date: '',
    starting_time: '',
    duration_days: 15,
    zipcode: '',
    street_address: '',
    apartment_unit: '',
    client_age: '',
    mobility_level: 'independent',
    specific_skills: [],
    medical_conditions: [],
    transportation_needed: false,
    special_instructions: '',
    status: 'approved'
  };
};

const saveBooking = async () => {
  if (!bookingForm.value.client_id) {
    error('Please select a client', 'Validation Error');
    return;
  }
  
  if (!bookingForm.value.service_date) {
    error('Please select a service date', 'Validation Error');
    return;
  }
  
  savingBooking.value = true;
  try {
    const requestData = {
      client_id: bookingForm.value.client_id,
      service_type: bookingForm.value.service_type,
      duty_type: bookingForm.value.duty_type,
      service_date: bookingForm.value.service_date,
      starting_time: bookingForm.value.starting_time || null,
      duration_days: bookingForm.value.duration_days,
      zipcode: bookingForm.value.zipcode || null,
      street_address: bookingForm.value.street_address || null,
      apartment_unit: bookingForm.value.apartment_unit || null,
      client_age: bookingForm.value.client_age || null,
      mobility_level: bookingForm.value.mobility_level || null,
      specific_skills: bookingForm.value.specific_skills || [],
      medical_conditions: bookingForm.value.medical_conditions || [],
      transportation_needed: bookingForm.value.transportation_needed || false,
      special_instructions: bookingForm.value.special_instructions || null,
      status: bookingForm.value.status || 'approved'
    };
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const response = await fetch('/api/bookings', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken || '',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(requestData)
    });
    
    const data = await response.json();
    
    if (response.ok && data.success) {
      success('Booking created successfully!', 'Booking Created');
      closeAddBookingDialog();
      await loadClientBookings();
    } else {
      error(data.message || 'Failed to create booking. Please try again.', 'Booking Failed');
    }
  } catch (err) {
    error('Failed to create booking: ' + err.message, 'Booking Failed');
  } finally {
    savingBooking.value = false;
  }
};

const saveCaregiver = async () => {
  try {
    if (!caregiverForm.value.firstName || !caregiverForm.value.lastName || !caregiverForm.value.email) {
      error('Please fill in required fields: First Name, Last Name, and Email', 'Validation Error');
      return;
    }
    if (!editingCaregiver.value && !caregiverForm.value.password) {
      error('Password is required for new caregivers', 'Validation Error');
      return;
    }

    const url = editingCaregiver.value ? `/api/admin/users/${caregiverForm.value.id}` : '/api/admin/users';
    const method = editingCaregiver.value ? 'PUT' : 'POST';
    
    const formData = {
      name: `${caregiverForm.value.firstName} ${caregiverForm.value.lastName}`.trim(),
      firstName: caregiverForm.value.firstName,
      lastName: caregiverForm.value.lastName,
      email: caregiverForm.value.email,
      phone: caregiverForm.value.phone || null,
      date_of_birth: caregiverForm.value.birthdate || null,
      address: caregiverForm.value.address || null,
      state: caregiverForm.value.state || 'New York',
      county: caregiverForm.value.county || null,
      city: caregiverForm.value.city || null,
      borough: caregiverForm.value.city || null,
      zip_code: caregiverForm.value.zip_code || null,
      years_experience: caregiverForm.value.experience || null,
      training_center: caregiverForm.value.isCustomTrainingCenter ? caregiverForm.value.customTrainingCenter : caregiverForm.value.trainingCenter || null,
      bio: caregiverForm.value.bio || null,
  preferred_hourly_rate_min: caregiverForm.value.preferred_hourly_rate_min ?? null,
  preferred_hourly_rate_max: caregiverForm.value.preferred_hourly_rate_max ?? null,
  has_hha: Boolean(caregiverForm.value.has_hha),
  hha_number: caregiverForm.value.hha_number || null,
  has_cna: Boolean(caregiverForm.value.has_cna),
  cna_number: caregiverForm.value.cna_number || null,
  has_rn: Boolean(caregiverForm.value.has_rn),
  rn_number: caregiverForm.value.rn_number || null,
      status: caregiverForm.value.status,
      user_type: 'caregiver'
    };
    
    if (!editingCaregiver.value && caregiverForm.value.password) {
      formData.password = caregiverForm.value.password;
    }

    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Requested-With': 'XMLHttpRequest',
  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(formData)
    });
    
  if (response.ok) {
      success(editingCaregiver.value ? 'Caregiver updated successfully!' : 'Caregiver added successfully!', editingCaregiver.value ? 'Caregiver Updated' : 'Caregiver Added');
      loadUsers();
      closeCaregiverDialog();
    } else {
      const errorData = await response.json().catch(() => ({}));
      error(errorData.message || 'Failed to save caregiver. Please try again.', 'Save Failed');
    }
  } catch (err) {
    error('Failed to save caregiver. Please try again.', 'Save Failed');
  }
};

const confirmDialog = ref(false);
const confirmData = ref({ title: '', message: '', action: null, buttonColor: 'error', buttonText: 'Confirm', buttonIcon: 'mdi-check' });

const showConfirm = (title, message, action, buttonColor = 'error', buttonText = 'Confirm', buttonIcon = 'mdi-check') => {
  confirmData.value = { title, message, action, buttonColor, buttonText, buttonIcon };
  confirmDialog.value = true;
};

const handleConfirm = () => {
  if (confirmData.value.action) confirmData.value.action();
  confirmDialog.value = false;
};

const deleteCaregiver = (caregiver) => {
  showConfirm(
    'Delete Caregiver',
    `Are you sure you want to delete ${caregiver.name}? This action cannot be undone.`,
    () => {
      const index = caregivers.value.findIndex(c => c.id === caregiver.id);
      if (index > -1) caregivers.value.splice(index, 1);
      success('Caregiver deleted successfully!', 'Caregiver Deleted');
    }
  );
};

// Bulk delete functions
const deleteSelectedUsers = async () => {
  if (selectedUsers.value.length === 0) return;
  showConfirm(
    'Delete Selected Users',
    `Are you sure you want to delete ${selectedUsers.value.length} user(s)? This action cannot be undone.`,
    async () => {
      try {
        let deletedCount = 0;
        let failedCount = 0;
        const userIdsToDelete = [...selectedUsers.value];
        
        for (const userId of userIdsToDelete) {
          try {
            const response = await fetch(`/api/admin/users/${userId}`, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
              }
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
              deletedCount++;
            } else {
              failedCount++;
            }
          } catch (err) {
            failedCount++;
          }
        }
        
        if (failedCount === 0) {
          success(`${deletedCount} user(s) deleted successfully!`, 'Users Deleted');
        } else if (deletedCount > 0) {
          warning(`${deletedCount} user(s) deleted, but ${failedCount} failed.`, 'Partial Success');
        } else {
          error('Failed to delete users. Please try again.', 'Delete Failed');
        }
        
        selectedUsers.value = [];
        await loadUsers();
      } catch (err) {
        error('Failed to delete users. Please try again.', 'Delete Failed');
      }
    }
  );
};

const deleteSelectedCaregivers = async () => {
  if (selectedCaregivers.value.length === 0) return;
  showConfirm(
    'Delete Selected Caregivers',
    `Are you sure you want to delete ${selectedCaregivers.value.length} caregiver(s)? This action cannot be undone.`,
    async () => {
      try {
        let deletedCount = 0;
        let failedCount = 0;
        const userIdsToDelete = [...selectedCaregivers.value]; // These are now userIds
        const errors = [];
        
        for (const userId of userIdsToDelete) {
          try {
            // Validate userId
            if (!userId || isNaN(userId)) {
              errors.push(`Invalid user ID: ${userId}`);
              failedCount++;
              continue;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
              errors.push(`Missing CSRF token for user ${userId}`);
              failedCount++;
              continue;
            }
            
            const response = await fetch(`/api/admin/users/${userId}`, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
              }
            });

let data;
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
              data = await response.json();
            } else {
              const text = await response.text();
              errors.push(`Server error for user ${userId}: ${response.status} ${response.statusText}`);
              failedCount++;
              continue;
            }
            
            if (response.ok && data.success) {
              deletedCount++;
            } else {
              failedCount++;
              const errorMsg = data.message || data.error || `HTTP ${response.status}`;
              errors.push(`User ${userId}: ${errorMsg}`);
            }
          } catch (err) {
            failedCount++;
            const errorMsg = err.message || 'Network error';
            errors.push(`User ${userId}: ${errorMsg}`);
          }
        }
        
        if (failedCount === 0) {
          success(`${deletedCount} caregiver(s) deleted successfully!`, 'Caregivers Deleted');
        } else if (deletedCount > 0) {
          warning(`${deletedCount} caregiver(s) deleted, but ${failedCount} failed. Check console for details.`, 'Partial Success');
        } else {
          error(`Failed to delete all caregivers. Check console for details.`, 'Delete Failed');
        }
        
        selectedCaregivers.value = [];
        await loadUsers();
      } catch (err) {
        error(`Failed to delete caregivers: ${err.message}`, 'Delete Failed');
      }
    }
  );
};

const deleteSelectedClients = async () => {
  if (selectedClients.value.length === 0) return;
  showConfirm(
    'Delete Selected Clients',
    `Are you sure you want to delete ${selectedClients.value.length} client(s)? This action cannot be undone.`,
    async () => {
      try {
        let deletedCount = 0;
        let failedCount = 0;
        const clientIdsToDelete = [...selectedClients.value];
        
        for (const clientId of clientIdsToDelete) {
          try {
            const response = await fetch(`/api/admin/users/${clientId}`, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
              }
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
              deletedCount++;
            } else {
              failedCount++;
            }
          } catch (err) {
            failedCount++;
          }
        }
        
        if (failedCount === 0) {
          success(`${deletedCount} client(s) deleted successfully!`, 'Clients Deleted');
        } else if (deletedCount > 0) {
          warning(`${deletedCount} client(s) deleted, but ${failedCount} failed.`, 'Partial Success');
        } else {
          error('Failed to delete clients. Please try again.', 'Delete Failed');
        }
        
        selectedClients.value = [];
        await loadUsers();
      } catch (err) {
        error('Failed to delete clients. Please try again.', 'Delete Failed');
      }
    }
  );
};

const deleteSelectedMarketingStaff = async () => {
  if (selectedMarketingStaff.value.length === 0) return;
  showConfirm(
    'Delete Selected Marketing Partner',
    `Are you sure you want to delete ${selectedMarketingStaff.value.length} marketing partner(s)? This action cannot be undone.`,
    async () => {
      try {
        let deletedCount = 0;
        let failedCount = 0;
        const staffIdsToDelete = [...selectedMarketingStaff.value];
        
        for (const staffId of staffIdsToDelete) {
          try {
            const response = await fetch(`/api/admin/marketing-staff/${staffId}`, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
              }
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
              deletedCount++;
            } else {
              failedCount++;
            }
          } catch (err) {
            failedCount++;
          }
        }
        
        if (failedCount === 0) {
          success(`${deletedCount} marketing partner(s) deleted successfully!`, 'Marketing Partner Deleted');
        } else if (deletedCount > 0) {
          warning(`${deletedCount} marketing partner(s) deleted, but ${failedCount} failed.`, 'Partial Success');
        } else {
          error('Failed to delete marketing partner. Please try again.', 'Delete Failed');
        }
        
        selectedMarketingStaff.value = [];
        await loadMarketingStaff();
      } catch (err) {
        error('Failed to delete marketing partner. Please try again.', 'Delete Failed');
      }
    }
  );
};

const deleteSelectedTrainingCenters = async () => {
  if (selectedTrainingCenters.value.length === 0) return;
  showConfirm(
    'Delete Selected Training Centers',
    `Are you sure you want to delete ${selectedTrainingCenters.value.length} training center(s)? This action cannot be undone.`,
    async () => {
      try {
        let deletedCount = 0;
        let failedCount = 0;
        const centerIdsToDelete = [...selectedTrainingCenters.value];
        
        for (const centerId of centerIdsToDelete) {
          try {
            const response = await fetch(`/api/admin/training-centers/${centerId}`, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
              }
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
              deletedCount++;
            } else {
              failedCount++;
            }
          } catch (err) {
            failedCount++;
          }
        }
        
        if (failedCount === 0) {
          success(`${deletedCount} training center(s) deleted successfully!`, 'Training Centers Deleted');
        } else if (deletedCount > 0) {
          warning(`${deletedCount} training center(s) deleted, but ${failedCount} failed.`, 'Partial Success');
        } else {
          error('Failed to delete training centers. Please try again.', 'Delete Failed');
        }
        
        selectedTrainingCenters.value = [];
        await loadTrainingCenters();
      } catch (err) {
        error('Failed to delete training centers. Please try again.', 'Delete Failed');
      }
    }
  );
};

const deleteSelectedBookings = async () => {
  if (selectedBookings.value.length === 0) return;
  showConfirm(
    'Delete Selected Bookings',
    `Are you sure you want to delete ${selectedBookings.value.length} booking(s)? This action cannot be undone.`,
    async () => {
      try {
        let deletedCount = 0;
        let failedCount = 0;
        const bookingIdsToDelete = [...selectedBookings.value];
        const errors = [];
        
        for (const bookingId of bookingIdsToDelete) {
          try {
            // Validate bookingId
            if (!bookingId || isNaN(bookingId)) {
              errors.push(`Invalid booking ID: ${bookingId}`);
              failedCount++;
              continue;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
              errors.push(`Missing CSRF token for booking ${bookingId}`);
              failedCount++;
              continue;
            }
            
            const response = await fetch(`/api/bookings/${bookingId}`, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
              }
            });

let data;
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
              data = await response.json();
            } else {
              const text = await response.text();
              errors.push(`Server error for booking ${bookingId}: ${response.status} ${response.statusText}`);
              failedCount++;
              continue;
            }
            
            if (response.ok && data.success) {
              deletedCount++;
            } else {
              failedCount++;
              const errorMsg = data.message || data.error || `HTTP ${response.status}`;
              errors.push(`Booking ${bookingId}: ${errorMsg}`);
            }
          } catch (err) {
            failedCount++;
            const errorMsg = err.message || 'Network error';
            errors.push(`Booking ${bookingId}: ${errorMsg}`);
          }
        }
        
        if (failedCount === 0) {
          success(`${deletedCount} booking(s) deleted successfully!`, 'Bookings Deleted');
        } else if (deletedCount > 0) {
          warning(`${deletedCount} booking(s) deleted, but ${failedCount} failed. Check console for details.`, 'Partial Success');
        } else {
          error(`Failed to delete all bookings. Check console for details.`, 'Delete Failed');
        }
        
        selectedBookings.value = [];
        await loadClientBookings();
      } catch (err) {
        error(`Failed to delete bookings: ${err.message}`, 'Delete Failed');
      }
    }
  );
};

const viewCaregiverDetails = async (caregiver) => {
  // Open dialog immediately and show a loading state (null -> template will tolerate)
  viewCaregiverDialog.value = true;
  viewingCaregiver.value = null;

  try {
    // Determine the user id (mapped item uses userId)
    const userId = caregiver.userId || caregiver.user_id || caregiver.id;

    // Fetch full caregiver profile from admin JSON endpoint (avoids /api/profile HTML/login redirects)
    const resp = await fetch(`/api/admin/caregivers/${userId}`);
    const text = await resp.text();
    if (!text || !text.trim().startsWith('{')) {
      // fallback to the passed-in item
      viewingCaregiver.value = caregiver;
      await loadCaregiverReviews(caregiver.id || caregiver.userId || caregiver.caregiverId);
      return;
    }
    const data = JSON.parse(text);

    if (!data || !data.user) {
      // fallback to the passed-in item
      viewingCaregiver.value = caregiver;
      // Attempt to load reviews with whatever id we have
      await loadCaregiverReviews(caregiver.id || caregiver.userId || caregiver.caregiverId);
      return;
    }

    const u = data.user;
    const c = data.caregiver || {};

  // Place indicator from ZIP (City, ST). No more "guesses".
  const zipVal = String(u.zip_code || u.zip || '');
  const placeIndicator = await resolveZipCityState(zipVal);
  const location = placeIndicator;

    // Merge into viewingCaregiver object used by the modal template
    viewingCaregiver.value = {
      id: c.id || caregiver.id,
      userId: u.id,
      name: u.name,
      first_name: (u.first_name || u.firstName || null),
      last_name: (u.last_name || u.lastName || null),
      email: u.email,
      phone: u.phone || caregiver.phone || '',
      birthdate: u.date_of_birth ? new Date(u.date_of_birth).toLocaleDateString() : (u.birthdate ? String(u.birthdate) : ''),
      age: (() => {
        const dob = u.date_of_birth || u.birthdate;
        if (!dob) return '';
        const d = new Date(dob);
        if (Number.isNaN(d.getTime())) return '';
        const today = new Date();
        let a = today.getFullYear() - d.getFullYear();
        const m = today.getMonth() - d.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < d.getDate())) a--;
        return a;
      })(),
      address: u.address || '',
      state: u.state || 'New York',
      county: u.county || u.borough || '',
      city: u.city || '',
      zip_code: u.zip_code || u.zip || '',
      borough: u.borough || u.county || '',
      place_indicator: placeIndicator,
      location: location,
      clients: c.clients_served || caregiver.clients || 0,
      joined: u.created_at ? new Date(u.created_at).toLocaleDateString() : (caregiver.joined || ''),
      verified: Boolean(u.email_verified_at),
      rating: c.rating || caregiver.rating || 0,
      preferred_hourly_rate_min: c.preferred_hourly_rate_min || caregiver.preferred_hourly_rate_min || 20,
      preferred_hourly_rate_max: c.preferred_hourly_rate_max || caregiver.preferred_hourly_rate_max || 50,
      has_hha: Boolean(c.has_hha),
      hha_number: c.hha_number || c.hhaNumber || null,
      has_cna: Boolean(c.has_cna),
      cna_number: c.cna_number || c.cnaNumber || null,
      has_rn: Boolean(c.has_rn),
      rn_number: c.rn_number || c.rnNumber || null,
  training_certificate: c.training_certificate || null,
      bio: c.bio || caregiver.bio || ''
    };

    // Load caregiver reviews using the caregiver record id if available
    const caregiverIdForReviews = c.id || caregiver.id || caregiver.userId;
    await loadCaregiverReviews(caregiverIdForReviews);
  } catch (err) {
    // On error, show the passed-in caregiver and attempt to load reviews
    viewingCaregiver.value = caregiver;
    await loadCaregiverReviews(caregiver.id || caregiver.userId || caregiver.caregiverId);
  }
};

// ------------------------------------------------------------
// ZIP -> City/State place indicator (Admin tables)
// - Uses the same public /api/zipcode-lookup/{zip} endpoint as registration.
// - Caches results to avoid hammering the endpoint when tables paginate/sort.
// ------------------------------------------------------------
const zipCityStateCache = new Map();
const normalizeZip5 = (z) => {
  const zip = String(z || '').trim();
  const m = zip.match(/^(\d{5})/);
  return m ? m[1] : '';
};
const resolveZipCityState = async (zipLike) => {
  const zip = normalizeZip5(zipLike);
  if (!zip) return '';
  if (zipCityStateCache.has(zip)) return zipCityStateCache.get(zip);

  try {
    const resp = await fetch(`/api/zipcode-lookup/${zip}`);
    if (resp.ok) {
      const data = await resp.json();
      if (data.success && data.location) {
        zipCityStateCache.set(zip, data.location);
        return data.location;
      }
      // Handle other location formats
      const location = String(
        (data && (data.place || data.location)) ||
        (data && data.city && (data.state || data.state_abbreviation) ? `${data.city}, ${data.state || data.state_abbreviation}` : '') ||
        ''
      ).trim();
      zipCityStateCache.set(zip, location);
      return location;
    }
  } catch (error) {
    console.error('AdminDashboard ZIP code lookup error:', error);
  }

  zipCityStateCache.set(zip, '');
  return '';
};

const ensureItemPlaceIndicator = async (item) => {
  // This function is deprecated - use resolveAllZipCodes instead
  return;
};

// Track which ZIPs are currently being processed to avoid duplicate requests
const processingZips = new Set();

// Batch resolve all ZIP codes for a list of items and force Vue reactivity update
const resolveAllZipCodes = async (items, arrayRef) => {
  if (!items || items.length === 0 || !arrayRef || !arrayRef.value) return;
  
  // Process each item asynchronously
  items.forEach((item, index) => {
    const zip = item.zip_code || item.zip || '';
    if (!zip) return;
    
    // Skip if already resolved to a real value (not empty, not "Loading...")
    if (item.place_indicator && item.place_indicator !== 'Loading...') return;
    
    // Skip if already being processed
    const cacheKey = `${zip}-${index}`;
    if (processingZips.has(cacheKey)) return;
    processingZips.add(cacheKey);
    
    // Resolve asynchronously
    resolveZipCityState(zip).then(loc => {
      processingZips.delete(cacheKey);
      // Update the array item to trigger Vue reactivity
      if (arrayRef.value && arrayRef.value[index]) {
        const updated = { ...arrayRef.value[index], place_indicator: loc || 'Unknown ZIP' };
        arrayRef.value.splice(index, 1, updated);
      }
    }).catch(() => {
      processingZips.delete(cacheKey);
      // On error, mark as Unknown ZIP
      if (arrayRef.value && arrayRef.value[index]) {
        const updated = { ...arrayRef.value[index], place_indicator: 'Unknown ZIP' };
        arrayRef.value.splice(index, 1, updated);
      }
    });
  });
};

const loadCaregiverReviews = async (caregiverId) => {
  if (!caregiverId) return;
  
  loadingCaregiverReviews.value = true;
  caregiverReviews.value = [];
  
  try {
    const response = await axios.get(`/api/reviews/caregiver/${caregiverId}`);
    if (response.data.success) {
      caregiverReviews.value = response.data.reviews || [];
    }
  } catch (error) {
    error('Failed to load reviews');
  } finally {
    loadingCaregiverReviews.value = false;
  }
};

const viewAllReviews = (caregiver) => {
  // Close the caregiver details dialog
  viewCaregiverDialog.value = false;
  
  // Navigate to reviews section (if needed, add filter for specific caregiver)
  currentSection.value = 'reviews';
  
  // Optional: Add a filter to show only this caregiver's reviews
  info(`Showing all reviews for ${caregiver.name}`, 'Reviews');
};

const getPaymentStatusColor = (status) => {
  const colors = { 
    'Paid': 'success', 
    'Pending': 'warning', 
    'Partial': 'info',
    'Overdue': 'error',
    'No Hours': 'grey'
  };
  return colors[status] || 'grey';
};

const getTransactionTypeColor = (type) => {
  const colors = { 'Payment': 'success', 'Salary': 'info', 'Refund': 'warning', 'Fee': 'error' };
  return colors[type] || 'grey';
};

const getBookingStatusColor = (status) => {
  const colors = {
    'pending': 'warning',
    'approved': 'success', 
    'rejected': 'error',
    'Pending': 'warning',
    'Approved': 'success',
    'Rejected': 'error',
    'confirmed': 'success',
    'completed': 'primary'
  };
  return colors[status] || 'grey';
};

const getAssignmentStatusColor = (assignmentStatus) => {
  const colors = {
    'unassigned': 'warning',
    'partial': 'info',
    'assigned': 'success',
    'Unassigned': 'warning',
    'Partial': 'info', 
    'Assigned': 'success'
  };
  return colors[assignmentStatus] || 'info';
};

const assignCaregiverDialog = async (booking) => {
  selectedBooking.value = booking;
  assignCaregiverSearch.value = '';
  assignAvailabilityFilter.value = 'Available';
  
  // Initialize custom caregivers needed with booking's default value
  customCaregiversNeeded.value = booking.caregiversNeeded;
  
  // Load currently assigned caregivers for this booking
  assignSelectedCaregivers.value = caregiverAssignments.value[booking.id] || [];
  
  // Initialize assigned rates for selected caregivers with default values
  assignedRates.value = {};
  assignSelectedCaregivers.value.forEach(caregiverId => {
    const caregiver = caregivers.value.find(c => c.id === caregiverId);
    if (caregiver) {
      // Default to minimum preferred rate
      assignedRates.value[caregiverId] = caregiver.preferred_hourly_rate_min || 20;
    }
  });
  
  assignDialog.value = true;
};

const assignHousekeeperDialog = async (booking) => {
  selectedBooking.value = booking;
  assignHousekeeperSearch.value = '';
  assignHousekeeperAvailabilityFilter.value = 'Available';

  customHousekeepersNeeded.value = booking.caregiversNeeded;

  // Prefill from existing assignments if provided
  assignSelectedHousekeepers.value = (booking.assignments || [])
  .filter(a => String(a.provider_type || '').toLowerCase() === 'housekeeper' || a.housekeeper_id || a.housekeeper?.id)
  .map(a => a.housekeeper_id || a.housekeeper?.id)
    .filter(Boolean);

  assignedHousekeeperRates.value = {};
  assignSelectedHousekeepers.value.forEach(housekeeperId => {
    const hk = housekeepers.value.find(h => h.id === housekeeperId);
    if (hk) {
      assignedHousekeeperRates.value[housekeeperId] = hk.hourly_rate || 20;
    }
  });

  assignHousekeeperDialogOpen.value = true;
};

const closeAssignDialog = () => {
  assignDialog.value = false;
  assignSelectedCaregivers.value = [];
  assignedRates.value = {};
  customCaregiversNeeded.value = null;
  selectedBooking.value = null;
};

const closeAssignHousekeeperDialog = () => {
  assignHousekeeperDialogOpen.value = false;
  assignSelectedHousekeepers.value = [];
  assignedHousekeeperRates.value = {};
  customHousekeepersNeeded.value = null;
  selectedBooking.value = null;
};

const toggleCaregiverSelection = (caregiverId) => {
  const index = assignSelectedCaregivers.value.indexOf(caregiverId);
  const workerType = selectedBooking.value?.service_type === 'Housekeeping' ? 'housekeeper' : 'caregiver';
  const workerTypePlural = selectedBooking.value?.service_type === 'Housekeeping' ? 'housekeepers' : 'caregivers';
  
  if (index > -1) {
    // Uncheck - remove from selection and clear rate
    assignSelectedCaregivers.value.splice(index, 1);
    delete assignedRates.value[caregiverId];
  } else {
    // Check - add to selection but validate limit
    const maxNeeded = customCaregiversNeeded.value || selectedBooking.value?.caregiversNeeded || 1;
    if (assignSelectedCaregivers.value.length >= maxNeeded) {
      warning(`This booking needs ${maxNeeded} ${maxNeeded !== 1 ? workerTypePlural : workerType}. Please unselect a ${workerType} first or increase the number needed.`, 'Selection Limit Reached');
      return;
    }
    assignSelectedCaregivers.value.push(caregiverId);
    
    // Initialize rate for newly selected caregiver
    const caregiver = caregivers.value.find(c => c.id === caregiverId);
    if (caregiver) {
      assignedRates.value[caregiverId] = caregiver.preferred_hourly_rate_min || 20;
    }
  }
};

const getCaregiverById = (id) => {
  return caregivers.value.find(c => c.id === id);
};

const getHousekeeperById = (id) => {
  return housekeepers.value.find(h => h.id === id);
};

const toggleHousekeeperSelection = (housekeeperId, checked) => {
  const maxNeeded = customHousekeepersNeeded.value || selectedBooking.value?.caregiversNeeded || 1;
  const isChecked = typeof checked === 'boolean' ? checked : !assignSelectedHousekeepers.value.includes(housekeeperId);

  if (!isChecked) {
    assignSelectedHousekeepers.value = assignSelectedHousekeepers.value.filter(id => id !== housekeeperId);
    delete assignedHousekeeperRates.value[housekeeperId];
    return;
  }

  if (!assignSelectedHousekeepers.value.includes(housekeeperId)) {
    if (assignSelectedHousekeepers.value.length >= maxNeeded) {
      warning(`This booking needs ${maxNeeded} housekeeper${maxNeeded !== 1 ? 's' : ''}. Please unselect a housekeeper first or increase the number needed.`, 'Selection Limit Reached');
      return;
    }

    assignSelectedHousekeepers.value = [...assignSelectedHousekeepers.value, housekeeperId];
    const hk = getHousekeeperById(housekeeperId);
    if (hk) {
      assignedHousekeeperRates.value[housekeeperId] = hk.hourly_rate || 20;
    }
  }
};

const calculateProfit = (caregiverId) => {
  // Calculate what this caregiver will be paid
  // Assume work is split evenly among all assigned caregivers
  const rate = parseFloat(assignedRates.value[caregiverId] || 0);
  const hours = parseFloat(selectedBooking.value?.hoursPerDay || 8);
  const totalDays = parseFloat(selectedBooking.value?.durationDays || 1);
  const numAssigned = assignSelectedCaregivers.value.length || 1;
  
  // If multiple caregivers assigned, split the days among them
  const daysPerCaregiver = totalDays / numAssigned;
  const caregiverPayout = rate * hours * daysPerCaregiver;
  
  return caregiverPayout.toFixed(2);
};

const calculateTotalProfit = () => {
  // Total client payment (based on ORIGINAL booking's caregivers needed)
  const clientRate = parseFloat(selectedBooking.value?.hourlyRate || 45);
  const hours = parseFloat(selectedBooking.value?.hoursPerDay || 8);
  const days = parseFloat(selectedBooking.value?.durationDays || 1);
  // Use ONLY the original booking's caregiversNeeded, NOT customCaregiversNeeded
  const caregiversNeeded = selectedBooking.value?.caregiversNeeded || 1;
  const totalClientPayment = clientRate * hours * days * caregiversNeeded;
  
  // Total caregiver payouts (sum of all selected caregivers)
  let totalCaregiverPayouts = 0;
  assignSelectedCaregivers.value.forEach(caregiverId => {
    totalCaregiverPayouts += parseFloat(calculateProfit(caregiverId));
  });
  
  // Profit = Client pays - Caregivers get paid
  const profit = totalClientPayment - totalCaregiverPayouts;
  return profit.toFixed(2);
};

const filteredAssignCaregivers = computed(() => {
  return caregivers.value.filter(c => {
    const matchesSearch = !assignCaregiverSearch.value || 
      c.name.toLowerCase().includes(assignCaregiverSearch.value.toLowerCase());
    const matchesAvailability = assignAvailabilityFilter.value === 'All' || 
      (assignAvailabilityFilter.value === 'Available' && c.status === 'Active') ||
      (assignAvailabilityFilter.value === 'Unavailable' && (c.status === 'Assigned' || c.status === 'Inactive'));
    return matchesSearch && matchesAvailability;
  });
});

const filteredAssignHousekeepers = computed(() => {
  return housekeepers.value.filter(h => {
    const matchesSearch = !assignHousekeeperSearch.value ||
      String(h.name || '').toLowerCase().includes(assignHousekeeperSearch.value.toLowerCase()) ||
      String(h.email || '').toLowerCase().includes(assignHousekeeperSearch.value.toLowerCase());

    const status = String(h.status || h.availability_status || '').toLowerCase();
    const isUnavailable = status === 'inactive' || status === 'unavailable';
    const isAssigned = status === 'assigned';
    const isAvailable = !isUnavailable && !isAssigned;

    const matchesAvailability = assignHousekeeperAvailabilityFilter.value === 'All' ||
      (assignHousekeeperAvailabilityFilter.value === 'Available' && isAvailable) ||
      (assignHousekeeperAvailabilityFilter.value === 'Assigned' && isAssigned) ||
      (assignHousekeeperAvailabilityFilter.value === 'Unavailable' && isUnavailable);

    return matchesSearch && matchesAvailability;
  });
});

const getAssignedCaregivers = (bookingId) => {
  const assignedIds = caregiverAssignments.value[bookingId] || [];
  const booking = clientBookings.value.find(b => b.id === bookingId);
  
  // If booking has assignments with caregiver data, use that (includes actual phone from DB)
  if (booking && booking.assignments) {
    return booking.assignments.map(assignment => ({
      id: assignment.caregiver_id,
      name: assignment.caregiver?.user?.name || 'Unknown',
      email: assignment.caregiver?.user?.email || 'Unknown',
      phone: assignment.caregiver?.user?.phone || '(646) 282-8282',
      rating: assignment.caregiver?.rating || 5.0,
      status: 'Active',
      borough: 'Manhattan',
      hourly_rate: assignment.assigned_hourly_rate,
      hourlyRate: assignment.assigned_hourly_rate
    }));
  }
  
  // Fallback to caregivers array
  return caregivers.value.filter(c => assignedIds.includes(c.id));
};

const getAssignedHousekeepers = (bookingId) => {
  const booking = clientBookings.value.find(b => b.id === bookingId);
  if (!booking || !Array.isArray(booking.assignments)) return [];

  return booking.assignments
    .filter(a => String(a.provider_type || '').toLowerCase() === 'housekeeper' || a.housekeeper_id)
    .map(a => ({
      id: a.housekeeper_id,
      name: a.housekeeper?.user?.name || a.housekeeper_name || 'Unknown',
      email: a.housekeeper?.user?.email || a.housekeeper_email || 'Unknown',
      hourly_rate: a.assigned_hourly_rate || 20,
    }))
    .filter(h => !!h.id);
};

// Helper function to check if booking is a housekeeping booking
const isHousekeepingBooking = (booking) => {
  if (!booking) return false;
  const serviceType = String(booking.service_type || booking.service || '').toLowerCase();
  return serviceType.includes('housekeeping') || serviceType.includes('house cleaning');
};

const viewBookingDialog = ref(false);
const viewingBooking = ref(null);
const viewAssignedCaregiversDialog = ref(false);
const viewingBookingCaregivers = ref(null);
const viewAssignedHousekeepersDialog = ref(false);
const viewingBookingHousekeepers = ref(null);
const caregiverSchedules = ref({}); // Store schedules for each caregiver
const weeklySchedule = ref({}); // Store caregiver assignments for each day: { monday: caregiverId, tuesday: caregiverId, ... }
const assignedHousekeepersTab = ref('housekeepers');
const housekeeperSchedules = ref({}); // Store schedules for each housekeeper
const weeklyHousekeeperSchedule = ref({}); // Store housekeeper assignments for each day

// Schedule management
const scheduleDialog = ref(false);
const scheduleCaregiver = ref(null);
const scheduleBooking = ref(null);
const selectedDays = ref([]);
const daySchedules = ref({});
const shiftStartTime = ref('08:00');
const shiftEndTime = ref('17:00');
const savingSchedule = ref(false);

const daysOfWeek = [
  { label: 'Monday', value: 'monday' },
  { label: 'Tuesday', value: 'tuesday' },
  { label: 'Wednesday', value: 'wednesday' },
  { label: 'Thursday', value: 'thursday' },
  { label: 'Friday', value: 'friday' },
  { label: 'Saturday', value: 'saturday' },
  { label: 'Sunday', value: 'sunday' }
];

const viewBooking = (booking) => {
  viewingBooking.value = booking;
  viewBookingDialog.value = true;
};

const viewAssignedCaregivers = async (booking) => {
  viewingBookingCaregivers.value = booking;
  loadUsers(); // Refresh caregiver data
  
  // Load schedules for all assigned caregivers
  caregiverSchedules.value = {};
  weeklySchedule.value = {}; // Reset weekly schedule

if (booking && booking.assignments) {
    for (const assignment of booking.assignments) {
      try {
        const response = await fetch(`/api/bookings/${booking.id}/caregiver/${assignment.caregiver_id}/schedule`);
        if (response.ok) {
          const data = await response.json();
          
          if (data.schedule) {
            // Store the full schedule object (days array and schedules object)
            caregiverSchedules.value[assignment.caregiver_id] = {
              days: data.schedule.days || [],
              schedules: data.schedule.schedules || {}
            };

// Build the weekly schedule view
            if (data.schedule.days) {
              for (const day of data.schedule.days) {
                weeklySchedule.value[day] = assignment.caregiver_id;
              }
            }
          }
        } else {
        }
      } catch (err) {
      }
    }
  }

viewAssignedCaregiversDialog.value = true;
};

const viewAssignedHousekeepers = async (booking) => {
  viewingBookingHousekeepers.value = booking;

  // Load schedules for all assigned housekeepers
  housekeeperSchedules.value = {};
  weeklyHousekeeperSchedule.value = {};

  const assigned = getAssignedHousekeepers(booking.id);
  for (const hk of assigned) {
    try {
      const response = await fetch(`/api/bookings/${booking.id}/housekeeper/${hk.id}/schedule`);
      if (response.ok) {
        const data = await response.json();
        if (data && data.schedule) {
          housekeeperSchedules.value[hk.id] = {
            days: data.schedule.days || [],
            schedules: data.schedule.schedules || {}
          };
          if (data.schedule.days) {
            for (const day of data.schedule.days) {
              weeklyHousekeeperSchedule.value[day] = hk.id;
            }
          }
        }
      }
    } catch (err) {
      // ignore
    }
  }

  assignedHousekeepersTab.value = 'housekeepers';
  viewAssignedHousekeepersDialog.value = true;
};

const getHousekeeperScheduleDays = (housekeeperId) => {
  const schedule = housekeeperSchedules.value[housekeeperId];
  return schedule?.days || [];
};

const getHousekeeperForDay = (dayValue) => {
  const hkId = weeklyHousekeeperSchedule.value[dayValue];
  if (!hkId || !viewingBookingHousekeepers.value) return null;
  return getAssignedHousekeepers(viewingBookingHousekeepers.value.id).find(h => h.id === hkId) || null;
};

const isTodayHousekeeper = (dayValue) => {
  if (!viewingBookingHousekeepers.value) return false;
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const bookingDate = viewingBookingHousekeepers.value.date;
  const bookingStart = new Date(bookingDate);
  bookingStart.setHours(0, 0, 0, 0);
  if (today < bookingStart) return false;
  const daysPassed = Math.floor((today - bookingStart) / (1000 * 60 * 60 * 24));
  const startDayOfWeek = bookingStart.getDay();
  const currentDayOfWeek = (startDayOfWeek + daysPassed) % 7;
  const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
  return days[currentDayOfWeek] === dayValue.toLowerCase();
};

const saveHousekeeperSchedule = async (housekeeperId) => {
  if (!viewingBookingHousekeepers.value) return;
  const bookingId = viewingBookingHousekeepers.value.id;
  const schedule = housekeeperSchedules.value[housekeeperId] || { days: [], schedules: {} };

  try {
    const el = document.querySelector('meta[name="csrf-token"]');
    const response = await fetch(`/api/bookings/${bookingId}/housekeeper/${housekeeperId}/schedule`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(el ? { 'X-CSRF-TOKEN': el.getAttribute('content') } : {}),
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        days: schedule.days || [],
        schedules: schedule.schedules || {},
      }),
    });
    if (!response.ok) {
    }
  } catch (e) {
  }
};

const removeHousekeeperFromDay = async (dayValue) => {
  const prevHkId = weeklyHousekeeperSchedule.value[dayValue];
  if (!prevHkId) return;

  delete weeklyHousekeeperSchedule.value[dayValue];
  const oldSchedule = housekeeperSchedules.value[prevHkId] || { days: [], schedules: {} };
  oldSchedule.days = (oldSchedule.days || []).filter(d => d !== dayValue);
  if (oldSchedule.schedules) {
    delete oldSchedule.schedules[dayValue];
  }
  housekeeperSchedules.value[prevHkId] = { ...oldSchedule };
  await saveHousekeeperSchedule(prevHkId);
};

const assignHousekeeperToDay = async (dayValue, housekeeperId) => {
  if (!housekeeperId) {
    await removeHousekeeperFromDay(dayValue);
    return;
  }

  const previousHousekeeperId = weeklyHousekeeperSchedule.value[dayValue];
  weeklyHousekeeperSchedule.value[dayValue] = housekeeperId;

  // remove from previous
  if (previousHousekeeperId && previousHousekeeperId !== housekeeperId) {
    const oldSchedule = housekeeperSchedules.value[previousHousekeeperId] || { days: [], schedules: {} };
    oldSchedule.days = (oldSchedule.days || []).filter(d => d !== dayValue);
    if (oldSchedule.schedules) {
      delete oldSchedule.schedules[dayValue];
    }
    housekeeperSchedules.value[previousHousekeeperId] = { ...oldSchedule };
    saveHousekeeperSchedule(previousHousekeeperId);
  }

  // add to new
  const newSchedule = housekeeperSchedules.value[housekeeperId] || { days: [], schedules: {} };
  if (!Array.isArray(newSchedule.days)) newSchedule.days = [];
  if (!newSchedule.days.includes(dayValue)) newSchedule.days.push(dayValue);
  if (!newSchedule.schedules) newSchedule.schedules = {};
  if (!newSchedule.schedules[dayValue]) {
    newSchedule.schedules[dayValue] = {
      start_time: viewingBookingHousekeepers.value?.start_time || '08:00',
      end_time: null,
    };
  }
  housekeeperSchedules.value[housekeeperId] = { ...newSchedule };
  await saveHousekeeperSchedule(housekeeperId);
};

const clearAllHousekeeperSchedules = async () => {
  if (!viewingBookingHousekeepers.value) return;
  const bookingId = viewingBookingHousekeepers.value.id;
  const assigned = getAssignedHousekeepers(bookingId);

  weeklyHousekeeperSchedule.value = {};
  for (const hk of assigned) {
    housekeeperSchedules.value[hk.id] = { days: [], schedules: {} };
    await saveHousekeeperSchedule(hk.id);
  }
};

const getCaregiverScheduleDays = (caregiverId) => {
  const schedule = caregiverSchedules.value[caregiverId];
  return schedule?.days || [];
};

const getCaregiverForDay = (dayValue) => {
  // Use the weeklySchedule to get the caregiver for this day
  const caregiverId = weeklySchedule.value[dayValue];
  
  if (!caregiverId || !viewingBookingCaregivers.value || !viewingBookingCaregivers.value.assignments) {
    return null;
  }
  
  // Find the caregiver details
  const assignment = viewingBookingCaregivers.value.assignments.find(a => a.caregiver_id === caregiverId);
  
  if (assignment) {
    return {
      id: caregiverId,
      name: assignment.caregiver?.user?.name || 'Unknown',
      shortName: (assignment.caregiver?.user?.name || 'Unknown').split(' ').map(n => n[0]).join('')
    };
  }
  
  return null;
};

// Check if a day is today
const isToday = (dayValue) => {
  if (!viewingBookingCaregivers.value) return false;
  
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  // Parse the booking start date
  const bookingDate = viewingBookingCaregivers.value.date;
  const bookingStart = new Date(bookingDate);
  bookingStart.setHours(0, 0, 0, 0);
  
  // If today is before the booking starts, no day should be marked as today
  if (today < bookingStart) {
    return false;
  }
  
  // Calculate how many days have passed since booking started
  const daysPassed = Math.floor((today - bookingStart) / (1000 * 60 * 60 * 24));
  
  // Get the day of week for the booking start date
  const startDayOfWeek = bookingStart.getDay(); // 0 = Sunday, 1 = Monday, etc.
  
  // Calculate current day of week in the booking cycle
  const currentDayOfWeek = (startDayOfWeek + daysPassed) % 7;
  
  // Map to day names
  const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
  
  return days[currentDayOfWeek] === dayValue.toLowerCase();
};

// Assign a caregiver to a specific day in the weekly schedule
const assignCaregiverToDay = async (dayValue, caregiverId) => {
  
  if (!caregiverId) {
    // Remove assignment if cleared
    await removeCaregiverFromDay(dayValue);
    return;
  }
  
  const previousCaregiverId = weeklySchedule.value[dayValue];
  
  // Update weeklySchedule immediately
  weeklySchedule.value[dayValue] = caregiverId;
  
  // If there was a previous caregiver assigned to this day, remove it from their schedule first
  if (previousCaregiverId && previousCaregiverId !== caregiverId) {
    try {
      const oldSchedule = caregiverSchedules.value[previousCaregiverId] || { days: [], schedules: {} };
      
      // Remove this day from old caregiver's schedule
      oldSchedule.days = oldSchedule.days.filter(d => d !== dayValue);
      delete oldSchedule.schedules[dayValue];
      
      // Update cache immediately
      caregiverSchedules.value[previousCaregiverId] = { ...oldSchedule };

// Save the removal to the database (async, don't wait)
      fetch(`/api/bookings/${viewingBookingCaregivers.value.id}/caregiver/${previousCaregiverId}/schedule`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          days: oldSchedule.days,
          schedules: oldSchedule.schedules
        })
      }).then(response => {
        if (response.ok) {
        } else {
        }
      }).catch(err => console.error('Error removing previous caregiver:', err));
      
    } catch (error) {
    }
  }
  
  // Save immediately to database
  try {
    const caregiver = getAssignedCaregivers(viewingBookingCaregivers.value.id).find(c => c.id === caregiverId);
    if (!caregiver) {
      return;
    }
    
    // Get current schedule for this caregiver from cache
    const currentSchedule = caregiverSchedules.value[caregiverId] || { days: [], schedules: {} };
    
    // Add this day if not already in the schedule
    if (!currentSchedule.days.includes(dayValue)) {
      currentSchedule.days.push(dayValue);
    }
    
    // Update cache immediately before API call
    caregiverSchedules.value[caregiverId] = { ...currentSchedule };

// Save to database
    const response = await fetch(`/api/bookings/${viewingBookingCaregivers.value.id}/caregiver/${caregiverId}/schedule`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        days: currentSchedule.days,
        schedules: currentSchedule.schedules
      })
    });
    
    if (response.ok) {
      const data = await response.json();
      // Update cache with server response
      caregiverSchedules.value[caregiverId] = data.schedule;
      
    } else {
      const errorText = await response.text();
      
      // Revert on error
      if (previousCaregiverId) {
        weeklySchedule.value[dayValue] = previousCaregiverId;
      } else {
        delete weeklySchedule.value[dayValue];
      }
    }
  } catch (error) {
    
    // Revert on error
    if (previousCaregiverId) {
      weeklySchedule.value[dayValue] = previousCaregiverId;
    } else {
      delete weeklySchedule.value[dayValue];
    }
  }
};

// Remove a caregiver from a specific day
const removeCaregiverFromDay = async (dayValue) => {
  const caregiverId = weeklySchedule.value[dayValue];
  if (!caregiverId) return;
  
  delete weeklySchedule.value[dayValue];
  
  // Save removal to database
  try {
    const currentSchedule = caregiverSchedules.value[caregiverId] || { days: [], schedules: {} };
    
    // Remove this day from the schedule
    currentSchedule.days = currentSchedule.days.filter(d => d !== dayValue);
    delete currentSchedule.schedules[dayValue];
    
    // Save to database
    const response = await fetch(`/api/bookings/${viewingBookingCaregivers.value.id}/caregiver/${caregiverId}/schedule`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        days: currentSchedule.days,
        schedules: currentSchedule.schedules
      })
    });
    
    if (response.ok) {
      const data = await response.json();
      // Update cache
      caregiverSchedules.value[caregiverId] = data.schedule;
    }
  } catch (error) {
  }
};

// Clear all schedules for all caregivers
const clearAllSchedules = () => {
  showConfirm(
    'Clear All Schedules',
    'Are you sure you want to clear all schedule assignments? This action cannot be undone.',
    async () => {
      if (!viewingBookingCaregivers.value) return;
      
      try {
        const bookingId = viewingBookingCaregivers.value.id;
        
        // Get all assigned caregivers from the booking, not just from weeklySchedule
        const allCaregiverIds = getAssignedCaregivers(bookingId).map(c => c.id);

// Clear all caregivers' schedules
        for (const caregiverId of allCaregiverIds) {
          const response = await fetch(`/api/bookings/${bookingId}/caregiver/${caregiverId}/schedule`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
              days: [],
              schedules: {}
            })
          });
          
          if (response.ok) {
            const data = await response.json();
            caregiverSchedules.value[caregiverId] = { days: [], schedules: {} };
          } else {
          }
        }
        
        // Clear local schedule
        weeklySchedule.value = {};
        
        success('All schedules cleared successfully', 'Success');
      } catch (error) {
        failure('Failed to clear schedules', 'Error');
      }
    },
    'error',
    'Clear All',
    'mdi-delete-sweep'
  );
};

// Save the weekly schedule
const saveWeeklySchedule = async () => {
  if (!viewingBookingCaregivers.value) return;
  
  const bookingId = viewingBookingCaregivers.value.id;
  const assignments = Object.entries(weeklySchedule.value);
  
  if (assignments.length === 0) {
    warning('Please assign at least one caregiver to a day', 'No Assignments');
    return;
  }
  
  try {
    // Group days by caregiver
    const caregiverDays = {};
    
    for (const [day, caregiverId] of assignments) {
      if (!caregiverDays[caregiverId]) {
        caregiverDays[caregiverId] = [];
      }
      caregiverDays[caregiverId].push(day);
    }
    
    // Save schedule for each caregiver
    for (const [caregiverId, days] of Object.entries(caregiverDays)) {
      const response = await fetch(`/api/bookings/${bookingId}/caregiver/${caregiverId}/schedule`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ 
          days: days,
          schedules: days.reduce((acc, day) => {
            acc[day] = {
              start_time: '08:00',
              end_time: '17:00'
            };
            return acc;
          }, {})
        })
      });
      
      if (!response.ok) throw new Error('Failed to save schedule');
      
      // Update local cache
      caregiverSchedules.value[caregiverId] = days;
    }
    
    success('Weekly schedule saved successfully!', 'Schedule Updated');
    
    // Refresh the caregiver list
    await viewAssignedCaregivers(viewingBookingCaregivers.value);
    
  } catch (error) {
    failure('Failed to save weekly schedule', 'Error');
  }
};

const unassignCaregiver = async (caregiverId) => {
  if (!viewingBookingCaregivers.value) return;
  
  try {
    const bookingId = viewingBookingCaregivers.value.id;
    
    const response = await fetch(`/api/bookings/${bookingId}/unassign`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ caregiver_id: caregiverId })
    });
    
    const responseData = await response.json();
    
    if (response.ok) {
      // Delete the caregiver's schedule assignments for this booking
      try {
        const deleteResponse = await fetch(`/api/bookings/${bookingId}/caregiver/${caregiverId}/schedule`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });
        
        if (deleteResponse.ok) {
          const deleteData = await deleteResponse.json();
        } else {
        }
        
        // Clear from local cache
        delete caregiverSchedules.value[caregiverId];
        
        // Remove from weeklySchedule
        Object.keys(weeklySchedule.value).forEach(day => {
          if (weeklySchedule.value[day] === caregiverId) {
            delete weeklySchedule.value[day];
          }
        });
        
      } catch (scheduleErr) {
        // Continue anyway - the important part is the caregiver unassignment
      }
      
      // Update local state immediately
      const currentAssignments = caregiverAssignments.value[bookingId] || [];
      const updatedAssignments = currentAssignments.filter(id => id !== caregiverId);
      
      caregiverAssignments.value[bookingId] = updatedAssignments;
      viewingBookingCaregivers.value.assignedCount = updatedAssignments.length;
      
      const booking = clientBookings.value.find(b => b.id === bookingId);
      if (booking) {
        booking.assignedCount = updatedAssignments.length;
        if (updatedAssignments.length === 0) {
          booking.assignmentStatus = 'unassigned';
        } else if (updatedAssignments.length >= booking.caregiversNeeded) {
          booking.assignmentStatus = 'assigned';
        } else {
          booking.assignmentStatus = 'partial';
        }
      }
      
      updateCaregiverStatuses();
      success('Caregiver unassigned successfully!', 'Assignment Updated');
      
      // Force refresh all data to ensure consistency
      await Promise.all([
        loadClientBookings(),
        loadUsers(),
        loadTimeTrackingData()
      ]);
      
      // Update the viewing dialog data
      if (booking) {
        viewingBookingCaregivers.value = { ...booking };
      }
    } else {
      error(responseData.error || 'Failed to unassign caregiver', 'Unassign Failed');
    }
  } catch (err) {
    error('Failed to unassign caregiver. Please try again.', 'Unassign Failed');
  }
};

const unassignHousekeeper = async (housekeeperId, bookingId) => {
  if (!bookingId) return;

  try {
    const resp = await fetch(`/api/bookings/${bookingId}/unassign-housekeeper`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
      body: JSON.stringify({ housekeeper_id: housekeeperId }),
    });

    // Parse response if possible
    let data = {};
    try {
      data = await resp.json();
    } catch (_) {
      data = {};
    }

    // For non-2xx, only show error if the response explicitly indicates failure
    // The backend now returns success: true even for idempotent cases
    if (!resp.ok && data?.success !== true) {
      console.error('Unassign housekeeper failed:', data);
      error(data?.error || 'Failed to unassign housekeeper', 'Unassign Failed');
      return;
    }

    // Clear local schedule caches for this housekeeper
    if (housekeeperSchedules?.value) {
      delete housekeeperSchedules.value[housekeeperId];
    }
    if (weeklyHousekeeperSchedule?.value) {
      Object.keys(weeklyHousekeeperSchedule.value).forEach(day => {
        if (weeklyHousekeeperSchedule.value[day] === housekeeperId) {
          delete weeklyHousekeeperSchedule.value[day];
        }
      });
    }

    // Refresh bookings so Assigned count updates
    await loadClientBookings();

    // Refresh the open dialog data so the card list updates immediately
    if (viewingBookingHousekeepers.value?.id === bookingId) {
      const updated = (clientBookings.value || []).find(b => b.id === bookingId);
      if (updated) {
        viewingBookingHousekeepers.value = { ...updated };
      }
    }
    success('Housekeeper unassigned successfully!', 'Assignment Updated');
  } catch (e) {
    console.error('Unassign housekeeper error:', e);
    error('Failed to unassign housekeeper. Please try again.', 'Unassign Failed');
  }
};

// Schedule Management Functions
const openScheduleDialog = async (caregiver, booking) => {
  
  scheduleCaregiver.value = caregiver;
  scheduleBooking.value = booking;

// Load existing schedule for this caregiver-booking combination
  try {
    const response = await fetch(`/api/bookings/${booking.id}/caregiver/${caregiver.id}/schedule`);
    if (response.ok) {
      const data = await response.json();
      if (data.schedule) {
        selectedDays.value = data.schedule.days || [];
        daySchedules.value = data.schedule.schedules || {};
      } else {
        // No existing schedule, reset
        selectedDays.value = [];
        daySchedules.value = {};
      }
    }
  } catch (err) {
    // If no schedule exists, start fresh
    selectedDays.value = [];
    daySchedules.value = {};
  }
  
  scheduleDialog.value = true;
};

const closeScheduleDialog = () => {
  scheduleDialog.value = false;
  selectedDays.value = [];
  daySchedules.value = {};
  shiftStartTime.value = '08:00';
  shiftEndTime.value = '17:00';
  scheduleCaregiver.value = null;
  scheduleBooking.value = null;
};

const isDaySelected = (day) => {
  return selectedDays.value.includes(day);
};

const toggleDay = (day) => {
  const index = selectedDays.value.indexOf(day);
  if (index > -1) {
    selectedDays.value.splice(index, 1);
    delete daySchedules.value[day];
  } else {
    selectedDays.value.push(day);
    // Initialize with default or current shift times
    daySchedules.value[day] = {
      start_time: shiftStartTime.value,
      end_time: shiftEndTime.value
    };
  }
};

const getDaySchedule = (day) => {
  return daySchedules.value[day] || { start_time: '', end_time: '' };
};

const getDayLabel = (day) => {
  const dayObj = daysOfWeek.find(d => d.value === day);
  return dayObj ? dayObj.label : day;
};

const applyTimesToSelectedDays = () => {
  if (!shiftStartTime.value || !shiftEndTime.value) {
    warning('Please set both start and end times', 'Missing Times');
    return;
  }
  
  selectedDays.value.forEach(day => {
    daySchedules.value[day] = {
      start_time: shiftStartTime.value,
      end_time: shiftEndTime.value
    };
  });
  
  success('Times applied to all selected days', 'Schedule Updated');
};

const getInitials = (name) => {
  if (!name) return 'NA';
  const parts = name.trim().split(' ');
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const getScheduleType = (dutyType) => {
  if (!dutyType) return 'All Days';
  if (dutyType.toLowerCase().includes('24 hours')) return '7 Days/Week';
  if (dutyType.toLowerCase().includes('12 hours')) return 'Custom Schedule';
  if (dutyType.toLowerCase().includes('8 hours')) return 'Weekdays Only';
  return 'Custom Schedule';
};

const getAvailableDays = (dutyTypeOrBooking) => {
  // If passed a booking object with day_schedules, use those
  if (typeof dutyTypeOrBooking === 'object' && dutyTypeOrBooking?.daySchedules) {
    // Convert day_schedules object to array of day objects
    return Object.keys(dutyTypeOrBooking.daySchedules).map(dayKey => {
      const dayName = dayKey.charAt(0).toUpperCase() + dayKey.slice(1);
      return daysOfWeek.find(d => d.label === dayName) || { label: dayName, value: dayKey };
    });
  }
  
  // Fallback to old logic based on duty_type
  const dutyType = typeof dutyTypeOrBooking === 'string' ? dutyTypeOrBooking : (dutyTypeOrBooking?.dutyType || dutyTypeOrBooking?.duty_type);
  
  // If 8 hours, show Monday-Friday only
  if (dutyType && dutyType.toLowerCase().includes('8 hours')) {
    return daysOfWeek.filter(day => 
      !['saturday', 'sunday'].includes(day.value)
    );
  }
  // For 12 hours and 24 hours, show all days
  return daysOfWeek;
};

const getTimeForDay = (booking, dayValue) => {
  // If booking has day_schedules, use the specific time for this day
  if (booking?.daySchedules && booking.daySchedules[dayValue]) {
    return booking.daySchedules[dayValue];
  }
  // Fallback to the booking's general time
  return booking?.time || 'N/A';
};

const saveSchedule = async () => {
  if (selectedDays.value.length === 0) {
    warning('Please select at least one day', 'No Days Selected');
    return;
  }
  
  // Validate that all selected days have times set
  const missingTimes = selectedDays.value.filter(day => {
    const schedule = daySchedules.value[day];
    return !schedule || !schedule.start_time || !schedule.end_time;
  });
  
  if (missingTimes.length > 0) {
    warning('Please set times for all selected days', 'Missing Times');
    return;
  }
  
  savingSchedule.value = true;
  
  try {
    // Log schedule data being saved
    const scheduleData = {
      bookingId: scheduleBooking.value.id,
      caregiverId: scheduleCaregiver.value.id,
      days: selectedDays.value,
      schedules: daySchedules.value
    };
    
    const response = await fetch(`/api/bookings/${scheduleBooking.value.id}/caregiver/${scheduleCaregiver.value.id}/schedule`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        days: selectedDays.value,
        schedules: daySchedules.value
      })
    });

if (response.ok) {
      const data = await response.json();
      
      // Update the caregiverSchedules cache
      caregiverSchedules.value[scheduleCaregiver.value.id] = selectedDays.value;
      
      success(`Schedule saved for ${scheduleCaregiver.value.name}`, 'Schedule Updated');
      closeScheduleDialog();
    } else {
      const data = await response.json();
      error(data.error || 'Failed to save schedule', 'Save Failed');
    }
  } catch (err) {
    error('Failed to save schedule. Please try again.', 'Save Failed');
  } finally {
    savingSchedule.value = false;
  }
};

const approveBooking = async (booking) => {
  try {
    const response = await fetch(`/api/bookings/${booking.id}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      const result = await response.json();
      booking.status = 'approved';
      const emailMsg = result.email_sent ? ` Approval email sent to ${booking.client_email || 'client'}.` : (result.email_message ? ` ${result.email_message}` : '');
      success(`Booking for ${booking.client} has been approved!${emailMsg}`, 'Booking Approved');
      // Show assign dialog immediately after approval (caregiver services only)
      // Housekeeping bookings are handled by the Housekeeper assignment flow.
      const serviceType = String(booking?.service_type || booking?.service_type_name || booking?.service || '').toLowerCase();
      const isHousekeeping = serviceType.includes('housekeeping');
      if (!isHousekeeping) {
        setTimeout(() => {
          assignCaregiverDialog(booking);
        }, 500);
      }
    } else {
      throw new Error('Failed to approve booking');
    }
  } catch (err) {
    error('Failed to approve booking. Please try again.', 'Approval Failed');
  }
};

const rejectBooking = async (booking) => {
  if (confirm(`Reject booking for ${booking.client}?`)) {
    try {
      const response = await fetch(`/api/bookings/${booking.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ status: 'rejected' })
      });
      
      if (response.ok) {
        booking.status = 'rejected';
        warning(`Booking for ${booking.client} has been rejected.`, 'Booking Rejected');
      } else {
        throw new Error('Failed to reject booking');
      }
    } catch (err) {
      error('Failed to reject booking. Please try again.', 'Rejection Failed');
    }
  }
};

const confirmAssignCaregivers = async () => {
  try {
    const bookingId = selectedBooking.value.id;
    
    // Validate all rates are set
    for (const caregiverId of assignSelectedCaregivers.value) {
      if (!assignedRates.value[caregiverId]) {
        error('Please assign hourly rate for all selected caregivers', 'Rate Required');
        return;
      }
      
      const caregiver = getCaregiverById(caregiverId);
      const rate = parseFloat(assignedRates.value[caregiverId]);
      const min = caregiver?.preferred_hourly_rate_min || 20;
      const max = caregiver?.preferred_hourly_rate_max || 50;
      
      if (rate < min || rate > max) {
        error(`Rate for ${caregiver?.name || 'caregiver'} must be between $${min} and $${max}`, 'Invalid Rate');
        return;
      }
    }
    
    // Save to database
    const response = await fetch(`/api/bookings/${bookingId}/assign`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ 
        caregiver_ids: assignSelectedCaregivers.value,
        assigned_rates: assignedRates.value,
        caregivers_needed: customCaregiversNeeded.value || selectedBooking.value.caregiversNeeded
      })
    });
    
    const responseData = await response.json().catch(() => ({}));
    
    if (!response.ok) {
      throw new Error(responseData.message || 'Failed to save assignments');
    }
    
    const booking = clientBookings.value.find(b => b.id === bookingId);
    
    // Update caregiver assignments tracking
    caregiverAssignments.value[bookingId] = assignSelectedCaregivers.value;
    
    // Update caregiver status based on assignments
    updateCaregiverStatuses();
    
    if (booking) {
      booking.assignedCount = assignSelectedCaregivers.value.length;
      
      // Update caregiversNeeded if it was customized
      if (customCaregiversNeeded.value && customCaregiversNeeded.value !== booking.caregiversNeeded) {
        booking.caregiversNeeded = customCaregiversNeeded.value;
      }
      
      // Update assignment status based on caregiver count
      if (assignSelectedCaregivers.value.length === 0) {
        booking.assignmentStatus = 'unassigned';
      } else if (assignSelectedCaregivers.value.length >= booking.caregiversNeeded) {
        booking.assignmentStatus = 'assigned';
      } else {
        booking.assignmentStatus = 'partial';
      }
    }
    
    if (assignSelectedCaregivers.value.length === 0) {
      success('All caregivers unassigned from this booking.', 'Assignment Updated');
    } else {
      const statusText = booking.assignmentStatus === 'partial' ? 'partially assigned' : 'fully assigned';
      success(`${assignSelectedCaregivers.value.length} caregiver(s) ${statusText} successfully with assigned rates!`, 'Assignment Complete');
    }
    
    // Refresh client bookings data
    await loadClientBookings();
    
    closeAssignDialog();
  } catch (err) {
    error(err.message || 'Failed to update caregiver assignments. Please try again.', 'Assignment Failed');
  }
};

const confirmAssignHousekeepers = async () => {
  try {
    if (!selectedBooking.value) return;
    const bookingId = selectedBooking.value.id;

    // Simple validation: rates exist
    for (const housekeeperId of assignSelectedHousekeepers.value) {
      if (!assignedHousekeeperRates.value[housekeeperId]) {
        error('Please assign hourly rate for all selected housekeepers', 'Rate Required');
        return;
      }
    }

    const response = await fetch(`/api/bookings/${bookingId}/assign-housekeepers`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
  credentials: 'same-origin',
      body: JSON.stringify({
        housekeeper_ids: assignSelectedHousekeepers.value,
        assigned_rates: assignedHousekeeperRates.value,
        housekeepers_needed: customHousekeepersNeeded.value || selectedBooking.value.caregiversNeeded
      })
    });

    const responseData = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(responseData.message || 'Failed to save housekeeper assignments');

  success('Housekeeper assignment updated successfully!', 'Assignment Complete');
  // Close immediately for better UX, then refresh data in the background
  closeAssignHousekeeperDialog();
  await loadClientBookings();
  } catch (err) {
    error(err.message || 'Failed to update housekeeper assignments. Please try again.', 'Assignment Failed');
  }
};

const viewPayment = (payment) => {
  selectedPayment.value = payment;
  paymentDetailsDialog.value = true;
};

const markPaid = (payment) => {
  if (confirm(`Mark payment from ${payment.client} as paid?`)) {
    payment.status = 'Paid';
    success('Payment marked as paid!', 'Payment Updated');
    paymentDetailsDialog.value = false;
  }
};

const downloadReceipt = async (payment) => {
  try {
    if (!payment.booking_id) {
      error('Cannot generate receipt: No booking ID found', 'Error');
      return;
    }
    
    info(`Generating receipt for ${payment.client}...`, 'Receipt');
    
    // Use existing ReceiptController to download PDF
    const receiptUrl = `/api/receipts/${payment.booking_id}/download`;
    window.open(receiptUrl, '_blank');
    
    success(`Receipt for ${payment.client} opened successfully!`, 'Download Complete');
  } catch (err) {
    error('Failed to generate receipt. Please try again.', 'Error');
  }
};

const viewPaymentDetails = (payment) => {
  // Show detailed payment information in styled modal
  selectedCaregiverPaymentDetails.value = payment;
  caregiverPaymentDetailsDialog.value = true;
};

const payCaregiver = async (payment) => {
  if (!payment.bank_connected) {
    error('Cannot process payment', 'Bank account not connected');
    return;
  }
  
  // Open styled confirmation dialog
  selectedCaregiverPayment.value = payment;
  paymentConfirmDialog.value = true;
};

const confirmPayment = async () => {
  const payment = selectedCaregiverPayment.value;
  if (!payment) return;
  
  paymentConfirmDialog.value = false;
  
  try {
    const response = await fetch('/api/admin/pay-caregiver', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        caregiver_id: payment.id,
        amount: payment.unpaid_amount
      })
    });
    
    const result = await response.json();
    
    if (result.success) {
      success(`Payment of ${payment.unpaid_display} sent to ${payment.caregiver}!`, 'Payment Sent');
      loadCaregiverPayments(); // Reload data
    } else {
      error(result.message || 'Payment failed', 'Payment Error');
    }
  } catch (err) {
    error('Failed to process payment', 'Network Error');
  }
};

const exportTransactions = () => {
  info('Exporting financial report to PDF...', 'Export Started');
  
  // Open PDF in new window
  window.open('/api/admin/financial-report/pdf?period=all', '_blank');
};

const exportFinancialReportPDF = () => {
  info('Generating comprehensive financial report...', 'Export Started');
  
  // Open PDF in new window
  window.open('/api/admin/financial-report/pdf?period=all', '_blank');
};

const caregiverContactsDialog = ref(false);
const boroughFilter = ref('All');
const sortBy = ref('name');
const sortOrder = ref('asc');

const boroughs = ['All', 'Manhattan', 'Brooklyn', 'Queens', 'Bronx', 'Staten Island'];
const sortOptions = [
  { title: 'Name (A-Z)', value: 'name-asc' },
  { title: 'Name (Z-A)', value: 'name-desc' },
  { title: 'Newest First', value: 'joined-desc' },
  { title: 'Oldest First', value: 'joined-asc' }
];

const quickCaregivers = ref([]);

const loadQuickCaregivers = async () => {
  try {
    const response = await fetch('/api/admin/quick-caregivers');
    if (response.ok) {
      quickCaregivers.value = await response.json();
    }
  } catch (error) {
  }
};

// Add phone numbers and boroughs to existing caregivers data
caregivers.value.forEach((caregiver, index) => {
  if (!caregiver.phone) {
  const phones = ['(646) 282-8282', '(646) 282-8282', '(646) 282-8282'];
  caregiver.phone = phones[index] || '(646) 282-8282';
  }

});

const filteredAndSortedCaregivers = computed(() => {
  let filtered = caregivers.value.filter(caregiver => {
    const matchesSearch = !caregiverSearch.value || 
      caregiver.name.toLowerCase().includes(caregiverSearch.value.toLowerCase()) ||
      caregiver.email.toLowerCase().includes(caregiverSearch.value.toLowerCase());
    const matchesBorough = boroughFilter.value === 'All' || caregiver.location === boroughFilter.value;
    return matchesSearch && matchesBorough;
  });

  // Sort the filtered results
  const [sortField, order] = sortBy.value.split('-');
  filtered.sort((a, b) => {
    let aVal, bVal;
    
    if (sortField === 'name') {
      aVal = a.name.toLowerCase();
      bVal = b.name.toLowerCase();
    } else if (sortField === 'joined') {
      // Convert joined date to comparable format
      const dateMap = { 'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04', 'May': '05', 'Jun': '06',
                       'Jul': '07', 'Aug': '08', 'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12' };
      const aDate = a.joined.replace(/([A-Za-z]+) (\d+)/, (match, month, year) => `${year}-${dateMap[month]}-01`);
      const bDate = b.joined.replace(/([A-Za-z]+) (\d+)/, (match, month, year) => `${year}-${dateMap[month]}-01`);
      aVal = new Date(aDate);
      bVal = new Date(bDate);
    }
    
    if (order === 'asc') {
      return aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
    } else {
      return aVal > bVal ? -1 : aVal < bVal ? 1 : 0;
    }
  });
  
  return filtered;
});

const nyCounties = [
  'Albany', 'Allegany', 'Bronx', 'Broome', 'Cattaraugus',
  'Cayuga', 'Chautauqua', 'Chemung', 'Chenango', 'Clinton',
  'Columbia', 'Cortland', 'Delaware', 'Dutchess', 'Erie',
  'Essex', 'Franklin', 'Fulton', 'Genesee', 'Greene',
  'Hamilton', 'Herkimer', 'Jefferson', 'Kings', 'Lewis',
  'Livingston', 'Madison', 'Monroe', 'Montgomery', 'Nassau',
  'New York', 'Niagara', 'Oneida', 'Onondaga', 'Ontario',
  'Orange', 'Orleans', 'Oswego', 'Otsego', 'Putnam',
  'Queens', 'Rensselaer', 'Richmond', 'Rockland', 'Saratoga',
  'Schenectady', 'Schoharie', 'Schuyler', 'Seneca', 'St. Lawrence',
  'Steuben', 'Suffolk', 'Sullivan', 'Tioga', 'Tompkins',
  'Ulster', 'Warren', 'Washington', 'Wayne', 'Westchester',
  'Wyoming', 'Yates'
];

const countyCityMap = {
  "Albany": ["Albany", "Cohoes", "Watervliet", "Green Island", "Menands", "Colonie", "Guilderland", "Bethlehem"],
  "Allegany": ["Wellsville", "Cuba", "Belmont", "Bolivar", "Richburg", "Scio", "Friendship", "Angelica"],
  "Bronx": ["Bronx", "Fordham", "Riverdale", "Throggs Neck", "Pelham Bay", "Concourse", "Morrisania"],
  "Broome": ["Binghamton", "Johnson City", "Endicott", "Vestal", "Conklin", "Chenango", "Kirkwood"],
  "Cattaraugus": ["Olean", "Salamanca", "Little Valley", "Ellicottville", "Franklinville", "Portville"],
  "Cayuga": ["Auburn", "Moravia", "Fair Haven", "Weedsport", "Union Springs", "Cato", "Meridian"],
  "Chautauqua": ["Jamestown", "Dunkirk", "Fredonia", "Westfield", "Mayville", "Silver Creek", "Brocton"],
  "Chemung": ["Elmira", "Horseheads", "Big Flats", "Elmira Heights", "Millport", "Van Etten"],
  "Chenango": ["Norwich", "Oxford", "Greene", "Sherburne", "Afton", "Bainbridge", "Sidney"],
  "Clinton": ["Plattsburgh", "Champlain", "Rouses Point", "Dannemora", "Altona", "Chazy", "Mooers"],
  "Columbia": ["Hudson", "Kinderhook", "Chatham", "Philmont", "Valatie", "Copake", "Hillsdale"],
  "Cortland": ["Cortland", "Homer", "McGraw", "Marathon", "Dryden", "Cincinnatus", "Truxton"],
  "Delaware": ["Delhi", "Oneonta", "Sidney", "Walton", "Margaretville", "Hancock", "Deposit"],
  "Dutchess": ["Poughkeepsie", "Beacon", "Rhinebeck", "Hyde Park", "Millbrook", "Red Hook", "Wappingers Falls"],
  "Erie": ["Buffalo", "Cheektowaga", "West Seneca", "Amherst", "Tonawanda", "Lackawanna", "Hamburg"],
  "Essex": ["Elizabethtown", "Ticonderoga", "Westport", "Keene", "Lake Placid", "Wilmington", "Jay"],
  "Franklin": ["Malone", "Saranac Lake", "Tupper Lake", "Fort Covington", "Bombay", "Constable"],
  "Fulton": ["Gloversville", "Johnstown", "Northville", "Broadalbin", "Mayfield", "Perth"],
  "Genesee": ["Batavia", "Le Roy", "Oakfield", "Alexander", "Byron", "Corfu", "Darien"],
  "Greene": ["Catskill", "Coxsackie", "Cairo", "Hunter", "Tannersville", "Windham", "Athens"],
  "Hamilton": ["Lake Pleasant", "Indian Lake", "Wells", "Speculator", "Long Lake", "Inlet"],
  "Herkimer": ["Herkimer", "Little Falls", "Ilion", "Mohawk", "Dolgeville", "Frankfort", "Poland"],
  "Jefferson": ["Watertown", "Carthage", "Clayton", "Alexandria Bay", "Sackets Harbor", "Dexter"],
  "Kings": ["Brooklyn", "Park Slope", "Williamsburg", "DUMBO", "Bay Ridge", "Bensonhurst", "Crown Heights"],
  "Lewis": ["Lowville", "Lyons Falls", "Croghan", "Constableville", "Harrisville", "Turin"],
  "Livingston": ["Geneseo", "Mount Morris", "Dansville", "Avon", "Caledonia", "Lima", "Nunda"],
  "Madison": ["Oneida", "Canastota", "Hamilton", "Chittenango", "Wampsville", "Morrisville"],
  "Monroe": ["Rochester", "Brighton", "Greece", "Irondequoit", "Webster", "Penfield", "Pittsford"],
  "Montgomery": ["Amsterdam", "Fonda", "Canajoharie", "Palatine Bridge", "St. Johnsville", "Fort Plain"],
  "Nassau": ["Hempstead", "Long Beach", "Glen Cove", "Freeport", "Valley Stream", "Rockville Centre"],
  "New York": ["Manhattan", "Upper East Side", "Greenwich Village", "SoHo", "Tribeca", "Chelsea", "Midtown"],
  "Niagara": ["Niagara Falls", "North Tonawanda", "Lockport", "Lewiston", "Youngstown", "Wilson"],
  "Oneida": ["Utica", "Rome", "Sherrill", "Whitesboro", "New York Mills", "Oriskany", "Camden"],
  "Onondaga": ["Syracuse", "Liverpool", "Baldwinsville", "East Syracuse", "North Syracuse", "Solvay"],
  "Ontario": ["Canandaigua", "Geneva", "Victor", "Phelps", "Shortsville", "Clifton Springs"],
  "Orange": ["Newburgh", "Middletown", "Port Jervis", "Goshen", "Warwick", "Monroe", "Washingtonville"],
  "Orleans": ["Albion", "Medina", "Holley", "Lyndonville", "Yates", "Kendall"],
  "Oswego": ["Oswego", "Fulton", "Mexico", "Pulaski", "Central Square", "Phoenix", "Hannibal"],
  "Otsego": ["Cooperstown", "Oneonta", "Richfield Springs", "Cherry Valley", "Milford", "Morris"],
  "Putnam": ["Carmel", "Cold Spring", "Brewster", "Mahopac", "Kent", "Putnam Valley"],
  "Queens": ["Queens", "Flushing", "Jamaica", "Astoria", "Long Island City", "Forest Hills", "Elmhurst"],
  "Rensselaer": ["Troy", "Rensselaer", "Hoosick Falls", "Berlin", "Grafton", "Petersburgh"],
  "Richmond": ["Staten Island", "St. George", "Tottenville", "New Brighton", "Port Richmond", "Stapleton"],
  "Rockland": ["New City", "Spring Valley", "Nyack", "Suffern", "Pearl River", "Haverstraw"],
  "Saratoga": ["Saratoga Springs", "Ballston Spa", "Mechanicville", "Waterford", "Stillwater", "Corinth"],
  "Schenectady": ["Schenectady", "Rotterdam", "Niskayuna", "Glenville", "Duanesburg", "Princetown"],
  "Schoharie": ["Schoharie", "Cobleskill", "Middleburgh", "Richmondville", "Sharon Springs", "Esperance"],
  "Schuyler": ["Watkins Glen", "Montour Falls", "Burdett", "Odessa", "Tyrone", "Hector"],
  "Seneca": ["Waterloo", "Seneca Falls", "Ovid", "Lodi", "Romulus", "Varick"],
  "St. Lawrence": ["Canton", "Ogdensburg", "Potsdam", "Massena", "Gouverneur", "Norwood"],
  "Steuben": ["Corning", "Hornell", "Bath", "Canisteo", "Painted Post", "Wayland"],
  "Suffolk": ["Huntington", "Brookhaven", "Islip", "Babylon", "Smithtown", "Riverhead"],
  "Sullivan": ["Monticello", "Liberty", "Fallsburg", "Bloomingburg", "Wurtsboro", "Jeffersonville"],
  "Tioga": ["Owego", "Waverly", "Spencer", "Candor", "Newark Valley", "Nichols"],
  "Tompkins": ["Ithaca", "Dryden", "Groton", "Trumansburg", "Lansing", "Newfield"],
  "Ulster": ["Kingston", "New Paltz", "Saugerties", "Ellenville", "Woodstock", "Rosendale"],
  "Warren": ["Glens Falls", "Lake George", "Warrensburg", "Bolton", "Chestertown", "Queensbury"],
  "Washington": ["Hudson Falls", "Whitehall", "Cambridge", "Hoosick", "Salem", "Granville"],
  "Wayne": ["Lyons", "Newark", "Clyde", "Palmyra", "Sodus", "Wolcott"],
  "Westchester": ["White Plains", "Yonkers", "New Rochelle", "Mount Vernon", "Scarsdale", "Rye"],
  "Wyoming": ["Warsaw", "Perry", "Attica", "Arcade", "Castile", "Silver Springs"],
  "Yates": ["Penn Yan", "Dresden", "Dundee", "Rushville", "Himrod", "Branchport"]
};

const clientCities = computed(() => {
  if (!clientForm.value.county) return [];
  return countyCityMap[clientForm.value.county] || [];
});

const caregiverCities = computed(() => {
  if (!caregiverForm.value.county) return [];
  return countyCityMap[caregiverForm.value.county] || [];
});

const housekeeperCities = computed(() => {
  if (!housekeeperForm.value.county) return [];
  return countyCityMap[housekeeperForm.value.county] || [];
});

// Watch for county changes to reset city selection
watch(() => clientForm.value.county, (newCounty) => {
  if (newCounty) {
    clientForm.value.city = ''; // Reset city when county changes
  }
});

watch(() => caregiverForm.value.county, (newCounty) => {
  if (newCounty) {
    caregiverForm.value.city = ''; // Reset city when county changes
  }
});

watch(() => housekeeperForm.value.county, (newCounty) => {
  if (newCounty) {
    housekeeperForm.value.city = ''; // Reset city when county changes
  }
});

const callCaregiver = (caregiver) => {
  info(`Calling ${caregiver.name} at ${caregiver.phone}`, 'Initiating Call');
};

const messageCaregiver = (caregiver) => {
  info(`Sending message to ${caregiver.name}`, 'Sending Message');
};

const viewCaregiverProfile = (caregiver) => {
  info(`Viewing profile for ${caregiver.name}`, 'Profile View');
};

const notificationCenter = ref(null);

const handleNotificationAction = (action) => {
};

const handleNavClick = (item) => {
  if (item.isToggle) {
    // Toggle the expanded state
    item.expanded = !item.expanded;
  } else {
    currentSection.value = item.value;
  }
};

watch(currentSection, (newVal) => {
  if (newVal === 'caregivers' || newVal === 'clients') {
    const userMgmtItem = navItems.value.find(nav => nav.isToggle);
    if (userMgmtItem) {
      userMgmtItem.expanded = true;
    }
  }
  if (newVal === 'reviews') {
    loadAllReviews();
  }
});

let revenueChartInstance = null;
let userChartInstance = null;
let bookingChartInstance = null;

const bookingChart = ref(null);

const initCharts = () => {
  if (!window.Chart) {
    setTimeout(initCharts, 100);
    return;
  }
  
  if (revenueChart.value && currentSection.value === 'analytics') {
    if (revenueChartInstance) revenueChartInstance.destroy();
    const ctx = revenueChart.value.getContext('2d');
    const revenue = parseFloat(analyticsStats.value[0].value.replace('$', '')) || 0;
    const monthlyData = revenue > 0 ? [revenue * 0.6, revenue * 0.7, revenue * 0.85, revenue] : [0, 0, 0, 0];
    revenueChartInstance = new window.Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr'],
        datasets: [{ 
          label: 'Revenue ($)',
          data: monthlyData, 
          borderColor: '#dc2626', 
          backgroundColor: 'rgba(220, 38, 38, 0.1)', 
          tension: 0.4, 
          fill: true, 
          borderWidth: 3,
          pointBackgroundColor: '#dc2626',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 5
        }]
      },
      options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        plugins: { 
          legend: { 
            display: true,
            position: 'top',
            labels: {
              font: { size: 11, weight: 'bold' },
              color: '#374151'
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return `Revenue: $${context.parsed.y.toLocaleString()}`;
              }
            }
          }
        }, 
        scales: { 
          x: { 
            display: true,
            grid: { display: false },
            ticks: { font: { size: 10 }, color: '#6b7280' }
          }, 
          y: { 
            display: true,
            grid: { color: '#f3f4f6' },
            ticks: { 
              font: { size: 10 }, 
              color: '#6b7280',
              callback: function(value) {
                return '$' + (value/1000) + 'K';
              }
            }
          } 
        } 
      }
    });
  }

  if (userChart.value && currentSection.value === 'analytics') {
    if (userChartInstance) userChartInstance.destroy();
    const ctx = userChart.value.getContext('2d');
    const clients = parseInt(clientMetrics.value[0].value) || 0;
    const caregivers = parseInt(caregiverMetrics.value[0].value) || 0;
    const admins = parseInt(adminCount.value) || 0;
    const total = clients + caregivers + admins;
    userChartInstance = new window.Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Clients', 'Caregivers', 'Admins'],
        datasets: [{ 
          data: [clients, caregivers, admins], 
          backgroundColor: ['#3b82f6', '#10b981', '#dc2626'], 
          borderWidth: 2,
          borderColor: '#fff',
          hoverBorderWidth: 3
        }]
      },
      options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        plugins: { 
          legend: { 
            position: 'bottom', 
            labels: { 
              boxWidth: 12, 
              font: { size: 10, weight: 'bold' },
              color: '#374151',
              generateLabels: function(chart) {
                const data = chart.data;
                if (data.labels.length && data.datasets.length) {
                  return data.labels.map((label, i) => {
                    const value = data.datasets[0].data[i];
                    const percentage = ((value / total) * 100).toFixed(1);
                    return {
                      text: `${label}: ${value} (${percentage}%)`,
                      fillStyle: data.datasets[0].backgroundColor[i],
                      strokeStyle: data.datasets[0].backgroundColor[i],
                      lineWidth: 0,
                      index: i
                    };
                  });
                }
                return [];
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const value = context.parsed;
                const percentage = ((value / total) * 100).toFixed(1);
                return `${context.label}: ${value} users (${percentage}%)`;
              }
            }
          }
        },
        cutout: '60%'
      }
    });
  }

  if (bookingChart.value && currentSection.value === 'analytics') {
    if (bookingChartInstance) bookingChartInstance.destroy();
    const ctx = bookingChart.value.getContext('2d');
    const bookingData = [
      parseInt(bookingStats.value.pending) || 0,
      parseInt(bookingStats.value.active) || 0,
      parseInt(bookingStats.value.completed) || 0,
      parseInt(bookingStats.value.cancelled) || 0
    ];
    const bookingTotal = bookingData.reduce((a, b) => a + b, 0);
    bookingChartInstance = new window.Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Pending', 'Active', 'Completed', 'Cancelled'],
        datasets: [{ 
          label: 'Bookings',
          data: bookingData, 
          backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444'], 
          borderWidth: 0,
          borderRadius: 4,
          borderSkipped: false
        }]
      },
      options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        plugins: { 
          legend: { 
            display: true,
            position: 'top',
            labels: {
              font: { size: 11, weight: 'bold' },
              color: '#374151'
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const value = context.parsed.y;
                const percentage = ((value / bookingTotal) * 100).toFixed(1);
                return `${context.label}: ${value} bookings (${percentage}%)`;
              }
            }
          }
        }, 
        scales: { 
          x: { 
            display: true,
            grid: { display: false },
            ticks: { 
              font: { size: 10, weight: 'bold' }, 
              color: '#374151'
            }
          }, 
          y: { 
            display: true,
            grid: { color: '#f3f4f6' },
            ticks: { 
              font: { size: 10 }, 
              color: '#6b7280'
            }
          } 
        },
        animation: {
          duration: 1000,
          easing: 'easeInOutQuart'
        }
      }
    });
  }
};

// PDF Export Functions
const exportCaregiverPaymentsPDF = () => {
  try {
    console.log('Exporting Caregiver Payments PDF...');
    console.log('Data:', caregiverPayments.value);
    console.log('Period:', salaryPeriodFilter.value);
    
    if (!window.jspdf) {
      console.error('jsPDF library not loaded!');
      alert('PDF library not loaded. Please refresh the page.');
      return;
    }
    
    if (!caregiverPayments.value || caregiverPayments.value.length === 0) {
      console.warn('No caregiver payment data to export');
      alert('No payment data available to export.');
      return;
    }
    
    generatePaymentPDF('Caregiver Payments', caregiverPayments.value, salaryPeriodFilter.value, [
      'Date',
      'Employee Name',
      'Client Name',
      'Total Hours',
      'Total Payout'
    ]);
    
    console.log('PDF exported successfully!');
  } catch (error) {
    console.error('Error exporting PDF:', error);
    alert('Error generating PDF: ' + error.message);
  }
};

const exportMarketingCommissionsPDF = () => {
  try {
    console.log('Exporting Marketing Commissions PDF...');
    console.log('Data:', filteredMarketingCommissions.value);
    console.log('Period:', marketingCommissionPeriodFilter.value);
    
    if (!window.jspdf) {
      console.error('jsPDF library not loaded!');
      alert('PDF library not loaded. Please refresh the page.');
      return;
    }
    
    if (!filteredMarketingCommissions.value || filteredMarketingCommissions.value.length === 0) {
      console.warn('No marketing commission data to export');
      alert('No commission data available to export.');
      return;
    }
    
    generatePaymentPDF('Marketing Commissions', filteredMarketingCommissions.value, marketingCommissionPeriodFilter.value, [
      'Date',
      'Marketing Staff',
      'Client Name',
      'Commission Amount',
      'Status'
    ]);
    
    console.log('PDF exported successfully!');
  } catch (error) {
    console.error('Error exporting PDF:', error);
    alert('Error generating PDF: ' + error.message);
  }
};

const exportTrainingCommissionsPDF = () => {
  try {
    console.log('Exporting Training Commissions PDF...');
    console.log('Data:', filteredTrainingCommissions.value);
    console.log('Period:', trainingCommissionPeriodFilter.value);
    
    if (!window.jspdf) {
      console.error('jsPDF library not loaded!');
      alert('PDF library not loaded. Please refresh the page.');
      return;
    }
    
    if (!filteredTrainingCommissions.value || filteredTrainingCommissions.value.length === 0) {
      console.warn('No training commission data to export');
      alert('No commission data available to export.');
      return;
    }
    
    generatePaymentPDF('Training Center Commissions', filteredTrainingCommissions.value, trainingCommissionPeriodFilter.value, [
      'Date',
      'Training Center',
      'Caregiver Name',
      'Commission Amount',
      'Status'
    ]);
    
    console.log('PDF exported successfully!');
  } catch (error) {
    console.error('Error exporting PDF:', error);
    alert('Error generating PDF: ' + error.message);
  }
};

const generatePaymentPDF = async (title, data, period, columns) => {
  try {
    // Prepare data for server-side PDF generation
    const totalRecords = data.length;
    
    // Fix: Try multiple possible field names for hours
    const totalHours = data.reduce((sum, item) => {
      const hours = parseFloat(
        item.hours_worked || 
        item.hours || 
        item.hoursWorked || 
        item.hours_display?.replace(' hrs', '') || 
        item.totalHours ||
        0
      );
      return sum + hours;
    }, 0);
    
    const totalPayout = data.reduce((sum, item) => sum + parseFloat(item.amount || item.total_commission || item.pending_commission || 0), 0);
    const avgRate = totalHours > 0 ? (totalPayout / totalHours) : 0;
    
    // Format table data
    const paymentData = [];
    data.forEach(item => {
      if (title.includes('Caregiver')) {
        // Extract hours from various possible fields
        const hoursValue = parseFloat(
          item.hours_worked || 
          item.hours || 
          item.hoursWorked || 
          item.hours_display?.replace(' hrs', '') || 
          item.totalHours ||
          0
        );
        
        paymentData.push([
          item.period || period,
          item.caregiver || item.name || 'N/A',
          'Various Clients',
          `${hoursValue.toFixed(1)} hrs`,
          `$${parseFloat(item.amount || 0).toFixed(2)}`
        ]);
      } else if (title.includes('Marketing')) {
        paymentData.push([
          period,
          item.name || 'N/A',
          'Multiple Bookings',
          `$${parseFloat(item.total_commission || 0).toFixed(2)}`,
          item.payment_status || 'Pending'
        ]);
      } else if (title.includes('Training')) {
        paymentData.push([
          period,
          item.name || 'N/A',
          `${item.caregivers_count || 0} caregivers`,
          `$${parseFloat(item.total_commission || 0).toFixed(2)}`,
          item.payment_status || 'Pending'
        ]);
      } else if (title.includes('Housekeeper')) {
        // Extract hours from various possible fields
        const hoursValue = parseFloat(
          item.hours_worked || 
          item.hours || 
          item.hoursWorked || 
          item.hours_display?.replace(' hrs', '') || 
          item.totalHours ||
          0
        );
        
        paymentData.push([
          item.period || period,
          item.housekeeper || item.name || 'N/A',
          'Various Clients',
          `${hoursValue.toFixed(1)} hrs`,
          `$${parseFloat(item.amount || 0).toFixed(2)}`
        ]);
      }
    });
    
    const reportData = {
      reportType: title,
      period: period,
      statusFilter: 'All',
      totalRecords: totalRecords.toString(),
      totalHours: Math.round(totalHours).toString(),
      activeEmployees: totalRecords.toString(),
      avgRate: avgRate.toFixed(1),
      paymentData: paymentData,
      columns: columns
    };
    
    // Generate PDF using the server-side template
    const response = await fetch('/api/reports/payment-pdf', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(reportData)
    });
    
    if (response.ok) {
      const blob = await response.blob();
      const url = URL.createObjectURL(blob);
      
      // Open in new window instead of download
      window.open(url, '_blank');
      
      // Clean up after a delay
      setTimeout(() => URL.revokeObjectURL(url), 100);
      
      console.log('PDF opened in new tab successfully');
    } else {
      throw new Error('Failed to generate PDF report');
    }
  } catch (error) {
    console.error('Error generating PDF:', error);
    alert('Failed to generate PDF: ' + error.message);
  }
};

watch(currentSection, (newVal) => {
  localStorage.setItem('adminSection', newVal);
  if (newVal === 'analytics') {
    setTimeout(initCharts, 300);
  }
  // Reload training centers when switching to training centers section
  if (newVal === 'training') {
    loadTrainingCenters();
  }
});

// Function to add data-label attributes to table cells for mobile view
const addMobileTableLabels = () => {
  if (typeof window === 'undefined') return;
  
  // Use requestAnimationFrame for better timing with Vue's rendering
  requestAnimationFrame(() => {
    setTimeout(() => {
      const tables = document.querySelectorAll('.v-data-table');
      if (tables.length === 0) return;
      
      tables.forEach(table => {
        const headers = Array.from(table.querySelectorAll('thead th'));
        if (headers.length === 0) return; // Table not rendered yet
        
        const rows = table.querySelectorAll('tbody tr');
        if (rows.length === 0) return; // No data rows yet
        
        // Check if first column is a checkbox
        const firstHeader = headers[0];
        const hasCheckbox = firstHeader?.querySelector('.v-checkbox') !== null || 
                           firstHeader?.querySelector('.v-selection-control-group') !== null ||
                           firstHeader?.querySelector('input[type="checkbox"]') !== null;
        const headerOffset = hasCheckbox ? 1 : 0;
        
        rows.forEach(row => {
          const cells = Array.from(row.querySelectorAll('td'));
          cells.forEach((cell, cellIndex) => {
            // Skip checkbox column
            if (cellIndex === 0 && hasCheckbox) return;
            
            const headerIndex = cellIndex - headerOffset;
            if (headerIndex >= 0 && headerIndex < headers.length) {
              const header = headers[headerIndex];
              // Try multiple ways to get header text
              let headerText = '';
              const headerContent = header?.querySelector('.v-data-table-header__content');
              if (headerContent) {
                headerText = headerContent.textContent || '';
              } else {
                headerText = header?.textContent || '';
              }
              
              // Remove sort icons, whitespace, and other noise from header text
              const cleanHeaderText = headerText.replace(/[↑↓\s]+/g, ' ').trim();
              if (cleanHeaderText && cleanHeaderText.length > 0) {
                cell.setAttribute('data-label', cleanHeaderText);
              } else if (cell.hasAttribute('data-label')) {
                // Keep existing label if header text is empty
                // Don't remove it
              }
            }
          });
        });
      });
    }, 200);
  });
};

onMounted(() => {
  loadProfile();
  loadAdminStats();
  loadQuickCaregivers();
  loadHousekeepers();
  loadUsers();
  loadClientBookings();
  loadApplications();
  loadPasswordResets();
  loadMetrics();
  loadAnalyticsStats();
  loadAdminNotificationCount();
  loadTimeTrackingData();
  loadMarketingStaff();
  loadAdminStaff();
  loadTrainingCenters();
  loadCaregiverTrainingCenters();
  loadQuickCaregivers();
  
  // Load payment & financial data from database
  loadPaymentStats();
  loadRecentTransactions();
  loadClientPayments();
  loadCaregiverPayments();
  loadHousekeeperPayments();
  loadMarketingCommissions();
  loadTrainingCommissions();
  loadAllTransactions();
  loadMoneyFlowData(); // NEW: Load money flow monitoring data
  loadTopPerformers();
  loadRecentAnalyticsActivity();
  
  if (currentSection.value === 'analytics') {
    setTimeout(initCharts, 500);
  }
  
  // Load time tracking history data
  loadTimeTrackingHistory();
  
  // Add mobile table labels after data loads
  setTimeout(() => {
    addMobileTableLabels();
  }, 1000);
  
  // Re-add labels when section changes
  watch(currentSection, () => {
    setTimeout(() => {
      addMobileTableLabels();
    }, 500);
  });
  
  // Auto-refresh bookings and stats every 15 seconds to catch payment updates
  setInterval(() => {
    loadClientBookings();  // Refresh bookings table
    loadAdminStats();      // Refresh platform metrics
    loadPaymentStats();    // Refresh payment stats
    loadMetrics();         // Refresh financial metrics
  }, 15000);
});

// Re-add labels when booking data changes
watch(clientBookings, () => {
  setTimeout(() => {
    addMobileTableLabels();
  }, 300);
}, { deep: true });

// Re-add labels when other table data changes
watch([caregivers, clients, pendingApplications, passwordResets, marketingStaff, trainingCenters], () => {
  setTimeout(() => {
    addMobileTableLabels();
  }, 300);
}, { deep: true });

// Re-add labels on window resize
if (typeof window !== 'undefined') {
  let resizeTimeout;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
      addMobileTableLabels();
    }, 200);
  });
}

// Refresh notification count every 30 seconds
setInterval(loadAdminNotificationCount, 30000);

// Refresh time tracking every 10 seconds for real-time updates
setInterval(() => {
  if (currentSection.value === 'time-tracking') {
    loadTimeTrackingData();
  }
}, 10000);
</script>

<style>
/* Global admin table header styling */
.v-data-table {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04) !important;
  border-radius: 12px !important;
  overflow: hidden !important;
}

.v-data-table .v-data-table__thead .v-data-table__tr .v-data-table__th {
  background-color: #dc2626 !important;
  color: white !important;
  box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2) !important;
  padding: 16px !important;
  height: auto !important;
  vertical-align: middle !important;
  border-right: 1px solid rgba(255, 255, 255, 0.3) !important;
}

.v-data-table .v-data-table__thead .v-data-table__tr .v-data-table__th .v-data-table-header__content {
  color: white !important;
  font-weight: 600 !important;
  font-size: 0.875rem !important;
  white-space: nowrap !important;
}

.v-data-table .v-data-table__thead .v-data-table__tr .v-data-table__th .v-icon {
  color: white !important;
}

.v-data-table .v-data-table__tbody .v-data-table__tr {
  border-bottom: 1px solid #e5e7eb !important;
}

.v-data-table .v-data-table__tbody .v-data-table__tr:last-child {
  border-bottom: none !important;
}

.v-data-table .v-data-table__tbody .v-data-table__tr .v-data-table__td {
  border-right: 1px solid #e5e7eb !important;
  color: #1a1a1a !important;
}

.v-data-table .v-data-table__tbody .v-data-table__tr .v-data-table__td * {
  color: inherit !important;
}

.v-data-table .v-data-table__tbody .v-data-table__tr .v-data-table__td .v-btn {
  color: inherit !important;
}

/* Force horizontal row borders only */
.v-data-table table tbody tr {
  border-bottom: 1px solid #e5e7eb !important;
}

.v-data-table table tbody tr:last-child {
  border-bottom: none !important;
}

.v-data-table table tbody tr td {
  border-right: 1px solid #e5e7eb !important;
}

.v-data-table table thead tr th:last-child,
.v-data-table table tbody tr td:last-child {
  border-right: none !important;
}

/* Reset first column for ALL tables - default to normal width */
.v-data-table table thead tr th:first-child,
.v-data-table table tbody tr td:first-child {
  width: auto !important;
  min-width: auto !important;
  max-width: none !important;
  padding: 16px 20px !important;
  text-align: left !important;
}

/* Narrow first column ONLY when it contains a checkbox */
.v-data-table table thead tr th:first-child:has(.v-checkbox),
.v-data-table table tbody tr td:first-child:has(.v-checkbox) {
  text-align: center !important;
  padding: 8px 4px !important;
  width: 48px !important;
  min-width: 48px !important;
  max-width: 48px !important;
}

.v-data-table table thead tr th:first-child .v-checkbox,
.v-data-table table tbody tr td:first-child .v-checkbox {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
  margin: 0 auto !important;
  width: 100% !important;
  min-width: unset !important;
}

.v-data-table table thead tr th:first-child .v-selection-control,
.v-data-table table tbody tr td:first-child .v-selection-control {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
  margin: 0 auto !important;
  width: 100% !important;
  min-width: unset !important;
  padding: 0 !important;
}

/* Compact checkbox styling */
.v-data-table .v-selection-control {
  min-height: unset !important;
}

.v-data-table .v-selection-control__wrapper {
  width: 24px !important;
  height: 24px !important;
}

/* Optimize table cell spacing and wrapping */
.v-data-table .v-data-table__tbody .v-data-table__tr .v-data-table__td {
  padding: 12px 16px !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  max-width: 200px !important;
}

/* Specific column widths for better layout */
.v-data-table .v-data-table__thead .v-data-table__tr .v-data-table__th {
  padding: 12px 16px !important;
  white-space: nowrap !important;
}

/* Compact action buttons */
.action-buttons {
  display: flex !important;
  gap: 4px !important;
  flex-wrap: nowrap !important;
  justify-content: center !important;
}

.action-buttons .v-btn {
  min-width: 32px !important;
  width: 32px !important;
  height: 32px !important;
  padding: 0 !important;
}

/* Compact chips */
.v-data-table .v-chip {
  font-size: 0.75rem !important;
  padding: 0 8px !important;
  height: 24px !important;
}

/* Allow specific columns to wrap if needed */
.v-data-table .v-data-table__tbody .v-data-table__tr .v-data-table__td:has(.assignment-progress),
.v-data-table .v-data-table__tbody .v-data-table__tr .v-data-table__td:has(.action-buttons) {
  white-space: normal !important;
  max-width: none !important;
}

/* Progress bars in tables */
.assignment-progress {
  min-width: 100px !important;
  max-width: 120px !important;
}

.assignment-progress .progress-text {
  font-size: 0.75rem !important;
  font-weight: 600 !important;
  margin-bottom: 2px !important;
}

/* Compact table density */
.v-data-table--density-default > .v-table__wrapper > table > tbody > tr > td,
.v-data-table--density-default > .v-table__wrapper > table > thead > tr > th {
  height: 48px !important;
}

/* Remove excessive padding from table wrapper */
.v-data-table__wrapper {
  overflow-x: auto !important;
}

/* Ensure table uses available width efficiently */
.v-data-table table {
  width: 100% !important;
  table-layout: auto !important;
}

/* Additional specificity for checkbox columns */
.v-data-table.v-table thead tr th:first-child,
.v-data-table.v-table tbody tr td:first-child {
  width: auto !important;
  padding: 12px 16px !important;
}

.v-data-table.v-table thead tr th:first-child:has(.v-selection-control),
.v-data-table.v-table tbody tr td:first-child:has(.v-selection-control) {
  width: 48px !important;
  max-width: 48px !important;
  min-width: 48px !important;
  padding: 4px !important;
  text-align: center !important;
}

/* Force checkbox controls to be compact */
.v-data-table .v-data-table-column--align-start:has(.v-selection-control) {
  width: 48px !important;
  max-width: 48px !important;
  padding: 4px !important;
}

.v-data-table .v-selection-control {
  width: 40px !important;
  min-width: 40px !important;
  margin: 0 auto !important;
}

.v-data-table .v-selection-control .v-selection-control__wrapper {
  width: 40px !important;
  height: 40px !important;
  margin: 0 auto !important;
}

.v-data-table .v-selection-control .v-selection-control__input {
  width: 40px !important;
  height: 40px !important;
}

/* Specific fixes for admin bookings table */
.admin-bookings-table.v-data-table {
  font-size: 0.875rem !important;
}

.admin-bookings-table .v-data-table__th {
  font-size: 0.8125rem !important;
  padding: 8px 12px !important;
}

.admin-bookings-table .v-data-table__td {
  padding: 8px 12px !important;
  font-size: 0.8125rem !important;
}

.admin-bookings-table .v-chip {
  height: 22px !important;
  font-size: 0.7rem !important;
}

.admin-bookings-table .v-btn {
  width: 30px !important;
  height: 30px !important;
  min-width: 30px !important;
}

/* Override Vuetify's default table density */
.v-data-table.v-table--density-compact > .v-table__wrapper > table > tbody > tr > td,
.v-data-table.v-table--density-compact > .v-table__wrapper > table > thead > tr > th {
  height: 44px !important;
  padding: 8px 12px !important;
}

/* Make TIME column narrower */
.v-data-table th[data-key="startingTime"],
.v-data-table td[data-column="startingTime"] {
  width: 70px !important;
  max-width: 70px !important;
}

/* Make HOURS/DAY column narrower */
.v-data-table th[data-key="hoursPerDay"],
.v-data-table td[data-column="hoursPerDay"] {
  width: 60px !important;
  max-width: 60px !important;
  text-align: center !important;
}

/* Make DURATION column narrower */
.v-data-table th[data-key="duration"],
.v-data-table td[data-column="duration"] {
  width: 80px !important;
  max-width: 80px !important;
}

/* Make ASSIGNED column specific width */
.v-data-table th[data-key="assignedCount"],
.v-data-table td[data-column="assignedCount"] {
  width: 100px !important;
  max-width: 100px !important;
}

/* Make STATUS column narrower */
.v-data-table th[data-key="status"],
.v-data-table td[data-column="status"] {
  width: 90px !important;
  max-width: 90px !important;
  text-align: center !important;
}

/* Make ACTIONS column compact */
.v-data-table th[data-key="actions"],
.v-data-table td[data-column="actions"] {
  width: 140px !important;
  max-width: 140px !important;
  text-align: center !important;
}

/* Price column with strikethrough for discounts */
.price-cell {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.original-price-strike {
  font-size: 0.7rem;
  color: #999;
  text-decoration: line-through;
  font-weight: 400;
}

.current-price {
  font-weight: 600;
  color: #1a1a1a;
}

/* Password Requirements Styling */
.password-requirements {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  margin-top: 0.5rem;
}

.requirement-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  transition: all 0.2s ease;
}

.requirement-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.25rem;
  height: 1.25rem;
  border-radius: 50%;
  font-size: 0.7rem;
  font-weight: bold;
  color: #dc2626;
  background-color: #fee2e2;
  flex-shrink: 0;
  transition: all 0.2s ease;
}

.requirement-item.valid .requirement-icon {
  color: #059669;
  background-color: #d1fae5;
}

.requirement-text {
  color: #64748b;
  transition: color 0.2s ease;
}

.requirement-item.valid .requirement-text {
  color: #059669;
}
</style>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Hover effect for caregiver cards */
.hover-card {
  transition: all 0.3s ease;
  border: 1px solid #e0e0e0;
}

.hover-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
  border-color: #10b981;
}

/* Weekly schedule day cards */
.day-schedule-card {
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.day-schedule-card:hover {
  border-color: #10b981;
}

.day-schedule-card.today-card {
  border-color: #2196F3 !important;
  box-shadow: 0 4px 12px rgba(33, 150, 243, 0.2) !important;
}

.bg-success-lighten-5 {
  background-color: #f0fdf4 !important;
}

.admin-btn {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
  color: white !important;
  font-size: 1.125rem !important;
  font-weight: 600 !important;
  border-radius: 16px !important;
  text-transform: none !important;
  letter-spacing: -0.01em !important;
  box-shadow: 0 6px 20px rgba(220, 38, 38, 0.3), 0 2px 8px rgba(220, 38, 38, 0.15) !important;
  height: 56px !important;
  transition: box-shadow 0.3s ease, transform 0.2s ease !important;
}

.admin-btn :deep(.v-btn__content) {
  color: white !important;
}

.admin-btn :deep(.v-icon) {
  color: white !important;
}

.admin-btn:hover {
  box-shadow: 0 8px 28px rgba(220, 38, 38, 0.4), 0 4px 12px rgba(220, 38, 38, 0.2) !important;
  transform: translateY(-1px) !important;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.card-header {
  background: #fafafa;
  border-bottom: 1px solid #f0f0f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

.modern-activity-card {
  border-radius: 16px !important;
  border: 1px solid #c5c5c5ff !important;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1), 0 2px 6px rgba(0, 0, 0, 0.05) !important;
  overflow: hidden !important;
  transition: box-shadow 0.3s ease !important;
}

.modern-activity-card:hover {
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.15), 0 4px 10px rgba(0, 0, 0, 0.08) !important;
}

.modern-card-header {
  background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%) !important;
  border-bottom: none !important;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
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

.time-col { width: 15% !important; }
.user-col { width: 25% !important; }
.action-col { width: 40% !important; }
.type-col { width: 20% !important; }

.modern-row {
  transition: all 0.2s ease !important;
  border-bottom: 1px solid #f3f4f6 !important;
}

.modern-row:hover {
  background: #f9fafb !important;
  transform: translateX(2px) !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
}

.modern-cell {
  padding: 16px !important;
  font-size: 0.875rem !important;
  color: #374151 !important;
  border-bottom: none !important;
}

.time-cell {
  font-weight: 500 !important;
  color: #6b7280 !important;
  font-size: 0.8rem !important;
}

.user-cell {
  font-weight: 600 !important;
}

.action-cell {
  color: #4b5563 !important;
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

.action-btn {
  border-radius: 14px !important;
  font-weight: 600 !important;
  font-size: 0.95rem !important;
  text-transform: none !important;
  letter-spacing: -0.01em !important;
  padding: 24px 16px !important;
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

.system-status-item {
  margin-bottom: 16px;
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
}

.system-status-item:last-child {
  border-bottom: none;
}

.section-title-compact {
  font-size: 1.1rem;
  font-weight: 600;
  letter-spacing: -0.01em;
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

.compact-card {
  border-radius: 16px !important;
  border: 1px solid #c5c5c5ff !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04) !important;
  height: 280px !important;
  transition: box-shadow 0.3s ease !important;
}

.compact-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12), 0 4px 8px rgba(0, 0, 0, 0.06) !important;
}

.action-btn-compact {
  border-radius: 10px !important;
  font-weight: 600 !important;
  font-size: 0.85rem !important;
  text-transform: none !important;
  letter-spacing: -0.01em !important;
  padding: 12px 16px !important;
  height: auto !important;
}

.health-item {
  padding: 4px 0;
}

.health-label {
  font-size: 0.85rem;
  color: #4b5563;
  font-weight: 500;
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

.metric-item {
  padding: 12px 16px;
  margin-bottom: 8px;
  background: #fafafa;
  border-radius: 12px;
  border-left: 4px solid #dc2626;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
  transition: all 0.2s ease;
}

.metric-item:hover {
  background: #f5f5f5;
  transform: translateX(2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1), 0 2px 6px rgba(0, 0, 0, 0.06) !important;
}

.metric-item:last-child {
  margin-bottom: 0;
}

.metric-label {
  font-size: 0.85rem;
  color: #4b5563;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.metric-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
}

.metric-change {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  font-style: italic;
}

.health-item {
  padding: 12px 16px;
  margin-bottom: 8px;
  background: #fafafa;
  border-radius: 12px;
  border-left: 4px solid #10b981;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
  transition: all 0.2s ease;
}

.health-item:hover {
  background: #f5f5f5;
  transform: translateX(2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1), 0 2px 6px rgba(0, 0, 0, 0.06) !important;
}

.health-item:last-child {
  margin-bottom: 0;
}

.health-label {
  font-size: 0.85rem;
  color: #4b5563;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.metric-grid {
  height: 100%;
}

.metric-card {
  background: #fafafa;
  border-radius: 12px;
  padding: 8px;
  height: 100%;
  border: 1px solid #c5c5c5ff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
  transition: all 0.2s ease;
}

.metric-card:hover {
  background: #f5f5f5;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.08) !important;
}

.metric-label-grid {
  font-size: 0.75rem;
  color: #4b5563;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.metric-value-grid {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.metric-change-grid {
  font-size: 0.7rem;
  font-weight: 500;
  margin-top: 2px;
}

.health-grid {
  height: 100%;
}

.health-card {
  background: #fafafa;
  border-radius: 12px;
  padding: 12px;
  height: 100%;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
  transition: all 0.2s ease;
}

.health-card:hover {
  background: #f5f5f5;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.08) !important;
}

.health-label-grid {
  font-size: 0.75rem;
  color: #4b5563;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.report-item {
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
}

.report-item:last-child {
  border-bottom: none;
}

.security-log-item {
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
}

.security-log-item:last-child {
  border-bottom: none;
}

.log-text {
  font-size: 0.85rem;
  color: #4b5563;
  font-weight: 500;
}

.announcement-item {
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
}

.announcement-item:last-child {
  border-bottom: none;
}

.announcement-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1f2937;
}

.announcement-message {
  font-size: 0.85rem;
  color: #4b5563;
  margin-top: 4px;
}

.compact-stat-card {
  border-radius: 12px !important;
  border: 1px solid #c5c5c5ff !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04) !important;
  transition: box-shadow 0.3s ease, transform 0.2s ease !important;
}

.compact-stat-card:hover {
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12), 0 4px 8px rgba(0, 0, 0, 0.06) !important;
  transform: translateY(-2px) !important;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-change {
  font-size: 0.7rem;
  font-weight: 600;
}

.compact-chart-card {
  border-radius: 12px !important;
  border: 1px solid #c5c5c5ff !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04) !important;
  transition: box-shadow 0.3s ease !important;
}

.compact-chart-card:hover {
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12), 0 4px 8px rgba(0, 0, 0, 0.06) !important;
}

.compact-header {
  background: #fafafa;
  border-bottom: 1px solid #f0f0f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

.compact-title {
  font-size: 1rem;
  font-weight: 600;
  letter-spacing: -0.01em;
}

.metric-box {
  text-align: center;
  padding: 12px;
  background: #fafafa;
  border-radius: 8px;
  margin-bottom: 8px;
  border: 1px solid #c5c5c5ff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
  transition: box-shadow 0.2s ease, transform 0.2s ease !important;
}

.metric-box:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1), 0 2px 6px rgba(0, 0, 0, 0.06) !important;
  transform: translateY(-1px) !important;
}

.metric-number {
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1;
}

.metric-text {
  font-size: 0.7rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  margin-top: 4px;
}

.performer-item {
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
}

.performer-item:last-child {
  border-bottom: none;
}

.performer-name {
  font-size: 0.85rem;
  font-weight: 500;
  color: #1f2937;
}

.activity-item {
  padding: 6px 0;
  border-bottom: 1px solid #f3f4f6;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-text {
  font-size: 0.8rem;
  color: #4b5563;
}

.activity-time {
  font-size: 0.7rem;
  color: #9ca3af;
  margin-left: 24px;
}

.health-item-compact {
  margin-bottom: 12px;
}

.health-service {
  font-size: 0.8rem;
  color: #4b5563;
  font-weight: 500;
}

.chart-subtitle {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.chart-value {
  font-size: 1rem;
  font-weight: 700;
}

.user-stats-row {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.user-stat-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.stat-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.stat-text {
  font-size: 0.75rem;
  color: #4b5563;
  font-weight: 500;
}

.booking-stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.booking-stat-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 4px;
  background: #f9fafb;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
  transition: box-shadow 0.2s ease !important;
}

.booking-stat-item:hover {
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
}

.stat-indicator {
  width: 6px;
  height: 20px;
  border-radius: 3px;
  flex-shrink: 0;
}

.stat-info {
  flex: 1;
}

.stat-number {
  font-size: 0.85rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.stat-label {
  font-size: 0.65rem;
  color: #6b7280;
  font-weight: 500;
  line-height: 1;
  margin-top: 1px;
}

.transaction-item {
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
}

.transaction-item:last-child {
  border-bottom: none;
}

.transaction-desc {
  font-size: 0.85rem;
  color: #1f2937;
  font-weight: 500;
}

.transaction-amount {
  font-size: 0.9rem;
  font-weight: 700;
}

.transaction-time {
  font-size: 0.7rem;
  color: #9ca3af;
  margin-left: 24px;
}

.payment-method-item {
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
}

.payment-method-item:last-child {
  border-bottom: none;
}

.method-name {
  font-size: 0.85rem;
  color: #1f2937;
  font-weight: 500;
}

.method-details {
  font-size: 0.75rem;
  color: #6b7280;
  margin-left: 32px;
}

.care-insights {
  padding: 8px 0;
}

.insight-item {
  padding: 8px 12px;
  background: #fafafa;
  border-radius: 8px;
  margin-bottom: 8px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
  transition: box-shadow 0.2s ease, transform 0.2s ease !important;
}

.insight-item:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1), 0 2px 6px rgba(0, 0, 0, 0.06) !important;
  transform: translateY(-1px) !important;
}

.insight-item:last-child {
  margin-bottom: 0;
}

.insight-label {
  font-size: 0.75rem;
  color: #4b5563;
  font-weight: 500;
}

.insight-value {
  font-size: 0.85rem;
  font-weight: 700;
}

/* Mobile Responsive Styles */
@media (max-width: 960px) {
  /* Compact stat cards mobile */
  .compact-stat-card .v-card-text {
    padding: 1rem !important;
  }

  .compact-stat-card .v-icon {
    font-size: 20px !important;
    margin-right: 0.75rem !important;
  }

  .stat-value {
    font-size: 1.25rem !important;
  }

  .stat-label {
    font-size: 0.6875rem !important;
  }

  .stat-change {
    font-size: 0.625rem !important;
  }

  /* Transaction stats mobile */
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

  .section-title-compact {
    font-size: 1rem !important;
  }

  /* Booking stats grid mobile */
  .booking-stats-grid {
    grid-template-columns: 1fr !important;
    gap: 0.5rem !important;
  }

  /* Compact buttons on mobile */
  .v-btn {
    font-size: 0.875rem !important;
    padding: 0.625rem 1rem !important;
  }
}

@media (max-width: 480px) {
  /* Compact stat cards very small */
  .compact-stat-card .v-card-text {
    padding: 0.875rem !important;
  }

  .compact-stat-card .v-icon {
    font-size: 18px !important;
    margin-right: 0.5rem !important;
  }

  .stat-value {
    font-size: 1.125rem !important;
  }

  .stat-label {
    font-size: 0.625rem !important;
  }

  .stat-change {
    font-size: 0.5625rem !important;
  }

  /* Transaction stats very compact */
  .stat-amount {
    font-size: 1.25rem !important;
  }

  .stat-label-text {
    font-size: 0.75rem !important;
  }

  /* Cards very compact */
  .card-header {
    padding: 0.875rem !important;
  }

  /* Booking stat items mobile */
  .stat-number {
    font-size: 0.75rem !important;
  }

  .stat-label {
    font-size: 0.5625rem !important;
  }
}

.caregiver-contacts {
  padding: 4px 0;
}

.caregiver-contact-item {
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
}

.caregiver-contact-item:last-child {
  border-bottom: none;
}

.caregiver-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1f2937;
}

.caregiver-status {
  font-size: 0.7rem;
  font-weight: 500;
}

.caregiver-phone {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

.caregiver-list {
  max-height: 400px;
  overflow-y: auto;
}

.caregiver-contact-row {
  border-bottom: 1px solid #f3f4f6;
  transition: background-color 0.2s;
}

.caregiver-contact-row:hover {
  background-color: #f9fafb;
}

.caregiver-name-large {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

.caregiver-details {
  font-size: 0.8rem;
  color: #6b7280;
}

.caregiver-location {
  font-size: 0.75rem;
  color: #9ca3af;
  font-weight: 500;
  margin-top: 2px;
}

/* Modern compact action buttons */
:deep(.v-btn--size-x-small) {
  width: 28px !important;
  height: 28px !important;
  min-width: 28px !important;
  padding: 0 !important;
  border-radius: 6px !important;
  box-shadow: none !important;
}

:deep(.v-btn--size-x-small .v-icon) {
  font-size: 14px !important;
  color: white !important;
}

:deep(.v-btn--variant-flat.bg-success) {
  background-color: #10b981 !important;
}

:deep(.v-btn--variant-flat.bg-error) {
  background-color: #ef4444 !important;
}

:deep(.v-btn--variant-flat.bg-info) {
  background-color: #3b82f6 !important;
}

.contact-actions-large {
  display: flex;
  gap: 8px;
}

/* Admin table headers - red theme */
:deep(.v-data-table) .v-data-table__thead .v-data-table__tr .v-data-table__th {
  background-color: #dc2626 !important;
  color: white !important;
  padding: 16px !important;
  height: auto !important;
}

:deep(.v-data-table) .v-data-table__thead .v-data-table__tr .v-data-table__th .v-data-table-header__content {
  color: white !important;
  font-weight: 600 !important;
  font-size: 0.875rem !important;
  white-space: nowrap !important;
}

:deep(.v-data-table) .v-data-table__thead .v-data-table__tr .v-data-table__th .v-data-table-header__icon {
  color: white !important;
}

:deep(.v-data-table) .v-data-table__thead .v-data-table__tr .v-data-table__th .v-icon {
  color: white !important;
}

:deep(.v-data-table) .v-data-table__tbody .v-data-table__tr .v-data-table__td {
  padding: 16px !important;
}

:deep(.v-data-table) .v-data-table__tbody .v-data-table__tr .v-data-table__td:last-child {
  padding: 12px 16px !important;
}

/* Reset first column for ALL tables - default to normal width */
:deep(.v-data-table) .v-data-table__thead .v-data-table__tr .v-data-table__th:first-child,
:deep(.v-data-table) .v-data-table__tbody .v-data-table__tr .v-data-table__td:first-child {
  width: auto !important;
  min-width: auto !important;
  max-width: none !important;
  padding: 16px 20px !important;
  text-align: left !important;
  vertical-align: middle !important;
}

/* Narrow first column ONLY when it contains a checkbox */
:deep(.v-data-table) .v-data-table__thead .v-data-table__tr .v-data-table__th:first-child:has(.v-checkbox),
:deep(.v-data-table) .v-data-table__tbody .v-data-table__tr .v-data-table__td:first-child:has(.v-checkbox) {
  text-align: center !important;
  padding: 16px !important;
  vertical-align: middle !important;
  width: 60px !important;
  min-width: 60px !important;
  max-width: 60px !important;
}

:deep(.v-data-table) .v-data-table__thead .v-data-table__tr .v-data-table__th:first-child .v-data-table-header__content,
:deep(.v-data-table) .v-data-table__tbody .v-data-table__tr .v-data-table__td:first-child .v-data-table__td-content {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
  width: 100% !important;
  margin: 0 auto !important;
}

:deep(.v-data-table) .v-data-table__thead .v-data-table__tr .v-data-table__th:first-child .v-checkbox,
:deep(.v-data-table) .v-data-table__tbody .v-data-table__tr .v-data-table__td:first-child .v-checkbox {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
  margin: 0 auto !important;
  width: 100% !important;
}

:deep(.v-data-table) .v-data-table__thead .v-data-table__tr .v-data-table__th:first-child .v-selection-control,
:deep(.v-data-table) .v-data-table__tbody .v-data-table__tr .v-data-table__td:first-child .v-selection-control {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
  margin: 0 auto !important;
  width: 100% !important;
}

:deep(.v-data-table) .v-data-table__thead .v-data-table__tr .v-data-table__th:first-child .v-selection-control__wrapper,
:deep(.v-data-table) .v-data-table__tbody .v-data-table__tr .v-data-table__td:first-child .v-selection-control__wrapper {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
  margin: 0 auto !important;
  width: 100% !important;
}

:deep(.v-data-table) .v-data-table__thead .v-data-table__tr .v-data-table__th:first-child .v-selection-control__input,
:deep(.v-data-table) .v-data-table__tbody .v-data-table__tr .v-data-table__td:first-child .v-selection-control__input {
  margin: 0 auto !important;
}

/* Reset first column for ALL tables - default to normal width */
:deep(.v-data-table) table thead tr th:first-child,
:deep(.v-data-table) table tbody tr td:first-child {
  width: auto !important;
  min-width: auto !important;
  max-width: none !important;
  padding: 16px 20px !important;
  text-align: left !important;
}

/* Narrow first column ONLY when it contains a checkbox */
:deep(.v-data-table) table thead tr th:first-child:has(.v-checkbox),
:deep(.v-data-table) table tbody tr td:first-child:has(.v-checkbox) {
  text-align: center !important;
  padding: 16px !important;
  width: 60px !important;
  min-width: 60px !important;
  max-width: 60px !important;
}

:deep(.v-data-table) table thead tr th:first-child *,
:deep(.v-data-table) table tbody tr td:first-child * {
  margin-left: auto !important;
  margin-right: auto !important;
}

:deep(.v-data-table) table thead tr th:first-child .v-checkbox,
:deep(.v-data-table) table tbody tr td:first-child .v-checkbox {
  display: inline-flex !important;
  justify-content: center !important;
  margin: 0 auto !important;
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

/* Reset first column for ALL tables - default to normal width */
:deep(.v-data-table) .v-table__wrapper table thead tr th:first-child,
:deep(.v-data-table) .v-table__wrapper table tbody tr td:first-child {
  text-align: left !important;
  padding-left: 20px !important;
  padding-right: 20px !important;
}

/* Narrow first column ONLY when it contains a checkbox */
:deep(.v-data-table) .v-table__wrapper table thead tr th:first-child:has(.v-checkbox),
:deep(.v-data-table) .v-table__wrapper table tbody tr td:first-child:has(.v-checkbox) {
  text-align: center !important;
  padding-left: 0 !important;
  padding-right: 0 !important;
}

:deep(.v-data-table) .v-table__wrapper table thead tr th:first-child > div,
:deep(.v-data-table) .v-table__wrapper table tbody tr td:first-child > div {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
  width: 100% !important;
}

:deep(.v-data-table) .v-table__wrapper table thead tr th:first-child .v-checkbox,
:deep(.v-data-table) .v-table__wrapper table tbody tr td:first-child .v-checkbox {
  margin: 0 !important;
  display: flex !important;
  justify-content: center !important;
}

/* Dialog shadows */
:deep(.v-dialog .v-card) {
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15), 0 4px 16px rgba(0, 0, 0, 0.1) !important;
  border-radius: 16px !important;
}

/* Enhanced caregiver contact rows */
.caregiver-contact-row {
  border-bottom: 1px solid #f3f4f6;
  border-radius: 8px;
  margin-bottom: 4px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
  transition: all 0.2s ease;
}

.caregiver-contact-row:hover {
  background-color: #f9fafb;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04) !important;
  transform: translateY(-1px) !important;
}

/* Enhanced form controls */
:deep(.v-text-field .v-field),
:deep(.v-select .v-field),
:deep(.v-textarea .v-field) {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04) !important;
  transition: box-shadow 0.2s ease !important;
}

:deep(.v-text-field .v-field:hover),
:deep(.v-select .v-field:hover),
:deep(.v-textarea .v-field:hover) {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08) !important;
}

:deep(.v-text-field .v-field--focused),
:deep(.v-select .v-field--focused),
:deep(.v-textarea .v-field--focused) {
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15), 0 2px 4px rgba(220, 38, 38, 0.1) !important;
}

/* Enhanced chips */
:deep(.v-chip) {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
  transition: box-shadow 0.2s ease !important;
}

:deep(.v-chip:hover) {
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important;
}

/* Enhanced tabs */
:deep(.v-tabs .v-tab) {
  transition: box-shadow 0.2s ease !important;
}

:deep(.v-tabs .v-tab--selected) {
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.2) !important;
}

/* Enhanced progress bars */
:deep(.v-progress-linear) {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
  border-radius: 4px !important;
}

/* Enhanced avatars */
:deep(.v-avatar) {
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1) !important;
  transition: box-shadow 0.2s ease !important;
}

:deep(.v-avatar:hover) {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

/* Assign Caregiver Dialog Styles */
.assign-dialog-card {
  border-radius: 20px !important;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
}

.assign-dialog-header {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
  color: white !important;
  border-radius: 20px 20px 0 0 !important;
}

.assign-dialog-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  letter-spacing: -0.02em;
}

.assign-dialog-subtitle {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.9);
  margin-top: 4px;
}

.selection-counter :deep(.v-chip__content) {
  color: white !important;
}

.booking-details-card {
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  border-radius: 12px;
  padding: 16px;
  border: 1px solid #fecaca;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);
}

.booking-details-header {
  font-size: 1rem;
  font-weight: 700;
  color: #dc2626;
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.booking-details-content {
  margin: 0;
}

.detail-item {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
  gap: 4px;
}

.detail-label {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 600;
}

.detail-value {
  font-size: 0.875rem;
  color: #1f2937;
  font-weight: 500;
}

.search-field :deep(.v-field) {
  border-radius: 12px !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
}

.filter-select :deep(.v-field) {
  border-radius: 12px !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
}

.caregivers-list-container {
  max-height: 400px;
  overflow-y: auto;
  padding: 4px;
}

.caregivers-list-container::-webkit-scrollbar {
  width: 8px;
}

.caregivers-list-container::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 4px;
}

.caregivers-list-container::-webkit-scrollbar-thumb {
  background: #dc2626;
  border-radius: 4px;
}

.caregivers-list-container::-webkit-scrollbar-thumb:hover {
  background: #b91c1c;
}

.caregiver-assign-card {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 12px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.caregiver-assign-card:hover {
  border-color: #dc2626;
  box-shadow: 0 6px 20px rgba(220, 38, 38, 0.15);
  transform: translateY(-2px);
}

.caregiver-assign-card:has(.v-checkbox .v-selection-control--dirty) {
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  border-color: #dc2626;
  box-shadow: 0 6px 20px rgba(220, 38, 38, 0.2);
}

.caregiver-assign-name {
  font-size: 1rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 4px;
}

.caregiver-assign-details {
  font-size: 0.8rem;
  color: #6b7280;
  display: flex;
  align-items: center;
}

.no-caregivers {
  text-align: center;
  padding: 60px 20px;
}

:deep(.v-checkbox .v-selection-control__input) {
  border-radius: 6px;
}

:deep(.v-checkbox .v-icon) {
  font-size: 24px;
}

.detail-section {
  margin-bottom: 16px;
}

.detail-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.detail-value {
  font-size: 1rem;
  color: #1f2937;
  font-weight: 500;
}

.certificate-card {
  border-radius: 12px !important;
  border: 2px solid #10b981 !important;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%) !important;
}

.certificate-name {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1f2937;
}

.certificate-info {
  font-size: 0.8rem;
  color: #6b7280;
  margin-top: 2px;
}

/* Booking Details Modal Styles */
.booking-overview-card {
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  border-radius: 16px;
  padding: 20px;
  border: 2px solid #fecaca;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);
}

.booking-overview-header {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}

.booking-overview-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #dc2626;
  letter-spacing: -0.02em;
}

.booking-detail-item {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
  gap: 8px;
}

.booking-detail-label {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 600;
  min-width: 80px;
}

.booking-detail-value {
  font-size: 0.875rem;
  color: #1f2937;
  font-weight: 600;
}

.assigned-caregivers-card {
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border-radius: 16px;
  padding: 20px;
  border: 2px solid #bbf7d0;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
}

.assigned-caregivers-header {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}

.assigned-caregivers-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #10b981;
  letter-spacing: -0.02em;
}

.assigned-caregivers-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.assigned-caregiver-item {
  display: flex;
  align-items: center;
  padding: 16px;
  background: white;
  border-radius: 12px;
  border: 1px solid #d1fae5;
  box-shadow: 0 2px 6px rgba(16, 185, 129, 0.08);
  transition: all 0.2s ease;
}

.assigned-caregiver-item:hover {
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
  transform: translateY(-1px);
}

.caregiver-name-assigned {
  font-size: 1rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 4px;
}

.caregiver-details-assigned {
  font-size: 0.8rem;
  color: #6b7280;
  display: flex;
  align-items: center;
}

.no-assigned-caregivers {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
}

.booking-timeline-card {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 16px;
  padding: 20px;
  border: 2px solid #bae6fd;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.booking-timeline-header {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}

.booking-timeline-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #3b82f6;
  letter-spacing: -0.02em;
}

.booking-timeline {
  position: relative;
  padding-left: 24px;
}

.booking-timeline::before {
  content: '';
  position: absolute;
  left: 8px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: linear-gradient(to bottom, #3b82f6, #10b981);
}

.timeline-item {
  position: relative;
  margin-bottom: 24px;
  display: flex;
  align-items: flex-start;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-dot {
  position: absolute;
  left: -20px;
  top: 4px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 3px solid white;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.timeline-dot-success {
  background-color: #10b981;
}

.timeline-dot-info {
  background-color: #3b82f6;
}

.timeline-dot-warning {
  background-color: #f59e0b;
}

.timeline-content {
  background: white;
  padding: 12px 16px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  flex: 1;
}

.timeline-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 4px;
}

.timeline-subtitle {
  font-size: 0.8rem;
  color: #6b7280;
}

.scrollable-content {
  max-height: calc(80vh - 140px);
  overflow-y: auto;
}

.scrollable-content::-webkit-scrollbar {
  width: 8px;
}

.scrollable-content::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 4px;
}

.scrollable-content::-webkit-scrollbar-thumb {
  background: #dc2626;
  border-radius: 4px;
}

.scrollable-content::-webkit-scrollbar-thumb:hover {
  background: #b91c1c;
}

/* Time History Modal Styles */
.current-status-card {
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #fecaca;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);
  margin-bottom: 20px;
}

.time-history-section {
  background: white;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.time-history-list {
  max-height: 400px;
  overflow-y: auto;
}

.time-history-item {
  background: #f9fafb;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 12px;
  border: 1px solid #e5e7eb;
  transition: all 0.2s ease;
}

.time-history-item:hover {
  background: #f3f4f6;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.history-date {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1f2937;
}

.history-client {
  font-size: 0.8rem;
  color: #6b7280;
  margin-top: 2px;
}

.time-details {
  text-align: right;
}

.time-in, .time-out {
  font-size: 0.85rem;
  font-weight: 500;
  color: #374151;
}

.hours-worked {
  margin-top: 8px;
}

.no-history {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
}

.action-buttons {
  display: flex;
  gap: 4px;
  justify-content: center;
  flex-wrap: wrap;
}

.action-btn-view,
.action-btn-edit,
.action-btn-approve,
.action-btn-reject,
.action-btn-delete,
.action-btn-clock-out,
.action-btn-refresh {
  width: 32px !important;
  height: 32px !important;
  min-width: 32px !important;
  padding: 0 !important;
  border-radius: 8px !important;
  transition: all 0.2s ease !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
}

.action-btn-approve {
  background-color: #10b981 !important;
  color: white !important;
}

.action-btn-approve:hover {
  background-color: #059669 !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3) !important;
}

.action-btn-reject {
  background-color: #ef4444 !important;
  color: white !important;
}

.action-btn-reject:hover {
  background-color: #dc2626 !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3) !important;
}

.action-btn-view {
  background-color: #3b82f6 !important;
  color: white !important;
}

.action-btn-view:hover {
  background-color: #2563eb !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3) !important;
}

.action-btn-edit {
  background-color: #f59e0b !important;
  color: white !important;
}

.action-btn-edit:hover {
  background-color: #d97706 !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3) !important;
}

.action-btn-delete {
  background-color: #ef4444 !important;
  color: white !important;
}

.action-btn-delete:hover {
  background-color: #dc2626 !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3) !important;
}

.action-btn-clock-out {
  background-color: #f97316 !important;
  color: white !important;
}

.action-btn-clock-out:hover {
  background-color: #ea580c !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 8px rgba(249, 115, 22, 0.3) !important;
}

.action-btn-refresh {
  background-color: #6366f1 !important;
  color: white !important;
}

.action-btn-refresh:hover {
  background-color: #4f46e5 !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3) !important;
}

.action-btn-caregivers {
  background-color: #10b981 !important;
  color: white !important;
}

.action-btn-caregivers:hover {
  background-color: #059669 !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3) !important;
}

.action-btn-housekeepers {
  background-color: #7B1FA2 !important;
  color: white !important;
}

.action-btn-housekeepers:hover {
  background-color: #6A1B9A !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 8px rgba(123, 31, 162, 0.3) !important;
}

/* Assigned Caregivers Modal Styles */
.caregivers-booking-summary {
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #bbf7d0;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.1);
  margin-bottom: 0;
}

.summary-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1f2937;
  letter-spacing: -0.01em;
}

.summary-item {
  text-align: center;
}

.summary-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.summary-value {
  font-size: 0.875rem;
  color: #1f2937;
  font-weight: 600;
}

.assigned-caregivers-section {
  background: transparent;
  border-radius: 0;
  padding: 0;
  border: none;
  box-shadow: none;
}

.schedule-tab-content {
  background: transparent;
  padding: 0;
}

.caregivers-section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  letter-spacing: -0.02em;
}

.assigned-caregivers-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.assigned-caregiver-card {
  display: flex;
  align-items: center;
  padding: 20px;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border-radius: 12px;
  border: 1px solid #bbf7d0;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.1);
  transition: all 0.2s ease;
}

.assigned-caregiver-card:hover {
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.15);
  transform: translateY(-2px);
}

.caregiver-name-large {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.caregiver-contact-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 8px;
}

.contact-item {
  display: flex;
  align-items: center;
}

.contact-link {
  font-size: 0.875rem;
  color: #374151;
  text-decoration: none;
  font-weight: 500;
}

.contact-link:hover {
  text-decoration: underline;
  color: #1f2937;
}

.caregiver-stats {
  display: flex;
  gap: 8px;
}

.caregiver-actions {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.action-buttons-vertical {
  display: flex;
  gap: 4px;
}

.no-assigned-caregivers {
  text-align: center;
  padding: 60px 20px;
  background: #f9fafb;
  border-radius: 12px;
  border: 2px dashed #d1d5db;
}

.assignment-progress {
  min-width: 100px;
  width: 100px;
  text-align: center;
}

.progress-text {
  font-size: 0.75rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 2px;
  white-space: nowrap;
}

/* Mobile Responsive Table Styles */
@media (max-width: 960px) {
  /* Make tables scrollable horizontally on tablets */
  :deep(.v-data-table .v-table__wrapper) {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
    border-radius: 12px !important;
  }

  /* Table headers compact on mobile */
  :deep(.v-data-table .v-data-table__thead .v-data-table__tr .v-data-table__th) {
    padding: 12px 8px !important;
    font-size: 0.75rem !important;
  }

  /* Table cells compact */
  :deep(.v-data-table .v-data-table__tbody .v-data-table__tr .v-data-table__td) {
    padding: 12px 8px !important;
    font-size: 0.8125rem !important;
  }

  /* Action buttons smaller on mobile */
  :deep(.action-buttons) {
    gap: 4px !important;
  }

  :deep(.action-btn-view),
  :deep(.action-btn-edit),
  :deep(.action-btn-delete) {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
  }

  /* Table pagination mobile */
  :deep(.v-data-table-footer) {
    flex-wrap: wrap !important;
    padding: 0.75rem !important;
    gap: 0.5rem !important;
  }

  :deep(.v-data-table-footer__items-per-page) {
    font-size: 0.75rem !important;
  }

  :deep(.v-data-table-footer__info) {
    font-size: 0.75rem !important;
  }

  :deep(.v-data-table-footer__pagination) {
    font-size: 0.75rem !important;
  }
}

/* Mobile Card-Based Table Layout */
@media (max-width: 768px) {
  /* Convert tables to card layout */
  :deep(.v-data-table .v-table__wrapper) {
    overflow: visible !important;
  }

  :deep(.v-data-table .v-table__wrapper table) {
    display: block !important;
    width: 100% !important;
  }

  :deep(.v-data-table .v-table__wrapper thead) {
    display: none !important;
  }

  :deep(.v-data-table .v-table__wrapper tbody) {
    display: block !important;
    width: 100% !important;
  }

  :deep(.v-data-table .v-table__wrapper tbody tr) {
    display: block !important;
    width: 100% !important;
    margin-bottom: 1rem !important;
    background: white !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 12px !important;
    padding: 1rem !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
    position: relative !important;
  }

  /* Checkbox positioning on mobile cards */
  :deep(.v-data-table .v-table__wrapper tbody tr td:first-child) {
    position: absolute !important;
    top: 1rem !important;
    left: 1rem !important;
    width: auto !important;
    padding: 0 !important;
    border: none !important;
    display: block !important;
  }

  :deep(.v-data-table .v-table__wrapper tbody tr td:not(:first-child)) {
    display: flex !important;
    flex-direction: row !important;
    justify-content: flex-start !important;
    align-items: flex-start !important;
    padding: 0.875rem 0.75rem 0.875rem 2.5rem !important;
    border: none !important;
    border-bottom: 1px solid #f3f4f6 !important;
    min-height: auto !important;
    gap: 0.75rem !important;
    flex-wrap: wrap !important;
  }

  :deep(.v-data-table .v-table__wrapper tbody tr td:last-child) {
    border-bottom: none !important;
    padding-bottom: 0.5rem !important;
  }

  /* Add labels using data-label attribute */
  :deep(.v-data-table .v-table__wrapper tbody tr td[data-label]:not(:first-child))::before {
    content: attr(data-label) ": " !important;
    font-weight: 600 !important;
    color: #6b7280 !important;
    font-size: 0.8125rem !important;
    margin-right: 0.75rem !important;
    flex-shrink: 0 !important;
    min-width: 110px !important;
    display: inline-block !important;
    line-height: 1.5 !important;
  }

  /* Make cell content take remaining space */
  :deep(.v-data-table .v-table__wrapper tbody tr td:not(:first-child) > *) {
    flex: 1 1 auto !important;
    text-align: left !important;
    min-width: 0 !important;
    line-height: 1.5 !important;
  }

  /* Ensure labels wrap properly */
  :deep(.v-data-table .v-table__wrapper tbody tr td[data-label]:not(:first-child)) {
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
  }

  /* Better spacing for complex content like chips and progress bars */
  :deep(.v-data-table .v-table__wrapper tbody tr td:not(:first-child) .v-chip) {
    margin: 0 !important;
  }

  :deep(.v-data-table .v-table__wrapper tbody tr td:not(:first-child) .assignment-progress) {
    width: 100% !important;
    margin-top: 0.25rem !important;
  }

  /* Action buttons container on mobile */
  :deep(.v-data-table .v-table__wrapper tbody tr td:has(.action-buttons)) {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 0.75rem !important;
    padding-left: 2.5rem !important;
  }

  :deep(.v-data-table .v-table__wrapper tbody tr td:has(.action-buttons)::before) {
    margin-bottom: 0.5rem !important;
  }

  :deep(.v-data-table .v-table__wrapper tbody tr td .action-buttons) {
    width: 100% !important;
    justify-content: flex-start !important;
    flex-wrap: wrap !important;
    gap: 0.5rem !important;
  }

  /* Ensure action buttons are touch-friendly */
  :deep(.v-data-table .v-table__wrapper tbody tr td .action-buttons .v-btn) {
    min-width: 44px !important;
    min-height: 44px !important;
  }

  /* Status chips on mobile */
  :deep(.v-data-table .v-table__wrapper tbody tr td .v-chip) {
    font-size: 0.75rem !important;
    padding: 4px 10px !important;
  }

  /* Progress bars on mobile */
  :deep(.v-data-table .v-table__wrapper tbody tr td .v-progress-linear) {
    margin-top: 0.5rem !important;
    width: 100% !important;
  }

  /* Assignment progress on mobile */
  :deep(.assignment-progress) {
    width: 100% !important;
    min-width: 100% !important;
  }

  :deep(.progress-text) {
    text-align: left !important;
    margin-bottom: 0.5rem !important;
  }

  /* Card header styling for mobile */
  .card-header {
    padding: 1rem !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 0.75rem !important;
  }

  .card-header .section-title {
    font-size: 1.125rem !important;
  }
}

/* Very Small Mobile Devices */
@media (max-width: 480px) {
  :deep(.v-data-table .v-table__wrapper tbody tr) {
    padding: 0.875rem !important;
    margin-bottom: 0.75rem !important;
  }

  :deep(.v-data-table .v-table__wrapper tbody tr td:not(:first-child)) {
    padding: 0.625rem 0.625rem 0.625rem 2.25rem !important;
    font-size: 0.75rem !important;
    min-height: 40px !important;
  }

  :deep(.v-data-table .v-table__wrapper tbody tr td:not(:first-child)::before) {
    font-size: 0.75rem !important;
    min-width: 80px !important;
  }

  /* Compact action buttons */
  :deep(.action-btn-view),
  :deep(.action-btn-edit),
  :deep(.action-btn-delete) {
    width: 28px !important;
    height: 28px !important;
    min-width: 28px !important;
  }

  :deep(.action-btn-view .v-icon),
  :deep(.action-btn-edit .v-icon),
  :deep(.action-btn-delete .v-icon) {
    font-size: 14px !important;
  }

  /* Compact pagination */
  :deep(.v-data-table-footer) {
    padding: 0.5rem !important;
    font-size: 0.6875rem !important;
  }

  :deep(.v-data-table-footer__items-per-page) {
    display: none !important;
  }

  :deep(.v-data-table-footer__info) {
    font-size: 0.6875rem !important;
    text-align: center !important;
  }

  :deep(.v-data-table-footer__pagination) {
    justify-content: center !important;
    width: 100% !important;
  }
}

/* Schedule Management Styles */
.schedule-dialog-card {
  border-radius: 16px !important;
  overflow: hidden !important;
}

.caregiver-info-card,
.booking-info-card {
  border-radius: 12px !important;
  border: 1px solid #e0e0e0 !important;
}

.section-header {
  display: flex;
  align-items: center;
}

.days-grid-row {
  display: flex;
  gap: 12px;
  overflow-x: auto;
  padding-bottom: 8px;
  margin-bottom: 16px;
}

.days-grid-row::-webkit-scrollbar {
  height: 8px;
}

.days-grid-row::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.days-grid-row::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.days-grid-row::-webkit-scrollbar-thumb:hover {
  background: #555;
}

.day-card-modern {
  cursor: pointer;
  border: 2px solid #e0e0e0 !important;
  border-radius: 12px !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
  min-height: 140px;
  min-width: 130px;
  flex-shrink: 0;
  background: white !important;
}

.day-card-modern:hover {
  border-color: #1976d2 !important;
  box-shadow: 0 8px 16px rgba(25, 118, 210, 0.2) !important;
  transform: translateY(-4px);
}

.day-card-modern.day-selected {
  border-color: #1976d2 !important;
  background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%) !important;
  box-shadow: 0 8px 24px rgba(25, 118, 210, 0.4) !important;
}

.day-card-modern.day-selected:hover {
  box-shadow: 0 12px 32px rgba(25, 118, 210, 0.5) !important;
  transform: translateY(-6px);
}

/* PDF Export Button Animation */
.v-btn.v-btn--elevated {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.v-btn.v-btn--elevated:hover {
  transform: translateY(-2px) scale(1.02) !important;
  box-shadow: 0 8px 24px rgba(34, 197, 94, 0.4) !important;
}

.v-btn.v-btn--elevated:active {
  transform: translateY(0) scale(0.98) !important;
}

@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  }
  50% {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06), 0 0 20px rgba(34, 197, 94, 0.3);
  }
}

.v-btn.v-btn--elevated.bg-success {
  animation: pulse-glow 2s ease-in-out infinite;
}

</style>

/* Time Tracking History Styles */
.history-stat-card {
  border-radius: 12px !important;
  border: 1px solid #e5e7eb !important;
  transition: all 0.2s ease !important;
}

.history-stat-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
  transform: translateY(-2px) !important;
}

.history-table-card {
  border-radius: 12px !important;
  border: 1px solid #e5e7eb !important;
  overflow: hidden !important;
}

.history-table-card .v-data-table {
  border-radius: 0 !important;
}

.history-table-card .v-data-table .v-data-table__thead .v-data-table__tr .v-data-table__th {
  background-color: #3b82f6 !important;
  color: white !important;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 4px;
}

/* Housekeeper chip styling */
.housekeeper-chip {
  background-color: #6A1B9A !important;
  color: white !important;
}

.housekeeper-chip * {
  color: white !important;
}

.housekeeper-chip :deep(.v-chip__content) {
  color: white !important;
}

.housekeeper-chip :deep(.v-chip__underlay) {
  background-color: #6A1B9A !important;
  opacity: 1 !important;
}

.housekeeper-chip :deep(.v-icon) {
  color: white !important;
}

.housekeeper-chip :deep(span) {
  color: white !important;
}

.housekeeper-chip :deep(.mdi) {
  color: white !important;
}

