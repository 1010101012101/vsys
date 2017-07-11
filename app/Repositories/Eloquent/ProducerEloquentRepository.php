<?php

namespace App\Repositories\Eloquent;

use App\Repositories\ProducerRepositoryInterface;
use App\Producer;

class ProducerEloquentRepository extends BaseEloquentRepository implements ProducerRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return Producer::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('producers.id', $id)->first();
    }
}