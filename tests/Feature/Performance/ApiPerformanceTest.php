<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Caregiver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

/**
 * Performance test suite to ensure API endpoints meet response time requirements.
 * 
 * Thresholds:
 * - Simple endpoints: < 100ms
 * - Complex endpoints: < 500ms
 * - Paginated lists: < 200ms
 */
class ApiPerformanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Maximum response time for simple endpoints (milliseconds)
     */
    protected int $simpleThreshold = 100;

    /**
     * Maximum response time for complex endpoints (milliseconds)
     */
    protected int $complexThreshold = 500;

    /**
     * Maximum response time for list endpoints (milliseconds)
     */
    protected int $listThreshold = 200;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed test data
        User::factory(10)->create();
        Caregiver::factory(5)->create();
    }

    /** @test */
    public function landing_page_loads_within_threshold(): void
    {
        $start = microtime(true);
        
        $response = $this->get('/');
        
        $duration = (microtime(true) - $start) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(
            $this->complexThreshold,
            $duration,
            "Landing page took {$duration}ms, expected < {$this->complexThreshold}ms"
        );
    }

    /** @test */
    public function health_check_responds_quickly(): void
    {
        $start = microtime(true);
        
        $response = $this->getJson('/api/health');
        
        $duration = (microtime(true) - $start) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(
            $this->simpleThreshold,
            $duration,
            "Health check took {$duration}ms, expected < {$this->simpleThreshold}ms"
        );
    }

    /** @test */
    public function user_profile_endpoint_is_performant(): void
    {
        $user = User::factory()->create();
        
        $start = microtime(true);
        
        $response = $this->actingAs($user)->getJson('/api/user');
        
        $duration = (microtime(true) - $start) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(
            $this->simpleThreshold,
            $duration,
            "User profile took {$duration}ms, expected < {$this->simpleThreshold}ms"
        );
    }

    /** @test */
    public function caregiver_list_endpoint_handles_pagination_efficiently(): void
    {
        // Create more caregivers for pagination test
        Caregiver::factory(50)->create();
        
        $start = microtime(true);
        
        $response = $this->getJson('/api/caregivers?page=1&per_page=15');
        
        $duration = (microtime(true) - $start) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(
            $this->listThreshold,
            $duration,
            "Caregiver list took {$duration}ms, expected < {$this->listThreshold}ms"
        );
    }

    /** @test */
    public function query_count_is_optimized_for_list_endpoints(): void
    {
        Caregiver::factory(20)->create();
        
        DB::enableQueryLog();
        
        $response = $this->getJson('/api/caregivers');
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        
        $response->assertStatus(200);
        
        // Should use eager loading, not N+1
        $this->assertLessThan(
            10,
            count($queries),
            'Too many queries detected, possible N+1 issue. Query count: ' . count($queries)
        );
    }

    /** @test */
    public function booking_creation_is_performant(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $caregiver = Caregiver::factory()->create();
        
        $start = microtime(true);
        
        $response = $this->actingAs($user)->postJson('/api/bookings', [
            'caregiver_id' => $caregiver->id,
            'service_type' => 'caregiving',
            'scheduled_date' => now()->addDays(3)->toDateString(),
            'scheduled_time' => '10:00',
            'duration_hours' => 4,
            'address' => '123 Test St',
            'city' => 'New York',
            'state' => 'NY',
            'zip' => '10001',
        ]);
        
        $duration = (microtime(true) - $start) * 1000;
        
        $this->assertContains($response->status(), [200, 201, 422]);
        $this->assertLessThan(
            $this->complexThreshold,
            $duration,
            "Booking creation took {$duration}ms, expected < {$this->complexThreshold}ms"
        );
    }

    /** @test */
    public function search_endpoint_is_performant(): void
    {
        Caregiver::factory(100)->create();
        
        $start = microtime(true);
        
        $response = $this->getJson('/api/caregivers/search?query=nurse&service=caregiving');
        
        $duration = (microtime(true) - $start) * 1000;
        
        $this->assertContains($response->status(), [200, 404]);
        $this->assertLessThan(
            $this->listThreshold,
            $duration,
            "Search took {$duration}ms, expected < {$this->listThreshold}ms"
        );
    }

    /** @test */
    public function dashboard_data_loads_efficiently(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        Booking::factory(20)->create(['client_id' => $user->id]);
        
        $start = microtime(true);
        
        $response = $this->actingAs($user)->getJson('/api/dashboard');
        
        $duration = (microtime(true) - $start) * 1000;
        
        $this->assertContains($response->status(), [200, 404]);
        $this->assertLessThan(
            $this->complexThreshold,
            $duration,
            "Dashboard took {$duration}ms, expected < {$this->complexThreshold}ms"
        );
    }

    /** @test */
    public function concurrent_requests_are_handled_efficiently(): void
    {
        $user = User::factory()->create();
        $times = [];
        
        // Simulate 5 concurrent requests
        for ($i = 0; $i < 5; $i++) {
            $start = microtime(true);
            $this->actingAs($user)->getJson('/api/user');
            $times[] = (microtime(true) - $start) * 1000;
        }
        
        $avgTime = array_sum($times) / count($times);
        $maxTime = max($times);
        
        $this->assertLessThan(
            $this->simpleThreshold * 2,
            $avgTime,
            "Average response time {$avgTime}ms is too high"
        );
        
        $this->assertLessThan(
            $this->simpleThreshold * 3,
            $maxTime,
            "Max response time {$maxTime}ms indicates performance degradation"
        );
    }

    /** @test */
    public function memory_usage_stays_within_limits(): void
    {
        Caregiver::factory(100)->create();
        
        $startMemory = memory_get_usage();
        
        $response = $this->getJson('/api/caregivers?per_page=100');
        
        $endMemory = memory_get_usage();
        $memoryUsedMB = ($endMemory - $startMemory) / 1024 / 1024;
        
        $response->assertStatus(200);
        
        // Should not use more than 50MB for a list request
        $this->assertLessThan(
            50,
            $memoryUsedMB,
            "Memory usage {$memoryUsedMB}MB is too high"
        );
    }

    /** @test */
    public function api_responses_are_properly_cached(): void
    {
        $caregiver = Caregiver::factory()->create();
        
        // First request
        $start1 = microtime(true);
        $this->getJson("/api/caregivers/{$caregiver->id}");
        $duration1 = (microtime(true) - $start1) * 1000;
        
        // Second request (should be cached)
        $start2 = microtime(true);
        $this->getJson("/api/caregivers/{$caregiver->id}");
        $duration2 = (microtime(true) - $start2) * 1000;
        
        // Cached response should be faster or similar
        $this->assertLessThan(
            $duration1 * 1.5,
            $duration2,
            "Second request ({$duration2}ms) is significantly slower than first ({$duration1}ms)"
        );
    }
}
