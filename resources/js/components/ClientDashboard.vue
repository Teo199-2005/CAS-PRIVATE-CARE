<template>
  <dashboard-template
    user-role="client"
    :user-name="userName"
    :user-initials="userInitials"
    :user-avatar="userAvatar"
    :welcome-message="welcomeMessage"
    subtitle="Manage your care services and bookings"
    header-title="Quality Care, Trusted Support"
    header-subtitle="Professional caregiving services at your fingertips"
    :nav-items="navItems"
    :current-section="currentSection"
    @section-change="currentSection = $event"
    @logout="logout"
  >
    <template #header-left>
      <v-btn color="success" size="x-large" prepend-icon="mdi-calendar-check" class="book-now-btn" @click="attemptBooking">Book Now</v-btn>
    </template>

    <!-- Email Verification Banner -->
    <email-verification-banner :user-data="userData" />

        <!-- Dashboard Section -->
        <div v-if="currentSection === 'dashboard'">
          <v-row class="mb-4">
            <v-col v-for="stat in stats" :key="stat.title" cols="6" sm="6" md="3">
              <stat-card :icon="stat.icon" :value="stat.value" :label="stat.title" :change="stat.change" :change-color="stat.changeColor" :change-icon="stat.changeIcon" />
            </v-col>
          </v-row>

          <v-row class="mt-2">
            <v-col cols="12">
              <v-card class="mb-6" elevation="0">
                <v-card-title class="card-header pa-6">
                  <div class="d-flex align-center justify-space-between">
                    <span class="section-title primary--text">My Bookings</span>
                    <v-chip color="primary" size="small">{{ allClientBookings.length }}</v-chip>
                  </div>
                  <p class="text-caption text-grey ma-0 mt-1">Review and manage your care service requests</p>
                </v-card-title>
                
                <!-- Tabs -->
                <v-tabs v-model="bookingTab" color="primary" density="compact" class="px-2 my-3" show-arrows>
                  <v-tab value="pending" class="text-caption">
                    <v-icon start size="small">mdi-clock-outline</v-icon>
                    <span class="text-xs">Pending</span>
                    <v-chip v-if="pendingBookings.length > 0" size="x-small" color="warning" class="ml-1">{{ pendingBookings.length }}</v-chip>
                  </v-tab>
                  <v-tab value="approved" class="text-caption">
                    <v-icon start size="small">mdi-check-circle</v-icon>
                    <span class="text-xs">Approved</span>
                    <v-chip v-if="confirmedBookings.length > 0" size="x-small" color="success" class="ml-1">{{ confirmedBookings.length }}</v-chip>
                  </v-tab>
                  <v-tab value="completed" class="text-caption">
                    <v-icon start size="small">mdi-checkbox-marked-circle</v-icon>
                    <span class="text-xs">Completed</span>
                    <v-chip v-if="completedBookings.length > 0" size="x-small" color="grey" class="ml-1">{{ completedBookings.length }}</v-chip>
                  </v-tab>
                </v-tabs>

                <v-card-text class="pa-0">
                  <v-window v-model="bookingTab">
                    <!-- Pending Tab -->
                    <v-window-item value="pending">
                      <div v-if="pendingBookings.length === 0" class="text-center pa-8">
                        <v-icon size="48" color="grey-lighten-2" class="mb-3">mdi-clock-outline</v-icon>
                        <p class="text-grey mb-0">No pending bookings</p>
                      </div>
                      <div v-else>
                        <div v-for="booking in pendingBookings.slice(0, 3)" :key="booking.id" class="contract-item pa-4 border-b">
                          <div class="mb-3">
                            <!-- Header -->
                            <div class="d-flex align-center justify-space-between mb-3">
                              <div class="d-flex align-center flex-grow-1">
                                <v-avatar size="44" color="warning" class="mr-3">
                                  <v-icon color="white" size="24">mdi-clock-outline</v-icon>
                                </v-avatar>
                                <div>
                                  <div class="text-subtitle-1 font-weight-bold">{{ booking.service || booking.serviceType }}</div>
                                  <div class="text-caption text-grey">{{ booking.date }} • {{ booking.startingTime }}</div>
                                  <div class="text-caption text-grey">
                                    <v-icon size="12" class="mr-1">mdi-map-marker</v-icon>
                                    {{ booking.location }}
                                  </div>
                                </div>
                              </div>
                              <div class="text-right">
                                <div class="text-h6 warning--text font-weight-bold">
                                  <span v-if="getOriginalBookingPrice(booking)" class="original-price-small">${{ getOriginalBookingPrice(booking) }}</span>
                                  ${{ getBookingPrice(booking) }}
                                </div>
                                <v-chip color="warning" size="x-small" class="font-weight-bold">
                                  <v-icon start size="12">mdi-clock</v-icon>
                                  Pending
                                </v-chip>
                              </div>
                            </div>

                            <!-- Unified Details Section - 2x2 Grid Layout -->
                            <div class="mb-2 pa-3" style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                              <v-row dense>
                                <!-- Service Information -->
                                <v-col cols="6">
                                  <div class="pa-2" style="border-right: 1px solid #e2e8f0;">
                                    <div class="text-caption font-weight-bold mb-2" style="color: #475569;">
                                      <v-icon size="14" color="primary" class="mr-1">mdi-information-outline</v-icon>
                                      Service Information
                                    </div>
                                    <div class="text-caption mb-1">
                                      <span class="text-grey">Hours per Day:</span> <span class="font-weight-bold">{{ booking.hoursPerDay || 8 }} hours</span>
                                    </div>
                                    <div class="text-caption mb-1">
                                      <span class="text-grey">Duration:</span> <span class="font-weight-bold">{{ booking.duration || 1 }} days</span>
                                    </div>
                                    <div class="text-caption">
                                      <span class="text-grey">Starting Time:</span> <span class="font-weight-bold">{{ booking.startingTime || 'N/A' }}</span>
                                    </div>
                                  </div>
                                </v-col>

                                <!-- Location -->
                                <v-col cols="6">
                                  <div class="pa-2">
                                    <div class="text-caption font-weight-bold mb-2" style="color: #475569;">
                                      <v-icon size="14" color="primary" class="mr-1">mdi-map-marker-outline</v-icon>
                                      Location
                                    </div>
                                    <div class="text-caption mb-1">
                                      <span class="text-grey">City/Borough:</span> <span class="font-weight-bold">{{ booking.borough || booking.location || 'N/A' }}</span>
                                    </div>
                                    <div class="text-caption mb-1">
                                      <span class="text-grey">Street Address:</span> <span class="font-weight-bold">{{ booking.streetAddress || 'N/A' }}</span>
                                    </div>
                                    <div class="text-caption" v-if="booking.apartmentUnit">
                                      <span class="text-grey">Apt/Unit:</span> <span class="font-weight-bold">{{ booking.apartmentUnit }}</span>
                                    </div>
                                  </div>
                                </v-col>

                                <!-- Client Information -->
                                <v-col cols="6">
                                  <div class="pa-2 pt-2" style="border-right: 1px solid #e2e8f0; border-top: 1px solid #e2e8f0;">
                                    <div class="text-caption font-weight-bold mb-2" style="color: #475569;">
                                      <v-icon size="14" color="primary" class="mr-1">mdi-account-circle-outline</v-icon>
                                      Client Information
                                    </div>
                                    <div class="text-caption mb-1">
                                      <span class="text-grey">Age:</span> <span class="font-weight-bold">{{ booking.clientAge || 'N/A' }}</span>
                                    </div>
                                    <div class="text-caption mb-1">
                                      <span class="text-grey">Mobility:</span> <span class="font-weight-bold">{{ booking.mobilityLevel || 'Standard' }}</span>
                                    </div>
                                    <div class="text-caption" v-if="booking.medicalConditions">
                                      <span class="text-grey">Medical:</span> <span class="font-weight-bold">{{ booking.medicalConditions }}</span>
                                    </div>
                                  </div>
                                </v-col>

                                <!-- Special Instructions -->
                                <v-col cols="6">
                                  <div class="pa-2 pt-2" style="border-top: 1px solid #e2e8f0;">
                                    <div class="text-caption font-weight-bold mb-2" style="color: #475569;">
                                      <v-icon size="14" color="primary" class="mr-1">mdi-note-text-outline</v-icon>
                                      Special Instructions
                                    </div>
                                    <div class="text-caption">{{ booking.specialInstructions || 'None specified' }}</div>
                                  </div>
                                </v-col>
                              </v-row>
                            </div>

                            <!-- Divider -->
                            <v-divider class="my-3"></v-divider>

                            <!-- Pending Status -->
                            <div class="text-center pa-2" style="background: #fffbeb; border-radius: 6px;">
                              <v-icon color="warning" size="16" class="mb-1">mdi-clock-alert-outline</v-icon>
                              <div class="font-weight-bold" style="color: #d97706; font-size: 0.7rem;">
                                Pending Assignment
                              </div>
                              <div class="text-grey" style="font-size: 0.65rem;">
                                We'll notify you once approved
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </v-window-item>

                    <!-- Approved Tab -->
                    <v-window-item value="approved">
                      <div v-if="confirmedBookings.length === 0" class="text-center pa-8">
                        <v-icon size="48" color="grey-lighten-2" class="mb-3">mdi-check-circle</v-icon>
                        <p class="text-grey mb-4">No approved bookings</p>
                        <v-btn color="primary" variant="outlined" size="small" @click="attemptBooking">Book Service</v-btn>
                      </div>
                      <div v-else>
                        <div v-for="booking in confirmedBookings.slice(0, 3)" :key="booking.id" class="contract-item pa-4 border-b">
                          <div class="mb-3">
                            <!-- Header with Service and Price -->
                            <div class="d-flex align-center justify-space-between mb-3">
                              <div class="d-flex align-center flex-grow-1">
                                <v-avatar size="44" color="success" class="mr-3">
                                  <v-icon color="white" size="24">mdi-check-circle</v-icon>
                                </v-avatar>
                                <div>
                                  <div class="text-subtitle-1 font-weight-bold">{{ booking.service || booking.serviceType }}</div>
                                  <div class="text-caption text-grey">{{ booking.date }} • {{ booking.startingTime }}</div>
                                  <div class="text-caption text-grey">
                                    <v-icon size="12" class="mr-1">mdi-map-marker</v-icon>
                                    {{ booking.location }}
                                  </div>
                                </div>
                              </div>
                              <div class="text-right d-flex flex-column align-end" style="gap: 8px;">
                                <div class="d-flex align-center" style="gap: 12px;">
                                  <div class="text-h6 success--text font-weight-bold">${{ getBookingPrice(booking) }}</div>
                                  <v-chip 
                                    :color="booking.payment_status === 'paid' ? 'success' : 'warning'" 
                                    size="x-small" 
                                    class="font-weight-bold"
                                  >
                                    <v-icon start size="12">
                                      {{ booking.payment_status === 'paid' ? 'mdi-check-circle' : 'mdi-check' }}
                                    </v-icon>
                                    {{ booking.payment_status === 'paid' ? 'Paid' : 'Approved' }}
                                  </v-chip>
                                </div>
                                
                                <!-- Show Receipt Button if Paid, otherwise Pay Now -->
                                <v-btn 
                                  v-if="booking.payment_status === 'paid'"
                                  color="success" 
                                  size="large" 
                                  prepend-icon="mdi-receipt-text"
                                  :href="`/api/receipts/payment/${booking.id}`"
                                  target="_blank"
                                  elevation="3"
                                  class="text-none font-weight-bold px-10"
                                  style="min-width: 220px;"
                                >
                                  View Receipt
                                </v-btn>
                                <v-btn 
                                  v-else
                                  color="error" 
                                  size="large" 
                                  prepend-icon="mdi-credit-card"
                                  @click="goToPayment(booking)"
                                  elevation="3"
                                  class="text-none font-weight-bold px-10 pay-now-glow"
                                  style="min-width: 220px;"
                                >
                                  Pay Now
                                </v-btn>
                              </div>
                            </div>

                            <!-- Unified Details Section - 2x2 Grid Layout -->
                            <div class="mb-3 pa-3" style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                              <v-row dense>
                                <!-- Service Information -->
                                <v-col cols="6">
                                  <div class="pa-2" style="border-right: 1px solid #e2e8f0;">
                                    <div class="text-caption font-weight-bold mb-2" style="color: #475569;">
                                      <v-icon size="14" color="primary" class="mr-1">mdi-information-outline</v-icon>
                                      Service Information
                                    </div>
                                    <div class="text-caption mb-1">
                                      <span class="text-grey">Hours per Day:</span> <span class="font-weight-bold">{{ booking.hoursPerDay || 8 }} hours</span>
                                    </div>
                                    <div class="text-caption mb-1">
                                      <span class="text-grey">Duration:</span> <span class="font-weight-bold">{{ booking.duration || 1 }} days</span>
                                    </div>
                                    <div class="text-caption">
                                      <span class="text-grey">Starting Time:</span> <span class="font-weight-bold">{{ booking.startingTime || 'N/A' }}</span>
                                    </div>
                                  </div>
                                </v-col>

                                <!-- Location -->
                                <v-col cols="6">
                                  <div class="pa-2">
                                    <div class="text-caption font-weight-bold mb-2" style="color: #475569;">
                                      <v-icon size="14" color="primary" class="mr-1">mdi-map-marker-outline</v-icon>
                                      Location
                                    </div>
                                    <div class="text-caption mb-1">
                                      <span class="text-grey">City/Borough:</span> <span class="font-weight-bold">{{ booking.borough || booking.location || 'N/A' }}</span>
                                    </div>
                                    <div class="text-caption mb-1">
                                      <span class="text-grey">Street Address:</span> <span class="font-weight-bold">{{ booking.streetAddress || 'N/A' }}</span>
                                    </div>
                                    <div class="text-caption" v-if="booking.apartmentUnit">
                                      <span class="text-grey">Apt/Unit:</span> <span class="font-weight-bold">{{ booking.apartmentUnit }}</span>
                                    </div>
                                  </div>
                                </v-col>

                                <!-- Client Information -->
                                <v-col cols="6">
                                  <div class="pa-2 pt-2" style="border-right: 1px solid #e2e8f0; border-top: 1px solid #e2e8f0;">
                                    <div class="text-caption font-weight-bold mb-2" style="color: #475569;">
                                      <v-icon size="14" color="primary" class="mr-1">mdi-account-circle-outline</v-icon>
                                      Client Information
                                    </div>
                                    <div class="text-caption mb-1">
                                      <span class="text-grey">Age:</span> <span class="font-weight-bold">{{ booking.clientAge || 'N/A' }}</span>
                                    </div>
                                    <div class="text-caption mb-1">
                                      <span class="text-grey">Mobility:</span> <span class="font-weight-bold">{{ booking.mobilityLevel || 'Standard' }}</span>
                                    </div>
                                    <div class="text-caption" v-if="booking.medicalConditions">
                                      <span class="text-grey">Medical:</span> <span class="font-weight-bold">{{ booking.medicalConditions }}</span>
                                    </div>
                                  </div>
                                </v-col>

                                <!-- Special Instructions -->
                                <v-col cols="6">
                                  <div class="pa-2 pt-2" style="border-top: 1px solid #e2e8f0;">
                                    <div class="text-caption font-weight-bold mb-2" style="color: #475569;">
                                      <v-icon size="14" color="primary" class="mr-1">mdi-note-text-outline</v-icon>
                                      Special Instructions
                                    </div>
                                    <div class="text-caption">{{ booking.specialInstructions || 'None specified' }}</div>
                                  </div>
                                </v-col>
                              </v-row>

                              <!-- Divider -->
                              <v-divider class="my-3"></v-divider>

                              <!-- Caregiver Assignment -->
                              <div>
                                <div class="d-flex justify-space-between align-center mb-1">
                                  <div class="font-weight-bold text-no-wrap" style="color: #475569; font-size: 0.55rem;">
                                    <v-icon size="10" color="primary" class="mr-1">mdi-account-heart-outline</v-icon>
                                    {{ booking.caregiver || 'Pending Assignment' }}
                                  </div>
                                  <v-chip 
                                    :color="(booking.assignedCount || 0) >= (booking.requiredCount || 1) ? 'success' : 'warning'" 
                                    size="x-small"
                                    class="font-weight-bold"
                                    style="font-size: 0.55rem;"
                                  >
                                    {{ booking.assignedCount || 0 }}/{{ booking.requiredCount || 1 }} Assigned
                                  </v-chip>
                                </div>
                                <v-progress-linear
                                  :model-value="((booking.assignedCount || 0) / (booking.requiredCount || 1)) * 100"
                                  :color="(booking.assignedCount || 0) >= (booking.requiredCount || 1) ? 'success' : 'warning'"
                                  height="3"
                                  rounded
                                ></v-progress-linear>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </v-window-item>

                    <!-- Completed Tab -->
                    <v-window-item value="completed">
                      <div v-if="completedBookings.length === 0" class="text-center pa-8">
                        <v-icon size="48" color="grey-lighten-2" class="mb-3">mdi-checkbox-marked-circle</v-icon>
                        <p class="text-grey mb-0">No completed bookings</p>
                      </div>
                      <div v-else>
                        <div v-for="booking in completedBookings.slice(0, 3)" :key="booking.id" class="contract-item pa-4">
                          <div class="mb-2">
                            <div class="d-flex align-center justify-space-between mb-2">
                              <div class="d-flex align-center flex-grow-1">
                                <v-avatar size="40" class="mr-3" color="grey">
                                  <v-icon color="white">mdi-checkbox-marked-circle</v-icon>
                                </v-avatar>
                                <div>
                                  <div class="contract-service font-weight-bold">{{ booking.service || booking.serviceType }}</div>
                                  <div class="contract-dates text-caption">{{ booking.date }}</div>
                                  <div class="text-caption text-grey">{{ booking.location }}</div>
                                </div>
                              </div>
                              <div class="text-right">
                                <div class="text-h6 grey--text font-weight-bold">${{ getBookingPrice(booking) }}</div>
                                <v-chip color="grey" size="x-small">Completed</v-chip>
                              </div>
                            </div>
                            <v-btn block variant="outlined" size="small" prepend-icon="mdi-star" @click="rateBooking(booking.id)">Rate Service</v-btn>
                          </div>
                        </div>
                      </div>
                    </v-window-item>
                  </v-window>

                  <!-- View All Button -->
                  <div v-if="allClientBookings.length > 3" class="pa-4 text-center border-t">
                    <v-btn 
                      color="primary" 
                      variant="text" 
                      size="small" 
                      @click="currentSection = 'my-bookings'"
                      append-icon="mdi-arrow-right"
                    >
                      View All {{ allClientBookings.length }} Bookings
                    </v-btn>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </div>

        <!-- Notifications Section -->
        <div v-if="currentSection === 'notifications'">
          <notification-center user-type="client" :user-id="1" @open-settings="() => {/* Add settings handler */}" @action-clicked="handleNotificationAction" />
        </div>

        <!-- Browse Caregivers Section -->
        <div v-if="currentSection === 'book'">
          <browse-caregivers @request-booking="handleBookingRequest" />
        </div>

        <!-- Book Service Form Section -->
        <div v-if="currentSection === 'book-form'">
          <!-- Header Section with Branding -->
          <div class="booking-header-section">
            <div class="booking-brand-header">
              <div class="brand-logo-section">
                <div class="brand-logo-wrapper">
                  <img src="/logo flower.png" alt="CAS Private Care Logo" class="brand-logo-image" />
                </div>
                <div class="brand-text">
                  <h1 class="brand-title">CAS Private Care</h1>
                  <p class="brand-subtitle">Professional Care Service Request</p>
                </div>
              </div>
              <div class="trust-indicators">
                <div class="trust-item">
                  <v-icon color="white" size="20">mdi-shield-check</v-icon>
                  <span>Verified Caregivers</span>
                </div>
                <div class="trust-item">
                  <v-icon color="white" size="20">mdi-star-check</v-icon>
                  <span>Quality Guaranteed</span>
                </div>
                <div class="trust-item">
                  <v-icon color="white" size="20">mdi-lock</v-icon>
                  <span>Secure & Insured</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Main Form Card -->
          <v-card elevation="0" class="professional-form-card">
            <v-card-text class="pa-0">
              <v-form ref="bookingForm">
                <!-- Service Information Section -->
                <div class="form-section">
                  <div class="section-header">
                    <div class="section-icon">
                      <v-icon color="primary" size="24">mdi-medical-bag</v-icon>
                    </div>
                    <div class="section-info">
                      <h3 class="section-title">Service Information</h3>
                      <p class="section-subtitle">Tell us about the care service you need</p>
                    </div>
                  </div>
                  <div class="section-content">
                    <v-row>
                      <v-col cols="12" md="6">
                        <div class="form-field">
                          <label class="field-label">Service Type *</label>
                          <v-select 
                            v-model="bookingData.serviceType" 
                            :items="['Caregiver']" 
                            variant="outlined" 
                            density="comfortable" 
                            :rules="[v => !!v || 'Service type is required']"
                            placeholder="Choose the type of care needed"
                            class="professional-select"
                          />
                        </div>
                      </v-col>
                      <v-col cols="12" md="6">
                        <div class="form-field">
                          <label class="field-label">Hours per Day *</label>
                          <v-select 
                            v-model="bookingData.dutyType" 
                            :items="[{title: '8 Hours per Day', value: '8 Hours per Day'}, {title: '12 Hours per Day', value: '12 Hours per Day'}, {title: '24 Hours per Day', value: '24 Hours per Day'}]" 
                            variant="outlined" 
                            density="comfortable" 
                            :rules="[v => !!v || 'Hours per day is required']"
                            placeholder="Select hours per day"
                            class="professional-select"
                          />
                        </div>
                      </v-col>
                      <v-col cols="12" md="6">
                        <div class="form-field">
                          <label class="field-label">Service Date *</label>
                          <v-text-field 
                            v-model="bookingData.date" 
                            type="date" 
                            variant="outlined" 
                            density="comfortable" 
                            :rules="[v => !!v || 'Date is required']"
                            :min="today"
                            class="professional-field"
                          />
                        </div>
                      </v-col>
                      <v-col cols="12" md="6">
                        <div class="form-field">
                          <label class="field-label">Service Duration</label>
                          <v-select 
                            v-model="bookingData.durationDays" 
                            :items="[{title: '15 Days', value: 15}, {title: '30 Days', value: 30}, {title: '60 Days', value: 60}, {title: '90 Days', value: 90}]" 
                            variant="outlined" 
                            density="comfortable"
                            placeholder="How long do you need care?"
                            class="professional-select"
                          />
                        </div>
                      </v-col>
                    </v-row>
                    
                    <!-- Day of Week Selection -->
                    <v-row class="mt-4">
                      <v-col cols="12">
                        <div class="form-field">
                          <label class="field-label mb-3 d-block">Select Days of Week *</label>
                          <div v-if="getSelectedDaysCount() < 3" class="text-error mb-2" style="font-size: 0.875rem; font-weight: 600;">
                            <v-icon size="small" color="error" class="mr-1">mdi-alert-circle</v-icon>
                            Minimum 3 days required ({{ getSelectedDaysCount() }}/3 selected)
                          </div>
                          <div v-else class="text-success mb-2" style="font-size: 0.875rem; font-weight: 500;">
                            <v-icon size="small" color="success" class="mr-1">mdi-check-circle</v-icon>
                            {{ getSelectedDaysCount() }} days selected
                          </div>
                          <div class="day-selector-container">
                            <div class="day-buttons-row">
                              <v-btn
                                v-for="(day, key) in bookingData.selectedDays"
                                :key="key"
                                :variant="day.enabled ? 'flat' : 'outlined'"
                                :color="day.enabled ? 'primary' : 'grey'"
                                size="large"
                                class="day-button"
                                @click="toggleDay(key)"
                              >
                                {{ getDayLabel(key) }}
                              </v-btn>
                            </div>
                            
                            <!-- Selected Days List -->
                            <div v-if="getSelectedDaysCount() > 0" class="selected-days-list mt-4">
                              <div 
                                v-for="(day, key) in bookingData.selectedDays" 
                                :key="key"
                                v-show="day.enabled"
                                class="selected-day-item"
                              >
                                <span class="day-name">{{ getFullDayName(key) }}</span>
                                <v-text-field
                                  v-model="day.startTime"
                                  type="time"
                                  variant="outlined"
                                  density="compact"
                                  hide-details
                                  class="time-input"
                                />
                                <span class="time-separator">-</span>
                                <v-text-field
                                  v-model="day.endTime"
                                  type="time"
                                  variant="outlined"
                                  density="compact"
                                  hide-details
                                  class="time-input"
                                  :disabled="!!bookingData.dutyType"
                                  :readonly="!!bookingData.dutyType"
                                />
                                <v-btn
                                  icon="mdi-chevron-down"
                                  variant="text"
                                  size="small"
                                  class="chevron-btn"
                                  @click="toggleDayExpanded(key)"
                                />
                              </div>
                            </div>
                            <div v-else class="text-grey text-caption mt-2">
                              Please select at least 3 days of the week
                            </div>
                          </div>
                        </div>
                      </v-col>
                    </v-row>
                  </div>
                </div>

                <!-- Location & Preferences Section -->
                <div class="form-section">
                  <div class="section-header">
                    <div class="section-icon">
                      <v-icon color="primary" size="24">mdi-map-marker</v-icon>
                    </div>
                    <div class="section-info">
                      <h3 class="section-title">Location & Preferences</h3>
                      <p class="section-subtitle">Where will the service be provided?</p>
                    </div>
                  </div>
                  <div class="section-content">
                    <v-row>
                      <v-col cols="12" md="6">
                        <div class="form-field">
                          <label class="field-label">ZIP Code *</label>
                          <v-text-field 
                            v-model="bookingData.zipcode" 
                            variant="outlined" 
                            density="comfortable" 
                            :rules="[v => !!v || 'ZIP code is required', v => /^\d{5}$/.test(v) || 'Please enter a valid 5-digit ZIP code']"
                            placeholder="Enter ZIP code"
                            class="professional-field"
                            maxlength="5"
                            @input="lookupZipCode"
                            @blur="lookupZipCode"
                          >
                            <template v-slot:prepend-inner>
                              <v-icon>mdi-map-marker</v-icon>
                            </template>
                          </v-text-field>
                          <div v-if="zipCodeLocation" class="zip-location-display">
                            {{ zipCodeLocation }}
                          </div>
                        </div>
                      </v-col>

                      <v-col cols="12" md="8">
                        <div class="form-field">
                          <label class="field-label">Street Address *</label>
                          <v-text-field 
                            v-model="bookingData.streetAddress" 
                            variant="outlined" 
                            density="comfortable" 
                            :rules="[v => !!v || 'Address is required']"
                            placeholder="Enter your street address"
                            class="professional-field"
                          />
                        </div>
                      </v-col>
                      <v-col cols="12" md="4">
                        <div class="form-field">
                          <label class="field-label">Apartment/Unit Number</label>
                          <v-text-field 
                            v-model="bookingData.apartmentUnit" 
                            variant="outlined" 
                            density="comfortable"
                            placeholder="Apt, Suite, Unit (optional)"
                            class="professional-field"
                          />
                        </div>
                      </v-col>
                    </v-row>
                  </div>
                </div>

                <!-- Care Requirements Section -->
                <div v-if="bookingData.serviceType === 'Caregiver'" class="form-section">
                  <div class="section-header">
                    <div class="section-icon">
                      <v-icon color="primary" size="24">mdi-account-heart</v-icon>
                    </div>
                    <div class="section-info">
                      <h3 class="section-title">Care Requirements</h3>
                      <p class="section-subtitle">Help us match you with the right caregiver</p>
                    </div>
                  </div>
                  <div class="section-content">
                    <v-row>

                      <v-col cols="12" md="4">
                        <div class="form-field">
                          <label class="field-label">Client Age</label>
                          <v-text-field 
                            v-model="bookingData.clientAge" 
                            type="number" 
                            variant="outlined" 
                            density="comfortable"
                            placeholder="Age of care recipient"
                            class="professional-field"
                          />
                        </div>
                      </v-col>
                      <v-col cols="12" md="4">
                        <div class="form-field">
                          <label class="field-label">Mobility Level</label>
                          <v-select 
                            v-model="bookingData.mobilityLevel" 
                            :items="[{title: 'Independent', value: 'independent'}, {title: 'Assisted', value: 'assisted'}, {title: 'Wheelchair', value: 'wheelchair'}, {title: 'Bedridden', value: 'bedridden'}]" 
                            variant="outlined" 
                            density="comfortable"
                            placeholder="Select mobility level"
                            class="professional-select"
                          />
                        </div>
                      </v-col>

                      <v-col cols="12">
                        <div class="form-field">
                          <label class="field-label">Medical Conditions</label>
                          <v-select 
                            v-model="bookingData.medicalConditions" 
                            :items="medicalConditionsList" 
                            variant="outlined" 
                            density="comfortable" 
                            multiple 
                            chips
                            placeholder="Select any medical conditions"
                            class="professional-select"
                          />
                        </div>
                      </v-col>
                      <v-col cols="12">
                        <div class="form-field">
                          <label class="field-label">Special Instructions</label>
                          <v-textarea 
                            v-model="bookingData.notes" 
                            variant="outlined" 
                            rows="4" 
                            placeholder="Please provide any additional information that would help us serve you better..."
                            class="professional-textarea"
                          />
                        </div>
                      </v-col>
                    </v-row>
                  </div>
                </div>
                
                <!-- Special Instructions for Non-Caregiver Services -->
                <div v-if="bookingData.serviceType !== 'Caregiver' && bookingData.serviceType" class="form-section">
                  <div class="section-header">
                    <div class="section-icon">
                      <v-icon color="primary" size="24">mdi-note-text</v-icon>
                    </div>
                    <div class="section-info">
                      <h3 class="section-title">Service Details</h3>
                      <p class="section-subtitle">Tell us about your specific requirements</p>
                    </div>
                  </div>
                  <div class="section-content">
                    <v-row>
                      <v-col cols="12">
                        <div class="form-field">
                          <label class="field-label">Special Instructions</label>
                          <v-textarea 
                            v-model="bookingData.notes" 
                            variant="outlined" 
                            rows="4" 
                            placeholder="Please provide any specific requirements or instructions..."
                            class="professional-textarea"
                          />
                        </div>
                      </v-col>
                    </v-row>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                  <!-- Price Breakdown -->
                  <div v-if="bookingData.serviceType && bookingData.dutyType && bookingData.durationDays" class="price-breakdown mb-4">
                    <v-card class="breakdown-card" elevation="2">
                      <v-card-text class="pa-4">
                        <div class="breakdown-header mb-3">
                          <h4 class="breakdown-title">Service Summary</h4>
                        </div>
                        <div class="breakdown-items">
                          <div class="breakdown-item">
                            <span class="item-label">Service Type</span>
                            <span class="item-value">{{ bookingData.serviceType }}</span>
                          </div>
                          <div class="breakdown-item">
                            <span class="item-label">Hours per Day</span>
                            <span class="item-value">{{ bookingData.dutyType.split(' ')[0] }} hours</span>
                          </div>
                          <div class="breakdown-item">
                            <span class="item-label">Duration</span>
                            <span class="item-value">{{ bookingData.durationDays }} days</span>
                          </div>
                          <div class="breakdown-item">
                            <span class="item-label">Rate per Hour</span>
                            <div class="item-value-container">
                              <span v-if="referralDiscount > 0" class="original-price">${{ getOriginalRate(bookingData.serviceType) }}</span>
                              <span class="item-value" :class="{ 'discounted-price': referralDiscount > 0 }">{{ getHourlyRate(bookingData.serviceType) }}</span>
                              <v-chip v-if="referralDiscount > 0" color="success" size="x-small" class="discount-chip">-${{ referralDiscount }}/hr</v-chip>
                            </div>
                          </div>
                          <v-divider class="my-2"></v-divider>
                          <div class="breakdown-item total-item">
                            <span class="item-label total-label">Order Total</span>
                            <div class="item-value-container">
                              <span v-if="referralDiscount > 0" class="original-total">${{ getOriginalTotal() }}</span>
                              <span class="item-value total-value" :class="{ 'discounted-price': referralDiscount > 0 }">{{ getTotalCost() }}</span>
                              <v-chip v-if="referralDiscount > 0" color="success" size="small" class="total-savings-chip">Save ${{ getTotalSavings() }}</v-chip>
                            </div>
                          </div>
                        </div>
                      </v-card-text>
                    </v-card>
                  </div>
                  
                  <!-- Referral Code Section -->
                  <div class="referral-section mb-4">
                    <v-card class="referral-card" elevation="2">
                      <v-card-text class="pa-4">
                        <div class="referral-header mb-3">
                          <span class="referral-title"></span>
                        </div>
                        <div class="d-flex gap-3">
                          <v-text-field
                            v-model="bookingData.referralCode"
                            variant="outlined"
                            density="comfortable"
                            placeholder="Enter referral code for discount"
                            class="referral-field flex-grow-1"
                            :error="!!referralCodeError"
                          />
                          <v-btn
                            color="primary"
                            variant="outlined"
                            size="large"
                            class="apply-btn"
                            @click="applyReferralCode"
                            :disabled="!bookingData.referralCode"
                          >
                            Apply
                          </v-btn>
                        </div>
                        <!-- Error Message -->
                        <v-alert
                          v-if="referralCodeError"
                          type="error"
                          density="compact"
                          class="mt-3"
                          closable
                          @click:close="referralCodeError = ''"
                        >
                          {{ referralCodeError }}
                        </v-alert>
                      </v-card-text>
                    </v-card>
                  </div>
                  
                  <div class="action-buttons">
                    <v-btn 
                      variant="flat" 
                      size="x-large" 
                      class="demo-fill-btn" 
                      @click="fillDemoData"
                    >
                      <v-icon start>mdi-auto-fix</v-icon>
                      Demo Fill
                    </v-btn>
                    <v-btn 
                      variant="outlined" 
                      size="x-large" 
                      class="cancel-btn" 
                      @click="currentSection = 'dashboard'"
                    >
                      <v-icon start>mdi-arrow-left</v-icon>
                      Cancel
                    </v-btn>
                    <v-btn 
                      variant="flat"
                      size="x-large" 
                      class="submit-btn" 
                      @click="submitBooking"
                    >
                      <v-icon start>mdi-check</v-icon>
                      Submit Request
                    </v-btn>
                  </div>
                  <div class="security-notice">
                    <v-icon color="success" size="16">mdi-shield-check</v-icon>
                    <span>Your information is secure and protected</span>
                  </div>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </div>

        <!-- My Bookings Section -->
        <div v-if="currentSection === 'my-bookings'">
          <div class="bookings-header mb-6">
            <h1 class="bookings-title">My Bookings</h1>
            <p class="bookings-subtitle">Review and manage your care service requests</p>
          </div>

          <v-tabs v-model="bookingTab" color="primary" class="mb-6 booking-tabs" bg-color="transparent" show-arrows density="comfortable">
            <v-tab value="pending" class="booking-tab">
              <v-icon start size="small">mdi-clock-outline</v-icon>
              <span class="tab-text">Pending Review</span>
            </v-tab>
            <v-tab value="approved" class="booking-tab">
              <v-icon start size="small">mdi-check-circle</v-icon>
              <span class="tab-text">Approved</span>
            </v-tab>
            <v-tab value="completed" class="booking-tab">
              <v-icon start size="small">mdi-calendar-check</v-icon>
              <span class="tab-text">Completed</span>
            </v-tab>
          </v-tabs>

          <v-window v-model="bookingTab">
            <v-window-item value="pending">
              <v-row>
                <v-col v-for="booking in pendingBookings" :key="booking.id" cols="12">
                  <v-card class="booking-request-card" elevation="2">
                    <v-card-text class="pa-6">
                      <div class="d-flex justify-between align-center mb-4">
                        <div>
                          <h3 class="booking-service-title">{{ booking.service }}</h3>
                          <p class="booking-id text-grey">Request ID: #{{ booking.id }}</p>
                        </div>
                        <v-chip color="warning" size="large">
                          <v-icon start>mdi-clock-outline</v-icon>
                          Pending Review
                        </v-chip>
                      </div>
                      
                      <v-row>
                        <v-col cols="12" md="5">
                          <div class="booking-details">
                            <div class="detail-row">
                              <v-icon size="18" color="primary">mdi-calendar</v-icon>
                              <span class="detail-text">{{ booking.date }}</span>
                            </div>
                            <div class="detail-row" v-if="booking.time && booking.time !== 'N/A'">
                              <v-icon size="18" color="primary">mdi-clock-start</v-icon>
                              <span class="detail-text">Starts at {{ booking.time }}</span>
                            </div>
                            <div class="detail-row">
                              <v-icon size="18" color="primary">mdi-map-marker</v-icon>
                              <span class="detail-text">{{ booking.location }}</span>
                            </div>
                            <div class="detail-row">
                              <v-icon size="18" color="primary">mdi-account-clock</v-icon>
                              <span class="detail-text">{{ booking.dutyType }}</span>
                            </div>
                            <div class="detail-row mt-3">
                              <v-chip color="warning" size="small" variant="flat">
                                <span v-if="getOriginalBookingPrice(booking)" class="original-price-chip">${{ getOriginalBookingPrice(booking) }}</span>
                                ${{ getBookingPrice(booking) }}
                              </v-chip>
                            </div>
                          </div>
                        </v-col>
                        <v-col cols="12" md="7">
                          <div class="booking-actions">
                            <v-btn color="info" variant="outlined" size="small" class="mr-2 mb-2" @click="viewBookingDetails(booking)">
                              <v-icon start>mdi-eye</v-icon>
                              View Details
                            </v-btn>
                            <v-btn color="primary" variant="outlined" size="small" class="mr-2 mb-2" @click="editBooking(booking.id)">
                              <v-icon start>mdi-pencil</v-icon>
                              Edit Request
                            </v-btn>
                            <v-btn color="error" variant="outlined" size="small" class="mb-2" @click="cancelBooking(booking.id)">
                              <v-icon start>mdi-cancel</v-icon>
                              Cancel
                            </v-btn>
                          </div>
                          
                          <!-- Pending Status Info -->
                          <v-alert type="info" density="compact" class="mt-3" variant="tonal">
                            <div class="d-flex align-center">
                              <v-icon start>mdi-information</v-icon>
                              <span class="text-caption">Your booking is under review by our admin team. We'll notify you once it's approved.</span>
                            </div>
                          </v-alert>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
              <v-alert v-if="pendingBookings.length === 0 && !loadingBookings" type="info" class="mt-4">
                <v-icon start>mdi-information</v-icon>
                No pending requests. <a href="#" @click.prevent="attemptBooking" class="text-primary">Submit a new request</a>
              </v-alert>
              <div v-if="loadingBookings" class="text-center pa-8">
                <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
                <p class="mt-4 text-grey">Loading bookings...</p>
              </div>
            </v-window-item>

            <v-window-item value="approved">
              <v-row>
                <v-col v-for="booking in confirmedBookings" :key="booking.id" cols="12">
                  <v-card class="booking-request-card" elevation="2">
                    <v-card-text class="pa-6">
                      <div class="d-flex justify-between align-center mb-4">
                        <div>
                          <h3 class="booking-service-title">{{ booking.service }}</h3>
                          <p class="booking-id text-grey">Booking ID: #{{ booking.id }}</p>
                        </div>
                        <v-chip color="success" size="large">
                          <v-icon start>mdi-check</v-icon>
                          Approved
                        </v-chip>
                      </div>
                      
                      <v-row>
                        <v-col cols="12" md="4">
                          <div class="booking-details">
                            <div class="detail-row">
                              <v-icon size="18" color="primary">mdi-account</v-icon>
                              <span class="detail-text">{{ booking.caregiver }}</span>
                            </div>
                            <div class="detail-row">
                              <v-icon size="18" color="primary">mdi-calendar</v-icon>
                              <span class="detail-text">{{ booking.date }}</span>
                            </div>
                            <div class="detail-row" v-if="booking.time && booking.time !== 'N/A'">
                              <v-icon size="18" color="primary">mdi-clock-start</v-icon>
                              <span class="detail-text">Starts at {{ booking.time }}</span>
                            </div>
                            <div class="detail-row">
                              <v-icon size="18" color="primary">mdi-map-marker</v-icon>
                              <span class="detail-text">{{ booking.location }}</span>
                            </div>
                            <div class="detail-row">
                              <v-icon size="18" color="primary">mdi-clock-outline</v-icon>
                              <span class="detail-text">{{ booking.durationDays || 15 }} days</span>
                            </div>
                          </div>
                        </v-col>
                        
                        <!-- Pricing Summary Card -->
                        <v-col cols="12" md="3">
                          <div class="pricing-summary-card pa-3 rounded-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <div class="text-caption mb-2 opacity-90">Total Amount</div>
                            <div class="text-h5 font-weight-bold mb-2">${{ getBookingPrice(booking) }}</div>
                            <v-divider class="my-2" style="border-color: rgba(255,255,255,0.3);"></v-divider>
                            <div class="d-flex justify-space-between text-caption opacity-90">
                              <span>Rate/Hour:</span>
                              <span>${{ booking.hourlyRate || 45 }}</span>
                            </div>
                            <div class="d-flex justify-space-between text-caption opacity-90">
                              <span>Hours/Day:</span>
                              <span>{{ extractHoursFromDuty(booking.dutyType || booking.duty_type) }}</span>
                            </div>
                            <div v-if="booking.hasDiscount" class="mt-2">
                              <v-chip color="success" size="x-small" class="mt-1">
                                <v-icon start size="x-small">mdi-tag-check</v-icon>
                                Discount Applied
                              </v-chip>
                            </div>
                          </div>
                        </v-col>
                        
                        <v-col cols="12" md="2">
                          <!-- Caregiver Assignment Progress -->
                          <div class="assignment-progress-card pa-3 rounded-lg" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="d-flex align-center mb-2">
                              <v-icon size="18" color="info" class="mr-2">mdi-account-group</v-icon>
                              <span class="text-caption font-weight-medium">Assignment</span>
                            </div>
                            <div class="d-flex align-center justify-between mb-2">
                              <span class="text-body-2">{{ booking.assignedCount || 0 }} / {{ booking.requiredCount || 1 }}</span>
                              <v-chip 
                                :color="(booking.assignedCount || 0) >= (booking.requiredCount || 1) ? 'success' : 'warning'" 
                                size="x-small"
                                variant="flat"
                              >
                                {{ (booking.assignedCount || 0) >= (booking.requiredCount || 1) ? 'Complete' : 'In Progress' }}
                              </v-chip>
                            </div>
                            <v-progress-linear
                              :model-value="((booking.assignedCount || 0) / (booking.requiredCount || 1)) * 100"
                              :color="(booking.assignedCount || 0) >= (booking.requiredCount || 1) ? 'success' : 'info'"
                              height="8"
                              rounded
                            ></v-progress-linear>
                          </div>
                        </v-col>
                        
                        <v-col cols="12" md="3">
                          <div class="d-flex flex-column gap-2">
                            <v-btn color="info" variant="outlined" size="small" @click="viewBookingDetails(booking)" block>
                              <v-icon start>mdi-eye</v-icon>
                              View Details
                            </v-btn>
                            <v-btn color="primary" variant="outlined" size="small" @click="openContactDialog(booking)" block>
                              <v-icon start>mdi-phone</v-icon>
                              Contact Caregiver
                            </v-btn>
                            <v-btn color="success" variant="outlined" size="small" @click="openAdminContactDialog()" block>
                              <v-icon start>mdi-account-tie</v-icon>
                              Contact Admin
                            </v-btn>
                          </div>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
              <v-alert v-if="confirmedBookings.length === 0 && !loadingBookings" type="info" class="mt-4">
                <v-icon start>mdi-information</v-icon>
                No approved bookings
              </v-alert>
              <div v-if="loadingBookings" class="text-center pa-8">
                <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
                <p class="mt-4 text-grey">Loading bookings...</p>
              </div>
            </v-window-item>

            <v-window-item value="completed">
              <v-row>
                <v-col v-for="booking in completedBookings" :key="booking.id" cols="12">
                  <v-card class="booking-request-card" elevation="2">
                    <v-card-text class="pa-6">
                      <div class="d-flex justify-between align-center mb-4">
                        <div>
                          <h3 class="booking-service-title">{{ booking.service }}</h3>
                          <p class="booking-id text-grey">Completed on {{ booking.date }}</p>
                        </div>
                        <v-chip color="success" size="large">
                          <v-icon start>mdi-check-circle</v-icon>
                          Completed
                        </v-chip>
                      </div>
                      
                      <div class="booking-details mb-4">
                        <div class="detail-row">
                          <v-icon size="18" color="primary">mdi-account</v-icon>
                          <span class="detail-text">{{ booking.caregiver }}</span>
                        </div>
                      </div>
                      
                      <div class="d-flex gap-2 flex-wrap">
                        <v-btn color="info" variant="outlined" size="small" @click="viewBookingDetails(booking)">
                          <v-icon start>mdi-eye</v-icon>
                          View Details
                        </v-btn>
                        <v-btn color="success" variant="flat" size="small" @click="downloadReceipt(booking.id)">
                          <v-icon start>mdi-receipt</v-icon>
                          Download Receipt
                        </v-btn>
                        <v-btn color="primary" variant="outlined" size="small" @click="rateBooking(booking.id)">
                          <v-icon start>mdi-star</v-icon>
                          Rate Service
                        </v-btn>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
              <v-alert v-if="completedBookings.length === 0 && !loadingBookings" type="info" class="mt-4">
                <v-icon start>mdi-information</v-icon>
                No completed bookings
              </v-alert>
              <div v-if="loadingBookings" class="text-center pa-8">
                <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
                <p class="mt-4 text-grey">Loading bookings...</p>
              </div>
            </v-window-item>
          </v-window>
        </div>

        <!-- Payment Information Section -->
        <div v-if="currentSection === 'payment'">
          <v-row>
            <!-- Payment History -->
            <v-col cols="12" md="8">
              <v-card elevation="0" class="mb-6">
                <v-card-title class="card-header pa-8">
                  <span class="section-title primary--text">Payment History</span>
                </v-card-title>
                <v-card-text class="pa-8">
                  <v-data-table
                    :headers="[
                      { title: 'Booking ID', key: 'id', width: '100px' },
                      { title: 'Date', key: 'date', width: '140px' },
                      { title: 'Service', key: 'service', width: '150px' },
                      { title: 'Amount', key: 'amount', width: '120px', align: 'end' },
                      { title: 'Status', key: 'status', width: '120px', align: 'center' },
                      { title: 'Receipt', key: 'receipt', width: '100px', align: 'center' }
                    ]"
                    :items="getPaymentHistoryItems()"
                    :items-per-page="10"
                    class="elevation-0"
                  >
                    <template v-slot:item.amount="{ item }">
                      <span class="font-weight-bold">${{ Math.round(item.amount).toLocaleString() }}</span>
                    </template>

                    <template v-slot:item.status="{ item }">
                      <v-chip 
                        v-if="item.paymentStatus === 'paid'" 
                        color="success" 
                        size="small" 
                        prepend-icon="mdi-check-circle"
                      >
                        Paid
                      </v-chip>
                      <v-chip 
                        v-else 
                        color="warning" 
                        size="small" 
                        prepend-icon="mdi-clock-outline"
                      >
                        Pending
                      </v-chip>
                    </template>

                    <template v-slot:item.receipt="{ item }">
                      <v-btn
                        v-if="item.paymentStatus === 'paid'"
                        color="primary"
                        size="small"
                        variant="text"
                        icon="mdi-download"
                        @click="downloadReceipt(item.id)"
                      />
                      <span v-else class="text-grey">—</span>
                    </template>

                    <template v-slot:no-data>
                      <div class="text-center py-8">
                        <v-icon size="48" color="grey" class="mb-4">mdi-receipt-text-outline</v-icon>
                        <div class="text-h6 text-grey">No payment history yet</div>
                        <div class="text-body-2 text-grey mt-2">Your completed payments will appear here</div>
                      </div>
                    </template>
                  </v-data-table>
                </v-card-text>
              </v-card>

              <v-card elevation="0">
                <v-card-title class="card-header pa-8">
                  <span class="section-title primary--text">Payment Information</span>
                </v-card-title>
                <v-card-text class="pa-8">
                  <v-alert type="info" variant="tonal" class="mb-4">
                    <div class="d-flex align-start">
                      <v-icon start>mdi-information</v-icon>
                      <div>
                        <div class="font-weight-bold mb-2">Secure Payment Processing</div>
                        <ul style="margin-left: 20px; line-height: 1.8;">
                          <li>All payments are processed securely through Stripe</li>
                          <li>Card information is never stored on our servers</li>
                          <li>You'll enter payment details when booking is approved</li>
                          <li>Receipts are automatically generated after payment</li>
                        </ul>
                      </div>
                    </div>
                  </v-alert>

                  <div class="d-flex align-center pa-4 rounded" style="background: rgba(var(--v-theme-primary), 0.05); border-left: 4px solid rgb(var(--v-theme-primary));">
                    <v-icon color="primary" size="40" class="mr-4">mdi-shield-check</v-icon>
                    <div>
                      <div class="font-weight-bold">PCI-DSS Compliant</div>
                      <div class="text-body-2 text-grey">Your payment information is protected with industry-standard encryption</div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>

            <!-- Payment Summary -->
            <v-col cols="12" md="4">
              <v-card elevation="0" class="mb-6">
                <v-card-title class="card-header pa-8">
                  <span class="section-title primary--text">Payment Summary</span>
                </v-card-title>
                <v-card-text class="pa-8">
                  <div class="summary-item">
                    <span class="summary-label">Total Spent</span>
                    <span class="summary-value primary--text">{{ stats[3]?.value || '$0' }}</span>
                  </div>
                  <div class="summary-item">
                    <span class="summary-label">This Month</span>
                    <span class="summary-value">${{ formatPrice(analyticsData?.thisMonth || 0) }}</span>
                  </div>
                  <div class="summary-item">
                    <span class="summary-label">Amount Due</span>
                    <span class="summary-value" :class="stats[0]?.color ? `${stats[0].color}--text` : 'success--text'">
                      {{ stats[0]?.value || '$0' }}
                    </span>
                  </div>
                  <v-divider class="my-4" />
                  <div class="summary-item">
                    <span class="summary-label">Paid Bookings</span>
                    <span class="summary-value font-weight-bold">{{ getPaidBookingsCount() }}</span>
                  </div>
                  <div class="summary-item">
                    <span class="summary-label">Pending Payments</span>
                    <span class="summary-value font-weight-bold">{{ getPendingPaymentsCount() }}</span>
                  </div>
                </v-card-text>
              </v-card>

              <v-card elevation="0">
                <v-card-title class="card-header pa-8">
                  <span class="section-title primary--text">Quick Actions</span>
                </v-card-title>
                <v-card-text class="pa-8">
                  <v-btn
                    color="primary"
                    block
                    size="large"
                    prepend-icon="mdi-home"
                    @click="currentSection = 'dashboard'"
                    class="mb-3"
                  >
                    Back to Dashboard
                  </v-btn>
                  <v-btn
                    color="success"
                    variant="outlined"
                    block
                    size="large"
                    prepend-icon="mdi-calendar-plus"
                    @click="currentSection = 'book-form'"
                  >
                    Book New Service
                  </v-btn>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </div>

