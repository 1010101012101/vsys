<?php

namespace App\Repositories;

interface CollectionRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}