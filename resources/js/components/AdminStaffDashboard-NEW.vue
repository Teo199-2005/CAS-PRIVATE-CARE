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
    :user-name="profile.firstName && profile.lastName ? `${profile.firstName} ${profile.lastName}` : 'Admin Staff'"
    :user-initials="profile.firstName && profile.lastName ? `${profile.firstName[0]}${profile.lastName[0]}` : 'AS'"
    :user-avatar="userAvatar"
    :welcome-message="profile.firstName ? `Welcome Back, ${profile.firstName}` : 'Welcome Back, Admin Staff'"
    subtitle="Manage applications, bookings, and communications"
    header-title="Admin Staff Panel"
    header-subtitle="Limited administrative operations"
    :nav-items="navItems"
    :current-section="currentSection"
    @section-change="currentSection = $event"
    @toggle-click="handleNavClick"
    @logout="logout"
  >
    <template #header-left>
      <v-btn color="error" size="x-large" prepend-icon="mdi-bullhorn" class="admin-btn" @click="announceDialog = true">Send Announcement</v-btn>
      <v-btn color="success" size="x-large" prepend-icon="mdi-email-send" class="admin-btn ml-2" @click="testEmailDialog = true">Test Email</v-btn>
    </template>

    <!-- Email Verification Banner -->
    <email-verification-banner />

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
        </v-card-title>
        <v-data-table :headers="userHeaders" :items="filteredUsers" :items-per-page="10" class="elevation-0" density="compact">
          <template v-slot:item.status="{ item }">
            <v-chip :color="getUserStatusColor(item.status)" size="small" class="font-weight-bold" :prepend-icon="getStatusIcon(item.status)">{{ item.status }}</v-chip>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn class="action-btn-view" icon="mdi-eye" size="small" @click="viewUser(item)"></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>
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
          <template v-slot:item.status="{ item }">
            <v-chip :color="getUserStatusColor(item.status)" size="small" class="font-weight-bold" :prepend-icon="getStatusIcon(item.status)">{{ item.status }}</v-chip>
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
    <v-dialog v-model="viewCaregiverDialog" max-width="800">
      <v-card v-if="viewingCaregiver">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="section-title" style="color: white;">Caregiver Details</span>
            <v-btn icon="mdi-close" variant="text" style="color: white;" @click="viewCaregiverDialog = false"></v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6">
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
                <div class="detail-label">Email</div>
                <div class="detail-value">{{ viewingCaregiver.email }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Phone</div>
                <div class="detail-value">{{ viewingCaregiver.phone }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Borough</div>
                <div class="detail-value">{{ viewingCaregiver.borough }}</div>
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
          <template v-slot:item.status="{ item }">
            <v-chip :color="getUserStatusColor(item.status)" size="small" class="font-weight-bold" :prepend-icon="getStatusIcon(item.status)">{{ item.status }}</v-chip>
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
          <template v-slot:item.email_verified="{ item }">
            <v-chip :color="item.email_verified === 'Yes' ? 'success' : 'warning'" size="small">
              <v-icon size="14" class="mr-1">{{ item.email_verified === 'Yes' ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
              {{ item.email_verified }}
            </v-chip>
          </template>
          <template v-slot:item.status="{ item }">
            <v-chip :color="getUserStatusColor(item.status)" size="small" class="font-weight-bold" :prepend-icon="getStatusIcon(item.status)">
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
    <v-dialog v-model="adminStaffDialog" max-width="600">
      <v-card>
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <span class="section-title" style="color: white;">{{ editingAdminStaff ? 'Edit Admin Staff' : 'Add Admin Staff' }}</span>
        </v-card-title>
        <v-card-text class="pa-6">
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
          <v-alert type="info" variant="tonal" class="mt-4">
            <strong>Note:</strong> Admin Staff will have limited permissions and can only access: Users (Read-Only), Contractors, Password Resets, Bookings, Time Tracking, Reviews, and Announcements.
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
            <v-chip :color="getUserStatusColor(item.status)" size="small" class="font-weight-bold" :prepend-icon="getStatusIcon(item.status)">{{ item.status }}</v-chip>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="action-buttons">
              <v-btn class="action-btn-view" icon="mdi-eye" size="small" @click="viewTrainingCenterDetails(item)"></v-btn>
              <v-btn class="action-btn-edit" icon="mdi-pencil" size="small" @click="openTrainingCenterDialog(item)"></v-btn>
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
                <div v-if="trainingCenterZipLocation" style="font-weight: 600; color: #000000; margin-top: -8px; font-size: 0.75rem; line-height: 1.2;">
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
              <v-avatar size="120" :color="viewingApplication.type === 'Caregiver' ? 'success' : (viewingApplication.type === 'Marketing Partner' ? 'info' : 'warning')" class="mb-3">
                <span class="text-h3 font-weight-bold text-white">{{ viewingApplication.name.split(' ').map(n => n[0]).join('') }}</span>
              </v-avatar>
              <h2>{{ viewingApplication.name }}</h2>
              <v-chip 
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
                ${{ ((item.hoursPerDay * item.durationDays * (item.hourlyRate + item.referralDiscountApplied))).toLocaleString() }}
              </span>
              <span class="current-price">{{ item.formattedPrice }}</span>
            </div>
          </template>
          <template v-slot:item.status="{ item }">
            <v-chip :color="getBookingStatusColor(item.status)" size="small" class="font-weight-bold" :prepend-icon="getStatusIcon(item.status)">{{ item.status }}</v-chip>
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
              <v-btn v-if="item.status === 'approved' || item.status === 'confirmed'" class="action-btn-caregivers" icon="mdi-account-group" size="small" @click="viewAssignedCaregivers(item)"></v-btn>
              <v-btn v-if="item.status === 'approved' || item.status === 'confirmed'" class="action-btn-edit" icon="mdi-account-plus" size="small" @click="assignCaregiverDialog(item)"></v-btn>
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
                  <v-text-field v-model="profileData.email" label="Email" variant="outlined" type="email" readonly>
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
              <v-text-field label="Current Password" variant="outlined" :type="showCurrentPassword ? 'text' : 'password'" :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showCurrentPassword = !showCurrentPassword" class="mb-4" />
              <v-text-field label="New Password" variant="outlined" :type="showNewPassword ? 'text' : 'password'" :append-inner-icon="showNewPassword ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showNewPassword = !showNewPassword" hint="8 minimum characters" persistent-hint class="mb-4" />
              <v-text-field label="Confirm New Password" variant="outlined" :type="showConfirmPassword ? 'text' : 'password'" :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showConfirmPassword = !showConfirmPassword" class="mb-4" />
              <v-btn color="error" block size="large">Change Password</v-btn>
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
                  <div class="caregiver-borough">{{ caregiver.borough }}</div>
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
                <v-select v-if="!caregiverForm.isCustomTrainingCenter" v-model="caregiverForm.trainingCenter" :items="caregiverTrainingCenterOptions" label="Training Center" variant="outlined" no-data-text="No CAS training centers. Use Custom Training Center to enter one." />
                <v-text-field v-else v-model="caregiverForm.customTrainingCenter" label="Custom Training Center" variant="outlined" />
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
              <v-text-field 
                v-model="bookingForm.service_type" 
                label="Service Type *" 
                readonly
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
    <v-dialog v-model="assignDialog" max-width="700">
      <v-card class="assign-dialog-card">
        <v-card-title class="assign-dialog-header pa-6">
          <div class="d-flex align-center justify-space-between w-100">
            <div>
              <span class="assign-dialog-title">Assign Caregivers</span>
              <div class="assign-dialog-subtitle">Select caregivers for this booking</div>
            </div>
            <v-chip 
              :color="assignSelectedCaregivers.length > (selectedBooking?.caregiversNeeded || 0) ? 'warning' : 'error'" 
              size="large" 
              class="font-weight-bold selection-counter"
            >
              {{ assignSelectedCaregivers.length }} / {{ selectedBooking?.caregiversNeeded || 0 }} Selected
              <v-icon v-if="assignSelectedCaregivers.length > (selectedBooking?.caregiversNeeded || 0)" size="16" class="ml-1">mdi-alert</v-icon>
            </v-chip>
            <div v-if="assignSelectedCaregivers.length > (selectedBooking?.caregiversNeeded || 0)" class="text-warning text-caption mt-1">
              <v-icon size="12" class="mr-1">mdi-information</v-icon>
              Too many caregivers selected. This booking only needs {{ selectedBooking?.caregiversNeeded || 0 }}.
            </div>
          </div>
        </v-card-title>
        <v-card-text class="pa-6">
          <div v-if="selectedBooking" class="booking-details-card mb-4">
            <div class="booking-details-header">Booking Details</div>
            <v-row class="booking-details-content">
              <v-col cols="6">
                <div class="detail-item">
                  <v-icon color="error" size="16" class="mr-2">mdi-account</v-icon>
                  <span class="detail-label">Client:</span>
                  <span class="detail-value">{{ selectedBooking.client }}</span>
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
          </div>
          
          <v-divider class="mb-4" />
          
          <div class="mb-4">
            <v-row>
              <v-col cols="8">
                <v-text-field
                  v-model="assignCaregiverSearch"
                  placeholder="Search caregivers by name..."
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
                      {{ caregiver.borough }}
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
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="closeAssignDialog">Cancel</v-btn>
          <v-btn 
            color="error" 
            @click="confirmAssignCaregivers"
          >
            {{ assignSelectedCaregivers.length === 0 ? 'Unassign All' : `Assign ${assignSelectedCaregivers.length} Caregiver${assignSelectedCaregivers.length !== 1 ? 's' : ''}` }}
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
                      <span class="booking-detail-label">Caregivers Assigned:</span>
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
                      <div class="timeline-title">Caregivers Assigned</div>
                      <div class="timeline-subtitle">{{ viewingBooking.assignedCount }} caregiver(s) assigned</div>
                    </div>
                  </div>
                  <div v-if="viewingBooking.status === 'Assigned'" class="timeline-item">
                    <div class="timeline-dot timeline-dot-success"></div>
                    <div class="timeline-content">
                      <div class="timeline-title">Fully Assigned</div>
                      <div class="timeline-subtitle">All required caregivers assigned</div>
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
                      v-for="day in getAvailableDays(viewingBookingCaregivers.dutyType || viewingBookingCaregivers.duty_type)" 
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
  status: 'Active' 
});

// CAS training center partners only (loaded from API)
const caregiverTrainingCenters = ref([]);
// Include current form value so selector always shows what training center it is
const caregiverTrainingCenterOptions = computed(() => {
  const list = [...(caregiverTrainingCenters.value || [])];
  const current = (caregiverForm.value?.trainingCenter || '').trim();
  if (current && !list.includes(current)) list.unshift(current);
  return list;
});
const loadCaregiverTrainingCenters = async () => {
  try {
    const response = await fetch('/api/training-centers?active_only=1', {
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
      credentials: 'include'
    });
    const data = await response.json().catch(() => ({}));
    const centers = Array.isArray(data) ? data : (data.centers || data.training_centers || data.trainingCenters || []);
    caregiverTrainingCenters.value = (centers || [])
      .map(c => (typeof c === 'string' ? c : (c?.name || c?.title || '')))
      .map(s => String(s || '').trim())
      .filter(Boolean);
  } catch (e) {
    caregiverTrainingCenters.value = [];
  }
};

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
  { title: 'Borough', key: 'borough' },
  { title: 'Status', key: 'status' },
  { title: 'Rating', key: 'rating' },
  { title: 'Clients', key: 'clients' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const clientHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
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
const marketingStaffHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
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
const adminStaffFormData = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  status: 'Active'
});
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
const trainingCenterHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
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
    platformMetrics.value.bookings = activeBookings.toString();
    platformMetrics.value.sessions = totalUsers.toString();
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
    const admins = totalUsers - totalCaregivers - totalClients;
    clientMetrics.value[0].value = totalClients.toString();
    caregiverMetrics.value[0].value = totalCaregivers.toString();
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
    const response = await fetch('/api/admin/users');
    const data = await response.json();
    users.value = data.users;
    
    const caregiverUsers = data.users.filter(u => u.type === 'Caregiver');
    
    caregivers.value = caregiverUsers
      .filter(u => u.caregiver && u.caregiver.id)
      .map((u) => {
        return {
          id: u.caregiver.id,
          userId: u.id,
          name: u.name,
          email: u.email,
          status: 'Active',
          rating: u.caregiver?.rating || null,
          clients: 0,
          joined: u.joined,
          verified: true,
          borough: 'Manhattan',
          phone: u.phone || '(646) 282-8282',
          // Use actual stored file path from DB (null when not uploaded)
          training_certificate: u.caregiver?.training_certificate || null
        };
      });
    
    clients.value = data.users.filter(u => u.type === 'Client').map(u => ({
      id: u.id,
      name: u.name,
      email: u.email,
      status: u.status,
      bookings: 0,
      totalSpent: '$0',
      joined: u.joined,
      verified: true
    }));
  } catch (error) {
  }
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
const processSalariesDialog = ref(false);
const timeTrackingSearch = ref('');
const timeTrackingDateFilter = ref('Today');
const timeTrackingStatusFilter = ref('All');

