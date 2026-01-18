<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Form1099Service;
use App\Models\TaxForm1099;
use Illuminate\Support\Facades\Log;

class Generate1099Forms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tax:generate-1099 
                            {year? : The tax year to generate 1099s for}
                            {--preview : Only preview, do not generate}
                            {--email : Send email notifications after generation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate 1099-NEC forms for contractors who earned $600 or more';

    protected $form1099Service;

    public function __construct(Form1099Service $form1099Service)
    {
        parent::__construct();
        $this->form1099Service = $form1099Service;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?? (date('Y') - 1);
        $isPreview = $this->option('preview');
        $sendEmail = $this->option('email');
        
        $this->info("1099-NEC Form Generation");
        $this->info("Tax Year: {$year}");
        $this->info("Processing Date: " . now()->format('Y-m-d H:i:s'));
        $this->line('');
        
        // Get contractors requiring 1099
        $contractors = $this->form1099Service->getContractorsRequiring1099($year);
        
        if (empty($contractors)) {
            $this->info("No contractors require 1099 forms for tax year {$year}");
            return Command::SUCCESS;
        }
        
        $this->info("Found " . count($contractors) . " contractors requiring 1099 forms");
        $this->line('');
        
        // Display preview
        $tableData = collect($contractors)->map(function ($c) {
            return [
                'ID' => $c['user_id'],
                'Name' => $c['name'],
                'Type' => $c['user_type'],
                'Total Earnings' => '$' . number_format($c['total_earnings'], 2),
                'W9 Status' => $c['w9_verified'] ? '✓ Verified' : ($c['w9_submitted'] ? 'Submitted' : '✗ Missing'),
                'TIN' => !empty($c['has_tin']) ? 'Yes' : 'No'
            ];
        })->toArray();
        
        $this->table(
            ['ID', 'Name', 'Type', 'Total Earnings', 'W9 Status', 'TIN'],
            $tableData
        );
        
        $totalAmount = collect($contractors)->sum('total_earnings');
        $this->line('');
        $this->info("Total Compensation: $" . number_format($totalAmount, 2));
        
        if ($isPreview) {
            $this->line('');
            $this->warn('PREVIEW MODE - No forms generated');
            return Command::SUCCESS;
        }
        
        // Confirmation
        if (!$this->confirm('Do you want to generate 1099 forms for these contractors?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }
        
        $this->line('');
        $this->info('Generating 1099 forms...');
        
        $bar = $this->output->createProgressBar(count($contractors));
        $bar->start();
        
        $generated = 0;
        $updated = 0;
        $errors = [];
        
        foreach ($contractors as $contractor) {
            try {
                // Check if form already exists
                $existingForm = TaxForm1099::where('user_id', $contractor['user_id'])
                    ->where('tax_year', $year)
                    ->where('is_corrected', false)
                    ->first();
                
                if ($existingForm) {
                    // Update existing form
                    $existingForm->update([
                        'total_compensation' => $contractor['total_earnings'],
                        'recipient_name' => $contractor['name'],
                        'generated_at' => now()
                    ]);
                    $updated++;
                } else {
                    // Create new form
                    $user = \App\Models\User::find($contractor['user_id']);
                    
                    TaxForm1099::create([
                        'user_id' => $contractor['user_id'],
                        'tax_year' => $year,
                        'form_type' => '1099-NEC',
                        'total_compensation' => $contractor['total_earnings'],
                        'federal_income_tax_withheld' => 0,
                        'state_income' => $contractor['total_earnings'],
                        'state_tax_withheld' => 0,
                        'state_code' => $user->state ?? null,
                        'recipient_name' => $contractor['name'],
                        'recipient_address' => $this->formatAddress($user),
                        'tin' => $user->ssn ?? $user->itin ?? null,
                        'tin_type' => $user->ssn ? 'SSN' : ($user->itin ? 'ITIN' : null),
                        'status' => 'draft',
                        'generated_at' => now()
                    ]);
                    $generated++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'user_id' => $contractor['user_id'],
                    'name' => $contractor['name'],
                    'error' => $e->getMessage()
                ];
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->line('');
        $this->line('');
        
        // Summary
        $this->info('=== Generation Complete ===');
        $this->info("Generated: {$generated} new forms");
        $this->info("Updated: {$updated} existing forms");
        
        if (!empty($errors)) {
            $this->error("Errors: " . count($errors));
            $this->table(['User ID', 'Name', 'Error'], collect($errors)->map(function ($e) {
                return [
                    'User ID' => $e['user_id'],
                    'Name' => $e['name'],
                    'Error' => substr($e['error'], 0, 50)
                ];
            })->toArray());
        }
        
        // Send email notifications if requested
        if ($sendEmail && ($generated > 0 || $updated > 0)) {
            $this->line('');
            $this->info('Sending email notifications...');
            
            // In production: Send emails to contractors notifying them about 1099 availability
            $this->warn('Email notifications are not yet implemented');
        }
        
        Log::info('1099 form generation completed', [
            'year' => $year,
            'generated' => $generated,
            'updated' => $updated,
            'errors' => count($errors),
            'total_amount' => $totalAmount
        ]);
        
        return Command::SUCCESS;
    }
    
    /**
     * Format address for 1099 form
     */
    protected function formatAddress(\App\Models\User $user): string
    {
        $parts = array_filter([
            $user->address ?? '',
            $user->city ?? '',
            $user->state ?? '',
            $user->zip ?? ''
        ]);
        
        return implode(', ', $parts);
    }
}
