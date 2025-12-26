<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Caregiver;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $caregiver = Caregiver::first();
        
        if ($caregiver) {
            Transaction::create([
                'caregiver_id' => $caregiver->id,
                'type' => 'payment',
                'description' => 'Service - John Doe (Elderly Care)',
                'amount' => 120.00,
                'status' => 'completed',
                'method' => 'Bank Transfer',
                'created_at' => now()->subDays(2)
            ]);

            Transaction::create([
                'caregiver_id' => $caregiver->id,
                'type' => 'payment',
                'description' => 'Service - Sarah Williams (Personal Care)',
                'amount' => 95.00,
                'status' => 'completed',
                'method' => 'Bank Transfer',
                'created_at' => now()->subDays(3)
            ]);

            Transaction::create([
                'caregiver_id' => $caregiver->id,
                'type' => 'payout',
                'description' => 'Weekly Payout to Bank Account',
                'amount' => 800.00,
                'status' => 'completed',
                'method' => 'Bank Transfer',
                'created_at' => now()->subDays(4)
            ]);

            Transaction::create([
                'caregiver_id' => $caregiver->id,
                'type' => 'payment',
                'description' => 'Service - Robert Chen (Elderly Care)',
                'amount' => 120.00,
                'status' => 'pending',
                'method' => 'Bank Transfer',
                'created_at' => now()->subDays(1)
            ]);

            Transaction::create([
                'caregiver_id' => $caregiver->id,
                'type' => 'bonus',
                'description' => 'Performance Bonus - December',
                'amount' => 200.00,
                'status' => 'completed',
                'method' => 'Bank Transfer',
                'created_at' => now()->subDays(5)
            ]);
        }
    }
}
