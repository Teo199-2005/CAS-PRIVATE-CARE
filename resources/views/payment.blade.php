<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    <title>Complete Payment - CAS Private Care LLC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Stripe.js - Must be loaded from Stripe's CDN -->
    <script src="https://js.stripe.com/v3/"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            background: #f5f3ef;
        }
        
        #payment-page-app {
            position: relative;
            z-index: 2;
            width: 100%;
            padding: 0;
        }
    </style>
</head>
<body>
    
    <div id="payment-page-app">
        <payment-page 
            :booking-data='@json($booking)'
            booking-id="{{ $bookingId }}"
            stripe-key="{{ $stripeKey ?? config('stripe.key') }}"
        ></payment-page>
    </div>
</body>
</html>
