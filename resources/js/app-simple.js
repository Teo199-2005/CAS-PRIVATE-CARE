import './bootstrap';
import { createApp } from 'vue';

// Simple dashboard components without heavy dependencies
const SimpleDashboard = {
  props: ['userData'],
  data() {
    return {
      currentSection: 'dashboard',
      stats: [
        { title: 'Amount Due', value: '$0', icon: 'mdi-currency-usd' },
        { title: 'Active Services', value: '2', icon: 'mdi-briefcase-check' },
        { title: 'Total Spent', value: '$0', icon: 'mdi-cash' }
      ]
    }
  },
  computed: {
    userName() {
      return this.userData?.name || 'User';
    }
  },
  template: `
    <div class="simple-dashboard">
      <header class="dashboard-header">
        <h1>Welcome, {{ userName }}</h1>
        <p>CAS Private Care Dashboard</p>
      </header>
      
      <nav class="dashboard-nav">
        <button @click="currentSection = 'dashboard'" :class="{ active: currentSection === 'dashboard' }">
          Dashboard
        </button>
        <button @click="currentSection = 'bookings'" :class="{ active: currentSection === 'bookings' }">
          My Bookings
        </button>
        <button @click="currentSection = 'book'" :class="{ active: currentSection === 'book' }">
          Book Service
        </button>
        <button @click="currentSection = 'profile'" :class="{ active: currentSection === 'profile' }">
          Profile
        </button>
      </nav>

      <main class="dashboard-content">
        <div v-if="currentSection === 'dashboard'" class="dashboard-section">
          <div class="stats-grid">
            <div v-for="stat in stats" :key="stat.title" class="stat-card">
              <h3>{{ stat.title }}</h3>
              <p class="stat-value">{{ stat.value }}</p>
            </div>
          </div>
          
          <div class="recent-activity">
            <h2>Recent Activity</h2>
            <div class="activity-item">
              <p>Service request submitted - Pending review</p>
              <span class="activity-time">2 hours ago</span>
            </div>
          </div>
        </div>

        <div v-if="currentSection === 'bookings'" class="bookings-section">
          <h2>My Bookings</h2>
          <div class="booking-card">
            <h3>Caregiver Service</h3>
            <p>Status: Pending Review</p>
            <p>Date: Dec 25, 2024</p>
            <button class="btn-primary">View Details</button>
          </div>
        </div>

        <div v-if="currentSection === 'book'" class="book-section">
          <h2>Book a Service</h2>
          <form class="booking-form">
            <div class="form-group">
              <label>Service Type</label>
              <select>
                <option>Caregiver</option>
                <option>Housekeeping</option>
                <option>Personal Care</option>
              </select>
            </div>
            <div class="form-group">
              <label>Date</label>
              <input type="date" />
            </div>
            <div class="form-group">
              <label>Hours per Day</label>
              <select>
                <option>8 Hours</option>
                <option>12 Hours</option>
                <option>24 Hours</option>
              </select>
            </div>
            <button type="submit" class="btn-primary">Submit Request</button>
          </form>
        </div>

        <div v-if="currentSection === 'profile'" class="profile-section">
          <h2>Profile Settings</h2>
          <form class="profile-form">
            <div class="form-group">
              <label>Name</label>
              <input type="text" :value="userName" />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" :value="userData?.email || ''" />
            </div>
            <button type="submit" class="btn-primary">Save Changes</button>
          </form>
        </div>
      </main>
    </div>
  `
};

// Initialize simple apps
if (document.getElementById('client-dashboard-app')) {
  const app = createApp({
    components: { SimpleDashboard },
    template: '<simple-dashboard :user-data="userData" />',
    data() {
      return {
        userData: window.userData || {}
      }
    }
  });
  app.mount('#client-dashboard-app');
}

if (document.getElementById('caregiver-dashboard-app')) {
  const app = createApp({
    template: `
      <div class="simple-dashboard">
        <header class="dashboard-header">
          <h1>Caregiver Dashboard</h1>
          <p>Manage your assignments and schedule</p>
        </header>
        <div class="dashboard-content">
          <div class="stat-card">
            <h3>Active Assignments</h3>
            <p class="stat-value">3</p>
          </div>
        </div>
      </div>
    `
  });
  app.mount('#caregiver-dashboard-app');
}

if (document.getElementById('admin-dashboard-app')) {
  const app = createApp({
    template: `
      <div class="simple-dashboard">
        <header class="dashboard-header">
          <h1>Admin Dashboard</h1>
          <p>System management and oversight</p>
        </header>
        <div class="dashboard-content">
          <div class="stat-card">
            <h3>Total Users</h3>
            <p class="stat-value">150</p>
          </div>
        </div>
      </div>
    `
  });
  app.mount('#admin-dashboard-app');
}

if (document.getElementById('marketing-dashboard-app')) {
  const app = createApp({
    template: `
      <div class="simple-dashboard">
        <header class="dashboard-header">
          <h1>Marketing Dashboard</h1>
          <p>Campaign management and analytics</p>
        </header>
        <div class="dashboard-content">
          <div class="stat-card">
            <h3>Active Campaigns</h3>
            <p class="stat-value">5</p>
          </div>
        </div>
      </div>
    `
  });
  app.mount('#marketing-dashboard-app');
}

if (document.getElementById('training-dashboard-app')) {
  const app = createApp({
    template: `
      <div class="simple-dashboard">
        <header class="dashboard-header">
          <h1>Training Dashboard</h1>
          <p>Training programs and certifications</p>
        </header>
        <div class="dashboard-content">
          <div class="stat-card">
            <h3>Active Courses</h3>
            <p class="stat-value">12</p>
          </div>
        </div>
      </div>
    `
  });
  app.mount('#training-dashboard-app');
}