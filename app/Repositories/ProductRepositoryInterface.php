<?php

namespace App\Repositories;

interface ProductRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}