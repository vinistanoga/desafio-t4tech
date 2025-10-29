<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface PlayerRepositoryInterface extends RepositoryInterface
{
    /**
     * Find player by external ID (BallDontLie ID).
     */
    public function findByExternalId(int $externalId): ?object;

    /**
     * Available filters:
     * - search: first or last name
     * - team_ids: array of external team IDs
     * - player_ids: array of external player IDs
     */
    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}
