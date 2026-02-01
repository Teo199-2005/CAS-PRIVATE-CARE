<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    <title>Connect Bank Account - CAS Private Care</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }
        #custom-bank-onboarding-app {
            min-height: 100vh;
        }
        /* Mobile modal fixes */
        @media (max-width: 600px) {
            .v-dialog {
                margin: 8px !important;
                max-height: calc(100vh - 16px) !important;
            }
            .v-dialog > .v-card {
                border-radius: 12px !important;
            }
            .v-overlay__content {
                max-width: calc(100vw - 16px) !important;
            }
        }
    </style>
</head>
<body>
    <div id="custom-bank-onboarding-app">
        <custom-bank-onboarding></custom-bank-onboarding>
    </div>
    
    <script>
        // Pass user role to Vue component
        window.userRole = '{{ auth()->check() ? auth()->user()->user_type : "guest" }}';
    </script>
</body>
</html>