<!-- Analytics Section -->
        <div v-if="currentSection === 'analytics'">
          <v-card elevation="0" class="mb-6">
            <v-card-text class="pa-8">
              <div class="d-flex justify-between align-center mb-4">
                <div class="transaction-stats" style="flex: 1;">
                  <div class="stat-item">
                    <div class="stat-amount primary--text">${{ analyticsData.totalSpent.toLocaleString() }}</div>
                    <div class="stat-label-text">Total Spent</div>
                  </div>
                  <v-divider vertical />
                  <div class="stat-item">
                    <div class="stat-amount info--text">${{ analyticsData.thisMonth.toLocaleString() }}</div>
                    <div class="stat-label-text">This Month</div>
                  </div>
                  <v-divider vertical />
                  <div class="stat-item">
                    <div class="stat-amount primary--text">${{ analyticsData.avgPerMonth.toLocaleString() }}</div>
                    <div class="stat-label-text">Avg per Month</div>
                  </div>
                </div>
                <v-btn color="primary" variant="flat" @click="exportAnalyticsPdf" :loading="exportingAnalytics">
                  <v-icon start>mdi-file-pdf-box</v-icon>
                  Export to PDF
                </v-btn>
              </div>
            </v-card-text>
          </v-card>

          <v-row>
            <v-col cols="12" md="9">
              <v-card elevation="0" class="mb-6">
                <v-card-title class="card-header pa-8 d-flex justify-between align-center">
                  <span class="section-title primary--text">Spending Over Time</span>
                  <v-btn-toggle v-model="spendingChartPeriod" mandatory color="primary" size="small">
                    <v-btn value="week">Week</v-btn>
                    <v-btn value="month">Month</v-btn>
                    <v-btn value="year">Year</v-btn>
                  </v-btn-toggle>
                  <v-select
                    v-model="selectedYear"
                    :items="availableYears"
                    variant="outlined"
                    density="compact"
                    style="width: 100px; margin-left: 16px;"
                    hide-details
                  />
                </v-card-title>
                <v-card-text class="pa-8">
                  <div style="height: 300px; position: relative;">
                    <canvas ref="spendingChart"></canvas>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="3">
              <v-card elevation="0">
                <v-card-title class="card-header pa-8">
                  <span class="section-title primary--text">Quick Stats</span>
                </v-card-title>
                <v-card-text class="pa-8">
                  <div class="quick-stat-item">
                    <v-icon color="primary" class="mr-3">mdi-calendar-check</v-icon>
                    <div>
                      <div class="quick-stat-value">{{ analyticsData.totalBookings }}</div>
                      <div class="quick-stat-label">Total Bookings</div>
                    </div>
                  </div>
                  <v-divider class="my-4" />
                  <div class="quick-stat-item">
                    <v-icon color="info" class="mr-3">mdi-clock-outline</v-icon>
                    <div>
                      <div class="quick-stat-value">{{ analyticsData.totalHours }} hrs</div>
                      <div class="quick-stat-label">Total Hours</div>
                    </div>
                  </div>
                  <v-divider class="my-4" />
                  <div class="quick-stat-item">
                    <v-icon color="warning" class="mr-3">mdi-account-multiple</v-icon>
                    <div>
                      <div class="quick-stat-value">{{ analyticsData.activeCaregivers }}</div>
                      <div class="quick-stat-label">Active Caregivers</div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </div>

        <!-- Payment Section -->
        <div v-if="currentSection === 'payment' && selectedBooking">
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-8 d-flex align-center">
              <v-btn icon="mdi-arrow-left" variant="text" @click="currentSection = 'dashboard'" class="mr-3"></v-btn>
              <span class="section-title primary--text">Complete Payment</span>
            </v-card-title>
            <v-card-text class="pa-8">
              <!-- Booking Summary -->
              <v-card class="mb-6" elevation="1">
                <v-card-title class="bg-primary text-white">
                  <v-icon start>mdi-file-document-outline</v-icon>
                  Booking Summary
                </v-card-title>
                <v-card-text class="pa-6">
                  <v-row>
                    <v-col cols="12" md="6">
                      <div class="mb-4">
                        <div class="text-caption text-grey mb-1">Service Type</div>
                        <div class="font-weight-bold">{{ selectedBooking.service || selectedBooking.serviceType }}</div>
                      </div>
                      <div class="mb-4">
                        <div class="text-caption text-grey mb-1">Service Date</div>
                        <div class="font-weight-bold">{{ selectedBooking.date }}</div>
                      </div>
                      <div class="mb-4">
                        <div class="text-caption text-grey mb-1">Location</div>
                        <div class="font-weight-bold">{{ selectedBooking.location }}</div>
                      </div>
                    </v-col>
                    <v-col cols="12" md="6">
                      <div class="mb-4">
                        <div class="text-caption text-grey mb-1">Duration</div>
                        <div class="font-weight-bold">{{ selectedBooking.duration_days }} days</div>
                      </div>
                      <div class="mb-4">
                        <div class="text-caption text-grey mb-1">Hours per Day</div>
                        <div class="font-weight-bold">{{ selectedBooking.hours_per_day }} hours</div>
                      </div>
                      <div class="mb-4">
                        <div class="text-caption text-grey mb-1">Status</div>
                        <v-chip color="success" size="small">Approved</v-chip>
                      </div>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>

              <!-- Price Breakdown -->
              <v-card class="mb-6" elevation="1">
                <v-card-title class="bg-primary text-white">
                  <v-icon start>mdi-cash-multiple</v-icon>
                  Price Breakdown
                </v-card-title>
                <v-card-text class="pa-6">
                  <div class="d-flex justify-space-between mb-3">
                    <span>Base Rate ({{ selectedBooking.duration_days }} days × {{ selectedBooking.hours_per_day }} hrs × ${{ selectedBooking.hourly_rate }}/hr)</span>
                    <span class="font-weight-bold">
                      <span v-if="getOriginalBookingPrice(selectedBooking)" class="original-price-inline">${{ getOriginalBookingPrice(selectedBooking) }}</span>
                      ${{ getBookingPrice(selectedBooking) }}
                    </span>
                  </div>
                  <div class="d-flex justify-space-between mb-3">
                    <span>Service Fee</span>
                    <span class="font-weight-bold">$0.00</span>
                  </div>
                  <v-divider class="my-4"></v-divider>
                  <div class="d-flex justify-space-between">
                    <span class="text-h6 font-weight-bold">Total Amount</span>
                    <span class="text-h5 primary--text font-weight-bold">
                      <span v-if="getOriginalBookingPrice(selectedBooking)" class="original-price-total">${{ getOriginalBookingPrice(selectedBooking) }}</span>
                      ${{ getBookingPrice(selectedBooking) }}
                    </span>
                  </div>
                </v-card-text>
              </v-card>

              <!-- Payment Form (Prototype) -->
              <v-card elevation="1">
                <v-card-title class="bg-primary text-white">
                  <v-icon start>mdi-credit-card</v-icon>
                  Payment Information
                </v-card-title>
                <v-card-text class="pa-6">
                  <v-alert type="info" variant="tonal" class="mb-6">
                    <div class="d-flex align-center">
                      <v-icon start>mdi-information</v-icon>
                      <div>
                        <strong>Prototype Mode:</strong> This is a demo payment interface. Stripe integration will be added in production.
                      </div>
                    </div>
                  </v-alert>

                  <v-text-field
                    label="Card Number"
                    placeholder="1234 5678 9012 3456"
                    prepend-inner-icon="mdi-credit-card"
                    variant="outlined"
                    class="mb-4"
                    hint="Enter 16-digit card number"
                  ></v-text-field>

                  <v-row>
                    <v-col cols="12" md="6">
                      <v-text-field
                        label="Expiry Date"
                        placeholder="MM/YY"
                        prepend-inner-icon="mdi-calendar"
                        variant="outlined"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-text-field
                        label="CVC"
                        placeholder="123"
                        prepend-inner-icon="mdi-lock"
                        variant="outlined"
                      ></v-text-field>
                    </v-col>
                  </v-row>

                  <v-text-field
                    label="ZIP Code"
                    placeholder="10001"
                    prepend-inner-icon="mdi-map-marker"
                    variant="outlined"
                    class="mb-4"
                  ></v-text-field>

                  <v-divider class="my-6"></v-divider>

                  <div class="d-flex gap-3">
                    <v-btn
                      color="grey"
                      variant="outlined"
                      size="large"
                      @click="currentSection = 'dashboard'"
                      class="flex-grow-1"
                    >
                      Cancel
                    </v-btn>
                    <v-btn
                      color="primary"
                      size="large"
                      prepend-icon="mdi-lock-check"
                      @click="processPayment"
                      class="flex-grow-1"
                    >
                      Process Payment - ${{ getBookingPrice(selectedBooking) }}
                    </v-btn>
                  </div>
                </v-card-text>
              </v-card>
            </v-card-text>
          </v-card>
        </div>

        <!-- Profile Section -->
        <div v-if="currentSection === 'profile'">
          <v-row>
            <v-col cols="12" md="8">
              <v-card elevation="0" class="mb-6">
                <v-card-title class="card-header pa-8">
                  <span class="section-title primary--text">Personal Information</span>
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
                          <v-tooltip :text="userData?.email_verified_at ? 'Email Verified' : 'Email Not Verified'" location="top">
                            <template v-slot:activator="{ props }">
                              <v-icon 
                                v-bind="props"
                                :color="userData?.email_verified_at ? 'success' : 'error'"
                                size="20"
                              >
                                {{ userData?.email_verified_at ? 'mdi-check-circle' : 'mdi-close-circle' }}
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
                      <v-text-field v-model="profileData.birthdate" label="Birthdate" variant="outlined" type="date" />
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-text-field :model-value="age" label="Age" variant="outlined" readonly />
                    </v-col>
                    <v-col cols="12">
                      <v-text-field v-model="profileData.address" label="Address" variant="outlined" />
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-select v-model="profileData.county" :items="counties" label="County/Borough" variant="outlined" @update:model-value="onProfileCountyChange" />
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-select v-model="profileData.city" :items="profileAvailableCities" label="City" variant="outlined" :disabled="!profileData.county" />
                    </v-col>
                    <v-col cols="12" md="3">
                      <v-text-field :model-value="'New York'" label="State" variant="outlined" readonly />
                    </v-col>
                    <v-col cols="12" md="3">
                      <v-text-field 
                        v-model="profileData.zip" 
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
                      <div v-if="profileZipLocation" class="zip-location-display mt-1">
                        {{ profileZipLocation }}
                      </div>
                    </v-col>
                  </v-row>
                  <v-btn color="primary" class="mt-4" size="large" @click="saveProfile">Save Changes</v-btn>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="4">
              <v-card elevation="0" class="mb-6">
                <v-card-text class="pa-8 text-center">
                  <div class="position-relative d-inline-block mb-4">
                    <v-avatar size="120" color="primary">
                      <img v-if="userAvatar && userAvatar.length > 0" :src="userAvatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" @error="userAvatar = ''" />
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

              <v-card elevation="0">
                <v-card-title class="card-header pa-8">
                  <span class="section-title primary--text">Change Password</span>
                </v-card-title>
                <v-card-text class="pa-8">
                  <v-text-field label="Current Password" variant="outlined" :type="showCurrentPassword ? 'text' : 'password'" :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showCurrentPassword = !showCurrentPassword" class="mb-4" />
                  <v-text-field label="New Password" variant="outlined" :type="showNewPassword ? 'text' : 'password'" :append-inner-icon="showNewPassword ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showNewPassword = !showNewPassword" hint="8 minimum characters" persistent-hint class="mb-4" />
                  <v-text-field label="Confirm New Password" variant="outlined" :type="showConfirmPassword ? 'text' : 'password'" :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showConfirmPassword = !showConfirmPassword" class="mb-4" />
                  <v-btn color="primary" block size="large">Change Password</v-btn>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </div>

    <!-- Add Payment Method Dialog -->
    <v-dialog v-model="addPaymentDialog" max-width="500" :fullscreen="$vuetify.display.xs">
      <v-card>
        <v-card-title class="pa-6" style="background: #2563eb; color: white;">
          <div class="d-flex align-center justify-between w-100">
            <span class="section-title" style="color: white;">Add Payment Method</span>
            <v-btn v-if="$vuetify.display.xs" icon variant="text" color="white" @click="addPaymentDialog = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12">
              <v-text-field label="Card Number" variant="outlined" placeholder="1234 5678 9012 3456" />
            </v-col>
            <v-col cols="12">
              <v-text-field label="Card Holder Name" variant="outlined" placeholder="John Doe" />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field label="Expiry Date" variant="outlined" placeholder="MM/YY" />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field label="CVV" variant="outlined" placeholder="123" type="password" />
            </v-col>
            <v-col cols="12">
              <v-checkbox label="Set as default payment method" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="addPaymentDialog = false">Cancel</v-btn>
          <v-btn color="primary" @click="addPaymentDialog = false">Add Card</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Contact Caregiver Dialog -->
    <v-dialog v-model="contactCaregiverDialog" max-width="500" :fullscreen="$vuetify.display.xs">
      <v-card class="caregiver-contact-dialog">
        <v-card-title class="pa-4 pa-sm-6 caregiver-contact-header" style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white;">
          <div class="d-flex align-center justify-between w-100">
            <div class="d-flex align-center">
              <v-avatar color="white" size="40" class="mr-3 d-none d-sm-flex">
                <v-icon color="primary">mdi-account-circle</v-icon>
              </v-avatar>
              <div>
                <span class="section-title" style="color: white; font-size: 1.1rem;">Assigned Caregivers</span>
                <p class="text-caption ma-0" style="color: rgba(255,255,255,0.8);">{{ selectedBooking?.assignedCaregivers?.length || 0 }} caregiver(s) assigned</p>
              </div>
            </div>
            <v-btn icon variant="text" color="white" size="small" class="d-sm-none" @click="contactCaregiverDialog = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-4 pa-sm-6">
          <!-- No Caregivers Assigned Message -->
          <div v-if="!selectedBooking?.assignedCaregivers || selectedBooking?.assignedCaregivers?.length === 0" class="text-center py-6">
            <v-icon size="64" color="grey-lighten-2" class="mb-3">mdi-account-clock</v-icon>
            <h3 class="text-h6 text-grey-darken-1 mb-2">No Caregiver Assigned Yet</h3>
            <p class="text-body-2 text-grey">A caregiver will be assigned to your booking soon. You'll be notified once assigned.</p>
            <v-btn color="success" variant="outlined" class="mt-4" @click="contactCaregiverDialog = false; openAdminContactDialog()">
              <v-icon start>mdi-account-tie</v-icon>
              Contact Admin Instead
            </v-btn>
          </div>
          
          <!-- Assigned Caregivers List -->
          <div v-else>
            <v-list class="contact-list pa-0">
              <template v-for="(cg, index) in selectedBooking?.assignedCaregivers" :key="cg.id || index">
                <div class="caregiver-contact-item mb-4 pa-4 rounded-lg" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                  <!-- Caregiver Name -->
                  <div class="d-flex align-center mb-3">
                    <v-avatar color="primary" size="48" class="mr-3">
                      <v-icon color="white" v-if="!cg.avatar">mdi-account</v-icon>
                      <v-img v-else :src="cg.avatar" />
                    </v-avatar>
                    <div>
                      <h4 class="font-weight-bold text-body-1">{{ cg.name }}</h4>
                      <p class="text-caption text-grey ma-0">{{ index === 0 ? 'Primary Caregiver' : 'Supporting Caregiver' }}</p>
                    </div>
                  </div>
                  
                  <!-- Contact Options -->
                  <div class="d-flex flex-column gap-2">
                    <!-- Phone -->
                    <v-list-item 
                      v-if="cg.phone" 
                      class="px-0 py-1" 
                      :href="'tel:' + cg.phone.replace(/[^0-9]/g, '')"
                      rounded
                    >
                      <template v-slot:prepend>
                        <v-avatar color="success" variant="tonal" size="36">
                          <v-icon size="18">mdi-phone</v-icon>
                        </v-avatar>
                      </template>
                      <v-list-item-title class="text-body-2">{{ cg.phone || 'No phone available' }}</v-list-item-title>
                      <template v-slot:append>
                        <v-icon color="success" size="18">mdi-phone-outgoing</v-icon>
                      </template>
                    </v-list-item>
                    
                    <!-- Email -->
                    <v-list-item 
                      v-if="cg.email" 
                      class="px-0 py-1" 
                      :href="'mailto:' + cg.email"
                      rounded
                    >
                      <template v-slot:prepend>
                        <v-avatar color="info" variant="tonal" size="36">
                          <v-icon size="18">mdi-email</v-icon>
                        </v-avatar>
                      </template>
                      <v-list-item-title class="text-body-2 text-truncate" style="max-width: 220px;">{{ cg.email }}</v-list-item-title>
                      <template v-slot:append>
                        <v-icon color="info" size="18">mdi-email-send</v-icon>
                      </template>
                    </v-list-item>
                    
                    <!-- No contact info -->
                    <v-alert v-if="!cg.phone && !cg.email" type="info" density="compact" variant="tonal" class="mt-2">
                      Contact information not available. Please contact admin.
                    </v-alert>
                  </div>
                  
                  <!-- Quick Actions for Mobile -->
                  <div class="d-flex gap-2 mt-3 d-sm-none" v-if="cg.phone || cg.email">
                    <v-btn v-if="cg.phone" color="success" variant="flat" size="small" block :href="'tel:' + cg.phone.replace(/[^0-9]/g, '')">
                      <v-icon start size="16">mdi-phone</v-icon>
                      Call
                    </v-btn>
                    <v-btn v-if="cg.email" color="info" variant="flat" size="small" block :href="'mailto:' + cg.email">
                      <v-icon start size="16">mdi-email</v-icon>
                      Email
                    </v-btn>
                  </div>
                </div>
              </template>
            </v-list>
          </div>
        </v-card-text>
        <v-card-actions class="pa-4 pa-sm-6 pt-0">
          <v-btn color="success" variant="outlined" size="small" @click="contactCaregiverDialog = false; openAdminContactDialog()">
            <v-icon start>mdi-account-tie</v-icon>
            Contact Admin
          </v-btn>
          <v-spacer />
          <v-btn color="primary" variant="flat" @click="contactCaregiverDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Contact Admin Dialog -->
    <v-dialog v-model="contactAdminDialog" max-width="500" :fullscreen="$vuetify.display.xs">
      <v-card>
        <v-card-title class="pa-6" style="background: #10b981; color: white;">
          <div class="d-flex align-center justify-between w-100">
            <span class="section-title" style="color: white;">Admin Contact</span>
            <v-btn v-if="$vuetify.display.xs" icon variant="text" color="white" @click="contactAdminDialog = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-list>
            <v-list-item prepend-icon="mdi-account-tie">
              <v-list-item-title>CAS Private Care Admin</v-list-item-title>
              <v-list-item-subtitle>24/7 Support Team</v-list-item-subtitle>
            </v-list-item>
            <v-list-item prepend-icon="mdi-phone">
              <v-list-item-title>(212) 555-0123</v-list-item-title>
              <v-list-item-subtitle>Emergency Hotline</v-list-item-subtitle>
            </v-list-item>
            <v-list-item prepend-icon="mdi-email">
              <v-list-item-title>support@casprivatecare.com</v-list-item-title>
              <v-list-item-subtitle>Email Support</v-list-item-subtitle>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="success" @click="contactAdminDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Contact Dialog -->
    <v-dialog v-model="contactDialog" max-width="500" :fullscreen="$vuetify.display.xs">
      <v-card>
        <v-card-title class="pa-4" style="background: #2563eb; color: white;">
          <div class="d-flex align-center justify-between w-100">
            <span>Contact Support</span>
            <v-btn v-if="$vuetify.display.xs" icon variant="text" color="white" @click="contactDialog = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-4">
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
        <v-card-actions class="pa-4">
          <v-spacer />
          <v-btn color="primary" @click="contactDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Notification Toast -->
    <notification-toast v-model="notification.show" :type="notification.type" :title="notification.title" :message="notification.message" :timeout="notification.timeout" />

    <!-- View Booking Details Dialog -->
    <v-dialog v-model="viewBookingDialog" max-width="700" scrollable :fullscreen="$vuetify.display.xs">
      <v-card :max-height="$vuetify.display.xs ? '100vh' : '80vh'">
        <v-card-title class="pa-4 pa-sm-6" style="background: #2563eb; color: white;">
          <div class="d-flex align-center justify-between w-100">
            <span style="font-size: 1.25rem; font-weight: 700;">Booking Details</span>
            <v-btn v-if="$vuetify.display.xs" icon variant="text" color="white" @click="viewBookingDialog = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-4 pa-sm-6 scrollable-content">
          <div v-if="selectedBookingDetails" class="booking-details-view">
            <!-- Service Information -->
            <div class="detail-section">
              <h3 class="section-header">Service Information</h3>
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="detail-label">Service Type</span>
                  <span class="detail-value">{{ selectedBookingDetails.service }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Hours per Day</span>
                  <span class="detail-value">{{ selectedBookingDetails.hoursPerDay }} hours</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Service Date</span>
                  <span class="detail-value">{{ selectedBookingDetails.serviceDate }}</span>
                </div>
                <div class="detail-item" v-if="selectedBookingDetails.startingTime">
                  <span class="detail-label">Starting Time</span>
                  <span class="detail-value">{{ selectedBookingDetails.startingTime }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Duration</span>
                  <span class="detail-value">{{ selectedBookingDetails.duration }} days</span>
                </div>
              </div>
            </div>

            <!-- Selected Days of Week -->
            <div class="detail-section" v-if="selectedBookingDetails && selectedBookingDetails.selectedDays && Object.keys(selectedBookingDetails.selectedDays).length > 0">
              <h3 class="section-header">Service Schedule</h3>
              <div class="selected-days-display" v-if="hasEnabledDays(selectedBookingDetails.selectedDays)">
                <template v-for="(day, dayKey) in selectedBookingDetails.selectedDays" :key="dayKey">
                  <div 
                    v-if="day && day.enabled"
                    class="day-schedule-item"
                  >
                    <div class="day-name">{{ capitalizeDay(dayKey) }}</div>
                    <div class="day-time">{{ formatTime(day.startTime) }} - {{ formatTime(day.endTime) }}</div>
                  </div>
                </template>
              </div>
              <div v-else class="text-grey text-center pa-4">
                No specific days selected - service runs daily
              </div>
            </div>

            <!-- Location Information -->
            <div class="detail-section">
              <h3 class="section-header">Location</h3>
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="detail-label">City/Borough</span>
                  <span class="detail-value">{{ selectedBookingDetails.city }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Street Address</span>
                  <span class="detail-value">{{ selectedBookingDetails.streetAddress }}</span>
                </div>
                <div class="detail-item" v-if="selectedBookingDetails.apartmentUnit">
                  <span class="detail-label">Apartment/Unit</span>
                  <span class="detail-value">{{ selectedBookingDetails.apartmentUnit }}</span>
                </div>
              </div>
            </div>

            <!-- Client Information -->
            <div class="detail-section" v-if="selectedBookingDetails.clientAge || selectedBookingDetails.mobilityLevel || (selectedBookingDetails.medicalConditions && selectedBookingDetails.medicalConditions.length > 0)">
              <h3 class="section-header">Client Information</h3>
              <div class="detail-grid">
                <div class="detail-item" v-if="selectedBookingDetails.clientAge">
                  <span class="detail-label">Client Age</span>
                  <span class="detail-value">{{ selectedBookingDetails.clientAge }}</span>
                </div>
                <div class="detail-item" v-if="selectedBookingDetails.mobilityLevel">
                  <span class="detail-label">Mobility Level</span>
                  <span class="detail-value">{{ selectedBookingDetails.mobilityLevel }}</span>
                </div>
                <div class="detail-item" v-if="selectedBookingDetails.medicalConditions && selectedBookingDetails.medicalConditions.length > 0">
                  <span class="detail-label">Medical Conditions</span>
                  <span class="detail-value">
                    <v-chip 
                      v-for="(condition, idx) in selectedBookingDetails.medicalConditions" 
                      :key="idx"
                      size="small"
                      color="info"
                      class="mr-1 mb-1"
                    >
                      {{ condition }}
                    </v-chip>
                  </span>
                </div>
              </div>
            </div>

            <!-- Special Instructions -->
            <div class="detail-section" v-if="selectedBookingDetails.specialInstructions">
              <h3 class="section-header">Special Instructions</h3>
              <div class="instructions-text">{{ selectedBookingDetails.specialInstructions }}</div>
            </div>

            <!-- Pricing Summary -->
            <div class="detail-section pricing-section">
              <h3 class="section-header">Pricing Summary</h3>
              
              <!-- Referral Discount Badge -->
              <div v-if="selectedBookingDetails.hasReferralDiscount" class="referral-discount-banner mb-4">
                <v-icon color="success" size="20" class="mr-2">mdi-tag-check</v-icon>
                <span class="discount-text">Referral Code Applied!</span>
                <v-chip color="success" size="small" class="ml-2">Save ${{ selectedBookingDetails.totalSavings?.toLocaleString() || '0' }}</v-chip>
              </div>
              
              <div class="pricing-grid">
                <div class="pricing-item">
                  <span class="pricing-label">Rate per Hour</span>
                  <div class="pricing-rate-display">
                    <span v-if="selectedBookingDetails.hasReferralDiscount" class="original-rate">${{ selectedBookingDetails.originalRate || 45 }}</span>
                    <span class="pricing-value" :class="{ 'discounted-rate': selectedBookingDetails.hasReferralDiscount }">${{ selectedBookingDetails.hourlyRate }}</span>
                    <v-chip v-if="selectedBookingDetails.hasReferralDiscount" color="success" size="x-small" class="ml-2">-$5/hr</v-chip>
                  </div>
                </div>
                <div class="pricing-item" v-if="selectedBookingDetails.hasReferralDiscount">
                  <span class="pricing-label">Original Total</span>
                  <span class="pricing-value original-total">${{ selectedBookingDetails.originalTotal?.toLocaleString() || '0' }}</span>
                </div>
                <div class="pricing-item" v-if="selectedBookingDetails.hasReferralDiscount">
                  <span class="pricing-label">Your Savings</span>
                  <span class="pricing-value savings-value">-${{ selectedBookingDetails.totalSavings?.toLocaleString() || '0' }}</span>
                </div>
                <div class="pricing-item total-item">
                  <span class="pricing-label total-label">Order Total</span>
                  <span class="pricing-value total-value">${{ selectedBookingDetails.total?.toLocaleString() || '0' }}</span>
                </div>
              </div>
            </div>

            <!-- Submission Info -->
            <div class="detail-section submission-section">
              <h3 class="section-header">Submission Info</h3>
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="detail-label">Submitted At</span>
                  <span class="detail-value">{{ selectedBookingDetails.submittedAt || 'N/A' }}</span>
                </div>
                <div class="detail-item" v-if="selectedBookingDetails.referralCode">
                  <span class="detail-label">Referral Code Used</span>
                  <span class="detail-value">
                    <v-chip color="success" size="small">{{ selectedBookingDetails.referralCode }}</v-chip>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="primary" @click="viewBookingDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="editBookingDialog" max-width="600" :fullscreen="$vuetify.display.xs">
      <v-card>
        <v-card-title class="pa-4 pa-sm-6" style="background: #2563eb; color: white;">
          <div class="d-flex align-center justify-between w-100">
            <span style="font-size: 1.25rem; font-weight: 700;">Edit Booking</span>
            <v-btn v-if="$vuetify.display.xs" icon variant="text" color="white" @click="editBookingDialog = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-4 pa-sm-6">
          <v-row>
            <v-col cols="12" md="6">
              <v-select v-model="editBookingData.serviceType" :items="['Caregiver', 'Elderly Care', 'Personal Care', 'Childcare', 'Companion Care']" label="Service Type" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-select v-model="editBookingData.dutyType" :items="['8 Hours per Day', '12 Hours per Day', '24 Hours per Day']" label="Hours per Day" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="editBookingData.date" label="Service Date" type="date" variant="outlined" :min="today" />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="editBookingData.startingTime" label="Starting Time" type="time" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-select v-model="editBookingData.durationDays" :items="[{title: '15 Days', value: 15}, {title: '30 Days', value: 30}, {title: '60 Days', value: 60}, {title: '90 Days', value: 90}]" label="Duration" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="editBookingData.city" label="City/Borough" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="editBookingData.clientAge" label="Client Age" type="number" variant="outlined" />
            </v-col>
            <v-col cols="12">
              <v-text-field v-model="editBookingData.streetAddress" label="Street Address" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="editBookingData.apartmentUnit" label="Apartment/Unit" variant="outlined" />
            </v-col>
            <v-col cols="12" md="6">
              <v-select v-model="editBookingData.mobilityLevel" :items="[{title: 'Independent', value: 'independent'}, {title: 'Assisted', value: 'assisted'}, {title: 'Wheelchair', value: 'wheelchair'}, {title: 'Bedridden', value: 'bedridden'}]" label="Mobility Level" variant="outlined" />
            </v-col>
            <v-col cols="12">
              <v-textarea v-model="editBookingData.notes" label="Special Instructions" variant="outlined" rows="3" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer />
          <v-btn color="grey" variant="outlined" @click="editBookingDialog = false">Cancel</v-btn>
          <v-btn color="primary" @click="updateBooking">Update Booking</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Rating Modal -->
    <rating-modal
      v-model="ratingDialog"
      :booking="selectedBookingForRating"
      :caregivers="caregiversToRate"
      @submitted="handleRatingSubmitted"
    />
  </dashboard-template>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import DashboardTemplate from './DashboardTemplate.vue';
import StatCard from './shared/StatCard.vue';
import BrowseCaregivers from './BrowseCaregivers.vue';
import NotificationCenter from './shared/NotificationCenter.vue';
import NotificationToast from './shared/NotificationToast.vue';
import EmailVerificationBanner from './EmailVerificationBanner.vue';
import RatingModal from './shared/RatingModal.vue';
import { useNotification } from '../composables/useNotification.js';
import { useNYLocationData } from '../composables/useNYLocationData.js';

const props = defineProps({
  userData: {
    type: Object,
    default: () => ({})
  }
});

const { notification, success, error } = useNotification();
const { counties, getCitiesForCounty, loadNYLocationData } = useNYLocationData();

// Computed properties for user display
const userName = computed(() => {
  return props.userData?.name || 'User';
});

const userInitials = computed(() => {
  const name = props.userData?.name || 'User';
  const nameParts = name.split(' ');
  if (nameParts.length >= 2) {
    return (nameParts[0][0] + nameParts[1][0]).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
});

const welcomeMessage = computed(() => {
  const firstName = props.userData?.name?.split(' ')[0] || 'User';
  return `Welcome Back, ${firstName}`;
});

const memberSince = computed(() => {
  if (props.userData?.created_at) {
    const date = new Date(props.userData.created_at);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
  }
  return 'Jan 2024';
});

// API URL with client ID for authenticated requests
const clientStatsUrl = computed(() => {
  const clientId = props.userData?.id;
  if (clientId) {
    return `/api/client/stats?client_id=${clientId}`;
  }
  return '/api/client/stats';
});

// API URL for profile with user ID
const profileUrl = computed(() => {
  const clientId = props.userData?.id;
  if (clientId) {
    return `/api/profile?user_id=${clientId}`;
  }
  return '/api/profile';
});

const availableCities = computed(() => {
  if (!bookingData.value.county) return [];
  return getCitiesForCounty(bookingData.value.county);
});

const profileAvailableCities = computed(() => {
  if (!profileData.value.county) return [];
  return getCitiesForCounty(profileData.value.county);
});

const zipCodeLocation = ref('');
const profileZipLocation = ref('');

const today = computed(() => {
  const date = new Date();
  return date.toISOString().split('T')[0];
});

// ZIP Code to City/State lookup mapping for NY
const zipCodeMap = {
  '10001': 'Manhattan, NY',
  '10002': 'Manhattan, NY',
  '10003': 'Manhattan, NY',
  '10004': 'Manhattan, NY',
  '10005': 'Manhattan, NY',
  '10006': 'Manhattan, NY',
  '10007': 'Manhattan, NY',
  '10009': 'Manhattan, NY',
  '10010': 'Manhattan, NY',
  '10011': 'Manhattan, NY',
  '10012': 'Manhattan, NY',
  '10013': 'Manhattan, NY',
  '10014': 'Manhattan, NY',
  '10016': 'Manhattan, NY',
  '10017': 'Manhattan, NY',
  '10018': 'Manhattan, NY',
  '10019': 'Manhattan, NY',
  '10020': 'Manhattan, NY',
  '10021': 'Manhattan, NY',
  '10022': 'Manhattan, NY',
  '10023': 'Manhattan, NY',
  '10024': 'Manhattan, NY',
  '10025': 'Manhattan, NY',
  '10026': 'Manhattan, NY',
  '10027': 'Manhattan, NY',
  '10028': 'Manhattan, NY',
  '10029': 'Manhattan, NY',
  '10030': 'Manhattan, NY',
  '10031': 'Manhattan, NY',
  '10032': 'Manhattan, NY',
  '10033': 'Manhattan, NY',
  '10034': 'Manhattan, NY',
  '10035': 'Manhattan, NY',
  '10036': 'Manhattan, NY',
  '10037': 'Manhattan, NY',
  '10038': 'Manhattan, NY',
  '10039': 'Manhattan, NY',
  '10040': 'Manhattan, NY',
  '10044': 'Manhattan, NY',
  '10065': 'Manhattan, NY',
  '10069': 'Manhattan, NY',
  '10075': 'Manhattan, NY',
  '10128': 'Manhattan, NY',
  '10280': 'Manhattan, NY',
  '11201': 'Brooklyn, NY',
  '11203': 'Brooklyn, NY',
  '11204': 'Brooklyn, NY',
  '11205': 'Brooklyn, NY',
  '11206': 'Brooklyn, NY',
  '11207': 'Brooklyn, NY',
  '11208': 'Brooklyn, NY',
  '11209': 'Brooklyn, NY',
  '11210': 'Brooklyn, NY',
  '11211': 'Brooklyn, NY',
  '11212': 'Brooklyn, NY',
  '11213': 'Brooklyn, NY',
  '11214': 'Brooklyn, NY',
  '11215': 'Brooklyn, NY',
  '11216': 'Brooklyn, NY',
  '11217': 'Brooklyn, NY',
  '11218': 'Brooklyn, NY',
  '11219': 'Brooklyn, NY',
  '11220': 'Brooklyn, NY',
  '11221': 'Brooklyn, NY',
  '11222': 'Brooklyn, NY',
  '11223': 'Brooklyn, NY',
  '11224': 'Brooklyn, NY',
  '11225': 'Brooklyn, NY',
  '11226': 'Brooklyn, NY',
  '11228': 'Brooklyn, NY',
  '11229': 'Brooklyn, NY',
  '11230': 'Brooklyn, NY',
  '11231': 'Brooklyn, NY',
  '11232': 'Brooklyn, NY',
  '11233': 'Brooklyn, NY',
  '11234': 'Brooklyn, NY',
  '11235': 'Brooklyn, NY',
  '11236': 'Brooklyn, NY',
  '11237': 'Brooklyn, NY',
  '11238': 'Brooklyn, NY',
  '11239': 'Brooklyn, NY',
  '11354': 'Flushing, NY',
  '11355': 'Flushing, NY',
  '11356': 'Flushing, NY',
  '11357': 'Flushing, NY',
  '11358': 'Flushing, NY',
  '11360': 'Bayside, NY',
  '11361': 'Bayside, NY',
  '11362': 'Bayside, NY',
  '11363': 'Bayside, NY',
  '11364': 'Bayside, NY',
  '11365': 'Fresh Meadows, NY',
  '11366': 'Fresh Meadows, NY',
  '11367': 'Fresh Meadows, NY',
  '11368': 'Corona, NY',
  '11369': 'East Elmhurst, NY',
  '11370': 'Elmhurst, NY',
  '11371': 'Elmhurst, NY',
  '11372': 'Jackson Heights, NY',
  '11373': 'Jackson Heights, NY',
  '11374': 'Rego Park, NY',
  '11375': 'Forest Hills, NY',
  '11377': 'Woodside, NY',
  '11378': 'Maspeth, NY',
  '11379': 'Middle Village, NY',
  '11385': 'Ridgewood, NY',
  '10451': 'Bronx, NY',
  '10452': 'Bronx, NY',
  '10453': 'Bronx, NY',
  '10454': 'Bronx, NY',
  '10455': 'Bronx, NY',
  '10456': 'Bronx, NY',
  '10457': 'Bronx, NY',
  '10458': 'Bronx, NY',
  '10459': 'Bronx, NY',
  '10460': 'Bronx, NY',
  '10461': 'Bronx, NY',
  '10462': 'Bronx, NY',
  '10463': 'Bronx, NY',
  '10464': 'Bronx, NY',
  '10465': 'Bronx, NY',
  '10466': 'Bronx, NY',
  '10467': 'Bronx, NY',
  '10468': 'Bronx, NY',
  '10469': 'Bronx, NY',
  '10470': 'Bronx, NY',
  '10471': 'Bronx, NY',
  '10472': 'Bronx, NY',
  '10473': 'Bronx, NY',
  '10474': 'Bronx, NY',
  '10475': 'Bronx, NY',
  '10301': 'Staten Island, NY',
  '10302': 'Staten Island, NY',
  '10303': 'Staten Island, NY',
  '10304': 'Staten Island, NY',
  '10305': 'Staten Island, NY',
  '10306': 'Staten Island, NY',
  '10307': 'Staten Island, NY',
  '10308': 'Staten Island, NY',
  '10309': 'Staten Island, NY',
  '10310': 'Staten Island, NY',
  '10311': 'Staten Island, NY',
  '10312': 'Staten Island, NY',
  '10314': 'Staten Island, NY',
  '11001': 'Long Island City, NY',
  '11004': 'Long Island City, NY',
  '11005': 'Long Island City, NY',
  '11040': 'Long Island City, NY',
  '11101': 'Long Island City, NY',
  '11102': 'Long Island City, NY',
  '11103': 'Long Island City, NY',
  '11104': 'Long Island City, NY',
  '11105': 'Long Island City, NY',
  '11106': 'Long Island City, NY',
  '11109': 'Long Island City, NY',
  '11501': 'Hempstead, NY',
  '11530': 'Hempstead, NY',
  '11550': 'Hempstead, NY',
  '11552': 'Hempstead, NY',
  '11553': 'Hempstead, NY',
  '11554': 'Hempstead, NY',
  '11555': 'Hempstead, NY',
  '11556': 'Hempstead, NY',
  '11557': 'Hempstead, NY',
  '11558': 'Hempstead, NY',
  '11559': 'Hempstead, NY',
  '11560': 'Hempstead, NY',
  '11561': 'Hempstead, NY',
  '11563': 'Hempstead, NY',
  '11565': 'Hempstead, NY',
  '11566': 'Hempstead, NY',
  '11568': 'Hempstead, NY',
  '11569': 'Hempstead, NY',
  '11570': 'Hempstead, NY',
  '11571': 'Hempstead, NY',
  '11572': 'Hempstead, NY',
  '11575': 'Hempstead, NY',
  '11576': 'Hempstead, NY',
  '11577': 'Hempstead, NY',
  '11579': 'Hempstead, NY',
  '11580': 'Hempstead, NY',
  '11581': 'Hempstead, NY',
  '11582': 'Hempstead, NY',
  '11590': 'Hempstead, NY',
  '11596': 'Hempstead, NY',
  '11598': 'Hempstead, NY',
  '11599': 'Hempstead, NY',
  '10501': 'White Plains, NY',
  '10502': 'White Plains, NY',
  '10504': 'White Plains, NY',
  '10505': 'White Plains, NY',
  '10506': 'White Plains, NY',
  '10507': 'White Plains, NY',
  '10510': 'White Plains, NY',
  '10514': 'White Plains, NY',
  '10520': 'White Plains, NY',
  '10522': 'White Plains, NY',
  '10523': 'White Plains, NY',
  '10524': 'White Plains, NY',
  '10526': 'White Plains, NY',
  '10527': 'White Plains, NY',
  '10528': 'White Plains, NY',
  '10530': 'White Plains, NY',
  '10532': 'White Plains, NY',
  '10533': 'White Plains, NY',
  '10538': 'White Plains, NY',
  '10543': 'White Plains, NY',
  '10546': 'White Plains, NY',
  '10547': 'White Plains, NY',
  '10548': 'White Plains, NY',
  '10549': 'White Plains, NY',
  '10550': 'White Plains, NY',
  '10552': 'White Plains, NY',
  '10553': 'White Plains, NY',
  '10560': 'White Plains, NY',
  '10562': 'White Plains, NY',
  '10566': 'White Plains, NY',
  '10567': 'White Plains, NY',
  '10570': 'White Plains, NY',
  '10573': 'White Plains, NY',
  '10576': 'White Plains, NY',
  '10577': 'White Plains, NY',
  '10578': 'White Plains, NY',
  '10579': 'White Plains, NY',
  '10580': 'White Plains, NY',
  '10583': 'White Plains, NY',
  '10587': 'White Plains, NY',
  '10588': 'White Plains, NY',
  '10589': 'White Plains, NY',
  '10590': 'White Plains, NY',
  '10591': 'White Plains, NY',
  '10594': 'White Plains, NY',
  '10595': 'White Plains, NY',
  '10596': 'White Plains, NY',
  '10597': 'White Plains, NY',
  '10598': 'White Plains, NY',
  '10601': 'White Plains, NY',
  '10602': 'White Plains, NY',
  '10603': 'White Plains, NY',
  '10604': 'White Plains, NY',
  '10605': 'White Plains, NY',
  '10606': 'White Plains, NY',
  '10607': 'White Plains, NY',
  '10701': 'Yonkers, NY',
  '10702': 'Yonkers, NY',
  '10703': 'Yonkers, NY',
  '10704': 'Yonkers, NY',
  '10705': 'Yonkers, NY',
  '10706': 'Yonkers, NY',
  '10707': 'Yonkers, NY',
  '10708': 'Yonkers, NY',
  '10709': 'Yonkers, NY',
  '10710': 'Yonkers, NY',
};

const lookupZipCode = async () => {
  const zip = bookingData.value.zipcode;
  if (zip && zip.length === 5 && /^\d{5}$/.test(zip)) {
    // Try API lookup first (supports all NY ZIP codes)
    try {
      const response = await fetch(`/api/zipcode-lookup/${zip}`);
      if (response.ok) {
        const data = await response.json();
        if (data.success && data.location) {
          zipCodeLocation.value = data.location;
          return;
        }
      }
    } catch (error) {
    }
    
    // Fallback to static map
    zipCodeLocation.value = zipCodeMap[zip] || 'New York, NY';
  } else {
    zipCodeLocation.value = '';
  }
};

const lookupProfileZipCode = async () => {
  const zip = profileData.value.zip;
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
    
    // Fallback to static map
    profileZipLocation.value = zipCodeMap[zip] || 'New York, NY';
  } else {
    profileZipLocation.value = '';
  }
};

const onProfileCountyChange = (county) => {
  profileData.value.city = ''; // Reset city when county changes
};

// Filter to allow only letters and spaces for name fields
const filterLettersOnly = (value) => {
  if (!value) return '';
  return value.replace(/[^A-Za-z\s]/g, '');
};

const currentSection = ref(localStorage.getItem('clientSection') || 'dashboard');
const contactDialog = ref(false);
const contactCaregiverDialog = ref(false);
const contactAdminDialog = ref(false);
const selectedBooking = ref(null);
const addPaymentDialog = ref(false);
const searchQuery = ref('');
const filterSpecialty = ref('All');
const filterAvailability = ref('All');
const bookingTab = ref(localStorage.getItem('clientBookingTab') || 'pending');
const editingBookingId = ref(null);
const editBookingDialog = ref(false);
const editBookingData = ref({});
const viewBookingDialog = ref(false);
const selectedBookingDetails = ref(null);
const exportingAnalytics = ref(false);
const spendingChartPeriod = ref('month');
const selectedYear = ref(new Date().getFullYear());
const analyticsData = ref({
  totalSpent: 0,
  thisMonth: 0,
  avgPerMonth: 0,
  totalHours: 0,
  totalBookings: 0,
  activeCaregivers: 0
});

const bookingData = ref({
  serviceType: '',
  dutyType: '',
  zipcode: '',
  date: '',
  startingTime: '',
  durationDays: 15,
  selectedDays: {
    sunday: { enabled: false, startTime: '09:00', endTime: '17:00' },
    monday: { enabled: false, startTime: '09:00', endTime: '17:00' },
    tuesday: { enabled: false, startTime: '09:00', endTime: '17:00' },
    wednesday: { enabled: false, startTime: '09:00', endTime: '17:00' },
    thursday: { enabled: false, startTime: '09:00', endTime: '17:00' },
    friday: { enabled: false, startTime: '09:00', endTime: '17:00' },
    saturday: { enabled: false, startTime: '09:00', endTime: '17:00' }
  },
  genderPreference: 'no_preference',
  specificSkills: [],
  clientAge: '',
  mobilityLevel: '',
  medicalConditions: [],
  transportationNeeded: false,
  streetAddress: '',
  apartmentUnit: '',
  notes: '',
  referralCode: ''
});

const referralDiscount = ref(0);

// Avatar upload
const avatarInput = ref(null);
const userAvatar = ref('');
const uploadingAvatar = ref(false);
const userId = ref(null);

const profileData = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  county: '',
  state: '',
  zip: '',
  birthdate: ''
});

const skillsList = [
  'Medication Management',
  'Physical Therapy',
  'Wound Care',
  'Dementia Care',
  'Diabetes Management',
  'Mobility Assistance',
  'Personal Hygiene',
  'Meal Preparation',
  'Light Housekeeping',
  'Companionship',
  'Transportation',
  'Emergency Response'
];

const medicalConditionsList = [
  'Diabetes',
  'Hypertension',
  'Heart Disease',
  'Arthritis',
  'Dementia/Alzheimer\'s',
  'Stroke Recovery',
  'COPD',
  'Parkinson\'s Disease',
  'Cancer',
  'Depression/Anxiety',
  'Kidney Disease',
  'Osteoporosis'
];

const age = computed(() => {
  if (!profileData.value.birthdate) return '';
  const today = new Date();
  const birthDate = new Date(profileData.value.birthdate);
  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }
  return age;
});

