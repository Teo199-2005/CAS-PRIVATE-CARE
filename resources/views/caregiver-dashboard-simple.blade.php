<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caregiver Dashboard - CAS Private Care LLC</title>
    
    <!-- Vue 3 from CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <!-- Vuetify 3 from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/vuetify@3.7.5/dist/vuetify.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vuetify@3.7.5/dist/vuetify.min.js"></script>
    
    <!-- Material Design Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: #f9fafb;
        }
        .loading-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #10b981;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="loading-container" v-if="loading">
            <div class="loading-spinner"></div>
            <h2 style="margin-top: 20px; color: #10b981;">Loading Caregiver Dashboard...</h2>
            <p style="color: #666;">Please wait while we set up your dashboard</p>
        </div>
        
        <v-app v-else>
            <v-navigation-drawer permanent width="300" style="background: white; box-shadow: 0 0 40px rgba(0,0,0,0.06);">
                <div style="padding: 24px; background: linear-gradient(180deg, #fafafa 0%, #ffffff 100%); border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; align-items: center; margin-bottom: 16px;">
                        <div style="width: 50px; height: 50px; background: #10b981; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px;">C</div>
                        <div style="margin-left: 16px;">
                            <div style="font-size: 18px; font-weight: 700; color: #1a1a1a;">CAS Private Care</div>
                            <div style="font-size: 12px; color: #666;">Comfort & Support</div>
                        </div>
                    </div>
                    <div style="text-align: center; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                        <h3 style="color: #10b981; margin-bottom: 8px;">Welcome Back, Demo</h3>
                        <p style="color: #666; margin: 0;">Manage your appointments and clients</p>
                    </div>
                </div>
                
                <v-list density="compact" nav style="padding: 12px;">
                    <v-list-item 
                        v-for="item in navItems" 
                        :key="item.value"
                        :prepend-icon="item.icon"
                        :title="item.title"
                        @click="currentSection = item.value"
                        :class="{ 'v-list-item--active': currentSection === item.value }"
                        style="border-radius: 12px; margin: 2px 0;"
                    >
                        <template v-if="item.badge" v-slot:append>
                            <v-chip color="error" size="x-small" v-text="item.badge"></v-chip>
                        </template>
                    </v-list-item>
                </v-list>
                
                <template v-slot:append>
                    <div style="padding: 24px;">
                        <v-btn block variant="flat" color="grey-lighten-2" prepend-icon="mdi-logout" size="large">Logout</v-btn>
                    </div>
                </template>
            </v-navigation-drawer>
            
            <v-main style="background: #f9fafb;">
                <v-container fluid style="padding: 32px;">
                    <!-- Header -->
                    <v-card style="margin-bottom: 32px; border-radius: 24px;" elevation="0">
                        <v-card-text style="display: flex; justify-content: space-between; align-items: center; padding: 16px;">
                            <div></div>
                            <div style="text-align: center;">
                                <h1 style="color: #10b981; font-size: 40px; font-weight: 700; margin-bottom: 8px;">Caregiver Portal</h1>
                                <p style="color: #666; font-size: 18px;">Manage your appointments and provide quality care</p>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <v-avatar color="success" size="56">DC</v-avatar>
                                <div style="margin-left: 16px;">
                                    <div style="font-size: 18px; font-weight: 600;">Demo Caregiver</div>
                                    <div style="color: #666;">Caregiver</div>
                                </div>
                            </div>
                        </v-card-text>
                    </v-card>
                    
                    <!-- Dashboard Content -->
                    <div v-if="currentSection === 'dashboard'">
                        <v-row>
                            <!-- Account Balance Card -->
                            <v-col cols="12" sm="6" md="3">
                                <v-card style="height: 100%; border-radius: 16px;" elevation="2">
                                    <v-card-title style="background: #f8fafc; color: #10b981; padding: 16px;">Account Balance</v-card-title>
                                    <v-card-text style="padding: 16px; text-align: center;">
                                        <div style="font-size: 24px; font-weight: 700; color: #10b981; margin-bottom: 8px;">$0.00</div>
                                        <div style="color: #666; font-size: 14px; margin-bottom: 16px;">Available Balance</div>
                                        <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                            <span>Auto Payout:</span>
                                            <span style="color: #10b981; font-weight: bold;">Every Friday</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                            <span>Next Payout:</span>
                                            <span style="font-weight: bold;">Dec 19</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 16px;">
                                            <span>Today:</span>
                                            <span style="font-weight: bold;">Thu, Dec 18</span>
                                        </div>
                                        <v-btn block variant="outlined" color="success" size="small">Request Payout</v-btn>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                            
                            <!-- Stats Cards -->
                            <v-col v-for="stat in stats" :key="stat.title" cols="12" sm="6" md="3">
                                <v-card style="height: 100%; border-radius: 16px;" elevation="2">
                                    <v-card-text style="padding: 16px;">
                                        <div style="display: flex; align-items: center; margin-bottom: 12px;">
                                            <v-icon :color="stat.color" size="32" v-text="stat.icon"></v-icon>
                                            <div style="margin-left: 12px;">
                                                <div style="font-size: 24px; font-weight: 700;" v-text="stat.value"></div>
                                                <div style="color: #666; font-size: 14px;" v-text="stat.title"></div>
                                            </div>
                                        </div>
                                        <div :style="'color: ' + (stat.changeColor || '#666') + '; font-size: 12px;'">
                                            <v-icon size="16" :color="stat.changeColor" v-text="stat.changeIcon"></v-icon>
                                            <span v-text="stat.change"></span>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                        
                        <v-row style="margin-top: 16px;">
                            <!-- Time Tracking Card -->
                            <v-col cols="12" md="6">
                                <v-card style="height: 100%; border-radius: 16px;" elevation="2">
                                    <v-card-title style="background: #f8fafc; color: #10b981; padding: 16px;">
                                        <v-icon color="success" style="margin-right: 12px;">mdi-clock-time-four</v-icon>
                                        Time Tracking
                                    </v-card-title>
                                    <v-card-text style="padding: 16px;">
                                        <div v-if="isTimedIn" style="text-align: center; margin-bottom: 16px;">
                                            <v-chip color="success" size="large" style="margin-bottom: 12px;">
                                                <v-icon start size="small">mdi-clock-check</v-icon>
                                                Clocked In
                                            </v-chip>
                                            <div style="font-size: 20px; font-weight: bold; margin-bottom: 16px;" v-text="timeIn"></div>
                                            <div style="margin-bottom: 8px;">
                                                <div style="font-weight: 500;">Currently working with</div>
                                                <div style="color: #10b981; font-weight: bold; font-size: 18px;" v-text="currentClient"></div>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                                <span>Location:</span>
                                                <span>Client Home</span>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 16px;">
                                                <span>Shift Duration:</span>
                                                <span>8 hours</span>
                                            </div>
                                            <v-alert type="info" variant="tonal" density="compact">
                                                <v-icon size="small" style="margin-right: 4px;">mdi-information</v-icon>
                                                Remember to clock out when your shift ends
                                            </v-alert>
                                        </div>
                                        <div v-else style="text-align: center; margin-bottom: 16px;">
                                            <v-chip color="grey" size="large" style="margin-bottom: 12px;">
                                                <v-icon start size="small">mdi-clock-outline</v-icon>
                                                Not Clocked In
                                            </v-chip>
                                            <div style="color: #666; margin-bottom: 16px;">Ready to start your shift</div>
                                            <v-alert type="info" variant="tonal" density="compact">
                                                <v-icon size="small" style="margin-right: 4px;">mdi-information</v-icon>
                                                You need an active client assignment to use time tracking
                                            </v-alert>
                                        </div>
                                        <v-btn v-if="!isTimedIn && currentClient !== 'N/A'" block color="success" size="large" prepend-icon="mdi-login" @click="handleTimeIn">Clock In</v-btn>
                                        <v-btn v-else-if="isTimedIn" block color="error" size="large" prepend-icon="mdi-logout" @click="handleTimeOut">Clock Out</v-btn>
                                        <v-btn v-else block color="grey" size="large" prepend-icon="mdi-lock" disabled>No Active Client</v-btn>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                            
                            <!-- Previous Week Summary -->
                            <v-col cols="12" md="6">
                                <v-card style="height: 100%; border-radius: 16px;" elevation="2">
                                    <v-card-title style="background: #f8fafc; color: #10b981; padding: 16px;">
                                        <v-icon color="success" style="margin-right: 12px;">mdi-chart-line</v-icon>
                                        Previous Week Summary
                                    </v-card-title>
                                    <v-card-text style="padding: 16px;">
                                        <div style="margin-bottom: 16px;">
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                                <span>Hours Worked</span>
                                                <span style="font-weight: bold;">32 hrs</span>
                                            </div>
                                            <v-progress-linear :model-value="80" color="info" height="6" rounded></v-progress-linear>
                                            <div style="color: #666; font-size: 12px; margin-top: 4px;">Target: 40 hrs/week</div>
                                        </div>
                                        <div style="margin-bottom: 16px;">
                                            <div style="display: flex; justify-content: space-between;">
                                                <span>Previous Payout</span>
                                                <span style="color: #3b82f6; font-weight: bold;">$960.00 - Dec 13, 2024</span>
                                            </div>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                        
                        <!-- Weekly Time History -->
                        <v-row style="margin-top: 16px;">
                            <v-col cols="12">
                                <v-card style="border-radius: 16px;" elevation="2">
                                    <v-card-title style="background: #f8fafc; color: #10b981; padding: 16px;">
                                        <v-icon color="success" style="margin-right: 12px;">mdi-calendar-week</v-icon>
                                        Weekly Time History
                                    </v-card-title>
                                    <v-card-text style="padding: 24px;">
                                        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 12px;">
                                            <div v-for="day in weekHistory" :key="day.date" style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: white;">
                                                <div :style="'background: ' + (day.isToday ? '#10b981' : '#f8fafc') + '; color: ' + (day.isToday ? 'white' : '#333') + '; padding: 8px; text-align: center; border-bottom: 1px solid #e5e7eb;'">
                                                    <div style="font-size: 12px; font-weight: 600; text-transform: uppercase;" v-text="day.dayName"></div>
                                                    <div style="font-size: 14px; font-weight: 700; margin-top: 2px;" v-text="day.date"></div>
                                                </div>
                                                <div style="padding: 12px 8px; min-height: 80px;">
                                                    <div v-if="day.timeIn" style="margin-bottom: 8px;">
                                                        <div style="font-size: 10px; color: #666; text-transform: uppercase; font-weight: 600;">Time In</div>
                                                        <div style="font-size: 12px; font-weight: 600; color: #10b981; margin-top: 2px;" v-text="day.timeIn"></div>
                                                    </div>
                                                    <div v-if="day.timeOut" style="margin-bottom: 8px;">
                                                        <div style="font-size: 10px; color: #666; text-transform: uppercase; font-weight: 600;">Time Out</div>
                                                        <div style="font-size: 12px; font-weight: 600; color: #ef4444; margin-top: 2px;" v-text="day.timeOut"></div>
                                                    </div>
                                                    <div v-if="day.totalHours" style="text-align: center; margin-top: 8px;">
                                                        <v-chip size="x-small" color="info"><span v-text="day.totalHours"></span> hrs</v-chip>
                                                    </div>
                                                    <div v-if="!day.timeIn" style="text-align: center; color: #9ca3af; font-size: 12px; font-style: italic; margin-top: 20px;">No record</div>
                                                </div>
                                            </div>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                    </div>
                    
                    <!-- Other sections placeholder -->
                    <div v-else>
                        <v-card style="border-radius: 16px; text-align: center; padding: 40px;" elevation="2">
                            <h2 style="color: #10b981; margin-bottom: 16px;" v-text="getSectionTitle(currentSection)"></h2>
                            <p style="color: #666;">This section is under development. Please check back later.</p>
                            <v-btn color="success" @click="currentSection = 'dashboard'" style="margin-top: 16px;">Back to Dashboard</v-btn>
                        </v-card>
                    </div>
                </v-container>
            </v-main>
        </v-app>
    </div>

    <script>
        const { createApp } = Vue;
        const { createVuetify } = Vuetify;

        const vuetify = createVuetify({
            theme: {
                defaultTheme: 'light',
                themes: {
                    light: {
                        colors: {
                            primary: '#3b82f6',
                            secondary: '#10b981',
                            success: '#10b981',
                            error: '#dc2626',
                            warning: '#f59e0b',
                            info: '#0ea5e9',
                        },
                    },
                },
            },
        });

        createApp({
            data() {
                return {
                    loading: true,
                    currentSection: 'dashboard',
                    isTimedIn: true,
                    timeIn: '9:00 AM',
                    currentClient: 'N/A',
                    navItems: [
                        { icon: 'mdi-view-dashboard', title: 'Dashboard', value: 'dashboard' },
                        { icon: 'mdi-bell', title: 'Notifications', value: 'notifications', badge: '1' },
                        { icon: 'mdi-credit-card', title: 'Payment Information', value: 'payment' },
                        { icon: 'mdi-history', title: 'Transaction History', value: 'transactions' },
                        { icon: 'mdi-account-search', title: 'Job Listings', value: 'available-clients' },
                        { icon: 'mdi-chart-bar', title: 'Earnings Report', value: 'analytics' },
                        { icon: 'mdi-account-circle', title: 'Profile', value: 'profile' }
                    ],
                    stats: [
                        { 
                            title: 'Current Client', 
                            value: 'N/A', 
                            icon: 'mdi-account', 
                            color: 'grey', 
                            change: 'Status: No Contract', 
                            changeColor: '#666', 
                            changeIcon: 'mdi-close-circle' 
                        },
                        { 
                            title: 'Hourly Rate', 
                            value: '$30', 
                            icon: 'mdi-currency-usd', 
                            color: 'success', 
                            change: 'Per hour', 
                            changeColor: '#3b82f6', 
                            changeIcon: 'mdi-clock-outline' 
                        },
                        { 
                            title: 'Weekly Earnings', 
                            value: '$800', 
                            icon: 'mdi-currency-usd', 
                            color: 'success', 
                            change: '15% vs last week', 
                            changeColor: '#10b981', 
                            changeIcon: 'mdi-trending-up' 
                        }
                    ],
                    weekHistory: [
                        { dayName: 'Fri', date: 'Dec 12', timeIn: '9:00 AM', timeOut: '5:00 PM', totalHours: '8', isToday: false },
                        { dayName: 'Sat', date: 'Dec 13', timeIn: '9:00 AM', timeOut: '5:00 PM', totalHours: '8', isToday: false },
                        { dayName: 'Sun', date: 'Dec 14', timeIn: '9:00 AM', timeOut: '5:00 PM', totalHours: '8', isToday: false },
                        { dayName: 'Mon', date: 'Dec 15', timeIn: '9:00 AM', timeOut: '5:00 PM', totalHours: '8', isToday: false },
                        { dayName: 'Tue', date: 'Dec 16', timeIn: null, timeOut: null, totalHours: null, isToday: false },
                        { dayName: 'Wed', date: 'Dec 17', timeIn: '9:00 AM', timeOut: '5:00 PM', totalHours: '8', isToday: false },
                        { dayName: 'Thu', date: 'Dec 18', timeIn: '9:07 PM', timeOut: null, totalHours: null, isToday: true },
                    ]
                }
            },
            methods: {
                handleTimeIn() {
                    const now = new Date();
                    this.timeIn = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                    this.isTimedIn = true;
                },
                handleTimeOut() {
                    this.isTimedIn = false;
                },
                getSectionTitle(section) {
                    const titles = {
                        'notifications': 'Notifications',
                        'payment': 'Payment Information',
                        'transactions': 'Transaction History',
                        'available-clients': 'Job Listings',
                        'analytics': 'Earnings Report',
                        'profile': 'Profile'
                    };
                    return titles[section] || 'Dashboard';
                },
                async loadCaregiverStats() {
                    try {
                        const response = await fetch('/api/caregiver/25/stats');
                        const data = await response.json();
                        
                        if (data.active_assignments && data.active_assignments.length > 0) {
                            const clientName = data.active_assignments[0].booking?.client?.name || 'Demo Client';
                            this.stats[0] = {
                                title: 'Current Client',
                                value: clientName,
                                icon: 'mdi-account',
                                color: 'success',
                                change: 'Status: Ongoing Contract',
                                changeColor: '#10b981',
                                changeIcon: 'mdi-check-circle'
                            };
                            this.currentClient = clientName;
                        }
                    } catch (error) {
                        console.error('Failed to load caregiver stats:', error);
                    }
                }
            },
            async mounted() {
                // Simulate loading
                setTimeout(() => {
                    this.loading = false;
                }, 1500);
                
                // Load caregiver stats
                await this.loadCaregiverStats();
            }
        }).use(vuetify).mount('#app');
    </script>
</body>
</html>