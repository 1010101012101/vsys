<?php

namespace App\Repositories;

interface PositionRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}