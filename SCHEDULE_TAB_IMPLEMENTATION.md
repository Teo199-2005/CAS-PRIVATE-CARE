# Schedule Tab Implementation Plan

## Overview
Replace individual "Schedule" buttons with a unified Schedule tab in the Assigned Caregivers dialog.

## Current Structure
- Assigned Caregivers Dialog has a list of caregivers
- Each caregiver has a "Schedule" button
- Clicking opens a separate modal for that caregiver

## New Structure
- Assigned Caregivers Dialog with 2 tabs:
  1. **Caregivers Tab** - List of assigned caregivers
  2. **Schedule Tab** - Weekly calendar view

## Schedule Tab Features

### 1. Weekly Calendar View
```
Monday    Tuesday   Wednesday Thursday  Friday   Saturday  Sunday
[TODAY]   
-------------------------------------------------------------
Robert    Robert    Robert    teofilo  teofilo   http     http
Chen      Chen      Chen      teofilo  teofilo   http     http
08:00-    08:00-    08:00-    09:00-   09:00-    10:00-   10:00-
17:00     17:00     17:00     18:00    18:00     19:00    19:00
```

### 2. Today Indicator
- Highlight current day with a badge "TODAY"
- Different background color for current day column
- Show if caregiver is currently active

### 3. Day Assignment Interface
For each day:
- Dropdown to select caregiver
- Time range pickers (start/end)
- Quick copy from previous day button
- Unassign button

### 4. Visual Features
- Color-coded by caregiver (each caregiver gets a color)
- Avatar/initials display
- Hover shows full details
- Click to edit that day

## Implementation Steps

### Step 1: Add Tabs to Dialog
```vue
<v-tabs v-model="assignedCaregiversTab" color="success">
  <v-tab value="caregivers">
    <v-icon class="mr-2">mdi-account-group</v-icon>
    Caregivers
  </v-tab>
  <v-tab value="schedule">
    <v-icon class="mr-2">mdi-calendar-clock</v-icon>
    Schedule
  </v-tab>
</v-tabs>

<v-tabs-window v-model="assignedCaregiversTab">
  <v-tabs-window-item value="caregivers">
    <!-- Current caregiver list -->
  </v-tabs-window-item>
  
  <v-tabs-window-item value="schedule">
    <!-- New schedule calendar -->
  </v-tabs-window-item>
</v-tabs-window>
```

### Step 2: Create Schedule Calendar Component
```vue
<div class="schedule-calendar">
  <div class="calendar-header">
    <div v-for="day in daysOfWeek" 
         :key="day.value"
         :class="['day-column', { 'today': isToday(day.value) }]">
      <div class="day-name">{{ day.label }}</div>
      <div v-if="isToday(day.value)" class="today-badge">TODAY</div>
      <div class="day-date">{{ getDate(day.value) }}</div>
    </div>
  </div>
  
  <div class="calendar-body">
    <div v-for="day in daysOfWeek" 
         :key="day.value"
         class="day-slot">
      <!-- Assignment UI for each day -->
      <v-select
        v-model="dayAssignments[day.value].caregiverId"
        :items="availableCaregivers"
        item-title="name"
        item-value="id"
        label="Assign Caregiver"
        density="compact"
      />
      
      <v-text-field
        v-model="dayAssignments[day.value].startTime"
        type="time"
        label="Start"
        density="compact"
      />
      
      <v-text-field
        v-model="dayAssignments[day.value].endTime"
        type="time"
        label="End"
        density="compact"
      />
    </div>
  </div>
</div>
```

### Step 3: Add Helper Functions
```javascript
const isToday = (dayValue) => {
  const today = new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
  return dayValue === today;
};

const getDate = (dayValue) => {
  // Calculate date for each day of the week
  const bookingStartDate = new Date(viewingBookingCaregivers.value.service_date);
  const dayIndex = daysOfWeek.findIndex(d => d.value === dayValue);
  const targetDate = new Date(bookingStartDate);
  targetDate.setDate(targetDate.getDate() + dayIndex);
  return targetDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const dayAssignments = ref({
  monday: { caregiverId: null, startTime: '08:00', endTime: '17:00' },
  tuesday: { caregiverId: null, startTime: '08:00', endTime: '17:00' },
  // ... for all days
});
```

### Step 4: Load/Save Schedule Data
```javascript
const loadWeeklySchedule = async (bookingId) => {
  // Load schedules for all caregivers
  // Organize by day of week
};

const saveWeeklySchedule = async () => {
  // Save all day assignments at once
  for (const [day, assignment] of Object.entries(dayAssignments.value)) {
    if (assignment.caregiverId) {
      await saveSchedule(assignment.caregiverId, [day], {
        [day]: {
          start_time: assignment.startTime,
          end_time: assignment.endTime
        }
      });
    }
  }
};
```

## Benefits
1. ✅ Single view of entire week
2. ✅ See all caregiver assignments at once
3. ✅ Easy to spot gaps or conflicts
4. ✅ Today indicator shows active day
5. ✅ Faster scheduling workflow
6. ✅ Better UX - no multiple modals

## Next Steps
1. Implement tabs in dialog
2. Create calendar grid layout
3. Add today indicator logic
4. Implement assignment dropdowns
5. Add save/load functionality
6. Style with color coding
7. Test with real data