const notifications = ref([]);
const unreadNotifications = computed(() => notifications.value.filter(n => !n.read).length);

const navItems = computed(() => [
  { icon: 'mdi-view-dashboard', title: 'Dashboard', value: 'dashboard' },
  { icon: 'mdi-bell', title: 'Notifications', value: 'notifications', badge: unreadNotifications.value > 0 },
  { icon: 'mdi-credit-card', title: 'Payment Information', value: 'payment' },
  { icon: 'mdi-calendar-plus', title: 'Book Service', value: 'book-form', category: 'SERVICES' },
  { icon: 'mdi-account-multiple', title: 'Browse Caregivers', value: 'book', category: 'SERVICES' },
  // Removed: My Bookings (accessible from dashboard)
  { icon: 'mdi-chart-bar', title: 'Analytics Reports', value: 'analytics', category: 'SERVICES' },
  { icon: 'mdi-account-circle', title: 'Profile', value: 'profile', category: 'ACCOUNT' }
]);

const loadNotificationCount = async () => {
  try {
    const response = await fetch('/api/notifications?user_id=1');
    const data = await response.json();
    notifications.value = data.notifications || [];
  } catch (error) {
  }
};

const stats = ref([
  { title: 'Amount Due', value: '$0', icon: 'mdi-currency-usd', color: 'warning', change: 'No coverage', changeColor: 'text-grey', changeIcon: 'mdi-calendar' },
  { title: 'Contract Status', value: 'N/A', icon: 'mdi-file-document-outline', color: 'grey', change: 'Status: No Active Service', changeColor: 'text-grey', changeIcon: 'mdi-close-circle' },
  { title: 'Total Hours Booked', value: '0', icon: 'mdi-clock-outline', color: 'info', change: 'Loading...', changeColor: 'text-grey', changeIcon: 'mdi-clock-outline' },
  { title: 'Total Spent', value: '$0', icon: 'mdi-cash', color: 'primary', change: 'Loading...', changeColor: 'text-grey', changeIcon: 'mdi-cash' },
]);

