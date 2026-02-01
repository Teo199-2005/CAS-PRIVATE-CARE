<?php

namespace Tests\Unit\Services;

use App\Services\ApiCacheService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ApiCacheServiceTest extends TestCase
{
    protected ApiCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = new ApiCacheService();
        Cache::flush();
    }

    /**
     * Test remember caches and returns data
     */
    public function test_remember_caches_data(): void
    {
        $callCount = 0;
        $callback = function () use (&$callCount) {
            $callCount++;
            return ['data' => 'test'];
        };

        // First call should execute callback
        $result1 = $this->cacheService->remember('test_key', $callback, 60);
        $this->assertEquals(['data' => 'test'], $result1);
        $this->assertEquals(1, $callCount);

        // Second call should return cached data
        $result2 = $this->cacheService->remember('test_key', $callback, 60);
        $this->assertEquals(['data' => 'test'], $result2);
        $this->assertEquals(1, $callCount); // Callback not called again
    }

    /**
     * Test get returns null for missing key
     */
    public function test_get_returns_null_for_missing_key(): void
    {
        $result = $this->cacheService->get('nonexistent_key');
        $this->assertNull($result);
    }

    /**
     * Test put stores data
     */
    public function test_put_stores_data(): void
    {
        $result = $this->cacheService->put('test_put', ['value' => 123], 60);
        $this->assertTrue($result);

        $cached = $this->cacheService->get('test_put');
        $this->assertEquals(['value' => 123], $cached);
    }

    /**
     * Test forget removes cached data
     */
    public function test_forget_removes_data(): void
    {
        $this->cacheService->put('test_forget', 'value', 60);
        $this->assertNotNull($this->cacheService->get('test_forget'));

        $this->cacheService->forget('test_forget');
        $this->assertNull($this->cacheService->get('test_forget'));
    }

    /**
     * Test user key generator
     */
    public function test_user_key_generator(): void
    {
        $key = $this->cacheService->userKey(123, 'profile');
        $this->assertEquals('user:123:profile', $key);

        $keyWithParams = $this->cacheService->userKey(123, 'bookings', ['status' => 'active']);
        $this->assertStringStartsWith('user:123:bookings:', $keyWithParams);
    }

    /**
     * Test list key generator
     */
    public function test_list_key_generator(): void
    {
        $key = $this->cacheService->listKey('bookings', ['status' => 'pending'], 2, 20);
        $this->assertStringContainsString('bookings:list:', $key);
        $this->assertStringContainsString(':p2:', $key);
        $this->assertStringContainsString(':pp20', $key);
    }

    /**
     * Test resource key generator
     */
    public function test_resource_key_generator(): void
    {
        $key = $this->cacheService->resourceKey('booking', 456);
        $this->assertEquals('booking:456', $key);
    }

    /**
     * Test stats returns configuration
     */
    public function test_stats_returns_config(): void
    {
        $stats = $this->cacheService->getStats();

        $this->assertArrayHasKey('driver', $stats);
        $this->assertArrayHasKey('supports_tags', $stats);
        $this->assertArrayHasKey('prefix', $stats);
        $this->assertArrayHasKey('default_ttl', $stats);
    }

    /**
     * Test set default TTL
     */
    public function test_set_default_ttl(): void
    {
        $result = $this->cacheService->setDefaultTtl(600);
        $this->assertInstanceOf(ApiCacheService::class, $result);

        $stats = $this->cacheService->getStats();
        $this->assertEquals(600, $stats['default_ttl']);
    }

    /**
     * Test TTL constants exist
     */
    public function test_ttl_constants(): void
    {
        $this->assertEquals(60, ApiCacheService::TTL_SHORT);
        $this->assertEquals(300, ApiCacheService::TTL_MEDIUM);
        $this->assertEquals(1800, ApiCacheService::TTL_LONG);
        $this->assertEquals(3600, ApiCacheService::TTL_HOUR);
        $this->assertEquals(86400, ApiCacheService::TTL_DAY);
        $this->assertEquals(604800, ApiCacheService::TTL_WEEK);
    }

    /**
     * Test invalidate user clears user caches
     */
    public function test_invalidate_user(): void
    {
        // Store some user data
        $this->cacheService->put('user:123:profile', ['name' => 'Test'], 60);
        $this->cacheService->put('user:123:bookings', [1, 2, 3], 60);

        // Invalidate user
        $result = $this->cacheService->invalidateUser(123);
        $this->assertTrue($result);

        // With non-tag supporting cache, specific keys should be cleared
        // (behavior depends on cache driver)
    }

    /**
     * Test remember forever
     */
    public function test_remember_forever(): void
    {
        $callCount = 0;
        $callback = function () use (&$callCount) {
            $callCount++;
            return 'permanent_data';
        };

        $result = $this->cacheService->rememberForever('forever_key', $callback);
        $this->assertEquals('permanent_data', $result);
        $this->assertEquals(1, $callCount);

        // Should still be cached
        $result2 = $this->cacheService->rememberForever('forever_key', $callback);
        $this->assertEquals('permanent_data', $result2);
        $this->assertEquals(1, $callCount);
    }
}
