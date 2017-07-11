<?php

namespace App\Repositories;

interface ProducerRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}