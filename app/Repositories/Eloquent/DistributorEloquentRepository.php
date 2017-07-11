<?php

namespace App\Repositories\Eloquent;

use App\Repositories\DistributorRepositoryInterface;
use App\Distributor;

class DistributorEloquentRepository extends BaseEloquentRepository implements DistributorRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return Distributor::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->model
            ->where('distributors.active', true)
            ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id')
            ->leftJoin('cities', 'cities.code', '=', 'distributors.city_code')
            ->leftJoin('districts', 'districts.code', '=', 'distributors.district_code')
            ->leftJoin('wards', 'wards.code', '=', 'distributors.ward_code')
            ->select('distributors.*'
                , 'suppliers.name as supplier_name'
                , 'cities.name as city'
                , 'districts.name as district'
                , 'wards.name as ward'
            )
            ->get();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('distributors.id', $id)->first();
    }
}