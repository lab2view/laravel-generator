<?php

namespace Lab2view\Generator;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @template TModel of Model
 */
interface RepositoryInterface
{
    /**
     * @param  array<string>|string  $queries
     * @return Collection<int, TModel>|LengthAwarePaginator<int, TModel>
     */
    public function all(array|string $queries = []): Collection|LengthAwarePaginator;

    /**
     * Get all trashed models.
     * @return Collection<int, TModel>
     */
    public function allTrashed(): Collection;

    /**
     * Find model by id.
     *
     * @param  array<string>  $columns
     * @return TModel
     */
    public function getById(int|string $modelId, array $columns = ['*']): Model;

    /**
     * @param  array<string>  $columns
     * @return TModel
     */
    public function getByAttribute(string $attribute, string $value, array $columns = ['*']): Model;

    /**
     * Find trashed model by id.
     * @return TModel
     */
    public function findTrashedById(int $modelId): Model;

    /**
     * Find only trashed model by id.
     * @return TModel
     */
    public function findOnlyTrashedById(int $modelId): Model;

    /**
     * Create a model.
     *
     * @param  array<string, mixed>  $payload
     * @return TModel|null
     */
    public function store(array $payload, bool $quietly = false): ?Model;

    /**
     * Update existing model.
     *
     * @param  array<string, mixed>  $payload
     * @param  int|string|TModel  $model
     * @return TModel|null
     */
    public function update(int|string|Model $model, array $payload, bool $quietly = false): ?Model;

    /**
     * Delete model by id.
     */
    public function destroyById(int $modelId): bool;

    /**
     * Delete model.
     * @param  TModel  $model
     */
    public function destroy(Model $model, bool $quietly = false): bool;

    /**
     * Restore model by id.
     */
    public function restoreById(int $modelId): bool;

    /**
     * Restore model.
     * @param  TModel  $model
     */
    public function restore(Model $model, bool $quietly = false): bool;

    /**
     * Permanently delete model by id.
     */
    public function forceDeleteById(int $modelId): bool;

    /**
     * Permanently delete model.
     * @param  TModel  $model
     */
    public function forceDelete(Model $model, bool $quietly = false): bool;
}
