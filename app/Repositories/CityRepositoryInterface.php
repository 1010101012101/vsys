<?php

namespace App\Repositories;

interface CityRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}