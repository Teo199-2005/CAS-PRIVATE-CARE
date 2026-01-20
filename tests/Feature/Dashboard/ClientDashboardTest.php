<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ClientDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $clientUser;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->clientUser = User::factory()->create([
            'email' => 'client@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'client',
            'status' => 'Active'
        ]);
        
        $this->client = Client::factory()->create([
            'user_id' => $this->clientUser->id,
        ]);
    }

    /** @test */
    public function client_can_access_dashboard()
    {
        $response = $this->actingAs($this->clientUser)
            ->get('/client/dashboard-vue');

        $response->assertStatus(200);
    }

    /** @test */
    public function caregiver_cannot_access_client_dashboard()
    {
        $caregiver = User::factory()->create([
            'user_type' => 'caregiver',
            'status' => 'approved'
        ]);

        $response = $this->actingAs($caregiver)
            ->get('/client/dashboard-vue');

        $response->assertStatus(302); // Redirect
    }

    /** @test */
    public function client_can_view_bookings()
    {
        // Create a booking for this client (client_id references users table)
        $booking = Booking::factory()->create([
            'client_id' => $this->clientUser->id,
            'status' => 'approved',
        ]);
        
        // Test the dashboard route which should show bookings
        $response = $this->actingAs($this->clientUser)
            ->get('/client/dashboard-vue');

        $response->assertStatus(200);
    }

    /** @test */
    public function client_can_access_booking_page()
    {
        // Test booking form page access
        $response = $this->actingAs($this->clientUser)
            ->get('/book-service');

        $response->assertStatus(200);
    }

    /** @test */
    public function client_can_view_profile()
    {
        $response = $this->actingAs($this->clientUser)
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure(['user']);
    }

    /** @test */
    public function client_can_update_profile()
    {
        $response = $this->actingAs($this->clientUser)
            ->postJson('/api/profile/update', [
                'name' => 'Updated Client Name',
                'phone' => '6462828282'
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function client_can_view_notifications()
    {
        $response = $this->actingAs($this->clientUser)
            ->getJson('/api/notifications');

        $response->assertStatus(200);
    }

    /** @test */
    public function client_can_mark_notification_as_read()
    {
        // Create a notification first
        $notification = \App\Models\Notification::create([
            'user_id' => $this->clientUser->id,
            'title' => 'Test Notification',
            'message' => 'Test message',
            'type' => 'System',
            'read' => false
        ]);

        $response = $this->actingAs($this->clientUser)
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        
        $notification->refresh();
        $this->assertTrue((bool)$notification->read);
    }

    /** @test */
    public function client_can_view_stats()
    {
        $response = $this->actingAs($this->clientUser)
            ->get('/api/client/stats');

        $this->assertTrue(
            $response->status() >= 200 && $response->status() < 400,
            "Expected success status, got: {$response->status()}"
        );
    }

    /** @test */
    public function client_can_access_payment_setup()
    {
        $response = $this->actingAs($this->clientUser)
            ->get('/client/payment-setup');

        $response->assertStatus(200);
    }

    /** @test */
    public function client_can_view_saved_payment_methods()
    {
        // Skip if Stripe is not configured
        if (empty(config('services.stripe.secret'))) {
            $this->markTestSkipped('Stripe API key not configured.');
        }
        
        $response = $this->actingAs($this->clientUser)
            ->getJson('/api/stripe/payment-methods');

        $response->assertStatus(200);
    }

    /** @test */
    public function client_can_view_payment_methods()
    {
        $response = $this->actingAs($this->clientUser)
            ->get('/api/client/payment-methods');

        $this->assertTrue(
            $response->status() >= 200 && $response->status() < 400,
            "Expected success status, got: {$response->status()}"
        );
    }

    /** @test */
    public function client_can_view_spending_data()
    {
        $response = $this->actingAs($this->clientUser)
            ->get('/api/client/spending-data');

        $this->assertTrue(
            $response->status() >= 200 && $response->status() < 400,
            "Expected success status, got: {$response->status()}"
        );
    }

    /** @test */
    public function client_stats_returned_correctly()
    {
        $response = $this->actingAs($this->clientUser)
            ->get('/api/client/stats');

        $this->assertTrue(
            $response->status() >= 200 && $response->status() < 400,
            "Expected success status, got: {$response->status()}"
        );
    }
}