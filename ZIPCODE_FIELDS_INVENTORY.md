# ZIP Code Fields Inventory - Complete System Overview

## Summary
This document identifies ALL ZIP code fields across the entire CAS Private Care application, organized by user role, page/section, and modal/button origin.

---

## 🎯 CLIENT PORTAL (ClientDashboard.vue)

### 1. **Book Service Page - Booking Form**
**Location:** Main Dashboard → Sidebar "Book Service" OR Header "Book Now" button  
**Field:** `bookingData.zipcode`  
**Line:** 636-653  
**Features:**
- Required field with validation (5-digit format)
- Real-time ZIP code lookup displays "City, NY" below input
- Auto-fills city/borough based on ZIP
- Uses `/api/zipcode-lookup/{zip}` endpoint
- **User Journey:** Client Dashboard → Book Service section → Service Location subsection

### 2. **Payment Information Modal - Add Payment Method**
**Location:** Dashboard → Sidebar "Payment Information" → "Add Payment Method" button  
**Field:** Billing ZIP code  
**Line:** 1849  
**Features:**
- Static display field (not fully integrated with Stripe)
- Part of manual card entry form
- **User Journey:** Client Dashboard → Payment Information → Add Payment Method modal

### 3. **Profile Page - Personal Information**
**Location:** Dashboard → Sidebar "Profile" → Account tab → Edit Profile button  
**Field:** `profileData.zip` (stored as `users.zip_code` in database)  
**Line:** 1941-1946  
**Features:**
- Optional field (validation only if entered)
- 5-digit format validation
- Real-time ZIP code lookup
- Displays location confirmation
- **User Journey:** Client Dashboard → Profile section → Edit Profile modal → Personal Information section

---

## 👨‍⚕️ CAREGIVER PORTAL (CaregiverDashboard.vue)

### 4. **Profile Page - Personal Information**
**Location:** Caregiver Dashboard → Sidebar "Profile" → Edit Profile  
**Field:** `profileData.zip` (stored as `users.zip_code`)  
**Line:** 1133-1138  
**Features:**
- Optional field
- 5-digit format validation
- ZIP code lookup with city/state display
- **User Journey:** Caregiver Dashboard → Profile section → Edit Profile

---

## 👔 ADMIN/STAFF PORTAL (AdminStaffDashboard.vue)

### 5. **Staff Management - Add/Edit Marketing Staff**
**Location:** Admin Dashboard → "Staff Management" tab → "Add Marketing Staff" button  
**Field:** `marketingStaffFormData.zip_code`  
**Line:** 826-833  
**Features:**
- Optional field
- 5-digit validation
- Real-time lookup with `lookupMarketingStaffZipCode()`
- **User Journey:** Admin Dashboard → Staff Management → Add/Edit Marketing Staff modal

### 6. **Training Centers Management - Add/Edit Training Center**
**Location:** Admin Dashboard → "Training Centers" tab → "Add Training Center" button  
**Field:** `trainingCenterFormData.zip_code`  
**Line:** 1333-1340  
**Features:**
- Optional field
- 5-digit validation
- Real-time lookup with `lookupTrainingCenterZipCode()`
- **User Journey:** Admin Dashboard → Training Centers → Add/Edit Training Center modal

### 7. **Client Management - Add/Edit Client**
**Location:** Admin Dashboard → "Clients" tab → "Add Client" button  
**Field:** `clientForm.zip_code`  
**Line:** 2186-2193  
**Features:**
- Optional field
- 5-digit validation
- Real-time lookup with `lookupClientZipCode()`
- **Used in Clients Table:** ZIP is resolved to "City, ST" format in table display
- **User Journey:** Admin Dashboard → Clients tab → Add/Edit Client modal

### 8. **Caregiver Management - Add/Edit Caregiver**
**Location:** Admin Dashboard → "Caregivers" tab → "Add Caregiver" button  
**Field:** `caregiverForm.zip_code`  
**Line:** 2361-2368  
**Features:**
- Optional field
- 5-digit validation
- Real-time lookup with `lookupCaregiverZipCode()`
- **Used in Caregivers Table:** ZIP is resolved to "City, ST" format in table display
- **User Journey:** Admin Dashboard → Caregivers tab → Add/Edit Caregiver modal

