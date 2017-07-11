<?php

namespace App\Repositories;

interface HistoryInputOutputRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}