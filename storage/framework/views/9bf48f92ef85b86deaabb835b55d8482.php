<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" type="image/png" href="<?php echo e(asset('logo flower.png')); ?>">
    <title>Complete Payment - CAS Private Care LLC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Stripe.js - Must be loaded from Stripe's CDN -->
    <script src="https://js.stripe.com/v3/"></script>
    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
            :booking-data='<?php echo json_encode($booking, 15, 512) ?>'
            booking-id="<?php echo e($bookingId); ?>"
            stripe-key="<?php echo e($stripeKey ?? config('stripe.key')); ?>"
        ></payment-page>
    </div>
</body>
</html>
<?php /**PATH C:\Users\Cocotantan\Downloads\--CAS WEBSITE-- - Copy (4)\resources\views/payment.blade.php ENDPATH**/ ?>