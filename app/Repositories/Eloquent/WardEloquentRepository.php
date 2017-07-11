<?php

namespace App\Repositories\Eloquent;

use App\Repositories\WardRepositoryInterface;
use App\Ward;

class WardEloquentRepository extends BaseEloquentRepository implements WardRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return Ward::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->model
            ->select('wards.id', 'wards.code', 'wards.name', 'wards.district_code')
            ->get();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('wards.id', $id)->first();
    }
}