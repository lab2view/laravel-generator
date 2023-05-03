<?php

namespace Lab2view\Generator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * @param array{filters: array<string>, includes: array<string>, sorts: array<string>, relations: array<string>} $config
     * @return void
     */
    public function setAllowedConfigs(array $config): void;

    /**
     * @param array<string>|string $queries
     * @param array<string>  $columns
     * @return Collection|LengthAwarePaginator
     */
    public function all(array|string $queries = [], array $columns = ['*']): Collection|LengthAwarePaginator;

    /**
     * Get all trashed models.
     *
     * @return Collection
     */
    public function allTrashed(): Collection;

    /**
     * Find model by id.
     *
     * @param int $modelId
     * @param array<string>  $columns
     * @return Model
     */
    public function getById(int $modelId, array $columns = ['*']): Model;

    /**
     * Find trashed model by id.
     *
     * @param int $modelId
     * @return Model
     */
    public function findTrashedById(int $modelId): Model;

    /**
     * Find only trashed model by id.
     *
     * @param int $modelId
     * @return Model
     */
    public function findOnlyTrashedById(int $modelId): Model;

    /**
     * Create a model.
     *
     * @param array<string, mixed>  $payload
     * @return Model|null
     */
    public function store(array $payload): ?Model;

    /**
     * Update existing model.
     *
     * @param int|string|Model $model
     * @param array<string, mixed> $payload
     * @return Model|null
     */
    public function update(int|string|Model $model, array $payload): ?Model;

    /**
     * Delete model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function destroyById(int $modelId): bool;

    /**
     * Delete model.
     *
     * @param Model $model
     * @return bool
     */
    public function destroy(Model $model): bool;

    /**
     * Restore model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function restoreById(int $modelId): bool;

    /**
     * Restore model.
     *
     * @param Model $model
     * @return bool
     */
    public function restore(Model $model): bool;

    /**
     * Permanently delete model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function forceDeleteById(int $modelId): bool;

    /**
     * Permanently delete model.
     *
     * @param Model $model
     * @return bool
     */
    public function forceDelete(Model $model): bool;
}
