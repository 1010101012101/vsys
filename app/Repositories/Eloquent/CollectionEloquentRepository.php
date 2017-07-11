<?php

namespace App\Repositories\Eloquent;

use App\Repositories\CollectionRepositoryInterface;
use App\Collection;

class CollectionEloquentRepository extends BaseEloquentRepository implements CollectionRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return Collection::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('collections.id', $id)->first();
    }
}