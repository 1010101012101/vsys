<?php

namespace App\Repositories\Eloquent;

use App\Repositories\LoggingRepositoryInterface;
use App\Logging;

class LoggingEloquentRepository extends BaseEloquentRepository implements LoggingRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return Logging::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('loggings.id', $id)->first();
    }
}