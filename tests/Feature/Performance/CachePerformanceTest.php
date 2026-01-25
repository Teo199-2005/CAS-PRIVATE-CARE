<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CachePerformanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cache_driver_is_configured()
    {
        $driver = config('cache.default');
        $this->assertNotNull($driver);
        $this->assertContains($driver, ['file', 'redis', 'memcached', 'database', 'array']);
    }

    /** @test */
    public function cache_can_store_and_retrieve_values()
    {
        Cache::put('test_key', 'test_value', 60);
        
        $this->assertEquals('test_value', Cache::get('test_key'));
    }

    /** @test */
    public function cache_can_forget_values()
    {
        Cache::put('forget_key', 'value', 60);
        Cache::forget('forget_key');
        
        $this->assertNull(Cache::get('forget_key'));
    }

    /** @test */
    public function cache_remember_pattern_works()
    {
        $callCount = 0;
        
        $value1 = Cache::remember('remember_key', 60, function () use (&$callCount) {
            $callCount++;
            return 'computed_value';
        });
        
        $value2 = Cache::remember('remember_key', 60, function () use (&$callCount) {
            $callCount++;
            return 'computed_value';
        });
        
        $this->assertEquals('computed_value', $value1);
        $this->assertEquals('computed_value', $value2);
        $this->assertEquals(1, $callCount); // Should only compute once
    }

    /** @test */
    public function database_query_count_is_reasonable()
    {
        DB::enableQueryLog();
        
        // Perform a simple database operation
        $this->get('/');
        
        $queries = DB::getQueryLog();
        
        // Homepage should not execute too many queries
        $this->assertLessThan(50, count($queries), 'Too many queries on homepage');
        
        DB::disableQueryLog();
    }

    /** @test */
    public function session_driver_is_configured()
    {
        $driver = config('session.driver');
        $this->assertNotNull($driver);
        $this->assertContains($driver, ['file', 'cookie', 'database', 'redis', 'memcached', 'array']);
    }
}