// Current service info
const currentCaregiverName = ref('N/A');
const currentServiceStartDate = ref('');
const currentServiceEndDate = ref('');

const ongoingContracts = ref([]);
// Use existing booking variables that are already declared

// Computed property for all bookings combined
const allClientBookings = computed(() => {
  const all = [];
  all.push(...(pendingBookings.value || []));
  all.push(...(confirmedBookings.value || []));
  all.push(...(completedBookings.value || []));
  return all;
});

// Computed property to display only first 3 contracts
const displayedContracts = computed(() => {
  return ongoingContracts.value.slice(0, 3);
});

// Navigate to My Bookings section filtered by approved status
const goToApprovedBookings = () => {
  currentSection.value = 'my-bookings';
  // Set tab to show approved bookings
  bookingTab.value = 'approved';
};

// Helper function to format date as M/D/YYYY
const formatShortDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return `${date.getMonth() + 1}/${date.getDate()}/${date.getFullYear()}`;
};

const loadClientStats = async () => {
  try {
    const response = await fetch(clientStatsUrl.value);
    if (!response.ok) throw new Error('API failed');
    const data = await response.json();
    
    // Update Amount Due with coverage dates
    const amountDue = data.amount_due || 0;
    stats.value[0].value = `$${amountDue.toLocaleString()}`;
    
    if (data.coverage_start && data.coverage_end) {
      const coverageText = `Coverage: ${formatShortDate(data.coverage_start)} - ${formatShortDate(data.coverage_end)}`;
      stats.value[0].change = coverageText;
      stats.value[0].changeColor = 'text-info';
      stats.value[0].changeIcon = 'mdi-calendar-range';
    } else {
      stats.value[0].change = `$${(data.this_month_amount_due || 0).toLocaleString()} this month`;
      stats.value[0].changeColor = 'text-warning';
      stats.value[0].changeIcon = 'mdi-calendar-month';
    }
    
    // Update Ongoing Contracts count
    // Update Contract Status stat card - show "Pending" or "Ongoing Contract" based on payment status
    const activeBookings = (data.my_bookings || [])
      .filter(b => ['approved', 'confirmed', 'in_progress'].includes(b.status))
      .sort((a, b) => new Date(a.service_date) - new Date(b.service_date));
    
    // Find the first booking
    const currentActiveBooking = activeBookings[0];
    
    if (currentActiveBooking) {
      // Check if booking is paid
      const isPaid = currentActiveBooking.payment_status === 'paid' || currentActiveBooking.payment_status === 'completed';
      const statusText = isPaid ? 'Ongoing Contract' : 'Pending';
      const statusColor = isPaid ? 'success' : 'warning';
      
      const startDate = new Date(currentActiveBooking.service_date);
      const endDate = new Date(startDate);
      endDate.setDate(endDate.getDate() + (currentActiveBooking.duration_days || 15));
      
      const startStr = startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
      const endStr = endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
      
      currentCaregiverName.value = statusText;
      currentServiceStartDate.value = startStr;
      currentServiceEndDate.value = endStr;
      
      stats.value[1] = {
        title: 'Contract Status',
        value: statusText,
        icon: 'mdi-file-document-outline',
        color: statusColor,
        change: `${startStr} - ${endStr}`,
        changeColor: isPaid ? 'text-success' : 'text-warning',
        changeIcon: 'mdi-calendar-range'
      };
    } else {
      stats.value[1] = {
        title: 'Contract Status',
        value: 'N/A',
        icon: 'mdi-file-document-outline',
        color: 'grey',
        change: 'Status: No Active Service',
        changeColor: 'text-grey',
        changeIcon: 'mdi-close-circle'
      };
      currentCaregiverName.value = 'N/A';
      currentServiceStartDate.value = '';
      currentServiceEndDate.value = '';
    }
    
    // Calculate total hours booked from active bookings
    const activeBookingsForHours = (data.my_bookings || [])
      .filter(b => ['approved', 'confirmed', 'in_progress'].includes(b.status));
    
    const totalHoursBooked = activeBookingsForHours.reduce((sum, b) => {
      const hoursMatch = b.duty_type?.match(/(\d+)/);
      const hoursPerDay = hoursMatch ? parseInt(hoursMatch[1]) : 8;
      return sum + (hoursPerDay * (b.duration_days || 15));
    }, 0);
    
    stats.value[2].value = totalHoursBooked.toString();
    
    // Add useful tags for Total Hours Booked
    if (activeBookingsForHours.length > 0) {
      const totalDays = activeBookingsForHours.reduce((sum, b) => sum + (b.duration_days || 15), 0);
      const avgHoursPerDay = totalDays > 0 ? Math.round(totalHoursBooked / totalDays) : 0;
      stats.value[2].change = `${activeBookingsForHours.length} active booking${activeBookingsForHours.length > 1 ? 's' : ''} • Avg: ${avgHoursPerDay} hrs/day`;
      stats.value[2].changeColor = 'text-info';
      stats.value[2].changeIcon = 'mdi-clock-time-four-outline';
    } else {
      stats.value[2].change = 'No active bookings';
      stats.value[2].changeColor = 'text-grey';
      stats.value[2].changeIcon = 'mdi-clock-outline';
    }
    
    // Update Total Spent
    stats.value[3].value = `$${(data.total_spent || 0).toLocaleString()}`;
    
    // Add useful tags for Total Spent
    const thisMonthSpent = data.this_month_spent || 0;
    const avgMonthlySpent = Math.round(data.avg_monthly_spent || 0);
    if (thisMonthSpent > 0 || avgMonthlySpent > 0) {
      const tags = [];
      if (thisMonthSpent > 0) {
        tags.push(`This month: $${thisMonthSpent.toLocaleString()}`);
      }
      if (avgMonthlySpent > 0) {
        tags.push(`Avg: $${avgMonthlySpent.toLocaleString()}/mo`);
      }
      stats.value[3].change = tags.join(' • ');
      stats.value[3].changeColor = 'text-primary';
      stats.value[3].changeIcon = 'mdi-chart-line';
    } else {
      stats.value[3].change = 'No spending recorded';
      stats.value[3].changeColor = 'text-grey';
      stats.value[3].changeIcon = 'mdi-cash-off';
    }
    
    // Update analytics data
    analyticsData.value = {
      totalSpent: data.total_spent || 0,
      thisMonth: data.this_month_spent || 0,
      avgPerMonth: Math.round(data.avg_monthly_spent || 0),
      totalHours: data.total_hours || 0,
      totalBookings: data.total_bookings || 0,
      activeCaregivers: data.active_caregivers || 0
    };
    
    // Update transaction history from API
    if (data.transactions && data.transactions.length > 0) {
      recentTransactions.value = data.transactions;
    }
  } catch (error) {
  }
};

