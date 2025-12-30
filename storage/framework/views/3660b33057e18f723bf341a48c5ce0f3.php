<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Client Dashboard - CAS Private Care LLC</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body>
    <?php
        // Get user from auth or query parameter for demo purposes
        $userId = request()->query('user_id');
        $user = auth()->user();
        
        if (!$user && $userId) {
            $user = \App\Models\User::find($userId);
        }
        
        // Fallback to Demo Client if no user specified
        if (!$user) {
            $user = \App\Models\User::where('email', 'client@demo.com')->first();
        }
    ?>
    <div id="client-dashboard-app">
        <client-dashboard :user-data='<?php echo json_encode($user, 15, 512) ?>'></client-dashboard>
    </div>
</body>
</html>
<?php /**PATH C:\Users\Cocotantan\Downloads\--CAS WEBSITE-- - Copy (4)\resources\views/client-dashboard-vue.blade.php ENDPATH**/ ?>