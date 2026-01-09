<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Client Dashboard - CAS Private Care LLC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
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
    @endphp
    <div id="client-dashboard-app">
        <dashboard-wrapper :is-admin="false">
            <client-dashboard :user-data='@json($user)'></client-dashboard>
        </dashboard-wrapper>
    </div>
</body>
</html>
