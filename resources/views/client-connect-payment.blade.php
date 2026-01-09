<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Link Payment Method - CAS Private Care</title>
    
    <!-- Stripe.js -->
    <script src="https://js.stripe.com/v3/"></script>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <div id="connect-payment-app">
        <connect-payment-method></connect-payment-method>
    </div>
</body>
</html>
