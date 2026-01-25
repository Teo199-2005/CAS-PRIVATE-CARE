<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Housekeeper Dashboard - CAS Private Care LLC</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: #7c3aed;
            color: white;
            padding: 8px 16px;
            z-index: 9999;
            text-decoration: none;
            border-radius: 0 0 4px 0;
            transition: top 0.3s;
            font-weight: 500;
        }
        .skip-link:focus {
            top: 0;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <div id="housekeeper-dashboard-app">
        <dashboard-wrapper :is-admin="false">
            <housekeeper-dashboard></housekeeper-dashboard>
        </dashboard-wrapper>
    </div>
</body>
</html>
