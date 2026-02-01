<?php

namespace App\Console\Commands;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Console\Command;

class SetMarketingReferralCode extends Command
{
    protected $signature = 'referral:set-code {email : Marketing partner email} {code : Referral code (e.g. HARRYPOGIG0553)}';
    protected $description = 'Set or update a marketing partner\'s referral code so clients can use it when booking';

    public function handle()
    {
        $email = $this->argument('email');
        $code = strtoupper(trim($this->argument('code')));

        if (strlen($code) < 4 || strlen($code) > 20) {
            $this->error('Code must be 4–20 characters.');
            return 1;
        }

        $user = User::where('email', $email)->where('user_type', 'marketing')->first();
        if (!$user) {
            $this->error("No marketing partner found with email: {$email}");
            return 1;
        }

        $existingOther = ReferralCode::where('code', $code)->where('user_id', '!=', $user->id)->first();
        if ($existingOther) {
            $this->error("Referral code \"{$code}\" is already used by another marketing partner.");
            return 1;
        }

        $referralCode = ReferralCode::where('user_id', $user->id)->first();
        if ($referralCode) {
            $referralCode->update(['code' => $code, 'is_active' => true]);
            $this->info("Updated referral code for {$user->name} ({$email}) to: {$code}");
        } else {
            ReferralCode::create([
                'user_id' => $user->id,
                'code' => $code,
                'discount_per_hour' => 5.00,
                'commission_per_hour' => 1.00,
                'is_active' => true,
            ]);
            $this->info("Created referral code for {$user->name} ({$email}): {$code}");
        }

        $this->info('Clients can now use this code when booking.');
        return 0;
    }
}
