<?php

namespace App\Services;

use App\Contracts\PlayerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class PlayerService
{
    public function __construct(
        protected PlayerRepositoryInterface $playerRepository
    ) {}

    /**
     * Get all players.
     */
    public function getAllPlayers(): Collection
    {
        return $this->playerRepository->all();
    }

    /**
     * Filters available:
     * - search: Returns players whose first or last name matches this value
     * - first_name: Exact first name match
     * - last_name: Exact last name match
     * - team_ids: Array of team external IDs (?team_ids[]=1&team_ids[]=2)
     * - player_ids: Array of player external IDs (?player_ids[]=1&player_ids[]=2)
     */
    public function getPaginatedPlayers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (empty($filters)) {
            return $this->playerRepository->paginate($perPage);
        }

        return $this->playerRepository->getWithFilters($filters, $perPage);
    }

    /**
     * Find player by ID.
     */
    public function findPlayer(int $id): ?Model
    {
        return $this->playerRepository->find($id);
    }

    /**
     * Create a new player.
     */
    public function createPlayer(array $data): Model
    {
        return $this->playerRepository->create($data);
    }

    /**
     * Update a player.
     */
    public function updatePlayer(int $id, array $data): Model
    {
        return $this->playerRepository->update($id, $data);
    }

    /**
     * Delete a player.
     */
    public function deletePlayer(int $id): bool
    {
        return $this->playerRepository->delete($id);
    }
}
