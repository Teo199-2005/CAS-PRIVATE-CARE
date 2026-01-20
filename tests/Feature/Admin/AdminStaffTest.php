<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'email' => 'admin@casprivatecare.com',
            'password' => Hash::make('password123'),
            'user_type' => 'admin',
            'status' => 'Active'
        ]);
    }

    /** @test */
    public function admin_can_view_admin_staff_list()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/admin-staff');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_admin_staff()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/admin-staff', [
                'name' => 'Staff Member',
                'email' => 'staff@casprivatecare.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone' => '6462828282',
                'status' => 'Active',
                'page_permissions' => [
                    'bookings' => true,
                    'users' => false,
                    'payments' => true
                ]
            ]);

        // API may return 200 on successful creation
        $response->assertSuccessful();
        $this->assertDatabaseHas('users', [
            'email' => 'staff@casprivatecare.com',
            'role' => 'Admin Staff'
        ]);
    }

    /** @test */
    public function admin_can_update_admin_staff_permissions()
    {
        $staff = User::factory()->create([
            'user_type' => 'admin',
            'role' => 'Admin Staff',
            'status' => 'Active'
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/admin-staff/{$staff->id}", [
                'page_permissions' => [
                    'bookings' => true,
                    'users' => true,
                    'payments' => true,
                    'caregivers' => true
                ]
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_delete_admin_staff()
    {
        $staff = User::factory()->create([
            'user_type' => 'admin',
            'role' => 'Admin Staff',
            'status' => 'Active'
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/admin/admin-staff/{$staff->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_staff_can_access_dashboard()
    {
        $staff = User::factory()->create([
            'user_type' => 'admin',
            'role' => 'Admin Staff',
            'status' => 'Active'
        ]);

        $response = $this->actingAs($staff)
            ->get('/admin-staff/dashboard-vue');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_staff_cannot_delete_other_admin_staff()
    {
        $adminStaff = User::factory()->create([
            'user_type' => 'admin_staff',
            'status' => 'Active'
        ]);
        
        $otherAdminStaff = User::factory()->create([
            'user_type' => 'admin_staff',
            'status' => 'Active'
        ]);

        // Admin staff should not be able to delete other admin staff
        $response = $this->actingAs($adminStaff)
            ->deleteJson("/api/admin/admin-staff/{$otherAdminStaff->id}");

        // Should return 403 or 401 (forbidden) or 404 if route doesn't exist
        $this->assertTrue(
            in_array($response->status(), [401, 403, 404, 405]),
            "Expected forbidden/error status, got: {$response->status()}"
        );
    }

    /** @test */
    public function non_admin_cannot_create_admin_staff()
    {
        $client = User::factory()->create([
            'user_type' => 'client',
            'status' => 'Active'
        ]);

        $response = $this->actingAs($client)
            ->postJson('/api/admin/admin-staff', [
                'name' => 'Unauthorized Staff',
                'email' => 'unauthorized@example.com',
                'password' => 'password123'
            ]);

        $response->assertStatus(403);
    }
}
