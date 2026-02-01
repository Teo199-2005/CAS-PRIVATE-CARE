<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test cases for Health Check API endpoints
 */
class HealthCheckTest extends TestCase
{
    /**
     * Test ping endpoint returns OK
     */
    public function test_ping_returns_ok(): void
    {
        $response = $this->getJson('/api/health/ping');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertJsonStructure([
                'status',
                'timestamp',
            ]);
    }

    /**
     * Test live endpoint returns alive
     */
    public function test_live_returns_alive(): void
    {
        $response = $this->getJson('/api/health/live');

        $response->assertStatus(200)
            ->assertJson([
                'alive' => true,
            ]);
    }

    /**
     * Test ready endpoint checks database
     */
    public function test_ready_returns_ready(): void
    {
        $response = $this->getJson('/api/health/ready');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ready',
            ]);
    }

    /**
     * Test check endpoint returns detailed health info
     */
    public function test_check_returns_detailed_health(): void
    {
        $response = $this->getJson('/api/health/check');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'services' => [
                    'database',
                    'cache',
                    'memory',
                    'php_version',
                    'laravel_version',
                    'timestamp',
                ],
            ]);
    }

    /**
     * Test version endpoint returns app info
     */
    public function test_version_returns_app_info(): void
    {
        $response = $this->getJson('/api/health/version');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'app',
                'version',
                'environment',
                'php_version',
                'laravel_version',
            ]);
    }

    /**
     * Test health endpoints are public (no auth required)
     */
    public function test_health_endpoints_are_public(): void
    {
        // All health endpoints should be accessible without authentication
        $endpoints = [
            '/api/health/ping',
            '/api/health/live',
            '/api/health/ready',
            '/api/health/check',
            '/api/health/version',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $this->assertNotEquals(401, $response->getStatusCode(), "Endpoint {$endpoint} should be public");
            $this->assertNotEquals(403, $response->getStatusCode(), "Endpoint {$endpoint} should be public");
        }
    }

    /**
     * Test health check database status
     */
    public function test_check_includes_database_status(): void
    {
        $response = $this->getJson('/api/health/check');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('services', $data);
        $this->assertArrayHasKey('database', $data['services']);
        $this->assertArrayHasKey('status', $data['services']['database']);
    }

    /**
     * Test health check cache status
     */
    public function test_check_includes_cache_status(): void
    {
        $response = $this->getJson('/api/health/check');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('cache', $data['services']);
        $this->assertArrayHasKey('status', $data['services']['cache']);
    }

    /**
     * Test health check memory usage
     */
    public function test_check_includes_memory_usage(): void
    {
        $response = $this->getJson('/api/health/check');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('memory', $data['services']);
        $this->assertArrayHasKey('current_mb', $data['services']['memory']);
        $this->assertArrayHasKey('peak_mb', $data['services']['memory']);
    }
}
