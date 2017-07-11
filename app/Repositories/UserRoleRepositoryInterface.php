<?php

namespace App\Repositories;

interface UserRoleRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}