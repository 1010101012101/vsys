<?php

namespace App\Repositories;

interface ProductPriceRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}