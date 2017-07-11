<?php

namespace App\Repositories\Eloquent;

use App\Repositories\UnitRepositoryInterface;
use App\Unit;

class UnitEloquentRepository extends BaseEloquentRepository implements UnitRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return Unit::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('units.id', $id)->first();
    }
}