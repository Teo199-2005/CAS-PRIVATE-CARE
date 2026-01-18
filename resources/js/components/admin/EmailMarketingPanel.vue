<template>
  <div class="email-marketing-panel">
    <!-- Header -->
    <div class="panel-header">
      <h2>Email Marketing</h2>
      <p class="text-gray-600">Send customized marketing emails to your clients</p>
    </div>

    <!-- Dashboard Stats -->
    <div class="stats-grid" v-if="dashboardStats">
      <div class="stat-card">
        <div class="stat-icon bg-red-100">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Total Campaigns</p>
          <p class="stat-value">{{ dashboardStats.total_campaigns }}</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-blue-100">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Emails Sent</p>
          <p class="stat-value">{{ dashboardStats.total_emails_sent }}</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-green-100">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Open Rate</p>
          <p class="stat-value">{{ dashboardStats.average_open_rate }}%</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-purple-100">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Click Rate</p>
          <p class="stat-value">{{ dashboardStats.average_click_rate }}%</p>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button 
        :class="['tab', { active: activeTab === 'compose' }]" 
        @click="activeTab = 'compose'"
      >
        Compose Email
      </button>
      <button 
        :class="['tab', { active: activeTab === 'campaigns' }]" 
        @click="activeTab = 'campaigns'"
      >
        Campaigns
      </button>
    </div>

    <!-- Compose Email Tab -->
    <div v-if="activeTab === 'compose'" class="compose-section">
      <div class="compose-layout">
        <!-- Left Side - Email Compose Form -->
        <div class="compose-left">
          <div class="compose-card">
            <h3 class="compose-card-title">Campaign Details</h3>
            
            <div class="form-row">
              <div class="form-group">
                <label>Campaign Name</label>
                <input 
                  type="text" 
                  v-model="campaign.name" 
                  placeholder="e.g., Winter Promotion 2024"
                  class="form-input"
                />
              </div>
              <div class="form-group">
                <label>Campaign Type</label>
                <select v-model="campaign.type" class="form-input">
                  <option value="marketing">Marketing</option>
                  <option value="promotional">Promotional</option>
                  <option value="newsletter">Newsletter</option>
                  <option value="announcement">Announcement</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Email Subject</label>
              <input 
                type="text" 
                v-model="campaign.subject" 
                placeholder="e.g., Special Holiday Offer Just for You!"
                class="form-input"
              />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Target Audience</label>
                <select v-model="campaign.target_audience" @change="fetchFilteredClients" class="form-input">
                  <option v-for="filter in filterOptions" :key="filter.value" :value="filter.value">
                    {{ filter.label }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label>Preview Text (optional)</label>
                <input 
                  type="text" 
                  v-model="campaign.preview_text" 
                  placeholder="Brief text shown in email preview"
                  class="form-input"
                />
              </div>
            </div>
          </div>

          <div class="compose-card">
            <h3 class="compose-card-title">Email Content</h3>
            
            <div class="content-toolbar">
              <span class="toolbar-label">Insert:</span>
              <button @click="insertToken('{{name}}')" class="toolbar-btn" title="Inserts the client's full name automatically">
                <svg class="toolbar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                Client Name
              </button>
              <button @click="insertToken('{{first_name}}')" class="toolbar-btn" title="Inserts just the client's first name">
                <svg class="toolbar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                First Name
              </button>
              <button @click="insertToken('{{company_name}}')" class="toolbar-btn" title="Inserts 'CAS Private Care'">
                <svg class="toolbar-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
                Company
              </button>
            </div>
            <textarea 
              ref="contentEditor"
              v-model="campaign.content" 
              placeholder="Write your email message here...

Example:
Hi [Click 'First Name' button above to insert],

We wanted to reach out with some exciting news!

Best regards,
The CAS Private Care Team"
              class="form-textarea"
              rows="14"
            ></textarea>
            <p class="form-hint">
              <svg class="hint-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
              Tip: Click the buttons above to personalize your email. Each recipient will see their own name!
            </p>
          </div>
        </div>

        <!-- Right Side - Recipients -->
        <div class="compose-right">
          <div class="recipients-card">
            <div class="recipients-header">
              <h3>Recipients ({{ selectedClientIds.length }} / {{ filteredClients.length }})</h3>
              <div class="recipients-actions">
                <button @click="selectAllClients" class="btn-link">Select All</button>
                <button @click="deselectAllClients" class="btn-link">Deselect All</button>
              </div>
            </div>
            <div class="recipients-list" v-if="filteredClients.length > 0">
              <label 
                v-for="client in filteredClients" 
                :key="client.id" 
                class="recipient-item"
              >
                <input 
                  type="checkbox" 
                  :value="client.id" 
                  v-model="selectedClientIds"
                />
                <div class="recipient-info">
                  <span class="recipient-name">{{ client.name }}</span>
                  <span class="recipient-email">{{ client.email }}</span>
                </div>
                <span class="recipient-bookings">{{ client.bookings_count }} bookings</span>
              </label>
            </div>
            <div v-else class="recipients-empty">
              <p>No clients match this filter</p>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
              <button @click="previewEmail" class="btn btn-secondary" :disabled="!canPreview">
                Preview
              </button>
              <button @click="sendTestEmail" class="btn btn-secondary" :disabled="!canSend">
                Send Test
              </button>
              <button @click="saveDraft" class="btn btn-secondary" :disabled="!campaign.name">
                Save Draft
              </button>
              <button @click="sendCampaign" class="btn btn-primary btn-full" :disabled="!canSend || sending">
                <span v-if="sending">Sending...</span>
                <span v-else>Send Campaign ({{ selectedClientIds.length }} recipients)</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Campaigns List Tab -->
    <div v-if="activeTab === 'campaigns'" class="campaigns-section">
      <div class="campaigns-table" v-if="campaigns.length > 0">
        <table>
          <thead>
            <tr>
              <th>Campaign</th>
              <th>Status</th>
              <th>Sent</th>
              <th>Opens</th>
              <th>Clicks</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="camp in campaigns" :key="camp.id">
              <td data-label="Campaign">
                <div class="campaign-name">{{ camp.name }}</div>
                <div class="campaign-subject">{{ camp.subject }}</div>
              </td>
              <td data-label="Status">
                <span :class="['status-badge', camp.status]">{{ camp.status }}</span>
              </td>
              <td data-label="Sent">{{ camp.sent_count || 0 }}</td>
              <td data-label="Opens">
                {{ camp.open_count || 0 }}
                <span class="rate" v-if="camp.sent_count">({{ ((camp.open_count / camp.sent_count) * 100).toFixed(1) }}%)</span>
              </td>
              <td data-label="Clicks">
                {{ camp.click_count || 0 }}
                <span class="rate" v-if="camp.sent_count">({{ ((camp.click_count / camp.sent_count) * 100).toFixed(1) }}%)</span>
              </td>
              <td data-label="Date">{{ formatDate(camp.sent_at || camp.created_at) }}</td>
              <td data-label="Actions">
                <button @click="viewCampaign(camp)" class="action-btn" title="View Analytics">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </button>
                <button v-if="camp.status === 'draft'" @click="editCampaign(camp)" class="action-btn" title="Edit">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
                <button v-if="camp.status === 'draft'" @click="deleteCampaign(camp)" class="action-btn delete" title="Delete">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="empty-state">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
        <p>No campaigns yet</p>
        <button @click="activeTab = 'compose'" class="btn btn-primary mt-4">Create Your First Campaign</button>
      </div>
    </div>

    <!-- Preview Modal -->
    <div v-if="showPreviewModal" class="modal-overlay" @click.self="showPreviewModal = false">
      <div class="modal preview-modal">
        <div class="modal-header">
          <h3>Email Preview</h3>
          <button @click="showPreviewModal = false" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
          <div class="preview-subject">
            <strong>Subject:</strong> {{ previewData.subject }}
          </div>
          <div class="preview-content" v-html="previewData.content"></div>
        </div>
      </div>
    </div>

    <!-- Test Email Modal -->
    <div v-if="showTestModal" class="modal-overlay" @click.self="showTestModal = false">
      <div class="modal">
        <div class="modal-header">
          <h3>Send Test Email</h3>
          <button @click="showTestModal = false" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Send test email to:</label>
            <input 
              type="email" 
              v-model="testEmail" 
              placeholder="your@email.com"
              class="form-input"
            />
          </div>
          <button @click="confirmSendTestEmail" class="btn btn-primary" :disabled="!testEmail">
            Send Test
          </button>
        </div>
      </div>
    </div>

    <!-- Analytics Modal -->
    <div v-if="showAnalyticsModal" class="modal-overlay" @click.self="showAnalyticsModal = false">
      <div class="modal analytics-modal">
        <div class="modal-header">
          <h3>Campaign Analytics</h3>
          <button @click="showAnalyticsModal = false" class="close-btn">&times;</button>
        </div>
        <div class="modal-body" v-if="analyticsData">
          <h4>{{ analyticsData.campaign_name }}</h4>
          <div class="analytics-stats">
            <div class="analytics-stat">
              <span class="label">Sent</span>
              <span class="value">{{ analyticsData.totals.sent }}</span>
            </div>
            <div class="analytics-stat">
              <span class="label">Opened</span>
              <span class="value">{{ analyticsData.totals.opened }} ({{ analyticsData.rates.open_rate }}%)</span>
            </div>
            <div class="analytics-stat">
              <span class="label">Clicked</span>
              <span class="value">{{ analyticsData.totals.clicked }} ({{ analyticsData.rates.click_rate }}%)</span>
            </div>
            <div class="analytics-stat">
              <span class="label">Failed</span>
              <span class="value">{{ analyticsData.totals.failed }}</span>
            </div>
          </div>
          
          <div v-if="analyticsData.recent_opens && analyticsData.recent_opens.length" class="analytics-section">
            <h5>Recent Opens</h5>
            <ul>
              <li v-for="(open, idx) in analyticsData.recent_opens" :key="idx">
                {{ open.email }} - {{ formatDate(open.opened_at) }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Notifications -->
    <div v-if="notification" :class="['notification', notification.type]">
      {{ notification.message }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'EmailMarketingPanel',
  
  data() {
    return {
      activeTab: 'compose',
      loading: false,
      sending: false,
      
      // Dashboard stats
      dashboardStats: null,
      
      // Campaign form
      campaign: {
        name: '',
        subject: '',
        content: '',
        preview_text: '',
        type: 'marketing',
        target_audience: 'all'
      },
      
      // Filter options
      filterOptions: [
        { value: 'all', label: 'All Clients', description: 'Send to all registered clients' },
        { value: 'never_booked', label: 'Never Booked', description: 'Clients who registered but never made a booking' },
        { value: 'inactive_30_days', label: 'Inactive 30+ Days', description: 'No bookings in the last 30 days' },
        { value: 'inactive_60_days', label: 'Inactive 60+ Days', description: 'No bookings in the last 60 days' },
        { value: 'inactive_90_days', label: 'Inactive 90+ Days', description: 'No bookings in the last 90 days' },
        { value: 'active_clients', label: 'Active Clients', description: 'Made a booking in the last 30 days' },
        { value: 'repeat_clients', label: 'Repeat Clients', description: 'Made 2 or more bookings' },
        { value: 'vip_clients', label: 'VIP Clients', description: 'Made 5 or more bookings' }
      ],
      
      // Clients
      filteredClients: [],
      selectedClientIds: [],
      
      // Campaigns list
      campaigns: [],
      
      // Modals
      showPreviewModal: false,
      showTestModal: false,
      showAnalyticsModal: false,
      previewData: {},
      testEmail: '',
      analyticsData: null,
      
      // Notification
      notification: null
    };
  },
  
  computed: {
    selectedFilterDescription() {
      const filter = this.filterOptions.find(f => f.value === this.campaign.target_audience);
      return filter ? filter.description : '';
    },
    
    canPreview() {
      return this.campaign.subject && this.campaign.content;
    },
    
    canSend() {
      return this.campaign.name && this.campaign.subject && this.campaign.content && this.selectedClientIds.length > 0;
    }
  },
  
  mounted() {
    this.fetchDashboardStats();
    this.fetchFilterOptions();
    this.fetchFilteredClients();
    this.fetchCampaigns();
  },
  
  methods: {
    async fetchDashboardStats() {
      try {
        const response = await fetch('/api/admin/email-marketing/dashboard');
        const data = await response.json();
        if (data.success) {
          this.dashboardStats = data.stats;
        }
      } catch (error) {
        console.error('Failed to fetch dashboard stats:', error);
      }
    },
    
    async fetchFilterOptions() {
      try {
        const response = await fetch('/api/admin/email-marketing/filter-options');
        const data = await response.json();
        if (data.success) {
          this.filterOptions = data.filters;
        }
      } catch (error) {
        console.error('Failed to fetch filter options:', error);
      }
    },
    
    async fetchFilteredClients() {
      try {
        this.loading = true;
        const response = await fetch(`/api/admin/email-marketing/clients?filter=${this.campaign.target_audience}`);
        const data = await response.json();
        if (data.success) {
          this.filteredClients = data.clients;
          this.selectedClientIds = data.clients.map(c => c.id); // Select all by default
        }
      } catch (error) {
        console.error('Failed to fetch clients:', error);
      } finally {
        this.loading = false;
      }
    },
    
    async fetchCampaigns() {
      try {
        const response = await fetch('/api/admin/email-marketing/campaigns');
        const data = await response.json();
        if (data.success) {
          this.campaigns = data.campaigns.data || data.campaigns;
        }
      } catch (error) {
        console.error('Failed to fetch campaigns:', error);
      }
    },
    
    selectAllClients() {
      this.selectedClientIds = this.filteredClients.map(c => c.id);
    },
    
    deselectAllClients() {
      this.selectedClientIds = [];
    },
    
    insertToken(token) {
      const textarea = this.$refs.contentEditor;
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;
      const text = this.campaign.content;
      
      // Use friendly display names that will be replaced on server
      const friendlyNames = {
        '{{name}}': '[Client Name]',
        '{{first_name}}': '[First Name]',
        '{{company_name}}': '[CAS Private Care]',
        '{{email}}': '[Client Email]'
      };
      
      const displayText = friendlyNames[token] || token;
      this.campaign.content = text.substring(0, start) + displayText + text.substring(end);
      
      // Set cursor after inserted token
      this.$nextTick(() => {
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + displayText.length;
      });
    },
    
    async previewEmail() {
      try {
        const response = await fetch('/api/admin/email-marketing/preview', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            subject: this.campaign.subject,
            content: this.campaign.content
          })
        });
        const data = await response.json();
        if (data.success) {
          this.previewData = {
            subject: data.subject,
            content: data.content
          };
          this.showPreviewModal = true;
        }
      } catch (error) {
        this.showNotification('Failed to generate preview', 'error');
      }
    },
    
    sendTestEmail() {
      this.showTestModal = true;
    },
    
    async confirmSendTestEmail() {
      try {
        const response = await fetch('/api/admin/email-marketing/test-email', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            email: this.testEmail,
            subject: this.campaign.subject,
            content: this.campaign.content,
            preview_text: this.campaign.preview_text
          })
        });
        const data = await response.json();
        this.showTestModal = false;
        this.showNotification(data.message, data.success ? 'success' : 'error');
      } catch (error) {
        this.showNotification('Failed to send test email', 'error');
      }
    },
    
    async saveDraft() {
      try {
        const response = await fetch('/api/admin/email-marketing/campaigns', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify(this.campaign)
        });
        const data = await response.json();
        if (data.success) {
          this.showNotification('Campaign saved as draft', 'success');
          this.fetchCampaigns();
        }
      } catch (error) {
        this.showNotification('Failed to save draft', 'error');
      }
    },
    
    async sendCampaign() {
      if (!confirm(`Are you sure you want to send this campaign to ${this.selectedClientIds.length} recipients?`)) {
        return;
      }
      
      try {
        this.sending = true;
        
        // First, create the campaign
        let campaignId;
        const createResponse = await fetch('/api/admin/email-marketing/campaigns', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify(this.campaign)
        });
        const createData = await createResponse.json();
        
        if (!createData.success) {
          throw new Error(createData.message);
        }
        
        campaignId = createData.campaign.id;
        
        // Then send it
        const sendResponse = await fetch(`/api/admin/email-marketing/campaigns/${campaignId}/send`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            client_ids: this.selectedClientIds
          })
        });
        const sendData = await sendResponse.json();
        
        if (sendData.success) {
          this.showNotification(sendData.message, 'success');
          this.resetForm();
          this.fetchCampaigns();
          this.fetchDashboardStats();
          this.activeTab = 'campaigns';
        } else {
          throw new Error(sendData.message);
        }
      } catch (error) {
        this.showNotification(error.message || 'Failed to send campaign', 'error');
      } finally {
        this.sending = false;
      }
    },
    
    async viewCampaign(campaign) {
      try {
        const response = await fetch(`/api/admin/email-marketing/campaigns/${campaign.id}/analytics`);
        const data = await response.json();
        if (data.success) {
          this.analyticsData = data.analytics;
          this.showAnalyticsModal = true;
        }
      } catch (error) {
        this.showNotification('Failed to load analytics', 'error');
      }
    },
    
    editCampaign(campaign) {
      this.campaign = {
        id: campaign.id,
        name: campaign.name,
        subject: campaign.subject,
        content: campaign.content,
        preview_text: campaign.preview_text || '',
        type: campaign.type,
        target_audience: campaign.target_audience
      };
      this.activeTab = 'compose';
    },
    
    async deleteCampaign(campaign) {
      if (!confirm('Are you sure you want to delete this campaign?')) {
        return;
      }
      
      try {
        const response = await fetch(`/api/admin/email-marketing/campaigns/${campaign.id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });
        const data = await response.json();
        if (data.success) {
          this.showNotification('Campaign deleted', 'success');
          this.fetchCampaigns();
        }
      } catch (error) {
        this.showNotification('Failed to delete campaign', 'error');
      }
    },
    
    resetForm() {
      this.campaign = {
        name: '',
        subject: '',
        content: '',
        preview_text: '',
        type: 'marketing',
        target_audience: 'all'
      };
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    showNotification(message, type = 'info') {
      this.notification = { message, type };
      setTimeout(() => {
        this.notification = null;
      }, 5000);
    }
  }
};
</script>

<style scoped>
.email-marketing-panel {
  padding: 24px;
  max-width: 100%;
  margin: 0 auto;
  width: 100%;
}

.panel-header {
  margin-bottom: 24px;
}

/* Compose Section Layout - Email Left, Recipients Right */
.compose-section {
  width: 100%;
}

.compose-layout {
  display: flex;
  gap: 24px;
  width: 100%;
  align-items: flex-start;
}

.compose-left {
  flex: 1.8;
  min-width: 0;
}

.compose-right {
  flex: 1;
  min-width: 320px;
  max-width: 400px;
  position: sticky;
  top: 24px;
}

.compose-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}

.compose-card-title {
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 20px 0;
  padding-bottom: 12px;
  border-bottom: 1px solid #e2e8f0;
}

.recipients-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  max-height: calc(100vh - 200px);
  display: flex;
  flex-direction: column;
}

.recipients-card h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 16px 0;
  padding-bottom: 12px;
  border-bottom: 1px solid #e2e8f0;
}

@media (max-width: 1200px) {
  .compose-layout {
    flex-direction: column;
  }
  
  .compose-left,
  .compose-right {
    flex: none;
    width: 100%;
    max-width: 100%;
  }
  
  .compose-right {
    position: static;
  }
}

.panel-header h2 {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 8px 0;
}

/* Main Layout - Compose Left, Recipients Right */
.main-layout {
  display: flex;
  gap: 24px;
  width: 100%;
}

.compose-panel {
  flex: 0 0 68%;
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.recipients-panel {
  flex: 0 0 30%;
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  max-height: 700px;
  display: flex;
  flex-direction: column;
}

.recipients-panel h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 16px 0;
  padding-bottom: 12px;
  border-bottom: 1px solid #e2e8f0;
}

/* Recipients list styles are defined below in the main recipients-panel section */

.recipient-count {
  font-size: 14px;
  color: #64748b;
  margin-bottom: 12px;
}

.recipients-actions {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
}

.recipients-actions button {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}

.recipients-actions button:hover {
  background: #f1f5f9;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}

@media (max-width: 1024px) {
  .main-layout {
    flex-direction: column;
  }
  
  .compose-panel,
  .recipients-panel {
    flex: none;
    width: 100%;
  }
  
  .recipients-panel {
    max-height: 400px;
  }
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-label {
  font-size: 13px;
  color: #64748b;
  margin: 0;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 16px;
}

.tab {
  padding: 10px 20px;
  border: none;
  background: #f1f5f9;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.tab.active {
  background: #dc2626;
  color: white;
}

/* Form Grid */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

/* Form Row - two columns side by side */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: #374151;
}

.form-input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
}

.form-input:focus {
  outline: none;
  border-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-textarea {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  resize: vertical;
  line-height: 1.6;
}

.form-hint {
  font-size: 12px;
  color: #6b7280;
  margin-top: 8px;
  display: flex;
  align-items: center;
  gap: 6px;
  background: #f0fdf4;
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #bbf7d0;
}

.hint-icon {
  width: 16px;
  height: 16px;
  color: #16a34a;
  flex-shrink: 0;
}

/* Content Toolbar */
.content-toolbar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  padding: 10px 12px;
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border-radius: 8px;
  border: 1px solid #fbbf24;
}

.toolbar-label {
  font-size: 13px;
  font-weight: 600;
  color: #92400e;
  margin-right: 4px;
}

.toolbar-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  color: #374151;
}

.toolbar-btn:hover {
  background: #dc2626;
  color: white;
  border-color: #dc2626;
}

.toolbar-btn:hover .toolbar-icon {
  color: white;
}

.toolbar-icon {
  width: 16px;
  height: 16px;
  color: #6b7280;
}

/* Recipients */
.recipients-section {
  margin-top: 24px;
  background: #f8fafc;
  border-radius: 12px;
  padding: 20px;
}

.recipients-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.recipients-header h3 {
  margin: 0;
  font-size: 16px;
}

.recipients-actions {
  display: flex;
  gap: 16px;
}

.btn-link {
  background: none;
  border: none;
  color: #dc2626;
  cursor: pointer;
  font-size: 14px;
}

.recipients-list {
  max-height: 300px;
  overflow-y: auto;
}

.recipient-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-bottom: 1px solid #e2e8f0;
  cursor: pointer;
}

.recipient-item:hover {
  background: #f1f5f9;
}

.recipient-item input[type="checkbox"] {
  flex-shrink: 0;
  width: 18px;
  height: 18px;
  accent-color: #dc2626;
}

.recipient-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.recipient-name {
  font-weight: 500;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.recipient-email {
  color: #64748b;
  font-size: 12px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.recipient-bookings {
  flex-shrink: 0;
  background: #e0f2fe;
  color: #0369a1;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 500;
  white-space: nowrap;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 12px;
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e2e8f0;
}

.btn {
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: #dc2626;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #b91c1c;
}

.btn-secondary {
  background: white;
  border: 1px solid #d1d5db;
  color: #374151;
}

.btn-secondary:hover:not(:disabled) {
  background: #f9fafb;
}

/* Campaigns Table */
.campaigns-table {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.campaigns-table table {
  width: 100%;
  border-collapse: collapse;
}

.campaigns-table th,
.campaigns-table td {
  padding: 14px 16px;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.campaigns-table th {
  background: #f8fafc;
  font-weight: 600;
  font-size: 13px;
  color: #64748b;
}

.campaign-name {
  font-weight: 600;
  color: #1e293b;
}

.campaign-subject {
  font-size: 13px;
  color: #64748b;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.draft {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.sending {
  background: #dbeafe;
  color: #1e40af;
}

.status-badge.sent {
  background: #dcfce7;
  color: #166534;
}

.rate {
  color: #64748b;
  font-size: 12px;
}

.action-btn {
  background: none;
  border: none;
  padding: 6px;
  cursor: pointer;
  color: #64748b;
  border-radius: 4px;
}

.action-btn:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.action-btn.delete:hover {
  background: #fef2f2;
  color: #dc2626;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

/* Modals */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 16px;
  max-width: 500px;
  width: 90%;
  max-height: 80vh;
  overflow: hidden;
}

.preview-modal,
.analytics-modal {
  max-width: 700px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h3 {
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #64748b;
}

.modal-body {
  padding: 24px;
  max-height: 60vh;
  overflow-y: auto;
}

.preview-subject {
  padding: 12px;
  background: #f8fafc;
  border-radius: 8px;
  margin-bottom: 16px;
}

.preview-content {
  padding: 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
}

/* Analytics */
.analytics-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin: 20px 0;
}

.analytics-stat {
  text-align: center;
  padding: 16px;
  background: #f8fafc;
  border-radius: 8px;
}

.analytics-stat .label {
  display: block;
  font-size: 12px;
  color: #64748b;
  margin-bottom: 4px;
}

.analytics-stat .value {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
}

.analytics-section {
  margin-top: 20px;
}

.analytics-section h5 {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: #374151;
}

.analytics-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.analytics-section li {
  padding: 8px 0;
  border-bottom: 1px solid #f1f5f9;
  font-size: 13px;
  color: #64748b;
}

/* Notification */
.notification {
  position: fixed;
  bottom: 24px;
  right: 24px;
  padding: 16px 24px;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  z-index: 1001;
  animation: slideIn 0.3s ease;
}

.notification.success {
  background: #059669;
}

.notification.error {
  background: #dc2626;
}

.notification.info {
  background: #2563eb;
}

@keyframes slideIn {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* =====================================================
   MOBILE RESPONSIVE STYLES
   ===================================================== */

/* Tablet and below (max-width: 1024px) */
@media (max-width: 1024px) {
  .email-marketing-panel {
    padding: 16px;
  }
  
  .panel-header h2 {
    font-size: 20px;
  }
  
  .compose-layout {
    flex-direction: column;
  }
  
  .compose-left,
  .compose-right {
    flex: none;
    width: 100%;
    max-width: 100%;
  }
  
  .compose-right {
    position: static;
    order: -1; /* Move recipients above compose on tablet */
  }
  
  .recipients-card {
    max-height: 350px;
    margin-bottom: 20px;
  }
  
  .campaigns-table {
    overflow-x: auto;
  }
  
  .campaigns-table table {
    min-width: 700px;
  }
}

/* Mobile devices (max-width: 768px) */
@media (max-width: 768px) {
  .email-marketing-panel {
    padding: 12px;
  }
  
  .panel-header {
    margin-bottom: 16px;
    text-align: center;
  }
  
  .panel-header h2 {
    font-size: 18px;
    margin-bottom: 4px;
  }
  
  .panel-header p {
    font-size: 13px;
  }
  
  /* Stats Grid - 2 columns on mobile */
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 16px;
  }
  
  .stat-card {
    padding: 14px;
    flex-direction: column;
    text-align: center;
    gap: 10px;
  }
  
  .stat-icon {
    width: 40px;
    height: 40px;
  }
  
  .stat-icon svg {
    width: 20px;
    height: 20px;
  }
  
  .stat-value {
    font-size: 20px;
  }
  
  .stat-label {
    font-size: 11px;
  }
  
  /* Tabs - full width on mobile */
  .tabs {
    flex-direction: row;
    gap: 6px;
    padding-bottom: 12px;
    margin-bottom: 16px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  
  .tab {
    padding: 10px 16px;
    font-size: 13px;
    white-space: nowrap;
    flex: 1;
    text-align: center;
  }
  
  /* Compose Cards */
  .compose-card {
    padding: 16px;
    margin-bottom: 12px;
    border-radius: 10px;
  }
  
  .compose-card-title {
    font-size: 15px;
    margin-bottom: 14px;
    padding-bottom: 10px;
  }
  
  /* Form Row - stack on mobile */
  .form-row {
    display: flex;
    flex-direction: column;
    gap: 0;
  }
  
  .form-group {
    margin-bottom: 12px;
  }
  
  .form-group label {
    font-size: 13px;
    margin-bottom: 6px;
  }
  
  .form-input {
    padding: 10px 12px;
    font-size: 14px;
    border-radius: 6px;
  }
  
  .form-textarea {
    padding: 10px 12px;
    font-size: 14px;
    min-height: 180px;
    border-radius: 6px;
  }
  
  /* Content Toolbar - wrap on mobile */
  .content-toolbar {
    flex-wrap: wrap;
    gap: 6px;
    padding: 8px 10px;
  }
  
  .toolbar-label {
    width: 100%;
    font-size: 12px;
    margin-bottom: 4px;
  }
  
  .toolbar-btn {
    padding: 6px 10px;
    font-size: 11px;
    gap: 4px;
    flex: 1;
    min-width: auto;
    justify-content: center;
  }
  
  .toolbar-icon {
    width: 14px;
    height: 14px;
  }
  
  .form-hint {
    font-size: 11px;
    padding: 6px 10px;
  }
  
  .hint-icon {
    width: 14px;
    height: 14px;
  }
  
  /* Recipients Card */
  .compose-right {
    order: 1; /* Keep recipients below on mobile for better UX */
  }
  
  .recipients-card {
    padding: 14px;
    max-height: none; /* Remove height restriction on mobile */
    overflow: visible;
  }
  
  .recipients-card h3 {
    font-size: 14px;
    margin-bottom: 10px;
    padding-bottom: 8px;
  }
  
  .recipients-header {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    margin-bottom: 12px;
  }
  
  .recipients-header h3 {
    font-size: 15px;
    font-weight: 600;
    border: none;
    padding: 0;
    margin: 0;
    text-align: center;
  }
  
  .recipients-actions {
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }
  
  .btn-link {
    font-size: 13px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    text-align: center;
    color: #dc2626;
    font-weight: 500;
  }
  
  .recipients-list {
    max-height: 250px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 16px;
  }
  
  .recipients-empty {
    padding: 24px;
    text-align: center;
    color: #64748b;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 16px;
  }
  
  .recipient-item {
    padding: 10px 12px;
    gap: 10px;
    border-bottom: 1px solid #f1f5f9;
  }
  
  .recipient-item:last-child {
    border-bottom: none;
  }
  
  .recipient-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
  }
  
  .recipient-name {
    font-size: 14px;
    font-weight: 500;
  }
  
  .recipient-email {
    font-size: 12px;
  }
  
  .recipient-bookings {
    padding: 4px 10px;
    font-size: 11px;
    border-radius: 12px;
  }
  
  /* Action Buttons - improved mobile layout */
  .action-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 0;
    padding-top: 0;
    border-top: none;
  }
  
  .btn {
    padding: 14px 18px;
    font-size: 14px;
    width: 100%;
    text-align: center;
    border-radius: 10px;
    font-weight: 600;
  }
  
  .btn-primary {
    order: -1; /* Move primary action to top on mobile */
    background: #dc2626;
    color: white;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
  }
  
  .btn-secondary {
    background: white;
    border: 1px solid #e2e8f0;
    color: #374151;
  }
  
  .btn-full {
    order: -1;
  }
  
  /* Campaigns Table - Cards on mobile */
  .campaigns-section {
    overflow: visible;
  }
  
  .campaigns-table {
    overflow: visible;
    box-shadow: none;
    background: transparent;
  }
  
  .campaigns-table table,
  .campaigns-table thead,
  .campaigns-table tbody,
  .campaigns-table th,
  .campaigns-table td,
  .campaigns-table tr {
    display: block;
  }
  
  .campaigns-table thead {
    display: none;
  }
  
  .campaigns-table tr {
    background: white;
    border-radius: 10px;
    margin-bottom: 12px;
    padding: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  }
  
  .campaigns-table td {
    padding: 6px 0;
    border: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .campaigns-table td::before {
    content: attr(data-label);
    font-weight: 600;
    font-size: 12px;
    color: #64748b;
    text-transform: uppercase;
  }
  
  .campaigns-table td:first-child {
    flex-direction: column;
    align-items: flex-start;
    padding-bottom: 10px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 6px;
  }
  
  .campaigns-table td:first-child::before {
    display: none;
  }
  
  .campaign-name {
    font-size: 14px;
  }
  
  .campaign-subject {
    font-size: 12px;
  }
  
  .campaigns-table td:last-child {
    justify-content: flex-end;
    padding-top: 10px;
    border-top: 1px solid #e2e8f0;
    margin-top: 6px;
  }
  
  .campaigns-table td:last-child::before {
    display: none;
  }
  
  .action-btn {
    padding: 8px;
  }
  
  /* Empty State */
  .empty-state {
    padding: 40px 16px;
  }
  
  .empty-state svg {
    width: 48px;
    height: 48px;
  }
  
  .empty-state p {
    font-size: 14px;
  }
  
  /* Modals - fullscreen on mobile */
  .modal-overlay {
    align-items: flex-end;
    padding: 0;
  }
  
  .modal {
    max-width: 100%;
    width: 100%;
    max-height: 90vh;
    border-radius: 16px 16px 0 0;
  }
  
  .preview-modal,
  .analytics-modal {
    max-width: 100%;
  }
  
  .modal-header {
    padding: 16px 18px;
  }
  
  .modal-header h3 {
    font-size: 16px;
  }
  
  .close-btn {
    font-size: 20px;
    padding: 4px;
  }
  
  .modal-body {
    padding: 16px 18px;
    max-height: 70vh;
  }
  
  .preview-subject {
    padding: 10px;
    font-size: 13px;
  }
  
  .preview-content {
    padding: 12px;
    font-size: 14px;
  }
  
  /* Analytics Stats - 2 columns on mobile */
  .analytics-stats {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }
  
  .analytics-stat {
    padding: 12px;
  }
  
  .analytics-stat .label {
    font-size: 11px;
  }
  
  .analytics-stat .value {
    font-size: 15px;
  }
  
  .analytics-section h5 {
    font-size: 13px;
  }
  
  .analytics-section li {
    font-size: 12px;
  }
  
  /* Notification - full width on mobile */
  .notification {
    left: 12px;
    right: 12px;
    bottom: 12px;
    padding: 14px 18px;
    font-size: 13px;
    text-align: center;
  }
}

/* Small mobile devices (max-width: 480px) */
@media (max-width: 480px) {
  .email-marketing-panel {
    padding: 10px;
  }
  
  .panel-header h2 {
    font-size: 16px;
  }
  
  .panel-header p {
    font-size: 12px;
  }
  
  /* Stats - smaller on very small screens */
  .stats-grid {
    gap: 8px;
  }
  
  .stat-card {
    padding: 10px;
  }
  
  .stat-icon {
    width: 32px;
    height: 32px;
  }
  
  .stat-icon svg {
    width: 16px;
    height: 16px;
  }
  
  .stat-value {
    font-size: 16px;
  }
  
  .stat-label {
    font-size: 10px;
  }
  
  /* Compose Card */
  .compose-card {
    padding: 12px;
  }
  
  .compose-card-title {
    font-size: 14px;
  }
  
  .form-group label {
    font-size: 12px;
  }
  
  .form-input,
  .form-textarea {
    padding: 8px 10px;
    font-size: 13px;
  }
  
  /* Toolbar buttons - 2 columns on very small screens */
  .content-toolbar {
    padding: 6px 8px;
  }
  
  .toolbar-btn {
    padding: 5px 8px;
    font-size: 10px;
  }
  
  /* Recipients */
  .recipients-card {
    padding: 10px;
    max-height: 250px;
  }
  
  .recipients-list {
    max-height: 150px;
  }
  
  .recipient-item {
    padding: 6px 8px;
  }
  
  .recipient-name {
    font-size: 12px;
  }
  
  .recipient-email {
    font-size: 10px;
  }
  
  .recipient-bookings {
    padding: 2px 6px;
    font-size: 9px;
  }
  
  /* Buttons */
  .btn {
    padding: 10px 14px;
    font-size: 12px;
  }
  
  /* Campaign cards */
  .campaigns-table tr {
    padding: 10px;
  }
  
  .campaign-name {
    font-size: 13px;
  }
  
  .campaign-subject {
    font-size: 11px;
  }
  
  .status-badge {
    padding: 3px 8px;
    font-size: 10px;
  }
  
  .rate {
    font-size: 10px;
  }
  
  /* Modal */
  .modal-header {
    padding: 12px 14px;
  }
  
  .modal-body {
    padding: 12px 14px;
  }
}

/* Landscape orientation fix for mobile */
@media (max-height: 500px) and (orientation: landscape) {
  .modal {
    max-height: 85vh;
  }
  
  .modal-body {
    max-height: 55vh;
  }
  
  .recipients-card {
    max-height: 200px;
  }
  
  .recipients-list {
    max-height: 120px;
  }
}

/* Touch-friendly improvements */
@media (hover: none) and (pointer: coarse) {
  .toolbar-btn,
  .btn,
  .action-btn,
  .tab,
  .btn-link {
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .recipient-item {
    min-height: 48px;
  }
  
  .recipient-item input[type="checkbox"] {
    width: 20px;
    height: 20px;
  }
}
</style>
