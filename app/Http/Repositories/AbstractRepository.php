<?php

namespace App\Http\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository
{
    protected Model $model;

    /**
     * @param int $id
     * @return Model|null
     */

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [], array $relations = [])
    {
        return $this->model
            ->query()
            ->when($filters, fn($query) => $query->where($filters))
            ->when($relations, fn($query) => $query->with($relations))
            ->get();

    }

    public function query(){
        return $this->model->newQuery();
    }

    public function find(int $id, array $relations = []): ?Model
    {
        return $this->model
            ->when($relations, fn($q) => $q->with($relations))
            ->findOrFail($id);
    }


    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update($model, array $data): Model
    {
        $model->update($data);
        return $model;
    }

    public function delete($model): mixed
    {
        return $model->delete();
    }

}
