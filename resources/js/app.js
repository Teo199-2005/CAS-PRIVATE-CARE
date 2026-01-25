import './bootstrap';
import { createApp, defineAsyncComponent } from 'vue';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import Chart from 'chart.js/auto';
import { configureErrorHandling } from './error-handler';
window.Chart = Chart;

// Import animation utilities for scroll animations, ripple effects, etc.
import { 
    initScrollAnimations, 
    initScrollProgress, 
    initRippleEffect, 
    initParallax
} from './animation-utils';

// Initialize global animation utilities
document.addEventListener('DOMContentLoaded', () => {
    // Initialize scroll-triggered animations
    initScrollAnimations({
        threshold: 0.15,
        rootMargin: '-50px 0px'
    });
    
    // Initialize ripple effect for buttons with .btn-ripple class
    initRippleEffect('.btn-ripple, .ripple-effect');
    
    // Initialize scroll progress indicator
    initScrollProgress();
    
    // Initialize parallax for elements with .parallax class
    initParallax('.parallax, .parallax-section');
    
    // Battery optimization: Pause animations when tab not visible
    document.addEventListener('visibilitychange', () => {
        document.body.classList.toggle('page-hidden', document.hidden);
        document.documentElement.toggleAttribute('data-page-hidden', document.hidden);
    });
    
    // Performance optimization: Pause animations during rapid scrolling
    let scrollTimer;
    window.addEventListener('scroll', () => {
        document.body.classList.add('is-scrolling');
        document.documentElement.setAttribute('data-scrolling', '');
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(() => {
            document.body.classList.remove('is-scrolling');
            document.documentElement.removeAttribute('data-scrolling');
        }, 100);
    }, { passive: true });
    
    // Back-to-top button visibility
    const backToTopBtn = document.getElementById('back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 500) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        }, { passive: true });
    }
});

// Import components - Use lazy loading for better performance
import ClientDashboard from './components/ClientDashboard.vue';
import CaregiverDashboard from './components/CaregiverDashboard.vue';
import HousekeeperDashboard from './components/HousekeeperDashboard.vue';
import AdminDashboard from './components/AdminDashboard.vue';
import AdminSettings from './components/AdminSettings.vue';
import MarketingDashboard from './components/MarketingDashboard.vue';
import TrainingDashboard from './components/TrainingDashboard.vue';
import PaymentPage from './components/PaymentPageStripeElements.vue';
import StripeConnectOnboarding from './components/StripeConnectOnboarding.vue';
import CustomBankOnboarding from './components/CustomBankOnboarding.vue';
import ClientPaymentSetup from './components/ClientPaymentSetup.vue';
import LinkPaymentMethod from './components/LinkPaymentMethod.vue';
import ConnectPaymentMethod from './components/ConnectPaymentMethod.vue';
import EmailVerificationModal from './components/EmailVerificationModal.vue';
import DashboardWrapper from './components/DashboardWrapper.vue';
import LandingPage from './components/LandingPage.vue';
import TaxPayrollSection from './components/TaxPayrollSection.vue';
import ErrorBoundary from './components/shared/ErrorBoundary.vue';

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
            DashboardWrapper,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#client-dashboard-app');
}

if (document.getElementById('caregiver-dashboard-app')) {
    const app = createApp({
        components: {
            CaregiverDashboard,
            DashboardWrapper,
            TaxPayrollSection,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#caregiver-dashboard-app');
}

if (document.getElementById('housekeeper-dashboard-app')) {
    const app = createApp({
        components: {
            HousekeeperDashboard,
            DashboardWrapper,
            TaxPayrollSection,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#housekeeper-dashboard-app');
}

if (document.getElementById('admin-dashboard-app')) {
    const app = createApp({
        components: {
            AdminDashboard,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#admin-dashboard-app');
}

if (document.getElementById('admin-settings-app')) {
    const app = createApp({
        components: {
            AdminSettings,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#admin-settings-app');
}

if (document.getElementById('marketing-dashboard-app')) {
    const app = createApp({
        components: {
            MarketingDashboard,
            DashboardWrapper,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#marketing-dashboard-app');
}

if (document.getElementById('training-dashboard-app')) {
    const app = createApp({
        components: {
            TrainingDashboard,
            DashboardWrapper,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#training-dashboard-app');
}

if (document.getElementById('payment-page-app')) {
    const app = createApp({
        components: {
            PaymentPage,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#payment-page-app');
}

if (document.getElementById('app')) {
    const app = createApp({
        components: {
            StripeConnectOnboarding,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#app');
}

if (document.getElementById('custom-bank-onboarding-app')) {
    const app = createApp({
        components: {
            CustomBankOnboarding,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#custom-bank-onboarding-app');
}

if (document.getElementById('marketing-bank-onboarding-app')) {
    const app = createApp({
        components: {
            CustomBankOnboarding,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#marketing-bank-onboarding-app');
}

if (document.getElementById('training-bank-onboarding-app')) {
    const app = createApp({
        components: {
            CustomBankOnboarding,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#training-bank-onboarding-app');
}

if (document.getElementById('client-payment-setup-app')) {
    const app = createApp({
        components: {
            ClientPaymentSetup,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#client-payment-setup-app');
}

if (document.getElementById('link-payment-app')) {
    const app = createApp({
        components: {
            LinkPaymentMethod,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#link-payment-app');
}

if (document.getElementById('connect-payment-app')) {
    const app = createApp({
        components: {
            ConnectPaymentMethod,
            ErrorBoundary,
        },
    });
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#connect-payment-app');
}

// Public landing page (Vue)
if (document.getElementById('landing-app')) {
    const app = createApp(LandingPage);
    app.use(vuetify);
    configureErrorHandling(app);
    app.mount('#landing-app');
}