### 9. **Booking Management - Create Booking**
**Location:** Admin Dashboard → "Bookings" tab → "Create Booking" button  
**Field:** `bookingForm.zipcode`  
**Line:** 2530-2537  
**Features:**
- **REQUIRED** field
- 5-digit validation
- Real-time lookup with `lookupBookingZipCode()`
- **User Journey:** Admin Dashboard → Bookings tab → Create Booking modal → Service Location section

---

## 📢 MARKETING STAFF PORTAL (MarketingDashboard.vue)

### 10. **Profile Page - Personal Information**
**Location:** Marketing Dashboard → Sidebar "Profile" → Edit Profile  
**Field:** `profileData.zip` (stored as `users.zip_code`)  
**Line:** 447-452  
**Features:**
- Optional field
- 5-digit validation
- Real-time lookup with `lookupProfileZipCode()`
- **User Journey:** Marketing Dashboard → Profile section → Edit Profile

---

## 🎓 TRAINING CENTER PORTAL (TrainingDashboard.vue)

### 11. **Profile Page - Personal Information**
**Location:** Training Dashboard → Sidebar "Profile" → Edit Profile  
**Field:** `profileData.zip` (stored as `users.zip_code`)  
**Line:** 387-392  
**Features:**
- Optional field
- 5-digit validation
- Real-time lookup with `lookupProfileZipCode()`
- **User Journey:** Training Dashboard → Profile section → Edit Profile

---

## 🔐 PUBLIC REGISTRATION PAGE (register.blade.php)

### 12. **Registration Form - Account Information**
**Location:** Public website → `/register` page  
**Field:** `zip_code` (name attribute)  
**Line:** 2469-2472  
**Features:**
- **REQUIRED** field for all user types (Client, Caregiver, Training Center, Marketing Staff)
- HTML5 pattern validation: `[0-9]{5}`
- JavaScript-enforced: numbers only, max 5 digits
- Real-time ZIP code lookup displays "City, State" below input
- Uses `/api/zipcode-lookup/{zip}` endpoint
- **User Journey:** Public Website → Register button (header/hero) → Role selection → Registration form

---

## 💳 STRIPE PAYMENT SETUP (StripePaymentSetup.vue)

### 13. **Payment Method Setup - Billing Details**
**Location:** Used in various payment flows  
**Field:** Billing ZIP code (`billingDetails.zip`)  
**Line:** 98, 240  
**Features:**
- Part of Stripe billing_details object
- Sent as `postal_code` to Stripe API
- Used during payment method creation
- **User Journey:** Various payment modals → Billing information section

---

## 📊 ADMIN TABLES WITH ZIP CODE DISPLAY

### Caregivers Table
**Location:** Admin Dashboard → "Caregivers" tab  
**Display Field:** `place_indicator` (resolved from `zip_code`)  
**Features:**
- Shows "City, ST" format (e.g., "Brooklyn, NY")
- Resolved via batch ZIP lookup on table load
- Falls back to "Loading..." then resolves asynchronously

### Clients Table
**Location:** Admin Dashboard → "Clients" tab  
**Display Field:** `place_indicator` (resolved from `zip_code`)  
**Features:**
- Shows "City, ST" format
- Resolved via batch ZIP lookup on table load
- Used for location-based filtering/sorting

---

## 🔍 BACKEND SERVICES

### ZIP Code Lookup API Endpoints

#### 1. **Public API Route** (`routes/api.php`)
**Endpoint:** `/api/zipcode-lookup/{zip}`  
**Line:** 30-36  
**Features:**
- Uses `ZipCodeService::lookupZipCode()`
- Returns JSON: `{ "location": "City, NY" }`
- Accessible without authentication

#### 2. **Web Route** (`routes/web.php`)
**Endpoint:** `/api/zipcode-lookup/{zip}` (duplicate, legacy)  
**Line:** 100-131  
**Features:**
- Validates ZIP format (5 digits)
- Uses `ZipCodeService`
- Returns JSON with error handling

### ZipCodeService Class
**File:** `app/Services/ZipCodeService.php`  
**Purpose:** Centralized ZIP code to city/state mapping service  
**Features:**
- Comprehensive NY ZIP code database
- Static mapping for fast lookups
- Used by all portals

---

## 📋 VALIDATION RULES ACROSS SYSTEM

