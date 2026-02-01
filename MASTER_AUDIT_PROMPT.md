# 🔍 MASTER AUDIT PROMPT: CAS Private Care LLC - Professional Website Audit

## Instructions for AI Assistant

You are a **Senior Website Auditor** with expertise in:
- Full-stack web application analysis
- UX/UI assessment
- Payment system integration
- Multi-tenant SaaS platforms
- Security & compliance auditing
- Performance optimization
- Accessibility standards (WCAG 2.1)

Your task is to perform a **comprehensive audit** of the CAS Private Care LLC web application across **15 major categories**, rating each 1-100 with detailed findings.

---

# 📋 AUDIT FRAMEWORK

## PART 1: USER FLOW ANALYSIS (Rate Each User Type)

### 1.1 CLIENT USER FLOW (Target: 100/100)

**Entry Points:**
- Landing page (`/`)
- Login (`/login`)
- Registration (`/register`)
- Direct booking (`/client/dashboard-vue`)

**Complete Flow to Audit:**
```
1. DISCOVERY PHASE
   └─ Landing Page → Services Info → Pricing → Register/Login
   
2. ONBOARDING PHASE
   └─ Registration Form → Email Verification → Profile Setup → Payment Method Setup
   
3. BOOKING PHASE
   └─ Dashboard → Create Booking → Select Service Type → Choose Dates/Hours
   └─ Select Location (NY Counties) → Set Budget → Choose Duty Type
   └─ Add Referral Code (optional) → Review Booking → Submit
   
4. PAYMENT PHASE
   └─ Booking Created → Payment Required Alert → Payment Page
   └─ Stripe Elements → Enter Card → 3D Secure (if required) → Payment Success
   └─ Auto-charge setup for recurring → Payment Confirmation
   
5. SERVICE PHASE
   └─ Caregiver Assigned Notification → View Assignment Details
   └─ Track Service Progress → View Time Logs → Rate Caregiver
   
6. POST-SERVICE PHASE
   └─ View Invoice → Download Receipt → Leave Review
   └─ Rebooking Option → Recurring Booking Setup
```

**Check Points:**
- [ ] Registration form has proper validation & autocomplete
- [ ] Email verification flow works
- [ ] Payment method linking with Stripe
- [ ] Booking form captures all required info
- [ ] Real-time price calculation
- [ ] Booking status updates properly
- [ ] Notifications received (email + in-app)
- [ ] Can view caregiver profile before assignment
- [ ] Rating/review submission works
- [ ] Payment history accessible
- [ ] Can manage recurring bookings
- [ ] Profile edit functionality
- [ ] Password reset flow

**Dashboard Sections to Audit:**
| Section | Component | Key Features |
|---------|-----------|--------------|
| Dashboard | `ClientDashboard.vue` | Stats cards, quick actions |
| My Bookings | `ClientMyBookings.vue` | Active, pending, completed tabs |
| New Booking | `ClientBookingForm.vue` | Multi-step booking wizard |
| Payments | `ClientPaymentSection.vue` | Payment history, methods |
| Browse Caregivers | `BrowseCaregivers.vue` | Search, filter, view profiles |
| Analytics | `ClientAnalytics.vue` | Spending charts, usage stats |
| Profile | `ClientProfile.vue` | Edit profile, change password |

---

### 1.2 CAREGIVER USER FLOW (Target: 100/100)

**Entry Points:**
- Partner application page
- Login (`/login`)
- Dashboard (`/caregiver/dashboard-vue`)

**Complete Flow to Audit:**
```
1. APPLICATION PHASE
   └─ Landing → Become a Partner → Select "Caregiver"
   └─ Registration Form → Upload Documents → Select Training Center
   └─ Submit Application → Pending Status
   
2. APPROVAL PHASE
   └─ Admin Reviews Application → Training Verification
   └─ Background Check → Approval/Rejection Notification
   
3. ONBOARDING PHASE
   └─ Login → Bank Account Setup → Stripe Connect Onboarding
   └─ CustomBankOnboarding Component → Enter Bank Details
   └─ Verification → Connected Status
   
4. ASSIGNMENT PHASE
   └─ Dashboard → View Available Clients → Apply for Jobs
   └─ Receive Assignment Notification → View Assignment Details
   └─ Accept/Decline Assignment
   
5. SERVICE PHASE
   └─ View Schedule → Clock In (location-based) → Active Shift
   └─ Clock Out → Earnings Calculated → Commission Split Applied
   
6. EARNINGS PHASE
   └─ View Pending Earnings → Weekly Payout Schedule
   └─ Stripe Transfer → Bank Deposit (2-3 days)
   └─ View Earnings History → Download Pay Stubs
```

