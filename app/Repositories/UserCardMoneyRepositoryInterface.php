<?php

namespace App\Repositories;

interface UserCardMoneyRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}