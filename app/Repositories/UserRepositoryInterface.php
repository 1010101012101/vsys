<?php

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function findAllSkeleton($dis_or_sup, $dis_or_sup_id);

    public function findOneSkeleton($id);

    public function findAllUserHaveNotCard($user_ids);
}