**Commission Structure to Verify:**
```
CLIENT PAYS: $45/hr (no referral) or $40/hr (with referral)
├─ Caregiver:         $28/hr (62-70%)
├─ Marketing Partner:  $1/hr (if referred)
├─ Training Center:   $0.50/hr (if trained)
└─ Platform Fee:      Remainder
```

**Dashboard Sections to Audit:**
| Section | Features to Check |
|---------|-------------------|
| Dashboard Overview | Earnings stats, upcoming shifts |
| My Assignments | Active, pending, completed |
| Available Clients | Apply for new clients |
| Time Tracking | Clock in/out, hours log |
| Earnings | Pending, paid, history |
| Payment Setup | Bank account connection |
| Profile | Personal info, certifications |
| Reviews | Client feedback |

---

### 1.3 HOUSEKEEPER USER FLOW (Target: 100/100)

**Complete Flow to Audit:**
```
1. APPLICATION → Same as Caregiver
2. ONBOARDING → Same bank setup flow
3. SERVICE → Housekeeping-specific assignments
4. EARNINGS → Same payout structure
```

**Specific Checks:**
- [ ] Dashboard uses correct user_type="housekeeper"
- [ ] Notifications reference "housekeeper" not "caregiver"
- [ ] Different hourly rate structure
- [ ] Housekeeping-specific job types displayed

---

### 1.4 ADMIN USER FLOW (Target: 100/100)

**Complete Flow to Audit:**
```
1. DASHBOARD OVERVIEW
   └─ View all platform stats → Revenue, bookings, users
   └─ Quick actions → Pending approvals, alerts
   
2. USER MANAGEMENT
   └─ View all users → Filter by type → Search
   └─ Create/Edit/Delete users → Change status
   └─ View detailed profiles → Activity logs
   
3. APPLICATION MANAGEMENT
   └─ View pending applications → Review documents
   └─ Approve/Reject with reason → Send notification
   
4. BOOKING MANAGEMENT
   └─ View all bookings → Filter by status
   └─ Assign caregivers → Manage assignments
   └─ Handle disputes → Cancellations
   
5. FINANCIAL MANAGEMENT
   └─ View payment stats → Revenue charts
   └─ Process caregiver payouts → Pay Marketing
   └─ Pay Training Centers → View transactions
   └─ Commission reports → 1099 generation
   
6. TIME TRACKING OVERSIGHT
   └─ View all time entries → Approve hours
   └─ Handle disputes → Manual adjustments
   
7. STAFF MANAGEMENT
   └─ Manage Admin Staff → Set permissions
   └─ Manage Marketing Staff → View referrals
   └─ Manage Training Centers → View commissions
   
8. ANALYTICS & REPORTS
   └─ Platform metrics → User growth
   └─ Revenue analytics → Booking trends
   └─ Export reports
   
9. SYSTEM SETTINGS
   └─ Platform settings → Email templates
   └─ Announcements → Notifications
```

**Dashboard Tabs to Audit:**
| Tab | Features |
|-----|----------|
| Dashboard | Stats cards, quick actions, charts |
| Users | All users table with CRUD |
| Caregivers | Caregiver management, status |
| Housekeepers | Housekeeper management |
| Clients | Client management |
| Bookings | All bookings, assignments |
| Time Tracking | All time entries |
| Payments | Financial overview, payouts |
| Marketing Staff | Referrals, commissions |
| Training Centers | Trained caregivers, commissions |
| Admin Staff | Staff permissions |
| Applications | Pending approvals |
| Reviews | All reviews |
| Announcements | System announcements |
| Analytics | Charts, reports |
| Profile | Admin profile settings |

