<?php

namespace App\Repositories;

use App\Contracts\TeamRepositoryInterface;
use App\Models\Team;
use Illuminate\Pagination\LengthAwarePaginator;

class TeamRepository extends BaseRepository implements TeamRepositoryInterface
{
    public function __construct(Team $model)
    {
        parent::__construct($model);
    }

    /**
     * Find team by external ID (BallDontLie ID).
     */
    public function findByExternalId(int $externalId): ?object
    {
        return $this->model->where('external_id', $externalId)->first();
    }

    /**
     * Available filters:
     * - division: Filter by division
     * - conference: Filter by conference (East or West)
     */
    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['division'])) {
            $query->where('division', $filters['division']);
        }

        if (isset($filters['conference'])) {
            $query->where('conference', $filters['conference']);
        }

        return $query->paginate($perPage);
    }
}
