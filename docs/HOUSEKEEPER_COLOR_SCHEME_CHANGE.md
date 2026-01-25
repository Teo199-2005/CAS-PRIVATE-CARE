# Housekeeper Color Scheme Change - Green to Purple/Violet

## Overview
Changed all housekeeper-related color schemes from green to purple/violet to differentiate from caregivers and prevent confusion.

## Color Scheme Summary

| Element | Caregiver | Housekeeper |
|---------|-----------|-------------|
| Primary Color | Green (#10b981 / `success`) | Purple (#7B1FA2 / `deep-purple`) |
| Hover Color | Dark Green (#059669) | Dark Purple (#6A1B9A) |
| Text Color | `success--text` | `deep-purple--text` |
| Avatar Color | #2E7D32 | #7B1FA2 |
| Badge Color | `success` | `deep-purple` |

## Files Changed

### 1. DashboardTemplate.vue
**Location:** `resources/js/components/DashboardTemplate.vue`

Changed role-based color configurations:
- `themeColor`: Added housekeeper → 'deep-purple'
- `roleAvatarColor`: Changed housekeeper from '#2E7D32' to '#7B1FA2'
- `roleTextClass`: Changed housekeeper from 'text-green' to 'text-purple'
- `roleStatusClass`: Changed housekeeper from 'status-green' to 'status-purple'
- `roleBadgeColor`: Changed housekeeper from 'success' to 'deep-purple'

### 2. HousekeeperDashboard.vue
**Location:** `resources/js/components/HousekeeperDashboard.vue`

Bulk replaced all color references (~200+ instances):
- `color="success"` → `color="deep-purple"`
- `success--text` → `deep-purple--text`
- `color: 'success'` → `color: 'deep-purple'`
- `icon-class="success"` → `icon-class="deep-purple"`
- `text-success` → `text-deep-purple`
- `bg-success` → `bg-deep-purple`
- Hover highlight color: `rgba(76, 175, 80, 0.08)` → `rgba(123, 31, 162, 0.08)`

### 3. AdminDashboard.vue
**Location:** `resources/js/components/AdminDashboard.vue`

**CSS Classes Added:**
```css
.action-btn-housekeepers {
  background-color: #7B1FA2 !important;
  color: white !important;
}

.action-btn-housekeepers:hover {
  background-color: #6A1B9A !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 8px rgba(123, 31, 162, 0.3) !important;
}
```

**Color Changes:**
- Housekeepers section title: `error--text` → `deep-purple--text`
- Add Housekeeper button: `color="error"` → `color="deep-purple"`
- Housekeeper Analytics title: `error--text` → `deep-purple--text`
- Housekeeper metrics colors: Changed from teal/info/warning/error to deep-purple/purple variations
- Assign Housekeepers dialog: Updated booking details icons from error to deep-purple
- Confirm Assignment button: `color="error"` → `color="deep-purple"`
- Booking table action buttons: Housekeeping bookings now use `action-btn-housekeepers` class (purple) instead of `action-btn-caregivers` (green)

### 4. StatCard.vue
**Location:** `resources/js/components/shared/StatCard.vue`

Added new CSS classes for purple icons:
```css
.stat-icon.deep-purple {
  background: linear-gradient(135deg, #7B1FA2, #6A1B9A);
  box-shadow: 0 4px 16px rgba(123, 31, 162, 0.2);
}

.stat-icon.purple {
  background: linear-gradient(135deg, #9C27B0, #7B1FA2);
  box-shadow: 0 4px 16px rgba(156, 39, 176, 0.2);
}
```

## Visual Differentiation

### Admin Dashboard - Bookings Table
- **Caregiver bookings**: Green action buttons (Assign Caregivers, View Assigned Caregivers)
- **Housekeeping bookings**: Purple action buttons (Assign Housekeepers, View Assigned Housekeepers)

### Housekeeper Portal (HousekeeperDashboard)
- All primary accents are now purple instead of green
- Stat cards, buttons, icons, text highlights all use purple theme
- Consistent visual identity distinct from CaregiverDashboard (which remains green)

### Applications Table (Admin)
- Caregiver type chips: Green
- Housekeeper type chips: Purple (#6A1B9A)

## Preserved Elements
- `data.success` (API response checks) - NOT changed
- `success()` function calls - NOT changed
- Status indicators for "Paid", "Completed", etc. remain semantic colors

---
*Completed: January 14, 2026*
