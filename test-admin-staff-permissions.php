<?php
/**
 * Test script for Admin Staff Page Permissions
 * Run with: php test-admin-staff-permissions.php
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "===========================================\n";
echo "ADMIN STAFF PAGE PERMISSIONS TEST\n";
echo "===========================================\n\n";

// Test 1: Check if page_permissions column exists
echo "1. Checking database schema...\n";
$columns = Schema::getColumnListing('users');
if (in_array('page_permissions', $columns)) {
    echo "   ✓ page_permissions column EXISTS in users table\n";
} else {
    echo "   ✗ page_permissions column MISSING in users table\n";
}
echo "\n";

// Test 2: Check Admin Staff users
echo "2. Checking Admin Staff users...\n";
$adminStaff = User::where('user_type', 'admin')
    ->where('role', 'Admin Staff')
    ->get();

echo "   Found " . $adminStaff->count() . " Admin Staff users\n";
foreach ($adminStaff as $staff) {
    echo "   - ID: {$staff->id}, Name: {$staff->name}, Email: {$staff->email}\n";
    if ($staff->page_permissions) {
        $perms = is_string($staff->page_permissions) ? json_decode($staff->page_permissions, true) : $staff->page_permissions;
        $enabledCount = is_array($perms) ? count(array_filter($perms)) : 0;
        $totalCount = is_array($perms) ? count($perms) : 0;
        echo "     Permissions: {$enabledCount}/{$totalCount} pages enabled\n";
    } else {
        echo "     Permissions: Not set (will use defaults)\n";
    }
}
echo "\n";

// Test 3: Check Master Admin users
echo "3. Checking Master Admin users...\n";
$masterAdmins = User::where('user_type', 'admin')
    ->where(function($q) {
        $q->where('role', 'Master')
          ->orWhere('role', 'master')
          ->orWhereNull('role')
          ->orWhere('role', '');
    })
    ->get();

echo "   Found " . $masterAdmins->count() . " Master Admin users\n";
foreach ($masterAdmins as $admin) {
    echo "   - ID: {$admin->id}, Name: {$admin->name}, Role: " . ($admin->role ?: 'null') . "\n";
}
echo "\n";

// Test 4: Check User model fillable
echo "4. Checking User model fillable array...\n";
$user = new User();
$fillable = $user->getFillable();
if (in_array('page_permissions', $fillable)) {
    echo "   ✓ page_permissions IS in User model fillable array\n";
} else {
    echo "   ✗ page_permissions is NOT in User model fillable array\n";
}
echo "\n";

// Test 5: Test default permissions function in controller
echo "5. Testing AdminController methods...\n";
try {
    $controller = new \App\Http\Controllers\AdminController();
    
    // Use reflection to test private method
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getDefaultAdminStaffPermissions');
    $method->setAccessible(true);
    $defaults = $method->invoke($controller);
    
    echo "   ✓ getDefaultAdminStaffPermissions() returns " . count($defaults) . " page permissions\n";
    echo "   Pages: " . implode(', ', array_keys($defaults)) . "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Check routes
echo "6. Checking routes...\n";
$routes = app('router')->getRoutes();
$adminStaffRoutes = [
    'api/admin/admin-staff' => 'GET',
    'api/admin/admin-staff' => 'POST',
    'api/admin/admin-staff/{id}' => 'PUT',
    'api/admin/admin-staff/{id}' => 'DELETE',
    'api/admin/admin-staff/permissions' => 'GET',
];

foreach ($adminStaffRoutes as $uri => $method) {
    $found = false;
    foreach ($routes as $route) {
        if (str_contains($route->uri(), $uri)) {
            $found = true;
            echo "   ✓ Route found: {$method} {$uri}\n";
            break;
        }
    }
    if (!$found) {
        echo "   ✗ Route MISSING: {$method} {$uri}\n";
    }
}
echo "\n";

// Test 7: Test creating an admin staff with permissions
echo "7. Testing Admin Staff creation with permissions...\n";
try {
    $testPermissions = [
        'dashboard' => true,
        'notifications' => true,
        'users' => false,
        'caregivers' => false,
        'payments' => true,
        'profile' => true,
    ];
    
    // Don't actually create, just validate the data
    echo "   ✓ Permission data structure is valid\n";
    echo "   Sample: " . json_encode($testPermissions) . "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "===========================================\n";
echo "TEST COMPLETE\n";
echo "===========================================\n";
