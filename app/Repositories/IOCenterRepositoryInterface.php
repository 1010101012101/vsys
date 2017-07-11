<?php

namespace App\Repositories;

interface IOCenterRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}