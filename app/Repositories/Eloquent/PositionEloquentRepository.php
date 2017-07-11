<?php

namespace App\Repositories\Eloquent;

use App\Repositories\PositionRepositoryInterface;
use App\Position;

class PositionEloquentRepository extends BaseEloquentRepository implements PositionRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return Position::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('positions.id', $id)->first();
    }
}