<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseEloquentRepository
{
    public function findOneById(string $id): Model|null;

    public function findOneBy(array $criteria, $latestColumn = 'created_at'): Model|null;

    public function paginate(): LengthAwarePaginator;

    public function store(array $data): Model;

    public function upsert(array $data, array $criteriaData): Model;

    public function update(Model $model, array $data, $silent = false): Model;

    public function findOrCreateOneBy(array $criteriaData): Model;

    public function findOneLike(string $term, string $columns): Model|null;

    public function getBy(array $criteria): array|Collection|null;

    public function deleteWhereIds(array $ids): bool;

    public function forceDeleteWhereIds(array $ids): bool;
}
