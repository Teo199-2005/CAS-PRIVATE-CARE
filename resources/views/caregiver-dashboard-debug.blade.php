<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Caregiver Dashboard Debug - CAS Private Care LLC</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="debug-info" style="padding: 20px; background: #f0f0f0; margin: 20px;">
        <h2>Debug Information</h2>
        <p>Vue App Mount Target: <span id="mount-target-check">Not Found</span></p>
        <p>Vue Available: <span id="vue-check">Not Available</span></p>
        <p>Vuetify Available: <span id="vuetify-check">Not Available</span></p>
        <p>Chart.js Available: <span id="chart-check">Not Available</span></p>
        <p>Console Errors: Check browser console for errors</p>
    </div>
    
    <div id="caregiver-dashboard-app">
        <div style="padding: 20px; text-align: center;">
            <h1>Loading Caregiver Dashboard...</h1>
            <p>If this message persists, there's a JavaScript loading issue.</p>
        </div>
        <caregiver-dashboard></caregiver-dashboard>
    </div>

    <script>
        // Debug checks
        document.addEventListener('DOMContentLoaded', function() {
            // Check if mount target exists
            const mountTarget = document.getElementById('caregiver-dashboard-app');
            document.getElementById('mount-target-check').textContent = mountTarget ? 'Found' : 'Not Found';
            
            // Check if Vue is available
            document.getElementById('vue-check').textContent = typeof Vue !== 'undefined' ? 'Available' : 'Not Available';
            
            // Check if Vuetify is available
            document.getElementById('vuetify-check').textContent = typeof Vuetify !== 'undefined' ? 'Available' : 'Not Available';
            
            // Check if Chart.js is available
            document.getElementById('chart-check').textContent = typeof Chart !== 'undefined' ? 'Available' : 'Not Available';
            
            console.log('Debug Info:', {
                mountTarget: !!mountTarget,
                vue: typeof Vue !== 'undefined',
                vuetify: typeof Vuetify !== 'undefined',
                chart: typeof Chart !== 'undefined',
                window: Object.keys(window).filter(key => key.includes('vue') || key.includes('Vue'))
            });
        });
    </script>
</body>
</html>