---

### 1.5 ADMIN STAFF USER FLOW (Target: 100/100)

**Complete Flow to Audit:**
```
1. Login → Limited Dashboard View
2. Only see permitted sections (based on permissions object)
3. Cannot access super-admin functions
```

**Permission System to Verify:**
```javascript
permissions = {
  dashboard: true/false,
  users: true/false,
  caregivers: true/false,
  housekeepers: true/false,
  clients: true/false,
  bookings: true/false,
  applications: true/false,
  'password-resets': true/false,
  'client-bookings': true/false,
  'time-tracking': true/false,
  reviews: true/false,
  announcements: true/false,
  payments: true/false,
  analytics: true/false,
  profile: true/false
}
```

---

### 1.6 MARKETING PARTNER USER FLOW (Target: 100/100)

**Complete Flow to Audit:**
```
1. APPLICATION PHASE
   └─ Apply as Marketing Partner → Submit details
   └─ Admin approval → Notification
   
2. ONBOARDING PHASE
   └─ Login → Bank Setup → Stripe Connect
   └─ CustomBankOnboarding with role="marketing"
   
3. REFERRAL PHASE
   └─ Dashboard → Generate Referral Code
   └─ Share with clients → Track usage
   
4. COMMISSION PHASE
   └─ View referred clients → See active bookings
   └─ Track pending commissions → $1/hr per referral
   └─ Admin pays commission → Stripe Transfer
   └─ View payment history
```

**Dashboard Sections to Audit:**
| Section | Features |
|---------|----------|
| Dashboard | Commission overview stats |
| Referrals | Referral code management |
| Clients | Referred clients list |
| Earnings | Pending vs paid commissions |
| Payments | Bank account, payout history |
| Profile | Personal information |

**Commission Flow to Verify:**
```
Client books using referral code
    ↓
Caregiver clocks out (12 hours)
    ↓
System calculates: 12hrs × $1 = $12 commission
    ↓
Marketing sees pending commission
    ↓
Admin clicks "Pay" in Marketing Staff tab
    ↓
Stripe Transfer to Marketing's Connect account
    ↓
Marketing receives in bank
```

---

### 1.7 TRAINING CENTER USER FLOW (Target: 100/100)

**Complete Flow to Audit:**
```
1. APPLICATION PHASE
   └─ Register as Training Center → Submit business info
   └─ Admin verification → Approval
   
2. ONBOARDING PHASE
   └─ Login → Bank Setup → Stripe Connect
   └─ CustomBankOnboarding with role="training"
   
3. CAREGIVER TRAINING PHASE
   └─ Dashboard → View Pending Caregivers
   └─ Approve/Certify caregivers → Link to center
   
4. COMMISSION PHASE
   └─ Trained caregiver works → $0.50/hr tracked
   └─ View pending commissions → Monthly accumulation
   └─ Admin pays commission → Stripe Transfer
   └─ View payment history
```

**Dashboard Sections to Audit:**
| Section | Features |
|---------|----------|
| Dashboard | Commission overview |
| Pending Caregivers | Approve/Reject |
| Trained Caregivers | List of certified |
| Earnings | Commission breakdown |
| Payments | Bank account, history |
| Profile | Center information |

**Commission Flow to Verify:**
```
Caregiver applies with Training Center selected
    ↓
Training Center approves caregiver
    ↓
Caregiver works 400 hours
    ↓
System calculates: 400hrs × $0.50 = $200 commission
    ↓
Admin clicks "Pay" in Training Centers tab
    ↓
Stripe Transfer to Training's Connect account
```

---

# 📋 PART 2: PAYMENT SYSTEM AUDIT

## 2.1 STRIPE INTEGRATION (Target: 100/100)

**Components to Audit:**
| Component | Purpose | File |
|-----------|---------|------|
| PaymentPage | Client payment processing | `PaymentPage.vue` |
| PaymentPageStripeElements | Stripe Elements UI | `PaymentPageStripeElements.vue` |
| ClientPaymentSetup | Save payment methods | `ClientPaymentSetup.vue` |
| CustomBankOnboarding | Contractor bank setup | `CustomBankOnboarding.vue` |
| StripeConnectOnboarding | Connect account setup | `StripeConnectOnboarding.vue` |

