<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Trait for optimized database queries with caching and pagination.
 * 
 * Provides production-grade query optimization patterns:
 * - Automatic eager loading
 * - Cursor-based pagination for large datasets
 * - Query result caching
 * - Chunked processing for memory efficiency
 * - Select optimization to avoid N+1 issues
 */
trait HasEfficientQueries
{
    /**
     * Default cache TTL in seconds (5 minutes)
     */
    protected static int $defaultCacheTtl = 300;

    /**
     * Scope for efficient list queries with automatic select optimization.
     * Only selects necessary columns to reduce memory usage.
     */
    public function scopeOptimizedList(Builder $query, array $columns = ['*']): Builder
    {
        if ($columns === ['*']) {
            $columns = $this->getListColumns();
        }

        return $query->select($columns);
    }

    /**
     * Scope for search queries with proper indexing awareness.
     */
    public function scopeSearchOptimized(
        Builder $query,
        string $term,
        array $searchableColumns
    ): Builder {
        if (empty($term)) {
            return $query;
        }

        $term = '%' . strtolower($term) . '%';
        
        return $query->where(function (Builder $q) use ($term, $searchableColumns) {
            foreach ($searchableColumns as $column) {
                // Use LOWER for case-insensitive search (index-friendly on many DBs)
                $q->orWhereRaw('LOWER(' . $column . ') LIKE ?', [$term]);
            }
        });
    }

    /**
     * Scope for date range filtering with index optimization.
     */
    public function scopeDateRange(
        Builder $query,
        string $column,
        ?string $startDate,
        ?string $endDate
    ): Builder {
        if ($startDate) {
            $query->where($column, '>=', $startDate);
        }

        if ($endDate) {
            $query->where($column, '<=', $endDate . ' 23:59:59');
        }

        return $query;
    }

    /**
     * Scope for status filtering.
     */
    public function scopeWithStatus(Builder $query, string|array $status): Builder
    {
        if (is_array($status)) {
            return $query->whereIn($this->getStatusColumn(), $status);
        }

        return $query->where($this->getStatusColumn(), $status);
    }

    /**
     * Scope for efficient counting without loading full models.
     */
    public function scopeCountOnly(Builder $query): int
    {
        return $query->toBase()->count();
    }

    /**
     * Scope for exists check without loading data.
     */
    public function scopeExistsOptimized(Builder $query): bool
    {
        return $query->toBase()->exists();
    }

    /**
     * Get paginated results with consistent eager loading.
     */
    public static function paginateWith(
        array $relations,
        int $perPage = 15,
        array $columns = ['*'],
        ?string $orderBy = null,
        string $orderDirection = 'desc'
    ): LengthAwarePaginator {
        $query = static::query()->with($relations);

        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }

        return $query->paginate($perPage, $columns);
    }

    /**
     * Get cached results with automatic invalidation.
     */
    public static function getCached(
        string $key,
        callable $callback,
        ?int $ttl = null
    ): mixed {
        $ttl = $ttl ?? static::$defaultCacheTtl;
        $cacheKey = static::class . ':' . $key;

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Clear cache for this model.
     */
    public static function clearCache(string $key): bool
    {
        $cacheKey = static::class . ':' . $key;
        return Cache::forget($cacheKey);
    }

    /**
     * Process large datasets in chunks to prevent memory issues.
     */
    public static function processInChunks(
        callable $callback,
        int $chunkSize = 1000,
        ?Builder $query = null
    ): void {
        $query = $query ?? static::query();

        $query->orderBy('id')->chunk($chunkSize, function (Collection $items) use ($callback) {
            foreach ($items as $item) {
                $callback($item);
            }
        });
    }

    /**
     * Process large datasets using cursor for memory efficiency.
     */
    public static function processWithCursor(
        callable $callback,
        ?Builder $query = null
    ): void {
        $query = $query ?? static::query();

        foreach ($query->cursor() as $item) {
            $callback($item);
        }
    }

    /**
     * Efficient bulk update with proper chunking.
     */
    public static function bulkUpdate(
        array $updates,
        string $whereColumn,
        int $chunkSize = 500
    ): int {
        $totalUpdated = 0;
        $chunks = array_chunk($updates, $chunkSize, true);

        DB::beginTransaction();

        try {
            foreach ($chunks as $chunk) {
                foreach ($chunk as $whereValue => $updateData) {
                    $updated = static::query()
                        ->where($whereColumn, $whereValue)
                        ->update($updateData);
                    $totalUpdated += $updated;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $totalUpdated;
    }

    /**
     * Efficient bulk insert with proper chunking.
     */
    public static function bulkInsert(array $records, int $chunkSize = 500): bool
    {
        $chunks = array_chunk($records, $chunkSize);

        DB::beginTransaction();

        try {
            foreach ($chunks as $chunk) {
                static::insert($chunk);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get aggregated data efficiently.
     */
    public static function getAggregated(
        string $function,
        string $column,
        ?Builder $query = null
    ): mixed {
        $query = $query ?? static::query();

        return match (strtolower($function)) {
            'sum' => $query->sum($column),
            'avg' => $query->avg($column),
            'count' => $query->count($column),
            'min' => $query->min($column),
            'max' => $query->max($column),
            default => throw new \InvalidArgumentException("Unknown aggregation function: {$function}"),
        };
    }

    /**
     * Get distinct values efficiently.
     */
    public static function getDistinctValues(
        string $column,
        ?Builder $query = null
    ): Collection {
        $query = $query ?? static::query();

        return $query->distinct()->pluck($column);
    }

    /**
     * Override in model to specify columns for list views.
     */
    protected function getListColumns(): array
    {
        return ['*'];
    }

    /**
     * Override in model to specify status column name.
     */
    protected function getStatusColumn(): string
    {
        return 'status';
    }

    /**
     * Override in model to specify default eager load relations.
     */
    protected static function getDefaultEagerLoads(): array
    {
        return [];
    }

    /**
     * Boot method to apply global scopes for eager loading.
     */
    protected static function bootHasEfficientQueries(): void
    {
        static::addGlobalScope('defaultEagerLoads', function (Builder $builder) {
            $eagerLoads = static::getDefaultEagerLoads();
            if (!empty($eagerLoads)) {
                $builder->with($eagerLoads);
            }
        });
    }
}