const bookingSearch = ref('');
const bookingStatusFilter = ref('All');
const bookingDateFilter = ref('All Time');
const addBookingDialog = ref(false);
const bookingZipLocation = ref('');

// ZIP -> City/State (API-backed, cached). No guessing.
const zipCityStateCache = new Map();
const normalizeZip5 = (zipLike) => {
  if (!zipLike) return '';
  const zip = String(zipLike).trim();
  const m = zip.match(/^(\d{5})/);
  return m ? m[1] : '';
};

const resolveZipCityState = async (zipLike) => {
  const zip = normalizeZip5(zipLike);
  if (!zip) return '';

  if (zipCityStateCache.has(zip)) return zipCityStateCache.get(zip);

  try {
    const resp = await fetch(`/api/zipcode-lookup/${zip}`, {
      headers: { Accept: 'application/json' },
    });

    if (!resp.ok) {
      zipCityStateCache.set(zip, '');
      return '';
    }

    const data = await resp.json();
    const city = (data?.city || '').trim();
    const state = (data?.state || '').trim();
    const cityState = city && state ? `${city}, ${state}` : '';
    zipCityStateCache.set(zip, cityState);
    return cityState;
  } catch (e) {
    zipCityStateCache.set(zip, '');
    return '';
  }
};

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

  bookingZipLocation.value = await resolveZipCityState(zip);
};

