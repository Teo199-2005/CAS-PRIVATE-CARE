<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the Demo Caregiver (ID 2)
        $caregiver = User::where('email', 'caregiver@demo.com')->first();
        if ($caregiver) {
            // Insert cards first (cards only)
            DB::table('payment_methods')->insert([
                [
                    'user_id' => $caregiver->id,
                    'type' => 'card',
                    'card_type' => 'visa',
                    'last_four' => '4532',
                    'card_holder_name' => 'Maria Santos',
                    'expiry_month' => '12',
                    'expiry_year' => '2025',
                    'is_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $caregiver->id,
                    'type' => 'card',
                    'card_type' => 'mastercard',
                    'last_four' => '8765',
                    'card_holder_name' => 'Maria Santos',
                    'expiry_month' => '08',
                    'expiry_year' => '2026',
                    'is_default' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Insert bank account separately (bank account only)
            DB::table('payment_methods')->insert([
                'user_id' => $caregiver->id,
                'type' => 'bank_account',
                'bank_name' => 'Chase Bank',
                'account_type' => 'Checking Account',
                'account_last_four' => '1234',
                'routing_last_four' => '5678',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert payment settings
            DB::table('payment_settings')->insert([
                'user_id' => $caregiver->id,
                'payout_frequency' => 'Weekly',
                'payout_method' => 'Bank Transfer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Get Marketing Staff
        $marketing = User::where('role', 'marketing')->first();
        if ($marketing) {
            // Insert cards first
            DB::table('payment_methods')->insert([
                [
                    'user_id' => $marketing->id,
                    'type' => 'card',
                    'card_type' => 'visa',
                    'last_four' => '4242',
                    'card_holder_name' => 'Marketing Staff',
                    'expiry_month' => '12',
                    'expiry_year' => '2025',
                    'is_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $marketing->id,
                    'type' => 'card',
                    'card_type' => 'mastercard',
                    'last_four' => '8888',
                    'card_holder_name' => 'Marketing Staff',
                    'expiry_month' => '06',
                    'expiry_year' => '2026',
                    'is_default' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Insert bank account separately
            DB::table('payment_methods')->insert([
                'user_id' => $marketing->id,
                'type' => 'bank_account',
                'bank_name' => 'Bank of America',
                'account_type' => 'Savings Account',
                'account_last_four' => '9876',
                'routing_last_four' => '4321',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert payment settings
            DB::table('payment_settings')->insert([
                'user_id' => $marketing->id,
                'payout_frequency' => 'Weekly',
                'payout_method' => 'Bank Transfer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Get Training Center Staff
        $training = User::where('role', 'training')->first();
        if ($training) {
            // Insert cards first
            DB::table('payment_methods')->insert([
                [
                    'user_id' => $training->id,
                    'type' => 'card',
                    'card_type' => 'visa',
                    'last_four' => '4242',
                    'card_holder_name' => 'Training Center',
                    'expiry_month' => '12',
                    'expiry_year' => '2025',
                    'is_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $training->id,
                    'type' => 'card',
                    'card_type' => 'mastercard',
                    'last_four' => '8888',
                    'card_holder_name' => 'Training Center',
                    'expiry_month' => '06',
                    'expiry_year' => '2026',
                    'is_default' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Insert bank account separately
            DB::table('payment_methods')->insert([
                'user_id' => $training->id,
                'type' => 'bank_account',
                'bank_name' => 'Wells Fargo',
                'account_type' => 'Business Checking',
                'account_last_four' => '5432',
                'routing_last_four' => '8765',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert payment settings
            DB::table('payment_settings')->insert([
                'user_id' => $training->id,
                'payout_frequency' => 'Weekly',
                'payout_method' => 'Bank Transfer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}