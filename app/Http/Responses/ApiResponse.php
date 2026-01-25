<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * API Response Helper
 * 
 * Standardizes API responses across all endpoints for consistency.
 * Follows JSON:API-inspired structure with success/error patterns.
 * 
 * @package App\Http\Responses
 */
class ApiResponse
{
    /**
     * Return a successful response
     *
     * @param mixed $data The data to return
     * @param string|null $message Optional success message
     * @param int $status HTTP status code (default: 200)
     * @param array $meta Optional metadata
     * @return JsonResponse
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'data' => $data,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a created response (201)
     *
     * @param mixed $data The created resource
     * @param string|null $message Optional success message
     * @return JsonResponse
     */
    public static function created(mixed $data = null, ?string $message = 'Resource created successfully'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * Return a no content response (204)
     *
     * @return JsonResponse
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return an error response
     *
     * @param string $message Error message
     * @param int $status HTTP status code (default: 400)
     * @param array|null $errors Validation errors or additional error details
     * @param string|null $code Error code for programmatic handling
     * @return JsonResponse
     */
    public static function error(
        string $message,
        int $status = 400,
        ?array $errors = null,
        ?string $code = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($code) {
            $response['code'] = $code;
        }

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a validation error response (422)
     *
     * @param array $errors Validation errors
     * @param string $message Optional message
     * @return JsonResponse
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return self::error($message, 422, $errors, 'VALIDATION_ERROR');
    }

    /**
     * Return an unauthorized response (401)
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, 401, null, 'UNAUTHORIZED');
    }

    /**
     * Return a forbidden response (403)
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, 403, null, 'FORBIDDEN');
    }

    /**
     * Return a not found response (404)
     *
     * @param string $message Error message
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, 404, null, 'NOT_FOUND');
    }

    /**
     * Return a server error response (500)
     *
     * @param string $message Error message
     * @param \Throwable|null $exception Optional exception for logging
     * @return JsonResponse
     */
    public static function serverError(
        string $message = 'Internal server error',
        ?\Throwable $exception = null
    ): JsonResponse {
        if ($exception && config('app.debug')) {
            return self::error($message, 500, [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ], 'SERVER_ERROR');
        }

        return self::error($message, 500, null, 'SERVER_ERROR');
    }

    /**
     * Return a rate limit exceeded response (429)
     *
     * @param int $retryAfter Seconds until rate limit resets
     * @return JsonResponse
     */
    public static function rateLimited(int $retryAfter = 60): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
            'code' => 'RATE_LIMITED',
            'retry_after' => $retryAfter,
        ], 429)->withHeaders([
            'Retry-After' => $retryAfter,
        ]);
    }

    /**
     * Return a paginated response
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @param string|null $message Optional message
     * @return JsonResponse
     */
    public static function paginated($paginator, ?string $message = null): JsonResponse
    {
        return self::success(
            $paginator->items(),
            $message,
            200,
            [
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ]
        );
    }
}
