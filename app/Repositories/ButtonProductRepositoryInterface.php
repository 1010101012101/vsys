<?php

namespace App\Repositories;

interface ButtonProductRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);

    public function saveOne($data);
}