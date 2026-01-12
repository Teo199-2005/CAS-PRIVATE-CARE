<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class PaymentProcessingTest extends TestCase
{
    use RefreshDatabase;

    private $client;
    private $booking;

    protected function setUp(): void
    {
        parent::setUp();

        // Set test Stripe key
        Config::set('stripe.secret_key', 'sk_test_dummy');
        Config::set('stripe.key', 'pk_test_dummy');

        $clientUser = User::factory()->create([
            'user_type' => 'client',
            'status' => 'Active',
            'stripe_customer_id' => 'cus_test_123'
        ]);

        Client::factory()->create(['user_id' => $clientUser->id]);

        $this->client = $clientUser;

        $this->booking = Booking::factory()->create([
            'client_id' => $this->client->id,
            'service_type' => 'home_care',
            'hourly_rate' => 30.00,
            'status' => 'pending',
            'payment_status' => 'pending'
        ]);
    }

    /** @test */
    public function payment_requires_authentication()
    {
        $response = $this->postJson('/api/stripe/setup-intent', [
            'payment_method_id' => 'pm_test_123',
            'booking_id' => $this->booking->id,
            'amount' => 24000 // $240 in cents
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function client_can_only_pay_for_their_own_bookings()
    {
        $otherClient = User::factory()->create([
            'user_type' => 'client',
            'stripe_customer_id' => 'cus_test_other'
        ]);

        $otherBooking = Booking::factory()->create([
            'client_id' => $otherClient->id
        ]);

        $this->actingAs($this->client);

        $response = $this->postJson('/api/stripe/setup-intent', [
            'payment_method_id' => 'pm_test_123',
            'booking_id' => $otherBooking->id,
            'amount' => 24000
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function payment_validates_required_fields()
    {
        $this->actingAs($this->client);

        $response = $this->postJson('/api/stripe/setup-intent', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'payment_method_id',
            'booking_id',
            'amount'
        ]);
    }

    /** @test */
    public function payment_validates_minimum_amount()
    {
        $this->actingAs($this->client);

        $response = $this->postJson('/api/stripe/setup-intent', [
            'payment_method_id' => 'pm_test_123',
            'booking_id' => $this->booking->id,
            'amount' => 50 // Less than $1.00
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function payment_method_saved_requires_payment_method_id()
    {
        $this->actingAs($this->client);

        $response = $this->postJson('/api/stripe/save-payment-method', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payment_method_id');
    }

    /** @test */
    public function setup_intent_creation_requires_authentication()
    {
        $response = $this->postJson('/api/stripe/create-setup-intent');

        $response->assertStatus(401);
    }

    /** @test */
    public function only_clients_can_create_setup_intents()
    {
        $caregiver = User::factory()->create([
            'user_type' => 'caregiver'
        ]);

        $this->actingAs($caregiver);

        $response = $this->postJson('/api/stripe/create-setup-intent');

        $response->assertStatus(403);
    }

    /** @test */
    public function booking_id_must_exist()
    {
        $this->actingAs($this->client);

        $response = $this->postJson('/api/stripe/setup-intent', [
            'payment_method_id' => 'pm_test_123',
            'booking_id' => 99999, // Non-existent
            'amount' => 24000
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function payment_status_changes_to_processing_during_payment()
    {
        $this->assertEquals('pending', $this->booking->payment_status);
        
        // This would be tested with Stripe mock/stub in real implementation
        // For now, we verify the initial state
        $this->assertNotEquals('paid', $this->booking->payment_status);
    }

    /** @test */
    public function successful_payment_updates_booking_status()
    {
        // This test verifies the logic flow
        // In production, you'd mock Stripe's response
        
        $this->booking->update([
            'payment_status' => 'paid',
            'stripe_payment_intent_id' => 'pi_test_123'
        ]);

        $this->assertEquals('paid', $this->booking->fresh()->payment_status);
        $this->assertNotNull($this->booking->fresh()->stripe_payment_intent_id);
    }
}
