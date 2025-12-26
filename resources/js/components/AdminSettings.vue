<template>
  <dashboard-template
    user-role="admin"
    user-name="Admin User"
    user-initials="AU"
    welcome-message="Settings"
    subtitle="System and Security Configuration"
    header-title="Settings"
    header-subtitle="Manage platform settings and security"
    :nav-items="navItems"
    :current-section="currentSection"
    @section-change="currentSection = $event"
    @logout="logout"
  >
    <!-- System Settings Section -->
    <div v-if="currentSection === 'system'">
      <v-row>
        <v-col cols="12" md="8">
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-8">
              <span class="section-title error--text">Platform Settings</span>
            </v-card-title>
            <v-card-text class="pa-8">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.platformName" label="Platform Name" variant="outlined" />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.supportEmail" label="Support Email" variant="outlined" type="email" />
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field v-model="settings.commissionRate" label="Commission Rate (%)" variant="outlined" type="number" />
                </v-col>
                <v-col cols="12" md="6">
                  <v-select v-model="settings.defaultCurrency" :items="['USD', 'EUR', 'GBP']" label="Default Currency" variant="outlined" />
                </v-col>
                <v-col cols="12">
                  <v-switch v-model="settings.maintenanceMode" label="Maintenance Mode" color="error" />
                </v-col>
                <v-col cols="12">
                  <v-switch v-model="settings.emailNotifications" label="Email Notifications" color="error" />
                </v-col>
              </v-row>
              <v-btn color="error" class="mt-4" size="large" @click="saveSettings">Save Settings</v-btn>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-8">
              <span class="section-title error--text">System Status</span>
            </v-card-title>
            <v-card-text class="pa-8">
              <div class="system-status-item">
                <div class="d-flex justify-space-between mb-2">
                  <span>Server Status</span>
                  <v-chip color="success" size="small">Online</v-chip>
                </div>
              </div>
              <div class="system-status-item">
                <div class="d-flex justify-space-between mb-2">
                  <span>Database</span>
                  <v-chip color="success" size="small">Connected</v-chip>
                </div>
              </div>
              <div class="system-status-item">
                <div class="d-flex justify-space-between mb-2">
                  <span>Payment Gateway</span>
                  <v-chip color="success" size="small">Active</v-chip>
                </div>
              </div>
              <div class="system-status-item">
                <div class="d-flex justify-space-between mb-2">
                  <span>Email Service</span>
                  <v-chip color="warning" size="small">Limited</v-chip>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Security Settings Section -->
    <div v-if="currentSection === 'security'">
      <v-row>
        <v-col cols="12" md="8">
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-8">
              <span class="section-title error--text">Security Settings</span>
            </v-card-title>
            <v-card-text class="pa-8">
              <v-switch v-model="settings.twoFactorAuth" label="Two-Factor Authentication" color="error" class="mb-4" />
              <v-switch v-model="settings.loginNotifications" label="Login Notifications" color="error" class="mb-4" />
              <v-switch v-model="settings.suspiciousActivityAlerts" label="Suspicious Activity Alerts" color="error" class="mb-4" />
              <v-text-field v-model="settings.sessionTimeout" label="Session Timeout (minutes)" variant="outlined" type="number" class="mb-4" />
              <v-text-field v-model="settings.maxLoginAttempts" label="Max Login Attempts" variant="outlined" type="number" class="mb-4" />
              <v-text-field v-model="settings.passwordMinLength" label="Minimum Password Length" variant="outlined" type="number" class="mb-4" />
              <v-switch v-model="settings.requirePasswordComplexity" label="Require Password Complexity" color="error" class="mb-4" />
              <v-btn color="error" size="large" @click="saveSettings">Save Security Settings</v-btn>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card elevation="0">
            <v-card-title class="card-header pa-8">
              <span class="section-title error--text">Security Logs</span>
            </v-card-title>
            <v-card-text class="pa-8">
              <div class="security-log-item mb-3">
                <div class="d-flex align-center mb-1">
                  <v-icon color="success" size="small" class="mr-2">mdi-check-circle</v-icon>
                  <span class="log-text">Admin login successful</span>
                </div>
                <div class="text-caption text-grey">2 hours ago</div>
              </div>
              <div class="security-log-item mb-3">
                <div class="d-flex align-center mb-1">
                  <v-icon color="warning" size="small" class="mr-2">mdi-alert</v-icon>
                  <span class="log-text">Failed login attempt</span>
                </div>
                <div class="text-caption text-grey">1 day ago</div>
              </div>
              <div class="security-log-item mb-3">
                <div class="d-flex align-center mb-1">
                  <v-icon color="info" size="small" class="mr-2">mdi-information</v-icon>
                  <span class="log-text">Password changed</span>
                </div>
                <div class="text-caption text-grey">3 days ago</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Backup & Maintenance Section -->
    <div v-if="currentSection === 'backup'">
      <v-row>
        <v-col cols="12" md="6">
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-8">
              <span class="section-title error--text">Database Backup</span>
            </v-card-title>
            <v-card-text class="pa-8">
              <v-select v-model="backupSettings.frequency" :items="['Daily', 'Weekly', 'Monthly']" label="Backup Frequency" variant="outlined" class="mb-4" />
              <v-text-field v-model="backupSettings.retentionDays" label="Retention Period (days)" variant="outlined" type="number" class="mb-4" />
              <v-switch v-model="backupSettings.autoBackup" label="Automatic Backup" color="error" class="mb-4" />
              <v-btn color="error" size="large" prepend-icon="mdi-backup-restore" @click="createBackup" class="mr-4">Create Backup Now</v-btn>
              <v-btn color="warning" size="large" prepend-icon="mdi-download" @click="downloadBackup">Download Latest</v-btn>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card elevation="0" class="mb-6">
            <v-card-title class="card-header pa-8">
              <span class="section-title error--text">System Maintenance</span>
            </v-card-title>
            <v-card-text class="pa-8">
              <v-switch v-model="settings.maintenanceMode" label="Maintenance Mode" color="error" class="mb-4" />
              <v-textarea v-model="maintenanceMessage" label="Maintenance Message" variant="outlined" rows="3" class="mb-4" />
              <v-btn color="warning" size="large" prepend-icon="mdi-wrench" @click="clearCache" class="mr-4">Clear Cache</v-btn>
              <v-btn color="info" size="large" prepend-icon="mdi-refresh" @click="restartServices">Restart Services</v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Notifications Section -->
    <div v-if="currentSection === 'notifications'">
      <v-card elevation="0">
        <v-card-title class="card-header pa-8">
          <span class="section-title error--text">Notification Settings</span>
        </v-card-title>
        <v-card-text class="pa-8">
          <v-row>
            <v-col cols="12" md="6">
              <h3 class="mb-4">Email Notifications</h3>
              <v-switch v-model="notifications.newUserRegistration" label="New User Registration" color="error" class="mb-2" />
              <v-switch v-model="notifications.bookingCreated" label="Booking Created" color="error" class="mb-2" />
              <v-switch v-model="notifications.paymentReceived" label="Payment Received" color="error" class="mb-2" />
              <v-switch v-model="notifications.systemAlerts" label="System Alerts" color="error" class="mb-2" />
            </v-col>
            <v-col cols="12" md="6">
              <h3 class="mb-4">SMS Notifications</h3>
              <v-switch v-model="notifications.smsAlerts" label="SMS Alerts" color="error" class="mb-2" />
              <v-switch v-model="notifications.emergencyNotifications" label="Emergency Notifications" color="error" class="mb-2" />
              <v-text-field v-model="notifications.adminPhone" label="Admin Phone Number" variant="outlined" class="mb-4" />
            </v-col>
          </v-row>
          <v-btn color="error" size="large" @click="saveSettings">Save Notification Settings</v-btn>
        </v-card-text>
      </v-card>
    </div>
  </dashboard-template>
