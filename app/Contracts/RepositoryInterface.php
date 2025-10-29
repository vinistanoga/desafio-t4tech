<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * Get all records.
     */
    public function all(): Collection;

    /**
     * Get paginated records.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find record by ID.
     */
    public function find(int $id): ?Model;

    /**
     * Find record by ID or fail.
     */
    public function findOrFail(int $id): Model;

    /**
     * Create a new record.
     */
    public function create(array $data): Model;

    /**
     * Update a record.
     */
    public function update(int $id, array $data): Model;

    /**
     * Delete a record.
     */
    public function delete(int $id): bool;

    /**
     * Find by specific column.
     */
    public function findBy(string $column, mixed $value): ?Model;

    /**
     * Check if record exists.
     */
    public function exists(int $id): bool;
}
