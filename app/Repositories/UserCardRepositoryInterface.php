<?php

namespace App\Repositories;

interface UserCardRepositoryInterface
{
    public function findAllSkeleton($dis_or_sup, $dis_or_sup_id);

    public function findOneSkeleton($id);
}