**Payment Flows to Verify:**

### A. Client Payment Flow
```
1. Client creates booking → Pending status
2. Navigate to payment page
3. Stripe Elements loads → Enter card details
4. Create PaymentMethod → Create PaymentIntent
5. Confirm payment → Handle 3D Secure if required
6. Webhook receives payment_intent.succeeded
7. Update booking status → confirmed
8. Send confirmation emails
```

### B. Caregiver Payout Flow
```
1. Caregiver clocks out → Earnings calculated
2. TimeTracking record created with:
   - caregiver_earnings
   - marketing_partner_commission
   - training_center_commission
   - agency_commission
3. Admin views pending payouts
4. Admin clicks "Pay" → Stripe Transfer created
5. TimeTracking.payment_status = 'paid'
6. Caregiver receives bank deposit
```

### C. Marketing Commission Payout
```
API: POST /api/stripe/admin/pay-marketing-commission/{userId}
Controller: StripeController@payMarketingCommission
Service: StripePaymentService@transferToMarketing
```

### D. Training Center Commission Payout
```
API: POST /api/stripe/admin/pay-training-commission/{userId}
Controller: StripeController@payTrainingCommission
Service: StripePaymentService@transferToTraining
```

**API Endpoints to Test:**
| Endpoint | Purpose |
|----------|---------|
| POST /api/v2/stripe/create-payment-intent | Create payment |
| POST /api/v2/stripe/confirm-payment | Confirm payment |
| POST /api/stripe/connect-bank-account | Caregiver bank |
| POST /api/stripe/connect-bank-account-marketing | Marketing bank |
| POST /api/stripe/connect-bank-account-training | Training bank |
| POST /api/stripe/housekeeper/connect-payout-method | Housekeeper bank |
| POST /api/webhooks/stripe | Webhook handler |

**Check Points:**
- [ ] PaymentIntent created with correct amount
- [ ] 3D Secure handling implemented
- [ ] Webhook signature verification
- [ ] Idempotency keys for transfers
- [ ] Error handling for failed payments
- [ ] Retry logic for failed transfers
- [ ] Proper status updates in database
- [ ] Commission calculation accuracy
- [ ] Multi-currency support (if applicable)

---

## 2.2 COMMISSION SPLIT SYSTEM (Target: 100/100)

**Rate Structure:**
| Role | Rate | Condition |
|------|------|-----------|
| Client Pays | $45/hr | No referral code |
| Client Pays | $40/hr | With referral code |
| Caregiver | $28/hr | Fixed |
| Marketing | $1/hr | If referral code used |
| Training | $0.50/hr | If caregiver trained |
| Platform | Remainder | Variable |

**Database Fields (TimeTracking):**
```php
- total_hours
- total_client_charge
- caregiver_earnings
- marketing_partner_id
- marketing_partner_commission
- training_center_user_id
- training_center_commission
- agency_commission
- payment_status (pending/paid)
- stripe_charge_id
- paid_at
```

**Calculation Logic to Verify:**
```php
$hours = 12;
$hasReferral = true;
$hasTrained = true;

$clientRate = $hasReferral ? 40 : 45;
$totalClientCharge = $hours * $clientRate; // $480

$caregiverEarnings = $hours * 28; // $336
$marketingCommission = $hasReferral ? ($hours * 1) : 0; // $12
$trainingCommission = $hasTrained ? ($hours * 0.50) : 0; // $6
$agencyCommission = $totalClientCharge - $caregiverEarnings - $marketingCommission - $trainingCommission;
// $480 - $336 - $12 - $6 = $126
```

---

# 📋 PART 3: UI/UX COMPONENT AUDIT

## 3.1 DASHBOARD COMPONENTS (Target: 100/100)

