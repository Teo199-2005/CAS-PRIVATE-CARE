<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;
use App\Models\User;
use App\Models\Housekeeper;
use App\Models\Caregiver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * Housekeeper accounts are decommissioned; dashboards and HK-specific APIs are removed or redirected.
 */
class HousekeeperDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $housekeeper;
    protected User $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create([
            'user_type' => 'client',
            'status' => 'Active',
        ]);

        $this->housekeeper = User::factory()->create([
            'email' => 'housekeeper@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'housekeeper',
            'status' => 'approved',
        ]);

        Housekeeper::create([
            'user_id' => $this->housekeeper->id,
            'hourly_rate' => 30.00,
            'experience_years' => 5,
            'specializations' => json_encode(['deep cleaning', 'laundry', 'organizing']),
        ]);
    }

    /** @test */
    public function housekeeper_dashboard_redirects_to_login_with_message(): void
    {
        $response = $this->actingAs($this->housekeeper)
            ->get('/housekeeper/dashboard-vue');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function pending_housekeeper_dashboard_redirects_to_login(): void
    {
        $pendingHousekeeper = User::factory()->create([
            'user_type' => 'housekeeper',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($pendingHousekeeper)
            ->get('/housekeeper/dashboard-vue');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function rejected_housekeeper_cannot_log_in(): void
    {
        $rejectedHousekeeper = User::factory()->create([
            'user_type' => 'housekeeper',
            'status' => 'rejected',
        ]);

        $response = $this->post('/login', [
            'email' => $rejectedHousekeeper->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    /** @test */
    public function client_cannot_access_housekeeper_dashboard(): void
    {
        $response = $this->actingAs($this->client)
            ->get('/housekeeper/dashboard-vue');

        $response->assertStatus(302);
    }

    /** @test */
    public function housekeeper_earnings_api_is_removed(): void
    {
        Housekeeper::where('user_id', $this->housekeeper->id)->first();

        $response = $this->actingAs($this->housekeeper)
            ->getJson("/api/housekeeper/{$this->housekeeper->id}/earnings");

        $response->assertNotFound();
    }

    /** @test */
    public function housekeeper_bank_connect_redirects(): void
    {
        $response = $this->actingAs($this->housekeeper)
            ->get('/connect-bank-account-housekeeper');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function housekeeper_profile_update_is_blocked(): void
    {
        $response = $this->actingAs($this->housekeeper)
            ->postJson('/api/profile/update', [
                'name' => 'Updated Housekeeper Name',
                'phone' => '6462828282',
            ]);

        $response->assertStatus(403);
    }
}
