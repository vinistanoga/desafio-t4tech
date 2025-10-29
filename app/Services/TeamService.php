<?php

namespace App\Services;

use App\Contracts\TeamRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class TeamService
{
    public function __construct(
        protected TeamRepositoryInterface $teamRepository
    ) {}

    /**
     * Get all teams.
     */
    public function getAllTeams(): Collection
    {
        return $this->teamRepository->all();
    }

    /**
     * Filters available:
     * - division: Filter by division
     * - conference: Filter by conference (East or West)
     */
    public function getPaginatedTeams(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (empty($filters)) {
            return $this->teamRepository->paginate($perPage);
        }

        return $this->teamRepository->getWithFilters($filters, $perPage);
    }

    /**
     * Find team by ID.
     */
    public function findTeam(int $id): ?Model
    {
        return $this->teamRepository->findOrFail($id);
    }

    /**
     * Create a new team.
     */
    public function createTeam(array $data): Model
    {
        return $this->teamRepository->create($data);
    }

    /**
     * Update a team.
     */
    public function updateTeam(int $id, array $data): Model
    {
        return $this->teamRepository->update($id, $data);
    }

    /**
     * Delete a team.
     */
    public function deleteTeam(int $id): bool
    {
        return $this->teamRepository->delete($id);
    }
}
