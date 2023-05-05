<?php

namespace Lab2view\Generator;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * @param  array<string>|string  $queries
     * @param  array<string>  $columns
     */
    public function all(array|string $queries = [], array $columns = ['*']): Collection|LengthAwarePaginator;

    /**
     * Get all trashed models.
     */
    public function allTrashed(): Collection;

    /**
     * Find model by id.
     *
     * @param  array<string>  $columns
     */
    public function getById(int|string $modelId, array $columns = ['*']): Model;

    /**
     * @param  array<string>  $columns
     */
    public function getByAttribute(string $attribute, string $value, array $columns = ['*']): Model;

    /**
     * Find trashed model by id.
     */
    public function findTrashedById(int $modelId): Model;

    /**
     * Find only trashed model by id.
     */
    public function findOnlyTrashedById(int $modelId): Model;

    /**
     * Create a model.
     *
     * @param  array<string, mixed>  $payload
     */
    public function store(array $payload): ?Model;

    /**
     * Update existing model.
     *
     * @param  array<string, mixed>  $payload
     */
    public function update(int|string|Model $model, array $payload): ?Model;

    /**
     * Delete model by id.
     */
    public function destroyById(int $modelId): bool;

    /**
     * Delete model.
     */
    public function destroy(Model $model): bool;

    /**
     * Restore model by id.
     */
    public function restoreById(int $modelId): bool;

    /**
     * Restore model.
     */
    public function restore(Model $model): bool;

    /**
     * Permanently delete model by id.
     */
    public function forceDeleteById(int $modelId): bool;

    /**
     * Permanently delete model.
     */
    public function forceDelete(Model $model): bool;
}