</template>

<script setup>
import { ref } from 'vue';
import DashboardTemplate from './DashboardTemplate.vue';

const currentSection = ref('system');

const navItems = [
  { icon: 'mdi-cog', title: 'System Settings', value: 'system' },
  { icon: 'mdi-shield-account', title: 'Security', value: 'security' },
  { icon: 'mdi-backup-restore', title: 'Backup & Maintenance', value: 'backup' },
  { icon: 'mdi-bell', title: 'Notifications', value: 'notifications' },
];

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

const logout = () => {
  window.location.href = '/login';
};

const saveSettings = () => {
  alert('Settings saved successfully!');
};

const createBackup = () => {
  alert('Database backup created successfully!');
};

const downloadBackup = () => {
  alert('Downloading latest backup...');
};

const clearCache = () => {
  alert('Cache cleared successfully!');
};

const restartServices = () => {
  if (confirm('Are you sure you want to restart system services? This may cause temporary downtime.')) {
    alert('Services restarted successfully!');
  }
};
</script>

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

.card-header {
  background: #fafafa;
  border-bottom: 1px solid #f0f0f0;
}

.system-status-item {
  margin-bottom: 16px;
  padding: 12px 0;
  border-bottom: 1px solid #f3f4f6;
}

.system-status-item:last-child {
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
</style>