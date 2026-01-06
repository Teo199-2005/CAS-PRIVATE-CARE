import './bootstrap';
import { createApp } from 'vue';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Import components
import ClientDashboard from './components/ClientDashboard.vue';
import CaregiverDashboard from './components/CaregiverDashboard.vue';
import AdminDashboard from './components/AdminDashboard.vue';
import AdminStaffDashboard from './components/AdminStaffDashboard.vue';
import AdminSettings from './components/AdminSettings.vue';
import MarketingDashboard from './components/MarketingDashboard.vue';
import TrainingDashboard from './components/TrainingDashboard.vue';

const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                colors: {
                    primary: '#3b82f6',
                    secondary: '#10b981',
                    accent: '#1e40af',
                    error: '#dc2626',
                    info: '#0ea5e9',
                    success: '#10b981',
                    warning: '#f59e0b',
                },
            },
        },
    },
});

// Initialize Vue apps for dashboards
if (document.getElementById('client-dashboard-app')) {
    const app = createApp({
        components: {
            ClientDashboard,
        },
    });
    app.use(vuetify);
    app.mount('#client-dashboard-app');
}

if (document.getElementById('caregiver-dashboard-app')) {
    const app = createApp({
        components: {
            CaregiverDashboard,
        },
    });
    app.use(vuetify);
    app.mount('#caregiver-dashboard-app');
}

if (document.getElementById('admin-dashboard-app')) {
    const app = createApp({
        components: {
            AdminDashboard,
        },
    });
    app.use(vuetify);
    app.mount('#admin-dashboard-app');
}

if (document.getElementById('admin-settings-app')) {
    const app = createApp({
        components: {
            AdminSettings,
        },
    });
    app.use(vuetify);
    app.mount('#admin-settings-app');
}

if (document.getElementById('marketing-dashboard-app')) {
    const app = createApp({
        components: {
            MarketingDashboard,
        },
    });
    app.use(vuetify);
    app.mount('#marketing-dashboard-app');
}

if (document.getElementById('training-dashboard-app')) {
    const app = createApp({
        components: {
            TrainingDashboard,
        },
    });
    app.use(vuetify);
    app.mount('#training-dashboard-app');
}

if (document.getElementById('admin-staff-dashboard-app')) {
    const app = createApp({
        components: {
            AdminStaffDashboard,
        },
    });
    app.use(vuetify);
    app.mount('#admin-staff-dashboard-app');
}
