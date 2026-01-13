<template>
  <v-card class="stat-card" elevation="0">
    <v-card-text class="pa-8">
      <div class="stat-icon mb-5" :class="iconClass">
        <v-icon color="white" size="36">{{ icon }}</v-icon>
      </div>
      <div class="stat-value mb-2">{{ value }}</div>
      <div class="stat-label mb-3">{{ label }}</div>
      <div v-if="change" class="stat-change" :class="changeColor">
        <v-icon size="small">{{ changeIcon }}</v-icon> {{ change }}
      </div>
      <div v-if="date" class="stat-date">{{ date }}</div>
    </v-card-text>
  </v-card>
</template>

<script setup>
defineProps({
  icon: String,
  value: [String, Number],
  label: String,
  change: String,
  changeColor: String,
  changeIcon: String,
  iconClass: { type: String, default: '' },
  date: String
});
</script>

<style scoped>
/* Font is loaded at app level - no need to import here */
* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.stat-card {
  border-radius: 24px !important;
  border: 1px solid #c5c5c5ff;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  background: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 120px;
  height: 120px;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.03) 0%, transparent 70%);
  border-radius: 50%;
  transform: translate(35%, -35%);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08) !important;
  border-color: #e0e0e0;
}

.stat-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.2);
}

.stat-icon.success {
  background: linear-gradient(135deg, #10b981, #059669);
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.2);
}

.stat-icon.deep-purple {
  background: linear-gradient(135deg, #7B1FA2, #6A1B9A);
  box-shadow: 0 4px 16px rgba(123, 31, 162, 0.2);
}

.stat-icon.purple {
  background: linear-gradient(135deg, #9C27B0, #7B1FA2);
  box-shadow: 0 4px 16px rgba(156, 39, 176, 0.2);
}

.stat-icon.error {
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  box-shadow: 0 4px 16px rgba(220, 38, 38, 0.2);
}

.stat-icon.grey-darken-2 {
  background: linear-gradient(135deg, #616161, #424242);
  box-shadow: 0 4px 16px rgba(97, 97, 97, 0.2);
}

.stat-value {
  font-size: 2.5rem;
  font-weight: 700;
  letter-spacing: -0.04em;
  color: #1a1a1a;
  line-height: 1;
}

.stat-label {
  font-size: 0.95rem;
  color: #666;
  font-weight: 500;
  letter-spacing: -0.01em;
}

.stat-change {
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.stat-date {
  font-size: 0.8rem;
  color: #888;
  font-weight: 500;
  margin-top: 2px;
}

/* Mobile Responsive Styles */
@media (max-width: 960px) {
  .stat-card {
    border-radius: 16px !important;
  }

  .stat-card .v-card-text {
    padding: 1rem !important;
  }

  .stat-icon {
    width: 48px !important;
    height: 48px !important;
    border-radius: 12px !important;
  }

  .stat-icon .v-icon {
    font-size: 24px !important;
  }

  .stat-value {
    font-size: 1.75rem !important;
    margin-bottom: 0.5rem !important;
  }

  .stat-label {
    font-size: 0.8125rem !important;
    margin-bottom: 0.5rem !important;
  }

  .stat-change {
    font-size: 0.75rem !important;
  }

  .stat-date {
    font-size: 0.7rem !important;
  }
}

@media (max-width: 480px) {
  .stat-card .v-card-text {
    padding: 0.875rem !important;
  }

  .stat-icon {
    width: 40px !important;
    height: 40px !important;
    margin-bottom: 0.75rem !important;
  }

  .stat-icon .v-icon {
    font-size: 20px !important;
  }

  .stat-value {
    font-size: 1.5rem !important;
    margin-bottom: 0.375rem !important;
  }

  .stat-label {
    font-size: 0.75rem !important;
    margin-bottom: 0.375rem !important;
  }

  .stat-change {
    font-size: 0.6875rem !important;
  }

  .stat-date {
    font-size: 0.65rem !important;
  }
}
</style>
