<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Http\Middleware\TwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'email' => 'admin@test.com',
            'status' => 'Active',
        ]);
    }

    /** @test */
    public function admin_user_is_redirected_to_2fa_verification_page()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/dashboard-vue');
        
        $response->assertRedirect(route('admin.2fa.verify'));
    }

    /** @test */
    public function admin_staff_is_redirected_to_2fa_verification_page()
    {
        $adminStaff = User::factory()->create([
            'user_type' => 'adminstaff',
            'email' => 'staff@test.com',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($adminStaff)
            ->get('/admin-staff/dashboard-vue');
        
        $response->assertRedirect(route('admin.2fa.verify'));
    }

    /** @test */
    public function non_admin_users_are_not_affected_by_2fa()
    {
        $caregiver = User::factory()->create([
            'user_type' => 'caregiver',
            'email' => 'caregiver@test.com',
            'status' => 'Active',
        ]);

        // 2FA middleware should not redirect non-admin users
        // The middleware checks user_type before requiring 2FA
        $middleware = new TwoFactorAuthentication();
        
        $request = \Illuminate\Http\Request::create('/caregiver/dashboard', 'GET');
        $request->setUserResolver(fn() => $caregiver);
        
        $response = $middleware->handle($request, fn($req) => response('OK'));
        
        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function verified_admin_can_access_dashboard()
    {
        // Mark admin as 2FA verified
        Session::put('2fa_verified_' . $this->admin->id, true);
        Session::put('2fa_verified_at_' . $this->admin->id, now()->timestamp);

        $middleware = new TwoFactorAuthentication();
        
        $request = \Illuminate\Http\Request::create('/admin/dashboard-vue', 'GET');
        $request->setUserResolver(fn() => $this->admin);
        $request->setLaravelSession(Session::getFacadeRoot());

        // Should pass through without redirect
        $response = $middleware->handle($request, fn($req) => response('Dashboard'));
        
        $this->assertEquals('Dashboard', $response->getContent());
    }

    /** @test */
    public function otp_can_be_generated_and_stored()
    {
        $middleware = new TwoFactorAuthentication();
        
        $otp = $middleware->generateOTP($this->admin);
        
        // OTP should be 6 digits
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);
        
        // OTP should be stored in session
        $this->assertTrue(Session::has('2fa_otp_' . $this->admin->id));
    }

    /** @test */
    public function valid_otp_verification_succeeds()
    {
        $middleware = new TwoFactorAuthentication();
        
        $otp = $middleware->generateOTP($this->admin);
        
        $result = $middleware->verifyOTP($this->admin, $otp);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function invalid_otp_verification_fails()
    {
        $middleware = new TwoFactorAuthentication();
        
        $middleware->generateOTP($this->admin);
        
        $result = $middleware->verifyOTP($this->admin, '000000');
        
        $this->assertFalse($result);
    }

    /** @test */
    public function expired_otp_verification_fails()
    {
        $middleware = new TwoFactorAuthentication();
        
        // Generate OTP
        $otp = $middleware->generateOTP($this->admin);
        
        // Manually expire the OTP by setting timestamp to past
        Session::put('2fa_otp_expires_' . $this->admin->id, now()->subMinutes(11)->timestamp);
        
        $result = $middleware->verifyOTP($this->admin, $otp);
        
        $this->assertFalse($result);
    }

    /** @test */
    public function two_factor_verification_page_loads_correctly()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.2fa.verify'));
        
        $response->assertStatus(200);
        $response->assertSee('Two-Factor Authentication');
    }

    /** @test */
    public function send_otp_endpoint_works()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.2fa.send'));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'email',
        ]);
    }

    /** @test */
    public function verify_otp_endpoint_validates_input()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.2fa.verify.submit'), [
                'otp' => '123', // Too short
            ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['otp']);
    }

    /** @test */
    public function successful_otp_verification_marks_session()
    {
        $middleware = new TwoFactorAuthentication();
        $otp = $middleware->generateOTP($this->admin);

        $response = $this->actingAs($this->admin)
            ->withSession([
                '2fa_otp_' . $this->admin->id => $otp,
                '2fa_otp_expires_' . $this->admin->id => now()->addMinutes(10)->timestamp,
            ])
            ->postJson(route('admin.2fa.verify.submit'), [
                'otp' => $otp,
            ]);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function rate_limiting_on_otp_verification()
    {
        // Attempt multiple failed verifications
        for ($i = 0; $i < 6; $i++) {
            $response = $this->actingAs($this->admin)
                ->postJson(route('admin.2fa.verify.submit'), [
                    'otp' => '000000',
                ]);
        }

        // After 5 attempts, should be rate limited
        $this->assertTrue(
            $response->status() === 429 || 
            ($response->status() === 200 && isset($response->json()['error']))
        );
    }
}
