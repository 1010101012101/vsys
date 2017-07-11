<?php

namespace App\Repositories;

interface DistributorRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}