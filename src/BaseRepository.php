<?php

namespace Lab2view\Generator;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\QueryBuilder;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @param array{filters: array<string>, includes: array<string>, sorts: array<string>, relations: array<string>} $config
     */
    public function __construct(
        protected Model $model,
        protected array $config = [
            'filters' => [],
            'includes' => [],
            'sorts' => [],
            'relations' => [],
        ])
    {
    }

    /**
     * {@inheritDoc}
     */
    public function all(array|string $queries = [], array $columns = ['*']): Collection|LengthAwarePaginator
    {
        if (is_array($queries)) {
            $paginate = Arr::get($queries, 'paginate');
            $query = QueryBuilder::for(get_class($this->model))
                ->allowedFilters($this->config['filters'])
                ->allowedIncludes($this->config['includes'])
                ->allowedSorts($this->config['sorts']);
            if ($paginate) {
                return $query->paginate((int)$queries['paginate'])
                    ->appends($queries);
            } else {
                return $query->get($columns);
            }
        }

        return $this->model->with($this->config['relations'])->get($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function allTrashed(): Collection
    {
        if (method_exists($this->model, 'onlyTrashed'))
            return $this->model->onlyTrashed()->get();
        return new Collection([]);
    }

    /**
     * {@inheritDoc}
     */
    public function getById(int|string $modelId, array $columns = ['*']): Model
    {
        return $this->model->newQuery()->select($columns)->with($this->config['relations'])->findOrFail($modelId);
    }

    /**
     * {@inheritDoc}
     */
    public function getByAttribute(string $attribute, string $value, array $columns = ['*']): Model
    {
        return $this->model->newQuery()
            ->where($attribute, $value)
            ->select($columns)
            ->with($this->config['relations'])
            ->firstOrFail();
    }

    /**
     * {@inheritDoc}
     */
    public function findTrashedById(int|string $modelId): Model
    {
        if (method_exists($this->model, 'withTrashed'))
            return $this->model->withTrashed()->findOrFail($modelId);
        return $this->getById($modelId);
    }

    /**
     * {@inheritDoc}
     */
    public function findOnlyTrashedById(int|string $modelId): Model
    {
        if (method_exists($this->model, 'onlyTrashed'))
            return $this->model->onlyTrashed()->findOrFail($modelId);
        throw new ModelNotFoundException(__('The trashed model with the specify id it is not found.'));
    }

    /**
     * {@inheritDoc}
     */
    public function store(array $payload): ?Model
    {
        return $this->model->newQuery()->create($payload);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int|string|Model $model, array $payload): ?Model
    {
        if (is_int($model)) {
            $model = $this->getById((int)$model);
        } elseif (is_string($model)) {
            $model = $this->getById((string)$model);
        }
        $model->update($payload);

        return $model->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function destroyById(int|string $modelId): bool
    {
        return $this->getById($modelId)->delete() ?? false;
    }

    /**
     * {@inheritDoc}
     */
    public function destroy(Model $model): bool
    {
        return $model->delete() ?? false;
    }

    /**
     * {@inheritDoc}
     */
    public function restoreById(int|string $modelId): bool
    {
        $model = $this->findOnlyTrashedById($modelId);
        if (method_exists($model, 'restore'))
            return $model->restore() ?? false;

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function restore(Model $model): bool
    {
        if (method_exists($model, 'restore'))
            return $model->restore() ?? false;
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function forceDeleteById(int $modelId): bool
    {
        return $this->findTrashedById($modelId)->forceDelete() ?? false;
    }

    /**
     * {@inheritDoc}
     */
    public function forceDelete(Model $model): bool
    {
        return $model->forceDelete() ?? false;
    }
}
