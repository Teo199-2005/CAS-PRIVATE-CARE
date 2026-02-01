<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;

/**
 * Pagination Trait
 * 
 * Provides consistent pagination for API endpoints.
 * Supports cursor-based and offset-based pagination.
 */
trait PaginationTrait
{
    /**
     * Default items per page
     */
    protected int $defaultPerPage = 25;

    /**
     * Maximum items per page
     */
    protected int $maxPerPage = 100;

    /**
     * Get pagination parameters from request
     *
     * @param Request $request
     * @return array{page: int, per_page: int, sort_by: string|null, sort_order: string}
     */
    protected function getPaginationParams(Request $request): array
    {
        $perPage = min(
            (int) $request->input('per_page', $this->defaultPerPage),
            $this->maxPerPage
        );

        return [
            'page' => max(1, (int) $request->input('page', 1)),
            'per_page' => max(1, $perPage),
            'sort_by' => $request->input('sort_by'),
            'sort_order' => strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc',
        ];
    }

    /**
     * Apply pagination to a query builder
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @param string|null $defaultSort Default column to sort by
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function paginateQuery($query, Request $request, ?string $defaultSort = 'created_at')
    {
        $params = $this->getPaginationParams($request);

        // Apply sorting
        $sortBy = $params['sort_by'] ?? $defaultSort;
        if ($sortBy) {
            $query->orderBy($sortBy, $params['sort_order']);
        }

        return $query->paginate($params['per_page']);
    }

    /**
     * Format paginated response
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @param callable|null $transformer Optional transformer for items
     * @return array
     */
    protected function formatPaginatedResponse($paginator, ?callable $transformer = null): array
    {
        $items = $transformer 
            ? $paginator->getCollection()->map($transformer) 
            : $paginator->items();

        return [
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more' => $paginator->hasMorePages(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ];
    }

    /**
     * Apply search filter to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @param array $searchableColumns Columns to search in
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySearch($query, Request $request, array $searchableColumns)
    {
        $search = $request->input('search');

        if ($search && count($searchableColumns) > 0) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }

        return $query;
    }

    /**
     * Apply date range filter to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @param string $column Date column to filter on
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDateFilter($query, Request $request, string $column = 'created_at')
    {
        if ($request->has('date_from')) {
            $query->whereDate($column, '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate($column, '<=', $request->input('date_to'));
        }

        return $query;
    }

    /**
     * Apply status filter to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @param string $column Status column
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyStatusFilter($query, Request $request, string $column = 'status')
    {
        $status = $request->input('status');

        if ($status && $status !== 'all') {
            $query->where($column, $status);
        }

        return $query;
    }
}
