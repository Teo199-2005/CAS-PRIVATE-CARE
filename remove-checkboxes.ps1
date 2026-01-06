$filePath = "resources\js\components\AdminStaffDashboard.vue"
$content = Get-Content $filePath -Raw

# Remove show-select and v-model for selected items from all data tables
$content = $content -replace 'v-model="selectedCaregivers"\s+:headers="caregiverHeaders"\s+:items="filteredCaregivers"\s+:items-per-page="10"\s+show-select\s+item-value="userId"', ':headers="caregiverHeaders" :items="filteredCaregivers" :items-per-page="10"'
$content = $content -replace 'v-model="selectedClients"\s+:headers="clientHeaders"\s+:items="filteredClients"\s+:items-per-page="10"\s+show-select\s+item-value="id"', ':headers="clientHeaders" :items="filteredClients" :items-per-page="10"'
$content = $content -replace 'v-model="selectedMarketingStaff"\s+:headers="marketingStaffHeaders"\s+:items="filteredMarketingStaff"\s+:items-per-page="10"\s+show-select\s+item-value="id"', ':headers="marketingStaffHeaders" :items="filteredMarketingStaff" :items-per-page="10"'
$content = $content -replace 'v-model="selectedAdminStaff"\s+:headers="adminStaffHeaders"\s+:items="filteredAdminStaff"\s+:items-per-page="10"\s+show-select\s+item-value="id"', ':headers="adminStaffHeaders" :items="filteredAdminStaff" :items-per-page="10"'
$content = $content -replace 'v-model="selectedTrainingCenters"\s+:headers="trainingCenterHeaders"\s+:items="filteredTrainingCenters"\s+:items-per-page="10"\s+show-select\s+item-value="id"', ':headers="trainingCenterHeaders" :items="filteredTrainingCenters" :items-per-page="10"'
$content = $content -replace 'v-model="selectedBookings"\s+:headers="bookingHeaders"\s+:items="filteredBookings"\s+:items-per-page="10"\s+:items-per-page-options="\[10,\s*25,\s*50,\s*-1\]"\s+show-select\s+item-value="id"', ':headers="bookingHeaders" :items="filteredBookings" :items-per-page="10" :items-per-page-options="[10, 25, 50, -1]"'

# Also remove standalone show-select
$content = $content -replace '\s+show-select\s+', ' '

# Remove delete buttons
$content = $content -replace '<v-btn\s+v-if="selected\w+\.length\s*>\s*0"[^>]*>\s*Delete\s+Selected[^<]*</v-btn>\s*', ''

$content | Set-Content $filePath -NoNewline

Write-Host "✅ Removed all show-select attributes and delete buttons from AdminStaffDashboard.vue"
