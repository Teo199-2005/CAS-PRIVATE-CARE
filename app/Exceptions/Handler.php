<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException as StripeInvalidRequestException;
use Throwable;

/**
 * Custom Exception Handler for CAS Private Care
 * 
 * Provides consistent error responses across the application
 * with proper security measures (no stack traces in production)
 */
class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'card_number',
        'cvv',
        'cvc',
        'ssn',
        'social_security',
        'bank_account',
        'routing_number',
    ];

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        CardException::class => 'warning',
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        ValidationException::class,
        ModelNotFoundException::class,
        AuthenticationException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log additional context for payment errors
            if ($e instanceof CardException || $e instanceof StripeInvalidRequestException) {
                \Log::channel('payments')->error('Stripe Error', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                ]);
            }
        });

        // Handle API responses consistently
        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($e);
            }
        });
    }

    /**
     * Handle API exceptions with consistent JSON responses
     */
    protected function handleApiException(Throwable $e)
    {
        // Validation errors
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'error' => 'Validation Error',
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        // Authentication errors
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthenticated',
                'message' => 'You must be logged in to access this resource.',
            ], 401);
        }

        // Model not found
        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            return response()->json([
                'success' => false,
                'error' => 'Not Found',
                'message' => "{$model} not found.",
            ], 404);
        }

        // Route not found
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'error' => 'Not Found',
                'message' => 'The requested resource was not found.',
            ], 404);
        }

        // Method not allowed
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'error' => 'Method Not Allowed',
                'message' => 'The HTTP method is not supported for this route.',
            ], 405);
        }

        // Rate limiting
        if ($e instanceof ThrottleRequestsException) {
            return response()->json([
                'success' => false,
                'error' => 'Too Many Requests',
                'message' => 'You have exceeded the rate limit. Please try again later.',
                'retry_after' => $e->getHeaders()['Retry-After'] ?? 60,
            ], 429);
        }

        // Stripe card errors
        if ($e instanceof CardException) {
            return response()->json([
                'success' => false,
                'error' => 'Payment Failed',
                'message' => $e->getMessage(),
                'code' => $e->getStripeCode(),
            ], 402);
        }

        // Stripe invalid request
        if ($e instanceof StripeInvalidRequestException) {
            return response()->json([
                'success' => false,
                'error' => 'Payment Error',
                'message' => 'There was an issue processing your payment. Please try again.',
            ], 400);
        }

        // Generic server error (hide details in production)
        $message = app()->environment('production')
            ? 'An unexpected error occurred. Please try again later.'
            : $e->getMessage();

        return response()->json([
            'success' => false,
            'error' => 'Server Error',
            'message' => $message,
            'debug' => app()->environment('local') ? [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5)->toArray(),
            ] : null,
        ], 500);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthenticated',
                'message' => 'You must be logged in to access this resource.',
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}
