<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $clientUser = User::factory()->create([
            'user_type' => 'client',
            'status' => 'Active'
        ]);

        Client::factory()->create(['user_id' => $clientUser->id]);

        $this->client = $clientUser;
    }

    /** @test */
    public function client_can_get_their_stats()
    {
        $this->actingAs($this->client);

        Booking::factory()->create([
            'client_id' => $this->client->id,
            'status' => 'approved',
            'hourly_rate' => 30.00
        ]);

        $response = $this->getJson('/api/client/stats?client_id=' . $this->client->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_bookings',
            'active_bookings',
            'total_spent',
            'amount_due'
        ]);
    }

    /** @test */
    public function client_can_get_their_bookings()
    {
        $this->actingAs($this->client);

        Booking::factory()->count(3)->create([
            'client_id' => $this->client->id
        ]);

        $response = $this->getJson('/api/client/bookings');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function client_cannot_access_api_without_authentication()
    {
        $response = $this->getJson('/api/client/stats?client_id=' . $this->client->id);

        $response->assertStatus(401);
    }

    /** @test */
    public function client_profile_can_be_updated()
    {
        $this->actingAs($this->client);

        $response = $this->putJson('/api/user/' . $this->client->id, [
            'name' => 'Updated Name',
            'phone' => '(212) 555-9999'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'id' => $this->client->id,
            'name' => 'Updated Name',
            'phone' => '(212) 555-9999'
        ]);
    }

    /** @test */
    public function avatar_upload_validates_file_type()
    {
        $this->actingAs($this->client);

        $response = $this->postJson('/api/user/' . $this->client->id . '/avatar', [
            'avatar' => 'not-a-file'
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function zip_code_lookup_returns_valid_location()
    {
        $response = $this->getJson('/api/zipcode-lookup/10001');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'city',
            'state',
            'location'
        ]);
    }

    /** @test */
    public function zip_code_lookup_validates_format()
    {
        $response = $this->getJson('/api/zipcode-lookup/123');

        $response->assertStatus(422);
    }

    /** @test */
    public function invalid_zip_code_returns_404()
    {
        $response = $this->getJson('/api/zipcode-lookup/00000');

        $response->assertStatus(404);
    }
}