const lookupTrainingCenterZipCode = async () => {
  const zip = normalizeZip5(trainingCenterFormData.value.zip_code);
  if (!zip) {
    trainingCenterZipLocation.value = '';
    return;
  }

  trainingCenterZipLocation.value = await resolveZipCityState(zip);
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

  clientZipLocation.value = await resolveZipCityState(zip);
};

const caregiverZipLocation = ref('');
const lookupCaregiverZipCode = async () => {
  const zip = normalizeZip5(caregiverForm.value.zip_code);
  if (!zip) {
    caregiverZipLocation.value = '';
    return;
  }

  caregiverZipLocation.value = await resolveZipCityState(zip);
};

const marketingStaffZipLocation = ref('');
const lookupMarketingStaffZipCode = async () => {
  const zip = normalizeZip5(marketingStaffFormData.value.zip_code);
  if (!zip) {
    marketingStaffZipLocation.value = '';
    return;
  }

  marketingStaffZipLocation.value = await resolveZipCityState(zip);
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
      const link = document.createElement('a');
      link.href = url;
      link.download = `CAS-TimeTracking-Report-${new Date().toISOString().split('T')[0]}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
      
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
  { type: 'stripe', name: 'Stripe', icon: 'mdi-credit-card', color: 'info', status: 'Active', details: 'Credit/Debit Cards' },
  { type: 'paypal', name: 'PayPal', icon: 'mdi-paypal', color: 'primary', status: 'Active', details: 'PayPal Payments' },
  { type: 'bank', name: 'Bank Transfer', icon: 'mdi-bank', color: 'success', status: 'Active', details: 'ACH Transfers' },
  { type: 'cash', name: 'Cash Payment', icon: 'mdi-cash', color: 'warning', status: 'Limited', details: 'In-person only' },
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

const salaryHeaders = [
  { title: 'Caregiver', key: 'caregiver' },
  { title: 'Hours Worked', key: 'hours' },
  { title: 'Rate', key: 'rate' },
  { title: 'Total Amount', key: 'amount' },
  { title: 'Period', key: 'period' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const caregiverSalaries = ref([]);

const loadCaregiverSalaries = async () => {
  try {
    const response = await fetch('/api/admin/caregiver-salaries');
    const data = await response.json();
    caregiverSalaries.value = data.salaries || [];
  } catch (error) {
  }
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
      const timeFormatted = startingTime ? (() => {
        const [hours, minutes] = startingTime.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
      })() : 'N/A';
      
      return {
        id: b.id,
        client: b.client?.name || 'Unknown',
        service: b.service_type,
        date: date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
        time: timeFormatted,
        startingTime: timeFormatted,
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
      message: error.message,
      stack: error.stack,
      name: error.name
    });
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
    
    if (response.ok) {
      success('Profile changes saved successfully!');
      // Update the header name
      profile.value.firstName = profileData.value.firstName;
      profile.value.lastName = profileData.value.lastName;
    } else {
      const data = await response.json();
      error('Error: ' + (data.message || 'Failed to save profile'));
    }
  } catch (err) {
    error('Failed to save profile. Please try again.');
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
    marketingStaff.value = data.staff || [];
  } catch (err) {
  }
};

const viewMarketingStaffDetails = (staff) => {
  viewingMarketingStaff.value = staff;
  viewMarketingStaffDialog.value = true;
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
    adminStaff.value = data.staff || [];
  } catch (err) {
    adminStaff.value = [];
  }
};

const viewAdminStaffDetails = (staff) => {
  viewingAdminStaff.value = staff;
  viewAdminStaffDialog.value = true;
};

const openAdminStaffDialog = (staff = null) => {
  if (staff) {
    editingAdminStaff.value = staff;
    adminStaffFormData.value = {
      name: staff.name || '',
      email: staff.email || '',
      phone: staff.phone || '',
      password: '',
      status: staff.status || 'Active'
    };
  } else {
    editingAdminStaff.value = null;
    adminStaffFormData.value = { 
      name: '', 
      email: '', 
      phone: '', 
      password: '', 
      status: 'Active' 
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
      status: adminStaffFormData.value.status
    };
    
    // Only include password if it's provided
    if (adminStaffFormData.value.password) {
      formData.password = adminStaffFormData.value.password;
    }

    const response = await fetch(url, {
      method: editingAdminStaff.value ? 'PUT' : 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(formData)
    });

    if (response.ok) {
      success(editingAdminStaff.value ? 'Admin staff updated!' : 'Admin staff created!', 'Success');
      adminStaffDialog.value = false;
      await loadAdminStaff();
    } else {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Failed to save');
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
    
    trainingCenters.value = data.centers || [];
    
    if (trainingCenters.value.length === 0) {
    }
  } catch (err) {
    trainingCenters.value = [];
  }
};

const viewTrainingCenterDetails = async (center) => {
  viewingTrainingCenter.value = { ...center };
  viewTrainingCenterDialog.value = true;
  
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
    
      isEdit: !!editingTrainingCenter.value,
      id: editingTrainingCenter.value?.id,
      url: url,
      formData: trainingCenterFormData.value
    });
    
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
  loadCaregiverTrainingCenters(); // Populate Training Center dropdown with CAS partners
  if (caregiver) {
    editingCaregiver.value = true;
    const nameParts = (caregiver.name || '').split(' ');
    caregiverForm.value = {
      id: caregiver.id || caregiver.userId,
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
      trainingCenter: caregiver.training_center || '',
      customTrainingCenter: '',
      isCustomTrainingCenter: false,
      trainingCertificate: null,
      bio: caregiver.bio || '',
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
  viewingCaregiver.value = caregiver;
  viewCaregiverDialog.value = true;
  
  // Load caregiver reviews
  await loadCaregiverReviews(caregiver.caregiverId);
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
  const colors = { 'Paid': 'success', 'Pending': 'warning', 'Overdue': 'error' };
  return colors[status] || 'grey';
};

const getSalaryStatusColor = (status) => {
  const colors = { 'Paid': 'success', 'Pending': 'warning', 'Processing': 'info' };
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
  
  // Load currently assigned caregivers for this booking
  assignSelectedCaregivers.value = caregiverAssignments.value[booking.id] || [];
  
  assignDialog.value = true;
};

const closeAssignDialog = () => {
  assignDialog.value = false;
  assignSelectedCaregivers.value = [];
  selectedBooking.value = null;
};

const toggleCaregiverSelection = (caregiverId) => {
  const index = assignSelectedCaregivers.value.indexOf(caregiverId);
  if (index > -1) {
    // Uncheck - remove from selection
    assignSelectedCaregivers.value.splice(index, 1);
  } else {
    // Check - add to selection but validate limit
    const maxNeeded = selectedBooking.value?.caregiversNeeded || 1;
    if (assignSelectedCaregivers.value.length >= maxNeeded) {
      warning(`This booking only needs ${maxNeeded} caregiver${maxNeeded !== 1 ? 's' : ''}. Please unselect a caregiver first.`, 'Selection Limit Reached');
      return;
    }
    assignSelectedCaregivers.value.push(caregiverId);
  }
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
      borough: 'Manhattan'
    }));
  }
  
  // Fallback to caregivers array
  return caregivers.value.filter(c => assignedIds.includes(c.id));
};

const viewBookingDialog = ref(false);
const viewingBooking = ref(null);
const viewAssignedCaregiversDialog = ref(false);
const viewingBookingCaregivers = ref(null);
const caregiverSchedules = ref({}); // Store schedules for each caregiver
const weeklySchedule = ref({}); // Store caregiver assignments for each day: { monday: caregiverId, tuesday: caregiverId, ... }

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

const getAvailableDays = (dutyType) => {
  // If 8 hours, show Monday-Friday only
  if (dutyType && dutyType.toLowerCase().includes('8 hours')) {
    return daysOfWeek.filter(day => 
      !['saturday', 'sunday'].includes(day.value)
    );
  }
  // For 12 hours and 24 hours, show all days
  return daysOfWeek;
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
      bookingId: scheduleBooking.value.id,
      caregiverId: scheduleCaregiver.value.id,
      days: selectedDays.value,
      schedules: daySchedules.value
    });
    
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
      // Show assign dialog immediately after approval
      setTimeout(() => {
        assignCaregiverDialog(booking);
      }, 500);
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
    
    // Save to database
    const response = await fetch(`/api/bookings/${bookingId}/assign`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ caregiver_ids: assignSelectedCaregivers.value })
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
      success(`${assignSelectedCaregivers.value.length} caregiver(s) ${statusText} successfully!`, 'Assignment Complete');
    }
    
    // Refresh client bookings data
    await loadClientBookings();
    
    closeAssignDialog();
  } catch (err) {
    error(err.message || 'Failed to update caregiver assignments. Please try again.', 'Assignment Failed');
  }
};

const viewPayment = (payment) => {
  info(`Viewing payment details for ${payment.client}`, 'Payment Details');
};

const markPaid = (payment) => {
  if (confirm(`Mark payment from ${payment.client} as paid?`)) {
    payment.status = 'Paid';
    success('Payment marked as paid!', 'Payment Updated');
  }
};

const viewSalary = (salary) => {
  info(`Viewing salary details for ${salary.caregiver}`, 'Salary Details');
};

const paySalary = (salary) => {
  if (confirm(`Process salary payment for ${salary.caregiver}?`)) {
    salary.status = 'Processing';
    success('Salary payment initiated!', 'Payment Processing');
  }
};

const exportTransactions = () => {
  info('Exporting transactions to CSV...', 'Export Started');
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
  if (!caregiver.borough) {
    const boroughsList = ['Manhattan', 'Brooklyn', 'Queens'];
    caregiver.borough = boroughsList[index] || 'Manhattan';
  }
});

const filteredAndSortedCaregivers = computed(() => {
  let filtered = caregivers.value.filter(caregiver => {
    const matchesSearch = !caregiverSearch.value || 
      caregiver.name.toLowerCase().includes(caregiverSearch.value.toLowerCase()) ||
      caregiver.email.toLowerCase().includes(caregiverSearch.value.toLowerCase());
    const matchesBorough = boroughFilter.value === 'All' || caregiver.borough === boroughFilter.value;
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

// Include current form city so saved values display (permanent fix for city not showing after load).
const clientCities = computed(() => {
  if (!clientForm.value.county) return [];
  const list = countyCityMap[clientForm.value.county] || [];
  const current = clientForm.value.city?.trim();
  if (!current) return list;
  const inList = list.some((c) => String(c).trim().toLowerCase() === current.toLowerCase());
  if (!inList) return [current, ...list];
  return list;
});

const caregiverCities = computed(() => {
  if (!caregiverForm.value.county) return [];
  const list = countyCityMap[caregiverForm.value.county] || [];
  const current = caregiverForm.value.city?.trim();
  if (!current) return list;
  const inList = list.some((c) => String(c).trim().toLowerCase() === current.toLowerCase());
  if (!inList) return [current, ...list];
  return list;
});

// Permanent: only reset city when admin changes county and current city is not valid for the new county.
watch(() => clientForm.value.county, (newCounty) => {
  if (!newCounty) return;
  const list = countyCityMap[newCounty] || [];
  const current = clientForm.value.city?.trim();
  if (!current) return;
  const valid = list.some((c) => String(c).trim().toLowerCase() === current.toLowerCase());
  if (!valid) clientForm.value.city = '';
});

watch(() => caregiverForm.value.county, (newCounty) => {
  if (!newCounty) return;
  const list = countyCityMap[newCounty] || [];
  const current = caregiverForm.value.city?.trim();
  if (!current) return;
  const valid = list.some((c) => String(c).trim().toLowerCase() === current.toLowerCase());
  if (!valid) caregiverForm.value.city = '';
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
  loadQuickCaregivers();
  
  // Load payment & financial data from database
  loadPaymentStats();
  loadRecentTransactions();
  loadClientPayments();
  loadCaregiverSalaries();
  loadAllTransactions();
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
});
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

.caregiver-borough {
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