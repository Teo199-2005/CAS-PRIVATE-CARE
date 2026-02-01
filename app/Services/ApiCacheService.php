<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Closure;

/**
 * API Response Caching Service
 * 
 * Provides intelligent caching for API responses with
 * automatic cache invalidation and tag support.
 */
class ApiCacheService
{
    /**
     * Default cache TTL in seconds
     */
    protected int $defaultTtl = 300; // 5 minutes

    /**
     * Cache prefix for API responses
     */
    protected string $prefix = 'api_cache:';

    /**
     * Remember or fetch data from cache
     *
     * @param string $key Cache key
     * @param Closure $callback Data generator
     * @param int|null $ttl Time to live in seconds
     * @param array $tags Cache tags for invalidation
     * @return mixed
     */
    public function remember(string $key, Closure $callback, ?int $ttl = null, array $tags = []): mixed
    {
        $cacheKey = $this->prefix . $key;
        $ttl = $ttl ?? $this->defaultTtl;

        if (!empty($tags) && $this->supportsTags()) {
            return Cache::tags($tags)->remember($cacheKey, $ttl, $callback);
        }

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Remember data forever (until manually invalidated)
     *
     * @param string $key Cache key
     * @param Closure $callback Data generator
     * @param array $tags Cache tags for invalidation
     * @return mixed
     */
    public function rememberForever(string $key, Closure $callback, array $tags = []): mixed
    {
        $cacheKey = $this->prefix . $key;

        if (!empty($tags) && $this->supportsTags()) {
            return Cache::tags($tags)->rememberForever($cacheKey, $callback);
        }

        return Cache::rememberForever($cacheKey, $callback);
    }

    /**
     * Get cached data or null
     *
     * @param string $key Cache key
     * @param array $tags Cache tags
     * @return mixed|null
     */
    public function get(string $key, array $tags = []): mixed
    {
        $cacheKey = $this->prefix . $key;

        if (!empty($tags) && $this->supportsTags()) {
            return Cache::tags($tags)->get($cacheKey);
        }

        return Cache::get($cacheKey);
    }

    /**
     * Store data in cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds
     * @param array $tags Cache tags
     * @return bool
     */
    public function put(string $key, mixed $value, ?int $ttl = null, array $tags = []): bool
    {
        $cacheKey = $this->prefix . $key;
        $ttl = $ttl ?? $this->defaultTtl;

        if (!empty($tags) && $this->supportsTags()) {
            return Cache::tags($tags)->put($cacheKey, $value, $ttl);
        }

        return Cache::put($cacheKey, $value, $ttl);
    }

    /**
     * Remove a specific cache entry
     *
     * @param string $key Cache key
     * @param array $tags Cache tags
     * @return bool
     */
    public function forget(string $key, array $tags = []): bool
    {
        $cacheKey = $this->prefix . $key;

        if (!empty($tags) && $this->supportsTags()) {
            return Cache::tags($tags)->forget($cacheKey);
        }

        return Cache::forget($cacheKey);
    }

    /**
     * Flush all caches with specific tags
     *
     * @param array $tags Cache tags to flush
     * @return bool
     */
    public function flushTags(array $tags): bool
    {
        if (!$this->supportsTags()) {
            Log::warning('Cache driver does not support tags, skipping flush');
            return false;
        }

        return Cache::tags($tags)->flush();
    }

    /**
     * Invalidate caches related to a user
     *
     * @param int $userId User ID
     * @return bool
     */
    public function invalidateUser(int $userId): bool
    {
        $tags = ["user:{$userId}"];
        
        if ($this->supportsTags()) {
            return $this->flushTags($tags);
        }

        // Fallback: forget known user cache keys
        $keys = [
            "user:{$userId}:profile",
            "user:{$userId}:bookings",
            "user:{$userId}:notifications",
            "user:{$userId}:stats",
        ];

        foreach ($keys as $key) {
            $this->forget($key);
        }

        return true;
    }

    /**
     * Invalidate caches related to bookings
     *
     * @param int|null $bookingId Specific booking ID or null for all
     * @return bool
     */
    public function invalidateBookings(?int $bookingId = null): bool
    {
        if ($this->supportsTags()) {
            $tags = $bookingId 
                ? ["booking:{$bookingId}"]
                : ['bookings'];
            return $this->flushTags($tags);
        }

        if ($bookingId) {
            return $this->forget("booking:{$bookingId}");
        }

        return true;
    }

    /**
     * Invalidate caches related to caregivers
     *
     * @param int|null $caregiverId Specific caregiver ID or null for all
     * @return bool
     */
    public function invalidateCaregivers(?int $caregiverId = null): bool
    {
        if ($this->supportsTags()) {
            $tags = $caregiverId 
                ? ["caregiver:{$caregiverId}"]
                : ['caregivers'];
            return $this->flushTags($tags);
        }

        if ($caregiverId) {
            $keys = [
                "caregiver:{$caregiverId}:profile",
                "caregiver:{$caregiverId}:availability",
                "caregiver:{$caregiverId}:reviews",
            ];
            foreach ($keys as $key) {
                $this->forget($key);
            }
        }

        return true;
    }

    /**
     * Cache key generator for user-specific data
     *
     * @param int $userId User ID
     * @param string $resource Resource name
     * @param array $params Additional parameters
     * @return string
     */
    public function userKey(int $userId, string $resource, array $params = []): string
    {
        $key = "user:{$userId}:{$resource}";
        
        if (!empty($params)) {
            $key .= ':' . md5(serialize($params));
        }

        return $key;
    }

    /**
     * Cache key generator for list data
     *
     * @param string $resource Resource name
     * @param array $filters Filter parameters
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return string
     */
    public function listKey(string $resource, array $filters = [], int $page = 1, int $perPage = 15): string
    {
        $filterHash = md5(serialize($filters));
        return "{$resource}:list:{$filterHash}:p{$page}:pp{$perPage}";
    }

    /**
     * Cache key generator for single resource
     *
     * @param string $resource Resource name
     * @param int|string $id Resource ID
     * @return string
     */
    public function resourceKey(string $resource, int|string $id): string
    {
        return "{$resource}:{$id}";
    }

    /**
     * Get cache statistics (for debugging)
     *
     * @return array
     */
    public function getStats(): array
    {
        $driver = config('cache.default');
        
        return [
            'driver' => $driver,
            'supports_tags' => $this->supportsTags(),
            'prefix' => $this->prefix,
            'default_ttl' => $this->defaultTtl,
        ];
    }

    /**
     * Check if current cache driver supports tags
     *
     * @return bool
     */
    protected function supportsTags(): bool
    {
        $driver = config('cache.default');
        return in_array($driver, ['redis', 'memcached', 'array', 'dynamodb']);
    }

    /**
     * Set default TTL
     *
     * @param int $seconds TTL in seconds
     * @return self
     */
    public function setDefaultTtl(int $seconds): self
    {
        $this->defaultTtl = $seconds;
        return $this;
    }

    /**
     * Common TTL presets
     */
    public const TTL_SHORT = 60;         // 1 minute
    public const TTL_MEDIUM = 300;       // 5 minutes
    public const TTL_LONG = 1800;        // 30 minutes
    public const TTL_HOUR = 3600;        // 1 hour
    public const TTL_DAY = 86400;        // 24 hours
    public const TTL_WEEK = 604800;      // 7 days
}
