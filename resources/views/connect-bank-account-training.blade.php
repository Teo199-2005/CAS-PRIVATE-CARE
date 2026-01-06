<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connect Bank Account - Training Center - CAS Private Care</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        window.userRole = 'training';
    </script>
</head>
<body>
    <div id="training-bank-onboarding-app">
        <custom-bank-onboarding></custom-bank-onboarding>
    </div>
</body>
</html>
