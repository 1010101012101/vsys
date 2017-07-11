<?php

namespace App\Repositories\Eloquent;

use App\Repositories\DistrictRepositoryInterface;
use App\District;

class DistrictEloquentRepository extends BaseEloquentRepository implements DistrictRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return District::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->model
            ->select('districts.id', 'districts.code', 'districts.name', 'districts.city_code')
            ->get();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('districts.id', $id)->first();
    }
}