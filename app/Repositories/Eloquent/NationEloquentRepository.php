<?php

namespace App\Repositories\Eloquent;

use App\Repositories\NationRepositoryInterface;
use App\Nation;

class NationEloquentRepository extends BaseEloquentRepository implements NationRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return Nation::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('nations.id', $id)->first();
    }
}