const loadOngoingContracts = async () => {
  try {
    const response = await fetch(clientStatsUrl.value);
    if (!response.ok) throw new Error('API failed');
    const data = await response.json();
    
    // Transform bookings into contract format
    ongoingContracts.value = (data.my_bookings || []).filter(booking => 
      booking.status === 'approved' || booking.status === 'in_progress'
    ).map(booking => {
      // Get caregiver info from assignments
      const assignment = booking.assignments && booking.assignments.length > 0 ? booking.assignments[0] : null;
      const caregiverUser = assignment?.caregiver?.user;
      const caregiverName = caregiverUser?.name || 'Pending Assignment';
      const caregiverAvatar = caregiverUser?.avatar ? `/storage/${caregiverUser.avatar}` : null;
      
      // Generate initials for fallback
      const initials = caregiverName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
      
      return {
        id: booking.id,
        caregiverName: caregiverName,
        caregiverImage: caregiverAvatar,
        caregiverInitials: initials,
        serviceType: booking.service_type || 'Care Service',
        startDate: new Date(booking.service_date).toLocaleDateString(),
        endDate: new Date(new Date(booking.service_date).getTime() + (booking.duration_days || 15) * 24 * 60 * 60 * 1000).toLocaleDateString(),
        status: booking.status === 'approved' ? 'active' : 'pending'
      };
    });
  } catch (error) {
    // Fallback data for demo
    ongoingContracts.value = [];
  }
};

const transactionHeaders = [
  { title: 'Date', key: 'date' },
  { title: 'Service', key: 'service' },
  { title: 'Caregiver', key: 'caregiver' },
  { title: 'Amount', key: 'amount' },
  { title: 'Status', key: 'status' },
];

const recentTransactions = ref([]);

const notificationFilter = ref('All');
const notificationType = ref('All Types');
const notificationSearch = ref('');

// Notifications are now handled by the NotificationCenter component

const filteredNotifications = computed(() => {
  return notifications.value.filter(n => {
    const matchesFilter = notificationFilter.value === 'All' || 
      (notificationFilter.value === 'Read' && n.read) ||
      (notificationFilter.value === 'Unread' && !n.read);
    const matchesType = notificationType.value === 'All Types' || n.type === notificationType.value;
    const matchesSearch = !notificationSearch.value || 
      n.title.toLowerCase().includes(notificationSearch.value.toLowerCase()) ||
      n.message.toLowerCase().includes(notificationSearch.value.toLowerCase());
    return matchesFilter && matchesType && matchesSearch;
  });
});

const markAllRead = () => {
  notifications.value.forEach(n => n.read = true);
};

const toggleRead = (notification) => {
  notification.read = !notification.read;
};

const deleteNotification = (index) => {
  notifications.value.splice(index, 1);
};

const specialties = ['All', 'Caregiver', 'Housekeeping', 'Personal Assistant'];

