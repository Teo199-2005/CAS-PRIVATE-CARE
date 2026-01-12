<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'client',
            'status' => 'Active'
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/client/dashboard-vue');
    }

    /** @test */
    public function user_cannot_login_with_incorrect_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'client'
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function user_cannot_login_with_nonexistent_email()
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function admin_redirects_to_admin_dashboard()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'admin',
            'status' => 'Active'
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/admin/dashboard-vue');
    }

    /** @test */
    public function caregiver_redirects_to_caregiver_dashboard()
    {
        $caregiver = User::factory()->create([
            'email' => 'caregiver@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'caregiver',
            'status' => 'approved'
        ]);

        $response = $this->post('/login', [
            'email' => 'caregiver@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/caregiver/dashboard-vue');
    }

    /** @test */
    public function rejected_caregiver_cannot_login()
    {
        $caregiver = User::factory()->create([
            'email' => 'rejected@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'caregiver',
            'status' => 'rejected'
        ]);

        $response = $this->post('/login', [
            'email' => 'rejected@example.com',
            'password' => 'password123'
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function login_validates_required_fields()
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create([
            'user_type' => 'client',
            'status' => 'Active'
        ]);

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