### Required Fields (Must Enter ZIP)
1. ✅ Client Booking Form (`bookingData.zipcode`)
2. ✅ Admin Create Booking Modal (`bookingForm.zipcode`)
3. ✅ Public Registration Page (`zip_code`)

### Optional Fields (Validation Only If Entered)
1. ⭕ Client Profile (`profileData.zip`)
2. ⭕ Caregiver Profile (`profileData.zip`)
3. ⭕ Marketing Staff Profile (`profileData.zip`)
4. ⭕ Training Center Profile (`profileData.zip`)
5. ⭕ Admin - Add/Edit Client (`clientForm.zip_code`)
6. ⭕ Admin - Add/Edit Caregiver (`caregiverForm.zip_code`)
7. ⭕ Admin - Add/Edit Marketing Staff (`marketingStaffFormData.zip_code`)
8. ⭕ Admin - Add/Edit Training Center (`trainingCenterFormData.zip_code`)

### Standard Validation Pattern
```javascript
:rules="[v => !v || /^\d{5}$/.test(v) || 'Please enter a valid 5-digit ZIP code']"
```

### Required Validation Pattern
```javascript
:rules="[v => !!v || 'ZIP code is required', v => /^\d{5}$/.test(v) || 'Please enter a valid 5-digit ZIP code']"
```

---

## 🗄️ DATABASE STORAGE

### Users Table Column
**Column:** `zip_code` (VARCHAR)  
**Used For:**
- Client profiles
- Caregiver profiles
- Marketing staff profiles
- Training center profiles

### Bookings Table Column
**Column:** `zipcode` (VARCHAR)  
**Used For:**
- Service location ZIP code for each booking
- Used in receipt generation
- Displayed in booking details

---

## 🎯 USER JOURNEY SUMMARY

### Client Journey
1. **Registration** → Enter ZIP code (required)
2. **Book Service** → Enter service location ZIP (required)
3. **Profile Edit** → Update ZIP (optional)
4. **Payment Setup** → Enter billing ZIP (for Stripe)

### Caregiver Journey
1. **Registration** → Enter ZIP code (required)
2. **Profile Edit** → Update ZIP (optional)

### Admin Journey
1. **Manage Clients** → View ZIP in table, edit via modal (optional)
2. **Manage Caregivers** → View ZIP in table, edit via modal (optional)
3. **Create Bookings** → Enter service ZIP (required)
4. **Manage Staff** → Add/edit marketing staff ZIP (optional)
5. **Manage Training Centers** → Add/edit training center ZIP (optional)

### Marketing Staff Journey
1. **Registration** → Enter ZIP code (required)
2. **Profile Edit** → Update ZIP (optional)

### Training Center Journey
1. **Registration** → Enter ZIP code (required)
2. **Profile Edit** → Update ZIP (optional)

---

## 🔧 TECHNICAL IMPLEMENTATION NOTES

### ZIP Code Lookup Flow
1. User enters 5 digits
2. Frontend validates format (`/^\d{5}$/`)
3. Calls `/api/zipcode-lookup/{zip}`
4. Backend queries `ZipCodeService`
5. Returns "City, State" format
6. Frontend displays below input field

### Batch Resolution (Admin Tables)
1. Admin loads Caregivers/Clients table
2. System collects all unique ZIPs
3. Async batch lookup via `resolveAllZipCodes()`
4. Updates table `place_indicator` field
5. Vue reactivity refreshes display

### Caching Strategy
- Frontend caches ZIP lookups in `zipCodeMap` object
- Prevents duplicate API calls for same ZIP
- Cache persists during session only

---

## 📝 TOTAL COUNT

**Total ZIP Code Input Fields:** 13 unique locations  
**Total User Portals:** 5 (Client, Caregiver, Admin, Marketing, Training)  
**Total Modals with ZIP:** 8 modals  
**Total Tables Displaying ZIP:** 2 tables (Caregivers, Clients)  
**Required Fields:** 3  
**Optional Fields:** 10  

---

## 🚀 FUTURE CONSIDERATIONS

1. Consider unifying field names (`zipcode` vs `zip_code`)
2. Add ZIP code autocomplete feature
3. Implement client-side caching for frequently used ZIPs
4. Add geographic filtering in Browse Caregivers by ZIP range
5. Consider adding ZIP code radius search for caregiver matching

---

**Last Updated:** January 11, 2026  
**Document Version:** 1.0  
**System Version:** CAS Private Care v1.2.0
