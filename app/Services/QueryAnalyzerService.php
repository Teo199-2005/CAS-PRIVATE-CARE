<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;

/**
 * Database Query Analyzer Service
 * 
 * Monitors database queries for performance issues
 * and provides optimization recommendations.
 */
class QueryAnalyzerService
{
    /**
     * Slow query threshold in milliseconds
     */
    protected int $slowQueryThreshold = 100;

    /**
     * Duplicate query threshold
     */
    protected int $duplicateThreshold = 3;

    /**
     * Collected queries during request
     */
    protected array $queries = [];

    /**
     * Whether logging is enabled
     */
    protected bool $enabled = true;

    /**
     * Start monitoring queries
     */
    public function start(): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->queries = [];

        DB::listen(function (QueryExecuted $query) {
            $this->recordQuery($query);
        });
    }

    /**
     * Record a query
     */
    protected function recordQuery(QueryExecuted $query): void
    {
        $sql = $this->interpolateQuery($query->sql, $query->bindings);
        $hash = md5($query->sql);

        $this->queries[] = [
            'sql' => $sql,
            'raw_sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time,
            'hash' => $hash,
            'connection' => $query->connectionName,
        ];

        // Log slow queries immediately
        if ($query->time > $this->slowQueryThreshold) {
            Log::warning('Slow query detected', [
                'sql' => $sql,
                'time_ms' => $query->time,
                'threshold_ms' => $this->slowQueryThreshold,
            ]);
        }
    }

    /**
     * Interpolate query bindings into SQL
     */
    protected function interpolateQuery(string $sql, array $bindings): string
    {
        foreach ($bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . addslashes($binding) . "'";
            $sql = preg_replace('/\?/', (string) $value, $sql, 1);
        }
        return $sql;
    }

    /**
     * Get query analysis report
     */
    public function getReport(): array
    {
        $totalQueries = count($this->queries);
        $totalTime = array_sum(array_column($this->queries, 'time'));
        $slowQueries = array_filter($this->queries, fn($q) => $q['time'] > $this->slowQueryThreshold);
        $duplicates = $this->findDuplicates();

        return [
            'summary' => [
                'total_queries' => $totalQueries,
                'total_time_ms' => round($totalTime, 2),
                'average_time_ms' => $totalQueries > 0 ? round($totalTime / $totalQueries, 2) : 0,
                'slow_queries_count' => count($slowQueries),
                'duplicate_query_groups' => count($duplicates),
            ],
            'slow_queries' => array_map(fn($q) => [
                'sql' => $q['sql'],
                'time_ms' => $q['time'],
            ], $slowQueries),
            'duplicates' => $duplicates,
            'recommendations' => $this->generateRecommendations($slowQueries, $duplicates),
        ];
    }

    /**
     * Find duplicate queries
     */
    protected function findDuplicates(): array
    {
        $grouped = [];
        
        foreach ($this->queries as $query) {
            $hash = $query['hash'];
            if (!isset($grouped[$hash])) {
                $grouped[$hash] = [
                    'sql' => $query['raw_sql'],
                    'count' => 0,
                    'total_time_ms' => 0,
                ];
            }
            $grouped[$hash]['count']++;
            $grouped[$hash]['total_time_ms'] += $query['time'];
        }

        // Filter to only duplicates above threshold
        $duplicates = array_filter($grouped, fn($g) => $g['count'] >= $this->duplicateThreshold);

        return array_values($duplicates);
    }

    /**
     * Generate optimization recommendations
     */
    protected function generateRecommendations(array $slowQueries, array $duplicates): array
    {
        $recommendations = [];

        // N+1 query detection
        if (count($duplicates) > 0) {
            foreach ($duplicates as $dup) {
                $sql = strtolower($dup['sql']);
                if (str_contains($sql, 'select') && str_contains($sql, 'where')) {
                    $recommendations[] = [
                        'type' => 'n_plus_one',
                        'severity' => 'high',
                        'message' => "Potential N+1 query detected (executed {$dup['count']} times). Consider using eager loading with ->with().",
                        'sql' => $dup['sql'],
                    ];
                }
            }
        }

        // Missing index detection
        foreach ($slowQueries as $query) {
            $sql = strtolower($query['sql']);
            
            // Look for table scans
            if (str_contains($sql, 'select') && !str_contains($sql, 'limit') && $query['time'] > 200) {
                $recommendations[] = [
                    'type' => 'missing_index',
                    'severity' => 'medium',
                    'message' => 'Slow query without LIMIT - consider adding an index or pagination.',
                    'sql' => $query['sql'],
                ];
            }
            
            // LIKE with leading wildcard
            if (preg_match('/like\s+[\'"]%/', $sql)) {
                $recommendations[] = [
                    'type' => 'inefficient_like',
                    'severity' => 'medium',
                    'message' => 'LIKE with leading wildcard prevents index usage. Consider full-text search.',
                    'sql' => $query['sql'],
                ];
            }
        }

        // Too many queries
        if (count($this->queries) > 50) {
            $recommendations[] = [
                'type' => 'too_many_queries',
                'severity' => 'medium',
                'message' => "High query count ({$this->queries['count']}). Consider consolidating queries or using caching.",
            ];
        }

        return $recommendations;
    }

    /**
     * Get all recorded queries
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * Clear recorded queries
     */
    public function clear(): void
    {
        $this->queries = [];
    }

    /**
     * Set slow query threshold
     */
    public function setSlowQueryThreshold(int $ms): self
    {
        $this->slowQueryThreshold = $ms;
        return $this;
    }

    /**
     * Set duplicate query threshold
     */
    public function setDuplicateThreshold(int $count): self
    {
        $this->duplicateThreshold = $count;
        return $this;
    }

    /**
     * Enable/disable logging
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Log report at end of request
     */
    public function logReport(): void
    {
        $report = $this->getReport();

        if ($report['summary']['slow_queries_count'] > 0 || count($report['duplicates']) > 0) {
            Log::info('Query Analysis Report', $report);
        }
    }

    /**
     * Get statistics as simple array (for API/debugging)
     */
    public function getStats(): array
    {
        return [
            'query_count' => count($this->queries),
            'total_time_ms' => round(array_sum(array_column($this->queries, 'time')), 2),
            'slow_query_threshold_ms' => $this->slowQueryThreshold,
            'enabled' => $this->enabled,
        ];
    }
}
