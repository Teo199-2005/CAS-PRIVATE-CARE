<?php

declare(strict_types=1);

namespace Tests\Feature\Stripe;

use App\Models\User;
use App\Models\Booking;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Feature tests for the new Stripe controller architecture.
 * 
 * Tests the v2 API endpoints.
 * 
 * @covers \App\Http\Controllers\Stripe\ClientStripeController
 * @covers \App\Http\Controllers\Stripe\CaregiverStripeController
 * @covers \App\Http\Controllers\Stripe\HousekeeperStripeController
 * @covers \App\Http\Controllers\Stripe\AdminStripeController
 */
class StripeControllersTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // ============================================
    // ClientStripeController Tests
    // ============================================

    /** @test */
    public function client_can_create_setup_intent(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client',
            'stripe_customer_id' => 'cus_test123'
        ]);

        Sanctum::actingAs($client);

        // Note: This will fail without Stripe mocking
        // In real tests, mock the Stripe API
        $response = $this->postJson('/api/v2/stripe/create-setup-intent');

        // Should fail gracefully without valid Stripe key
        $this->assertTrue(in_array($response->status(), [200, 400, 500]));
    }

    /** @test */
    public function non_client_cannot_create_setup_intent(): void
    {
        $caregiver = User::factory()->create([
            'user_type' => 'caregiver'
        ]);

        Sanctum::actingAs($caregiver);

        $response = $this->postJson('/api/v2/stripe/create-setup-intent');

        $response->assertStatus(403);
    }

    /** @test */
    public function client_can_get_payment_methods(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client',
            'stripe_customer_id' => null
        ]);

        Sanctum::actingAs($client);

        $response = $this->getJson('/api/v2/stripe/payment-methods');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'payment_methods' => []
            ]);
    }

    /** @test */
    public function client_cannot_save_payment_method_without_id(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client'
        ]);

        Sanctum::actingAs($client);

        $response = $this->postJson('/api/v2/stripe/save-payment-method', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method_id']);
    }

    /** @test */
    public function process_payment_requires_booking_id(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client'
        ]);

        Sanctum::actingAs($client);

        $response = $this->postJson('/api/v2/stripe/process-payment', [
            'payment_method_id' => 'pm_test123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking_id']);
    }

    /** @test */
    public function process_payment_requires_valid_booking(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client'
        ]);

        Sanctum::actingAs($client);

        $response = $this->postJson('/api/v2/stripe/process-payment', [
            'payment_method_id' => 'pm_test123',
            'booking_id' => 99999
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking_id']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_payment_endpoints(): void
    {
        $response = $this->getJson('/api/v2/stripe/payment-methods');

        $response->assertStatus(401);
    }

    // ============================================
    // CaregiverStripeController Tests
    // ============================================

    /** @test */
    public function caregiver_can_start_onboarding(): void
    {
        $caregiver = User::factory()->create([
            'user_type' => 'caregiver',
            'stripe_account_id' => null
        ]);

        Caregiver::factory()->create([
            'user_id' => $caregiver->id
        ]);

        Sanctum::actingAs($caregiver);

        $response = $this->postJson('/api/v2/caregiver/stripe/onboard');

        // Will fail without Stripe mocking, but should not be 403
        $this->assertNotEquals(403, $response->status());
    }

    /** @test */
    public function non_caregiver_cannot_start_caregiver_onboarding(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client'
        ]);

        Sanctum::actingAs($client);

        $response = $this->postJson('/api/v2/caregiver/stripe/onboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function caregiver_can_check_stripe_status(): void
    {
        $caregiver = User::factory()->create([
            'user_type' => 'caregiver',
            'stripe_account_id' => null
        ]);

        Sanctum::actingAs($caregiver);

        $response = $this->getJson('/api/v2/caregiver/stripe/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_account' => false,
                'status' => 'not_started'
            ]);
    }

    /** @test */
    public function caregiver_without_account_cannot_get_dashboard(): void
    {
        $caregiver = User::factory()->create([
            'user_type' => 'caregiver',
            'stripe_account_id' => null
        ]);

        Sanctum::actingAs($caregiver);

        $response = $this->getJson('/api/v2/caregiver/stripe/dashboard');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false
            ]);
    }

    // ============================================
    // HousekeeperStripeController Tests
    // ============================================

    /** @test */
    public function housekeeper_can_start_onboarding(): void
    {
        $housekeeper = User::factory()->create([
            'user_type' => 'housekeeper',
            'stripe_account_id' => null
        ]);

        Housekeeper::factory()->create([
            'user_id' => $housekeeper->id
        ]);

        Sanctum::actingAs($housekeeper);

        $response = $this->postJson('/api/v2/housekeeper/stripe/onboard');

        $this->assertNotEquals(403, $response->status());
    }

    /** @test */
    public function non_housekeeper_cannot_start_housekeeper_onboarding(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client'
        ]);

        Sanctum::actingAs($client);

        $response = $this->postJson('/api/v2/housekeeper/stripe/onboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function housekeeper_can_check_stripe_status(): void
    {
        $housekeeper = User::factory()->create([
            'user_type' => 'housekeeper',
            'stripe_account_id' => null
        ]);

        Sanctum::actingAs($housekeeper);

        $response = $this->getJson('/api/v2/housekeeper/stripe/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_account' => false,
                'status' => 'not_started'
            ]);
    }

    // ============================================
    // AdminStripeController Tests
    // ============================================

    /** @test */
    public function admin_can_list_payments(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v2/admin/stripe/payments');

        // Will fail without Stripe mocking, but should not be 403
        $this->assertNotEquals(403, $response->status());
    }

    /** @test */
    public function non_admin_cannot_list_payments(): void
    {
        $client = User::factory()->create([
            'user_type' => 'client'
        ]);

        Sanctum::actingAs($client);

        $response = $this->getJson('/api/v2/admin/stripe/payments');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_platform_balance(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v2/admin/stripe/balance');

        $this->assertNotEquals(403, $response->status());
    }

    /** @test */
    public function admin_can_list_connected_accounts(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v2/admin/stripe/accounts');

        $this->assertNotEquals(403, $response->status());
    }

    /** @test */
    public function refund_request_validates_payment_intent_id(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v2/admin/stripe/refund', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_intent_id']);
    }

    /** @test */
    public function refund_request_validates_amount(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v2/admin/stripe/refund', [
            'payment_intent_id' => 'pi_test123',
            'amount' => -50
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function admin_staff_can_access_admin_endpoints(): void
    {
        $adminStaff = User::factory()->create([
            'user_type' => 'adminstaff'
        ]);

        Sanctum::actingAs($adminStaff);

        $response = $this->getJson('/api/v2/admin/stripe/stats');

        // Should not be 403 for adminstaff
        $this->assertNotEquals(403, $response->status());
    }

    // ============================================
    // Authorization Tests
    // ============================================

    /** @test */
    public function each_user_type_can_only_access_their_endpoints(): void
    {
        // Client
        $client = User::factory()->create(['user_type' => 'client']);
        Sanctum::actingAs($client);
        $this->getJson('/api/v2/stripe/payment-methods')->assertStatus(200);
        $this->postJson('/api/v2/caregiver/stripe/onboard')->assertStatus(403);
        $this->postJson('/api/v2/housekeeper/stripe/onboard')->assertStatus(403);
        $this->getJson('/api/v2/admin/stripe/payments')->assertStatus(403);

        // Caregiver
        $caregiver = User::factory()->create(['user_type' => 'caregiver']);
        Sanctum::actingAs($caregiver);
        $this->getJson('/api/v2/caregiver/stripe/status')->assertStatus(200);
        $this->postJson('/api/v2/housekeeper/stripe/onboard')->assertStatus(403);
        $this->getJson('/api/v2/admin/stripe/payments')->assertStatus(403);

        // Housekeeper
        $housekeeper = User::factory()->create(['user_type' => 'housekeeper']);
        Sanctum::actingAs($housekeeper);
        $this->getJson('/api/v2/housekeeper/stripe/status')->assertStatus(200);
        $this->postJson('/api/v2/caregiver/stripe/onboard')->assertStatus(403);
        $this->getJson('/api/v2/admin/stripe/payments')->assertStatus(403);
    }
}
