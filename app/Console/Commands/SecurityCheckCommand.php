<?php

namespace App\Console\Commands;

use App\Services\ProductionSecurityService;
use Illuminate\Console\Command;

/**
 * Artisan command to run production security checks
 * 
 * This command should be run before deploying to production
 * to ensure all security configurations are properly set.
 * 
 * Usage:
 *   php artisan security:check
 *   php artisan security:check --strict  (fails on warnings too)
 * 
 * @package App\Console\Commands
 */
class SecurityCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:check 
                            {--strict : Fail on warnings as well as critical issues}
                            {--json : Output results as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run production security configuration checks';

    /**
     * Execute the console command.
     */
    public function handle(ProductionSecurityService $securityService): int
    {
        $results = $securityService->runAllChecks();
        $strict = $this->option('strict');
        $json = $this->option('json');

        if ($json) {
            $this->output->write(json_encode($results, JSON_PRETTY_PRINT));
            return $this->determineExitCode($results, $strict);
        }

        $this->output->write($securityService->formatResults($results));

        // Log results
        $securityService->logResults($results);

        return $this->determineExitCode($results, $strict);
    }

    /**
     * Determine the exit code based on results
     */
    protected function determineExitCode(array $results, bool $strict): int
    {
        if (!$results['passed']) {
            return Command::FAILURE;
        }

        if ($strict && !empty($results['warnings'])) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
