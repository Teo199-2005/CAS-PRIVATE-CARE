<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connect Bank Account - CAS Private Care</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
