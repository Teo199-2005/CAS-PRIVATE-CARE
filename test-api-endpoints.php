<?php
/**
 * API Test script for Admin Staff Page Permissions
 * Run with: php test-api-endpoints.php
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use App\Models\User;

echo "===========================================\n";
echo "ADMIN STAFF API ENDPOINT TESTS\n";
echo "===========================================\n\n";

// Find an admin user to authenticate as
$adminUser = User::where('user_type', 'admin')->first();
if (!$adminUser) {
    echo "✗ No admin user found. Creating test admin...\n";
    $adminUser = User::create([
        'name' => 'Test Admin',
        'email' => 'testadmin@test.com',
        'password' => bcrypt('password123'),
        'user_type' => 'admin',
        'role' => 'Master',
        'status' => 'Active',
    ]);
}

// Authenticate
auth()->login($adminUser);
echo "✓ Authenticated as: {$adminUser->name} ({$adminUser->email})\n\n";

$controller = new AdminController();

// Test 1: Get Admin Staff List
echo "1. Testing getAdminStaff()...\n";
try {
    $response = $controller->getAdminStaff();
    $data = json_decode($response->getContent(), true);
    
    if (isset($data['staff'])) {
        echo "   ✓ API returned " . count($data['staff']) . " admin staff members\n";
        
        // Check if page_permissions is included
        if (count($data['staff']) > 0) {
            $firstStaff = $data['staff'][0];
            if (array_key_exists('page_permissions', $firstStaff)) {
                echo "   ✓ page_permissions field is included in response\n";
                $perms = $firstStaff['page_permissions'];
                if (is_array($perms)) {
                    echo "   ✓ page_permissions is an array with " . count($perms) . " entries\n";
                }
            } else {
                echo "   ✗ page_permissions field is MISSING from response\n";
            }
        }
    } else {
        echo "   ✗ Unexpected response format\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Test storeAdminStaff with permissions
echo "2. Testing storeAdminStaff() with page_permissions...\n";
try {
    $request = new Request([
        'name' => 'Test Permission Staff',
        'email' => 'testpermstaff_' . time() . '@test.com',
        'password' => 'password123',
        'status' => 'Active',
        'page_permissions' => [
            'dashboard' => true,
            'notifications' => true,
            'users' => false,
            'caregivers' => false,
            'housekeepers' => false,
            'clients' => false,
            'admin-staff' => false,
            'marketing-staff' => false,
            'training-centers' => false,
            'pending' => true,
            'password-resets' => true,
            'client-bookings' => true,
            'time-tracking' => true,
            'reviews' => true,
            'announcements' => true,
            'payments' => false,
            'analytics' => false,
            'profile' => true,
        ]
    ]);
    
    $response = $controller->storeAdminStaff($request);
    $data = json_decode($response->getContent(), true);
    
    if (isset($data['success']) && $data['success']) {
        echo "   ✓ Admin staff created successfully\n";
        $newStaffId = $data['staff']['id'] ?? null;
        
        // Verify the permissions were saved
        if ($newStaffId) {
            $createdUser = User::find($newStaffId);
            if ($createdUser && $createdUser->page_permissions) {
                $perms = is_string($createdUser->page_permissions) 
                    ? json_decode($createdUser->page_permissions, true) 
                    : $createdUser->page_permissions;
                
                if (is_array($perms)) {
                    $enabledCount = count(array_filter($perms));
                    echo "   ✓ Permissions saved: {$enabledCount} pages enabled\n";
                    
                    // Verify specific permissions
                    if (isset($perms['payments']) && $perms['payments'] === false) {
                        echo "   ✓ Payments permission correctly set to FALSE\n";
                    }
                    if (isset($perms['dashboard']) && $perms['dashboard'] === true) {
                        echo "   ✓ Dashboard permission correctly set to TRUE\n";
                    }
                }
            }
            
            // Clean up - delete test user
            $createdUser->delete();
            echo "   ✓ Test user cleaned up\n";
        }
    } else {
        echo "   ✗ Failed to create admin staff\n";
        print_r($data);
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Test getAdminStaffPermissions for Admin Staff user
echo "3. Testing getAdminStaffPermissions()...\n";
try {
    // Find or create an admin staff user
    $adminStaff = User::where('user_type', 'admin')
        ->where('role', 'Admin Staff')
        ->first();
    
    if ($adminStaff) {
        // Login as admin staff
        auth()->login($adminStaff);
        
        $response = $controller->getAdminStaffPermissions();
        $data = json_decode($response->getContent(), true);
        
        if (isset($data['permissions'])) {
            echo "   ✓ Permissions endpoint returned successfully\n";
            echo "   ✓ Found " . count($data['permissions']) . " permission entries\n";
        } else if (isset($data['error'])) {
            echo "   Result: " . $data['error'] . "\n";
        }
        
        // Re-login as master admin
        auth()->login($adminUser);
    } else {
        echo "   ⚠ No Admin Staff user found to test with\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Test updateAdminStaff with permissions
echo "4. Testing updateAdminStaff() with page_permissions...\n";
try {
    $adminStaff = User::where('user_type', 'admin')
        ->where('role', 'Admin Staff')
        ->first();
    
    if ($adminStaff) {
        $request = new Request([
            'page_permissions' => [
                'dashboard' => true,
                'payments' => true,
                'analytics' => true,
                'profile' => true,
            ]
        ]);
        
        $response = $controller->updateAdminStaff($request, $adminStaff->id);
        $data = json_decode($response->getContent(), true);
        
        if (isset($data['success']) && $data['success']) {
            echo "   ✓ Admin staff updated successfully\n";
            
            // Verify the update
            $updatedUser = User::find($adminStaff->id);
            if ($updatedUser && $updatedUser->page_permissions) {
                $perms = is_string($updatedUser->page_permissions) 
                    ? json_decode($updatedUser->page_permissions, true) 
                    : $updatedUser->page_permissions;
                
                if (isset($perms['payments']) && $perms['payments'] === true) {
                    echo "   ✓ Payments permission updated to TRUE\n";
                }
            }
        } else {
            echo "   ✗ Failed to update admin staff\n";
        }
    } else {
        echo "   ⚠ No Admin Staff user found to test with\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "===========================================\n";
echo "API TESTS COMPLETE\n";
echo "===========================================\n";
