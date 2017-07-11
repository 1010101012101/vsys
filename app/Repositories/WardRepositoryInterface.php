<?php

namespace App\Repositories;

interface WardRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}