<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface TeamRepositoryInterface extends RepositoryInterface
{
    /**
     * Find team by external ID (BallDontLie ID).
     */
    public function findByExternalId(int $externalId): ?object;

    /**
     * Available filters:
     * - division: Filter by division
     * - conference: Filter by conference (East or West)
     */
    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}
