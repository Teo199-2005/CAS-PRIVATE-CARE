<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class QueryCacheService
{
    /**
     * Cache duration in seconds (default: 5 minutes)
     */
    protected int $defaultTtl = 300;
    
    /**
     * Cache tags for easier invalidation
     */
    protected array $tags = [];
    
    /**
     * Set cache tags
     */
    public function tags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }
    
    /**
     * Remember query result with caching
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $cacheKey = $this->generateKey($key);
        
        try {
            if (!empty($this->tags)) {
                return Cache::tags($this->tags)->remember($cacheKey, $ttl, $callback);
            }
            
            return Cache::remember($cacheKey, $ttl, $callback);
        } catch (\Exception $e) {
            Log::error('Cache remember failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to direct query execution
            return $callback();
        }
    }
    
    /**
     * Cache user's bookings with related data
     */
    public function userBookings(int $userId, ?int $ttl = null): Collection
    {
        return $this->tags(['user_bookings', "user_{$userId}"])
            ->remember("user_bookings_{$userId}", function () use ($userId) {
                return \App\Models\Booking::where('client_id', $userId)
                    ->with(['caregiver', 'payments', 'timeTrackings'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }, $ttl);
    }
    
    /**
     * Cache caregiver's assignments
     */
    public function caregiverAssignments(int $caregiverId, ?int $ttl = null): Collection
    {
        return $this->tags(['caregiver_assignments', "caregiver_{$caregiverId}"])
            ->remember("caregiver_assignments_{$caregiverId}", function () use ($caregiverId) {
                return \App\Models\BookingAssignment::where('caregiver_id', $caregiverId)
                    ->with(['booking.client'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }, $ttl);
    }
    
    /**
     * Cache dashboard statistics
     */
    public function dashboardStats(int $userId, string $userType, ?int $ttl = 600): array
    {
        return $this->tags(['dashboard_stats', "user_{$userId}"])
            ->remember("dashboard_stats_{$userId}_{$userType}", function () use ($userId, $userType) {
                if ($userType === 'client') {
                    return [
                        'total_bookings' => \App\Models\Booking::where('client_id', $userId)->count(),
                        'active_bookings' => \App\Models\Booking::where('client_id', $userId)
                            ->whereIn('status', ['confirmed', 'in_progress'])->count(),
                        'total_spent' => \App\Models\Payment::where('user_id', $userId)
                            ->where('status', 'completed')->sum('amount'),
                    ];
                } elseif ($userType === 'caregiver') {
                    return [
                        'total_assignments' => \App\Models\BookingAssignment::where('caregiver_id', $userId)->count(),
                        'completed_assignments' => \App\Models\BookingAssignment::where('caregiver_id', $userId)
                            ->where('status', 'completed')->count(),
                        'total_earnings' => \App\Models\TimeTracking::where('caregiver_id', $userId)
                            ->where('payment_status', 'paid')->sum('total_payment'),
                    ];
                }
                
                return [];
            }, $ttl);
    }
    
    /**
     * Cache available caregivers
     */
    public function availableCaregivers(?int $ttl = 300): Collection
    {
        return $this->tags(['caregivers'])
            ->remember('available_caregivers', function () {
                return \App\Models\Caregiver::where('availability_status', 'available')
                    ->with(['user'])
                    ->get();
            }, $ttl);
    }
    
    /**
     * Invalidate cache by tags
     */
    public function invalidate(array $tags): bool
    {
        try {
            Cache::tags($tags)->flush();
            
            Log::info('Cache invalidated', ['tags' => $tags]);
            return true;
        } catch (\Exception $e) {
            Log::error('Cache invalidation failed', [
                'tags' => $tags,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Invalidate user-related caches
     */
    public function invalidateUser(int $userId): bool
    {
        return $this->invalidate([
            "user_{$userId}",
            'user_bookings',
            'dashboard_stats'
        ]);
    }
    
    /**
     * Invalidate caregiver-related caches
     */
    public function invalidateCaregiver(int $caregiverId): bool
    {
        return $this->invalidate([
            "caregiver_{$caregiverId}",
            'caregiver_assignments',
            'caregivers'
        ]);
    }
    
    /**
     * Invalidate booking-related caches
     */
    public function invalidateBooking(int $clientId): bool
    {
        return $this->invalidate([
            "user_{$clientId}",
            'user_bookings',
            'dashboard_stats'
        ]);
    }
    
    /**
     * Generate cache key with prefix
     */
    protected function generateKey(string $key): string
    {
        return 'query_cache_' . $key;
    }
    
    /**
     * Set default TTL
     */
    public function setDefaultTtl(int $seconds): self
    {
        $this->defaultTtl = $seconds;
        return $this;
    }
    
    /**
     * Clear all query cache
     */
    public function clearAll(): bool
    {
        try {
            Cache::flush();
            Log::info('All cache cleared');
            return true;
        } catch (\Exception $e) {
            Log::error('Cache clear all failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
