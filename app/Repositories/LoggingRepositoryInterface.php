<?php

namespace App\Repositories;

interface LoggingRepositoryInterface
{
    public function findAllSkeleton();

    public function findOneSkeleton($id);
}