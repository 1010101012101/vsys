<?php

namespace App\Repositories;

interface ProductTypeRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}