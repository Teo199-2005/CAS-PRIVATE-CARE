<?php

namespace App\Exceptions;

use Exception;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\IdempotencyException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Illuminate\Support\Facades\Log;

/**
 * Handles Stripe-specific exceptions with appropriate user-friendly messages
 * and logging for debugging.
 */
class StripeExceptionHandler
{
    /**
     * Handle a Stripe exception and return appropriate response data
     *
     * @param \Exception $e
     * @return array{success: false, error: string, code: string, status: int}
     */
    public static function handle(Exception $e): array
    {
        // Card errors (declined, expired, etc.)
        if ($e instanceof CardException) {
            return self::handleCardException($e);
        }

        // Invalid request (missing parameters, invalid data)
        if ($e instanceof InvalidRequestException) {
            return self::handleInvalidRequest($e);
        }

        // Authentication failures
        if ($e instanceof AuthenticationException) {
            Log::critical('Stripe authentication failed - check API keys', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Payment system configuration error. Please contact support.',
                'code' => 'authentication_error',
                'status' => 500
            ];
        }

        // Rate limiting
        if ($e instanceof RateLimitException) {
            Log::warning('Stripe rate limit exceeded', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Too many requests. Please wait a moment and try again.',
                'code' => 'rate_limit',
                'status' => 429
            ];
        }

        // Connection errors
        if ($e instanceof ApiConnectionException) {
            Log::error('Stripe API connection failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Unable to connect to payment service. Please try again.',
                'code' => 'connection_error',
                'status' => 503
            ];
        }

        // Idempotency errors (duplicate requests)
        if ($e instanceof IdempotencyException) {
            Log::warning('Stripe idempotency error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'This request was already processed.',
                'code' => 'idempotency_error',
                'status' => 409
            ];
        }

        // Generic Stripe API errors
        if ($e instanceof ApiErrorException) {
            Log::error('Stripe API error', [
                'error' => $e->getMessage(),
                'type' => get_class($e)
            ]);
            
            return [
                'success' => false,
                'error' => 'Payment processing error. Please try again.',
                'code' => 'api_error',
                'status' => 500
            ];
        }

        // Unknown errors
        Log::error('Unexpected Stripe error', [
            'error' => $e->getMessage(),
            'type' => get_class($e)
        ]);

        return [
            'success' => false,
            'error' => 'An unexpected error occurred. Please try again.',
            'code' => 'unknown_error',
            'status' => 500
        ];
    }

    /**
     * Handle card-specific exceptions with user-friendly messages
     */
    protected static function handleCardException(CardException $e): array
    {
        $declineCode = $e->getDeclineCode();
        $errorCode = $e->getStripeCode();

        // Map decline codes to user-friendly messages
        $messages = [
            'card_declined' => 'Your card was declined. Please try a different card.',
            'insufficient_funds' => 'Insufficient funds. Please try a different card.',
            'expired_card' => 'Your card has expired. Please use a different card.',
            'incorrect_cvc' => 'The CVC code is incorrect. Please check and try again.',
            'incorrect_number' => 'The card number is incorrect. Please check and try again.',
            'processing_error' => 'An error occurred while processing your card. Please try again.',
            'do_not_honor' => 'Your bank declined this transaction. Please contact your bank.',
            'lost_card' => 'This card has been reported lost. Please use a different card.',
            'stolen_card' => 'This card has been reported stolen. Please use a different card.',
            'fraudulent' => 'This transaction was flagged as suspicious. Please contact your bank.',
            'card_not_supported' => 'This card type is not supported. Please try a different card.',
            'currency_not_supported' => 'This currency is not supported by your card.',
            'duplicate_transaction' => 'A duplicate transaction was detected. Please wait and try again.',
            'invalid_account' => 'The card account is invalid. Please contact your bank.',
            'pickup_card' => 'This card cannot be used. Please contact your bank.',
            'restricted_card' => 'This card is restricted. Please use a different card.',
            'try_again_later' => 'Unable to process right now. Please try again later.',
        ];

        $message = $messages[$declineCode] ?? $messages[$errorCode] ?? $e->getMessage();

        Log::info('Card declined', [
            'decline_code' => $declineCode,
            'error_code' => $errorCode,
            'message' => $e->getMessage()
        ]);

        return [
            'success' => false,
            'error' => $message,
            'code' => $declineCode ?? $errorCode ?? 'card_error',
            'status' => 400
        ];
    }

    /**
     * Handle invalid request exceptions
     */
    protected static function handleInvalidRequest(InvalidRequestException $e): array
    {
        $param = $e->getStripeParam();
        $code = $e->getStripeCode();

        // Common invalid request messages
        $messages = [
            'resource_missing' => 'The requested resource was not found.',
            'parameter_missing' => 'A required parameter is missing.',
            'parameter_invalid' => 'A parameter value is invalid.',
            'amount_too_small' => 'The payment amount is too small.',
            'amount_too_large' => 'The payment amount exceeds the maximum allowed.',
        ];

        $message = $messages[$code] ?? 'Invalid request. Please check your input.';

        Log::warning('Stripe invalid request', [
            'code' => $code,
            'param' => $param,
            'message' => $e->getMessage()
        ]);

        return [
            'success' => false,
            'error' => $message,
            'code' => $code ?? 'invalid_request',
            'status' => 400,
            'param' => $param
        ];
    }

    /**
     * Check if an exception is retryable
     */
    public static function isRetryable(Exception $e): bool
    {
        // Rate limits and connection issues are retryable
        if ($e instanceof RateLimitException || $e instanceof ApiConnectionException) {
            return true;
        }

        // Some card declines are retryable
        if ($e instanceof CardException) {
            $retryableCodes = ['processing_error', 'try_again_later'];
            return in_array($e->getDeclineCode(), $retryableCodes);
        }

        return false;
    }

    /**
     * Get retry delay in seconds based on exception type
     */
    public static function getRetryDelay(Exception $e, int $attempt = 1): int
    {
        // Exponential backoff with jitter
        $baseDelay = match (true) {
            $e instanceof RateLimitException => 5,
            $e instanceof ApiConnectionException => 2,
            default => 1
        };

        return min($baseDelay * pow(2, $attempt - 1) + random_int(0, 1000) / 1000, 30);
    }
}