**Shared Components:**
| Component | Usage | File |
|-----------|-------|------|
| DashboardTemplate | Base layout for all dashboards | `DashboardTemplate.vue` |
| StatCard | Stats display cards | `StatCard.vue` |
| NotificationCenter | In-app notifications | `NotificationCenter.vue` |
| BreadcrumbNav | Navigation breadcrumbs | `BreadcrumbNav.vue` |
| SessionTimeoutWarning | Session management | `SessionTimeoutWarning.vue` |

**Check Points:**
- [ ] Consistent styling across all dashboards
- [ ] Responsive design (mobile/tablet/desktop)
- [ ] Dark mode support
- [ ] Loading states (skeletons)
- [ ] Error states
- [ ] Empty states
- [ ] Proper icons (Material Design Icons)

---

## 3.2 FORMS & INPUTS (Target: 100/100)

**Check Points:**
- [ ] All forms have proper validation
- [ ] Autocomplete attributes present
- [ ] inputmode for numeric fields
- [ ] Required fields marked
- [ ] Error messages display correctly
- [ ] Success feedback provided
- [ ] Loading states during submission

**Forms to Audit:**
| Form | Location | Fields |
|------|----------|--------|
| Login | `/login` | email, password |
| Register | `/register` | name, email, phone, password, user_type |
| Booking | Client Dashboard | service_type, dates, location, budget |
| Payment | Payment Page | card details (Stripe) |
| Bank Account | Bank Onboarding | routing, account, type |
| Profile | All dashboards | various fields |

---

## 3.3 TABLES & DATA DISPLAY (Target: 100/100)

**Tables to Audit:**
| Table | Dashboard | Features Needed |
|-------|-----------|-----------------|
| Users | Admin | Search, filter, sort, pagination |
| Caregivers | Admin | Status badges, actions |
| Bookings | Admin | Status filter, date range |
| Time Tracking | Admin | Approval actions |
| Payments | Admin | Pay buttons, status |
| Marketing Staff | Admin | Commission display |
| Training Centers | Admin | Commission display |

**Check Points:**
- [ ] Proper column headers
- [ ] Sortable columns
- [ ] Search functionality
- [ ] Filter options
- [ ] Pagination (server-side for large datasets)
- [ ] Row actions (view, edit, delete)
- [ ] Status badges with appropriate colors
- [ ] Loading states
- [ ] Empty state messages
- [ ] Export functionality

---

## 3.4 MODALS & DIALOGS (Target: 100/100)

**Modals to Audit:**
| Modal | Purpose | Triggers |
|-------|---------|----------|
| User Details | View full profile | Click user row |
| Caregiver Profile | View caregiver details | Click caregiver |
| Booking Details | View booking info | Click booking |
| Payment Confirmation | Confirm payout | Pay button |
| Rating Modal | Submit review | Rate button |
| Alert Modal | Confirm destructive actions | Delete buttons |

**Check Points:**
- [ ] Accessible (focus trap, escape to close)
- [ ] Backdrop click to close (where appropriate)
- [ ] Clear title and content
- [ ] Action buttons properly labeled
- [ ] Loading state during actions
- [ ] Close button visible
- [ ] Mobile responsive

---

## 3.5 CARDS & WIDGETS (Target: 100/100)

**Widgets to Audit:**
| Widget | Dashboard | Content |
|--------|-----------|---------|
| Stats Cards | All | Numbers with icons, trends |
| Booking Cards | Client | Service details, status |
| Assignment Cards | Caregiver | Client info, schedule |
| Earnings Cards | Caregiver/Marketing/Training | Pending vs paid |
| Quick Actions | Admin | Common tasks |
| Charts | Admin Analytics | Revenue, users, bookings |

**Check Points:**
- [ ] Consistent card styling
- [ ] Appropriate use of elevation/shadow
- [ ] Click interactions where expected
- [ ] Hover states
- [ ] Mobile stacking

---

# 📋 PART 4: TECHNICAL AUDIT

## 4.1 BACKEND ARCHITECTURE (Target: 100/100)

**Controllers to Audit:**
| Controller | Purpose | Lines (approx) |
|------------|---------|----------------|
| DashboardController | Dashboard data | 700+ |
| AdminController | Admin functions | 3000+ |
| StripeController | Payment processing | 1200+ |
| TimeTrackingController | Clock in/out | 500+ |
| BookingController | Booking CRUD | 600+ |
| UserProfileController | Profile management | 200+ |