const caregivers = ref([
  { id: 1, name: 'Maria Santos', specialty: 'Elderly Care Specialist', rating: 5.0, reviews: 24, experience: 8, rate: 25, availability: 'available', image: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=300&h=200&fit=crop' },
  { id: 2, name: 'Ana Rodriguez', specialty: 'House Cleaning & Personal Care', rating: 4.8, reviews: 31, experience: 5, rate: 22, availability: 'available', image: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=300&h=200&fit=crop' },
  { id: 3, name: 'Lisa Johnson', specialty: 'Personal Care Specialist', rating: 5.0, reviews: 18, experience: 12, rate: 35, availability: 'busy', image: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=300&h=200&fit=crop' },
  { id: 4, name: 'Robert Chen', specialty: 'Physical Therapy', rating: 4.9, reviews: 42, experience: 10, rate: 30, availability: 'available', image: 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=300&h=200&fit=crop' },
  { id: 5, name: 'Emma Wilson', specialty: 'Companion Care', rating: 4.7, reviews: 28, experience: 6, rate: 24, availability: 'available', image: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=300&h=200&fit=crop' },
  { id: 6, name: 'James Taylor', specialty: 'Elderly Care', rating: 4.9, reviews: 35, experience: 9, rate: 28, availability: 'available', image: 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=300&h=200&fit=crop' },
  { id: 7, name: 'Sophie Martinez', specialty: 'Childcare Specialist', rating: 5.0, reviews: 50, experience: 7, rate: 26, availability: 'available', image: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?w=300&h=200&fit=crop' },
  { id: 8, name: 'David Brown', specialty: 'Personal Care', rating: 4.6, reviews: 22, experience: 4, rate: 23, availability: 'busy', image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=200&fit=crop' },
  { id: 9, name: 'Jennifer Lee', specialty: 'House Cleaning', rating: 4.8, reviews: 38, experience: 8, rate: 21, availability: 'available', image: 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=300&h=200&fit=crop' },
  { id: 10, name: 'Michael Davis', specialty: 'Physical Therapy', rating: 5.0, reviews: 45, experience: 11, rate: 32, availability: 'available', image: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=300&h=200&fit=crop' },
  { id: 11, name: 'Amanda Garcia', specialty: 'Elderly Care', rating: 4.9, reviews: 33, experience: 9, rate: 27, availability: 'available', image: 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=300&h=200&fit=crop' },
  { id: 12, name: 'Christopher White', specialty: 'Companion Care', rating: 4.7, reviews: 26, experience: 5, rate: 25, availability: 'busy', image: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=200&fit=crop' },
  { id: 13, name: 'Jessica Moore', specialty: 'Personal Care', rating: 4.8, reviews: 30, experience: 7, rate: 24, availability: 'available', image: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&h=200&fit=crop' },
  { id: 14, name: 'Daniel Anderson', specialty: 'Elderly Care', rating: 5.0, reviews: 40, experience: 10, rate: 29, availability: 'available', image: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=300&h=200&fit=crop' },
  { id: 15, name: 'Sarah Thompson', specialty: 'Childcare Specialist', rating: 4.9, reviews: 36, experience: 6, rate: 26, availability: 'available', image: 'https://images.unsplash.com/photo-1489424731084-a5d8b219a5bb?w=300&h=200&fit=crop' },
  { id: 16, name: 'Kevin Martinez', specialty: 'Physical Therapy', rating: 4.8, reviews: 29, experience: 8, rate: 31, availability: 'available', image: 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=300&h=200&fit=crop' },
  { id: 17, name: 'Rachel Harris', specialty: 'House Cleaning', rating: 4.7, reviews: 25, experience: 5, rate: 22, availability: 'busy', image: 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=300&h=200&fit=crop' },
  { id: 18, name: 'Brian Clark', specialty: 'Companion Care', rating: 4.9, reviews: 34, experience: 9, rate: 28, availability: 'available', image: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=200&fit=crop' },
  { id: 19, name: 'Michelle Lewis', specialty: 'Elderly Care', rating: 5.0, reviews: 48, experience: 12, rate: 33, availability: 'available', image: 'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=300&h=200&fit=crop' },
  { id: 20, name: 'Thomas Walker', specialty: 'Personal Care', rating: 4.6, reviews: 21, experience: 4, rate: 23, availability: 'available', image: 'https://images.unsplash.com/photo-1463453091185-61582044d556?w=300&h=200&fit=crop' },
  { id: 21, name: 'Laura Hall', specialty: 'Childcare Specialist', rating: 4.8, reviews: 37, experience: 7, rate: 25, availability: 'available', image: 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=300&h=200&fit=crop' },
  { id: 22, name: 'Steven Young', specialty: 'Physical Therapy', rating: 4.9, reviews: 41, experience: 10, rate: 30, availability: 'busy', image: 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=300&h=200&fit=crop' },
  { id: 23, name: 'Nicole King', specialty: 'House Cleaning', rating: 4.7, reviews: 27, experience: 6, rate: 21, availability: 'available', image: 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=300&h=200&fit=crop' },
  { id: 24, name: 'Jason Wright', specialty: 'Elderly Care', rating: 5.0, reviews: 44, experience: 11, rate: 31, availability: 'available', image: 'https://images.unsplash.com/photo-1542909168-82c3e7fdca44?w=300&h=200&fit=crop' },
  { id: 25, name: 'Ashley Scott', specialty: 'Companion Care', rating: 4.8, reviews: 32, experience: 8, rate: 27, availability: 'available', image: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=300&h=200&fit=crop' },
]);

const loadCaregivers = async () => {
  try {
    const response = await fetch('/api/caregivers');
    const data = await response.json();
    caregivers.value = data.caregivers.map(c => ({
      id: c.id,
      name: c.user?.name || 'Caregiver',
      specialty: c.specializations?.[0] || 'General Care',
      rating: c.rating || 4.5,
      reviews: c.total_reviews || 0,
      experience: c.years_experience || 0,
      rate: c.hourly_rate || 28,
      availability: c.availability_status || 'available',
      image: c.user?.avatar || 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=300&h=200&fit=crop'
    }));
  } catch (error) {
    // Keep the default caregivers if API fails
  }
};

const filteredCaregivers = computed(() => {
  return caregivers.value.filter(c => {
    const matchesSearch = !searchQuery.value || c.name.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesSpecialty = filterSpecialty.value === 'All' || c.specialty.includes(filterSpecialty.value);
    const matchesAvailability = filterAvailability.value === 'All' || c.availability === filterAvailability.value.toLowerCase();
    return matchesSearch && matchesSpecialty && matchesAvailability;
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

const applyFilters = () => {
  // Filters are reactive, so this is just for UI feedback
};

const resetFilters = () => {
  searchQuery.value = '';
  filterSpecialty.value = 'All';
  filterAvailability.value = 'All';
};

const viewCaregiverDetails = (caregiver) => {
  alert(`Viewing details for ${caregiver.name}`);
};

const pendingBookings = ref([]);
const confirmedBookings = ref([]);
const loadingBookings = ref(true);

const loadMyBookings = async () => {
  try {
    loadingBookings.value = true;
    const response = await fetch(clientStatsUrl.value);
    const data = await response.json();
    
    if (data.my_bookings) {
      
      pendingBookings.value = data.my_bookings
        .filter(b => {
          return b.status === 'pending';
        })
        .map(booking => {
          // Extract hours from duty_type
          const hoursMatch = (booking.duty_type || '8 Hours Duty').match(/(\d+)\s*Hours?/i);
          const hoursPerDay = hoursMatch ? parseInt(hoursMatch[1]) : 8;
          
          return {
            id: booking.id,
            service: booking.service_type || 'Care Service',
            serviceType: booking.service_type || 'Care Service',
            date: new Date(booking.service_date).toLocaleDateString(),
            startingTime: booking.starting_time || 'N/A',
            location: booking.borough || booking.city || 'Manhattan',
            streetAddress: booking.street_address || 'N/A',
            apartmentUnit: booking.apartment_unit || booking.unit || 'N/A',
            borough: booking.borough || booking.city || 'Manhattan',
            dutyType: booking.duty_type || '8 Hours Duty',
            hoursPerDay: hoursPerDay,
            duration: booking.duration_days || 1,
            durationDays: booking.duration_days || 1,
            clientAge: booking.client_age || null,
            mobilityLevel: booking.mobility_level || 'Standard',
            medicalConditions: booking.medical_conditions || '',
            specialInstructions: booking.special_instructions || '',
            hourlyRate: booking.hourly_rate || 45,
            referralDiscount: booking.referral_discount_applied || 0,
            price: booking.total_price || (hoursPerDay * (booking.duration_days || 1) * (booking.hourly_rate || 45))
          };
        });
        
      confirmedBookings.value = data.my_bookings
        .filter(b => {
          const isApproved = b.status === 'approved';
          return isApproved;
        })
        .map(booking => {
          // Extract hours from duty_type first
          const hoursMatch = (booking.duty_type || '8 Hours Duty').match(/(\d+)\s*Hours?/i);
          const hoursPerDay = hoursMatch ? parseInt(hoursMatch[1]) : 8;
          
          // Calculate required caregivers based on duty hours
          // 8 hours = 1 caregiver, 12 hours = 2 caregivers, 24 hours = 3 caregivers
          let requiredCount = 1;
          if (hoursPerDay === 8) requiredCount = 1;
          else if (hoursPerDay === 12) requiredCount = 2;
          else if (hoursPerDay === 24) requiredCount = 3;
          else if (hoursPerDay < 12) requiredCount = 1;
          else if (hoursPerDay < 24) requiredCount = 2;
          else requiredCount = 3;
          
          const durationDays = booking.duration_days || 15;
          const assignedCount = booking.assignments?.length || 0;
          
          // Build assigned caregivers list with contact info
          const assignedCaregivers = (booking.assignments || []).map(assignment => ({
            id: assignment.caregiver?.id,
            name: assignment.caregiver?.user?.name || 'Unknown Caregiver',
            email: assignment.caregiver?.user?.email || '',
            phone: assignment.caregiver?.phone || assignment.caregiver?.user?.phone || '',
            avatar: assignment.caregiver?.user?.avatar || null
          }));
          
          return {
            id: booking.id,
            service: booking.service_type || 'Care Service',
            serviceType: booking.service_type || 'Care Service',
            caregiver: booking.assignments && booking.assignments.length > 0 
              ? booking.assignments[0].caregiver?.user?.name || 'Caregiver Assigned'
              : 'Pending Assignment',
            date: new Date(booking.service_date).toLocaleDateString(),
            time: new Date(booking.service_date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
            startingTime: booking.starting_time || 'N/A',
            location: booking.borough || booking.city || 'Manhattan',
            streetAddress: booking.street_address || 'N/A',
            apartmentUnit: booking.apartment_unit || booking.unit || 'N/A',
            borough: booking.borough || booking.city || 'Manhattan',
            assignedCount: assignedCount,
            requiredCount: requiredCount,
            duration: durationDays,
            durationDays: durationDays,
            dutyType: booking.duty_type || '8 Hours Duty',
            hoursPerDay: hoursPerDay,
            clientAge: booking.client_age || null,
            mobilityLevel: booking.mobility_level || 'Standard',
            medicalConditions: booking.medical_conditions || '',
            specialInstructions: booking.special_instructions || '',
            hourlyRate: booking.hourly_rate || 45,
            referralDiscount: booking.referral_discount_applied || 0,
            price: booking.total_price || (hoursPerDay * durationDays * (booking.hourly_rate || 45)),
            assignedCaregivers: assignedCaregivers,
            payment_status: booking.payment_status || 'unpaid',
            payment_intent_id: booking.stripe_payment_intent_id || booking.payment_intent_id || null,
            payment_date: booking.payment_date || null
          };
        });
        
    }
  } catch (error) {
    pendingBookings.value = [];
    confirmedBookings.value = [];
  } finally {
    loadingBookings.value = false;
  }
};

const completedBookings = ref([]);

const loadCompletedBookings = async () => {
  try {
    const response = await fetch(clientStatsUrl.value);
    const data = await response.json();
    if (data.my_bookings) {
      completedBookings.value = data.my_bookings
        .filter(b => b.status === 'completed')
        .map(booking => {
          // Extract hours from duty_type
          const hoursMatch = (booking.duty_type || '8 Hours Duty').match(/(\d+)\s*Hours?/i);
          const hoursPerDay = hoursMatch ? parseInt(hoursMatch[1]) : 8;
          const durationDays = booking.duration_days || 15;
          
          return {
            id: booking.id,
            service: booking.service_type || 'Care Service',
            serviceType: booking.service_type || 'Care Service',
            caregiver: booking.assignments && booking.assignments.length > 0 
              ? booking.assignments[0].caregiver?.user?.name || 'Caregiver'
              : 'Caregiver',
            date: new Date(booking.service_date).toLocaleDateString(),
            startingTime: booking.starting_time || 'N/A',
            location: booking.borough || booking.city || 'Manhattan',
            streetAddress: booking.street_address || 'N/A',
            apartmentUnit: booking.apartment_unit || booking.unit || 'N/A',
            hoursPerDay: hoursPerDay,
            duration: durationDays,
            durationDays: durationDays,
            clientAge: booking.client_age || null,
            mobilityLevel: booking.mobility_level || 'Standard',
            medicalConditions: booking.medical_conditions || '',
            hourlyRate: booking.hourly_rate || 45,
            price: booking.total_price || (hoursPerDay * durationDays * (booking.hourly_rate || 45)),
            caregiverImage: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=100&h=100&fit=crop'
          };
        });
    }
  } catch (error) {
    // Fallback data for demo
    completedBookings.value = [
      {
        id: 3,
        service: 'Personal Care',
        caregiver: 'Sarah Williams',
        date: 'Dec 15, 2024',
        caregiverImage: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=100&h=100&fit=crop'
      }
    ];
  }
};

const autoPayEnabled = ref(true);

// Mock data removed - now using real payment data from bookings

// Helper function to convert 24-hour time to 12-hour format with AM/PM
const formatTimeTo12Hour = (time24) => {
  if (!time24) return '9:00 AM';
  
  const [hourStr, minuteStr] = time24.split(':');
  let hour = parseInt(hourStr, 10);
  const minute = minuteStr || '00';
  
  const ampm = hour >= 12 ? 'PM' : 'AM';
  hour = hour % 12 || 12; // Convert 0 to 12 for midnight, and 13-23 to 1-11
  
  return `${hour}:${minute} ${ampm}`;
};

const submitBooking = async () => {
  try {
    // CHECK BOOKING LIMITS: Only allow 1 pending OR 1 approved booking at a time
    const hasPending = pendingBookings.value.length > 0;
    const hasApproved = confirmedBookings.value.length > 0;
    
    if (hasPending) {
      error(
        'You have a pending booking that needs to be reviewed by our admin team first. You can submit a new booking once the current one is approved or rejected.',
        'Cannot Submit Booking'
      );
      return;
    }
    
    if (hasApproved) {
      error(
        'You have an active approved booking. To avoid scheduling conflicts, please wait until your current service is completed before booking a new one.',
        'Cannot Submit Booking'
      );
      return;
    }
    
    // Calculate the hourly rate (with or without referral discount)
    const baseRate = 45; // Base rate: Caregiver $28 + Agency $16.50 + Training $0.50
    const finalRate = referralDiscount.value > 0 ? (baseRate - referralDiscount.value) : baseRate;
    
    // Parse zipcode location to extract city and county
    let city = null;
    let county = null;
    let borough = null;
    
    if (bookingData.value.zipcode && zipCodeLocation.value) {
      // zipCodeLocation format: "City, NY" or "Borough, NY"
      const locationParts = zipCodeLocation.value.split(',');
      if (locationParts.length >= 2) {
        const locationName = locationParts[0].trim();
        // Check if it's a borough (Manhattan, Brooklyn, Queens, Bronx, Staten Island)
        const boroughs = ['Manhattan', 'Brooklyn', 'Queens', 'Bronx', 'Staten Island'];
        if (boroughs.includes(locationName)) {
          borough = locationName;
        } else {
          city = locationName;
          // Try to determine county from city or use a default
          county = locationName; // You may want to add a city-to-county mapping
        }
      }
    }
    
    // Get the earliest start time from the selected days to use as the booking's starting time
    let earliestStartTime = '09:00';
    const enabledDays = Object.entries(bookingData.value.selectedDays)
      .filter(([_, dayData]) => dayData.enabled)
      .map(([_, dayData]) => dayData);
    
    // Validate minimum 3 days selected
    if (enabledDays.length < 3) {
      error(
        'Please select at least 3 days of the week for your service. This ensures consistent care and better availability of qualified caregivers.',
        'Minimum Days Required'
      );
      return;
    }
    
    if (enabledDays.length > 0) {
      const times = enabledDays.map(day => day.startTime).sort();
      earliestStartTime = times[0];
    }
    
    // Build day_schedules object from selectedDays
    const daySchedules = {};
    Object.entries(bookingData.value.selectedDays).forEach(([dayKey, dayData]) => {
      if (dayData.enabled) {
        // Format: "11:00 AM - 11:00 PM"
        daySchedules[dayKey] = `${formatTimeTo12Hour(dayData.startTime)} - ${formatTimeTo12Hour(dayData.endTime)}`;
      }
    });
    
    const response = await fetch('/api/bookings', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        service_type: bookingData.value.serviceType,
        duty_type: bookingData.value.dutyType,
        zipcode: bookingData.value.zipcode,
        borough: borough,
        city: city,
        county: county,
        service_date: bookingData.value.date,
        starting_time: earliestStartTime, // Use earliest time from selected days
        selected_days: bookingData.value.selectedDays, // Keep for backward compatibility
        day_schedules: Object.keys(daySchedules).length > 0 ? daySchedules : null, // NEW: Send day schedules
        duration_days: bookingData.value.durationDays,
        gender_preference: bookingData.value.genderPreference,
        specific_skills: bookingData.value.specificSkills,
        client_age: bookingData.value.clientAge,
        mobility_level: bookingData.value.mobilityLevel,
        medical_conditions: bookingData.value.medicalConditions,
        transportation_needed: bookingData.value.transportationNeeded,
        street_address: bookingData.value.streetAddress,
        apartment_unit: bookingData.value.apartmentUnit,
        special_instructions: bookingData.value.notes,
        // Referral code and pricing
        referral_code: bookingData.value.referralCode || null,
        referral_discount_applied: referralDiscount.value > 0 ? referralDiscount.value : null,
        hourly_rate: finalRate
      })
    });
    
    if (response.ok) {
      success('Booking submitted successfully!');
      bookingData.value = {
        serviceType: '',
        dutyType: '',
        zipcode: '',
        date: '',
        startingTime: '',
        durationDays: 15,
        selectedDays: {
          sunday: { enabled: false, startTime: '09:00', endTime: '17:00' },
          monday: { enabled: false, startTime: '09:00', endTime: '17:00' },
          tuesday: { enabled: false, startTime: '09:00', endTime: '17:00' },
          wednesday: { enabled: false, startTime: '09:00', endTime: '17:00' },
          thursday: { enabled: false, startTime: '09:00', endTime: '17:00' },
          friday: { enabled: false, startTime: '09:00', endTime: '17:00' },
          saturday: { enabled: false, startTime: '09:00', endTime: '17:00' }
        },
        genderPreference: 'no_preference',
        specificSkills: [],
        clientAge: '',
        mobilityLevel: '',
        medicalConditions: [],
        transportationNeeded: false,
        streetAddress: '',
        apartmentUnit: '',
        notes: '',
        referralCode: ''
      };
      // Refresh bookings data
      await loadMyBookings();
      await loadCompletedBookings();
      await loadOngoingContracts();
      currentSection.value = 'my-bookings';
    } else {
      const errorData = await response.json();
      alert('Error: ' + (errorData.message || 'Failed to submit booking'));
    }
  } catch (error) {
    alert('Error submitting booking. Please try again.');
  }
};

const cancelBooking = async (id) => {
  if (confirm('Are you sure you want to cancel this booking?')) {
    try {
      const response = await fetch(`/api/bookings/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: 'cancelled' })
      });
      
      if (response.ok) {
        success('Booking cancelled successfully!');
        await loadMyBookings();
        await loadCompletedBookings();
        await loadOngoingContracts();
      } else {
        alert('Failed to cancel booking. Please try again.');
      }
    } catch (error) {
      alert('Error cancelling booking. Please try again.');
    }
  }
};

const ratingDialog = ref(false);
const selectedBookingForRating = ref(null);
const caregiversToRate = ref([]);

// Check if client can book (only 1 pending OR 1 approved allowed)
const attemptBooking = () => {
  const hasPending = pendingBookings.value.length > 0;
  const hasApproved = confirmedBookings.value.length > 0;
  
  if (hasPending) {
    error(
      'You have a pending booking awaiting approval. Please wait for our admin team to review your request before submitting a new booking.',
      'Booking Limit Reached'
    );
    // Navigate to My Bookings to show pending booking
    currentSection.value = 'my-bookings';
    return;
  }
  
  if (hasApproved) {
    error(
      'You have an active booking in progress. To prevent scheduling conflicts and ensure quality service, we only allow one active booking at a time. Your current booking will be completed soon.',
      'Active Booking in Progress'
    );
    // Navigate to My Bookings to show approved booking
    currentSection.value = 'my-bookings';
    bookingTab.value = 'approved';
    return;
  }
  
  // All clear, proceed to booking form
  currentSection.value = 'book-form';
};

const goToPayment = (booking) => {
  // Navigate to separate payment page
  window.location.href = `/payment?booking_id=${booking.id}`;
};

const processPayment = () => {
  // Prototype: Show success message
  success('Payment processed successfully! This is a prototype - Stripe integration coming soon.', 'Payment Successful');
  selectedBooking.value = null;
  currentSection.value = 'dashboard';
  // Reload bookings to reflect any status changes
  loadPendingBookings();
  loadConfirmedBookings();
  loadCompletedBookings();
};

const rateBooking = async (id) => {
  try {
    // Check if client can review this booking
    const response = await fetch(`/api/reviews/booking/${id}/can-review`);
    const data = await response.json();

    if (data.success && data.can_review) {
      // Find the booking details
      const booking = completedBookings.value.find(b => b.id === id);
      selectedBookingForRating.value = booking;
      caregiversToRate.value = data.caregivers;
      ratingDialog.value = true;
    } else {
      if (data.caregivers && data.caregivers.length === 0) {
        success('You have already reviewed all caregivers for this booking!', 'Already Reviewed');
      } else {
        success(data.message || 'Unable to review this booking at this time.', 'Info');
      }
    }
  } catch (error) {
    success('Unable to load review form. Please try again.', 'Error');
  }
};

const handleRatingSubmitted = (data) => {
  success('Thank you for your feedback!', 'Review Submitted');
  // Reload completed bookings to update any UI changes
  loadCompletedBookings();
};

const downloadReceipt = (bookingId) => {
  // Open receipt PDF in new tab
  window.open(`/api/receipts/${bookingId}/download`, '_blank');
};

const viewReceipt = (bookingId) => {
  // View receipt PDF in new tab (inline)
  window.open(`/api/receipts/${bookingId}`, '_blank');
};

// Payment Information Section Helpers
const getPaymentHistoryItems = () => {
  try {
    // Get all bookings and format for payment history table
    // Only use bookings arrays that actually exist: pending, confirmed, completed
    const allBookings = [
      ...(pendingBookings.value || []),
      ...(confirmedBookings.value || []),
      ...(completedBookings.value || [])
    ];

return allBookings.map(booking => ({
      id: booking.id,
      date: booking.date || (booking.service_date ? new Date(booking.service_date).toLocaleDateString() : 'N/A'),
      service: booking.service || booking.serviceType || booking.service_type || 'Caregiver Service',
      amount: booking.price || booking.total_budget || (booking.hourly_rate * booking.durationDays * 12) || 0,
      paymentStatus: booking.payment_status || 'unpaid',  // Use payment_status (with underscore)
      status: booking.status
    })).sort((a, b) => b.id - a.id); // Sort by ID descending (newest first)
  } catch (error) {
    return [];
  }
};

const formatPrice = (value) => {
  try {
    if (!value || isNaN(value)) return '0.00';
    return Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  } catch (error) {
    return '0.00';
  }
};

const getPaidBookingsCount = () => {
  try {
    const allBookings = [
      ...(pendingBookings.value || []),
      ...(confirmedBookings.value || []),
      ...(completedBookings.value || [])
    ];
    const count = allBookings.filter(b => b.payment_status === 'paid').length;
    return count;
  } catch (error) {
    return 0;
  }
};

const getPendingPaymentsCount = () => {
  try {
    // Count approved bookings that haven't been paid yet
    const count = (confirmedBookings.value || []).filter(b => 
      b.payment_status !== 'paid'  // Use payment_status (with underscore)
    ).length;
    return count;
  } catch (error) {
    return 0;
  }
};

const exportAnalyticsPdf = async () => {
  try {
    exportingAnalytics.value = true;
    
    // Prepare analytics data for PDF export
    const pdfData = {
      clientName: props.userData?.name || 'Client',
      totalSpent: analyticsData.value.totalSpent,
      thisMonth: analyticsData.value.thisMonth,
      avgPerMonth: analyticsData.value.avgPerMonth,
      totalBookings: analyticsData.value.totalBookings,
      totalHours: analyticsData.value.totalHours,
      activeCaregivers: analyticsData.value.activeCaregivers,
      selectedYear: selectedYear.value,
      period: spendingChartPeriod.value
    };
    
    const response = await fetch('/api/reports/client-analytics-pdf', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(pdfData)
    });
    
    if (response.ok) {
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `CAS-Client-Analytics-${new Date().toISOString().split('T')[0]}.pdf`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
      success('Analytics report exported successfully!');
    } else {
      throw new Error('Failed to generate PDF');
    }
  } catch (err) {
    notification.value = { show: true, message: 'Failed to export analytics report', type: 'error' };
  } finally {
    exportingAnalytics.value = false;
  }
};

const getServicePrice = (serviceType) => {
  // Pricing breakdown:
  // With Referral: Caregiver $28 + Agency $10.50 + Marketing $1 + Training $0.50 = $40/hr
  // Without Referral: Caregiver $28 + Agency $16.50 + Training $0.50 = $45/hr
  const prices = {
    'Caregiver': '$45 per hour',
    'Elderly Care': '$45 per hour',
    'Personal Care': '$45 per hour',
    'Companion Care': '$45 per hour',
    'Childcare': '$45 per hour',
    'Housekeeping': '$25 per hour', 
    'House Cleaning': '$25 per hour',
    'Personal Assistant': '$30 per hour'
  };
  return prices[serviceType] || '$45 per hour';
};

const getHourlyRate = (serviceType) => {
  // PRICING BREAKDOWN:
  // Without referral (with training center):
  //   Caregiver $28 + Agency $16.50 + Training $0.50 = $45/hr
  // Without referral (NO training center):
  //   Caregiver $28 + Agency $17.00 (gets training's $0.50) = $45/hr
  // With referral (with training center):
  //   Caregiver $28 + Agency $10.50 + Marketing $1 + Training $0.50 = $40/hr
  // With referral (NO training center):
  //   Caregiver $28 + Agency $11.00 (gets training's $0.50) + Marketing $1 = $40/hr
  const rates = {
    'Caregiver': 45,
    'Elderly Care': 45,
    'Personal Care': 45,
    'Companion Care': 45,
    'Childcare': 45,
    'Housekeeping': 25,
    'House Cleaning': 25,
    'Personal Assistant': 30
  };
  const baseRate = rates[serviceType] || 45;
  const discountedRate = baseRate - referralDiscount.value;
  return discountedRate > 0 ? `$${discountedRate}` : `$${baseRate}`;
};

const getOriginalRate = (serviceType) => {
  const rates = {
    'Caregiver': 45,
    'Elderly Care': 45,
    'Personal Care': 45,
    'Companion Care': 45,
    'Childcare': 45,
    'Housekeeping': 25,
    'House Cleaning': 25,
    'Personal Assistant': 30
  };
  return rates[serviceType] || 45;
};

// Get default rate for a service type (used in booking details)
const getDefaultRate = (serviceType) => {
  // Pricing: Caregiver $28 + Agency $16.50 + Training $0.50 = $45/hr
  const rates = {
    'Caregiver': 45,
    'Elderly Care': 45,
    'Personal Care': 45,
    'Companion Care': 45,
    'Childcare': 45,
    'Housekeeping': 25,
    'House Cleaning': 25,
    'Personal Assistant': 30
  };
  return rates[serviceType] || 45;
};

const getOriginalTotal = () => {
  if (!bookingData.value.serviceType || !bookingData.value.dutyType || !bookingData.value.durationDays) {
    return '';
  }
  
  const hoursPerDay = parseInt(bookingData.value.dutyType.split(' ')[0]) || 0;
  const days = bookingData.value.durationDays || 0;
  const originalRate = getOriginalRate(bookingData.value.serviceType);
  
  const total = hoursPerDay * days * originalRate;
  return total > 0 ? total.toLocaleString() : '';
};

const getTotalSavings = () => {
  if (!bookingData.value.serviceType || !bookingData.value.dutyType || !bookingData.value.durationDays) {
    return '';
  }
  
  const hoursPerDay = parseInt(bookingData.value.dutyType.split(' ')[0]) || 0;
  const days = bookingData.value.durationDays || 0;
  const savings = hoursPerDay * days * referralDiscount.value;
  
  return savings > 0 ? savings.toLocaleString() : '';
};

const getTotalCost = () => {
  if (!bookingData.value.serviceType || !bookingData.value.dutyType || !bookingData.value.durationDays) {
    return '';
  }
  
  // Pricing: Caregiver $28 + Agency $16.50 + Training $0.50 = $45/hr (no referral)
  // With referral: Caregiver $28 + Agency $10.50 + Marketing $1 + Training $0.50 = $40/hr
  const rates = {
    'Caregiver': 45,
    'Elderly Care': 45,
    'Personal Care': 45,
    'Companion Care': 45,
    'Childcare': 45,
    'Housekeeping': 25,
    'House Cleaning': 25,
    'Personal Assistant': 30
  };
  
  const hoursPerDay = parseInt(bookingData.value.dutyType.split(' ')[0]) || 0;
  const days = bookingData.value.durationDays || 0;
  const baseRate = rates[bookingData.value.serviceType] || 45;
  const discountedRate = baseRate - referralDiscount.value;
  const finalRate = discountedRate > 0 ? discountedRate : baseRate;
  
  const total = hoursPerDay * days * finalRate;
  return total > 0 ? `$${total.toLocaleString()}` : '';
};

// Calculate price for a booking object (used in dashboard widgets)
const getBookingPrice = (booking) => {
  if (!booking) return '0';
  
  // Extract hours from duty type (e.g., "8 Hours Duty" -> 8)
  const hoursMatch = booking.dutyType?.match(/(\d+)/) || booking.duty_type?.match(/(\d+)/);
  const hoursPerDay = hoursMatch ? parseInt(hoursMatch[1]) : 8;
  
  // Get duration days
  const days = booking.durationDays || booking.duration_days || 15;
  
  // Use the actual hourly rate from the booking (which includes referral discount if applied)
  // If not available, fall back to price property or calculate using default rate
  const hourlyRate = booking.hourlyRate || booking.hourly_rate;
  
  if (hourlyRate) {
    // Use the stored hourly rate (includes any referral discount)
    const total = hoursPerDay * days * hourlyRate;
    return total.toLocaleString();
  }
  
  // Fallback: use price if available
  if (booking.price) {
    return booking.price.toLocaleString();
  }
  
  // Last resort: calculate with default rate
  const total = hoursPerDay * days * 45;
  return total.toLocaleString();
};

// Calculate original price (before discount) for a booking
const getOriginalBookingPrice = (booking) => {
  if (!booking || !booking.referralDiscount || booking.referralDiscount === 0) return null;
  
  // Extract hours from duty type
  const hoursMatch = booking.dutyType?.match(/(\d+)/) || booking.duty_type?.match(/(\d+)/);
  const hoursPerDay = hoursMatch ? parseInt(hoursMatch[1]) : 8;
  
  // Get duration days
  const days = booking.durationDays || booking.duration_days || 15;
  
  // referralDiscount is the dollar amount per hour discount
  // Original price = current total + (discount per hour × hours × days)
  const currentPrice = parseFloat(getBookingPrice(booking).replace(/,/g, ''));
  const totalDiscount = booking.referralDiscount * hoursPerDay * days;
  const originalPrice = currentPrice + totalDiscount;
  
  return originalPrice.toLocaleString();
};

const updateBooking = async () => {
  try {
    const response = await fetch(`/api/bookings/${editingBookingId.value}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        service_type: editBookingData.value.serviceType,
        duty_type: editBookingData.value.dutyType,
        city: editBookingData.value.city,
        service_date: editBookingData.value.date,
        start_time: editBookingData.value.startingTime || null,
        duration_days: editBookingData.value.durationDays,
        client_age: editBookingData.value.clientAge,
        mobility_level: editBookingData.value.mobilityLevel,
        street_address: editBookingData.value.streetAddress,
        apartment_unit: editBookingData.value.apartmentUnit,
        special_instructions: editBookingData.value.notes
      })
    });
    
    if (response.ok) {
      success('Booking updated successfully!');
      editBookingDialog.value = false;
      await loadMyBookings();
      await loadCompletedBookings();
      await loadOngoingContracts();
    } else {
      const errorData = await response.json();
      alert('Error: ' + (errorData.message || 'Failed to update booking'));
    }
  } catch (error) {
    alert('Error updating booking. Please try again.');
  }
};

const viewBookingDetails = async (booking) => {
  try {
    const response = await fetch(`/api/bookings/${booking.id}`);
    const data = await response.json();
    if (data.success) {
      const bookingData = data.data;
      const hoursPerDay = extractHours(bookingData.duty_type);
      const hourlyRate = bookingData.hourly_rate || getDefaultRate(bookingData.service_type);
      const duration = bookingData.duration_days || 15;
      const total = hoursPerDay * duration * hourlyRate;
      
      // Check if referral discount was applied
      const referralDiscountAmount = bookingData.referral_discount_applied || 0;
      const hasReferralDiscount = referralDiscountAmount > 0;
      const originalRate = hasReferralDiscount ? (hourlyRate + referralDiscountAmount) : hourlyRate;
      const originalTotal = hoursPerDay * duration * originalRate;
      const totalSaved = hoursPerDay * duration * referralDiscountAmount;
      
      // Format submitted timestamp - prioritize submitted_at over created_at
      const timestampSource = bookingData.submitted_at || bookingData.created_at;
      let submittedAt = 'N/A';
      
      if (timestampSource) {
        try {
          const date = new Date(timestampSource);
          // Check if date is valid
          if (!isNaN(date.getTime())) {
            submittedAt = date.toLocaleString('en-US', {
              year: 'numeric',
              month: 'long',
              day: 'numeric',
              hour: 'numeric',
              minute: '2-digit',
              hour12: true
            });
          }
        } catch (e) {
        }
      }
      
      // Format starting time
      let startingTime = 'N/A';
      if (bookingData.starting_time || bookingData.start_time) {
        try {
          const timeStr = bookingData.starting_time || bookingData.start_time;
          if (timeStr.includes(':')) {
            const [hours, minutes] = timeStr.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            startingTime = `${displayHour}:${minutes} ${ampm}`;
          } else {
            startingTime = timeStr;
          }
        } catch (e) {
          startingTime = bookingData.starting_time || bookingData.start_time;
        }
      }

      // Parse selected days if available
      let selectedDays = {};
      if (bookingData.recurring_schedule) {
        try {
          selectedDays = typeof bookingData.recurring_schedule === 'string' 
            ? JSON.parse(bookingData.recurring_schedule) 
            : bookingData.recurring_schedule;
        } catch (e) {
        }
      } else if (bookingData.selected_days) {
        try {
          selectedDays = typeof bookingData.selected_days === 'string'
            ? JSON.parse(bookingData.selected_days)
            : bookingData.selected_days;
        } catch (e) {
        }
      }

      // Parse medical conditions - handle both string and array formats
      let medicalConditions = [];
      if (bookingData.medical_conditions) {
        if (Array.isArray(bookingData.medical_conditions)) {
          medicalConditions = bookingData.medical_conditions;
        } else if (typeof bookingData.medical_conditions === 'string') {
          // Split by comma or newline
          medicalConditions = bookingData.medical_conditions
            .split(/[,\n]/)
            .map(c => c.trim())
            .filter(c => c.length > 0);
        }
      }

      selectedBookingDetails.value = {
        service: bookingData.service_type,
        hoursPerDay,
        duration,
        hourlyRate,
        total,
        serviceDate: new Date(bookingData.service_date).toLocaleDateString(),
        startingTime,
        selectedDays,
        city: bookingData.borough || bookingData.city || 'N/A',
        streetAddress: bookingData.street_address || 'N/A',
        apartmentUnit: bookingData.apartment_unit || bookingData.unit || '',
        clientAge: bookingData.client_age || '',
        mobilityLevel: bookingData.mobility_level ? bookingData.mobility_level.charAt(0).toUpperCase() + bookingData.mobility_level.slice(1) : '',
        medicalConditions: medicalConditions,
        specialInstructions: bookingData.special_instructions || '',
        submittedAt,
        referralCode: bookingData.referral_code?.code || '',
        hasReferralDiscount,
        referralDiscount: referralDiscountAmount,
        originalRate,
        originalTotal,
        totalSavings: totalSaved
      };
      viewBookingDialog.value = true;
    }
  } catch (error) {
    // Fallback with booking data if available
    const hoursPerDay = booking.hoursPerDay || (booking.dutyType ? parseInt(booking.dutyType.split(' ')[0]) : 8);
    const hourlyRate = booking.hourlyRate || 45;
    const duration = booking.duration || booking.durationDays || 15;
    const total = booking.price || (hoursPerDay * duration * hourlyRate);
    
    // Parse medical conditions
    let medicalConditions = [];
    if (booking.medicalConditions) {
      if (Array.isArray(booking.medicalConditions)) {
        medicalConditions = booking.medicalConditions;
      } else if (typeof booking.medicalConditions === 'string') {
        medicalConditions = booking.medicalConditions.split(/[,\n]/).map(c => c.trim()).filter(c => c.length > 0);
      }
    }
    
    selectedBookingDetails.value = {
      service: booking.service || booking.serviceType || 'Care Service',
      hoursPerDay,
      duration,
      hourlyRate,
      total,
      serviceDate: booking.date || 'N/A',
      startingTime: booking.startingTime || booking.time || 'N/A',
      city: booking.location || booking.borough || 'N/A',
      streetAddress: booking.streetAddress || 'N/A',
      apartmentUnit: booking.apartmentUnit || '',
      clientAge: booking.clientAge || '',
      mobilityLevel: booking.mobilityLevel || '',
      medicalConditions: medicalConditions,
      specialInstructions: booking.specialInstructions || '',
      submittedAt: 'N/A',
      referralCode: '',
      hasReferralDiscount: false,
      referralDiscount: 0,
      originalRate: 45,
      originalTotal: total,
      totalSavings: 0
    };
    viewBookingDialog.value = true;
  }
};

const extractHours = (dutyType) => {
  if (dutyType && typeof dutyType === 'string') {
    const match = dutyType.match(/(\d+)\s*Hours?/i);
    return match ? parseInt(match[1]) : 8;
  }
  return 8;
};

// Helper for template usage
const extractHoursFromDuty = (dutyType) => {
  return extractHours(dutyType);
};

const formatTime = (timeStr) => {
  if (!timeStr) return 'N/A';
  try {
    if (timeStr.includes(':')) {
      const [hours, minutes] = timeStr.split(':');
      const hour = parseInt(hours);
      const ampm = hour >= 12 ? 'PM' : 'AM';
      const displayHour = hour % 12 || 12;
      return `${displayHour}:${minutes} ${ampm}`;
    }
    return timeStr;
  } catch (e) {
    return timeStr;
  }
};

const capitalizeDay = (dayKey) => {
  const dayNames = {
    'sunday': 'Sunday',
    'monday': 'Monday',
    'tuesday': 'Tuesday',
    'wednesday': 'Wednesday',
    'thursday': 'Thursday',
    'friday': 'Friday',
    'saturday': 'Saturday'
  };
  return dayNames[dayKey.toLowerCase()] || dayKey.charAt(0).toUpperCase() + dayKey.slice(1);
};

const hasEnabledDays = (selectedDays) => {
  if (!selectedDays || typeof selectedDays !== 'object') return false;
  return Object.values(selectedDays).some(day => day && day.enabled === true);
};

const editBooking = async (id) => {
  try {
    const response = await fetch(`/api/bookings/${id}`);
    const data = await response.json();
    if (data.success) {
      const booking = data.data;
      // Format starting time for time input
      let startingTime = '';
      if (booking.start_time) {
        try {
          const timeStr = booking.start_time;
          if (timeStr.includes(':')) {
            // If it's already in HH:MM format, use it directly
            startingTime = timeStr.substring(0, 5); // Get HH:MM part
          } else {
            startingTime = timeStr;
          }
        } catch (e) {
          startingTime = booking.start_time;
        }
      }

      editBookingData.value = {
        serviceType: booking.service_type,
        dutyType: booking.duty_type,
        city: booking.borough,
        date: booking.service_date,
        startingTime: startingTime,
        durationDays: booking.duration_days,
        clientAge: booking.client_age?.toString() || '',
        mobilityLevel: booking.mobility_level,
        streetAddress: booking.street_address,
        apartmentUnit: booking.apartment_unit,
        notes: booking.special_instructions
      };
      editingBookingId.value = id;
      editBookingDialog.value = true;
    }
  } catch (error) {
    alert('Error loading booking for edit.');
  }
};

const editCard = (id) => {
  alert('Edit card feature coming soon!');
};

const deleteCard = (id) => {
  if (confirm('Are you sure you want to remove this card?')) {
    alert('Card removed');
  }
};

const loadProfile = async () => {
  try {
    const response = await fetch(profileUrl.value);
    const data = await response.json();
    if (data.user) {
      // Set userId for avatar upload
      userId.value = data.user.id;
      
      // Set avatar if exists
      if (data.user.avatar) {
        userAvatar.value = data.user.avatar.startsWith('/') ? data.user.avatar : '/storage/' + data.user.avatar;
      }
      
      const nameParts = (data.user.name || '').split(' ');
      profileData.value = {
        firstName: nameParts[0] || '',
        lastName: nameParts.slice(1).join(' ') || '',
        email: data.user.email || '',
        phone: data.user.phone || '',
        address: data.user.address || '',
        city: data.user.city || '',
        county: data.user.borough || data.user.county || '',
        state: data.user.state || '',
        zip: data.user.zip_code || '',
        birthdate: data.user.date_of_birth || ''
      };
    } else {
      // Fallback data if no user found
      profileData.value = {
        firstName: 'John',
        lastName: 'Doe',
        email: 'client@demo.com',
        phone: '',
        address: '',
        city: '',
        county: '',
        state: '',
        zip: '',
        birthdate: ''
      };
    }
  } catch (error) {
    // Fallback data on error
    profileData.value = {
      firstName: 'John',
      lastName: 'Doe', 
      email: 'client@demo.com',
      phone: '',
      address: '',
      city: '',
      borough: '',
      state: '',
      zip: '',
      birthdate: ''
    };
  }
};

const saveProfile = async () => {
  try {
    if (!userId.value) {
      alert('User ID not available. Please refresh the page.');
      return;
    }
    
    // Build payload, only including fields with values
    const payload = {
      name: `${profileData.value.firstName} ${profileData.value.lastName}`.trim(),
      email: profileData.value.email || null,
      address: profileData.value.address || null,
      city: profileData.value.city || null,
      borough: profileData.value.county || null,
      state: profileData.value.state || 'New York',
      zip_code: profileData.value.zip || null,
      date_of_birth: profileData.value.birthdate || null
    };
    
    // Only include phone if it has a value, and ensure it's a string
    if (profileData.value.phone) {
      payload.phone = String(profileData.value.phone);
    }
    
    const response = await fetch(`/api/user/${userId.value}/profile`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
      alert('Error: ' + errorMessage);
    }
  } catch (error) {
    const errorMessage = error?.message || error?.toString() || 'Unknown error occurred';
    alert('Error saving profile: ' + errorMessage);
  }
};

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
  
  if (!userId.value) {
    alert('User ID not available. Please refresh the page.');
    return;
  }
  
  uploadingAvatar.value = true;
  
  try {
    const formData = new FormData();
    formData.append('avatar', file);
    
    const response = await fetch(`/api/user/${userId.value}/avatar`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: formData
    });
    
    const data = await response.json();
    
    if (response.ok && data.success) {
      userAvatar.value = data.avatar;
      success('Profile picture updated successfully!');
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

// Day selection helper functions
const toggleDay = (dayKey) => {
  bookingData.value.selectedDays[dayKey].enabled = !bookingData.value.selectedDays[dayKey].enabled;
  
  // If enabling a day and duty type is selected, calculate end time
  if (bookingData.value.selectedDays[dayKey].enabled && bookingData.value.dutyType) {
    const hours = getHoursFromDutyType(bookingData.value.dutyType);
    bookingData.value.selectedDays[dayKey].endTime = addHoursToTime(
      bookingData.value.selectedDays[dayKey].startTime,
      hours
    );
  }
};

const getDayLabel = (dayKey) => {
  const labels = {
    sunday: 'Sun',
    monday: 'Mon',
    tuesday: 'Tue',
    wednesday: 'Wed',
    thursday: 'Thu',
    friday: 'Fri',
    saturday: 'Sat'
  };
  return labels[dayKey] || dayKey;
};

const getFullDayName = (dayKey) => {
  const names = {
    sunday: 'Sunday',
    monday: 'Monday',
    tuesday: 'Tuesday',
    wednesday: 'Wednesday',
    thursday: 'Thursday',
    friday: 'Friday',
    saturday: 'Saturday'
  };
  return names[dayKey] || dayKey;
};

const getSelectedDaysCount = () => {
  return Object.values(bookingData.value.selectedDays).filter(day => day.enabled).length;
};

const toggleDayExpanded = (dayKey) => {
  // This can be used for future expansion if needed
};

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const openContactDialog = (booking) => {
  selectedBooking.value = booking;
  contactCaregiverDialog.value = true;
};

const openAdminContactDialog = () => {
  contactAdminDialog.value = true;
};

const handleBookingRequest = (caregiver) => {
  bookingData.value.caregiver = caregiver.name;
  bookingData.value.serviceType = caregiver.specialty.split(' ')[0] + ' ' + caregiver.specialty.split(' ')[1];
  currentSection.value = 'book-form';
};

const handleNotificationAction = (action) => {
};

const appliedReferralCodeId = ref(null);
const referralCodeError = ref('');

const applyReferralCode = async () => {
  if (!bookingData.value.referralCode) {
    return;
  }
  
  // Clear previous error
  referralCodeError.value = '';
  
  const code = bookingData.value.referralCode.toUpperCase().trim();
  
  try {
    const response = await fetch('/api/referral-codes/validate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ code })
    });
    
    const data = await response.json();
    
    if (data.valid) {
      // Pricing with referral: Caregiver $28 + Agency $10.50 + Marketing $1 + Training $0.50 = $40/hr
      // Without referral: Caregiver $28 + Agency $16.50 + Training $0.50 = $45/hr
      // Discount: $45 - $40 = $5/hr
      referralDiscount.value = parseFloat(data.data.discount_per_hour) || 5.00;
      appliedReferralCodeId.value = data.data.id;
      referralCodeError.value = '';
      success(`Referral code "${code}" applied successfully! You'll receive $${referralDiscount.value.toFixed(2)} off per hour.`);
    } else {
      referralDiscount.value = 0;
      appliedReferralCodeId.value = null;
      const errorMessage = data.message || 'Invalid referral code. Please check and try again.';
      referralCodeError.value = errorMessage;
      // Removed duplicate error toast - inline alert is shown instead
    }
  } catch (err) {
    // Fallback to static validation for demo purposes
    const validCodes = ['SAVE10', 'WELCOME20', 'FRIEND15', 'CARE25'];
    if (validCodes.includes(code)) {
      referralDiscount.value = 5.00;
      referralCodeError.value = '';
      success(`Referral code "${code}" applied successfully! You'll receive $5.00 off per hour.`);
    } else {
      referralCodeError.value = 'Invalid referral code. Please check and try again.';
      // Removed duplicate error toast - inline alert is shown instead
    }
  }
};

const fillDemoData = () => {
  const dutyTypes = ['8 Hours per Day', '12 Hours per Day', '24 Hours per Day'];
  const genderPrefs = ['no_preference', 'male', 'female'];
  const mobilityLevels = ['independent', 'needs_assistance', 'wheelchair_bound', 'bedridden'];
  const addresses = ['123 Main St', '456 Oak Ave', '789 Pine Rd', '321 Elm St', '654 Maple Dr', '890 Broadway', '567 Park Ave', '432 Madison Ave', '111 Lexington Ave', '999 5th Ave'];
  const apartments = ['Apt 4B', 'Unit 12', 'Suite 305', '2nd Floor', 'Penthouse', 'Apt 3A', 'Unit 8', 'Suite 201', '1st Floor'];
  const notes = [
    'Patient prefers morning care routine',
    'Needs assistance with medication reminders', 
    'Enjoys classical music and reading',
    'Requires help with meal preparation',
    'Prefers quiet and calm environment',
    'Likes to take walks in the afternoon',
    'Needs help with bathing and dressing',
    'Requires assistance with mobility exercises'
  ];
  
  // NY ZIP codes for demo
  const nyZipCodes = ['10001', '10002', '10003', '10004', '10005', '11201', '11203', '11204', '11205', '11206', '11354', '11355', '11375', '10451', '10452', '10301', '10302', '11101', '11501', '10501', '10701'];
  
  // Time ranges for day selection
  const timeRanges = [
    { start: '08:00', end: '16:00' },
    { start: '09:00', end: '17:00' },
    { start: '10:00', end: '18:00' },
    { start: '07:00', end: '15:00' },
    { start: '06:00', end: '14:00' }
  ];

  const rand = (arr) => arr[Math.floor(Math.random() * arr.length)];
  const randNum = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;
  const futureDate = () => {
    const date = new Date();
    date.setDate(date.getDate() + randNum(1, 30));
    return date.toISOString().split('T')[0];
  };
  
  const randomTime = () => {
    const hour = randNum(6, 10).toString().padStart(2, '0');
    const minute = rand([0, 15, 30, 45]).toString().padStart(2, '0');
    return `${hour}:${minute}`;
  };
  
  // Randomly select days (at least 3 days)
  const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
  const numDaysToSelect = randNum(3, 7);
  const selectedDaysArray = days.sort(() => 0.5 - Math.random()).slice(0, numDaysToSelect);
  const timeRange = rand(timeRanges);
  
  // Build selectedDays object
  const selectedDaysObj = {
    sunday: { enabled: selectedDaysArray.includes('sunday'), startTime: timeRange.start, endTime: timeRange.end },
    monday: { enabled: selectedDaysArray.includes('monday'), startTime: timeRange.start, endTime: timeRange.end },
    tuesday: { enabled: selectedDaysArray.includes('tuesday'), startTime: timeRange.start, endTime: timeRange.end },
    wednesday: { enabled: selectedDaysArray.includes('wednesday'), startTime: timeRange.start, endTime: timeRange.end },
    thursday: { enabled: selectedDaysArray.includes('thursday'), startTime: timeRange.start, endTime: timeRange.end },
    friday: { enabled: selectedDaysArray.includes('friday'), startTime: timeRange.start, endTime: timeRange.end },
    saturday: { enabled: selectedDaysArray.includes('saturday'), startTime: timeRange.start, endTime: timeRange.end }
  };
  
  const zipcode = rand(nyZipCodes);

  bookingData.value = {
    serviceType: 'Caregiver',
    dutyType: rand(dutyTypes),
    zipcode: zipcode,
    date: futureDate(),
    startingTime: randomTime(),
    durationDays: rand([15, 30, 60, 90]),
    selectedDays: selectedDaysObj,
    genderPreference: rand(genderPrefs),
    specificSkills: skillsList.slice(0, randNum(1, 4)),
    clientAge: randNum(25, 95).toString(),
    mobilityLevel: rand(mobilityLevels),
    medicalConditions: medicalConditionsList.slice(0, randNum(0, 3)),
    transportationNeeded: Math.random() > 0.5,
    streetAddress: rand(addresses),
    apartmentUnit: Math.random() > 0.3 ? rand(apartments) : '',
    notes: rand(notes),
    referralCode: Math.random() > 0.7 ? rand(['SAVE10', 'WELCOME20', 'FRIEND15', 'CARE25']) : ''
  };
  
  // Trigger ZIP code lookup after data is set
  nextTick(() => {
    lookupZipCode();
  });
  
  success('Demo data filled! Review and adjust as needed.');
};

watch(bookingTab, (newVal) => {
  localStorage.setItem('clientBookingTab', newVal);
});

watch(currentSection, (newVal) => {
  localStorage.setItem('clientSection', newVal);
  if (newVal === 'analytics') {
    setTimeout(initSpendingChart, 300);
  }
});

// Helper function to add hours to a time string
const addHoursToTime = (timeString, hoursToAdd) => {
  if (!timeString) return '';
  
  const [hours, minutes] = timeString.split(':').map(Number);
  let newHours = hours + hoursToAdd;
  
  // Handle overflow past 24 hours
  if (newHours >= 24) {
    newHours = newHours % 24;
  }
  
  return `${String(newHours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
};

// Helper function to get hours from duty type
const getHoursFromDutyType = (dutyType) => {
  if (dutyType.includes('8 Hours')) return 8;
  if (dutyType.includes('12 Hours')) return 12;
  if (dutyType.includes('24 Hours')) return 24;
  return 8; // default
};

// Watch for duty type changes to recalculate all end times
watch(() => bookingData.value.dutyType, (newDutyType) => {
  if (!newDutyType) return;
  
  const hours = getHoursFromDutyType(newDutyType);
  
  // Recalculate end time for all enabled days
  Object.keys(bookingData.value.selectedDays).forEach(day => {
    if (bookingData.value.selectedDays[day].enabled && bookingData.value.selectedDays[day].startTime) {
      bookingData.value.selectedDays[day].endTime = addHoursToTime(
        bookingData.value.selectedDays[day].startTime,
        hours
      );
    }
  });
});

// Watch for start time changes on each day
Object.keys(bookingData.value.selectedDays).forEach(day => {
  watch(() => bookingData.value.selectedDays[day].startTime, (newStartTime) => {
    if (bookingData.value.dutyType && newStartTime && bookingData.value.selectedDays[day].enabled) {
      const hours = getHoursFromDutyType(bookingData.value.dutyType);
      bookingData.value.selectedDays[day].endTime = addHoursToTime(newStartTime, hours);
    }
  });
});

onMounted(() => {
  loadNYLocationData();
  loadAvailableYears();
  loadProfile();
  loadClientStats();
  loadOngoingContracts();
  loadMyBookings();
  loadCompletedBookings();
  loadCaregivers();
  loadNotificationCount();
  loadTopCaregivers();
  
  // Check if returning from successful payment
  const paymentCompleted = localStorage.getItem('payment_completed');
  const paymentTimestamp = localStorage.getItem('payment_timestamp');
  
  if (paymentCompleted === 'true') {
    // Check if payment was recent (within last 5 minutes)
    const timeDiff = Date.now() - parseInt(paymentTimestamp || '0');
    if (timeDiff < 300000) { // 5 minutes
      
      // Force reload stats and bookings
      setTimeout(() => {
        loadClientStats();
        loadMyBookings();
        loadCompletedBookings();
      }, 500);
      
      // Show success message
      setTimeout(() => {
        success('Payment successful! Your dashboard has been updated.');
      }, 1000);
    }
    
    // Clear the flags
    localStorage.removeItem('payment_completed');
    localStorage.removeItem('payment_booking_id');
    localStorage.removeItem('payment_timestamp');
  }
  
  // Check for account creation success message
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('account_created') === 'true') {
    success('Account created successfully! You can now login with Google or manually.');
    // Clean up URL
    window.history.replaceState({}, document.title, window.location.pathname);
  }
  
  if (currentSection.value === 'analytics') {
    setTimeout(initSpendingChart, 300);
  }
  // Refresh notification count every 30 seconds
  setInterval(loadNotificationCount, 30000);
  
  // Watch for spending chart period and year changes
  watch(spendingChartPeriod, () => {
    if (currentSection.value === 'analytics') {
      loadSpendingData();
    }
  });
  
  watch(selectedYear, () => {
    if (currentSection.value === 'analytics') {
      loadSpendingData();
    }
  });
  
  // Refresh bookings and stats every 15 seconds to catch payment updates
  setInterval(() => {
    loadClientStats();
    loadMyBookings();
    loadCompletedBookings();
  }, 15000);
});

const availableYears = ref([]);
const spendingChart = ref(null);

const topCaregivers = ref([]);

const loadTopCaregivers = async () => {
  try {
    const response = await fetch(`/api/client/top-caregivers?client_id=${userId.value}`);
    const data = await response.json();
    topCaregivers.value = data.caregivers || [];
  } catch (error) {
  }
};

let spendingChartInstance = null;

const loadAvailableYears = async () => {
  try {
    const response = await fetch('/api/client/available-years');
    const data = await response.json();
    availableYears.value = data.years || [new Date().getFullYear()];
    selectedYear.value = availableYears.value[0]; // Set current year as default
  } catch (error) {
    availableYears.value = [new Date().getFullYear()];
    selectedYear.value = new Date().getFullYear();
  }
};

const loadSpendingData = async () => {
  try {
    const response = await fetch(`/api/client/spending-data?period=${spendingChartPeriod.value}&year=${selectedYear.value}`);
    const data = await response.json();
    
    if (spendingChartInstance) {
      spendingChartInstance.destroy();
    }
    
    if (spendingChart.value && window.Chart) {
      const ctx = spendingChart.value.getContext('2d');
      spendingChartInstance = new window.Chart(ctx, {
        type: 'line',
        data: {
          labels: data.labels || ['No Data'],
          datasets: [{
            label: 'Spending',
            data: data.data || [0],
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 3,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, ticks: { callback: (value) => '$' + value } }
          }
        }
      });
    }
  } catch (error) {
    // Fallback to static data if API fails
    if (spendingChart.value && window.Chart) {
      const ctx = spendingChart.value.getContext('2d');
      spendingChartInstance = new window.Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
          datasets: [{
            label: 'Spending',
            data: [320, 450, 380, 520],
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 3,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, ticks: { callback: (value) => '$' + value } }
          }
        }
      });
    }
  }
};

const initSpendingChart = () => {
  if (spendingChart.value && currentSection.value === 'analytics') {
    loadSpendingData();
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Strikethrough styling for original prices */
.original-price-small {
  font-size: 0.75rem;
  color: #999;
  text-decoration: line-through;
  font-weight: 500;
  display: block;
  margin-bottom: 2px;
}

.original-price-chip {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.6);
  text-decoration: line-through;
  font-weight: 500;
  margin-right: 4px;
}

.original-price-inline {
  font-size: 0.875rem;
  color: #999;
  text-decoration: line-through;
  font-weight: 500;
  margin-right: 8px;
}

.original-price-total {
  font-size: 1rem;
  color: #999;
  text-decoration: line-through;
  font-weight: 500;
  display: block;
  margin-bottom: 4px;
}

/* Glowing Pay Now Button Animation */
@keyframes background-glow-pulse {
  0%, 100% {
    opacity: 0.5;
    transform: translate(-50%, -50%) scale(1);
  }
  50% {
    opacity: 0.9;
    transform: translate(-50%, -50%) scale(1.15);
  }
}

.pay-now-glow {
  position: relative !important;
  overflow: visible !important;
  z-index: 1 !important;
  isolation: isolate;
}

.pay-now-glow::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 130%;
  height: 150%;
  background: radial-gradient(ellipse, rgba(239, 68, 68, 0.7) 0%, rgba(239, 68, 68, 0.4) 35%, rgba(239, 68, 68, 0.2) 60%, transparent 80%);
  border-radius: 12px;
  z-index: -1;
  animation: background-glow-pulse 2s ease-in-out infinite;
  pointer-events: none;
  filter: blur(25px);
  will-change: transform, opacity;
}

.pay-now-glow:hover::before {
  animation: background-glow-pulse 1.5s ease-in-out infinite;
}

.pay-now-glow:hover {
  transform: translateY(-2px) !important;
  transition: transform 0.2s ease !important;
}

/* Booking Tabs Mobile Responsive */
.booking-tabs {
  overflow-x: auto;
  flex-wrap: nowrap;
}

.booking-tab {
  min-width: auto !important;
  flex: 0 0 auto;
  padding: 12px 16px !important;
}

@media (max-width: 600px) {
  .booking-tab {
    font-size: 0.75rem !important;
    padding: 8px 12px !important;
  }
  
  .tab-text {
    font-size: 0.75rem;
  }
  
  .booking-tabs .v-tab__slider {
    height: 2px;
  }
}

@media (max-width: 400px) {
  .booking-tab {
    padding: 6px 10px !important;
  }
  
  .tab-text {
    font-size: 0.7rem;
  }
}

/* Caregiver Contact Dialog Responsive Styles */
.caregiver-contact-dialog {
  border-radius: 16px;
  overflow: hidden;
}

.caregiver-contact-header {
  border-radius: 0 !important;
}

.contact-list .v-list-item {
  border-radius: 12px;
  transition: background 0.2s ease;
}

.contact-list .v-list-item:hover {
  background: #f8fafc;
}

/* Assignment Progress Card Styles */
.assignment-progress-card {
  transition: all 0.2s ease;
}

.assignment-progress-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

@media (max-width: 600px) {
  .caregiver-contact-dialog {
    border-radius: 0;
  }
  
  .contact-list .v-list-item {
    padding: 12px 0 !important;
  }
  
  .assignment-progress-card {
    margin-top: 12px;
  }
}

.book-now-btn {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
  font-size: 1.125rem !important;
  font-weight: 600 !important;
  border-radius: 16px !important;
  text-transform: none !important;
  letter-spacing: -0.01em !important;
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.25) !important;
  height: 56px !important;
}

.book-now-btn :deep(.v-btn__content) {
  display: flex;
  align-items: center;
  justify-content: center;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.card-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a1a1a;
  letter-spacing: -0.01em;
  line-height: 1.4;
}

.card-specialty {
  font-size: 0.95rem;
  color: #6b7280;
  font-weight: 500;
  line-height: 1.5;
}

.card-meta {
  font-size: 0.875rem;
  color: #666;
  font-weight: 500;
}

.rating-text {
  font-size: 0.9rem;
  color: #4b5563;
  font-weight: 600;
}

.card-price {
  font-size: 1.75rem;
  font-weight: 800;
  color: #10b981;
  letter-spacing: -0.02em;
}

.price-unit {
  font-size: 0.95rem;
  font-weight: 500;
  color: #6b7280;
}

.status-chip {
  position: absolute;
  top: 16px;
  right: 16px;
  font-weight: 600;
  font-size: 0.75rem;
}

.info-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-text {
  font-size: 0.9rem;
  color: #6b7280;
  font-weight: 500;
}

.view-details-btn {
  border-radius: 8px !important;
  font-weight: 600 !important;
  text-transform: none !important;
  letter-spacing: 0 !important;
  font-size: 1rem !important;
  height: 44px !important;
}

.card-header {
  background: #fafafa;
  border-bottom: 1px solid #f0f0f0;
}

.activity-card {
  border-radius: 24px !important;
  border: 1px solid #f0f0f0 !important;
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

.filter-card {
  border-radius: 20px !important;
  border: 2px solid #e5e7eb !important;
  background: #fafbfc !important;
  box-shadow: none !important;
}

.search-field {
  background: white;
  border-radius: 12px;
}

.caregivers-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1a1a1a;
  letter-spacing: -0.02em;
}

.caregiver-count {
  font-size: 1.25rem;
  color: #6b7280;
  font-weight: 500;
}

.caregiver-card {
  border-radius: 16px !important;
  border: 1px solid #e5e7eb !important;
  transition: all 0.3s ease;
  background: white;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
}

.caregiver-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
}

.caregiver-image {
  position: relative;
}

:deep(.v-data-table) {
  border-radius: 0 !important;
  font-size: 0.95rem;
}

:deep(.v-data-table thead th) {
  background: #fafafa !important;
  color: #2563eb !important;
  font-weight: 700 !important;
  font-size: 0.875rem !important;
  letter-spacing: 0.02em !important;
  text-transform: uppercase !important;
  padding: 20px 16px !important;
  border-bottom: 2px solid #e0e0e0 !important;
}

:deep(.v-data-table tbody tr) {
  transition: all 0.2s ease;
}

:deep(.v-data-table tbody tr:hover) {
  background: #f8f9fa !important;
  transform: scale(1.005);
}

:deep(.v-data-table tbody td) {
  font-size: 0.95rem !important;
  font-weight: 500 !important;
  padding: 20px 16px !important;
  color: #333 !important;
}

:deep(.v-data-table-footer) {
  padding: 16px !important;
  border-top: 1px solid #f0f0f0 !important;
}

.booking-card,
.payment-card,
.add-card {
  border-radius: 24px !important;
  border: 1px solid #f0f0f0 !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
}

.booking-card {
  margin-bottom: 16px;
  transition: all 0.3s ease;
}

.booking-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
}

.payment-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  transition: all 0.3s ease;
}

.payment-card .card-name,
.payment-card .card-number,
.payment-card .card-meta {
  color: white !important;
}

.payment-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(102, 126, 234, 0.3) !important;
}

.primary-card {
  border: 2px solid #10b981 !important;
}

.add-card {
  border: 2px dashed #d1d5db !important;
  cursor: pointer;
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.add-card:hover {
  border-color: #3b82f6 !important;
  background: #f8fafc;
}

.transaction-amount {
  font-weight: 700;
  font-size: 1.05rem;
  color: #10b981;
}

.analytics-stat {
  text-align: center;
  padding: 24px 0;
}

.analytics-value {
  font-size: 3rem;
  font-weight: 800;
  color: #2563eb;
  letter-spacing: -0.02em;
}

.analytics-label {
  font-size: 1rem;
  color: #666;
  font-weight: 500;
  margin-top: 8px;
}

.analytics-breakdown {
  padding: 16px 0;
}

.breakdown-item {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  font-size: 1rem;
  font-weight: 500;
  border-bottom: 1px solid #f0f0f0;
}

.breakdown-item:last-child {
  border-bottom: none;
}

.breakdown-value {
  font-weight: 700;
  color: #2563eb;
}

.card-number {
  font-size: 1.5rem;
  font-weight: 500;
  letter-spacing: 4px;
  margin: 20px 0;
  font-family: 'Courier New', monospace;
}

.payment-card {
  background: linear-gradient(135deg, #1a1f36 0%, #2d3561 100%);
  border-radius: 20px;
  padding: 28px;
  color: white;
  min-height: 220px;
  position: relative;
  overflow: hidden;
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

.billing-address-card {
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
}

.address-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
}

.address-type {
  font-size: 0.875rem;
  color: #6b7280;
}

.address-line {
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

.bookings-header {
  margin-bottom: 24px;
}

.bookings-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1a1a1a;
  letter-spacing: -0.02em;
}

.bookings-subtitle {
  font-size: 1rem;
  color: #6b7280;
  margin-top: 4px;
}

.booking-tab {
  font-weight: 600;
  text-transform: none;
  font-size: 1rem;
}

/* Mobile Tabs Responsive */
@media (max-width: 960px) {
  .bookings-header {
    margin-bottom: 1rem !important;
  }

  .bookings-title {
    font-size: 1.5rem !important;
  }

  .bookings-subtitle {
    font-size: 0.875rem !important;
  }

  /* Compact tabs on mobile */
  :deep(.v-tabs) {
    margin-bottom: 1rem !important;
  }

  :deep(.v-tab) {
    font-size: 0.8125rem !important;
    padding: 0.625rem 0.75rem !important;
    min-width: auto !important;
    flex: 1 !important;
  }

  :deep(.v-tab .v-icon) {
    font-size: 18px !important;
    margin-right: 0.375rem !important;
  }

  :deep(.v-tabs__wrapper) {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
  }

  /* Ensure tabs are visible on mobile */
  :deep(.v-slide-group__content) {
    display: flex !important;
    width: 100% !important;
  }
}

@media (max-width: 480px) {
  .bookings-title {
    font-size: 1.25rem !important;
  }

  .bookings-subtitle {
    font-size: 0.8125rem !important;
  }

  /* Very compact tabs on small mobile */
  :deep(.v-tab) {
    font-size: 0.75rem !important;
    padding: 0.5rem 0.375rem !important;
    min-width: 60px !important;
  }

  :deep(.v-tab .v-icon) {
    font-size: 16px !important;
    margin-right: 0.25rem !important;
  }

  /* Show condensed text on very small screens */
  :deep(.v-tab .text-xs) {
    font-size: 0.7rem !important;
  }

  :deep(.v-chip.ml-1) {
    margin-left: 2px !important;
  }
}

.booking-request-card {
  border-radius: 16px !important;
  border: 1px solid #e5e7eb !important;
  transition: all 0.3s ease;
  margin-bottom: 16px;
}

.booking-request-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
}

.booking-id {
  font-size: 0.875rem;
  margin: 0;
}

.booking-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: flex-end;
}

.booking-info {
  padding-left: 16px;
}

.booking-service-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a1a;
  letter-spacing: -0.01em;
  margin-bottom: 8px;
}

.booking-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.detail-text {
  font-size: 0.95rem;
  color: #4b5563;
  font-weight: 500;
}

.status-chip-large {
  font-weight: 600;
  padding: 8px 16px;
  width: 100%;
}

.analytics-header {
  margin-bottom: 24px;
}

.analytics-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1a1a1a;
  letter-spacing: -0.02em;
}

.analytics-subtitle {
  font-size: 1rem;
  color: #6b7280;
  margin-top: 4px;
}

.quick-stat-card {
  border-radius: 16px !important;
  border: 1px solid #e5e7eb !important;
  transition: all 0.3s ease;
}

.quick-stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 8px 0;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.caregiver-stat-card {
  border-radius: 16px !important;
  border: 1px solid #e5e7eb !important;
  transition: all 0.3s ease;
}

.caregiver-stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
}

.caregiver-stat-name {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1a1a1a;
}

.caregiver-stat-specialty {
  font-size: 0.875rem;
  color: #6b7280;
  margin-top: 2px;
}

.caregiver-stats-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.caregiver-stat-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.stat-text {
  font-size: 0.875rem;
  color: #4b5563;
  font-weight: 500;
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

.client-revenue-item {
  margin-bottom: 24px;
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

.notifications-header {
  margin-bottom: 24px;
}

.notifications-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1a1a1a;
  letter-spacing: -0.02em;
}

.notifications-subtitle {
  font-size: 1rem;
  color: #6b7280;
  margin-top: 4px;
}

.notification-card {
  border-radius: 16px !important;
  border: 1px solid #e5e7eb !important;
  transition: all 0.3s ease;
  margin-bottom: 16px;
}

.notification-card.unread {
  background: #f8fafc;
}

.notification-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
}

.notification-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1a1a1a;
}

.notification-message {
  font-size: 0.95rem;
  color: #4b5563;
  line-height: 1.5;
  margin: 0;
}

.notification-time {
  font-size: 0.875rem;
  color: #6b7280;
}

.profile-stat {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 12px 0;
  font-size: 0.95rem;
}

/* Modern Table Styling */
.modern-activity-card {
  border-radius: 16px !important;
  border: 1px solid #e5e7eb !important;
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

.date-col { width: 20% !important; }
.service-col { width: 25% !important; }
.caregiver-col { width: 25% !important; }
.amount-col { width: 15% !important; }
.status-col { width: 15% !important; }

.modern-row {
  transition: all 0.2s ease !important;
  border-bottom: 1px solid #f3f4f6 !important;
}

.modern-row:hover {
  background: #f9fafb !important;
  transform: translateX(2px) !important;
}

.modern-cell {
  padding: 16px !important;
  font-size: 0.875rem !important;
  color: #374151 !important;
  border-bottom: none !important;
}

.date-cell {
  font-weight: 500 !important;
  color: #6b7280 !important;
}

/* Mobile Responsive Table Styles */
@media (max-width: 960px) {
  .modern-activity-table {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .modern-activity-table .v-table__wrapper {
    overflow-x: auto !important;
  }

  /* Stack table rows as cards on mobile */
  .modern-activity-table table {
    min-width: 600px;
  }

  /* Make pagination mobile-friendly */
  :deep(.modern-activity-table .v-data-table-footer) {
    flex-wrap: wrap !important;
    padding: 0.75rem !important;
  }

  :deep(.modern-activity-table .v-data-table-footer__items-per-page) {
    font-size: 0.75rem !important;
  }

  :deep(.modern-activity-table .v-data-table-footer__info) {
    font-size: 0.75rem !important;
  }
}

@media (max-width: 480px) {
  .modern-card-header {
    padding: 1rem !important;
  }

  .modern-title {
    font-size: 1rem !important;
  }

  .activity-count {
    font-size: 0.6875rem !important;
  }

  /* Ensure table cards are full width */
  .modern-activity-card {
    margin: 0 !important;
    border-radius: 12px !important;
  }

  /* Compact table header on mobile */
  .modern-header-cell {
    font-size: 0.6875rem !important;
    padding: 0.75rem 0.5rem !important;
  }

  /* Compact table cells on mobile */
  .modern-cell {
    padding: 0.75rem 0.5rem !important;
    font-size: 0.8125rem !important;
  }

  /* Smaller transaction amount on mobile */
  .transaction-amount {
    font-size: 0.9375rem !important;
  }

  /* Compact status chips */
  .modern-type-chip {
    font-size: 0.6875rem !important;
    padding: 2px 6px !important;
    height: 20px !important;
  }

  /* Pagination mobile adjustments */
  :deep(.modern-activity-table .v-data-table-footer) {
    padding: 0.5rem !important;
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 0.5rem !important;
  }

  :deep(.modern-activity-table .v-data-table-footer__items-per-page) {
    order: 2 !important;
    width: 100% !important;
    justify-content: center !important;
  }

  :deep(.modern-activity-table .v-data-table-footer__info) {
    order: 1 !important;
    text-align: center !important;
    font-size: 0.6875rem !important;
  }

  :deep(.modern-activity-table .v-data-table-footer__pagination) {
    order: 3 !important;
    justify-content: center !important;
    width: 100% !important;
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
  .v-card {
    margin-bottom: 1rem !important;
  }

  .card-header {
    padding: 1rem !important;
  }

  .section-title {
    font-size: 1.25rem !important;
  }

  /* Compact buttons on mobile */
  .v-btn {
    font-size: 0.875rem !important;
    padding: 0.625rem 1rem !important;
  }

  /* Contract items mobile */
  .contract-item {
    padding: 1rem !important;
  }

  .contract-caregiver {
    font-size: 0.875rem !important;
  }

  .contract-service {
    font-size: 0.75rem !important;
  }

  .contract-dates {
    font-size: 0.6875rem !important;
  }
}

@media (max-width: 480px) {
  /* Stats row spacing */
  .mb-4 {
    margin-bottom: 1rem !important;
  }

  .mt-2 {
    margin-top: 0.75rem !important;
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

  .section-title {
    font-size: 1.125rem !important;
  }
}

.service-cell, .caregiver-cell {
  font-weight: 600 !important;
}

.amount-cell {
  font-weight: 700 !important;
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

/* Ongoing Contracts Styles */
.contract-item {
  border-bottom: 1px solid #f3f4f6;
  transition: all 0.2s ease;
}

.contract-item:last-child {
  border-bottom: none;
}

.contract-item:hover {
  background: #f9fafb;
}

.contract-caregiver {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1f2937;
  line-height: 1.3;
}

.contract-service {
  font-size: 0.8rem;
  color: #6b7280;
  font-weight: 500;
  margin-top: 2px;
}

.contract-dates {
  font-size: 0.75rem;
  color: #9ca3af;
  margin-top: 2px;
}

/* Professional Booking Form Styles */
.booking-header-section {
  margin-bottom: 2rem;
}

.booking-brand-header {
  background: linear-gradient(135deg, #0B4FA2 0%, #1e40af 50%, #3b82f6 100%);
  border-radius: 20px;
  padding: 2.5rem 2rem;
  color: white;
  position: relative;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(11, 79, 162, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.booking-brand-header::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -20%;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
  border-radius: 50%;
  z-index: 1;
}

.booking-brand-header::after {
  content: '';
  position: absolute;
  bottom: -30%;
  left: -10%;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
  border-radius: 50%;
  z-index: 1;
}

.brand-logo-section {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
  position: relative;
  z-index: 2;
}

.brand-logo-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 16px;
  padding: 0.75rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.brand-logo-image {
  height: 80px;
  width: auto;
  object-fit: contain;
  filter: brightness(0) invert(1) drop-shadow(0 2px 8px rgba(0, 0, 0, 0.15));
}

.brand-title {
  font-size: 2rem;
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.02em;
}

.brand-subtitle {
  font-size: 1rem;
  margin: 0.25rem 0 0 0;
  opacity: 0.9;
  font-weight: 500;
}

.trust-indicators {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
  position: relative;
  z-index: 2;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.trust-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1rem;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.75rem 1.25rem;
  border-radius: 12px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  opacity: 1;
}

.trust-item:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.trust-item .v-icon {
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.professional-form-card {
  border-radius: 20px !important;
  border: 1px solid rgba(11, 79, 162, 0.08) !important;
  box-shadow: 0 8px 32px rgba(11, 79, 162, 0.12) !important;
  overflow: hidden;
  background: white !important;
}

.form-section {
  position: relative;
  padding: 2rem 2.5rem;
  background: #fafbfc;
  border-radius: 16px;
  margin-bottom: 1.5rem;
  transition: all 0.3s ease;
}

.form-section:not(:last-child) {
  border-bottom: none;
}

.section-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.75rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.section-icon {
  width: 48px;
  height: 48px;
  background: white;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 2px solid #e5e7eb;
}

.section-icon .v-icon {
  font-size: 24px !important;
  color: #0B4FA2 !important;
}

.section-info {
  flex: 1;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0B4FA2;
  margin: 0 0 0.5rem 0;
  letter-spacing: -0.02em;
}

.section-subtitle {
  font-size: 1rem;
  color: #64748b;
  margin: 0;
  font-weight: 500;
  line-height: 1.5;
}

.section-content {
  margin-left: 0;
  margin-top: 1.5rem;
}

.form-field {
  margin-bottom: 0.75rem;
}

.field-label {
  display: block;
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.5rem;
  letter-spacing: 0.01em;
  text-transform: uppercase;
  font-size: 0.875rem;
  letter-spacing: 0.05em;
}

.professional-select :deep(.v-field),
.professional-field :deep(.v-field),
.professional-textarea :deep(.v-field) {
  border-radius: 12px !important;
  border: 2px solid #e5e7eb !important;
  background: white !important;
  transition: all 0.3s ease !important;
  min-height: 56px !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

.professional-select :deep(.v-field:hover),
.professional-field :deep(.v-field:hover),
.professional-textarea :deep(.v-field:hover) {
  border-color: #0B4FA2 !important;
  background: white !important;
  box-shadow: 0 4px 16px rgba(11, 79, 162, 0.15) !important;
  transform: translateY(-1px);
}

.professional-select :deep(.v-field--focused),
.professional-field :deep(.v-field--focused),
.professional-textarea :deep(.v-field--focused) {
  border-color: #0B4FA2 !important;
  background: white !important;
  box-shadow: 0 0 0 4px rgba(11, 79, 162, 0.1), 0 4px 16px rgba(11, 79, 162, 0.15) !important;
}

.professional-select :deep(.v-field__input),
.professional-field :deep(.v-field__input),
.professional-textarea :deep(.v-field__input) {
  font-size: 1.05rem !important;
  font-weight: 500 !important;
  color: #1f2937 !important;
  padding: 14px 18px !important;
}

.professional-select :deep(.v-field__placeholder),
.professional-field :deep(.v-field__placeholder),
.professional-textarea :deep(.v-field__placeholder) {
  color: #9ca3af !important;
  font-size: 1rem !important;
  font-weight: 400 !important;
}

.professional-select :deep(.v-chip) {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%) !important;
  color: #1e40af !important;
  font-weight: 600 !important;
  border-radius: 8px !important;
}

.switch-field {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}

.professional-switch {
  margin-top: 0.5rem;
}

.professional-switch :deep(.v-switch__track) {
  background: #e5e7eb !important;
}

.professional-switch :deep(.v-switch__thumb) {
  background: white !important;
}

.form-actions {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  padding: 1.5rem 1.5rem;
  text-align: center;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.action-buttons .v-btn {
  height: 60px !important;
  min-width: 160px !important;
  padding: 0 2rem !important;
  font-weight: 600 !important;
  border-radius: 14px !important;
  text-transform: none !important;
  font-size: 1rem !important;
}

.demo-fill-btn,
.demo-fill-btn.v-btn {
  background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%) !important;
  color: white !important;
  border: none !important;
  letter-spacing: 0.5px !important;
  box-shadow: 0 4px 12px rgba(107, 114, 128, 0.25) !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.demo-fill-btn:hover,
.demo-fill-btn.v-btn:hover {
  box-shadow: 0 8px 20px rgba(107, 114, 128, 0.35), 0 4px 8px rgba(107, 114, 128, 0.2) !important;
  transform: translateY(-2px) !important;
  background: linear-gradient(135deg, #5b6573 0%, #8b92a0 100%) !important;
}

.submit-btn,
.submit-btn.v-btn {
  background: linear-gradient(135deg, #0B4FA2 0%, #2563eb 100%) !important;
  color: white !important;
  font-size: 1.125rem !important;
  font-weight: 700 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.05em !important;
  box-shadow: 0 8px 24px rgba(11, 79, 162, 0.35), 0 4px 8px rgba(11, 79, 162, 0.2) !important;
  min-width: 220px !important;
}

.submit-btn:hover,
.submit-btn.v-btn:hover {
  box-shadow: 0 12px 32px rgba(11, 79, 162, 0.4), 0 6px 12px rgba(11, 79, 162, 0.25) !important;
  transform: translateY(-3px) !important;
  background: linear-gradient(135deg, #094d8f 0%, #1e40af 100%) !important;
}

.cancel-btn,
.cancel-btn.v-btn {
  border: 2px solid #cbd5e1 !important;
  color: #475569 !important;
  background: white !important;
  letter-spacing: 0.02em !important;
}

.cancel-btn:hover,
.cancel-btn.v-btn:hover {
  border-color: #94a3b8 !important;
  background: #f1f5f9 !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}

.demo-btn {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
  color: white !important;
  font-size: 1rem !important;
  font-weight: 600 !important;
  border-radius: 14px !important;
  text-transform: none !important;
  letter-spacing: 0.02em !important;
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.25) !important;
  height: 60px !important;
  padding: 0 2rem !important;
  min-width: 160px !important;
  transition: all 0.3s ease !important;
}

.demo-btn:hover {
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35) !important;
  transform: translateY(-2px) !important;
  background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
}

.security-notice {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  font-size: 0.95rem;
  color: #64748b;
  margin-top: 1.5rem;
  padding: 1rem;
  background: rgba(16, 185, 129, 0.1);
  border-radius: 12px;
  border: 1px solid rgba(16, 185, 129, 0.2);
  font-weight: 500;
}

.price-breakdown {
  width: 100%;
}

.breakdown-card {
  border-radius: 12px !important;
  border: 1px solid #e5e7eb !important;
  background: white !important;
  width: 100%;
}

.breakdown-header {
  text-align: center;
}

.breakdown-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #374151;
  margin: 0;
}

.breakdown-items {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.breakdown-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 0;
}

.item-label {
  font-size: 0.9rem;
  color: #6b7280;
  font-weight: 500;
}

.item-value {
  font-size: 0.9rem;
  color: #374151;
  font-weight: 600;
}

.total-item {
  padding: 8px 0;
}

.total-label {
  font-size: 1rem;
  font-weight: 700;
  color: #374151;
}

.total-value {
  font-size: 1.1rem;
  font-weight: 800;
  color: #059669;
}

.item-value-container {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.original-price,
.original-total,
.original-rate {
  font-size: 0.85rem;
  color: #9ca3af;
  text-decoration: line-through;
  font-weight: 500;
}

.discounted-price,
.discounted-rate {
  color: #059669 !important;
  font-weight: 700 !important;
}

.savings-value {
  color: #059669 !important;
  font-weight: 700 !important;
}

.zip-location-display {
  margin-top: 0.25rem !important;
  font-size: 1.1rem !important;
  color: #64748b !important;
  font-weight: 700 !important;
  padding: 0 !important;
  display: block !important;
}

.referral-discount-banner {
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  border: 1px solid #059669;
  border-radius: 8px;
  padding: 12px 16px;
}

.discount-text {
  font-weight: 600;
  color: #047857;
  font-size: 0.95rem;
}

.pricing-rate-display {
  display: flex;
  align-items: center;
  gap: 8px;
}

.discount-chip {
  font-weight: 600 !important;
  font-size: 0.7rem !important;
}

.total-savings-chip {
  font-weight: 700 !important;
  font-size: 0.75rem !important;
}

.service-summary {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
}

.summary-label {
  font-size: 1rem;
  color: #6b7280;
  font-weight: 500;
}

.summary-value {
  font-size: 1rem;
  color: #374151;
  font-weight: 600;
}

.total-summary {
  padding: 16px 0 8px 0;
}

.total-label {
  font-size: 1.1rem;
  font-weight: 700;
  color: #374151;
}

.total-value {
  font-size: 1.25rem;
  font-weight: 800;
  color: #059669;
}

.scrollable-content {
  max-height: calc(80vh - 140px);
  overflow-y: auto;
}

.booking-details-view {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.detail-section {
  background: #f8fafc;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #e2e8f0;
}

.section-header {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 16px 0;
  padding-bottom: 8px;
  border-bottom: 2px solid #e2e8f0;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-value {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
}

.instructions-text {
  background: white;
  border-radius: 8px;
  padding: 16px;
  border: 1px solid #e2e8f0;
  font-size: 0.95rem;
  color: #475569;
  line-height: 1.6;
  min-height: 60px;
}

.pricing-section {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border: 1px solid #0ea5e9;
}

.pricing-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.pricing-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
}

.pricing-label {
  font-size: 1rem;
  font-weight: 500;
  color: #475569;
}

.pricing-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
}

.total-item {
  border-top: 2px solid #0ea5e9;
  padding-top: 16px;
  margin-top: 8px;
}

.total-label {
  font-size: 1.2rem;
  font-weight: 800;
  color: #1e293b;
}

.total-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: #059669;
}

/* Mobile Responsive - Book Service Form (Facebook-style like registration) */
@media (max-width: 960px) {
  .booking-header-section {
    margin-bottom: 1rem !important;
  }

  .booking-brand-header {
    padding: 1.5rem 1.25rem !important;
    border-radius: 16px !important;
  }

  .brand-logo-section {
    gap: 1rem !important;
    margin-bottom: 1rem !important;
  }

  .brand-logo-wrapper {
    padding: 0.5rem !important;
  }

  .brand-logo-image {
    height: 60px !important;
  }

  .brand-title {
    font-size: 1.5rem !important;
  }

  .brand-subtitle {
    font-size: 0.875rem !important;
  }

  .trust-indicators {
    flex-direction: column !important;
    gap: 0.75rem !important;
    margin-top: 1rem !important;
    padding-top: 1rem !important;
  }

  .trust-item {
    font-size: 0.875rem !important;
    padding: 0.625rem 1rem !important;
    width: 100% !important;
  }

  .trust-item .v-icon {
    font-size: 18px !important;
  }

  .professional-form-card {
    border-radius: 0 !important;
    box-shadow: none !important;
    border: none !important;
    margin: 0 !important;
  }

  .form-section {
    padding: 1.25rem !important;
    margin-bottom: 1rem !important;
    border-radius: 0 !important;
    background: white !important;
  }

  .section-header {
    gap: 0.75rem !important;
    margin-bottom: 1rem !important;
    padding-bottom: 0.75rem !important;
  }

  .section-icon {
    width: 40px !important;
    height: 40px !important;
  }

  .section-icon .v-icon {
    font-size: 20px !important;
  }

  .section-title {
    font-size: 1.125rem !important;
    margin-bottom: 0.25rem !important;
  }

  .section-subtitle {
    font-size: 0.8125rem !important;
  }

  .section-content {
    margin-top: 1rem !important;
  }

  .form-field {
    margin-bottom: 0.625rem !important;
  }

  .field-label {
    font-size: 0.8125rem !important;
    margin-bottom: 0.375rem !important;
  }

  .professional-select :deep(.v-field),
  .professional-field :deep(.v-field),
  .professional-textarea :deep(.v-field) {
    min-height: 44px !important;
    border-radius: 6px !important;
    border: 1px solid #ccd0d5 !important;
    background: #f5f6f7 !important;
    box-shadow: none !important;
  }

  .professional-select :deep(.v-field:hover),
  .professional-field :deep(.v-field:hover),
  .professional-textarea :deep(.v-field:hover) {
    border-color: #ccd0d5 !important;
    background: #f5f6f7 !important;
    box-shadow: none !important;
    transform: none !important;
  }

  .professional-select :deep(.v-field--focused),
  .professional-field :deep(.v-field--focused),
  .professional-textarea :deep(.v-field--focused) {
    border-color: #1877f2 !important;
    background: #ffffff !important;
    box-shadow: 0 0 0 2px #e7f3ff !important;
  }

  .professional-select :deep(.v-field__input),
  .professional-field :deep(.v-field__input),
  .professional-textarea :deep(.v-field__input) {
    font-size: 0.9375rem !important;
    padding: 0.75rem !important;
  }

  .action-buttons {
    flex-direction: column !important;
    gap: 0.75rem !important;
    margin-bottom: 1rem !important;
  }

  .demo-fill-btn,
  .submit-btn,
  .cancel-btn {
    width: 100% !important;
    font-size: 0.9375rem !important;
    padding: 0.75rem 1.5rem !important;
    border-radius: 6px !important;
    min-height: 48px !important;
  }

  .submit-btn {
    background: #1877f2 !important;
  }
}

@media (max-width: 480px) {
  body {
    background: #f0f2f5 !important;
  }

  .booking-header-section {
    margin-bottom: 0.75rem !important;
  }

  .booking-brand-header {
    padding: 1.25rem 1rem !important;
    border-radius: 0 !important;
    margin: 0 -1rem !important;
    width: calc(100% + 2rem) !important;
  }

  .brand-logo-section {
    gap: 0.75rem !important;
    margin-bottom: 0.75rem !important;
  }

  .brand-logo-image {
    height: 50px !important;
  }

  .brand-title {
    font-size: 1.25rem !important;
  }

  .brand-subtitle {
    font-size: 0.8125rem !important;
  }

  .trust-indicators {
    gap: 0.5rem !important;
    margin-top: 0.75rem !important;
    padding-top: 0.75rem !important;
  }

  .trust-item {
    font-size: 0.8125rem !important;
    padding: 0.5rem 0.75rem !important;
  }

  .trust-item .v-icon {
    font-size: 16px !important;
  }

  .professional-form-card {
    border-radius: 0 !important;
    margin: 0 -1rem !important;
    width: calc(100% + 2rem) !important;
  }

  .form-section {
    padding: 1rem !important;
    margin-bottom: 0.75rem !important;
  }

  .section-header {
    gap: 0.625rem !important;
    margin-bottom: 0.875rem !important;
    padding-bottom: 0.625rem !important;
  }

  .section-icon {
    width: 36px !important;
    height: 36px !important;
  }

  .section-icon .v-icon {
    font-size: 18px !important;
  }

  .section-title {
    font-size: 1rem !important;
  }

  .section-subtitle {
    font-size: 0.75rem !important;
  }

  .section-content {
    margin-top: 0.875rem !important;
  }

  .form-field {
    margin-bottom: 0.5rem !important;
  }

  .field-label {
    font-size: 0.75rem !important;
    margin-bottom: 0.25rem !important;
  }

  .professional-select :deep(.v-field),
  .professional-field :deep(.v-field),
  .professional-textarea :deep(.v-field) {
    min-height: 40px !important;
    border-radius: 6px !important;
  }

  .professional-select :deep(.v-field__input),
  .professional-field :deep(.v-field__input),
  .professional-textarea :deep(.v-field__input) {
    font-size: 0.9375rem !important;
    padding: 0.75rem !important;
  }

  /* Day buttons mobile */
  .day-buttons-row {
    gap: 0.5rem !important;
  }

  .day-button {
    font-size: 0.8125rem !important;
    padding: 0.5rem 0.75rem !important;
    min-width: auto !important;
    flex: 1 !important;
  }

  .selected-day-item {
    padding: 0.75rem !important;
    font-size: 0.8125rem !important;
  }

  .time-input {
    font-size: 0.8125rem !important;
  }
}

/* Day Selection Styles */
.day-selector-container {
  width: 100%;
}

.day-buttons-row {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  width: 100%;
}

.day-button {
  flex: 1;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.day-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.selected-days-list {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1rem;
}

.selected-day-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  background: white;
  border-radius: 8px;
  margin-bottom: 0.5rem;
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
  gap: 0.75rem;
}

.selected-day-item:hover {
  border-color: #3b82f6;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}

.selected-day-item:last-child {
  margin-bottom: 0;
}

.day-name {
  font-size: 0.95rem;
  color: #1e293b;
  font-weight: 600;
  width: 90px;
  flex-shrink: 0;
}

.selected-days-display {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1rem;
}

.day-schedule-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.day-schedule-item:hover {
  border-color: #3b82f6;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}

.day-schedule-item .day-name {
  width: 100px;
  color: #1e293b;
  font-weight: 600;
}

.day-schedule-item .day-time {
  flex: 1;
  text-align: right;
  color: #64748b;
  font-size: 0.9rem;
}

.time-input {
  width: 110px;
}

.time-input :deep(.v-field) {
  min-height: 36px;
}

.time-input :deep(.v-field__input) {
  font-size: 0.875rem;
  padding: 6px 10px;
}

.time-separator {
  color: #6b7280;
  font-weight: 400;
  margin: 0 0.25rem;
}

.chevron-btn {
  margin-left: auto;
}
</style>

