<?php

namespace App\Repositories\Eloquent;

use App\Repositories\IOCenterRepositoryInterface;
use App\IOCenter;

class IOCenterEloquentRepository extends BaseEloquentRepository implements IOCenterRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return IOCenter::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->model
            ->where('io_centers.active', true)
            ->leftJoin('distributors', 'distributors.id', '=', 'io_centers.dis_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id')
            ->select('io_centers.*'
                , 'distributors.id as distributor_id', 'distributors.name as distributor_name'
                , 'suppliers.id as supplier_id', 'suppliers.name as supplier_name'
            )
            ->get();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('io_centers.id', $id)->first();
    }
}