**Services to Audit:**
| Service | Purpose |
|---------|---------|
| StripePaymentService | All Stripe operations |
| NotificationService | Email/in-app notifications |
| QueryCacheService | Database caching |

**Check Points:**
- [ ] Controllers not exceeding 500 lines (extract to services)
- [ ] Eager loading on all queries
- [ ] N+1 query problems resolved
- [ ] Proper error handling with try/catch
- [ ] Validation using Form Requests
- [ ] Authorization checks on all endpoints
- [ ] Rate limiting on sensitive endpoints

---

## 4.2 API ROUTES AUDIT (Target: 100/100)

**Route Groups:**
| Prefix | Middleware | Purpose |
|--------|------------|---------|
| /api | sanctum | General API |
| /api/admin | auth, user.type:admin | Admin endpoints |
| /api/client | auth, user.type:client | Client endpoints |
| /api/caregiver | auth, user.type:caregiver | Caregiver endpoints |
| /api/marketing | auth, user.type:marketing | Marketing endpoints |
| /api/training | auth, user.type:training | Training endpoints |

**Check Points:**
- [ ] All routes properly authenticated
- [ ] Role-based access control working
- [ ] Consistent route naming
- [ ] RESTful conventions followed
- [ ] API versioning (v2 prefix where used)

---

## 4.3 DATABASE MODELS (Target: 100/100)

**Models to Audit:**
| Model | Key Relationships |
|-------|-------------------|
| User | hasOne: Caregiver, Client, Housekeeper |
| Booking | belongsTo: Client; hasMany: Payments, Assignments |
| TimeTracking | belongsTo: Booking, Caregiver |
| Payment | belongsTo: Booking |
| Caregiver | belongsTo: User, TrainingCenter |
| ReferralCode | belongsTo: User (marketing) |

**Check Points:**
- [ ] Relationships properly defined
- [ ] Fillable/guarded attributes set
- [ ] Casts for dates, booleans, decimals
- [ ] Accessors/mutators where needed
- [ ] Soft deletes where appropriate
- [ ] Indexes on frequently queried columns

---

## 4.4 FRONTEND ARCHITECTURE (Target: 100/100)

**Vue Components Structure:**
```
resources/js/components/
├── [Dashboard].vue          # Main dashboard files
├── admin/                   # Admin-specific components
├── client/                  # Client-specific components
├── shared/                  # Reusable components
├── Global/                  # Global utilities
└── A11y/                    # Accessibility components
```

**Check Points:**
- [ ] Components follow single responsibility
- [ ] Props properly typed with defaults
- [ ] Emits defined for child-to-parent communication
- [ ] Composables for shared logic
- [ ] No inline styles (use CSS classes)
- [ ] Proper use of computed properties
- [ ] Watchers used sparingly

---

## 4.5 SECURITY AUDIT (Target: 100/100)

**Check Points:**
- [ ] CSRF protection on all forms
- [ ] SQL injection prevention (Eloquent)
- [ ] XSS prevention (Vue auto-escaping)
- [ ] Authentication required on protected routes
- [ ] Authorization checks before actions
- [ ] Sensitive data not exposed in API responses
- [ ] Passwords hashed (bcrypt)
- [ ] Stripe webhook signature verification
- [ ] Rate limiting on auth endpoints
- [ ] HTTPS enforced
- [ ] Security headers present

**Headers to Verify:**
```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Content-Security-Policy: [appropriate policy]
Strict-Transport-Security: max-age=31536000
Referrer-Policy: strict-origin-when-cross-origin
```

---

## 4.6 PERFORMANCE AUDIT (Target: 100/100)

**Check Points:**
- [ ] Database queries optimized (< 20 per page)
- [ ] Caching implemented for static data
- [ ] Assets minified and bundled
- [ ] Images lazy-loaded
- [ ] Large components code-split
- [ ] API responses paginated
- [ ] Service worker for offline support
- [ ] CDN for static assets

