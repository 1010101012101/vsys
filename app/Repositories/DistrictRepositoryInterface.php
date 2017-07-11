<?php

namespace App\Repositories;

interface DistrictRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}