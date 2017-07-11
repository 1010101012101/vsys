<?php

namespace App\Repositories;

interface FileRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);

    public function findOneActiveByTableNameAndTableId($table_name, $table_id);
}