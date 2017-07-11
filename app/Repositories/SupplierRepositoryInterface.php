<?php

namespace App\Repositories;

interface SupplierRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}