<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ScheduledPayoutService;
use Illuminate\Support\Facades\Log;

class ProcessScheduledPayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payouts:process 
                            {frequency? : The frequency to process (weekly, biweekly, monthly)}
                            {--dry-run : Run without actually processing payouts}
                            {--force : Force processing even if not scheduled day}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled contractor payouts based on frequency';

    protected $scheduledPayoutService;

    public function __construct(ScheduledPayoutService $scheduledPayoutService)
    {
        parent::__construct();
        $this->scheduledPayoutService = $scheduledPayoutService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $frequency = $this->argument('frequency');
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        $this->info('Processing scheduled payouts...');
        $this->info('Date: ' . now()->format('Y-m-d H:i:s'));
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No payouts will be processed');
        }
        
        $frequencies = $frequency ? [$frequency] : $this->getFrequenciesToProcess($force);
        
        if (empty($frequencies)) {
            $this->info('No frequencies scheduled for processing today.');
            return Command::SUCCESS;
        }
        
        $totalProcessed = 0;
        $totalAmount = 0;
        $totalFailed = 0;
        
        foreach ($frequencies as $freq) {
            $this->line('');
            $this->info("Processing {$freq} payouts...");
            
            if ($isDryRun) {
                $preview = $this->previewPayouts($freq);
                $this->table(
                    ['User ID', 'Name', 'Amount', 'Bank Connected'],
                    $preview
                );
                $totalProcessed += count($preview);
                $totalAmount += collect($preview)->sum(fn($p) => floatval(str_replace(['$', ','], '', $p['Amount'])));
            } else {
                $result = $this->scheduledPayoutService->processScheduledPayouts($freq);
                
                if ($result['success']) {
                    $this->info("✓ {$freq} payouts processed: {$result['processed']} contractors");
                    $totalProcessed += $result['processed'];
                    $totalAmount += $result['total_amount'];
                    $totalFailed += $result['failed'];
                    
                    if ($result['failed'] > 0) {
                        $this->warn("  Failed: {$result['failed']}");
                    }
                } else {
                    $this->error("✗ Failed to process {$freq} payouts: " . ($result['error'] ?? 'Unknown error'));
                }
            }
        }
        
        $this->line('');
        $this->info('=== Summary ===');
        $this->info("Total Processed: {$totalProcessed}");
        $this->info("Total Amount: $" . number_format($totalAmount, 2));
        if ($totalFailed > 0) {
            $this->warn("Total Failed: {$totalFailed}");
        }
        
        Log::info('Scheduled payouts command completed', [
            'frequencies' => $frequencies,
            'total_processed' => $totalProcessed,
            'total_amount' => $totalAmount,
            'total_failed' => $totalFailed,
            'is_dry_run' => $isDryRun
        ]);
        
        return Command::SUCCESS;
    }
    
    /**
     * Get frequencies that should be processed today
     */
    protected function getFrequenciesToProcess(bool $force): array
    {
        $dayOfWeek = now()->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
        $dayOfMonth = now()->day;
        $weekOfYear = now()->weekOfYear;
        
        $frequencies = [];
        
        // Weekly payouts: Every Friday (dayOfWeek = 5)
        if ($force || $dayOfWeek === 5) {
            $frequencies[] = 'weekly';
        }
        
        // Biweekly payouts: Every other Friday
        if ($force || ($dayOfWeek === 5 && $weekOfYear % 2 === 0)) {
            $frequencies[] = 'biweekly';
        }
        
        // Monthly payouts: Last day of month or 1st of month
        if ($force || $dayOfMonth === 1 || now()->isLastOfMonth()) {
            $frequencies[] = 'monthly';
        }
        
        return array_unique($frequencies);
    }
    
    /**
     * Preview payouts without processing
     */
    protected function previewPayouts(string $frequency): array
    {
        $contractors = \App\Models\User::whereIn('user_type', ['caregiver', 'housekeeper', 'marketing', 'training_center'])
            ->where('status', 'Active')
            ->where(function ($query) use ($frequency) {
                $query->where('payout_frequency', $frequency)
                      ->orWhereNull('payout_frequency');
            })
            ->get();
        
        $preview = [];
        
        foreach ($contractors as $contractor) {
            $pendingEarnings = $this->getPendingEarnings($contractor);
            
            if ($pendingEarnings > 0) {
                $preview[] = [
                    'User ID' => $contractor->id,
                    'Name' => $contractor->name,
                    'Amount' => '$' . number_format($pendingEarnings, 2),
                    'Bank Connected' => !empty($contractor->stripe_connect_id) ? 'Yes' : 'No'
                ];
            }
        }
        
        return $preview;
    }
    
    /**
     * Get pending earnings for a contractor
     */
    protected function getPendingEarnings(\App\Models\User $user): float
    {
        if ($user->user_type === 'caregiver') {
            $caregiver = $user->caregiver;
            if ($caregiver) {
                return (float) \App\Models\TimeTracking::where('caregiver_id', $caregiver->id)
                    ->whereNull('paid_at')
                    ->where('status', 'completed')
                    ->sum('caregiver_earnings');
            }
        } elseif ($user->user_type === 'housekeeper') {
            $housekeeper = $user->housekeeper;
            if ($housekeeper) {
                return (float) \App\Models\TimeTracking::where('housekeeper_id', $housekeeper->id)
                    ->whereNull('paid_at')
                    ->where('status', 'completed')
                    ->sum('caregiver_earnings');
            }
        } elseif ($user->user_type === 'marketing') {
            return (float) \App\Models\TimeTracking::where('marketing_partner_id', $user->id)
                ->whereNull('paid_at')
                ->where('status', 'completed')
                ->sum('marketing_partner_commission');
        } elseif (in_array($user->user_type, ['training', 'training_center'])) {
            return (float) \App\Models\TimeTracking::where('training_center_user_id', $user->id)
                ->whereNull('paid_at')
                ->where('status', 'completed')
                ->sum('training_center_commission');
        }
        
        return 0;
    }
}