**Metrics to Target:**
| Metric | Target |
|--------|--------|
| First Contentful Paint | < 1.8s |
| Largest Contentful Paint | < 2.5s |
| Time to Interactive | < 3.8s |
| Cumulative Layout Shift | < 0.1 |
| Total Blocking Time | < 200ms |

---

## 4.7 ACCESSIBILITY AUDIT (Target: 100/100)

**Check Points:**
- [ ] All images have alt text
- [ ] Form labels associated with inputs
- [ ] Color contrast ratios meet WCAG AA
- [ ] Keyboard navigation works
- [ ] Focus indicators visible
- [ ] Skip navigation link present
- [ ] ARIA labels where needed
- [ ] Screen reader announcements
- [ ] Reduced motion support

**Components to Verify:**
| Component | Purpose |
|-----------|---------|
| SkipNavigation | Skip to main content |
| AriaAnnouncer | Screen reader updates |
| AccessibilityHelper | A11y utilities |

---

# 📋 PART 5: SPECIFIC FEATURES AUDIT

## 5.1 NOTIFICATION SYSTEM (Target: 100/100)

**Notification Types:**
| Type | Trigger | Recipients |
|------|---------|------------|
| Booking Created | New booking | Admin, Client |
| Booking Confirmed | Payment complete | Client, Caregiver |
| Assignment Created | Caregiver assigned | Caregiver |
| Clock In | Shift started | Client |
| Clock Out | Shift ended | Client, Admin |
| Payment Received | Payout processed | Caregiver/Marketing/Training |
| Review Received | Rating submitted | Caregiver |

**Channels:**
- [ ] In-app notifications (NotificationCenter)
- [ ] Email notifications
- [ ] Push notifications (if implemented)

---

## 5.2 TIME TRACKING SYSTEM (Target: 100/100)

**Flow:**
```
Caregiver → Clock In → Active Shift
    ↓
Work Period
    ↓
Caregiver → Clock Out → Earnings Calculated
    ↓
TimeTracking Record Created:
- clock_in_time
- clock_out_time
- total_hours (minute precision)
- caregiver_earnings
- marketing_partner_commission
- training_center_commission
- agency_commission
- payment_status: pending
```

**Check Points:**
- [ ] Minute-based calculation accuracy
- [ ] Timezone handling
- [ ] Break time tracking (if applicable)
- [ ] Manual time adjustments (admin only)
- [ ] Time entry approval workflow

---

## 5.3 REFERRAL SYSTEM (Target: 100/100)

**Flow:**
```
Marketing Partner creates referral code
    ↓
Client enters code during booking
    ↓
Booking linked to marketing_partner_id
    ↓
Client gets $5/hr discount ($45 → $40)
    ↓
Marketing earns $1/hr commission
```

**Check Points:**
- [ ] Referral code validation
- [ ] One-time use vs reusable codes
- [ ] Commission tracking accuracy
- [ ] Referral source attribution

---

## 5.4 REVIEW SYSTEM (Target: 100/100)

**Flow:**
```
Client completes booking → Prompt for review
    ↓
RatingModal → Select stars → Add comment
    ↓
Submit → Review stored → Caregiver rating updated
    ↓
Admin can view all reviews
```

**Check Points:**
- [ ] 1-5 star rating
- [ ] Optional comment
- [ ] Reviews visible on caregiver profile
- [ ] Average rating calculation
- [ ] Moderation capability (admin)

---

# 📋 AUDIT SCORING TEMPLATE

## Rate Each Category 1-100:

### USER FLOWS
| Category | Score | Notes |
|----------|-------|-------|
| 1.1 Client Flow | /100 | |
| 1.2 Caregiver Flow | /100 | |
| 1.3 Housekeeper Flow | /100 | |
| 1.4 Admin Flow | /100 | |
| 1.5 Admin Staff Flow | /100 | |
| 1.6 Marketing Flow | /100 | |
| 1.7 Training Center Flow | /100 | |

### PAYMENT SYSTEM
| Category | Score | Notes |
|----------|-------|-------|
| 2.1 Stripe Integration | /100 | |
| 2.2 Commission Split | /100 | |

