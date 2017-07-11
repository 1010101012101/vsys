<?php

namespace App\Repositories\Eloquent;

use App\Repositories\FileRepositoryInterface;
use App\File;

class FileEloquentRepository extends BaseEloquentRepository implements FileRepositoryInterface
{
    /** ===== INIT MODEL ===== */
    public function setModel()
    {
        return File::class;
    }

    /** ===== PUBLIC FUNCTION ===== */
    public function findAllSkeleton()
    {
        return $this->findAllActive();
    }

    public function findOneSkeleton($id)
    {
        return $this->findAllSkeleton()->where('files.id', $id)->first();
    }

    public function findOneActiveByTableNameAndTableId($table_name, $table_id)
    {
        return $this->model
            ->whereActive(true)
            ->where('table_name', $table_name)
            ->where('table_id', $table_id)
            ->first();
    }
}