<?php

namespace Lab2view\Generator;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

abstract class BaseRepository implements RepositoryInterface
{
    private ?string $defaultSort = null;

    /**
     * @param  array{filters: array<int, AllowedFilter|string>, includes: array<string>, sorts: array<string>, relations: array<string>}  $config
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
    public function all(array|string $queries = []): Collection|LengthAwarePaginator
    {
        if (is_array($queries)) {
            $paginate = Arr::get($queries, 'paginate');

            $query = QueryBuilder::for(get_class($this->model))
                ->allowedFilters($this->config['filters'])
                ->allowedIncludes($this->config['includes'])
                ->allowedSorts($this->config['sorts']);
            if ($this->defaultSort) {
                $query = $query->defaultSort($this->defaultSort);
            }

            $query = $query->with($this->config['relations']);

            $columns = $this->getSelectedAttributes($queries);
            if ($paginate) {
                return $query->paginate((int) $queries['paginate'], $columns)
                    ->appends($queries);
            } else {
                return $query->get($columns);
            }
        }

        return $this->model->with($this->config['relations'])->get();
    }

    /**
     * {@inheritDoc}
     */
    public function allTrashed(): Collection
    {
        if (method_exists($this->model, 'onlyTrashed')) {
            return $this->model->onlyTrashed()->get();
        }

        return new Collection([]);
    }

    /**
     * {@inheritDoc}
     */
    public function getById(int|string $modelId, array $columns = ['*']): Model
    {
        $query = QueryBuilder::for(get_class($this->model))
            ->allowedIncludes($this->config['includes'])
            ->select($columns);

        if (count($this->config['relations']) > 0) {
            $query = $query->with($this->config['relations']);
        }

        return $query->findOrFail($modelId);
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
        if (method_exists($this->model, 'withTrashed')) {
            return $this->model->withTrashed()->findOrFail($modelId);
        }

        return $this->getById($modelId);
    }

    /**
     * {@inheritDoc}
     */
    public function findOnlyTrashedById(int|string $modelId): Model
    {
        if (method_exists($this->model, 'onlyTrashed')) {
            return $this->model->onlyTrashed()->findOrFail($modelId);
        }
        throw new ModelNotFoundException(__('The trashed model with the specify id it is not found.'));
    }

    /**
     * {@inheritDoc}
     */
    public function store(array $payload): ?Model
    {
        $model = $this->model->newQuery()->create($payload);
        if (count($this->config['relations']) > 0) {
            $model = $model->load($this->config['relations']);
        }

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function update(int|string|Model $model, array $payload): ?Model
    {
        if (is_int($model) || is_string($model)) {
            $model = $this->getById($model);
        }
        $model->update($payload);

        if (count($this->config['relations']) > 0) {
            $model = $model->load($this->config['relations']);
        }

        return $model->refresh();
    }

    /**
     * {@inheritDoc}
     */
    public function destroyById(int|string $modelId): bool
    {
        return $this->model->newQuery()->where('id', $modelId)->delete() ?? false;
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
        if (method_exists($model, 'restore')) {
            return $model->restore() ?? false;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function restore(Model $model): bool
    {
        if (method_exists($model, 'restore')) {
            return $model->restore() ?? false;
        }

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

    /**
     * @param  array<string>  $queries
     * @return array<string>
     */
    private function getSelectedAttributes(array $queries): array
    {
        $queries = Arr::get($queries, config('lab2view-generator.request_query_attribute', 'attributes'));
        if (is_string($queries)) {
            $queries = explode(',', $queries);
        }

        if (is_array($queries) && count($this->model->getFillable()) > 0) {
            $queries = Arr::where($queries, fn ($value) => in_array($value, $this->model->getFillable()));
            if (count($queries) == 0) {
                $queries = null;
            }
        }

        return $queries != null ? Arr::prepend($queries, $this->model->getKeyName()) : ['*'];
    }

    protected function setDefaultSort(string $defaultSort): void
    {
        $this->defaultSort = $defaultSort;
    }
}
