<?php

namespace App\Repositories\Eloquent;

use App\Repositories\SupplierRepositoryInterface;
use App\Supplier;

class SupplierEloquentRepository extends BaseEloquentRepository implements SupplierRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return Supplier::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->model
            ->where('suppliers.active', true)
            ->leftJoin('cities', 'cities.code', '=', 'suppliers.city_code')
            ->leftJoin('districts', 'districts.code', '=', 'suppliers.district_code')
            ->leftJoin('wards', 'wards.code', '=', 'suppliers.ward_code')
            ->select('suppliers.*'
                , 'cities.name as city', 'districts.name as district', 'wards.name as ward'
            )
            ->get();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('suppliers.id', $id)->first();
    }
}