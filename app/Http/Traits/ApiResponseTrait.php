<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

/**
 * API Response Trait
 * 
 * Provides standardized API response formats for consistency across all controllers.
 * All API responses should use this trait to ensure uniform error/success formats.
 * 
 * Response Format:
 * {
 *   "success": boolean,
 *   "message": string,
 *   "data": mixed (optional),
 *   "errors": array (optional, for validation errors),
 *   "meta": object (optional, for pagination etc.)
 * }
 */
trait ApiResponseTrait
{
    /**
     * Return a successful response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operation successful',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a created response (201)
     */
    protected function createdResponse(
        mixed $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return a no content response (204)
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return an error response
     */
    protected function errorResponse(
        string $message = 'An error occurred',
        int $status = 400,
        array $errors = [],
        string $code = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        if ($code !== null) {
            $response['error_code'] = $code;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a validation error response (422)
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors, 'VALIDATION_ERROR');
    }

    /**
     * Return an unauthorized response (401)
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return $this->errorResponse($message, 401, [], 'UNAUTHORIZED');
    }

    /**
     * Return a forbidden response (403)
     */
    protected function forbiddenResponse(
        string $message = 'Forbidden'
    ): JsonResponse {
        return $this->errorResponse($message, 403, [], 'FORBIDDEN');
    }

    /**
     * Return a not found response (404)
     */
    protected function notFoundResponse(
        string $message = 'Resource not found'
    ): JsonResponse {
        return $this->errorResponse($message, 404, [], 'NOT_FOUND');
    }

    /**
     * Return a conflict response (409)
     */
    protected function conflictResponse(
        string $message = 'Resource conflict'
    ): JsonResponse {
        return $this->errorResponse($message, 409, [], 'CONFLICT');
    }

    /**
     * Return a too many requests response (429)
     */
    protected function tooManyRequestsResponse(
        string $message = 'Too many requests',
        int $retryAfter = 60
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'RATE_LIMITED',
            'retry_after' => $retryAfter,
        ], 429)->header('Retry-After', $retryAfter);
    }

    /**
     * Return a server error response (500)
     */
    protected function serverErrorResponse(
        string $message = 'Internal server error'
    ): JsonResponse {
        return $this->errorResponse($message, 500, [], 'SERVER_ERROR');
    }

    /**
     * Return a paginated response
     */
    protected function paginatedResponse(
        $paginator,
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], 200);
    }
}
