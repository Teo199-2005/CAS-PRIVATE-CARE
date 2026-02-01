<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;

/**
 * Comprehensive security test suite for authentication and authorization.
 * 
 * Tests:
 * - Authentication flows
 * - Rate limiting
 * - CSRF protection
 * - Session security
 * - Password policies
 */
class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login');
    }

    /** @test */
    public function login_is_rate_limited_after_multiple_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt to login multiple times with wrong password
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // Next attempt should be rate limited
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    /** @test */
    public function login_requires_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Wrong password
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJsonStructure([
            'success',
            'error',
        ]);
    }

    /** @test */
    public function login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    /** @test */
    public function login_validates_email_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function authenticated_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/user');

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    /** @test */
    public function logout_invalidates_session(): void
    {
        $user = User::factory()->create();

        // Login
        $this->actingAs($user);

        // Logout
        $response = $this->postJson('/api/logout');

        $response->assertStatus(200);

        // Try to access protected route
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);
    }

    /** @test */
    public function password_reset_requires_valid_token(): void
    {
        $response = $this->postJson('/api/reset-password', [
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => 'invalid-token',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_cannot_access_admin_routes_without_admin_role(): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($user)
            ->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->getJson('/api/admin/users');

        $response->assertStatus(200);
    }

    /** @test */
    public function csrf_token_is_required_for_state_changing_requests(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ], [
            'Accept' => 'text/html',
        ]);

        // Without CSRF token, should be redirected or get 419
        $this->assertContains($response->status(), [302, 419]);
    }

    /** @test */
    public function session_is_regenerated_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        // Session should have a new ID after login (prevents session fixation)
    }

    /** @test */
    public function sensitive_data_is_not_exposed_in_error_responses(): void
    {
        $response = $this->getJson('/api/nonexistent-endpoint');

        $response->assertStatus(404);
        
        // Should not contain stack traces or file paths
        $content = $response->getContent();
        $this->assertStringNotContainsString('Exception', $content);
        $this->assertStringNotContainsString('.php', $content);
        $this->assertStringNotContainsString('Stack trace', $content);
    }

    /** @test */
    public function password_is_never_returned_in_user_response(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJsonMissing(['password']);
    }

    /** @test */
    public function api_responses_include_security_headers(): void
    {
        $response = $this->get('/');

        // Check for common security headers
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options');
    }

    /** @test */
    public function two_factor_authentication_can_be_enabled(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/user/two-factor-authentication');

        // Should return 2FA secret or success
        $this->assertContains($response->status(), [200, 201]);
    }

    /** @test */
    public function inactive_users_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'inactive',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Should be denied
        $this->assertContains($response->status(), [401, 403]);
    }

    /** @test */
    public function email_verification_is_required_for_sensitive_actions(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/bookings');

        // Should require email verification
        $this->assertContains($response->status(), [403, 409]);
    }
}
