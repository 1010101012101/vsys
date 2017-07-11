<?php

namespace App\Repositories;

interface GroupRoleRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}