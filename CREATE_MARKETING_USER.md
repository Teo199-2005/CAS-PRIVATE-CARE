# Create Marketing User Account

## Run this command to create the marketing user:

```bash
php artisan db:seed --class=DatabaseSeeder
```

**OR** if you already have data and don't want to reset everything:

```bash
php artisan tinker
```

Then paste this code:

```php
\App\Models\User::create([
    'name' => 'Marketing Staff',
    'email' => 'marketing@demo.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
    'user_type' => 'marketing',
    'email_verified_at' => now(),
]);
```

Press Ctrl+C to exit tinker.

## Verify the user was created:

```bash
php artisan tinker
```

```php
\App\Models\User::where('email', 'marketing@demo.com')->first();
```

You should see the marketing user details.

## Then try logging in again with:
- Email: marketing@demo.com
- Password: password123
