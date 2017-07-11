<?php

namespace App\Repositories\Eloquent;

use App\Repositories\CityRepositoryInterface;
use App\City;

class CityEloquentRepository extends BaseEloquentRepository implements CityRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return City::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->model
            ->select('cities.id', 'cities.code', 'cities.name')
            ->get();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('cities.id', $id)->first();
    }
}