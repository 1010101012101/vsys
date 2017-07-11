<?php

namespace App\Repositories;

interface NationRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}