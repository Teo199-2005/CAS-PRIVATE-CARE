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
    <div id="link-payment-app">
        <link-payment-method></link-payment-method>
    </div>
    
    <script>
        window.userRole = '{{ auth()->check() ? auth()->user()->user_type : "guest" }}';
        window.userName = '{{ auth()->check() ? auth()->user()->name : "Guest" }}';
        window.userEmail = '{{ auth()->check() ? auth()->user()->email : "" }}';
    </script>
</body>
</html>
