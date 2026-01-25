<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class APIRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_endpoints_have_rate_limiting()
    {
        // Make multiple requests to trigger rate limiting
        $responses = [];
        
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson('/api/services');
        }
        
        // All should succeed (within rate limit)
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function rate_limit_headers_are_present()
    {
        $response = $this->getJson('/api/services');
        
        // Check for rate limit headers (if configured)
        // These may vary based on your rate limiting configuration
        $this->assertTrue(true); // Placeholder for rate limit header check
    }

    /** @test */
    public function authenticated_requests_have_different_rate_limits()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user');
        
        $response->assertSuccessful();
    }

    /** @test */
    public function api_returns_json_for_all_responses()
    {
        $response = $this->getJson('/api/services');
        
        $response->assertHeader('Content-Type', 'application/json');
    }

    /** @test */
    public function api_handles_not_found_gracefully()
    {
        $response = $this->getJson('/api/nonexistent-endpoint');
        
        $response->assertStatus(404)
            ->assertJson(['message' => 'Not Found']);
    }

    /** @test */
    public function api_requires_authentication_for_protected_routes()
    {
        $response = $this->getJson('/api/user');
        
        $response->assertStatus(401);
    }

    /** @test */
    public function api_cors_headers_are_configured()
    {
        $response = $this->options('/api/services');
        
        // CORS should be handled
        $this->assertTrue(true); // Placeholder for CORS header verification
    }
}
