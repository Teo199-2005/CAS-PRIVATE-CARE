<?php

namespace Tests\Unit\Services;

use App\Services\QueryAnalyzerService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueryAnalyzerServiceTest extends TestCase
{
    protected QueryAnalyzerService $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new QueryAnalyzerService();
    }

    /**
     * Test analyzer can be started
     */
    public function test_analyzer_can_start(): void
    {
        $this->analyzer->start();
        $this->assertIsArray($this->analyzer->getQueries());
        $this->assertCount(0, $this->analyzer->getQueries());
    }

    /**
     * Test stats returns correct structure
     */
    public function test_stats_structure(): void
    {
        $stats = $this->analyzer->getStats();

        $this->assertArrayHasKey('query_count', $stats);
        $this->assertArrayHasKey('total_time_ms', $stats);
        $this->assertArrayHasKey('slow_query_threshold_ms', $stats);
        $this->assertArrayHasKey('enabled', $stats);
    }

    /**
     * Test set slow query threshold
     */
    public function test_set_slow_query_threshold(): void
    {
        $result = $this->analyzer->setSlowQueryThreshold(200);
        
        $this->assertInstanceOf(QueryAnalyzerService::class, $result);
        
        $stats = $this->analyzer->getStats();
        $this->assertEquals(200, $stats['slow_query_threshold_ms']);
    }

    /**
     * Test set duplicate threshold
     */
    public function test_set_duplicate_threshold(): void
    {
        $result = $this->analyzer->setDuplicateThreshold(5);
        $this->assertInstanceOf(QueryAnalyzerService::class, $result);
    }

    /**
     * Test enable/disable
     */
    public function test_enable_disable(): void
    {
        $this->analyzer->setEnabled(false);
        $stats = $this->analyzer->getStats();
        $this->assertFalse($stats['enabled']);

        $this->analyzer->setEnabled(true);
        $stats = $this->analyzer->getStats();
        $this->assertTrue($stats['enabled']);
    }

    /**
     * Test clear queries
     */
    public function test_clear_queries(): void
    {
        $this->analyzer->clear();
        $this->assertCount(0, $this->analyzer->getQueries());
    }

    /**
     * Test report structure
     */
    public function test_report_structure(): void
    {
        $report = $this->analyzer->getReport();

        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('slow_queries', $report);
        $this->assertArrayHasKey('duplicates', $report);
        $this->assertArrayHasKey('recommendations', $report);

        $this->assertArrayHasKey('total_queries', $report['summary']);
        $this->assertArrayHasKey('total_time_ms', $report['summary']);
        $this->assertArrayHasKey('average_time_ms', $report['summary']);
        $this->assertArrayHasKey('slow_queries_count', $report['summary']);
        $this->assertArrayHasKey('duplicate_query_groups', $report['summary']);
    }

    /**
     * Test queries are recorded when enabled
     */
    public function test_queries_recorded_when_enabled(): void
    {
        $this->analyzer->setEnabled(true);
        $this->analyzer->start();

        // Execute a query
        DB::table('users')->limit(1)->get();

        $queries = $this->analyzer->getQueries();
        $this->assertGreaterThanOrEqual(1, count($queries));

        // Check query structure
        if (count($queries) > 0) {
            $this->assertArrayHasKey('sql', $queries[0]);
            $this->assertArrayHasKey('time', $queries[0]);
            $this->assertArrayHasKey('hash', $queries[0]);
        }
    }

    /**
     * Test empty report for no queries
     */
    public function test_empty_report(): void
    {
        $this->analyzer->clear();
        $report = $this->analyzer->getReport();

        $this->assertEquals(0, $report['summary']['total_queries']);
        $this->assertEquals(0, $report['summary']['total_time_ms']);
        $this->assertCount(0, $report['slow_queries']);
        $this->assertCount(0, $report['duplicates']);
    }
}
