<?php

namespace App\Repositories;

use App\Contracts\BaseEloquentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class EloquentRepository implements BaseEloquentRepository
{
    protected Model $model;

    const PAGINATION_COUNT = 30;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findOneById(string $id): Model|null
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findOneBy(array $criteria, $latestColumn = 'created_at'): Model|null
    {
        return $this->model->where($criteria)
            ->orderByDesc($latestColumn)
            ->first();
    }

    public function paginate(int $count = self::PAGINATION_COUNT): LengthAwarePaginator
    {
        return $this->model->paginate($count);
    }

    public function store(array $data): Model
    {
        return $this->model->create($data);
    }

    public function upsert(array $data, array $criteriaData): Model
    {
        return $this->model->updateOrCreate(
            $criteriaData,
            $data,
        );
    }

    public function update(Model $model, array $data, $silent = false): Model
    {
        if ($silent) {
            return tap($model)->updateQuietly($data);
        }

        return tap($model)->update($data);
    }

    public function findOrCreateOneBy(array $criteriaData): Model
    {
        return $this->model->firstOrCreate(
            $criteriaData
        );
    }

    public function findOneLike(string $term, string $columns): Model|null
    {
        return $this->model->newQuery()
            ->where($columns, 'like', $term)
            ->first();
    }

    public function getBy(array $criteria): array|Collection|null
    {
        return $this->model
            ->where($criteria)
            ->orderByDesc('created_at')
            ->get();
    }

    public function deleteWhereIds(array $ids): bool
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function forceDeleteWhereIds(array $ids): bool
    {
        return $this->model->whereIn('id', $ids)->forceDelete();
    }
}
