<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    <title>Caregiver Dashboard - CAS Private Care LLC</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.accessibility')
    <div id="caregiver-dashboard-app">
        <dashboard-wrapper :is-admin="false">
            <caregiver-dashboard></caregiver-dashboard>
        </dashboard-wrapper>
    </div>
</body>
</html>
