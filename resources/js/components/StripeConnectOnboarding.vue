<template>
  <div class="onboarding-container pa-8">
    <v-container class="py-12" style="max-width: 640px;">
      <v-card elevation="4" class="pa-8">
        <div class="text-center mb-6">
          <v-icon size="72" color="primary">mdi-briefcase-account</v-icon>
        </div>
        <h1 class="text-h4 font-weight-bold text-center mb-4">Payroll onboarding (W-2)</h1>
        <p class="text-body-1 text-medium-emphasis text-center mb-6">
          Stripe Connect payouts are retired for caregivers. As a W-2 employee, you submit payroll information
          (legal name, tax ID, address, bank details for direct deposit) securely in the caregiver dashboard.
        </p>
        <v-alert type="info" variant="tonal" class="mb-6 text-left" density="comfortable">
          Use <strong>Payroll onboarding</strong> on your dashboard. Data is encrypted at rest; we do not log full SSN or account numbers.
        </v-alert>
        <div class="d-flex flex-column flex-sm-row gap-3 justify-center">
          <v-btn color="primary" size="large" prepend-icon="mdi-arrow-right" @click="goDashboard">
            Open caregiver dashboard
          </v-btn>
          <v-btn variant="text" color="grey-darken-1" prepend-icon="mdi-arrow-left" @click="goHome">
            Home
          </v-btn>
        </div>
      </v-card>
    </v-container>
  </div>
</template>

<script setup>
const dashboardUrl = '/caregiver/dashboard-vue?payroll_onboarding=1';

const goDashboard = () => {
  window.location.href = dashboardUrl;
};

const goHome = () => {
  window.location.href = '/';
};
</script>

<style scoped>
.onboarding-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}
</style>
