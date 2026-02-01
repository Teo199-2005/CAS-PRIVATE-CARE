<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    <title>Admin Staff Dashboard - CAS Private Care</title>
    @vite(['resources/css/app.css', 'resources/js/app-complex.js'])
</head>
<body>
    <div id="admin-staff-dashboard-app">
        <admin-staff-dashboard></admin-staff-dashboard>
    </div>
</body>
</html>
