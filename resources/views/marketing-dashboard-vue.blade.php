<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Marketing Dashboard - CAS Private Care LLC</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="marketing-dashboard-app">
        <dashboard-wrapper :is-admin="false">
            <marketing-dashboard></marketing-dashboard>
        </dashboard-wrapper>
    </div>
</body>
</html>