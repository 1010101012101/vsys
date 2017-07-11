<?php

namespace App\Repositories;

interface RoleRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}