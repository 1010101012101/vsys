<?php

namespace App\Repositories;

interface UnitRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}