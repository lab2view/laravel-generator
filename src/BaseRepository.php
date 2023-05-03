<?php

namespace Lab2view\Generator;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var array{filters: array<string>, includes: array<string>, sorts: array<string>, relations: array<string>}
     */
    private array $config = [
        'filters' => [],
        'includes' => [],
        'sorts' => [],
        'relations' => [],
    ];

    public function __construct(protected Model $model)
    {
    }

    /**
     * @inheritDoc
     */
    public abstract function setAllowedConfigs(array $config): void;

    /**
     * @inheritDoc
     */
    public function all(array|string $queries = [], array $columns = ['*']): Collection|LengthAwarePaginator
    {
        if (is_array($queries)) {
            $paginate = Arr::get($queries, 'paginate');
            $query = QueryBuilder::for(get_class($this->model))
                ->allowedFilters($this->config['filters'])
                ->allowedIncludes($this->config['includes'])
                ->allowedSorts($this->config['sorts']);
            if ($paginate)
                return $query->paginate((int)$queries['paginate'])
                    ->appends($queries);
            else
                return $query->get($columns);
        }
        return $this->model->with($this->config['relations'])->get($columns);
    }

    /**
     * @inheritDoc
     */
    public function allTrashed(): Collection
    {
        return $this->model->newQuery()->onlyTrashed()->get();
    }

    /**
     * @inheritDoc
     */
    public function getById(int $modelId, array $columns = ['*']): Model
    {
        return $this->model->newQuery()->select($columns)->with($this->config['relations'])->findOrFail($modelId);
    }

    /**
     * @inheritDoc
     */
    public function findTrashedById(int $modelId): Model
    {
        return $this->model->newQuery()->withTrashed()->findOrFail($modelId);
    }

    /**
     * @inheritDoc
     */
    public function findOnlyTrashedById(int $modelId): Model
    {
        return $this->model->newQuery()->onlyTrashed()->findOrFail($modelId);
    }

    /**
     * @inheritDoc
     */
    public function store(array $payload): ?Model
    {
        return $this->model->newQuery()->create($payload);
    }

    /**
     * @inheritDoc
     */
    public function update(int|string|Model $model, array $payload): ?Model
    {
        if (is_int($model) || is_string($model))
            $model = $this->getById($model);
        $model->update($payload);
        return $model->fresh();
    }

    /**
     * @inheritDoc
     */
    public function destroyById(int $modelId): bool
    {
        return $this->getById($modelId)?->delete() ?? false;
    }

    /**
     * @inheritDoc
     */
    public function destroy(Model $model): bool
    {
        return $model->delete() ?? false;
    }

    /**
     * @inheritDoc
     */
    public function restoreById(int $modelId): bool
    {
        return $this->findOnlyTrashedById($modelId)?->restore() ?? false;
    }

    /**
     * @inheritDoc
     */
    public function restore(Model $model): bool
    {
        return $model->restore() ?? false;
    }

    /**
     * @inheritDoc
     */
    public function forceDeleteById(int $modelId): bool
    {
        return $this->findTrashedById($modelId)?->forceDelete() ?? false;
    }

    /**
     * @inheritDoc
     */
    public function forceDelete(Model $model): bool
    {
        return $model->forceDelete() ?? false;
    }
}
