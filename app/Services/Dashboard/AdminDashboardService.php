<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\Booking;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Services\QueryCacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

/**
 * Admin Dashboard Service
 * 
 * Centralized service for admin dashboard data retrieval.
 * Implements eager loading, caching, and query optimization.
 */
class AdminDashboardService
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    protected const CACHE_TTL = 300;

    public function __construct(
        protected QueryCacheService $cache
    ) {}

    /**
     * Get dashboard statistics.
     * 
     * @return array<string, int>
     */
    public function getStats(): array
    {
        return Cache::remember('admin_dashboard_stats', self::CACHE_TTL, function () {
            return [
                'total_caregivers' => Caregiver::count(),
                'total_housekeepers' => Housekeeper::count(),
                'total_clients' => User::where('user_type', 'client')->count(),
                'pending_caregivers' => Caregiver::whereHas('user', fn($q) => $q->where('status', 'pending'))->count(),
                'pending_housekeepers' => Housekeeper::whereHas('user', fn($q) => $q->where('status', 'pending'))->count(),
                'pending_bookings' => Booking::where('status', 'pending')->count(),
                'active_bookings' => Booking::whereIn('status', ['confirmed', 'in_progress'])->count(),
                'completed_bookings' => Booking::where('status', 'completed')->count(),
                'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            ];
        });
    }

    /**
     * Get revenue statistics.
     * 
     * @return array<string, float>
     */
    public function getRevenueStats(): array
    {
        return Cache::remember('admin_revenue_stats', self::CACHE_TTL, function () {
            $today = now()->startOfDay();
            $thisMonth = now()->startOfMonth();
            $lastMonth = now()->subMonth()->startOfMonth();

            return [
                'today_revenue' => Booking::where('status', 'completed')
                    ->where('created_at', '>=', $today)
                    ->sum('total_budget'),
                'this_month_revenue' => Booking::where('status', 'completed')
                    ->where('created_at', '>=', $thisMonth)
                    ->sum('total_budget'),
                'last_month_revenue' => Booking::where('status', 'completed')
                    ->whereBetween('created_at', [$lastMonth, $thisMonth])
                    ->sum('total_budget'),
            ];
        });
    }

    /**
     * Get caregivers with optimized eager loading.
     * 
     * @param int $limit
     * @return Collection
     */
    public function getCaregivers(int $limit = 50): Collection
    {
        return Caregiver::with([
                'user:id,name,email,avatar,status,phone,created_at'
            ])
            ->select([
                'id', 
                'user_id', 
                'availability_status', 
                'hourly_rate', 
                'rating',
                'experience_years',
                'created_at'
            ])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get housekeepers with optimized eager loading.
     * 
     * @param int $limit
     * @return Collection
     */
    public function getHousekeepers(int $limit = 50): Collection
    {
        return Housekeeper::with([
                'user:id,name,email,avatar,status,phone,created_at'
            ])
            ->select([
                'id', 
                'user_id', 
                'availability_status', 
                'hourly_rate', 
                'rating',
                'created_at'
            ])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get clients with optimized eager loading.
     * 
     * @param int $limit
     * @return Collection
     */
    public function getClients(int $limit = 50): Collection
    {
        return User::where('user_type', 'client')
            ->select([
                'id', 
                'name', 
                'email', 
                'avatar', 
                'status', 
                'phone',
                'created_at'
            ])
            ->withCount('bookings')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent bookings with optimized eager loading.
     * 
     * @param int $limit
     * @return Collection
     */
    public function getRecentBookings(int $limit = 20): Collection
    {
        return Booking::with([
                'client:id,name,email',
                'assignments.caregiver.user:id,name',
                'assignments.housekeeper.user:id,name',
            ])
            ->select([
                'id', 
                'client_id', 
                'status', 
                'service_type',
                'duty_type',
                'service_date', 
                'total_budget',
                'created_at'
            ])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get pending applications (caregivers + housekeepers).
     * 
     * @return array
     */
    public function getPendingApplications(): array
    {
        $pendingCaregivers = Caregiver::with('user:id,name,email,created_at')
            ->whereHas('user', fn($q) => $q->where('status', 'pending'))
            ->select(['id', 'user_id', 'hourly_rate', 'experience_years'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $pendingHousekeepers = Housekeeper::with('user:id,name,email,created_at')
            ->whereHas('user', fn($q) => $q->where('status', 'pending'))
            ->select(['id', 'user_id', 'hourly_rate'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'caregivers' => $pendingCaregivers,
            'housekeepers' => $pendingHousekeepers,
        ];
    }

    /**
     * Invalidate dashboard caches.
     * 
     * Call this when relevant data changes.
     */
    public function invalidateCache(): void
    {
        Cache::forget('admin_dashboard_stats');
        Cache::forget('admin_revenue_stats');
    }
}
