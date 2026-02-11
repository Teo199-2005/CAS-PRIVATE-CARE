<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    <title>Admin Staff Dashboard - CAS Private Care LLC</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" integrity="sha384-..." crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app-complex.js'])
</head>
<body>
    @include('partials.accessibility')
    <div id="admin-staff-dashboard-app"></div>
</body>
</html>
