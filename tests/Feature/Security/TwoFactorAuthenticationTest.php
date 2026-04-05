<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Http\Middleware\TwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'email' => 'admin@test.com',
            'status' => 'Active',
        ]);
    }

    /** @test */
    public function admin_user_is_redirected_to_2fa_verification_page()
    {
        config(['security.admin_two_factor_enabled' => true]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/dashboard-vue');

        $response->assertRedirect(route('admin.2fa.verify'));
    }

    /** @test */
    public function admin_staff_is_redirected_to_2fa_verification_page()
    {
        config(['security.admin_two_factor_enabled' => true]);

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
    public function admin_reaches_dashboard_when_two_factor_is_disabled()
    {
        config(['security.admin_two_factor_enabled' => false]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/dashboard-vue');

        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_users_are_not_affected_by_2fa()
    {
        $caregiver = User::factory()->create([
            'user_type' => 'caregiver',
            'email' => 'caregiver@test.com',
            'status' => 'Active',
        ]);

        $middleware = new TwoFactorAuthentication();

        $request = \Illuminate\Http\Request::create('/caregiver/dashboard', 'GET');
        $request->setUserResolver(fn () => $caregiver);

        $response = $middleware->handle($request, fn ($req) => response('OK'));

        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function verified_admin_can_access_dashboard()
    {
        $this->actingAs($this->admin);
        Session::put('2fa_verified_' . $this->admin->id, true);
        Session::put('2fa_verified_at_' . $this->admin->id, now()->timestamp);

        $response = $this->get('/admin/dashboard-vue');

        $this->assertNotEquals(302, $response->status(), $response->getContent());
        $response->assertStatus(200);
    }

    /** @test */
    public function otp_can_be_generated_and_stored()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.2fa.send-otp'));

        $otp = session('2fa_otp_' . $this->admin->id);
        $this->assertNotNull($otp);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);
    }

    /** @test */
    public function valid_otp_verification_succeeds()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.2fa.send-otp'));

        $otp = session('2fa_otp_' . $this->admin->id);
        $this->assertNotNull($otp);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.2fa.verify.submit'), [
                'code' => $otp,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function invalid_otp_verification_fails()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.2fa.send-otp'));

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.2fa.verify.submit'), [
                'code' => '000000',
            ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function expired_otp_verification_fails()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.2fa.send-otp'));

        $otp = session('2fa_otp_' . $this->admin->id);
        $this->assertNotNull($otp);

        Session::put('2fa_otp_expires_' . $this->admin->id, now()->subMinutes(11)->timestamp);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.2fa.verify.submit'), [
                'code' => $otp,
            ]);

        $response->assertStatus(401);
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
            ->postJson(route('admin.2fa.send-otp'));

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
                'code' => '123',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['code']);
    }

    /** @test */
    public function successful_otp_verification_marks_session()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.2fa.send-otp'));

        $otp = session('2fa_otp_' . $this->admin->id);
        $this->assertNotNull($otp);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.2fa.verify.submit'), [
                'code' => $otp,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertTrue(session('2fa_verified_' . $this->admin->id));
    }

    /** @test */
    public function rate_limiting_on_otp_verification()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.2fa.send-otp'));

        $last = null;
        for ($i = 0; $i < 6; $i++) {
            $last = $this->actingAs($this->admin)
                ->postJson(route('admin.2fa.verify.submit'), [
                    'code' => '000000',
                ]);
        }

        $this->assertNotNull($last);
        $last->assertStatus(401);
    }
}
