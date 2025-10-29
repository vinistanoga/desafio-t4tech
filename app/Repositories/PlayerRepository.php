<?php

namespace App\Repositories;

use App\Contracts\PlayerRepositoryInterface;
use App\Models\Player;
use Illuminate\Pagination\LengthAwarePaginator;

class PlayerRepository extends BaseRepository implements PlayerRepositoryInterface
{
    public function __construct(Player $model)
    {
        parent::__construct($model);
    }

    /**
     * Find player by external ID (BallDontLie ID).
     */
    public function findByExternalId(int $externalId): ?object
    {
        return $this->model->where('external_id', $externalId)->first();
    }

    /**
     * Available filters:
     * - search: first or last name contains
     * - team_ids: array of external team IDs
     * - player_ids: array of external player IDs
     */
    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['team_ids']) && is_array($filters['team_ids'])) {
            $query->whereHas('team', function ($q) use ($filters) {
                $q->whereIn('external_id', $filters['team_ids']);
            });
        }

        if (isset($filters['player_ids']) && is_array($filters['player_ids'])) {
            $query->whereIn('external_id', $filters['player_ids']);
        }

        return $query->paginate($perPage);
    }
}
