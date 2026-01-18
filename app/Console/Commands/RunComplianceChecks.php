<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ComplianceService;
use App\Models\User;
use App\Models\ComplianceCheck;
use Illuminate\Support\Facades\Log;

class RunComplianceChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compliance:check 
                            {--user= : Check specific user by ID}
                            {--type= : Check specific user type (caregiver, housekeeper, marketing, training_center)}
                            {--failed-only : Only show non-compliant contractors}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run compliance checks for contractors (W9, bank setup, certifications)';

    protected $complianceService;

    public function __construct(ComplianceService $complianceService)
    {
        parent::__construct();
        $this->complianceService = $complianceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $specificUserId = $this->option('user');
        $userType = $this->option('type');
        $failedOnly = $this->option('failed-only');
        
        $this->info('Running Compliance Checks');
        $this->info('Date: ' . now()->format('Y-m-d H:i:s'));
        $this->line('');
        
        // Get users to check
        $query = User::whereIn('user_type', ['caregiver', 'housekeeper', 'marketing', 'training_center'])
            ->where('status', 'Active');
        
        if ($specificUserId) {
            $query->where('id', $specificUserId);
        }
        
        if ($userType) {
            $query->where('user_type', $userType);
        }
        
        $users = $query->get();
        
        if ($users->isEmpty()) {
            $this->warn('No contractors found to check.');
            return Command::SUCCESS;
        }
        
        $this->info("Checking {$users->count()} contractors...");
        $this->line('');
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        $results = [];
        $compliant = 0;
        $nonCompliant = 0;
        
        foreach ($users as $user) {
            try {
                $checkResult = $this->complianceService->runComplianceCheck($user->id);
                
                if ($checkResult['overall_compliant']) {
                    $compliant++;
                    if (!$failedOnly) {
                        $results[] = [
                            'id' => $user->id,
                            'name' => $user->name,
                            'type' => $user->user_type,
                            'status' => '✓ Compliant',
                            'issues' => '-'
                        ];
                    }
                } else {
                    $nonCompliant++;
                    $issues = collect($checkResult['results'] ?? [])
                        ->filter(fn($item) => !($item['passed'] ?? false))
                        ->keys()
                        ->implode(', ');
                    
                    $results[] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'type' => $user->user_type,
                        'status' => '✗ Non-Compliant',
                        'issues' => $issues ?: 'Multiple issues'
                    ];
                }
            } catch (\Exception $e) {
                $results[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'type' => $user->user_type,
                    'status' => '⚠ Error',
                    'issues' => substr($e->getMessage(), 0, 50)
                ];
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->line('');
        $this->line('');
        
        // Display results table
        if (!empty($results)) {
            $this->table(
                ['ID', 'Name', 'Type', 'Status', 'Issues'],
                $results
            );
        }
        
        // Summary
        $this->line('');
        $this->info('=== Summary ===');
        $this->info("Total Checked: {$users->count()}");
        $this->info("Compliant: {$compliant}");
        
        if ($nonCompliant > 0) {
            $this->warn("Non-Compliant: {$nonCompliant}");
        } else {
            $this->info("Non-Compliant: {$nonCompliant}");
        }
        
        $complianceRate = $users->count() > 0 
            ? round(($compliant / $users->count()) * 100, 1) 
            : 0;
        $this->info("Compliance Rate: {$complianceRate}%");
        
        Log::info('Compliance check command completed', [
            'total_checked' => $users->count(),
            'compliant' => $compliant,
            'non_compliant' => $nonCompliant,
            'compliance_rate' => $complianceRate
        ]);
        
        return Command::SUCCESS;
    }
}
