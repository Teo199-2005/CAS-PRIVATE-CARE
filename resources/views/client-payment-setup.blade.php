<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Link Payment Method - CAS Private Care</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <div id="client-payment-setup-app">
        <client-payment-setup></client-payment-setup>
    </div>
    
    <script>
        // Pass user data to Vue component
        window.userData = {
            role: '{{ auth()->check() ? auth()->user()->user_type : "guest" }}',
            userId: {{ auth()->check() ? auth()->user()->id : 'null' }},
            userName: '{{ auth()->check() ? auth()->user()->name : "" }}',
            userEmail: '{{ auth()->check() ? auth()->user()->email : "" }}'
        };
        window.STRIPE_PUBLISHABLE_KEY = '{{ env('VITE_STRIPE_KEY') }}';
    </script>
</body>
</html>
