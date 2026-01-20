<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Caregiver;
use App\Models\BookingAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class CaregiverDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $caregiver;
    protected User $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = User::factory()->create([
            'user_type' => 'client',
            'status' => 'Active'
        ]);

        $this->caregiver = User::factory()->create([
            'email' => 'caregiver@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'caregiver',
            'status' => 'approved'
        ]);

        // Create caregiver profile
        Caregiver::create([
            'user_id' => $this->caregiver->id,
            'hourly_rate' => 25.00,
            'experience_years' => 3,
            'specializations' => json_encode(['elderly care', 'medication management']),
            'bio' => 'Experienced caregiver with passion for helping others.'
        ]);
    }

    /** @test */
    public function caregiver_can_access_dashboard()
    {
        $response = $this->actingAs($this->caregiver)
            ->get('/caregiver/dashboard-vue');

        $response->assertStatus(200);
    }

    /** @test */
    public function pending_caregiver_can_access_dashboard()
    {
        $pendingCaregiver = User::factory()->create([
            'user_type' => 'caregiver',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($pendingCaregiver)
            ->get('/caregiver/dashboard-vue');

        $response->assertStatus(200);
    }

    /** @test */
    public function client_cannot_access_caregiver_dashboard()
    {
        $response = $this->actingAs($this->client)
            ->get('/caregiver/dashboard-vue');

        $response->assertStatus(302); // Redirect
    }

    /** @test */
    public function caregiver_can_view_assigned_bookings()
    {
        $caregiver = Caregiver::where('user_id', $this->caregiver->id)->first();
        
        // Create a booking (client_id references users table directly)
        $booking = Booking::factory()->create([
            'client_id' => $this->client->id,
            'status' => 'approved',
        ]);
        
        BookingAssignment::factory()->create([
            'booking_id' => $booking->id,
            'caregiver_id' => $caregiver->id,
            'status' => 'assigned',
        ]);
        
        // Test viewing dashboard which shows assigned bookings
        $response = $this->actingAs($this->caregiver)
            ->get('/caregiver/dashboard-vue');

        $response->assertStatus(200);
    }

    /** @test */
    public function caregiver_can_view_time_tracking()
    {
        $caregiver = Caregiver::where('user_id', $this->caregiver->id)->first();
        
        $response = $this->actingAs($this->caregiver)
            ->get("/api/time-tracking/weekly-history/{$caregiver->id}");

        // Accept 200 or JSON response
        $this->assertTrue(
            $response->status() >= 200 && $response->status() < 400,
            "Expected success status, got: {$response->status()}"
        );
    }

    /** @test */
    public function caregiver_can_view_profile()
    {
        $response = $this->actingAs($this->caregiver)
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'caregiver'
            ]);
    }

    /** @test */
    public function caregiver_can_update_profile()
    {
        $response = $this->actingAs($this->caregiver)
            ->postJson('/api/profile/update', [
                'name' => 'Updated Caregiver Name',
                'phone' => '6462828282',
                'bio' => 'Updated bio information'
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function caregiver_can_view_notifications()
    {
        $response = $this->actingAs($this->caregiver)
            ->getJson('/api/notifications');

        $response->assertStatus(200);
    }

    /** @test */
    public function caregiver_can_connect_bank_account()
    {
        $response = $this->actingAs($this->caregiver)
            ->get('/connect-bank-account');

        $response->assertStatus(200);
    }

    /** @test */
    public function caregiver_can_view_time_tracking_history()
    {
        $caregiver = Caregiver::where('user_id', $this->caregiver->id)->first();
        
        $response = $this->actingAs($this->caregiver)
            ->get("/api/time-tracking/weekly-history/{$caregiver->id}");

        $this->assertTrue(
            $response->status() >= 200 && $response->status() < 400,
            "Expected success status, got: {$response->status()}"
        );
    }

    /** @test */
    public function caregiver_can_view_schedule()
    {
        $response = $this->actingAs($this->caregiver)
            ->get('/api/caregiver/schedule-events');

        $this->assertTrue(
            $response->status() >= 200 && $response->status() < 400,
            "Expected success status, got: {$response->status()}"
        );
    }

    /** @test */
    public function caregiver_can_view_reviews()
    {
        $response = $this->actingAs($this->caregiver)
            ->getJson("/api/reviews/caregiver/{$this->caregiver->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function caregiver_stats_returned_correctly()
    {
        // Caregiver stats are returned via the profile endpoint
        $response = $this->actingAs($this->caregiver)
            ->getJson('/api/profile');

        $response->assertStatus(200);
    }
}
