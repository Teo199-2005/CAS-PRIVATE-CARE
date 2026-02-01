<?php
/**
 * Check if an email exists in the users table.
 * Usage: php check_email.php <email>
 * Example: php check_email.php teofiloharry69trader@gmail.com
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$email = $argv[1] ?? null;
if (!$email) {
    echo "Usage: php check_email.php <email>\n";
    echo "Example: php check_email.php teofiloharry69trader@gmail.com\n";
    exit(1);
}

// Normalize: lowercase for comparison (DB may store case-sensitive)
$email = trim(strtolower($email));

$user = User::where('email', $email)->first();
if ($user) {
    echo "FOUND: This email is already registered.\n\n";
    echo "  ID:         {$user->id}\n";
    echo "  Email:      {$user->email}\n";
    echo "  Name:       {$user->name}\n";
    echo "  User type:  {$user->user_type}\n";
    echo "  Status:     " . ($user->status ?? 'null') . "\n";
    echo "  Created:    {$user->created_at}\n";
    echo "\n";
    echo "The person should use 'Login' (or 'Forgot password') instead of creating a new account.\n";
    echo "One email = one account; they cannot register again with the same email.\n";
    exit(0);
}

// Also check case-insensitive in case DB has different casing
$any = User::whereRaw('LOWER(email) = ?', [$email])->first();
if ($any) {
    echo "FOUND (different casing in DB): This email is already registered.\n\n";
    echo "  ID:         {$any->id}\n";
    echo "  Email:      {$any->email}\n";
    echo "  Name:       {$any->name}\n";
    echo "  User type:  {$any->user_type}\n";
    echo "  Status:    " . ($any->status ?? 'null') . "\n";
    echo "\n";
    echo "The person should use 'Login' instead of creating a new account.\n";
    exit(0);
}

echo "NOT FOUND: No user in the database with email: {$email}\n";
echo "If registration still says 'email already taken', check:\n";
echo "  - Validation may be running against a different DB or cache.\n";
echo "  - Try clearing config/cache: php artisan config:clear && php artisan cache:clear\n";
exit(0);
