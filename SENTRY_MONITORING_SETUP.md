# Sentry Monitoring Setup for CAS Private Care

## Installation

```bash
composer require sentry/sentry-laravel
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

## Configuration

### 1. Add to `.env`

```env
SENTRY_LARAVEL_DSN=https://YOUR_DSN@sentry.io/YOUR_PROJECT_ID
SENTRY_TRACES_SAMPLE_RATE=0.2
SENTRY_ENVIRONMENT=production
```

### 2. Configure `config/sentry.php`

```php
<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    
    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),
    
    'release' => env('SENTRY_RELEASE'),
    
    'sample_rate' => (float) env('SENTRY_SAMPLE_RATE', 1.0),
    
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),
    
    'breadcrumbs' => [
        'logs' => true,
        'cache' => true,
        'livewire' => true,
        'sql_queries' => true,
        'sql_bindings' => true,
        'queue_info' => true,
        'command_info' => true,
    ],
    
    'send_default_pii' => false,
    
    'traces_sampler' => function (\Sentry\Tracing\SamplingContext $context): float {
        // Sample critical paths more frequently
        $path = $context->getTransactionContext()->getName();
        
        if (str_contains($path, '/api/payments')) {
            return 1.0; // 100% of payment requests
        }
        
        if (str_contains($path, '/api/bookings')) {
            return 0.5; // 50% of booking requests
        }
        
        if (str_contains($path, '/admin')) {
            return 0.3; // 30% of admin requests
        }
        
        return 0.1; // 10% of other requests
    },
    
    'integrations' => [
        'breadcrumbs' => true,
        'transaction' => true,
    ],
    
    'ignore_exceptions' => [
        Illuminate\Auth\AuthenticationException::class,
        Illuminate\Validation\ValidationException::class,
    ],
    
    'before_send' => function (\Sentry\Event $event): ?\Sentry\Event {
        // Filter sensitive data
        if ($event->getRequest()) {
            $request = $event->getRequest();
            
            // Remove sensitive headers
            $headers = $request->getHeaders();
            unset($headers['Authorization'], $headers['Cookie']);
            $request->setHeaders($headers);
            
            // Remove sensitive data from request body
            $data = $request->getData();
            if (isset($data['password'])) {
                $data['password'] = '[FILTERED]';
            }
            if (isset($data['ssn'])) {
                $data['ssn'] = '[FILTERED]';
            }
            if (isset($data['stripe_token'])) {
                $data['stripe_token'] = '[FILTERED]';
            }
            $request->setData($data);
        }
        
        return $event;
    },
];
```

## Usage Examples

### 1. Manual Error Tracking

```php
use Sentry\Laravel\Facades\Sentry;

// Capture exception
try {
    // risky code
} catch (\Exception $e) {
    Sentry::captureException($e);
}

// Capture message
Sentry::captureMessage('Something went wrong', \Sentry\Severity::warning());

// Add breadcrumbs
Sentry::addBreadcrumb(new \Sentry\Breadcrumb(
    \Sentry\Breadcrumb::LEVEL_INFO,
    \Sentry\Breadcrumb::TYPE_DEFAULT,
    'payment',
    'Payment initiated',
    ['amount' => 100, 'user_id' => 123]
));
```

### 2. User Context

```php
// In AuthController after login
Sentry::configureScope(function (\Sentry\State\Scope $scope) use ($user) {
    $scope->setUser([
        'id' => $user->id,
        'email' => $user->email,
        'username' => $user->name,
        'user_type' => $user->user_type,
    ]);
});
```

### 3. Performance Monitoring

```php
// Track slow database queries
use Sentry\Tracing\Transaction;
use Sentry\Tracing\SpanContext;

$transaction = \Sentry\startTransaction(
    new \Sentry\Tracing\TransactionContext('booking.create')
);

\Sentry\SentrySdk::getCurrentHub()->setSpan($transaction);

// Your code here
$booking = Booking::create($data);

$transaction->finish();
```

### 4. Custom Tags

```php
Sentry::configureScope(function (\Sentry\State\Scope $scope) {
    $scope->setTag('payment_method', 'stripe');
    $scope->setTag('service_type', 'companion_care');
    $scope->setContext('booking', [
        'booking_id' => 123,
        'client_id' => 456,
    ]);
});
```

## Alert Configuration

### Critical Errors
- Payment processing failures
- Stripe webhook failures
- Database connection errors
- Authentication bypass attempts

### Performance Alerts
- API response time > 2s
- Database query time > 1s
- High memory usage > 80%

### Business Metrics
- Booking creation failures
- Payment success rate < 95%
- User registration failures

## Best Practices

1. **Don't log PII**: Filter passwords, SSNs, credit cards
2. **Use breadcrumbs**: Track user journey before error
3. **Set user context**: Easier to reproduce issues
4. **Tag by feature**: Organize errors by domain
5. **Monitor performance**: Track slow transactions
6. **Alert on trends**: Not just individual errors
7. **Review regularly**: Weekly error triage meetings

## Environment Setup

### Development
```env
SENTRY_LARAVEL_DSN=  # Leave empty in dev
SENTRY_TRACES_SAMPLE_RATE=0
```

### Staging
```env
SENTRY_LARAVEL_DSN=https://staging-dsn@sentry.io/123
SENTRY_TRACES_SAMPLE_RATE=0.5
SENTRY_ENVIRONMENT=staging
```

### Production
```env
SENTRY_LARAVEL_DSN=https://production-dsn@sentry.io/456
SENTRY_TRACES_SAMPLE_RATE=0.2
SENTRY_ENVIRONMENT=production
SENTRY_RELEASE=v1.0.0
```

## Testing Sentry Integration

```bash
# Test exception tracking
php artisan sentry:test

# Check DSN configuration
php artisan config:clear
php artisan tinker
>>> \Sentry\Laravel\Facades\Sentry::captureMessage('Test from tinker');
```

## Dashboard Setup

1. Create project at sentry.io
2. Set up alerts for:
   - New issues
   - Regression issues
   - Performance degradation
3. Integrate with Slack/email
4. Configure weekly digest reports

---

**Implementation Status**: Configuration ready
**Next Steps**: 
1. Sign up for Sentry account
2. Create project and get DSN
3. Add DSN to production .env
4. Deploy and verify tracking
