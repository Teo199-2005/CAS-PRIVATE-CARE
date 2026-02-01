<?php

namespace Tests\Feature\Database;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Caregiver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

/**
 * Query Optimization Test
 * 
 * Tests to ensure database queries are efficient and don't have N+1 problems.
 */
class QueryOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable query log by default
        DB::disableQueryLog();
    }

    /**
     * @test
     * Test that client dashboard uses efficient queries.
     */
    public function client_dashboard_uses_efficient_queries(): void
    {
        // Create test data
        $client = User::factory()->create(['user_type' => 'client']);
        
        // Create bookings if factory exists
        if (class_exists(\Database\Factories\BookingFactory::class)) {
            Booking::factory()->count(10)->create(['client_id' => $client->id]);
        }
        
        DB::enableQueryLog();
        
        $this->actingAs($client)
            ->getJson('/api/client/bookings');
        
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        
        // Should be less than 10 queries (auth + main query + eager loads)
        $this->assertLessThan(
            15, 
            $queryCount, 
            "Client dashboard has potential N+1 problem: {$queryCount} queries executed. " .
            "Review eager loading in the controller."
        );
    }

    /**
     * @test
     * Test that admin dashboard uses efficient queries.
     */
    public function admin_dashboard_uses_efficient_queries(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        
        DB::enableQueryLog();
        
        $response = $this->actingAs($admin)
            ->getJson('/api/admin/stats');
        
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        
        // Admin stats should use caching and efficient queries
        $this->assertLessThan(
            20, 
            $queryCount,
            "Admin dashboard has too many queries: {$queryCount}. " .
            "Consider caching aggregate queries."
        );
    }

    /**
     * @test
     * Test that caregiver listing uses eager loading.
     */
    public function caregiver_listing_uses_eager_loading(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        
        // Create some caregivers
        User::factory()->count(5)->create(['user_type' => 'caregiver']);
        
        DB::enableQueryLog();
        
        $this->actingAs($admin)
            ->getJson('/api/admin/caregivers');
        
        $queries = DB::getQueryLog();
        
        // Should not have N+1 (1 query per caregiver for user data)
        // Expected: 1 auth + 1 caregivers + 1 users eager load = ~3-5 queries
        $this->assertLessThan(
            10, 
            count($queries),
            "Caregiver listing may have N+1 problem: " . count($queries) . " queries"
        );
    }

    /**
     * @test
     * Test that booking listing with relationships is efficient.
     */
    public function booking_listing_uses_eager_loading(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        
        DB::enableQueryLog();
        
        $this->actingAs($admin)
            ->getJson('/api/admin/bookings');
        
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        
        // Bookings with client, assignments, caregiver should use eager loading
        $this->assertLessThan(
            15, 
            $queryCount,
            "Booking listing has too many queries: {$queryCount}"
        );
    }

    /**
     * @test
     * Test that repeated similar queries are detected.
     */
    public function detect_repeated_query_patterns(): void
    {
        $queries = [
            ['query' => 'SELECT * FROM users WHERE id = 1', 'time' => 1],
            ['query' => 'SELECT * FROM users WHERE id = 2', 'time' => 1],
            ['query' => 'SELECT * FROM users WHERE id = 3', 'time' => 1],
            ['query' => 'SELECT * FROM users WHERE id = 4', 'time' => 1],
            ['query' => 'SELECT * FROM users WHERE id = 5', 'time' => 1],
            ['query' => 'SELECT * FROM users WHERE id = 6', 'time' => 1],
        ];

        $hasN1 = $this->detectN1Pattern($queries);
        
        $this->assertTrue($hasN1, 'Should detect N+1 pattern in repeated queries');
    }

    /**
     * @test
     * Test that diverse queries don't trigger N+1 detection.
     */
    public function diverse_queries_not_flagged_as_n1(): void
    {
        $queries = [
            ['query' => 'SELECT * FROM users WHERE id = 1', 'time' => 1],
            ['query' => 'SELECT * FROM bookings WHERE client_id = 1', 'time' => 1],
            ['query' => 'SELECT * FROM caregivers', 'time' => 1],
            ['query' => 'SELECT COUNT(*) FROM payments', 'time' => 1],
        ];

        $hasN1 = $this->detectN1Pattern($queries);
        
        $this->assertFalse($hasN1, 'Diverse queries should not be flagged as N+1');
    }

    /**
     * Helper to detect N+1 patterns (mirrors middleware logic).
     */
    protected function detectN1Pattern(array $queries): bool
    {
        if (count($queries) < 5) {
            return false;
        }

        $patterns = [];
        foreach ($queries as $query) {
            $normalized = preg_replace('/\s+/', ' ', $query['query']);
            $normalized = preg_replace('/\d+/', 'N', $normalized);
            $normalized = preg_replace("/'[^']*'/", "'X'", $normalized);
            
            $patterns[$normalized] = ($patterns[$normalized] ?? 0) + 1;
        }

        foreach ($patterns as $count) {
            if ($count > 5) {
                return true;
            }
        }

        return false;
    }
}
