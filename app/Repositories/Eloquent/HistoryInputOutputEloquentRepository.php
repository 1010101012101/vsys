<?php

namespace App\Repositories\Eloquent;

use App\Repositories\HistoryInputOutputRepositoryInterface;
use App\HistoryInputOutput;

class HistoryInputOutputEloquentRepository extends BaseEloquentRepository implements HistoryInputOutputRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return HistoryInputOutput::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('history_input_outputs.id', $id)->first();
    }
}