### UI/UX COMPONENTS
| Category | Score | Notes |
|----------|-------|-------|
| 3.1 Dashboard Components | /100 | |
| 3.2 Forms & Inputs | /100 | |
| 3.3 Tables & Data | /100 | |
| 3.4 Modals & Dialogs | /100 | |
| 3.5 Cards & Widgets | /100 | |

### TECHNICAL
| Category | Score | Notes |
|----------|-------|-------|
| 4.1 Backend Architecture | /100 | |
| 4.2 API Routes | /100 | |
| 4.3 Database Models | /100 | |
| 4.4 Frontend Architecture | /100 | |
| 4.5 Security | /100 | |
| 4.6 Performance | /100 | |
| 4.7 Accessibility | /100 | |

### FEATURES
| Category | Score | Notes |
|----------|-------|-------|
| 5.1 Notification System | /100 | |
| 5.2 Time Tracking | /100 | |
| 5.3 Referral System | /100 | |
| 5.4 Review System | /100 | |

---

## OVERALL SCORE CALCULATION

```
User Flows (7 categories × weight 2)     = __ / 1400
Payment System (2 categories × weight 3) = __ / 600
UI/UX (5 categories × weight 1.5)        = __ / 750
Technical (7 categories × weight 2)      = __ / 1400
Features (4 categories × weight 1)       = __ / 400
                                          ─────────
TOTAL WEIGHTED SCORE                     = __ / 4550

FINAL PERCENTAGE: (Total / 4550) × 100 = ___%
```

---

# 📋 ACTION ITEMS TEMPLATE

## Critical Issues (Must Fix)
| Issue | Location | Impact | Priority |
|-------|----------|--------|----------|
| | | | P0 |

## Major Issues (Should Fix)
| Issue | Location | Impact | Priority |
|-------|----------|--------|----------|
| | | | P1 |

## Minor Issues (Nice to Fix)
| Issue | Location | Impact | Priority |
|-------|----------|--------|----------|
| | | | P2 |

## Improvement Suggestions
| Suggestion | Location | Benefit |
|------------|----------|---------|
| | | |

---

# 📋 FILES TO ANALYZE

## Key Backend Files:
```
app/Http/Controllers/
├── AdminController.php
├── AuthController.php
├── BookingController.php
├── DashboardController.php
├── DashboardRedirectController.php
├── HousekeeperController.php
├── MarketingStaffController.php
├── StripeController.php
├── TimeTrackingController.php
├── TrainingCenterController.php
└── Admin/
    ├── AdminPaymentController.php
    ├── ReportAdminController.php
    ├── StaffAdminController.php
    └── UserAdminController.php

app/Services/
├── StripePaymentService.php
└── NotificationService.php

app/Models/
├── User.php
├── Booking.php
├── TimeTracking.php
├── Payment.php
├── Caregiver.php
├── Housekeeper.php
└── ReferralCode.php
```

## Key Frontend Files:
```
resources/js/components/
├── AdminDashboard.vue (~15000 lines - NEEDS SPLIT)
├── AdminStaffDashboard.vue
├── CaregiverDashboard.vue
├── ClientDashboard.vue
├── HousekeeperDashboard.vue
├── MarketingDashboard.vue
├── TrainingDashboard.vue
├── PaymentPage.vue
├── CustomBankOnboarding.vue
└── DashboardTemplate.vue
```

## Route Files:
```
routes/
├── web.php
└── api.php
```

---

# 📋 AUDIT EXECUTION COMMAND

To perform this audit, analyze each file systematically:

1. **Read all dashboard components** - Verify user flows
2. **Read all controllers** - Check backend logic
3. **Read route files** - Verify API structure
4. **Read models** - Check relationships
5. **Run through each user flow manually** - Document findings
6. **Check Stripe integration** - Verify payment flows
7. **Test commission calculations** - Verify math
8. **Assess UI/UX** - Check consistency
9. **Run security checks** - Verify protections
10. **Run performance tests** - Check load times

---

**START AUDIT: Analyze the codebase systematically and provide ratings for each category with specific findings and recommendations.**
